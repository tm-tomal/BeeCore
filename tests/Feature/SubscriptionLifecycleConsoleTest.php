<?php

namespace Tests\Feature;

use App\Livewire\Subscriptions;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SubscriptionLifecycleConsoleTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(array $overrides = []): Tenant
    {
        return Tenant::create(array_merge([
            'name' => 'Subscription ISP', 'slug' => 'subscription-isp-'.uniqid(), 'status' => 'active',
            'currency' => 'BDT', 'timezone' => 'Asia/Dhaka',
        ], $overrides));
    }

    private function subscription(Tenant $tenant, SaasPlan $plan, array $overrides = []): TenantSubscription
    {
        return TenantSubscription::create(array_merge([
            'tenant_id' => $tenant->id,
            'saas_plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'price' => $plan->monthly_price,
            'starts_at' => today()->subDays(20),
            'current_period_ends_at' => today()->addDays(10),
            'grace_ends_at' => today()->addDays(10 + $plan->grace_days),
            'auto_renew' => true,
        ], $overrides));
    }

    public function test_super_admin_can_renew_a_subscription_and_reactivate_a_suspended_tenant(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant(['status' => 'suspended']);
        $plan = SaasPlan::create(['name' => 'Starter', 'slug' => 'starter', 'monthly_price' => 1000, 'yearly_price' => 10000, 'trial_days' => 0, 'grace_days' => 5, 'is_active' => true]);
        $subscription = $this->subscription($tenant, $plan, ['status' => 'past_due']);

        Livewire::actingAs($admin)->test(Subscriptions::class)
            ->call('renew', $subscription->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tenant_subscriptions', ['id' => $subscription->id, 'status' => 'active']);
        $this->assertDatabaseHas('tenants', ['id' => $tenant->id, 'status' => 'active']);
        $this->assertDatabaseHas('tenant_subscription_events', ['tenant_subscription_id' => $subscription->id, 'event' => 'subscription.renewed']);
    }

    public function test_super_admin_can_pause_and_resume_a_subscription(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $plan = SaasPlan::create(['name' => 'Starter', 'slug' => 'starter-2', 'monthly_price' => 1000, 'yearly_price' => 10000, 'trial_days' => 0, 'grace_days' => 5, 'is_active' => true]);
        $subscription = $this->subscription($tenant, $plan);

        $component = Livewire::actingAs($admin)->test(Subscriptions::class)
            ->call('pause', $subscription->id)
            ->assertHasNoErrors();
        $this->assertDatabaseHas('tenant_subscriptions', ['id' => $subscription->id, 'status' => 'paused']);

        $component->call('resume', $subscription->id)->assertHasNoErrors();
        $this->assertDatabaseHas('tenant_subscriptions', ['id' => $subscription->id, 'status' => 'active']);
        $this->assertDatabaseHas('tenant_subscription_events', ['tenant_subscription_id' => $subscription->id, 'event' => 'subscription.resumed']);
    }

    public function test_super_admin_can_cancel_a_subscription(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $plan = SaasPlan::create(['name' => 'Starter', 'slug' => 'starter-3', 'monthly_price' => 1000, 'yearly_price' => 10000, 'trial_days' => 0, 'grace_days' => 5, 'is_active' => true]);
        $subscription = $this->subscription($tenant, $plan);

        Livewire::actingAs($admin)->test(Subscriptions::class)
            ->call('cancel', $subscription->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tenant_subscriptions', ['id' => $subscription->id, 'status' => 'cancelled', 'auto_renew' => false]);
    }

    public function test_super_admin_can_extend_a_trial(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $plan = SaasPlan::create(['name' => 'Starter', 'slug' => 'starter-4', 'monthly_price' => 1000, 'yearly_price' => 10000, 'trial_days' => 14, 'grace_days' => 5, 'is_active' => true]);
        $subscription = $this->subscription($tenant, $plan, ['status' => 'trialing', 'trial_ends_at' => today()->addDays(5)]);

        Livewire::actingAs($admin)->test(Subscriptions::class)
            ->call('openExtendTrial', $subscription->id)
            ->set('extendTrialDays', 10)
            ->call('extendTrial')
            ->assertHasNoErrors();

        $subscription->refresh();
        $this->assertTrue($subscription->trial_ends_at->isSameDay(today()->addDays(15)));
    }

    public function test_super_admin_can_change_plan_with_prorated_credit_logged(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $basic = SaasPlan::create(['name' => 'Basic', 'slug' => 'basic', 'monthly_price' => 1000, 'yearly_price' => 10000, 'trial_days' => 0, 'grace_days' => 5, 'is_active' => true]);
        $pro = SaasPlan::create(['name' => 'Pro', 'slug' => 'pro', 'monthly_price' => 2000, 'yearly_price' => 20000, 'trial_days' => 0, 'grace_days' => 5, 'is_active' => true]);
        $subscription = $this->subscription($tenant, $basic);

        Livewire::actingAs($admin)->test(Subscriptions::class)
            ->call('openChangePlan', $subscription->id)
            ->set('newPlanId', $pro->id)
            ->set('newBillingCycle', 'monthly')
            ->call('changePlan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tenant_subscriptions', ['id' => $subscription->id, 'saas_plan_id' => $pro->id, 'price' => 2000]);
        $this->assertDatabaseHas('tenant_subscription_events', ['tenant_subscription_id' => $subscription->id, 'event' => 'subscription.plan_changed']);
    }
}
