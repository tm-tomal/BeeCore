<?php

namespace Tests\Feature;

use App\Models\SaasInvoice;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Services\SaasSubscriptionBilling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaasSubscriptionBillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_trial_converts_to_active_and_generates_an_invoice(): void
    {
        $plan = $this->plan();
        $tenant = $this->tenant();
        $subscription = TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'saas_plan_id' => $plan->id,
            'status' => 'trialing',
            'billing_cycle' => 'monthly',
            'price' => 2500,
            'starts_at' => today()->subDays(14),
            'trial_ends_at' => today()->subDay(),
            'current_period_ends_at' => today()->addDays(16),
            'grace_ends_at' => today()->addDays(23),
            'auto_renew' => true,
        ]);

        $summary = app(SaasSubscriptionBilling::class)->processDue();

        $this->assertSame(1, $summary['trials_converted']);
        $this->assertSame('active', $subscription->fresh()->status);
        $this->assertDatabaseHas('saas_invoices', [
            'tenant_subscription_id' => $subscription->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('tenant_subscription_events', [
            'tenant_subscription_id' => $subscription->id,
            'event' => 'trial.converted',
        ]);
    }

    public function test_active_subscription_renews_when_paid_and_expires_when_not_auto_renewing(): void
    {
        $plan = $this->plan();

        $renewing = TenantSubscription::create([
            'tenant_id' => $this->tenant('renew-isp')->id,
            'saas_plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'price' => 2500,
            'starts_at' => today()->subMonth(),
            'current_period_ends_at' => today()->subDay(),
            'grace_ends_at' => today()->addDays(6),
            'auto_renew' => true,
        ]);

        $nonRenewing = TenantSubscription::create([
            'tenant_id' => $this->tenant('expire-isp')->id,
            'saas_plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'price' => 2500,
            'starts_at' => today()->subMonth(),
            'current_period_ends_at' => today()->subDay(),
            'grace_ends_at' => today()->addDays(6),
            'auto_renew' => false,
        ]);

        $summary = app(SaasSubscriptionBilling::class)->processDue();

        $this->assertSame(1, $summary['renewed']);
        $this->assertSame(1, $summary['expired']);
        $this->assertSame('active', $renewing->fresh()->status);
        $this->assertTrue($renewing->fresh()->current_period_ends_at->isFuture());
        $this->assertDatabaseHas('saas_invoices', ['tenant_subscription_id' => $renewing->id]);
        $this->assertSame('cancelled', $nonRenewing->fresh()->status);
        $this->assertNotNull($nonRenewing->fresh()->cancelled_at);
    }

    public function test_unpaid_invoice_becomes_overdue_and_subscription_is_suspended_after_grace(): void
    {
        $plan = $this->plan();
        $tenant = $this->tenant();
        $subscription = TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'saas_plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'price' => 2500,
            'starts_at' => today()->subMonths(2),
            'current_period_ends_at' => today()->addDays(10),
            'grace_ends_at' => today()->subDay(),
            'auto_renew' => true,
        ]);

        SaasInvoice::create([
            'tenant_id' => $tenant->id,
            'tenant_subscription_id' => $subscription->id,
            'invoice_number' => 'SAAS-TEST-0001',
            'status' => 'pending',
            'period_start' => today()->subMonth(),
            'period_end' => today()->subDay(),
            'amount' => 2500,
            'due_date' => today()->subDays(3),
        ]);

        $summary = app(SaasSubscriptionBilling::class)->processDue();

        $this->assertSame(1, $summary['invoices_overdue']);
        $this->assertSame(1, $summary['suspended']);
        $this->assertSame('suspended', $subscription->fresh()->status);
        $this->assertSame('suspended', $tenant->fresh()->status);
        $this->assertDatabaseHas('saas_invoices', ['tenant_subscription_id' => $subscription->id, 'status' => 'overdue']);
    }

    public function test_processing_twice_for_the_same_day_is_idempotent(): void
    {
        $plan = $this->plan();
        $tenant = $this->tenant();
        $subscription = TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'saas_plan_id' => $plan->id,
            'status' => 'trialing',
            'billing_cycle' => 'monthly',
            'price' => 2500,
            'starts_at' => today()->subDays(14),
            'trial_ends_at' => today()->subDay(),
            'current_period_ends_at' => today()->addDays(16),
            'grace_ends_at' => today()->addDays(23),
            'auto_renew' => true,
        ]);

        $billing = app(SaasSubscriptionBilling::class);
        $billing->processDue();
        $billing->processDue();

        $this->assertSame(1, SaasInvoice::query()->where('tenant_subscription_id', $subscription->id)->count());
    }

    public function test_recurring_addon_renews_and_generates_next_invoice(): void
    {
        $plan = $this->plan();
        $tenant = $this->tenant('addon-renews');
        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'saas_plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'price' => 2500,
            'starts_at' => today()->subMonth(),
            'current_period_ends_at' => today()->addDays(20),
            'grace_ends_at' => today()->addDays(27),
            'auto_renew' => true,
        ]);

        $addon = \App\Models\Addon::create([
            'name' => 'SMS Pack', 'slug' => 'sms-pack-'.uniqid(), 'category' => 'sms',
            'price' => 500, 'billing_cycle' => 'monthly', 'is_active' => true,
        ]);
        $tenantAddon = \App\Models\TenantAddon::create([
            'tenant_id' => $tenant->id,
            'addon_id' => $addon->id,
            'status' => 'active',
            'price' => 500,
            'billing_cycle' => 'monthly',
            'starts_at' => now(),
            'period_start' => today()->subMonth()->addDay(),
            'period_end' => today()->subDay(),
            'auto_renew' => true,
        ]);

        $summary = app(SaasSubscriptionBilling::class)->processDue();

        $this->assertSame(1, $summary['addons_renewed']);
        $invoice = SaasInvoice::where('tenant_addon_id', $tenantAddon->id)->firstOrFail();
        $this->assertSame('pending', $invoice->status);
        $this->assertNotNull($invoice->tenant_subscription_id);

        $fresh = $tenantAddon->fresh();
        $this->assertSame($invoice->period_start->toDateString(), $fresh->period_start->toDateString());
        $this->assertSame($invoice->period_end->toDateString(), $fresh->period_end->toDateString());
    }

    private function plan(): SaasPlan
    {
        return SaasPlan::create([
            'name' => 'Professional', 'slug' => 'professional-'.uniqid(), 'monthly_price' => 2500,
            'yearly_price' => 25000, 'trial_days' => 14, 'grace_days' => 7, 'is_active' => true,
        ]);
    }

    private function tenant(string $slug = 'billing-isp'): Tenant
    {
        return Tenant::create([
            'name' => 'Billing ISP', 'slug' => $slug.'-'.uniqid(), 'status' => 'active',
            'currency' => 'BDT', 'timezone' => 'Asia/Dhaka',
        ]);
    }
}
