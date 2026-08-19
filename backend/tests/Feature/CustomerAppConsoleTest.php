<?php

namespace Tests\Feature;

use App\Livewire\CustomerApp;
use App\Models\CustomerAppEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerAppConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_update_version_policy_and_maintenance_mode(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(CustomerApp::class)
            ->set('currentVersion', '2.1.0')
            ->set('minimumSupportedVersion', '2.0.0')
            ->set('forceUpdateEnabled', true)
            ->set('maintenanceModeEnabled', true)
            ->set('maintenanceMessage', 'Upgrading infrastructure, back shortly.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customer_app_settings', [
            'current_version' => '2.1.0',
            'minimum_supported_version' => '2.0.0',
            'force_update_enabled' => true,
            'maintenance_mode_enabled' => true,
        ]);
    }

    public function test_super_admin_can_log_a_test_event_and_see_it_in_stats(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(CustomerApp::class)
            ->call('logTestEvent', 'crash')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customer_app_events', ['type' => 'crash']);
        $this->assertSame(1, CustomerAppEvent::where('type', 'crash')->count());
    }
}
