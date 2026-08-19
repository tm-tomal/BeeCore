<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\TenantBranding;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class WhiteLabel extends Component
{
    use WithFileUploads;

    public ?int $selectedTenantId = null;

    public bool $isEnabled = false;
    public string $brandName = '';
    public string $brandColor = '#14b8a6';
    public string $appName = '';
    public bool $loginBrandingEnabled = true;
    public bool $dashboardBrandingEnabled = true;
    public bool $emailBrandingEnabled = true;
    public bool $smsBrandingEnabled = true;
    public bool $customerAppBrandingEnabled = true;

    public $logo;
    public $favicon;
    public $appIcon;
    public $splashScreen;

    public function updatedSelectedTenantId($value): void
    {
        $this->loadBranding($value);
    }

    public function loadBranding(?int $tenantId): void
    {
        $this->reset(['logo', 'favicon', 'appIcon', 'splashScreen']);

        $branding = $tenantId ? TenantBranding::where('tenant_id', $tenantId)->first() : null;

        $this->isEnabled = $branding->is_enabled ?? false;
        $this->brandName = $branding->brand_name ?? '';
        $this->brandColor = $branding->brand_color ?? '#14b8a6';
        $this->appName = $branding->app_name ?? '';
        $this->loginBrandingEnabled = $branding->login_branding_enabled ?? true;
        $this->dashboardBrandingEnabled = $branding->dashboard_branding_enabled ?? true;
        $this->emailBrandingEnabled = $branding->email_branding_enabled ?? true;
        $this->smsBrandingEnabled = $branding->sms_branding_enabled ?? true;
        $this->customerAppBrandingEnabled = $branding->customer_app_branding_enabled ?? true;
    }

    public function save(): void
    {
        $this->assertSuperAdmin();

        $data = $this->validate([
            'selectedTenantId' => ['required', 'exists:tenants,id'],
            'brandName' => ['nullable', 'string', 'max:255'],
            'brandColor' => ['nullable', 'string', 'max:20'],
            'appName' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:512'],
            'appIcon' => ['nullable', 'image', 'max:1024'],
            'splashScreen' => ['nullable', 'image', 'max:2048'],
        ]);

        $branding = TenantBranding::firstOrNew(['tenant_id' => $data['selectedTenantId']]);

        $attributes = [
            'is_enabled' => $this->isEnabled,
            'brand_name' => $data['brandName'] ?: null,
            'brand_color' => $data['brandColor'] ?: null,
            'app_name' => $data['appName'] ?: null,
            'login_branding_enabled' => $this->loginBrandingEnabled,
            'dashboard_branding_enabled' => $this->dashboardBrandingEnabled,
            'email_branding_enabled' => $this->emailBrandingEnabled,
            'sms_branding_enabled' => $this->smsBrandingEnabled,
            'customer_app_branding_enabled' => $this->customerAppBrandingEnabled,
        ];

        foreach (['logo' => 'logo_path', 'favicon' => 'favicon_path', 'appIcon' => 'app_icon_path', 'splashScreen' => 'splash_screen_path'] as $property => $column) {
            if ($this->{$property}) {
                if ($branding->{$column}) {
                    Storage::disk('public')->delete($branding->{$column});
                }
                $attributes[$column] = $this->{$property}->store('branding/'.$data['selectedTenantId'], 'public');
            }
        }

        $branding->fill($attributes)->save();

        AuditLog::record('tenant.branding_updated', $branding, ['enabled' => $branding->is_enabled], tenantId: $data['selectedTenantId']);

        $this->reset(['logo', 'favicon', 'appIcon', 'splashScreen']);
        session()->flash('message', 'Branding saved.');
    }

    public function render()
    {
        $this->assertSuperAdmin();

        return view('livewire.white-label', [
            'tenants' => Tenant::query()->whereNull('archived_at')->orderBy('name')->get(),
            'branding' => $this->selectedTenantId ? TenantBranding::where('tenant_id', $this->selectedTenantId)->first() : null,
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
