<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\AuthorizesRoles;
use App\Support\CurrentTenant;
use App\Support\PlanQuota;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class IspTeam extends Component
{
    use AuthorizesRoles;

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

    /** Roles a workspace admin may assign to team members. */
    private function assignableRoles(): array
    {
        return [
            User::ROLE_TENANT_ADMIN => 'Workspace admin',
            User::ROLE_FINANCE => 'Finance',
            User::ROLE_SUPPORT => 'Support',
            User::ROLE_NETWORK_ENGINEER => 'Network engineer',
        ];
    }

    public function openForm(): void
    {
        $this->reset(['name', 'email', 'password', 'gateError']);
        $this->role = User::ROLE_FINANCE;
        $this->resetValidation();
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

        $this->reset(['name', 'email', 'password', 'gateError']);
        $this->role = User::ROLE_FINANCE;
        session()->flash('message', 'Team member added.');
    }

    public function toggleActive(int $userId): void
    {
        $tenant = app(CurrentTenant::class)->resolve();
        $user = $tenant->users()->findOrFail($userId);

        abort_if($user->id === auth()->id(), 422, 'You cannot change your own access.');

        if ($user->role === User::ROLE_TENANT_ADMIN && $user->status === 'active') {
            $activeAdmins = $tenant->users()
                ->where('role', User::ROLE_TENANT_ADMIN)
                ->where('status', 'active')
                ->whereKeyNot($user->id)
                ->count();
            abort_if($activeAdmins === 0, 422, 'You cannot deactivate the last active workspace admin.');
        }

        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);
        AuditLog::record('tenant.staff.status_changed', $user, ['status' => $user->status], tenantId: $tenant->id);
        session()->flash('message', $user->status === 'active' ? 'Team member activated.' : 'Team member deactivated.');
    }

    public function remove(int $userId): void
    {
        $tenant = app(CurrentTenant::class)->resolve();
        $user = $tenant->users()->findOrFail($userId);

        abort_if($user->id === auth()->id(), 422, 'You cannot remove your own account.');

        if ($user->role === User::ROLE_TENANT_ADMIN) {
            $activeAdmins = $tenant->users()
                ->where('role', User::ROLE_TENANT_ADMIN)
                ->where('status', 'active')
                ->whereKeyNot($user->id)
                ->count();
            abort_if($activeAdmins === 0, 422, 'You cannot remove the last active workspace admin.');
        }

        AuditLog::record('tenant.staff.removed', $user, ['email' => $user->email], tenantId: $tenant->id);
        $user->delete();
        session()->flash('message', 'Team member removed.');
    }

    public function render()
    {
        $tenant = app(CurrentTenant::class)->resolve();
        $roleLabels = $this->assignableRoles();

        $members = $tenant
            ? $tenant->users()
                ->whereIn('role', array_keys($roleLabels))
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

        return view('livewire.isp-team', [
            'tenant' => $tenant,
            'members' => $members,
            'roleLabels' => $roleLabels,
            'currentUser' => auth()->user(),
            'usage' => $usage,
            'limit' => $limit,
            'planName' => $subscription?->plan?->name,
        ]);
    }
}
