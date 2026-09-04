<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\User;
use App\Support\PlanQuota;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PlatformUsers extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public ?int $userId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = User::ROLE_TENANT_ADMIN;
    public string $status = 'active';
    public ?int $tenantId = null;
    public string $search = '';

    public function create(): void
    {
        $this->assertSuperAdmin();
        $this->reset(['userId', 'name', 'email', 'password', 'tenantId']);
        $this->role = User::ROLE_TENANT_ADMIN;
        $this->status = 'active';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $this->assertSuperAdmin();
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->role;
        $this->status = $user->status;
        $this->tenantId = $user->tenant_id;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->assertSuperAdmin();

        $roles = [
            User::ROLE_SUPER_ADMIN,
            User::ROLE_TENANT_ADMIN,
            User::ROLE_FINANCE,
            User::ROLE_SUPPORT,
            User::ROLE_NETWORK_ENGINEER,
            User::ROLE_RESELLER,
        ];

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->userId)],
            'password' => [$this->userId ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ['required', Rule::in($roles)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'tenantId' => [Rule::requiredIf($this->role !== User::ROLE_SUPER_ADMIN), 'nullable', 'integer', 'exists:tenants,id'],
        ]);

        if ($this->userId === auth()->id() && $validated['role'] !== User::ROLE_SUPER_ADMIN) {
            $this->addError('role', 'You cannot remove your own Super Admin access.');

            return;
        }

        if ($this->userId === auth()->id() && $validated['status'] !== 'active') {
            $this->addError('status', 'You cannot deactivate your own account.');

            return;
        }

        // A tenant cannot receive staff beyond its BeeCore plan's staff limit.
        $staffRoles = [User::ROLE_TENANT_ADMIN, User::ROLE_FINANCE, User::ROLE_SUPPORT, User::ROLE_NETWORK_ENGINEER];
        if (! $this->userId && in_array($validated['role'], $staffRoles, true) && filled($validated['tenantId'])) {
            $tenant = Tenant::find($validated['tenantId']);
            if ($tenant) {
                $gate = PlanQuota::check($tenant, PlanQuota::STAFF);
                if (! $gate['allowed']) {
                    $this->showModal = false;
                    session()->flash('plan_error', $gate + [
                        'actionUrl' => route('tenant-details', $tenant),
                        'actionLabel' => __('Open tenant subscription'),
                    ]);

                    return;
                }
            }
        }

        $attributes = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'status' => $validated['status'],
            'tenant_id' => $validated['role'] === User::ROLE_SUPER_ADMIN ? null : $validated['tenantId'],
        ];

        if (filled($validated['password'] ?? null)) {
            $attributes['password'] = $validated['password'];
        }

        $user = $this->userId ? User::findOrFail($this->userId) : new User();
        $user->fill($attributes)->save();
        AuditLog::record($this->userId ? 'platform.user.updated' : 'platform.user.created', $user, [
            'role' => $user->role,
            'tenant_id' => $user->tenant_id,
        ]);

        $this->showModal = false;
        session()->flash('message', $this->userId ? 'User updated.' : 'User created.');
    }

    public function impersonateTenant(int $id)
    {
        $this->assertSuperAdmin();

        $target = User::findOrFail($id);
        abort_unless($target->tenant_id, 422, 'This account is not attached to a tenant workspace.');
        abort_unless($target->status === 'active', 422, 'This account is inactive.');

        $tenant = Tenant::query()->where('status', 'active')->findOrFail($target->tenant_id);

        session()->put('impersonated_tenant_id', $tenant->id);
        session()->put('impersonated_tenant_name', $tenant->name);
        session()->migrate(true);
        AuditLog::record('tenant.impersonation.started', $tenant, tenantId: $tenant->id);

        return redirect()->route('dashboard');
    }

    public function delete(int $id): void
    {
        $this->assertSuperAdmin();
        abort_if($id === auth()->id(), 422, 'You cannot deactivate your own account.');
        $user = User::findOrFail($id);
        $user->update(['status' => 'inactive']);
        AuditLog::record('platform.user.deactivated', $user, ['email' => $user->email]);
        session()->flash('message', 'User deactivated.');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->assertSuperAdmin();

        return view('livewire.platform-users', [
            'users' => User::query()
                ->with('tenant')
                ->when($this->search, fn ($query) => $query->where(fn ($nested) => $nested
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")))
                ->latest()
                ->paginate(12),
            'tenants' => Tenant::query()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}