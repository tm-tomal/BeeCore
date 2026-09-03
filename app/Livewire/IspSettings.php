<?php

namespace App\Livewire;

use App\Models\Tenant;
use App\Models\User;
use App\Support\AuthorizesRoles;
use App\Support\CurrentTenant;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class IspSettings extends Component
{
    use AuthorizesRoles;

    public function boot(): void
    {
        $this->authorizeRoles(User::ROLE_SUPER_ADMIN, User::ROLE_TENANT_ADMIN);
    }

    public string $name = '';
    public string $contactPhone = '';
    public string $contactAddress = '';
    public string $currency = '';
    public string $timezone = '';
    public string $language = '';

    public int $graceDays = 7;
    public int $cutoffDay = 1;
    public bool $autoSuspend = false;
    public int $autoSuspendDays = 7;
    public string $invoiceTerms = '';

    public function mount(): void
    {
        $tenant = $this->tenant();

        $this->name = (string) $tenant->name;
        $this->contactPhone = (string) ($tenant->contact_phone ?? '');
        $this->contactAddress = (string) ($tenant->contact_address ?? '');
        $this->currency = (string) ($tenant->currency ?? 'BDT');
        $this->timezone = (string) ($tenant->timezone ?? 'Asia/Dhaka');
        $this->language = (string) ($tenant->language ?? 'en');

        $this->graceDays = (int) $tenant->billingSetting('grace_days', 7);
        $this->cutoffDay = (int) $tenant->billingSetting('cutoff_day', 1);
        $this->autoSuspend = (bool) $tenant->billingSetting('auto_suspend_enabled', false);
        $this->autoSuspendDays = (int) $tenant->billingSetting('auto_suspend_days', 7);
        $this->invoiceTerms = (string) ($tenant->billingSetting('invoice_terms', '') ?? '');
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'contactPhone' => ['nullable', 'string', 'max:30'],
            'contactAddress' => ['nullable', 'string', 'max:500'],
            'currency' => ['required', 'string', 'max:10'],
            'timezone' => ['required', 'string', 'max:80'],
            'language' => ['required', 'string', 'max:10'],
            'graceDays' => ['required', 'integer', 'min:0', 'max:60'],
            'cutoffDay' => ['required', 'integer', 'min:1', 'max:28'],
            'autoSuspend' => ['boolean'],
            'autoSuspendDays' => ['required', 'integer', 'min:1', 'max:90'],
            'invoiceTerms' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $tenant = $this->tenant();
        $tenant->update([
            'name' => $this->name,
            'contact_phone' => $this->contactPhone ?: null,
            'contact_address' => $this->contactAddress ?: null,
            'currency' => $this->currency,
            'timezone' => $this->timezone,
            'language' => $this->language,
        ]);

        $billing = array_merge($tenant->settings['billing'] ?? [], [
            'grace_days' => $this->graceDays,
            'cutoff_day' => $this->cutoffDay,
            'auto_suspend_enabled' => $this->autoSuspend,
            'auto_suspend_days' => $this->autoSuspendDays,
            'invoice_terms' => $this->invoiceTerms ?: null,
        ]);

        $settings = $tenant->settings ?? [];
        $settings['billing'] = $billing;

        $tenant->update(['settings' => $settings]);

        session()->flash('message', 'Workspace settings saved successfully.');
    }

    public function render()
    {
        $tenant = $this->tenant();

        return view('livewire.isp-settings', [
            'workspace' => $tenant,
        ]);
    }

    private function tenant(): Tenant
    {
        return Tenant::query()->findOrFail(app(CurrentTenant::class)->id());
    }
}
