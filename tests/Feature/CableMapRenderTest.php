<?php

namespace Tests\Feature;

use App\Livewire\CableMap;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CableMapRenderTest extends TestCase
{
    use RefreshDatabase;

    private function tenantContext(): array
    {
        $tenant = \App\Models\Tenant::create(['name' => 'Demo ISP', 'slug' => 'demo-isp', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC', 'operation_mode' => 'manual']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        return [$tenant, $user];
    }

    public function test_clicking_add_fiber_route_renders_route_form(): void
    {
        [$tenant, $user] = $this->tenantContext();

        Livewire::actingAs($user)
            ->test(CableMap::class)
            ->assertSet('viewMode', 'overview')
            ->call('createRoute')
            ->assertSet('viewMode', 'routeForm')
            ->assertSee('Route name')
            ->assertSee('Add route');
    }

    public function test_clicking_add_splitter_renders_splitter_form(): void
    {
        [$tenant, $user] = $this->tenantContext();

        Livewire::actingAs($user)
            ->test(CableMap::class)
            ->call('createSplitter')
            ->assertSet('viewMode', 'splitterForm')
            ->assertSee('Splitter name')
            ->assertSee('Port count');
    }

    public function test_overview_renders_map_when_route_points_exist(): void
    {
        [$tenant, $user] = $this->tenantContext();

        \App\Models\CableRoute::create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Line',
            'latitude' => '23.7800000',
            'longitude' => '90.4000000',
        ]);

        Livewire::actingAs($user)
            ->test(CableMap::class)
            ->assertSet('viewMode', 'overview')
            ->assertSee('Live network map')
            ->assertSee('data-cable-map');
    }

    public function test_clicking_report_issue_renders_issue_form(): void
    {
        [$tenant, $user] = $this->tenantContext();

        Livewire::actingAs($user)
            ->test(CableMap::class)
            ->call('createIssue', 'route')
            ->assertSet('viewMode', 'issueForm')
            ->assertSee('Problem at')
            ->assertSee('Short title');
    }
}
