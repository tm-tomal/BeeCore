<?php

namespace App\Livewire;

use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Models\User;
use App\Support\AuthorizesRoles;
use App\Support\CurrentTenant;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class IspGateway extends Component
{
    use AuthorizesRoles;

    public function boot(): void
    {
        $this->authorizeRoles(User::ROLE_SUPER_ADMIN, User::ROLE_TENANT_ADMIN);
    }

    public string $collectionMode = 'bee';

    public bool $bkashEnabled = false;
    public string $bkashNumber = '';
    public bool $nagadEnabled = false;
    public string $nagadNumber = '';
    public bool $bankEnabled = false;
    public string $bankDetails = '';

    public function mount(): void
    {
        $tenant = $this->tenant();
        $config = $tenant->settings['collection'] ?? [];

        $this->collectionMode = $config['mode'] ?? 'bee';

        $methods = $config['methods'] ?? [];
        $this->bkashEnabled = (bool) ($methods['bkash']['enabled'] ?? false);
        $this->bkashNumber = (string) ($methods['bkash']['number'] ?? '');
        $this->nagadEnabled = (bool) ($methods['nagad']['enabled'] ?? false);
        $this->nagadNumber = (string) ($methods['nagad']['number'] ?? '');
        $this->bankEnabled = (bool) ($methods['bank']['enabled'] ?? false);
        $this->bankDetails = (string) ($methods['bank']['details'] ?? '');
    }

    protected function rules(): array
    {
        return [
            'collectionMode' => ['required', 'in:bee,own'],
            'bkashNumber' => ['nullable', 'string', 'max:30'],
            'nagadNumber' => ['nullable', 'string', 'max:30'],
            'bankDetails' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $tenant = $this->tenant();
        $settings = $tenant->settings ?? [];

        $settings['collection'] = [
            'mode' => $this->collectionMode,
            'methods' => [
                'bkash' => ['enabled' => $this->bkashEnabled, 'number' => $this->bkashNumber ?: null],
                'nagad' => ['enabled' => $this->nagadEnabled, 'number' => $this->nagadNumber ?: null],
                'bank' => ['enabled' => $this->bankEnabled, 'details' => $this->bankDetails ?: null],
            ],
        ];

        $tenant->update(['settings' => $settings]);

        session()->flash('message', 'Customer payment gateway settings saved successfully.');
    }

    public function render()
    {
        return view('livewire.isp-gateway', [
            'workspace' => $this->tenant(),
            'beeFeePercent' => SystemSetting::beeFeePercent(),
        ]);
    }

    private function tenant(): Tenant
    {
        return Tenant::query()->findOrFail(app(CurrentTenant::class)->id());
    }
}
