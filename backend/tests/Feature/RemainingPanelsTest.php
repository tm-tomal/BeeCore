<?php
namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemainingPanelsTest extends TestCase {
    use RefreshDatabase;

    public function test_all_panels_load(): void {
        $tenant = Tenant::create(['name' => 'Demo', 'slug' => 'demo', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        $this->actingAs($user)->get('/payments')->assertSee('Payments');
        $this->actingAs($user)->get('/network')->assertSee('Network devices');
        $this->actingAs($user)->get('/resellers')->assertSee('Resellers');
        $this->actingAs($user)->get('/reports')->assertSee('Business reports');
    }
}
