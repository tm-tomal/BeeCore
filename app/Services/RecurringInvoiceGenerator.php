<?php

namespace App\Services;

use App\Models\CustomerSubscription;
use App\Models\Invoice;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class RecurringInvoiceGenerator
{
    public function generateDue(?CarbonInterface $through = null): int
    {
        $through ??= today();

        return $this->generateFromQuery(
            CustomerSubscription::query()
                ->where('status', 'active')
                ->whereDate('next_billing_date', '<=', $through)
                ->whereHas('customer', fn ($query) => $query->where('status', 'active')),
            $through
        );
    }

    public function generateDueForTenant(int $tenantId, ?CarbonInterface $through = null): int
    {
        $through ??= today();

        return $this->generateFromQuery(
            CustomerSubscription::query()
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->whereDate('next_billing_date', '<=', $through)
                ->whereHas('customer', fn ($query) => $query->where('tenant_id', $tenantId)->where('status', 'active')),
            $through
        );
    }

    private function generateFromQuery($query, CarbonInterface $through): int
    {
        $generated = 0;

        $query->orderBy('id')
            ->pluck('id')
            ->each(function (int $subscriptionId) use ($through, &$generated) {
                for ($period = 0; $period < 120; $period++) {
                    $subscription = CustomerSubscription::query()->with('customer')->find($subscriptionId);

                    if (!$subscription
                        || $subscription->status !== 'active'
                        || $subscription->customer->status !== 'active'
                        || $subscription->next_billing_date->isAfter($through)) {
                        break;
                    }

                    $generated += $this->generateForSubscription($subscriptionId, $through) ? 1 : 0;
                }
            });

        return $generated;
    }

    public function generateForSubscription(int $subscriptionId, CarbonInterface $through): ?Invoice
    {
        return DB::transaction(function () use ($subscriptionId, $through) {
            $subscription = CustomerSubscription::query()
                ->with(['customer', 'tenant'])
                ->lockForUpdate()
                ->findOrFail($subscriptionId);

            if ($subscription->status !== 'active'
                || $subscription->customer->status !== 'active'
                || $subscription->next_billing_date->isAfter($through)) {
                return null;
            }

            $periodStart = $subscription->next_billing_date->copy();
            $subtotal = round((float) $subscription->price, 2);
            $tax = round($subtotal * (float) $subscription->tax_rate / 100, 2);
            $graceDays = (int) ($subscription->tenant?->billingSetting('grace_days', 7) ?? 7);

            $invoice = Invoice::firstOrCreate([
                'subscription_id' => $subscription->id,
                'billing_period_start' => $periodStart->toDateString(),
            ], [
                'tenant_id' => $subscription->tenant_id,
                'customer_id' => $subscription->customer_id,
                'invoice_number' => sprintf('INV-%s-S%06d', $periodStart->format('Ymd'), $subscription->id),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'total' => $subtotal + $tax,
                'due_date' => $periodStart->copy()->addDays($graceDays),
            ]);

            if ($invoice->wasRecentlyCreated) {
                $invoice->items()->create([
                    'tenant_id' => $subscription->tenant_id,
                    'description' => $subscription->package_name.' - '.$this->cycleLabel($subscription->billing_cycle),
                    'quantity' => 1,
                    'unit_price' => $subtotal,
                    'amount' => $subtotal,
                ]);
            }

            $subscription->update([
                'next_billing_date' => $this->nextDate($periodStart, $subscription->billing_cycle),
            ]);

            return $invoice->wasRecentlyCreated ? $invoice : null;
        });
    }

    private function nextDate(CarbonInterface $date, string $cycle): CarbonInterface
    {
        return match ($cycle) {
            'quarterly' => $date->copy()->addMonthsNoOverflow(3),
            'semiannual' => $date->copy()->addMonthsNoOverflow(6),
            'yearly' => $date->copy()->addYearNoOverflow(),
            default => $date->copy()->addMonthNoOverflow(),
        };
    }

    private function cycleLabel(string $cycle): string
    {
        return match ($cycle) {
            'quarterly' => 'Quarterly service',
            'semiannual' => 'Half-yearly service',
            'yearly' => 'Yearly service',
            default => 'Monthly service',
        };
    }
}