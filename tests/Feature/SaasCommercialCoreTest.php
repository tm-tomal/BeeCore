<?php

namespace Tests\Feature;

use App\Livewire\SaasPlans;
use App\Livewire\TenantDetails;
use App\Livewire\Tenants;
use App\Models\SaasInvoice;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SaasCommercialCoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_a_saas_plan(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(SaasPlans::class)
            ->set('name', 'Professional')
            ->set('slug', 'professional')
            ->set('monthlyPrice', 2500)
            ->set('yearlyDiscountPercent', 25)
            ->set('customerLimit', 5000)
            ->set('overflowRate', 2.5)
            ->set('staffLimit', 20)
            ->set('resellerLimit', 10)
            ->set('trialDays', 14)
            ->set('graceDays', 7)
            ->set('operationMode', 'automatic')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('saas_plans', [
            'slug' => 'professional',
            'monthly_price' => 2500,
            'yearly_discount_percent' => 25,
            'yearly_price' => 22500,
            'trial_days' => 14,
            'operation_mode' => 'automatic',
            'overflow_rate' => 2.5,
        ]);
    }

    public function test_plan_yearly_price_is_always_derived_from_monthly_with_discount(): void
    {
        $plan = SaasPlan::create([
            'name' => 'Starter', 'slug' => 'derived-yearly', 'monthly_price' => 1000,
            'yearly_price' => 9999, 'yearly_discount_percent' => 25,
            'trial_days' => 0, 'grace_days' => 0, 'is_active' => true,
        ]);

        // Stored fixed amount (9999) is ignored — yearly is derived from the monthly price.
        $this->assertSame(9000.0, $plan->yearly_price);
        $this->assertSame(9000.0, $plan->computedYearlyPrice());
        $this->assertSame(3000.0, $plan->yearlySavings());

        $discounted = SaasPlan::create([
            'name' => 'Pro', 'slug' => 'derived-pro', 'monthly_price' => 2000,
            'yearly_discount_percent' => 30, 'trial_days' => 0, 'grace_days' => 0, 'is_active' => true,
        ]);

        $this->assertSame(16800.0, $discounted->yearly_price);
    }

    public function test_plan_reports_overflow_charge_above_included_limit(): void
    {
        $plan = SaasPlan::create([
            'name' => 'Growth', 'slug' => 'growth', 'monthly_price' => 1200,
            'yearly_price' => 12000, 'customer_limit' => 750, 'overflow_rate' => 2.8,
            'trial_days' => 0, 'grace_days' => 0, 'is_active' => true,
        ]);

        $this->assertSame(0.0, $plan->overflowChargeFor(600));
        $this->assertSame(0.0, $plan->overflowChargeFor(750));
        $this->assertSame(140.0, $plan->overflowChargeFor(800));
        $this->assertSame(700.0, $plan->overflowChargeFor(1000));
    }

    public function test_plan_assignment_options_are_filtered_by_tenant_operation_mode(): void
    {
        $admin = User::factory()->create();
        $automaticTenant = $this->tenant();
        $manualTenant = $this->tenant(['slug' => 'manual-isp', 'operation_mode' => 'manual']);

        $automaticPlan = SaasPlan::create([
            'name' => 'Auto Pro', 'slug' => 'auto-pro', 'monthly_price' => 3000,
            'yearly_price' => 30000, 'trial_days' => 0, 'grace_days' => 0,
            'operation_mode' => 'automatic', 'is_active' => true,
        ]);
        $manualPlan = SaasPlan::create([
            'name' => 'Manual Pro', 'slug' => 'manual-pro', 'monthly_price' => 2000,
            'yearly_price' => 20000, 'trial_days' => 0, 'grace_days' => 0,
            'operation_mode' => 'manual', 'is_active' => true,
        ]);
        $bothPlan = SaasPlan::create([
            'name' => 'Starter', 'slug' => 'starter', 'monthly_price' => 1000,
            'yearly_price' => 10000, 'trial_days' => 0, 'grace_days' => 0,
            'operation_mode' => 'both', 'is_active' => true,
        ]);

        Livewire::actingAs($admin)->test(TenantDetails::class, ['tenant' => $automaticTenant])
            ->assertViewHas('plans', fn ($plans) => $plans->pluck('id')->contains($automaticPlan->id)
                && $plans->pluck('id')->contains($bothPlan->id)
                && ! $plans->pluck('id')->contains($manualPlan->id));

        Livewire::actingAs($admin)->test(TenantDetails::class, ['tenant' => $manualTenant])
            ->assertViewHas('plans', fn ($plans) => $plans->pluck('id')->contains($manualPlan->id)
                && $plans->pluck('id')->contains($bothPlan->id)
                && ! $plans->pluck('id')->contains($automaticPlan->id));
    }

    public function test_super_admin_can_assign_and_change_a_tenant_subscription(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $plan = SaasPlan::create([
            'name' => 'Professional', 'slug' => 'professional', 'monthly_price' => 2500,
            'yearly_price' => 25000, 'trial_days' => 14, 'grace_days' => 7, 'is_active' => true,
        ]);

        $component = Livewire::actingAs($admin)->test(TenantDetails::class, ['tenant' => $tenant])
            ->set('planId', $plan->id)
            ->set('billingCycle', 'monthly')
            ->set('subscriptionStatus', 'trialing')
            ->set('startsAt', '2026-08-18')
            ->set('trialEndsAt', '2026-09-01')
            ->call('saveSubscription')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tenant_subscriptions', [
            'tenant_id' => $tenant->id,
            'saas_plan_id' => $plan->id,
            'status' => 'trialing',
            'price' => 2500,
        ]);

        $component->set('subscriptionStatus', 'active')->call('saveSubscription')->assertHasNoErrors();
        $this->assertDatabaseHas('tenant_subscriptions', ['tenant_id' => $tenant->id, 'status' => 'active']);
        $this->assertDatabaseCount('tenant_subscription_events', 2);
    }

    public function test_super_admin_can_mark_a_saas_invoice_paid_and_reactivate_a_suspended_tenant(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $plan = SaasPlan::create([
            'name' => 'Professional', 'slug' => 'professional-mark-paid', 'monthly_price' => 2500,
            'yearly_price' => 25000, 'trial_days' => 14, 'grace_days' => 7, 'is_active' => true,
        ]);
        $subscription = TenantSubscription::create([
            'tenant_id' => $tenant->id, 'saas_plan_id' => $plan->id, 'status' => 'suspended',
            'billing_cycle' => 'monthly', 'price' => 2500, 'starts_at' => today()->subMonths(2),
            'current_period_ends_at' => today()->addDays(20), 'grace_ends_at' => today()->subDay(),
            'auto_renew' => true,
        ]);
        $tenant->update(['status' => 'suspended']);
        $invoice = SaasInvoice::create([
            'tenant_id' => $tenant->id, 'tenant_subscription_id' => $subscription->id,
            'invoice_number' => 'SAAS-MARKPAID-0001', 'status' => 'overdue',
            'period_start' => today()->subMonth(), 'period_end' => today()->subDay(),
            'amount' => 2500, 'due_date' => today()->subDays(5),
        ]);

        Livewire::actingAs($admin)->test(TenantDetails::class, ['tenant' => $tenant])
            ->call('markInvoicePaid', $invoice->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('saas_invoices', ['id' => $invoice->id, 'status' => 'paid']);
        $this->assertDatabaseHas('saas_payments', ['saas_invoice_id' => $invoice->id, 'amount' => 2500]);
        $this->assertSame('active', $subscription->fresh()->status);
        $this->assertSame('active', $tenant->fresh()->status);
    }

    public function test_archiving_a_tenant_preserves_its_record(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();

        Livewire::actingAs($admin)->test(Tenants::class)->call('delete', $tenant->id);

        $this->assertDatabaseHas('tenants', ['id' => $tenant->id, 'status' => 'inactive']);
        $this->assertNotNull($tenant->fresh()->archived_at);
        $this->assertDatabaseHas('audit_logs', ['tenant_id' => $tenant->id, 'action' => 'tenant.archived']);
    }

    public function test_tenant_user_cannot_manage_saas_plans_or_subscriptions(): void
    {
        $tenant = $this->tenant();
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        $this->actingAs($user)->get('/saas-plans')->assertForbidden();
        $this->actingAs($user)->get(route('tenant-details', $tenant))->assertForbidden();
    }

    private function tenant(array $attributes = []): Tenant
    {
        return Tenant::create(array_merge([
            'name' => 'Commercial ISP', 'slug' => 'commercial-isp', 'status' => 'active',
            'currency' => 'BDT', 'timezone' => 'Asia/Dhaka',
        ], $attributes));
    }
}