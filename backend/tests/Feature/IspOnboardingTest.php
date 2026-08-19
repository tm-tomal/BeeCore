<?php

namespace Tests\Feature;

use App\Livewire\IspOnboarding;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IspOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_onboard_a_new_isp_with_plan_and_admin_account(): void
    {
        $admin = User::factory()->create();
        $plan = SaasPlan::create([
            'name' => 'Starter', 'slug' => 'starter', 'monthly_price' => 1500,
            'yearly_price' => 15000, 'trial_days' => 14, 'grace_days' => 5, 'is_active' => true,
        ]);

        Livewire::actingAs($admin)->test(IspOnboarding::class)
            ->set('name', 'Green Fiber Networks')
            ->set('companyLegalName', 'Green Fiber Networks Ltd.')
            ->set('businessType', 'Limited company')
            ->set('ownerName', 'Rahim Uddin')
            ->set('ownerEmail', 'rahim@greenfiber.test')
            ->set('ownerPhone', '01700000000')
            ->set('contactPhone', '01800000000')
            ->set('contactAddress', 'Dhaka, Bangladesh')
            ->set('subdomain', 'green-fiber')
            ->set('planId', $plan->id)
            ->set('billingCycle', 'monthly')
            ->set('adminName', 'Rahim Uddin')
            ->set('adminEmail', 'admin@greenfiber.test')
            ->set('adminPassword', 'password123')
            ->call('register')
            ->assertHasNoErrors();

        $tenant = Tenant::where('slug', 'green-fiber-networks')->firstOrFail();
        $this->assertSame('trial', $tenant->status);
        $this->assertSame('completed', $tenant->onboarding_status);
        $this->assertNotNull($tenant->onboarding_completed_at);

        $this->assertDatabaseHas('users', [
            'tenant_id' => $tenant->id,
            'email' => 'admin@greenfiber.test',
            'role' => User::ROLE_TENANT_ADMIN,
        ]);

        $this->assertDatabaseHas('tenant_subscriptions', [
            'tenant_id' => $tenant->id,
            'saas_plan_id' => $plan->id,
            'status' => 'trialing',
        ]);
    }

    public function test_onboarding_requires_all_profile_fields(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(IspOnboarding::class)
            ->set('name', 'Incomplete ISP')
            ->call('register')
            ->assertHasErrors(['companyLegalName', 'ownerEmail', 'planId', 'adminEmail']);
    }
}
