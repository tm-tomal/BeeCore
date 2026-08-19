<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PackagesBillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_packages_and_billing(): void
    {
        $tenant = Tenant::create(['name' => 'Demo', 'slug' => 'demo', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        $this->actingAs($user)
            ->get('/packages')
            ->assertSee('Packages & IP plans', false);

        $this->actingAs($user)
            ->get('/billing')
            ->assertSee('Invoices');
    }

    public function test_can_create_package_and_invoice(): void
    {
        $tenant = Tenant::create(['name' => 'Demo', 'slug' => 'demo', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);
        $customer = Customer::create(['tenant_id' => $tenant->id, 'name' => 'Test Cus', 'email' => 't@t.com', 'status' => 'active']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Packages::class)
            ->set('name', '10Mbps Dedicated')
            ->set('price', 2500)
            ->set('bandwidth', '10Mbps')
            ->set('type', 'dedicated_ip')
            ->call('save')
            ->assertHasNoErrors();
            
        $this->assertDatabaseHas('packages', [
            'tenant_id' => $tenant->id,
            'name' => '10Mbps Dedicated'
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Billing::class)
            ->set('customer_id', $customer->id)
            ->set('status', 'draft')
            ->set('items', [[
                'description' => '10Mbps Dedicated',
                'quantity' => 1,
                'unit_price' => 2500,
            ]])
            ->set('tax_amount', 0)
            ->set('due_date', now()->addDays(7)->format('Y-m-d'))
            ->call('save')
            ->assertHasNoErrors();
        
        $this->assertDatabaseHas('invoices', [
            'customer_id' => $customer->id,
            'total' => 2500
        ]);
    }
}
