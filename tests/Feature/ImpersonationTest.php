<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_impersonate_tenant(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::create(['name' => 'Demo ISP', 'slug' => 'demoisp', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Tenants::class)
            ->call('impersonate', $tenant->id)
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'action' => 'tenant.impersonation.started',
        ]);

        $this->actingAs($user)->get('/dashboard')
            ->assertSessionHas('impersonated_tenant_id', $tenant->id)
            ->assertSee('Viewing tenant')
            ->assertSee('Demo ISP')
            ->assertSee('Exit workspace');

        $this->actingAs($user)->get('/customers')->assertOk();
            
        // Test leaving impersonation restores context
        $this->actingAs($user)->get('/leave-impersonation')
             ->assertRedirect(route('tenants'))
             ->assertSessionMissing('impersonated_tenant_id');

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'action' => 'tenant.impersonation.ended',
        ]);
    }

    public function test_super_admin_can_login_to_tenant_workspace_from_platform_users(): void
    {
        $admin = User::factory()->create();
        $tenant = Tenant::create(['name' => 'Demo ISP', 'slug' => 'demoisp', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);
        $staff = User::factory()->create([
            'role' => User::ROLE_TENANT_ADMIN,
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\PlatformUsers::class)
            ->call('impersonateTenant', $staff->id)
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $tenant->id,
            'user_id' => $admin->id,
            'action' => 'tenant.impersonation.started',
        ]);

        $this->actingAs($admin)->get('/dashboard')
            ->assertSessionHas('impersonated_tenant_id', $tenant->id)
            ->assertSee('Demo ISP')
            ->assertSee('Exit workspace');

        $this->actingAs($admin)->get('/customers')->assertOk();
    }
}
