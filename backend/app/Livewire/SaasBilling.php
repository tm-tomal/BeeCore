<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\SaasInvoice;
use App\Models\SaasInvoiceItem;
use App\Models\SaasPayment;
use App\Models\SaasRefund;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class SaasBilling extends Component
{
    use WithPagination;

    public string $statusFilter = '';
    public string $search = '';

    public ?int $detailInvoiceId = null;

    public ?int $adjustmentForInvoiceId = null;
    public string $adjustmentType = 'discount';
    public string $adjustmentDescription = '';
    public float $adjustmentAmount = 0;

    public ?int $refundForPaymentId = null;
    public float $refundAmount = 0;
    public string $refundReason = '';

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function viewInvoice(int $id): void
    {
        $this->detailInvoiceId = $id;
    }

    public function openAdjustment(int $invoiceId): void
    {
        $this->adjustmentForInvoiceId = $invoiceId;
        $this->adjustmentType = 'discount';
        $this->adjustmentDescription = '';
        $this->adjustmentAmount = 0;
    }

    public function addAdjustment(): void
    {
        $this->assertSuperAdmin();

        $data = $this->validate([
            'adjustmentType' => ['required', Rule::in(['discount', 'credit', 'adjustment'])],
            'adjustmentDescription' => ['required', 'string', 'max:255'],
            'adjustmentAmount' => ['required', 'numeric', 'min:0.01'],
        ]);

        DB::transaction(function () use ($data) {
            $invoice = SaasInvoice::query()->lockForUpdate()->findOrFail($this->adjustmentForInvoiceId);
            abort_if(in_array($invoice->status, ['cancelled', 'refunded'], true), 422, 'This invoice can no longer be adjusted.');

            $signedAmount = in_array($data['adjustmentType'], ['discount', 'credit'], true) ? -abs($data['adjustmentAmount']) : abs($data['adjustmentAmount']);

            SaasInvoiceItem::create([
                'saas_invoice_id' => $invoice->id,
                'type' => $data['adjustmentType'],
                'description' => $data['adjustmentDescription'],
                'amount' => $signedAmount,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            $newTotal = max(0, $invoice->items()->sum('amount'));
            $invoice->update(['amount' => $newTotal]);

            AuditLog::record('saas.invoice.adjusted', $invoice, [
                'type' => $data['adjustmentType'],
                'amount' => $signedAmount,
            ], tenantId: $invoice->tenant_id);
        });

        $this->adjustmentForInvoiceId = null;
        session()->flash('message', 'Invoice adjustment recorded.');
    }

    public function cancelInvoice(int $id): void
    {
        $this->assertSuperAdmin();

        $invoice = SaasInvoice::findOrFail($id);
        abort_unless(in_array($invoice->status, ['pending', 'overdue'], true), 422, 'Only a pending or overdue invoice can be cancelled.');

        $invoice->update(['status' => 'cancelled']);
        AuditLog::record('saas.invoice.cancelled', $invoice, tenantId: $invoice->tenant_id);
        session()->flash('message', 'SaaS invoice cancelled.');
    }

    public function sendReminder(int $id): void
    {
        $this->assertSuperAdmin();

        $invoice = SaasInvoice::findOrFail($id);
        abort_unless(in_array($invoice->status, ['pending', 'overdue'], true), 422, 'Reminders can only be sent for unpaid invoices.');

        $invoice->update(['reminder_sent_at' => now()]);
        AuditLog::record('saas.invoice.reminder_sent', $invoice, tenantId: $invoice->tenant_id);
        session()->flash('message', 'Payment reminder logged for '.$invoice->invoice_number.'.');
    }

    public function openRefund(int $paymentId): void
    {
        $this->refundForPaymentId = $paymentId;
        $this->refundAmount = 0;
        $this->refundReason = '';
    }

    public function recordRefund(): void
    {
        $this->assertSuperAdmin();

        $data = $this->validate([
            'refundAmount' => ['required', 'numeric', 'min:0.01'],
            'refundReason' => ['nullable', 'string', 'max:255'],
        ]);

        $payment = SaasPayment::with('invoice')->findOrFail($this->refundForPaymentId);
        $refundable = $payment->amount - $payment->refunds()->sum('amount');

        if ($data['refundAmount'] > $refundable) {
            $this->addError('refundAmount', 'Refund amount exceeds the refundable balance of ৳'.number_format($refundable, 2).' on this payment.');

            return;
        }

        DB::transaction(function () use ($data, $payment) {
            $payment = SaasPayment::query()->with('invoice')->lockForUpdate()->findOrFail($payment->id);

            SaasRefund::create([
                'tenant_id' => $payment->tenant_id,
                'saas_payment_id' => $payment->id,
                'amount' => $data['refundAmount'],
                'reason' => $data['refundReason'] ?: null,
                'refunded_by' => auth()->id(),
                'refunded_at' => now(),
            ]);

            $invoice = $payment->invoice;
            $totalRefundedForInvoice = SaasRefund::whereIn('saas_payment_id', $invoice->payments()->pluck('id'))->sum('amount');

            if ($totalRefundedForInvoice >= $invoice->amount && $invoice->status === 'paid') {
                $invoice->update(['status' => 'refunded']);
            }

            AuditLog::record('saas.payment.refunded', $payment, [
                'amount' => $data['refundAmount'],
                'reason' => $data['refundReason'],
            ], tenantId: $payment->tenant_id);
        });

        $this->refundForPaymentId = null;
        session()->flash('message', 'Refund recorded.');
    }

    public function closeModals(): void
    {
        $this->detailInvoiceId = null;
        $this->adjustmentForInvoiceId = null;
        $this->refundForPaymentId = null;
    }

    public function render()
    {
        $this->assertSuperAdmin();

        $baseQuery = SaasInvoice::query();

        return view('livewire.saas-billing', [
            'invoices' => SaasInvoice::query()
                ->with(['tenant', 'subscription.plan'])
                ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
                ->when($this->search !== '', fn ($query) => $query->where(fn ($q) => $q
                    ->where('invoice_number', 'like', '%'.$this->search.'%')
                    ->orWhereHas('tenant', fn ($t) => $t->where('name', 'like', '%'.$this->search.'%'))))
                ->latest('due_date')
                ->paginate(15),
            'detailInvoice' => $this->detailInvoiceId
                ? SaasInvoice::with(['tenant', 'items.creator', 'payments.refunds'])->find($this->detailInvoiceId)
                : null,
            'summary' => [
                'collected' => (clone $baseQuery)->whereIn('status', ['paid', 'refunded'])->sum('amount'),
                'outstanding' => (clone $baseQuery)->whereIn('status', ['pending', 'overdue'])->sum('amount'),
                'overdue' => (clone $baseQuery)->where('status', 'overdue')->sum('amount'),
                'credits' => (float) SaasInvoiceItem::whereIn('type', ['discount', 'credit'])->sum('amount') * -1,
                'refunds' => (float) SaasRefund::sum('amount'),
            ],
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
