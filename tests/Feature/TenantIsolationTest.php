<?php

namespace Tests\Feature;

use App\Livewire\Billing;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_must_select_a_tenant_before_opening_workspace(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/customers')
            ->assertForbidden();
    }

    public function test_tenant_user_cannot_open_saas_tenant_management(): void
    {
        [$tenant, $user] = $this->tenantActor('alpha');

        $this->actingAs($user)->get('/tenants')->assertForbidden();
    }

    public function test_customer_list_only_contains_current_tenant_records(): void
    {
        [$tenant, $user] = $this->tenantActor('alpha');
        $otherTenant = $this->tenant('beta');
        Customer::create(['tenant_id' => $tenant->id, 'name' => 'Visible Customer', 'email' => 'visible@test.com', 'status' => 'active']);
        Customer::create(['tenant_id' => $otherTenant->id, 'name' => 'Hidden Customer', 'email' => 'hidden@test.com', 'status' => 'active']);

        $this->actingAs($user)
            ->get('/customers')
            ->assertOk()
            ->assertSee('Visible Customer')
            ->assertDontSee('Hidden Customer');
    }

    public function test_invoice_rejects_customer_from_another_tenant(): void
    {
        [$tenant, $user] = $this->tenantActor('alpha');
        $otherCustomer = Customer::create([
            'tenant_id' => $this->tenant('beta')->id,
            'name' => 'Other Customer',
            'email' => 'other@test.com',
            'status' => 'active',
        ]);

        Livewire::actingAs($user)
            ->test(Billing::class)
            ->set('customer_id', $otherCustomer->id)
            ->set('status', 'draft')
            ->set('subtotal', 100)
            ->set('tax_amount', 0)
            ->set('due_date', now()->addDay()->format('Y-m-d'))
            ->call('save')
            ->assertHasErrors(['customer_id']);

        $this->assertDatabaseCount('invoices', 0);
    }

    private function tenantActor(string $slug): array
    {
        $tenant = $this->tenant($slug);
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => User::ROLE_TENANT_ADMIN,
        ]);

        return [$tenant, $user];
    }

    private function tenant(string $slug): Tenant
    {
        return Tenant::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'status' => 'active',
            'currency' => 'BDT',
            'timezone' => 'UTC',
        ]);
    }
}