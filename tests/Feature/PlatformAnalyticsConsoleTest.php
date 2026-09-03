<?php

namespace Tests\Feature;

use App\Livewire\PlatformAnalytics;
use App\Models\PlatformAnalyticsSnapshot;
use App\Models\Reseller;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformAnalyticsConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_analytics_shows_arpu_and_reseller_totals(): void
    {
        $admin = User::factory()->create();
        $tenant = Tenant::create(['name' => 'Analytics ISP', 'slug' => 'analytics-isp', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'Asia/Dhaka']);
        Reseller::create(['tenant_id' => $tenant->id, 'name' => 'Reseller One', 'email' => 'reseller@example.test', 'status' => 'active']);
        $plan = SaasPlan::create(['name' => 'Growth', 'slug' => 'growth-analytics', 'monthly_price' => 4000, 'yearly_price' => 40000, 'trial_days' => 14, 'grace_days' => 5, 'is_active' => true]);
        TenantSubscription::create([
            'tenant_id' => $tenant->id, 'saas_plan_id' => $plan->id, 'status' => 'active',
            'billing_cycle' => 'monthly', 'price' => 4000, 'starts_at' => today(),
            'current_period_ends_at' => today()->addMonth(), 'grace_ends_at' => today()->addMonth()->addDays(5),
            'auto_renew' => true,
        ]);

        Livewire::actingAs($admin)->test(PlatformAnalytics::class)
            ->assertSee('৳4,000.00')
            ->assertSee('1'); // reseller count
    }

    public function test_super_admin_can_record_an_analytics_snapshot(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(PlatformAnalytics::class)
            ->call('recordSnapshotNow')
            ->assertHasNoErrors();

        $this->assertSame(1, PlatformAnalyticsSnapshot::count());
        $snapshot = PlatformAnalyticsSnapshot::firstOrFail();
        $this->assertNotNull($snapshot->recorded_at);
    }
}
