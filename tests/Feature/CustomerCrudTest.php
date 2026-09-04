<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\CreatesPlanSubscriptions;
use Tests\TestCase;

class CustomerCrudTest extends TestCase
{
    use RefreshDatabase, CreatesPlanSubscriptions;

    public function test_can_view_customers(): void
    {
        $tenant = Tenant::create(['name' => 'Demo', 'slug' => 'demo', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        $this->actingAs($user)
            ->get('/customers')
            ->assertSee('Customer directory');
    }

    public function test_can_create_customer(): void
    {
        $tenant = Tenant::create(['name' => 'Demo', 'slug' => 'demo', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);
        $this->attachActivePlan($tenant, customerLimit: 100);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Customers::class)
            ->set('name', 'John Doe')
            ->set('email', 'john@test.com')
            ->set('phone', '12345678')
            ->set('status', 'active')
            ->set('package_name', '10Mbps')
            ->call('save')
            ->assertHasNoErrors();
            
        $this->assertDatabaseHas('customers', [
            'tenant_id' => $tenant->id,
            'email' => 'john@test.com'
        ]);
    }
}
