<?php

namespace Tests\Feature;

use App\Livewire\IspTeam;
use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\TenantRolePermission;
use App\Models\User;
use App\Support\TenantPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TenantRolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(string $slug): Tenant
    {
        return Tenant::create(['name' => 'PermCo', 'slug' => $slug, 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);
    }

    private function owner(Tenant $tenant): User
    {
        return User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN, 'email' => 'owner@perm.test']);
    }

    private function staff(Tenant $tenant, string $role, string $email): User
    {
        return User::factory()->create(['tenant_id' => $tenant->id, 'role' => $role, 'email' => $email]);
    }

    public function test_staff_default_modules_match_legacy_access(): void
    {
        $tenant = $this->tenant('perm-defaults');

        $this->assertTrue(TenantPermissions::isEnabled($tenant->id, User::ROLE_FINANCE, 'billing'));
        $this->assertFalse(TenantPermissions::isEnabled($tenant->id, User::ROLE_FINANCE, 'customers'));
        $this->assertTrue(TenantPermissions::isEnabled($tenant->id, User::ROLE_SUPPORT, 'customers'));
        $this->assertTrue(TenantPermissions::isEnabled($tenant->id, User::ROLE_NETWORK_ENGINEER, 'network'));
        $this->assertFalse(TenantPermissions::isEnabled($tenant->id, User::ROLE_NETWORK_ENGINEER, 'billing'));
    }

    public function test_finance_can_open_billing_but_not_customers_by_default(): void
    {
        $tenant = $this->tenant('perm-finance');
        $finance = $this->staff($tenant, User::ROLE_FINANCE, 'finance@perm.test');

        $this->actingAs($finance)
            ->get(route('billing'))
            ->assertOk();

        $this->actingAs($finance)
            ->get(route('customers'))
            ->assertForbidden();
    }

    public function test_owner_grant_opens_a_module_and_revoke_closes_it(): void
    {
        $tenant = $this->tenant('perm-grant');
        $finance = $this->staff($tenant, User::ROLE_FINANCE, 'grant@perm.test');

        TenantPermissions::setEnabled($tenant->id, User::ROLE_FINANCE, 'customers', true);

        $this->actingAs($finance)
            ->get(route('customers'))
            ->assertOk();

        TenantPermissions::setEnabled($tenant->id, User::ROLE_FINANCE, 'customers', false);

        $this->actingAs($finance)
            ->get(route('customers'))
            ->assertForbidden();
    }

    public function test_owner_toggles_permissions_from_the_team_page(): void
    {
        $tenant = $this->tenant('perm-team-toggle');
        $owner = $this->owner($tenant);
        $this->staff($tenant, User::ROLE_FINANCE, 'member@perm.test');

        Livewire::actingAs($owner)
            ->test(IspTeam::class)
            ->call('switchTab', 'roles')
            ->assertSet('tab', 'roles')
            ->call('togglePermission', User::ROLE_FINANCE, 'billing');

        $this->assertDatabaseHas('tenant_role_permissions', [
            'tenant_id' => $tenant->id,
            'role' => User::ROLE_FINANCE,
            'module' => 'billing',
            'allowed' => false,
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'tenant.role.permission_changed']);

        // Toggle back on.
        Livewire::actingAs($owner)
            ->test(IspTeam::class)
            ->call('togglePermission', User::ROLE_FINANCE, 'billing');

        $this->assertTrue(TenantPermissions::isEnabled($tenant->id, User::ROLE_FINANCE, 'billing'));
    }

    public function test_support_and_engineer_defaults_keep_their_pages_open(): void
    {
        $tenant = $this->tenant('perm-staff-open');
        $support = $this->staff($tenant, User::ROLE_SUPPORT, 'support@perm.test');
        $engineer = $this->staff($tenant, User::ROLE_NETWORK_ENGINEER, 'eng@perm.test');

        $this->actingAs($support)->get(route('customers'))->assertOk();
        $this->actingAs($support)->get(route('issues'))->assertOk();
        $this->actingAs($engineer)->get(route('network'))->assertOk();
        $this->actingAs($engineer)->get(route('cable-map'))->assertOk();
        $this->actingAs($engineer)->get(route('billing'))->assertForbidden();
    }
}
