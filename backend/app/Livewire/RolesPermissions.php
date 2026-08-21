<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class RolesPermissions extends Component
{
    public string $tab = 'roles';

    // Role form
    public string $viewMode = 'index';
    public ?int $roleId = null;
    public string $roleName = '';
    public string $roleKey = '';
    public string $roleDescription = '';

    public ?int $managingRoleId = null;

    // Permission form
    public string $permissionViewMode = 'index';
    public string $permissionName = '';
    public string $permissionKey = '';
    public string $permissionCategory = 'other';
    public string $permissionScope = 'tenant';

    public function createRole(): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $this->reset(['roleId', 'roleName', 'roleKey', 'roleDescription']);
        $this->viewMode = 'create';
    }

    public function editRole(int $id): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $role = Role::findOrFail($id);
        $this->roleId = $role->id;
        $this->roleName = $role->name;
        $this->roleKey = $role->key;
        $this->roleDescription = $role->description ?? '';
        $this->viewMode = 'create';
    }

    public function cancelRoleForm(): void
    {
        $this->viewMode = 'index';
    }

    public function saveRole(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate([
            'roleName' => ['required', 'string', 'max:255'],
            'roleKey' => ['required', 'string', 'max:255', Rule::unique('roles', 'key')->ignore($this->roleId)],
            'roleDescription' => ['nullable', 'string', 'max:500'],
        ]);

        $role = $this->roleId ? Role::findOrFail($this->roleId) : new Role(['is_system' => false]);
        $role->fill([
            'name' => $data['roleName'],
            'key' => Str::slug($data['roleKey'], '_'),
            'description' => $data['roleDescription'] ?: null,
        ])->save();

        AuditLog::record($this->roleId ? 'role.updated' : 'role.created', $role);

        $this->viewMode = 'index';
        session()->flash('message', $this->roleId ? 'Role updated.' : 'Role created.');
    }

    public function deleteRole(int $id): void
    {
        $this->assertSuperAdmin();
        $role = Role::findOrFail($id);
        abort_if($role->is_system, 422, 'System roles cannot be deleted.');

        $role->delete();
        AuditLog::record('role.deleted', null, ['key' => $role->key]);
        session()->flash('message', 'Role deleted.');
    }

    public function manageRole(int $id): void
    {
        $this->managingRoleId = $id;
    }

    public function closeManageRole(): void
    {
        $this->managingRoleId = null;
    }

    public function togglePermission(int $roleId, int $permissionId): void
    {
        $this->assertSuperAdmin();
        $role = Role::findOrFail($roleId);
        $permission = Permission::findOrFail($permissionId);

        if ($role->permissions()->where('permission_id', $permissionId)->exists()) {
            $role->permissions()->detach($permission);
            $action = 'removed';
        } else {
            $role->permissions()->attach($permission);
            $action = 'assigned';
        }

        AuditLog::record('role.permission_'.$action, $role, ['permission' => $permission->key]);
        session()->flash('message', 'Permission '.$action.' for '.$role->name.'.');
    }

    // --- Permissions catalog ---

    public function createPermission(): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $this->reset(['permissionName', 'permissionKey']);
        $this->tab = 'permissions';
        $this->permissionCategory = 'other';
        $this->permissionScope = 'tenant';
        $this->permissionViewMode = 'create';
    }

    public function cancelPermissionForm(): void
    {
        $this->permissionViewMode = 'index';
    }

    public function savePermission(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate([
            'permissionName' => ['required', 'string', 'max:255'],
            'permissionKey' => ['required', 'string', 'max:255', 'unique:permissions,key'],
            'permissionCategory' => ['required', Rule::in(['financial', 'network', 'security', 'audit', 'tenant', 'other'])],
            'permissionScope' => ['required', Rule::in(['platform', 'tenant'])],
        ]);

        $permission = Permission::create([
            'key' => trim($data['permissionKey']),
            'name' => $data['permissionName'],
            'category' => $data['permissionCategory'],
            'scope' => $data['permissionScope'],
        ]);

        AuditLog::record('permission.created', $permission);

        $this->permissionViewMode = 'index';
        session()->flash('message', 'Permission created.');
    }

    public function render()
    {
        $this->assertSuperAdmin();

        return view('livewire.roles-permissions', [
            'roles' => Role::query()->withCount('permissions')->orderBy('name')->get(),
            'permissions' => Permission::query()->orderBy('category')->orderBy('name')->get(),
            'managingRole' => $this->managingRoleId ? Role::with('permissions')->find($this->managingRoleId) : null,
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
