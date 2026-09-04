<?php

namespace Tests\Feature;

use App\Livewire\Customers;
use App\Livewire\IspTeam;
use App\Livewire\Resellers;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\CreatesPlanSubscriptions;
use Tests\TestCase;

class PlanRestrictionsTest extends TestCase
{
    use RefreshDatabase, CreatesPlanSubscriptions;

    private function tenantActor(string $suffix): array
    {
        $tenant = Tenant::create([
            'name' => 'Plan ISP',
            'slug' => 'plan-isp-'.$suffix,
            'status' => 'active',
            'currency' => 'BDT',
            'timezone' => 'UTC',
            'operation_mode' => 'automatic',
        ]);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        return [$tenant, $user];
    }

    public function test_customer_creation_is_blocked_without_an_active_plan(): void
    {
        [$tenant, $user] = $this->tenantActor('no-plan');

        Livewire::actingAs($user)
            ->test(Customers::class)
            ->set('name', 'Blocked Customer')
            ->set('email', 'blocked@test.com')
            ->set('status', 'active')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('does not have an active BeeCore plan');

        $this->assertDatabaseMissing('customers', ['tenant_id' => $tenant->id, 'email' => 'blocked@test.com']);
    }

    public function test_customer_creation_is_blocked_when_plan_limit_is_reached(): void
    {
        [$tenant, $user] = $this->tenantActor('at-limit');
        $this->attachActivePlan($tenant, customerLimit: 1);
        Customer::create(['tenant_id' => $tenant->id, 'name' => 'First', 'email' => 'first@test.com', 'status' => 'active']);

        Livewire::actingAs($user)
            ->test(Customers::class)
            ->set('name', 'Second Customer')
            ->set('email', 'second@test.com')
            ->set('status', 'active')
            ->call('save')
            ->assertSee('Upgrade your plan to add more');

        $this->assertDatabaseMissing('customers', ['tenant_id' => $tenant->id, 'email' => 'second@test.com']);
    }

    public function test_customer_creation_is_allowed_within_the_plan_limit(): void
    {
        [$tenant, $user] = $this->tenantActor('within-limit');
        $this->attachActivePlan($tenant, customerLimit: 5);

        Livewire::actingAs($user)
            ->test(Customers::class)
            ->set('name', 'Allowed Customer')
            ->set('email', 'allowed@test.com')
            ->set('status', 'active')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customers', ['tenant_id' => $tenant->id, 'email' => 'allowed@test.com']);
        $this->assertFalse(session()->has('plan_error'));
    }

    public function test_reseller_creation_is_blocked_without_an_active_plan(): void
    {
        [$tenant, $user] = $this->tenantActor('reseller-no-plan');

        Livewire::actingAs($user)
            ->test(Resellers::class)
            ->set('name', 'Blocked Partner')
            ->set('email', 'partner@test.com')
            ->set('status', 'active')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('does not have an active BeeCore plan');

        $this->assertDatabaseMissing('resellers', ['tenant_id' => $tenant->id, 'email' => 'partner@test.com']);
    }

    public function test_tenant_admin_can_add_staff_within_the_staff_limit(): void
    {
        [$tenant, $user] = $this->tenantActor('team-ok');
        $this->attachActivePlan($tenant, staffLimit: 10);

        Livewire::actingAs($user)
            ->test(IspTeam::class)
            ->set('name', 'Finance Officer')
            ->set('email', 'finance@team.test')
            ->set('role', User::ROLE_FINANCE)
            ->set('password', 'secret123')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'tenant_id' => $tenant->id,
            'email' => 'finance@team.test',
            'role' => User::ROLE_FINANCE,
            'status' => 'active',
        ]);
    }

    public function test_team_staff_creation_is_blocked_when_staff_limit_is_reached(): void
    {
        [$tenant, $user] = $this->tenantActor('team-full');
        $this->attachActivePlan($tenant, staffLimit: 1);

        $testable = Livewire::actingAs($user)->test(IspTeam::class)
            ->set('name', 'Extra Member')
            ->set('email', 'extra@team.test')
            ->set('role', User::ROLE_SUPPORT)
            ->set('password', 'secret123')
            ->call('save');

        $this->assertSame('limit', $testable->get('gateError')['reason']);
        $this->assertDatabaseMissing('users', ['tenant_id' => $tenant->id, 'email' => 'extra@team.test']);
    }

    public function test_team_staff_creation_is_blocked_without_an_active_plan(): void
    {
        [$tenant, $user] = $this->tenantActor('team-no-plan');

        $testable = Livewire::actingAs($user)->test(IspTeam::class)
            ->set('name', 'No Plan Member')
            ->set('email', 'noplan@team.test')
            ->set('role', User::ROLE_SUPPORT)
            ->set('password', 'secret123')
            ->call('save');

        $this->assertSame('no_plan', $testable->get('gateError')['reason']);
        $this->assertDatabaseMissing('users', ['tenant_id' => $tenant->id, 'email' => 'noplan@team.test']);
    }
}
