<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\AuthorizesRoles;
use App\Support\CurrentTenant;
use App\Support\PlanQuota;
use App\Support\TenantPermissions;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class IspTeam extends Component
{
    use AuthorizesRoles;

    public string $tab = 'members';
    public bool $showAddForm = false;

    public string $name = '';
    public string $email = '';
    public string $role = User::ROLE_FINANCE;
    public string $password = '';

    /** @var array<string, mixed>|null */
    public ?array $gateError = null;

    public function boot(): void
    {
        $this->authorizeRoles(User::ROLE_SUPER_ADMIN, User::ROLE_TENANT_ADMIN);
    }

    /**
     * Roles shown on the Team page. The workspace admin is presented as the
     * fixed "ISP Owner"; the remaining roles are assignable staff seats.
     */
    private function roleCatalog(): array
    {
        return [
            User::ROLE_TENANT_ADMIN => [
                'label' => 'ISP Owner',
                'description' => 'Owns this ISP workspace — full access including billing and team management.',
                'fixed' => true,
            ],
            User::ROLE_FINANCE => [
                'label' => 'Finance',
                'description' => 'Records payments, verifies transfers and manages billing.',
                'fixed' => false,
            ],
            User::ROLE_SUPPORT => [
                'label' => 'Support',
                'description' => 'Helps customers — tickets, problems and daily support work.',
                'fixed' => false,
            ],
            User::ROLE_NETWORK_ENGINEER => [
                'label' => 'Network engineer',
                'description' => 'Operates the network — devices, cable map and connectivity.',
                'fixed' => false,
            ],
        ];
    }

    private function assignableRoles(): array
    {
        $assignable = [];

        foreach ($this->roleCatalog() as $role => $meta) {
            if (! $meta['fixed']) {
                $assignable[$role] = $meta['label'];
            }
        }

        return $assignable;
    }

    public function switchTab(string $tab): void
    {
        $this->tab = in_array($tab, ['roles', 'members'], true) ? $tab : 'members';
    }

    public function openAdd(?string $role = null): void
    {
        $this->reset(['name', 'email', 'password', 'gateError']);
        $this->role = in_array($role, array_keys($this->assignableRoles()), true) ? $role : User::ROLE_FINANCE;
        $this->resetValidation();
        $this->showAddForm = true;
    }

    public function closeAdd(): void
    {
        $this->showAddForm = false;
        $this->reset(['name', 'email', 'password', 'gateError']);
        $this->resetValidation();
    }

    public function togglePermission(string $role, string $module): void
    {
        $tenant = app(CurrentTenant::class)->resolve();

        abort_unless($tenant, 403);
        abort_unless(in_array($role, array_keys($this->assignableRoles()), true), 403);

        $enabled = TenantPermissions::isEnabled($tenant->id, $role, $module);
        TenantPermissions::setEnabled($tenant->id, $role, $module, ! $enabled);

        AuditLog::record('tenant.role.permission_changed', null, [
            'role' => $role,
            'module' => $module,
            'enabled' => ! $enabled,
        ], tenantId: $tenant->id);

        session()->flash('message', 'Permissions updated.');
    }

    public function save(): void
    {
        $tenant = app(CurrentTenant::class)->resolve();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in(array_keys($this->assignableRoles()))],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $gate = $tenant ? PlanQuota::check($tenant, PlanQuota::STAFF) : ['allowed' => true];
        if (! $gate['allowed']) {
            $this->gateError = $gate;

            return;
        }

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'status' => 'active',
        ]);

        AuditLog::record('tenant.staff.created', $user, [
            'role' => $user->role,
            'email' => $user->email,
        ], tenantId: $tenant->id);

        $this->closeAdd();
        session()->flash('message', 'Team member added.');
    }

    public function toggleActive(int $userId): void
    {
        $tenant = app(CurrentTenant::class)->resolve();
        $user = $tenant->users()->findOrFail($userId);

        abort_if($user->id === auth()->id(), 422, 'You cannot change your own access.');
        abort_if($user->role === User::ROLE_TENANT_ADMIN, 422, 'The ISP Owner account is fixed — manage it from the BeeCore platform.');

        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);
        AuditLog::record('tenant.staff.status_changed', $user, ['status' => $user->status], tenantId: $tenant->id);
        session()->flash('message', $user->status === 'active' ? 'Team member activated.' : 'Team member deactivated.');
    }

    public function remove(int $userId): void
    {
        $tenant = app(CurrentTenant::class)->resolve();
        $user = $tenant->users()->findOrFail($userId);

        abort_if($user->id === auth()->id(), 422, 'You cannot remove your own account.');
        abort_if($user->role === User::ROLE_TENANT_ADMIN, 422, 'The ISP Owner account is fixed — manage it from the BeeCore platform.');

        AuditLog::record('tenant.staff.removed', $user, ['email' => $user->email], tenantId: $tenant->id);
        $user->delete();
        session()->flash('message', 'Team member removed.');
    }

    public function render()
    {
        $tenant = app(CurrentTenant::class)->resolve();
        $roleCatalog = $this->roleCatalog();

        $members = $tenant
            ? $tenant->users()
                ->whereIn('role', array_keys($roleCatalog))
                ->orderByRaw("(status = 'active') DESC")
                ->orderBy('name')
                ->get()
            : collect();

        $usage = $tenant ? PlanQuota::usage($tenant, PlanQuota::STAFF) : 0;
        $subscription = $tenant?->saasSubscriptions()
            ->whereIn('status', PlanQuota::ENTITLED_STATUSES)
            ->latest('id')
            ->first();
        $limit = $subscription?->plan?->staff_limit;

        $permissions = $tenant
            ? collect(TenantPermissions::STAFF_ROLES)
                ->mapWithKeys(fn (string $role) => [$role => TenantPermissions::roleModules($tenant->id, $role)])
                ->all()
            : [];

        return view('livewire.isp-team', [
            'tenant' => $tenant,
            'members' => $members,
            'membersByRole' => $members->groupBy('role'),
            'roleCatalog' => $roleCatalog,
            'permissions' => $permissions,
            'permissionCatalog' => TenantPermissions::catalog(),
            'currentUser' => auth()->user(),
            'usage' => $usage,
            'limit' => $limit,
            'planName' => $subscription?->plan?->name,
        ]);
    }
}
