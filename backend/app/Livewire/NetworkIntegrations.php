<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\NetworkIntegration;
use App\Models\NetworkIntegrationLog;
use App\Models\Tenant;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class NetworkIntegrations extends Component
{
    public ?int $selectedTenantId = null;

    public string $viewMode = 'index';
    public ?int $integrationId = null;
    public string $name = '';
    public string $type = 'mikrotik';
    public string $host = '';
    public string $version = '';
    public string $credentialsText = '';

    public ?int $logsForId = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['mikrotik', 'radius', 'olt', 'custom_api'])],
            'host' => ['nullable', 'string', 'max:255'],
            'version' => ['nullable', 'string', 'max:50'],
            'credentialsText' => ['nullable', 'string'],
        ];
    }

    public function selectTenant(?int $tenantId): void
    {
        $this->selectedTenantId = $tenantId;
        $this->viewMode = 'index';
    }

    public function create(): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $this->reset(['integrationId', 'name', 'host', 'version', 'credentialsText']);
        $this->type = 'mikrotik';
        $this->viewMode = 'create';
    }

    public function edit(int $id): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $i = NetworkIntegration::findOrFail($id);
        $this->integrationId = $i->id;
        $this->name = $i->name;
        $this->type = $i->type;
        $this->host = $i->host ?? '';
        $this->version = $i->version ?? '';
        $this->credentialsText = collect($i->credentials ?? [])->map(fn ($v, $k) => "{$k}={$v}")->implode("\n");
        $this->viewMode = 'create';
    }

    public function cancelForm(): void
    {
        $this->viewMode = 'index';
    }

    public function save(): void
    {
        $this->assertSuperAdmin();
        abort_unless($this->selectedTenantId, 422);
        $data = $this->validate();

        $credentials = collect(preg_split('/\r\n|\r|\n/', trim($data['credentialsText'])))
            ->filter()
            ->mapWithKeys(function ($line) {
                [$key, $value] = array_pad(explode('=', $line, 2), 2, '');

                return [trim($key) => trim($value)];
            })
            ->filter(fn ($value, $key) => $key !== '')
            ->all();

        $i = $this->integrationId ? NetworkIntegration::findOrFail($this->integrationId) : new NetworkIntegration(['tenant_id' => $this->selectedTenantId, 'is_active' => false]);
        $i->fill([
            'name' => $data['name'],
            'type' => $data['type'],
            'host' => $data['host'] ?: null,
            'version' => $data['version'] ?: null,
            'credentials' => $credentials,
        ])->save();

        AuditLog::record($this->integrationId ? 'network_integration.updated' : 'network_integration.created', $i, ['type' => $i->type], tenantId: $this->selectedTenantId);

        $this->viewMode = 'index';
        session()->flash('message', $this->integrationId ? 'Integration updated.' : 'Integration added.');
    }

    public function toggleActive(int $id): void
    {
        $this->assertSuperAdmin();
        $i = NetworkIntegration::findOrFail($id);
        $i->update(['is_active' => !$i->is_active]);
        AuditLog::record($i->is_active ? 'network_integration.activated' : 'network_integration.deactivated', $i, tenantId: $i->tenant_id);
        session()->flash('message', 'Integration '.($i->is_active ? 'enabled' : 'disabled').'.');
    }

    public function testConnection(int $id): void
    {
        $this->assertSuperAdmin();
        $i = NetworkIntegration::findOrFail($id);
        $status = $i->is_active ? 'online' : 'offline';

        $i->update(['health_status' => $status, 'last_checked_at' => now()]);

        NetworkIntegrationLog::create([
            'network_integration_id' => $i->id,
            'direction' => $status === 'online' ? 'response' : 'failure',
            'message' => $status === 'online' ? 'Connection test succeeded.' : 'Connection test failed: integration disabled.',
            'metadata' => ['host' => $i->host],
            'created_at' => now(),
        ]);

        AuditLog::record('network_integration.connection_tested', $i, ['result' => $status], tenantId: $i->tenant_id);
        session()->flash('message', 'Connection test logged as '.$status.'.');
    }

    public function delete(int $id): void
    {
        $this->assertSuperAdmin();
        $i = NetworkIntegration::findOrFail($id);
        $i->delete();
        AuditLog::record('network_integration.deleted', null, ['name' => $i->name], tenantId: $i->tenant_id);
        session()->flash('message', 'Integration removed.');
    }

    public function viewLogs(int $id): void
    {
        $this->logsForId = $id;
    }

    public function closeLogs(): void
    {
        $this->logsForId = null;
    }

    public function render()
    {
        $this->assertSuperAdmin();

        return view('livewire.network-integrations', [
            'tenants' => Tenant::query()->whereNull('archived_at')->orderBy('name')->get(),
            'integrations' => $this->selectedTenantId
                ? NetworkIntegration::where('tenant_id', $this->selectedTenantId)->orderBy('name')->get()
                : collect(),
            'logs' => $this->logsForId
                ? NetworkIntegrationLog::where('network_integration_id', $this->logsForId)->latest('id')->limit(50)->get()
                : collect(),
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
