<?php

namespace Tests\Feature;

use App\Livewire\IspTeam;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IspTeamTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(string $slug): Tenant
    {
        return Tenant::create(['name' => 'TeamCo', 'slug' => $slug, 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);
    }

    private function owner(Tenant $tenant, string $email = 'owner@team.test'): User
    {
        return User::factory()->create(['tenant_id' => $tenant->id, 'email' => $email, 'role' => User::ROLE_TENANT_ADMIN]);
    }

    public function test_team_page_shows_roles_and_members_tabs_with_owner_separated(): void
    {
        $tenant = $this->tenant('roles-tabs');
        $admin = $this->owner($tenant, 'a@team.test');

        Livewire::actingAs($admin)
            ->test(IspTeam::class)
            ->assertOk()
            ->assertSee('Team & roles')
            ->assertSee('Members')
            ->assertSee('Roles')
            ->assertSee('ISP Owner')
            ->assertSee('Add member');
    }

    public function test_roles_tab_lists_every_role_card_and_assigns_members(): void
    {
        $tenant = $this->tenant('roles-tab-list');
        $admin = $this->owner($tenant);

        User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_FINANCE, 'name' => 'Finance Officer', 'email' => 'finance@x.test']);
        User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_SUPPORT, 'name' => 'Support Rep', 'email' => 'support@x.test']);

        Livewire::actingAs($admin)
            ->test(IspTeam::class)
            ->call('switchTab', 'roles')
            ->assertSet('tab', 'roles')
            ->assertSee('Finance')
            ->assertSee('Support')
            ->assertSee('Network engineer')
            ->assertSee('Assign member')
            ->assertSee('Finance Officer')
            ->assertSee('Support Rep');
    }

    public function test_owner_account_cannot_be_assigned_via_add_form(): void
    {
        $tenant = $this->tenant('owner-fixed');
        $admin = $this->owner($tenant);

        Livewire::actingAs($admin)
            ->test(IspTeam::class)
            ->set('name', 'Fake Owner')
            ->set('email', 'fakeowner@x.test')
            ->set('role', User::ROLE_TENANT_ADMIN)
            ->set('password', 'secret123')
            ->call('save')
            ->assertHasErrors('role');
    }

    public function test_owner_cannot_deactivate_another_owner(): void
    {
        $tenant = $this->tenant('owner-guard');
        $acting = $this->owner($tenant, 'acting@team.test');
        $otherOwner = $this->owner($tenant, 'other@team.test');

        Livewire::actingAs($acting)
            ->test(IspTeam::class)
            ->call('toggleActive', $otherOwner->id);

        // The guard blocks the change — the owner stays active.
        $this->assertSame('active', $otherOwner->fresh()->status);
    }

    public function test_staff_can_be_deactivated_and_reactivated(): void
    {
        $tenant = $this->tenant('staff-toggle');
        $acting = $this->owner($tenant);
        $staff = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_FINANCE, 'name' => 'Staff', 'email' => 'staff@x.test']);

        Livewire::actingAs($acting)
            ->test(IspTeam::class)
            ->call('toggleActive', $staff->id)
            ->call('toggleActive', $staff->id);

        $this->assertSame('active', $staff->fresh()->status);
    }
}
