<?php

namespace Tests\Feature;

use App\Livewire\MediaServerConsole;
use App\Models\Tenant;
use App\Models\TenantMediaSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MediaServerConsoleTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Media ISP', 'slug' => 'media-isp-'.uniqid(), 'status' => 'active',
            'currency' => 'BDT', 'timezone' => 'Asia/Dhaka',
        ]);
    }

    public function test_super_admin_can_add_a_media_server_and_check_its_health(): void
    {
        $admin = User::factory()->create();

        $component = Livewire::actingAs($admin)->test(MediaServerConsole::class)
            ->call('create')
            ->set('name', 'Media Node 1')
            ->set('host', 'media1.beecore.internal')
            ->set('storageCapacityGb', 500)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('media_servers', ['name' => 'Media Node 1', 'status' => 'offline']);

        $server = \App\Models\MediaServer::where('name', 'Media Node 1')->firstOrFail();
        $component->call('checkHealth', $server->id)->assertHasNoErrors();
        $this->assertDatabaseHas('media_servers', ['id' => $server->id, 'status' => 'online']);
    }

    public function test_super_admin_can_enable_media_module_for_a_tenant_and_simulate_usage(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();

        Livewire::actingAs($admin)->test(MediaServerConsole::class)
            ->set('tab', 'tenants')
            ->call('selectTenant', $tenant->id)
            ->set('isEnabled', true)
            ->set('storageAllocatedGb', 200)
            ->set('contentPolicy', 'No copyrighted content without a license.')
            ->call('saveTenantSettings')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tenant_media_settings', [
            'tenant_id' => $tenant->id, 'is_enabled' => true, 'storage_allocated_gb' => 200,
        ]);

        Livewire::actingAs($admin)->test(MediaServerConsole::class)
            ->call('selectTenant', $tenant->id)
            ->call('simulateUsage')
            ->assertHasNoErrors();

        $settings = TenantMediaSetting::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertSame(5, $settings->storage_used_gb);
        $this->assertSame(10, $settings->streaming_used_gb);
        $this->assertSame(15, $settings->bandwidth_used_gb);
    }
}
