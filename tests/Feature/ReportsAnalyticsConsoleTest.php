<?php

namespace Tests\Feature;

use App\Livewire\ReportsAnalytics;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportsAnalyticsConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_render_with_mrr_and_tenant_counts(): void
    {
        $admin = User::factory()->create();
        $tenant = Tenant::create(['name' => 'Reports ISP', 'slug' => 'reports-isp', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'Asia/Dhaka']);
        $plan = SaasPlan::create(['name' => 'Pro', 'slug' => 'pro-report', 'monthly_price' => 3000, 'yearly_price' => 30000, 'trial_days' => 14, 'grace_days' => 5, 'is_active' => true]);
        TenantSubscription::create([
            'tenant_id' => $tenant->id, 'saas_plan_id' => $plan->id, 'status' => 'active',
            'billing_cycle' => 'monthly', 'price' => 3000, 'starts_at' => today(),
            'current_period_ends_at' => today()->addMonth(), 'grace_ends_at' => today()->addMonth()->addDays(5),
            'auto_renew' => true,
        ]);

        Livewire::actingAs($admin)->test(ReportsAnalytics::class)
            ->assertSee('৳3,000.00')
            ->assertSee('Pro');

        $this->assertDatabaseHas('tenants', ['id' => $tenant->id]);
    }

    public function test_export_csv_returns_a_downloadable_file(): void
    {
        $admin = User::factory()->create();

        $response = Livewire::actingAs($admin)->test(ReportsAnalytics::class)->call('exportCsv');

        $response->assertStatus(200);
    }
}
