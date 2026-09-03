<?php

namespace Tests\Feature;

use App\Livewire\FeatureModules;
use App\Models\Feature;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FeatureModulesConsoleTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Feature ISP', 'slug' => 'feature-isp-'.uniqid(), 'status' => 'active',
            'currency' => 'BDT', 'timezone' => 'Asia/Dhaka',
        ]);
    }

    public function test_super_admin_can_toggle_a_global_feature_flag(): void
    {
        $admin = User::factory()->create();
        $feature = Feature::where('key', 'whatsapp_integration')->firstOrFail();

        Livewire::actingAs($admin)->test(FeatureModules::class)
            ->call('toggleGlobal', $feature->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('features', ['id' => $feature->id, 'is_globally_enabled' => false]);
    }

    public function test_plan_entitlement_toggle_affects_tenant_effective_feature_state(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $plan = SaasPlan::create(['name' => 'Basic', 'slug' => 'basic-'.uniqid(), 'monthly_price' => 1000, 'yearly_price' => 10000, 'trial_days' => 0, 'grace_days' => 5, 'is_active' => true]);
        TenantSubscription::create([
            'tenant_id' => $tenant->id, 'saas_plan_id' => $plan->id, 'status' => 'active',
            'billing_cycle' => 'monthly', 'price' => 1000, 'starts_at' => today(),
            'current_period_ends_at' => today()->addMonth(), 'grace_ends_at' => today()->addMonth()->addDays(5),
            'auto_renew' => true,
        ]);
        $feature = Feature::where('key', 'media_server')->firstOrFail();

        $this->assertTrue($tenant->hasFeature('media_server'));

        Livewire::actingAs($admin)->test(FeatureModules::class)
            ->set('selectedPlanId', $plan->id)
            ->call('togglePlanFeature', $feature->id)
            ->assertHasNoErrors();

        $this->assertFalse($tenant->fresh()->hasFeature('media_server'));
    }

    public function test_tenant_override_takes_precedence_over_plan_entitlement_and_can_be_cleared(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $feature = Feature::where('key', 'white_label')->firstOrFail();

        Livewire::actingAs($admin)->test(FeatureModules::class)
            ->set('selectedTenantId', $tenant->id)
            ->call('toggleTenantOverride', $feature->id)
            ->assertHasNoErrors();

        $override = \App\Models\TenantFeatureOverride::where('tenant_id', $tenant->id)->where('feature_id', $feature->id)->firstOrFail();
        $this->assertFalse($override->is_enabled);
        $this->assertFalse($tenant->fresh()->hasFeature('white_label'));

        Livewire::actingAs($admin)->test(FeatureModules::class)
            ->set('selectedTenantId', $tenant->id)
            ->call('clearTenantOverride', $feature->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('tenant_feature_overrides', ['tenant_id' => $tenant->id, 'feature_id' => $feature->id]);
        $this->assertTrue($tenant->fresh()->hasFeature('white_label'));
    }
}
