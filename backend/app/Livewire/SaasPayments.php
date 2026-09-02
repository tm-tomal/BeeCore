<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\SaasInvoice;
use App\Models\SaasPayment;
use App\Models\SaasRefund;
use App\Models\TenantSubscriptionEvent;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class SaasPayments extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $methodFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public ?int $recordForInvoiceId = null;
    public float $recordAmount = 0;
    public string $recordMethod = 'manual';
    public string $recordReference = '';
    public bool $recordAsPending = false;

    public function updated($property): void
    {
        if (in_array($property, ['search', 'statusFilter', 'methodFilter', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    public function openRecordPayment(int $invoiceId): void
    {
        $invoice = SaasInvoice::findOrFail($invoiceId);
        $alreadyPaid = $invoice->payments()->where('status', 'completed')->sum('amount');
        $this->recordForInvoiceId = $invoiceId;
        $this->recordAmount = max(0, $invoice->amount - $alreadyPaid);
        $this->recordMethod = 'manual';
        $this->recordReference = '';
        $this->recordAsPending = false;
    }

    public function recordPayment(): void
    {
        $this->assertSuperAdmin();

        $data = $this->validate([
            'recordAmount' => ['required', 'numeric', 'min:0.01'],
            'recordMethod' => ['required', 'string', 'max:50'],
            'recordReference' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data) {
            $invoice = SaasInvoice::query()->lockForUpdate()->findOrFail($this->recordForInvoiceId);
            abort_unless(in_array($invoice->status, ['pending', 'overdue'], true), 422, 'Only a pending or overdue invoice can receive a payment.');

            $payment = SaasPayment::create([
                'tenant_id' => $invoice->tenant_id,
                'saas_invoice_id' => $invoice->id,
                'recorded_by' => auth()->id(),
                'amount' => $data['recordAmount'],
                'method' => $data['recordMethod'],
                'reference' => $data['recordReference'] ?: null,
                'status' => $this->recordAsPending ? 'pending' : 'completed',
                'verified_at' => $this->recordAsPending ? null : now(),
                'verified_by' => $this->recordAsPending ? null : auth()->id(),
                'paid_at' => now(),
            ]);

            if (!$this->recordAsPending) {
                $this->settleInvoiceIfCovered($invoice);
            }

            AuditLog::record('saas.payment.recorded', $payment, [
                'amount' => $data['recordAmount'],
                'method' => $data['recordMethod'],
                'status' => $payment->status,
            ], tenantId: $invoice->tenant_id);
        });

        $this->recordForInvoiceId = null;
        session()->flash('message', 'Payment recorded.');
    }

    public function verifyPayment(int $id): void
    {
        $this->assertSuperAdmin();

        DB::transaction(function () use ($id) {
            $payment = SaasPayment::query()->lockForUpdate()->findOrFail($id);
            abort_unless($payment->status === 'pending', 422, 'Only a pending payment can be verified.');

            $payment->update(['status' => 'completed', 'verified_at' => now(), 'verified_by' => auth()->id()]);
            $this->settleInvoiceIfCovered(SaasInvoice::query()->lockForUpdate()->findOrFail($payment->saas_invoice_id));

            AuditLog::record('saas.payment.verified', $payment, tenantId: $payment->tenant_id);
        });

        session()->flash('message', 'Payment verified.');
    }

    public function markFailed(int $id): void
    {
        $this->assertSuperAdmin();

        DB::transaction(function () use ($id) {
            $payment = SaasPayment::query()->lockForUpdate()->findOrFail($id);
            abort_unless($payment->status === 'pending', 422, 'Only a pending payment can be marked failed.');

            $payment->update(['status' => 'failed']);
            AuditLog::record('saas.payment.failed', $payment, tenantId: $payment->tenant_id);

            // If this was the only pending payment for a pending-approval order, close the order.
            $subscription = $payment->invoice?->subscription;
            if (!$subscription || $subscription->status !== 'pending_approval') {
                return;
            }

            $otherPending = SaasPayment::query()
                ->whereIn('saas_invoice_id', $subscription->invoices()->pluck('id'))
                ->where('status', 'pending')
                ->where('id', '!=', $payment->id)
                ->exists();

            if (!$otherPending) {
                $subscription->update(['status' => 'cancelled', 'cancelled_at' => now(), 'auto_renew' => false]);
                TenantSubscriptionEvent::create([
                    'tenant_subscription_id' => $subscription->id,
                    'user_id' => auth()->id(),
                    'event' => 'subscription.cancelled',
                    'from_status' => 'pending_approval',
                    'to_status' => 'cancelled',
                    'metadata' => ['reason' => 'payment_failed'],
                    'created_at' => now(),
                ]);
                AuditLog::record('tenant.subscription.cancelled', $subscription, tenantId: $subscription->tenant_id);
            }
        });

        session()->flash('message', 'Payment marked failed.');
    }

    public function closeModals(): void
    {
        $this->recordForInvoiceId = null;
    }

    public function render()
    {
        $this->assertSuperAdmin();

        $payments = SaasPayment::query()
            ->with(['tenant', 'invoice', 'refunds'])
            ->when($this->search, fn ($query) => $query->where(fn ($nested) => $nested
                ->where('reference', 'like', "%{$this->search}%")
                ->orWhereHas('tenant', fn ($t) => $t->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('invoice', fn ($i) => $i->where('invoice_number', 'like', "%{$this->search}%"))))
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->methodFilter, fn ($query) => $query->where('method', $this->methodFilter))
            ->when($this->dateFrom, fn ($query) => $query->whereDate('paid_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('paid_at', '<=', $this->dateTo))
            ->latest('paid_at');

        $reportBase = SaasPayment::query();

        return view('livewire.saas-payments', [
            'payments' => $payments->paginate(15),
            'methods' => SaasPayment::query()->select('method')->distinct()->pluck('method'),
            'unpaidInvoices' => SaasInvoice::query()->with('tenant')->whereIn('status', ['pending', 'overdue'])->orderBy('due_date')->limit(50)->get(),
            'report' => [
                'completed' => (clone $reportBase)->where('status', 'completed')->sum('amount'),
                'pending' => (clone $reportBase)->where('status', 'pending')->sum('amount'),
                'failed' => (clone $reportBase)->where('status', 'failed')->count(),
                'refunded' => (float) SaasRefund::sum('amount'),
            ],
        ]);
    }

    private function settleInvoiceIfCovered(SaasInvoice $invoice): void
    {
        $collected = $invoice->payments()->where('status', 'completed')->sum('amount');

        if ($collected < $invoice->amount) {
            return;
        }

        $invoice->update(['status' => 'paid', 'paid_at' => now()]);

        $subscription = $invoice->subscription;
        if (!$subscription) {
            return;
        }

        $stillUnpaid = $subscription->invoices()->whereIn('status', ['pending', 'overdue'])->where('id', '!=', $invoice->id)->exists();

        if (!$stillUnpaid && in_array($subscription->status, ['pending_approval', 'past_due', 'suspended'], true)) {
            $wasPendingApproval = $subscription->status === 'pending_approval';
            $fromStatus = $subscription->status;
            $subscription->update(['status' => 'active']);
            if ($invoice->tenant->status === 'suspended') {
                $invoice->tenant->update(['status' => 'active']);
            }

            TenantSubscriptionEvent::create([
                'tenant_subscription_id' => $subscription->id,
                'user_id' => auth()->id(),
                'event' => $wasPendingApproval ? 'subscription.approved' : 'subscription.reactivated',
                'from_status' => $fromStatus,
                'to_status' => 'active',
                'metadata' => null,
                'created_at' => now(),
            ]);
        }
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
