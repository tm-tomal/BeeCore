<?php

namespace Tests\Feature;

use App\Livewire\NetworkIntegrations;
use App\Models\NetworkIntegration;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class NetworkIntegrationsConsoleTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Network ISP', 'slug' => 'network-isp-'.uniqid(), 'status' => 'active',
            'currency' => 'BDT', 'timezone' => 'Asia/Dhaka',
        ]);
    }

    public function test_super_admin_can_create_an_integration_with_encrypted_credentials(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();

        Livewire::actingAs($admin)->test(NetworkIntegrations::class)
            ->call('selectTenant', $tenant->id)
            ->call('create')
            ->set('name', 'Core Router')
            ->set('type', 'mikrotik')
            ->set('host', '10.0.0.1')
            ->set('version', 'RouterOS 7.14')
            ->set('credentialsText', "username=admin\npassword=secret")
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('network_integrations', ['tenant_id' => $tenant->id, 'name' => 'Core Router', 'type' => 'mikrotik']);
        $integration = NetworkIntegration::where('name', 'Core Router')->firstOrFail();
        $this->assertSame('secret', $integration->credentials['password']);

        $raw = DB::table('network_integrations')->where('id', $integration->id)->value('credentials');
        $this->assertStringNotContainsString('secret', $raw);
    }

    public function test_connection_test_health_depends_on_active_status_and_logs_result(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $integration = NetworkIntegration::create(['tenant_id' => $tenant->id, 'name' => 'RADIUS Server', 'type' => 'radius', 'is_active' => false]);

        Livewire::actingAs($admin)->test(NetworkIntegrations::class)->call('testConnection', $integration->id);
        $this->assertDatabaseHas('network_integrations', ['id' => $integration->id, 'health_status' => 'offline']);
        $this->assertDatabaseHas('network_integration_logs', ['network_integration_id' => $integration->id, 'direction' => 'failure']);

        $integration->update(['is_active' => true]);
        Livewire::actingAs($admin)->test(NetworkIntegrations::class)->call('testConnection', $integration->id);
        $this->assertDatabaseHas('network_integrations', ['id' => $integration->id, 'health_status' => 'online']);
        $this->assertDatabaseHas('network_integration_logs', ['network_integration_id' => $integration->id, 'direction' => 'response']);
    }

    public function test_super_admin_can_delete_an_integration(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $integration = NetworkIntegration::create(['tenant_id' => $tenant->id, 'name' => 'OLT Node', 'type' => 'olt', 'is_active' => true]);

        Livewire::actingAs($admin)->test(NetworkIntegrations::class)->call('delete', $integration->id);
        $this->assertDatabaseMissing('network_integrations', ['id' => $integration->id]);
    }
}
