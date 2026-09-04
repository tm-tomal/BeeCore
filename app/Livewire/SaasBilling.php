<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\SaasInvoice;
use App\Models\SaasInvoiceItem;
use App\Models\SaasPayment;
use App\Models\SaasRefund;
use App\Models\TenantSubscriptionEvent;
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

    public ?int $recordForInvoiceId = null;
    public float $recordAmount = 0;
    public string $recordMethod = 'manual';
    public string $recordReference = '';
    public bool $recordAsPending = false;

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

    /**
     * Permanently remove an invoice together with its attached payments/refunds.
     * Intended for cleaning up mistaken or test orders — audit log keeps a snapshot.
     */
    public function deleteInvoice(int $id): void
    {
        $this->assertSuperAdmin();

        DB::transaction(function () use ($id) {
            $invoice = SaasInvoice::query()->lockForUpdate()->findOrFail($id);

            AuditLog::record('saas.invoice.deleted', $invoice, [
                'invoice_number' => $invoice->invoice_number,
                'amount' => $invoice->amount,
                'status' => $invoice->status,
                'subscription_id' => $invoice->tenant_subscription_id,
                'tenant_addon_id' => $invoice->tenant_addon_id,
            ], tenantId: $invoice->tenant_id);

            $invoice->delete();
            $this->detailInvoiceId = null;
        });

        session()->flash('message', 'Invoice permanently deleted.');
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

    /**
     * Admin correction — permanently remove a wrongly recorded SaaS payment.
     * The invoice status is recomputed so it reopens if the payment was the
     * only one covering it. Payments that already have refunds are kept for audit.
     */
    public function deletePayment(int $paymentId): void
    {
        $this->assertSuperAdmin();

        DB::transaction(function () use ($paymentId) {
            $payment = SaasPayment::query()->with('invoice')->lockForUpdate()->findOrFail($paymentId);

            abort_if($payment->refunds()->exists(), 422, 'This payment has refunds attached. Delete the refunds first or keep the payment for the audit trail.');

            $invoice = $payment->invoice;

            $payment->delete();

            AuditLog::record('saas.payment.deleted', $payment, [
                'amount' => $payment->amount,
                'method' => $payment->method,
                'status' => $payment->status,
            ], tenantId: $payment->tenant_id);

            if (! $invoice) {
                return;
            }

            $invoice = SaasInvoice::query()->lockForUpdate()->find($invoice->id);

            if (! $invoice) {
                return;
            }

            $covered = (float) $invoice->payments()->where('status', 'completed')->sum('amount');
            $total = (float) $invoice->amount;

            if ($covered >= $total) {
                $invoice->update(['status' => 'paid', 'paid_at' => $invoice->paid_at ?? now()]);
            } elseif (in_array($invoice->status, ['paid', 'refunded'], true)) {
                $dueOpen = $invoice->due_date && $invoice->due_date->lt(now()->startOfDay());

                $invoice->update(['status' => $dueOpen ? 'overdue' : 'pending', 'paid_at' => null]);
            }
        });

        session()->flash('message', 'Payment deleted and its invoice status was updated.');
    }

    public function openRecordPayment(int $invoiceId): void
    {
        $invoice = SaasInvoice::findOrFail($invoiceId);
        $alreadyPaid = $invoice->payments()->where('status', 'completed')->sum('amount');

        $this->recordForInvoiceId = $invoiceId;
        $this->recordAmount = max(0, (float) $invoice->amount - (float) $alreadyPaid);
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

            if (! $this->recordAsPending) {
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

    /**
     * Mark a pending payment as received (bank transfer, cash, on-hold bKash…) and
     * settle the invoice — activates the plan / add-on when everything is covered.
     */
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

            // Close a pending-approval subscription order when no other pending payment remains.
            $subscription = $payment->invoice?->subscription;
            if ($subscription && $subscription->status === 'pending_approval') {
                $otherPending = SaasPayment::query()
                    ->whereIn('saas_invoice_id', $subscription->invoices()->pluck('id'))
                    ->where('status', 'pending')
                    ->where('id', '!=', $payment->id)
                    ->exists();

                if (! $otherPending) {
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
            }

            // Close a pending add-on order when its payment fails.
            $addon = $payment->invoice?->addon;
            if ($addon && $addon->status === 'pending_approval') {
                $otherPendingAddon = SaasPayment::query()
                    ->whereIn('saas_invoice_id', SaasInvoice::query()->where('tenant_addon_id', $addon->id)->pluck('id'))
                    ->where('status', 'pending')
                    ->where('id', '!=', $payment->id)
                    ->exists();

                if (! $otherPendingAddon) {
                    $addon->update(['status' => 'cancelled', 'cancelled_at' => now(), 'auto_renew' => false]);
                    AuditLog::record('addon.payment_failed', $addon, ['addon_id' => $addon->addon_id], tenantId: $addon->tenant_id);
                }
            }
        });

        session()->flash('message', 'Payment marked failed.');
    }

    private function settleInvoiceIfCovered(SaasInvoice $invoice): void
    {
        $collected = $invoice->payments()->where('status', 'completed')->sum('amount');

        if ($collected < $invoice->amount) {
            return;
        }

        $invoice->update(['status' => 'paid', 'paid_at' => now()]);

        $subscription = $invoice->subscription;
        if ($subscription) {
            $stillUnpaid = $subscription->invoices()->whereIn('status', ['pending', 'overdue'])->where('id', '!=', $invoice->id)->exists();

            if (! $stillUnpaid && in_array($subscription->status, ['pending_approval', 'past_due', 'suspended'], true)) {
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

        $addon = $invoice->addon;
        if ($addon && $addon->status === 'pending_approval') {
            $addon->update(['status' => 'active']);
            AuditLog::record('addon.request_approved', $addon, ['addon_id' => $addon->addon_id, 'amount' => $addon->price], tenantId: $invoice->tenant_id);
            \App\Services\SmsGateway::creditSmsAddon($addon);
        }
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
        $this->recordForInvoiceId = null;
    }

    public function render()
    {
        $this->assertSuperAdmin();

        $baseQuery = SaasInvoice::query();

        return view('livewire.saas-billing', [
            'invoices' => SaasInvoice::query()
                ->with(['tenant', 'subscription.plan', 'addon.addon'])
                ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
                ->when($this->search !== '', fn ($query) => $query->where(fn ($q) => $q
                    ->where('invoice_number', 'like', '%'.$this->search.'%')
                    ->orWhereHas('tenant', fn ($t) => $t->where('name', 'like', '%'.$this->search.'%'))))
                ->latest('due_date')
                ->paginate(15),
            'detailInvoice' => $this->detailInvoiceId
                ? SaasInvoice::with(['tenant', 'subscription.plan', 'addon', 'items.creator', 'payments.refunds', 'payments.verifiedBy'])->find($this->detailInvoiceId)
                : null,
            'refundingPayment' => $this->refundForPaymentId
                ? SaasPayment::with('refunds')->find($this->refundForPaymentId)
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
