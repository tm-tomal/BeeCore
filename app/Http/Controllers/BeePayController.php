<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\AuditLog;
use App\Models\BeePaymentIntent;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SaasInvoice;
use App\Models\SaasInvoiceItem;
use App\Models\SaasPayment;
use App\Models\SaasPlan;
use App\Models\SystemSetting;
use App\Models\TenantAddon;
use App\Models\TenantSubscription;
use App\Models\TenantSubscriptionEvent;
use App\Services\BkashGateway;
use App\Services\SaasSubscriptionBilling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BeePayController
{
    /**
     * Public start point for paying an ISP subscriber invoice through the Bee gateway.
     */
    public function invoice(Invoice $invoice)
    {
        $tenant = $invoice->tenant;
        abort_unless($tenant && $tenant->status === 'active' && ! $tenant->archived_at, 404);

        $outstanding = (float) $invoice->outstanding_amount;
        abort_unless($outstanding > 0, 422, 'This invoice has no outstanding balance.');
        abort_unless(in_array($invoice->status, ['pending', 'overdue'], true), 422, 'This invoice is not open for payment.');

        $intent = BeePaymentIntent::findOpen(BeePaymentIntent::KIND_INVOICE, $tenant->id, ['invoice_id' => $invoice->id])
            ?? BeePaymentIntent::createFor(
                BeePaymentIntent::KIND_INVOICE,
                $tenant->id,
                $outstanding,
                ['invoice_id' => $invoice->id],
            );

        return redirect()->route('bee-pay.intent', ['intent' => $intent->token]);
    }

    /**
     * Pay an existing BeeCore (SaaS) invoice — subscription plan or add-on —
     * through the hosted bKash flow. Reached from the tenant's subscription page.
     */
    public function saasInvoice(SaasInvoice $saasInvoice)
    {
        $tenant = $saasInvoice->tenant;
        abort_unless($tenant && $tenant->status === 'active' && ! $tenant->archived_at, 404);
        abort_unless(in_array($saasInvoice->status, ['pending', 'overdue'], true), 422, 'This invoice is not open for payment.');

        $covered = (float) $saasInvoice->payments()->where('status', 'completed')->sum('amount');
        $outstanding = max(0, (float) $saasInvoice->amount - $covered);
        abort_unless($outstanding > 0, 422, 'This invoice is already paid.');

        $meta = ['saas_invoice_id' => $saasInvoice->id];
        if ($saasInvoice->tenant_addon_id) {
            $meta['tenant_addon_id'] = $saasInvoice->tenant_addon_id;
        }

        $kind = $saasInvoice->tenant_addon_id ? BeePaymentIntent::KIND_SAAS_ADDON : BeePaymentIntent::KIND_SAAS_PLAN;

        $intent = BeePaymentIntent::findOpen($kind, $tenant->id, ['saas_invoice_id' => $saasInvoice->id])
            ?? BeePaymentIntent::createFor($kind, $tenant->id, $outstanding, $meta);

        return redirect()->route('bee-pay.intent', ['intent' => $intent->token]);
    }

    public function show(string $token, Request $request)
    {
        $intent = BeePaymentIntent::query()->where('token', $token)->with('tenant')->firstOrFail();
        $tenant = $intent->tenant;

        if ($tenant && ($tenant->status !== 'active' || $tenant->archived_at)) {
            abort(404);
        }

        return view('bee-pay.page', [
            'intent' => $intent,
            'fee' => SystemSetting::beeFeePercent(),
            'error' => $request->session()->pull('bee_pay_error'),
        ]);
    }

    public function initiate(BeePaymentIntent $intent, Request $request)
    {
        abort_unless($intent->status !== BeePaymentIntent::STATUS_SUCCESS, 422, 'This payment was already completed.');

        $gateway = BkashGateway::resolve();

        try {
            $callbackUrl = config('services.beecore.callback_url') ?: route('bee-pay.callback');

            $result = BkashGateway::createPayment(
                $gateway,
                (float) $intent->amount,
                $intent->merchant_invoice_number,
                $callbackUrl,
            );

            $intent->update([
                'status' => BeePaymentIntent::STATUS_PROCESSING,
                'bkash_payment_id' => $result['paymentID'],
            ]);

            return redirect()->away($result['bkashURL']);
        } catch (\Throwable $e) {
            $request->session()->flash('bee_pay_error', $e->getMessage());

            return redirect()->route('bee-pay.intent', ['intent' => $intent->token]);
        }
    }

    public function callback(Request $request)
    {
        $paymentId = (string) $request->input('paymentID', '');
        $callbackStatus = strtolower((string) $request->input('status', ''));

        if ($paymentId === '') {
            return $this->resultView(false, 'We did not receive a payment reference from bKash.');
        }

        $intent = BeePaymentIntent::query()->where('bkash_payment_id', $paymentId)->first();

        if (! $intent) {
            return $this->resultView(false, 'We could not match this bKash payment to an order. Please contact the merchant.');
        }

        if ($intent->isSettled()) {
            // bKash sometimes re-delivers the callback; never settle twice.
            return $this->resultView(true, 'Payment successful. Thank you!', $intent->merchant_invoice_number, $intent->bkash_trx_id);
        }

        // Failed/cancelled on the bKash side: nothing was charged. Re-arm the intent.
        if ($callbackStatus !== '' && $callbackStatus !== 'success') {
            $intent->resetForRetry();

            return $this->resultView(false, 'The payment was not completed. You can try again.', $intent->merchant_invoice_number, null, $intent->token);
        }

        try {
            $gateway = BkashGateway::resolve();
            $result = BkashGateway::executePayment($gateway, $paymentId);

            if (($result['statusCode'] ?? '') !== '0000' || ! $result['trxID']) {
                // The customer says they paid but execute did not confirm. Query
                // bKash to find the real state before declaring failure.
                $query = BkashGateway::queryPayment($gateway, $paymentId);
                if (($query['transactionStatus'] ?? '') === 'Completed' && $query['trxID']) {
                    return $this->settleAndReturn($intent, $query['trxID']);
                }

                $intent->resetForRetry();

                return $this->resultView(false, $result['statusMessage'] ?? 'Payment was not completed. You can try again.', $intent->merchant_invoice_number, null, $intent->token);
            }

            return $this->settleAndReturn($intent, $result['trxID']);
        } catch (\Throwable $e) {
            // execute() may fail without an answer (network/timeout). Ask bKash
            // what actually happened to the payment before letting the customer
            // retry — otherwise a completed payment could be charged twice.
            try {
                $gateway = BkashGateway::resolve();
                $query = BkashGateway::queryPayment($gateway, $paymentId);
                if (($query['transactionStatus'] ?? '') === 'Completed' && $query['trxID']) {
                    return $this->settleAndReturn($intent, $query['trxID']);
                }
            } catch (\Throwable $reconcileError) {
                // Fall through: reconciliation also failed, keep processing state so
                // the customer can check status from the payment page later.
                $intent->resetForRetry();

                return $this->resultView(false, $e->getMessage(), $intent->merchant_invoice_number, null, $intent->token);
            }

            $intent->resetForRetry();

            return $this->resultView(false, $e->getMessage(), $intent->merchant_invoice_number, null, $intent->token);
        }
    }

    /**
     * Reconciliation endpoint used when a bKash session stays "processing" —
     * e.g. the customer paid but was never redirected back.
     */
    public function check(BeePaymentIntent $intent, Request $request)
    {
        if ($intent->isSettled()) {
            return $this->resultView(true, 'Payment successful. Thank you!', $intent->merchant_invoice_number, $intent->bkash_trx_id);
        }

        if (! $intent->bkash_payment_id) {
            $request->session()->flash('bee_pay_error', 'No bKash session has been started for this payment.');

            return redirect()->route('bee-pay.intent', ['intent' => $intent->token]);
        }

        try {
            $gateway = BkashGateway::resolve();
            $query = BkashGateway::queryPayment($gateway, $intent->bkash_payment_id);

            if (($query['transactionStatus'] ?? '') === 'Completed' && $query['trxID']) {
                return $this->settleAndReturn($intent, $query['trxID']);
            }

            $request->session()->flash('bee_pay_error', 'bKash reports this payment as "'.($query['transactionStatus'] ?? 'not found').'". If you just paid, wait a few seconds and check again.');

            return redirect()->route('bee-pay.intent', ['intent' => $intent->token]);
        } catch (\Throwable $e) {
            $request->session()->flash('bee_pay_error', $e->getMessage());

            return redirect()->route('bee-pay.intent', ['intent' => $intent->token]);
        }
    }

    private function settleAndReturn(BeePaymentIntent $intent, string $trxID)
    {
        DB::transaction(function () use ($intent, $trxID) {
            $intent->update([
                'status' => BeePaymentIntent::STATUS_SUCCESS,
                'bkash_trx_id' => $trxID,
                'callback_at' => now(),
            ]);

            $this->settle($intent, $trxID);
        });

        return $this->resultView(true, 'Payment successful. Thank you!', $intent->merchant_invoice_number, $trxID);
    }

    private function resultView(bool $ok, string $message, ?string $reference = null, ?string $trxID = null, ?string $retryToken = null)
    {
        return view('bee-pay.result', [
            'ok' => $ok,
            'message' => $message,
            'reference' => $reference,
            'trxID' => $trxID,
            'retryUrl' => $retryToken ? route('bee-pay.intent', ['intent' => $retryToken]) : null,
        ]);
    }

    private function settle(BeePaymentIntent $intent, string $trxID): void
    {
        if ($intent->kind === BeePaymentIntent::KIND_INVOICE) {
            $this->settleCustomerInvoice($intent, $trxID);

            return;
        }

        $this->settleSaas($intent, $intent->meta ?? [], $trxID);
    }

    private function settleCustomerInvoice(BeePaymentIntent $intent, string $trxID): void
    {
        $invoice = Invoice::query()->where('tenant_id', $intent->tenant_id)->lockForUpdate()->findOrFail($intent->meta['invoice_id'] ?? 0);

        if (! in_array($invoice->status, ['pending', 'overdue'], true)) {
            return;
        }

        $outstanding = (float) $invoice->outstanding_amount;
        $charge = min($outstanding, (float) $intent->amount);
        if ($charge <= 0) {
            return;
        }

        Payment::create([
            'tenant_id' => $intent->tenant_id,
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'amount' => $charge,
            'payment_method' => 'bkash',
            'transaction_id' => $trxID,
            'payment_date' => now(),
            'status' => 'successful',
        ]);

        $remaining = $outstanding - $charge;
        if ($remaining <= 0) {
            $invoice->update(['status' => 'paid']);
        } elseif ($invoice->due_date && $invoice->due_date->lt(now()->startOfDay())) {
            $invoice->update(['status' => 'overdue']);
        } else {
            $invoice->update(['status' => 'pending']);
        }
    }

    private function settleSaas(BeePaymentIntent $intent, array $meta, string $trxID): void
    {
        // Online bKash checkout orders carry no pre-created rows — the order
        // rides inside the intent and is materialised only once bKash confirms
        // the payment, so a failed attempt leaves no records behind.
        if (! empty($meta['deferred'])) {
            if ($intent->kind === BeePaymentIntent::KIND_SAAS_ADDON) {
                $this->materializeDeferredAddonOrder($intent, $trxID);
            } else {
                $this->materializeDeferredSubscriptionOrder($intent, $trxID);
            }

            return;
        }

        // Legacy / “Pay now” invoices that already exist as pending rows.
        $invoice = SaasInvoice::query()
            ->where('tenant_id', $intent->tenant_id)
            ->lockForUpdate()
            ->findOrFail($meta['saas_invoice_id'] ?? 0);

        $paid = $this->settleBkashInvoice($invoice, $trxID);

        if (isset($meta['tenant_addon_id']) && $paid) {
            $addon = TenantAddon::query()->where('tenant_id', $intent->tenant_id)->find($meta['tenant_addon_id']);
            if ($addon && $addon->status === 'pending_approval') {
                $addon->update(['status' => 'active']);
                \App\Services\SmsGateway::creditSmsAddon($addon);
            }
        }

        $subscription = $invoice->subscription;

        if ($paid && $subscription && in_array($subscription->status, ['pending_approval', 'past_due', 'suspended'], true)) {
            $fromStatus = $subscription->status;
            $subscription->update(['status' => 'active']);
            if ($subscription->tenant && $subscription->tenant->status === 'suspended') {
                $subscription->tenant->update(['status' => 'active']);
            }

            TenantSubscriptionEvent::create([
                'tenant_subscription_id' => $subscription->id,
                'user_id' => null,
                'event' => $fromStatus === 'pending_approval' ? 'subscription.approved' : 'subscription.reactivated',
                'from_status' => $fromStatus,
                'to_status' => 'active',
                'metadata' => ['source' => 'bkash_callback', 'trx_id' => $trxID],
                'created_at' => now(),
            ]);
        }
    }

    /**
     * Materialise a deferred plan order once bKash confirms the payment:
     * activates (or changes) the subscription and records a paid invoice.
     */
    private function materializeDeferredSubscriptionOrder(BeePaymentIntent $intent, string $trxID): void
    {
        $meta = $intent->meta ?? [];
        $plan = SaasPlan::find((int) ($meta['saas_plan_id'] ?? 0));

        if (! $plan) {
            throw new \RuntimeException('The BeeCore plan on this order no longer exists.');
        }

        $tenantId = $intent->tenant_id;
        $cycle = ($meta['billing_cycle'] ?? 'monthly') === 'yearly' ? 'yearly' : 'monthly';
        $price = (float) $intent->amount;

        $subscription = TenantSubscription::query()
            ->where('tenant_id', $tenantId)
            ->latest('id')
            ->lockForUpdate()
            ->first();

        $create = ! $subscription || $subscription->status === 'cancelled';

        if ($create) {
            // Fresh subscription, activated immediately.
            $starts = today();
            $periodEnd = $cycle === 'yearly'
                ? $starts->copy()->addYear()->subDay()
                : $starts->copy()->addMonth()->subDay();

            $subscription = TenantSubscription::create([
                'tenant_id' => $tenantId,
                'saas_plan_id' => $plan->id,
                'status' => 'active',
                'billing_cycle' => $cycle,
                'price' => $price,
                'starts_at' => $starts,
                'current_period_ends_at' => $periodEnd,
                'grace_ends_at' => $periodEnd->copy()->addDays($plan->grace_days),
                'auto_renew' => true,
            ]);

            $fromStatus = null;
            $planChanged = false;
            $periodStart = $starts;
        } else {
            // Paid against the current subscription: plan change or reactivation.
            $fromStatus = $subscription->status;
            $planChanged = $subscription->saas_plan_id !== $plan->id
                || $subscription->billing_cycle !== $cycle
                || (float) $subscription->price !== $price;

            $periodStart = $subscription->current_period_ends_at && $subscription->current_period_ends_at->isFuture()
                ? $subscription->current_period_ends_at->copy()->addDay()
                : today();

            $subscription->update([
                'saas_plan_id' => $plan->id,
                'billing_cycle' => $cycle,
                'price' => $price,
                'status' => 'active',
                'auto_renew' => true,
            ]);
        }

        if ($subscription->tenant && $subscription->tenant->status === 'suspended') {
            $subscription->tenant->update(['status' => 'active']);
        }

        $periodEnd = $cycle === 'yearly'
            ? $periodStart->copy()->addYear()->subDay()
            : $periodStart->copy()->addMonth()->subDay();

        $dueDate = $periodStart->copy()->addDays(max((int) $plan->grace_days, 7));
        $invoice = (new SaasSubscriptionBilling())->createInvoiceForPeriod($subscription, $periodStart, $periodEnd, $dueDate);

        $this->recordBkashPayment($invoice, $trxID);
        $this->settleBkashInvoice($invoice, $trxID);

        $this->recordSubscriptionEvent($subscription, $trxID, $create, $fromStatus, $planChanged, $plan->id, $cycle, $price);

        AuditLog::record(
            $create ? 'tenant.subscription.created' : ($planChanged ? 'tenant.subscription.plan_changed' : 'tenant.subscription.updated'),
            $subscription,
            ['source' => 'bkash_callback', 'trx_id' => $trxID, 'plan_id' => $plan->id, 'billing_cycle' => $cycle],
            tenantId: $tenantId,
        );
    }

    /**
     * Materialise a deferred add-on order once bKash confirms the payment.
     */
    private function materializeDeferredAddonOrder(BeePaymentIntent $intent, string $trxID): void
    {
        $meta = $intent->meta ?? [];
        $addonProduct = Addon::find((int) ($meta['addon_id'] ?? 0));

        if (! $addonProduct) {
            throw new \RuntimeException('The add-on on this order no longer exists.');
        }

        $tenantId = $intent->tenant_id;
        $cycle = $addonProduct->billing_cycle;
        $recurring = in_array($cycle, ['monthly', 'yearly'], true);
        $amount = (float) $intent->amount;
        $start = today();
        $periodEnd = $cycle === 'yearly'
            ? $start->copy()->addYear()->subDay()
            : ($cycle === 'monthly' ? $start->copy()->addMonth()->subDay() : null);

        // The add-on invoice must hang off the workspace's base subscription
        // (column is NOT NULL); prefer the latest live one, else any latest row.
        $subscription = TenantSubscription::query()
            ->where('tenant_id', $tenantId)
            ->where('status', '!=', 'cancelled')
            ->latest('id')
            ->first();

        if (! $subscription) {
            $subscription = TenantSubscription::query()
                ->where('tenant_id', $tenantId)
                ->latest('id')
                ->first();
        }

        $row = TenantAddon::create([
            'tenant_id' => $tenantId,
            'addon_id' => $addonProduct->id,
            'status' => 'active',
            'price' => (float) $addonProduct->price,
            'billing_cycle' => $cycle,
            'assigned_by' => null,
            'starts_at' => now(),
            'period_start' => $start,
            'period_end' => $periodEnd,
            'auto_renew' => $recurring,
        ]);

        \App\Services\SmsGateway::creditSmsAddon($row);

        $invoice = SaasInvoice::create([
            'tenant_id' => $tenantId,
            'tenant_subscription_id' => $subscription?->id,
            'tenant_addon_id' => $row->id,
            'invoice_number' => SaasInvoice::draftNumber(),
            'status' => 'pending',
            'period_start' => $start->toDateString(),
            'period_end' => ($periodEnd ?? $start)->toDateString(),
            'amount' => $amount,
            'due_date' => $start->toDateString(),
        ]);
        $invoice->setSequentialNumber();

        SaasInvoiceItem::create([
            'saas_invoice_id' => $invoice->id,
            'type' => 'charge',
            'description' => $addonProduct->name.' add-on ('.$cycle.')',
            'amount' => $amount,
            'created_by' => null,
            'created_at' => now(),
        ]);

        $this->recordBkashPayment($invoice, $trxID);
        $this->settleBkashInvoice($invoice, $trxID);

        AuditLog::record('addon.purchased', $row, [
            'addon_id' => $addonProduct->id,
            'amount' => $amount,
            'cycle' => $cycle,
            'source' => 'bkash_callback',
            'trx_id' => $trxID,
        ], tenantId: $tenantId);
    }

    /**
     * Create the completed bKash payment row for a SaaS invoice and settle it.
     */
    private function recordBkashPayment(SaasInvoice $invoice, string $trxID): void
    {
        SaasPayment::create([
            'tenant_id' => $invoice->tenant_id,
            'saas_invoice_id' => $invoice->id,
            'recorded_by' => null,
            'amount' => (float) $invoice->amount,
            'method' => 'bkash',
            'reference' => 'bKash '.$trxID,
            'status' => 'pending',
            'paid_at' => now(),
        ]);
    }

    /**
     * Complete pending bKash payments on an invoice and mark it paid when the
     * collected amount covers the invoice.
     *
     * @return bool whether the invoice is now fully covered
     */
    private function settleBkashInvoice(SaasInvoice $invoice, string $trxID): bool
    {
        SaasPayment::query()
            ->where('saas_invoice_id', $invoice->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'completed',
                'method' => 'bkash',
                'reference' => 'bKash '.$trxID,
                'verified_at' => now(),
                'verified_by' => null,
            ]);

        $covered = (float) $invoice->payments()->where('status', 'completed')->sum('amount');
        $paid = $covered >= (float) $invoice->amount;

        if ($paid && $invoice->status !== 'paid') {
            $invoice->update(['status' => 'paid', 'paid_at' => now()]);
        } elseif (! $paid && $invoice->status === 'paid') {
            // A partial online payment against an already “paid” invoice.
            $invoice->update(['status' => 'pending']);
        }

        return $paid;
    }

    private function recordSubscriptionEvent(TenantSubscription $subscription, string $trxID, bool $create, ?string $fromStatus, bool $planChanged, int $planId, string $cycle, float $price): void
    {
        if ($create) {
            $event = 'subscription.created';
        } elseif ($planChanged) {
            $event = 'subscription.plan_changed';
        } elseif ($fromStatus === 'pending_approval') {
            $event = 'subscription.approved';
        } elseif (in_array($fromStatus, ['past_due', 'suspended'], true)) {
            $event = 'subscription.reactivated';
        } else {
            $event = 'subscription.renewed';
        }

        TenantSubscriptionEvent::create([
            'tenant_subscription_id' => $subscription->id,
            'user_id' => null,
            'event' => $event,
            'from_status' => $fromStatus,
            'to_status' => 'active',
            'metadata' => [
                'source' => 'bkash_callback',
                'trx_id' => $trxID,
                'plan_id' => $planId,
                'billing_cycle' => $cycle,
                'price' => $price,
            ],
            'created_at' => now(),
        ]);
    }
}
