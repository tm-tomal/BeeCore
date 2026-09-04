<?php

namespace Tests\Feature;

use App\Livewire\PlatformUsers;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\CreatesPlanSubscriptions;
use Tests\TestCase;

class SuperAdminControlsTest extends TestCase
{
    use RefreshDatabase, CreatesPlanSubscriptions;

    public function test_super_admin_can_open_platform_management_pages(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->get('/platform-users')->assertOk()->assertSee('Platform users');
        $this->actingAs($admin)->get('/audit-activity')->assertOk()->assertSee('Audit activity');
    }

    public function test_tenant_user_cannot_open_platform_management_pages(): void
    {
        $tenant = $this->tenant();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => User::ROLE_TENANT_ADMIN,
        ]);

        $this->actingAs($user)->get('/platform-users')->assertForbidden();
        $this->actingAs($user)->get('/audit-activity')->assertForbidden();
    }

    public function test_super_admin_can_create_a_tenant_user(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $this->attachActivePlan($tenant, staffLimit: 100);

        Livewire::actingAs($admin)
            ->test(PlatformUsers::class)
            ->set('name', 'Finance Operator')
            ->set('email', 'finance@example.test')
            ->set('password', 'password123')
            ->set('role', User::ROLE_FINANCE)
            ->set('tenantId', $tenant->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'finance@example.test',
            'role' => User::ROLE_FINANCE,
            'tenant_id' => $tenant->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'platform.user.created',
        ]);

        $createdUser = User::query()->where('email', 'finance@example.test')->firstOrFail();
        $passwordHash = $createdUser->password;

        Livewire::actingAs($admin)
            ->test(PlatformUsers::class)
            ->call('edit', $createdUser->id)
            ->set('name', 'Senior Finance Operator')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame($passwordHash, $createdUser->fresh()->password);
    }

    public function test_tenant_role_requires_a_workspace_and_admin_cannot_delete_self(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(PlatformUsers::class)
            ->set('name', 'Unassigned User')
            ->set('email', 'unassigned@example.test')
            ->set('password', 'password123')
            ->set('role', User::ROLE_SUPPORT)
            ->call('save')
            ->assertHasErrors(['tenantId']);

        Livewire::actingAs($admin)
            ->test(PlatformUsers::class)
            ->call('delete', $admin->id)
            ->assertStatus(422);

        Livewire::actingAs($admin)
            ->test(PlatformUsers::class)
            ->call('edit', $admin->id)
            ->set('role', User::ROLE_SUPPORT)
            ->set('tenantId', $this->tenant()->id)
            ->call('save')
            ->assertHasErrors(['role']);

        Livewire::actingAs($admin)
            ->test(PlatformUsers::class)
            ->call('edit', $admin->id)
            ->set('status', 'inactive')
            ->call('save')
            ->assertHasErrors(['status']);

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => User::ROLE_SUPER_ADMIN,
            'tenant_id' => null,
        ]);
    }

    private function tenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Managed ISP',
            'slug' => 'managed-isp',
            'status' => 'active',
            'currency' => 'BDT',
            'timezone' => 'Asia/Dhaka',
        ]);
    }
}