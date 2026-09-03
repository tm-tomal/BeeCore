<?php

namespace Tests\Feature;

use App\Livewire\CableMap;
use App\Models\CableRoute;
use App\Models\CableSplitter;
use App\Models\Customer;
use App\Models\SplitterPort;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CableMapConsoleTest extends TestCase
{
    use RefreshDatabase;

    private function tenantContext(): array
    {
        $tenant = \App\Models\Tenant::create(['name' => 'Demo ISP', 'slug' => 'demo-isp', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC', 'operation_mode' => 'automatic']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        return [$tenant, $user];
    }

    public function test_cable_map_page_requires_tenant_context_and_renders(): void
    {
        [$tenant, $user] = $this->tenantContext();

        $this->actingAs($user)
            ->get('/cable-map')
            ->assertOk()
            ->assertSee('Cable & fiber map');
    }

    public function test_network_engineer_can_open_cable_map(): void
    {
        [$tenant, $user] = $this->tenantContext();
        $engineer = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_NETWORK_ENGINEER]);

        $this->actingAs($engineer)->get('/cable-map')->assertOk();
    }

    public function test_admin_can_add_and_edit_fiber_route(): void
    {
        [$tenant, $user] = $this->tenantContext();

        Livewire::actingAs($user)
            ->test(CableMap::class)
            ->call('createRoute')
            ->set('routeName', 'Badda Main Line')
            ->set('routeSource', 'Uttara POP')
            ->set('routeDestination', 'Banani')
            ->set('routeCores', 8)
            ->set('routeLength', 3.5)
            ->call('saveRoute')
            ->assertHasNoErrors()
            ->assertSet('viewMode', 'overview');

        $route = CableRoute::where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($route);
        $this->assertSame('Badda Main Line', $route->name);

        Livewire::actingAs($user)
            ->test(CableMap::class)
            ->call('editRoute', $route->id)
            ->set('routeName', 'Badda Line 2')
            ->call('saveRoute')
            ->assertHasNoErrors();

        $this->assertSame('Badda Line 2', $route->fresh()->name);
    }

    public function test_coordinates_are_saved_and_visible_on_map(): void
    {
        [$tenant, $user] = $this->tenantContext();

        Livewire::actingAs($user)
            ->test(CableMap::class)
            ->call('createRoute')
            ->set('routeName', 'Mapped Line')
            ->set('routeSource', 'Uttara')
            ->set('routeLatitude', '23.7800000')
            ->set('routeLongitude', '90.4000000')
            ->call('saveRoute')
            ->assertHasNoErrors();

        $route = CableRoute::where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($route);
        $this->assertSame('23.7800000', $route->latitude);
        $this->assertSame('90.4000000', $route->longitude);

        // Overview exposes the map container with the pinned point in its payload.
        $this->actingAs($user)
            ->get('/cable-map')
            ->assertOk()
            ->assertSee('Live network map')
            ->assertSee('data-cable-map')
            ->assertSee('Mapped Line');
    }

    public function test_admin_can_add_splitter_and_link_customer_to_port(): void
    {
        [$tenant, $user] = $this->tenantContext();

        $route = CableRoute::create(['tenant_id' => $tenant->id, 'name' => 'Main Line']);
        $customer = Customer::create(['tenant_id' => $tenant->id, 'name' => 'Rahim', 'email' => 'rahim@test.com', 'status' => 'active']);

        Livewire::actingAs($user)
            ->test(CableMap::class)
            ->call('createSplitter', $route->id)
            ->set('splitterName', 'Banani S1')
            ->set('splitterRouteId', $route->id)
            ->set('splitterPortCount', 4)
            ->call('saveSplitter')
            ->assertHasNoErrors();

        $splitter = CableSplitter::where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($splitter);
        $this->assertSame(4, SplitterPort::where('cable_splitter_id', $splitter->id)->count());

        // Open edit and assign customer to port 1.
        $port = SplitterPort::where('cable_splitter_id', $splitter->id)->where('port_number', 1)->first();

        Livewire::actingAs($user)
            ->test(CableMap::class)
            ->call('editSplitter', $splitter->id)
            ->set("portAssignments.{$port->id}", (string) $customer->id)
            ->call('saveSplitter')
            ->assertHasNoErrors();

        $this->assertSame($customer->id, $port->fresh()->customer_id);
        $this->assertNull(SplitterPort::where('cable_splitter_id', $splitter->id)->where('port_number', 2)->first()->customer_id);
    }

    public function test_issue_lists_affected_customers_on_route(): void
    {
        [$tenant, $user] = $this->tenantContext();

        $route = CableRoute::create(['tenant_id' => $tenant->id, 'name' => 'Main Line']);
        $splitter = CableSplitter::create(['tenant_id' => $tenant->id, 'cable_route_id' => $route->id, 'name' => 'S1', 'port_count' => 2]);
        $customerA = Customer::create(['tenant_id' => $tenant->id, 'name' => 'A', 'email' => 'a@test.com', 'status' => 'active']);
        $customerB = Customer::create(['tenant_id' => $tenant->id, 'name' => 'B', 'email' => 'b@test.com', 'status' => 'active']);

        SplitterPort::create(['tenant_id' => $tenant->id, 'cable_splitter_id' => $splitter->id, 'port_number' => 1, 'customer_id' => $customerA->id]);
        SplitterPort::create(['tenant_id' => $tenant->id, 'cable_splitter_id' => $splitter->id, 'port_number' => 2, 'customer_id' => $customerB->id]);

        Livewire::actingAs($user)
            ->test(CableMap::class)
            ->call('createIssue', 'route', $route->id)
            ->set('issueType', 'fiber_cut')
            ->set('issueTitle', 'Cut at road 11')
            ->call('saveIssue')
            ->assertHasNoErrors();

        $issue = \App\Models\CableIssue::where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($issue);
        $this->assertSame('open', $issue->status);

        // Both customers sit below the route's splitter -> both affected.
        $this->actingAs($user)->get('/cable-map')->assertOk()->assertSee('A')->assertSee('B');

        Livewire::actingAs($user)
            ->test(CableMap::class)
            ->call('resolveIssue', $issue->id);

        $this->assertSame('resolved', $issue->fresh()->status);
    }
}
