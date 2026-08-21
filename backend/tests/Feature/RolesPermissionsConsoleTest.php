<?php

namespace Tests\Feature;

use App\Livewire\RolesPermissions;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RolesPermissionsConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_system_roles_and_permissions_exist(): void
    {
        $this->assertDatabaseHas('roles', ['key' => 'super_admin', 'is_system' => true]);
        $this->assertDatabaseHas('roles', ['key' => 'tenant_admin', 'is_system' => true]);
        $this->assertDatabaseHas('permissions', ['key' => 'billing.manage']);

        $superAdmin = Role::where('key', 'super_admin')->firstOrFail();
        $this->assertSame(Permission::count(), $superAdmin->permissions()->count());
    }

    public function test_super_admin_can_create_a_custom_role_and_delete_it(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(RolesPermissions::class)
            ->call('createRole')
            ->set('roleName', 'Regional Manager')
            ->set('roleKey', 'regional manager')
            ->call('saveRole')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('roles', ['key' => 'regional_manager', 'is_system' => false]);

        $role = Role::where('key', 'regional_manager')->firstOrFail();
        Livewire::actingAs($admin)->test(RolesPermissions::class)->call('deleteRole', $role->id);
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_system_roles_cannot_be_deleted(): void
    {
        $admin = User::factory()->create();
        $role = Role::where('key', 'finance')->firstOrFail();

        try {
            Livewire::actingAs($admin)->test(RolesPermissions::class)->call('deleteRole', $role->id);
        } catch (\Throwable $e) {
            // Guarded by abort_if; either the exception surfaces or the row stays.
        }

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_super_admin_can_assign_and_remove_a_permission_from_a_role(): void
    {
        $admin = User::factory()->create();
        $role = Role::where('key', 'support')->firstOrFail();
        $permission = Permission::where('key', 'network.view')->firstOrFail();

        Livewire::actingAs($admin)->test(RolesPermissions::class)
            ->call('togglePermission', $role->id, $permission->id)
            ->assertHasNoErrors();
        $this->assertTrue($role->fresh()->permissions()->where('permission_id', $permission->id)->exists());

        Livewire::actingAs($admin)->test(RolesPermissions::class)
            ->call('togglePermission', $role->id, $permission->id)
            ->assertHasNoErrors();
        $this->assertFalse($role->fresh()->permissions()->where('permission_id', $permission->id)->exists());
    }

    public function test_super_admin_can_create_a_custom_permission(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(RolesPermissions::class)
            ->call('createPermission')
            ->set('permissionName', 'Manage white label')
            ->set('permissionKey', 'white_label.manage')
            ->set('permissionCategory', 'tenant')
            ->set('permissionScope', 'tenant')
            ->call('savePermission')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('permissions', ['key' => 'white_label.manage', 'category' => 'tenant']);
    }
}
