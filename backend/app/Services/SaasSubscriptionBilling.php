<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\SaasInvoice;
use App\Models\SaasInvoiceItem;
use App\Models\TenantSubscription;
use App\Models\TenantSubscriptionEvent;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class SaasSubscriptionBilling
{
    /**
     * @return array{trials_converted:int, renewed:int, expired:int, invoices_overdue:int, suspended:int}
     */
    public function processDue(?CarbonInterface $through = null): array
    {
        $through ??= today();

        $summary = [
            'trials_converted' => 0,
            'renewed' => 0,
            'expired' => 0,
            'invoices_overdue' => 0,
            'suspended' => 0,
        ];

        TenantSubscription::query()
            ->where('status', 'trialing')
            ->whereNotNull('trial_ends_at')
            ->whereDate('trial_ends_at', '<', $through)
            ->pluck('id')
            ->each(function (int $id) use ($through, &$summary) {
                if ($this->convertTrial($id, $through)) {
                    $summary['trials_converted']++;
                }
            });

        $summary['invoices_overdue'] += $this->markOverdueInvoices($through);

        TenantSubscription::query()
            ->whereIn('status', ['active', 'past_due'])
            ->whereNotNull('grace_ends_at')
            ->whereDate('grace_ends_at', '<', $through)
            ->whereHas('invoices', fn ($query) => $query->whereIn('status', ['pending', 'overdue']))
            ->pluck('id')
            ->each(function (int $id) use (&$summary) {
                if ($this->suspendForNonPayment($id)) {
                    $summary['suspended']++;
                }
            });

        TenantSubscription::query()
            ->where('status', 'active')
            ->whereNotNull('current_period_ends_at')
            ->whereDate('current_period_ends_at', '<', $through)
            ->pluck('id')
            ->each(function (int $id) use ($through, &$summary) {
                $result = $this->renewOrExpire($id, $through);
                if ($result === 'renewed') {
                    $summary['renewed']++;
                } elseif ($result === 'expired') {
                    $summary['expired']++;
                }
            });

        return $summary;
    }

    private function convertTrial(int $subscriptionId, CarbonInterface $through): bool
    {
        return DB::transaction(function () use ($subscriptionId, $through) {
            $subscription = TenantSubscription::query()->with('plan')->lockForUpdate()->find($subscriptionId);

            if (!$subscription || $subscription->status !== 'trialing') {
                return false;
            }

            $subscription->update(['status' => 'active']);

            $this->createInvoiceForPeriod($subscription, $subscription->starts_at, $subscription->current_period_ends_at, $through);

            $this->recordEvent($subscription, 'trial.converted', 'trialing', 'active');
            AuditLog::record('tenant.subscription.trial_converted', $subscription, tenantId: $subscription->tenant_id);

            return true;
        });
    }

    private function renewOrExpire(int $subscriptionId, CarbonInterface $through): ?string
    {
        return DB::transaction(function () use ($subscriptionId, $through) {
            $subscription = TenantSubscription::query()->with('plan')->lockForUpdate()->find($subscriptionId);

            if (!$subscription || $subscription->status !== 'active' || !$subscription->current_period_ends_at || !$subscription->current_period_ends_at->lt($through)) {
                return null;
            }

            $hasUnpaid = $subscription->invoices()->whereIn('status', ['pending', 'overdue'])->exists();

            if ($hasUnpaid) {
                return null;
            }

            if (!$subscription->auto_renew) {
                $subscription->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                $this->recordEvent($subscription, 'subscription.expired', 'active', 'cancelled');
                AuditLog::record('tenant.subscription.expired', $subscription, tenantId: $subscription->tenant_id);

                return 'expired';
            }

            $periodStart = $subscription->current_period_ends_at->copy()->addDay();
            $periodEnd = $subscription->billing_cycle === 'yearly'
                ? $periodStart->copy()->addYear()->subDay()
                : $periodStart->copy()->addMonth()->subDay();

            $subscription->update([
                'current_period_ends_at' => $periodEnd,
                'grace_ends_at' => $periodEnd->copy()->addDays($subscription->plan->grace_days),
            ]);

            $this->createInvoiceForPeriod($subscription, $periodStart, $periodEnd, $periodStart);
            $this->recordEvent($subscription, 'subscription.renewed', 'active', 'active');
            AuditLog::record('tenant.subscription.renewed', $subscription, tenantId: $subscription->tenant_id);

            return 'renewed';
        });
    }

    private function suspendForNonPayment(int $subscriptionId): bool
    {
        return DB::transaction(function () use ($subscriptionId) {
            $subscription = TenantSubscription::query()->with('tenant')->lockForUpdate()->find($subscriptionId);

            if (!$subscription || !in_array($subscription->status, ['active', 'past_due'], true)) {
                return false;
            }

            $fromStatus = $subscription->status;
            $subscription->update(['status' => 'suspended']);
            $subscription->tenant()->update(['status' => 'suspended']);

            $this->recordEvent($subscription, 'subscription.suspended', $fromStatus, 'suspended');
            AuditLog::record('tenant.subscription.suspended', $subscription, tenantId: $subscription->tenant_id);

            return true;
        });
    }

    private function markOverdueInvoices(CarbonInterface $through): int
    {
        $updated = 0;

        SaasInvoice::query()
            ->where('status', 'pending')
            ->whereDate('due_date', '<', $through)
            ->with('subscription')
            ->chunkById(100, function ($invoices) use (&$updated) {
                foreach ($invoices as $invoice) {
                    $invoice->update(['status' => 'overdue']);

                    if ($invoice->subscription && $invoice->subscription->status === 'active') {
                        $invoice->subscription->update(['status' => 'past_due']);
                        $this->recordEvent($invoice->subscription, 'invoice.overdue', 'active', 'past_due');
                    }

                    $updated++;
                }
            });

        return $updated;
    }

    public function createInvoiceForPeriod(TenantSubscription $subscription, $periodStart, $periodEnd, $dueDate): SaasInvoice
    {
        $invoice = SaasInvoice::firstOrCreate([
            'tenant_subscription_id' => $subscription->id,
            'period_start' => $periodStart->toDateString(),
        ], [
            'tenant_id' => $subscription->tenant_id,
            'invoice_number' => sprintf('SAAS-%s-T%04d-S%06d', $periodStart->format('Ymd'), $subscription->tenant_id, $subscription->id),
            'status' => 'pending',
            'period_end' => $periodEnd->toDateString(),
            'amount' => $subscription->price,
            'due_date' => $dueDate->toDateString(),
        ]);

        if ($invoice->wasRecentlyCreated) {
            SaasInvoiceItem::create([
                'saas_invoice_id' => $invoice->id,
                'type' => 'charge',
                'description' => $subscription->plan->name.' subscription ('.$subscription->billing_cycle.')',
                'amount' => $subscription->price,
                'created_at' => now(),
            ]);
        }

        return $invoice;
    }

    private function recordEvent(TenantSubscription $subscription, string $event, ?string $from, ?string $to): void
    {
        TenantSubscriptionEvent::create([
            'tenant_subscription_id' => $subscription->id,
            'user_id' => null,
            'event' => $event,
            'from_status' => $from,
            'to_status' => $to,
            'metadata' => null,
            'created_at' => now(),
        ]);
    }
}
