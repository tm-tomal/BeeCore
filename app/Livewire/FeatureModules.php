<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Feature;
use App\Models\PlanFeature;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\TenantFeatureOverride;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class FeatureModules extends Component
{
    public string $tab = 'catalog';

    public ?int $selectedPlanId = null;
    public ?int $selectedTenantId = null;

    public function toggleGlobal(int $featureId): void
    {
        $this->assertSuperAdmin();
        $feature = Feature::findOrFail($featureId);
        $feature->update(['is_globally_enabled' => !$feature->is_globally_enabled]);
        AuditLog::record('feature.global_toggled', $feature, ['enabled' => $feature->is_globally_enabled]);
        session()->flash('message', $feature->name.' is now '.($feature->is_globally_enabled ? 'enabled' : 'disabled').' platform-wide.');
    }

    public function togglePlanFeature(int $featureId): void
    {
        $this->assertSuperAdmin();
        abort_unless($this->selectedPlanId, 422);

        $planFeature = PlanFeature::firstOrNew(['saas_plan_id' => $this->selectedPlanId, 'feature_id' => $featureId]);
        $planFeature->is_enabled = !($planFeature->exists ? $planFeature->is_enabled : true);
        $planFeature->save();

        AuditLog::record('feature.plan_entitlement_toggled', $planFeature, ['feature_id' => $featureId, 'enabled' => $planFeature->is_enabled]);
        session()->flash('message', 'Plan entitlement updated.');
    }

    public function toggleTenantOverride(int $featureId): void
    {
        $this->assertSuperAdmin();
        abort_unless($this->selectedTenantId, 422);

        $override = TenantFeatureOverride::firstOrNew(['tenant_id' => $this->selectedTenantId, 'feature_id' => $featureId]);
        $override->is_enabled = !($override->exists ? $override->is_enabled : true);
        $override->save();

        AuditLog::record('feature.tenant_override_toggled', $override, ['feature_id' => $featureId, 'enabled' => $override->is_enabled], tenantId: $this->selectedTenantId);
        session()->flash('message', 'Tenant override updated.');
    }

    public function clearTenantOverride(int $featureId): void
    {
        $this->assertSuperAdmin();
        abort_unless($this->selectedTenantId, 422);

        TenantFeatureOverride::where('tenant_id', $this->selectedTenantId)->where('feature_id', $featureId)->delete();
        AuditLog::record('feature.tenant_override_cleared', null, ['feature_id' => $featureId], tenantId: $this->selectedTenantId);
        session()->flash('message', 'Tenant override cleared, falling back to plan entitlement.');
    }

    public function render()
    {
        $this->assertSuperAdmin();

        $features = Feature::query()->orderBy('name')->get();
        $planFeatures = $this->selectedPlanId
            ? PlanFeature::where('saas_plan_id', $this->selectedPlanId)->get()->keyBy('feature_id')
            : collect();
        $tenantOverrides = $this->selectedTenantId
            ? TenantFeatureOverride::where('tenant_id', $this->selectedTenantId)->get()->keyBy('feature_id')
            : collect();

        $tenant = $this->selectedTenantId ? Tenant::find($this->selectedTenantId) : null;

        return view('livewire.feature-modules', [
            'features' => $features,
            'plans' => SaasPlan::query()->whereNull('archived_at')->orderBy('name')->get(),
            'tenants' => Tenant::query()->whereNull('archived_at')->orderBy('name')->get(),
            'planFeatures' => $planFeatures,
            'tenantOverrides' => $tenantOverrides,
            'effectiveStates' => $tenant ? $features->mapWithKeys(fn ($feature) => [$feature->id => $tenant->hasFeature($feature->key)]) : collect(),
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
