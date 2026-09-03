<?php

namespace App\Http\Controllers;

use App\Models\BeePaymentIntent;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SaasInvoice;
use App\Models\SaasPayment;
use App\Models\SystemSetting;
use App\Models\TenantAddon;
use App\Models\TenantSubscriptionEvent;
use App\Services\BkashGateway;
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
        $invoice = SaasInvoice::query()->where('tenant_id', $intent->tenant_id)->lockForUpdate()->findOrFail($meta['saas_invoice_id'] ?? 0);

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
        } elseif (! $paid && in_array($invoice->status, ['paid'], true)) {
            // Partial bKash payment: keep the invoice open.
            $invoice->update(['status' => 'pending']);
        }

        $subscription = $invoice->subscription;

        if (isset($meta['tenant_addon_id']) && $paid) {
            $addon = TenantAddon::query()->where('tenant_id', $intent->tenant_id)->find($meta['tenant_addon_id']);
            if ($addon && $addon->status === 'pending_approval') {
                $addon->update(['status' => 'active']);
            }
        }

        if ($paid && $subscription && in_array($subscription->status, ['pending_approval', 'past_due', 'suspended'], true)) {
            $fromStatus = $subscription->status;
            $subscription->update(['status' => 'active']);
            if ($subscription->tenant && $subscription->tenant->status === 'suspended') {
                $subscription->tenant->update(['status' => 'active']);
            }

            TenantSubscriptionEvent::create([
                'tenant_subscription_id' => $subscription->id,
                'user_id' => null,
                'event' => 'subscription.approved',
                'from_status' => $fromStatus,
                'to_status' => 'active',
                'metadata' => ['source' => 'bkash_callback', 'trx_id' => $trxID],
                'created_at' => now(),
            ]);
        }
    }
}
