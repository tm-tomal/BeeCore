<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_user_can_open_billing_but_not_network(): void
    {
        $user = $this->userWithRole(User::ROLE_FINANCE);

        $this->actingAs($user)->get('/billing')->assertOk();
        $this->actingAs($user)->get('/network')->assertForbidden();
    }

    public function test_support_user_can_open_customers_but_not_payments(): void
    {
        $user = $this->userWithRole(User::ROLE_SUPPORT);

        $this->actingAs($user)->get('/customers')->assertOk();
        $this->actingAs($user)->get('/payments')->assertForbidden();
    }

    public function test_network_engineer_can_open_network_but_not_resellers(): void
    {
        $user = $this->userWithRole(User::ROLE_NETWORK_ENGINEER);

        $this->actingAs($user)->get('/network')->assertOk();
        $this->actingAs($user)->get('/resellers')->assertForbidden();
    }

    private function userWithRole(string $role): User
    {
        $tenant = Tenant::create([
            'name' => 'Role Test ISP',
            'slug' => 'role-test-'.str_replace('_', '-', $role),
            'status' => 'active',
            'currency' => 'BDT',
            'timezone' => 'UTC',
        ]);

        return User::factory()->create(['tenant_id' => $tenant->id, 'role' => $role]);
    }
}