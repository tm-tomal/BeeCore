<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentAllocator
{
    public function allocate(
        int $tenantId,
        int $invoiceId,
        float $amount,
        string $method,
        ?string $transactionId = null,
    ): Payment {
        return DB::transaction(function () use ($tenantId, $invoiceId, $amount, $method, $transactionId) {
            $invoice = Invoice::query()
                ->where('tenant_id', $tenantId)
                ->lockForUpdate()
                ->findOrFail($invoiceId);

            if (in_array($invoice->status, ['draft', 'cancelled', 'paid'], true)) {
                throw ValidationException::withMessages([
                    'invoice_id' => 'Payments can only be allocated to pending or overdue invoices.',
                ]);
            }

            $paid = (float) $invoice->payments()->where('status', 'successful')->sum('amount');
            $outstanding = round(max(0, (float) $invoice->total - $paid), 2);
            $amount = round($amount, 2);

            if ($amount <= 0 || $amount > $outstanding) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment must be greater than zero and cannot exceed the outstanding balance.',
                ]);
            }

            if ($transactionId && Payment::query()
                ->where('tenant_id', $tenantId)
                ->where('payment_method', $method)
                ->where('transaction_id', $transactionId)
                ->exists()) {
                throw ValidationException::withMessages([
                    'transaction_id' => 'This gateway transaction has already been recorded.',
                ]);
            }

            $payment = Payment::create([
                'tenant_id' => $tenantId,
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'amount' => $amount,
                'payment_method' => $method,
                'transaction_id' => $transactionId,
                'payment_date' => now(),
                'status' => 'successful',
            ]);

            if ($amount === $outstanding) {
                $invoice->update(['status' => 'paid']);
            }

            return $payment;
        });
    }
}