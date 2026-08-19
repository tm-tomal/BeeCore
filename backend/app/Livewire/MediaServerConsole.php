<?php

namespace App\Livewire;

use App\Models\Addon;
use App\Models\AuditLog;
use App\Models\MediaServer;
use App\Models\Tenant;
use App\Models\TenantMediaSetting;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class MediaServerConsole extends Component
{
    public string $tab = 'servers';

    // Server form
    public string $viewMode = 'index';
    public ?int $serverId = null;
    public string $name = '';
    public string $host = '';
    public int $storageCapacityGb = 100;

    // Tenant settings
    public ?int $selectedTenantId = null;
    public bool $isEnabled = false;
    public int $storageAllocatedGb = 0;
    public string $contentPolicy = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'host' => ['required', 'string', 'max:255'],
            'storageCapacityGb' => ['required', 'integer', 'min:1'],
        ];
    }

    public function create(): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $this->reset(['serverId', 'name', 'host']);
        $this->storageCapacityGb = 100;
        $this->viewMode = 'create';
    }

    public function edit(int $id): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $server = MediaServer::findOrFail($id);
        $this->serverId = $server->id;
        $this->name = $server->name;
        $this->host = $server->host;
        $this->storageCapacityGb = $server->storage_capacity_gb;
        $this->viewMode = 'create';
    }

    public function cancelForm(): void
    {
        $this->viewMode = 'index';
    }

    public function save(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate();

        $server = $this->serverId ? MediaServer::findOrFail($this->serverId) : new MediaServer(['status' => 'offline']);
        $server->fill([
            'name' => $data['name'],
            'host' => $data['host'],
            'storage_capacity_gb' => $data['storageCapacityGb'],
        ])->save();

        AuditLog::record($this->serverId ? 'media_server.updated' : 'media_server.created', $server);

        $this->viewMode = 'index';
        session()->flash('message', $this->serverId ? 'Media server updated.' : 'Media server added.');
    }

    public function checkHealth(int $id): void
    {
        $this->assertSuperAdmin();
        $server = MediaServer::findOrFail($id);
        $server->update(['status' => 'online', 'last_checked_at' => now()]);
        AuditLog::record('media_server.health_checked', $server, ['status' => 'online']);
        session()->flash('message', $server->name.' health check logged as online.');
    }

    public function markDegraded(int $id): void
    {
        $this->assertSuperAdmin();
        $server = MediaServer::findOrFail($id);
        $server->update(['status' => 'degraded', 'last_checked_at' => now()]);
        AuditLog::record('media_server.health_checked', $server, ['status' => 'degraded']);
        session()->flash('message', $server->name.' marked degraded.');
    }

    public function selectTenant(?int $tenantId): void
    {
        $this->selectedTenantId = $tenantId;
        $settings = $tenantId ? TenantMediaSetting::where('tenant_id', $tenantId)->first() : null;

        $this->isEnabled = $settings->is_enabled ?? false;
        $this->storageAllocatedGb = $settings->storage_allocated_gb ?? 0;
        $this->contentPolicy = $settings->content_policy ?? '';
    }

    public function saveTenantSettings(): void
    {
        $this->assertSuperAdmin();

        $data = $this->validate([
            'selectedTenantId' => ['required', 'exists:tenants,id'],
            'storageAllocatedGb' => ['required', 'integer', 'min:0'],
            'contentPolicy' => ['nullable', 'string', 'max:2000'],
        ]);

        $settings = TenantMediaSetting::firstOrNew(['tenant_id' => $data['selectedTenantId']]);
        $settings->fill([
            'is_enabled' => $this->isEnabled,
            'storage_allocated_gb' => $data['storageAllocatedGb'],
            'content_policy' => $data['contentPolicy'] ?: null,
        ])->save();

        AuditLog::record('tenant.media_settings_updated', $settings, ['enabled' => $settings->is_enabled], tenantId: $data['selectedTenantId']);
        session()->flash('message', 'Tenant media settings saved.');
    }

    public function simulateUsage(): void
    {
        $this->assertSuperAdmin();
        abort_unless($this->selectedTenantId, 422);

        $settings = TenantMediaSetting::where('tenant_id', $this->selectedTenantId)->firstOrFail();
        $settings->increment('storage_used_gb', 5);
        $settings->increment('streaming_used_gb', 10);
        $settings->increment('bandwidth_used_gb', 15);

        session()->flash('message', 'Simulated usage recorded.');
    }

    public function render()
    {
        $this->assertSuperAdmin();

        return view('livewire.media-server-console', [
            'servers' => MediaServer::query()->orderBy('name')->get(),
            'tenants' => Tenant::query()->whereNull('archived_at')->orderBy('name')->get(),
            'tenantSettings' => $this->selectedTenantId ? TenantMediaSetting::where('tenant_id', $this->selectedTenantId)->first() : null,
            'mediaAddons' => Addon::query()->where('category', 'media')->whereNull('archived_at')->get(),
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
