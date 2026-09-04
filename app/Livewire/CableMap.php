<?php

namespace App\Livewire;

use App\Models\CableIssue;
use App\Models\CableRoute;
use App\Models\CableSplitter;
use App\Models\Customer;
use App\Models\SplitterPort;
use App\Models\User;
use App\Support\AuthorizesRoles;
use App\Support\CurrentTenant;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class CableMap extends Component
{
    use AuthorizesRoles;

    public string $viewMode = 'overview'; // overview | routeForm | splitterForm | issueForm

    // Route form
    public ?int $routeId = null;
    public string $routeName = '';
    public string $routeSource = '';
    public string $routeDestination = '';
    public ?int $routeCores = null;
    public ?float $routeLength = null;
    public string $routeNotes = '';
    public ?string $routeLatitude = null;
    public ?string $routeLongitude = null;

    // Splitter form
    public ?int $splitterId = null;
    public string $splitterName = '';
    public string $splitterLocation = '';
    public int $splitterPortCount = 8;
    public ?int $splitterRouteId = null;
    public string $splitterNotes = '';
    public ?string $splitterLatitude = null;
    public ?string $splitterLongitude = null;
    public array $portAssignments = [];

    // Issue form
    public string $issueScope = 'route'; // route | splitter
    public ?int $issueRouteId = null;
    public ?int $issueSplitterId = null;
    public string $issueType = 'fiber_cut';
    public string $issueTitle = '';
    public string $issueDescription = '';

    public function boot(): void
    {
        \App\Support\TenantPermissions::assert('network');
    }

    private function tenantId(): int
    {
        return app(CurrentTenant::class)->id();
    }

    private function routes(): \Illuminate\Database\Eloquent\Builder
    {
        return CableRoute::query()->where('tenant_id', $this->tenantId());
    }

    private function splitters(): \Illuminate\Database\Eloquent\Builder
    {
        return CableSplitter::query()->where('tenant_id', $this->tenantId());
    }

    private function customers(): \Illuminate\Database\Eloquent\Builder
    {
        return Customer::query()->where('tenant_id', $this->tenantId());
    }

    /* ---------- Routes ---------- */

    public function showOverview(): void
    {
        $this->resetForm();
        $this->viewMode = 'overview';
    }

    public function createRoute(): void
    {
        $this->resetForm();
        $this->viewMode = 'routeForm';
    }

    public function editRoute(int $id): void
    {
        $this->resetForm();
        $route = $this->routes()->findOrFail($id);
        $this->routeId = $route->id;
        $this->routeName = $route->name;
        $this->routeSource = (string) $route->source;
        $this->routeDestination = (string) $route->destination;
        $this->routeCores = $route->fiber_cores;
        $this->routeLength = $route->length_km !== null ? (float) $route->length_km : null;
        $this->routeNotes = (string) $route->notes;
        $this->routeLatitude = $route->latitude !== null ? (string) $route->latitude : null;
        $this->routeLongitude = $route->longitude !== null ? (string) $route->longitude : null;
        $this->viewMode = 'routeForm';
    }

    public function saveRoute(): void
    {
        $data = $this->validate([
            'routeName' => 'required|string|max:255',
            'routeSource' => 'nullable|string|max:255',
            'routeDestination' => 'nullable|string|max:255',
            'routeCores' => 'nullable|integer|min:1|max:10000',
            'routeLength' => 'nullable|numeric|min:0|max:100000',
            'routeNotes' => 'nullable|string|max:2000',
            'routeLatitude' => 'nullable|regex:/^-?\d{1,2}(\.\d{1,7})?$/',
            'routeLongitude' => 'nullable|regex:/^-?\d{1,3}(\.\d{1,7})?$/',
        ]);

        $attributes = [
            'name' => $data['routeName'],
            'source' => $data['routeSource'] ?: null,
            'destination' => $data['routeDestination'] ?: null,
            'fiber_cores' => $data['routeCores'] ?: null,
            'length_km' => $data['routeLength'],
            'notes' => $data['routeNotes'] ?: null,
            'latitude' => $data['routeLatitude'] !== null && $data['routeLatitude'] !== '' ? $data['routeLatitude'] : null,
            'longitude' => $data['routeLongitude'] !== null && $data['routeLongitude'] !== '' ? $data['routeLongitude'] : null,
        ];

        if ($this->routeId) {
            $this->routes()->findOrFail($this->routeId)->update($attributes);
        } else {
            $this->routes()->create($attributes + ['tenant_id' => $this->tenantId()]);
        }

        $this->showOverview();
        session()->flash('message', $this->routeId ? __('Fiber route updated.') : __('Fiber route added.'));
    }

    public function deleteRoute(int $id): void
    {
        $route = $this->routes()->findOrFail($id);
        DB::transaction(function () use ($route) {
            CableSplitter::where('tenant_id', $this->tenantId())->where('cable_route_id', $route->id)
                ->update(['cable_route_id' => null]);
            $route->delete();
        });
        session()->flash('message', __('Fiber route deleted.'));
    }

    /* ---------- Splitters & ports ---------- */

    public function createSplitter(?int $routeId = null): void
    {
        $this->resetForm();
        $this->splitterRouteId = $routeId;
        $this->viewMode = 'splitterForm';
    }

    public function editSplitter(int $id): void
    {
        $this->resetForm();
        $splitter = $this->splitters()->with('ports.customer')->findOrFail($id);
        $this->splitterId = $splitter->id;
        $this->splitterName = $splitter->name;
        $this->splitterLocation = (string) $splitter->location;
        $this->splitterPortCount = $splitter->port_count;
        $this->splitterRouteId = $splitter->cable_route_id;
        $this->splitterNotes = (string) $splitter->notes;
        $this->splitterLatitude = $splitter->latitude !== null ? (string) $splitter->latitude : null;
        $this->splitterLongitude = $splitter->longitude !== null ? (string) $splitter->longitude : null;
        $this->portAssignments = $splitter->ports->mapWithKeys(
            fn (SplitterPort $port) => [$port->id => (string) ($port->customer_id ?? '')]
        )->all();
        $this->viewMode = 'splitterForm';
    }

    public function saveSplitter(): void
    {
        $data = $this->validate([
            'splitterName' => 'required|string|max:255',
            'splitterLocation' => 'nullable|string|max:255',
            'splitterPortCount' => 'required|integer|min:1|max:256',
            'splitterRouteId' => 'nullable|integer|exists:cable_routes,id',
            'splitterNotes' => 'nullable|string|max:2000',
            'splitterLatitude' => 'nullable|regex:/^-?\d{1,2}(\.\d{1,7})?$/',
            'splitterLongitude' => 'nullable|regex:/^-?\d{1,3}(\.\d{1,7})?$/',
            'portAssignments' => 'array',
            'portAssignments.*' => 'nullable|integer',
        ]);

        $routeId = $data['splitterRouteId'];
        if ($routeId && !$this->routes()->whereKey($routeId)->exists()) {
            abort(422, 'Invalid fiber route.');
        }

        // Guard: one customer cannot occupy two ports of the same splitter.
        $picked = collect($this->portAssignments)->filter(fn ($v) => (string) $v !== '')->values();
        if ($picked->unique()->count() !== $picked->count()) {
            session()->flash('error', __('A customer can only be linked to one port on this splitter.'));
            return;
        }

        // Guard: a customer already linked to a port on another splitter cannot be re-used.
        if ($picked->isNotEmpty()) {
            $elsewhere = SplitterPort::query()
                ->where('tenant_id', $this->tenantId())
                ->whereIn('customer_id', $picked->map(fn ($v) => (int) $v)->all())
                ->when($this->splitterId, fn ($q) => $q->where('cable_splitter_id', '!=', $this->splitterId))
                ->first();
            if ($elsewhere) {
                session()->flash('error', __('This customer is already linked to a port on another splitter.'));
                return;
            }
        }

        DB::transaction(function () use ($data) {
            $splitter = $this->splitterId
                ? $this->splitters()->findOrFail($this->splitterId)
                : $this->splitters()->create([
                    'tenant_id' => $this->tenantId(),
                    'cable_route_id' => $data['splitterRouteId'] ?: null,
                    'name' => $data['splitterName'],
                    'location' => $data['splitterLocation'] ?: null,
                    'port_count' => $data['splitterPortCount'],
                    'notes' => $data['splitterNotes'] ?: null,
                ]);

            $splitter->update([
                'cable_route_id' => $data['splitterRouteId'] ?: null,
                'name' => $data['splitterName'],
                'location' => $data['splitterLocation'] ?: null,
                'port_count' => $data['splitterPortCount'],
                'notes' => $data['splitterNotes'] ?: null,
                'latitude' => $data['splitterLatitude'] !== null && $data['splitterLatitude'] !== '' ? $data['splitterLatitude'] : null,
                'longitude' => $data['splitterLongitude'] !== null && $data['splitterLongitude'] !== '' ? $data['splitterLongitude'] : null,
            ]);

            // Sync ports to the requested count.
            $ports = $splitter->ports()->orderBy('port_number')->get();
            $toKeep = $data['splitterPortCount'];

            foreach ($ports as $port) {
                if ($port->port_number > $toKeep) {
                    if ($port->customer_id !== null) {
                        session()->flash('error', __('Cannot remove port :port — a customer is linked to it.', ['port' => $port->port_number]));
                        return;
                    }
                    $port->delete();
                }
            }

            if ($ports->count() < $toKeep) {
                for ($n = $ports->count() + 1; $n <= $toKeep; $n++) {
                    $splitter->ports()->create([
                        'tenant_id' => $this->tenantId(),
                        'port_number' => $n,
                    ]);
                }
            }

            // Persist customer assignments.
            $validCustomerIds = $this->customers()->pluck('id')->map(fn ($id) => (string) $id)->all();
            foreach ($splitter->ports()->get() as $port) {
                $wanted = (string) ($this->portAssignments[$port->id] ?? '');
                $port->customer_id = ($wanted !== '' && in_array($wanted, $validCustomerIds, true)) ? (int) $wanted : null;
                $port->save();
            }
        });

        if (session()->has('error')) {
            $this->viewMode = 'splitterForm';
            return;
        }

        $this->showOverview();
        session()->flash('message', $this->splitterId ? __('Splitter updated.') : __('Splitter added.'));
    }

    public function deleteSplitter(int $id): void
    {
        $splitter = $this->splitters()->findOrFail($id);
        $splitter->delete();
        session()->flash('message', __('Splitter deleted.'));
    }

    public function unlinkPort(int $portId): void
    {
        SplitterPort::where('tenant_id', $this->tenantId())->whereKey($portId)->update(['customer_id' => null]);
        session()->flash('message', __('Port unlinked.'));
    }

    /* ---------- Issues ---------- */

    public function createIssue(?string $scope = null, ?int $routeId = null, ?int $splitterId = null): void
    {
        $this->resetForm();
        $this->issueScope = $scope ?? 'route';
        $this->issueRouteId = $scope === 'splitter' ? null : $routeId;
        $this->issueSplitterId = $scope === 'splitter' ? $splitterId : null;
        $this->viewMode = 'issueForm';
    }

    public function saveIssue(): void
    {
        $data = $this->validate([
            'issueScope' => 'required|in:route,splitter',
            'issueRouteId' => 'required_if:issueScope,route|nullable|integer|exists:cable_routes,id',
            'issueSplitterId' => 'required_if:issueScope,splitter|nullable|integer|exists:cable_splitters,id',
            'issueType' => 'required|in:fiber_cut,maintenance,other',
            'issueTitle' => 'required|string|max:255',
            'issueDescription' => 'nullable|string|max:2000',
        ]);

        $routeId = $data['issueScope'] === 'route' ? $data['issueRouteId'] : null;
        $splitterId = $data['issueScope'] === 'splitter' ? $data['issueSplitterId'] : null;

        if ($routeId && !$this->routes()->whereKey($routeId)->exists()) {
            abort(422, 'Invalid route.');
        }
        if ($splitterId && !$this->splitters()->whereKey($splitterId)->exists()) {
            abort(422, 'Invalid splitter.');
        }

        CableIssue::create([
            'tenant_id' => $this->tenantId(),
            'cable_route_id' => $routeId,
            'cable_splitter_id' => $splitterId,
            'issue_type' => $data['issueType'],
            'title' => $data['issueTitle'],
            'description' => $data['issueDescription'] ?: null,
            'status' => 'open',
        ]);

        $this->showOverview();
        session()->flash('message', __('Issue reported. Affected customers can be reviewed on the map.'));
    }

    public function resolveIssue(int $id): void
    {
        CableIssue::where('tenant_id', $this->tenantId())
            ->whereKey($id)->where('status', 'open')
            ->update(['status' => 'resolved', 'resolved_at' => now()]);
        session()->flash('message', __('Issue resolved.'));
    }

    /* ---------- Render ---------- */

    public function render()
    {
        $tenantId = $this->tenantId();

        $stats = [
            'routes' => CableRoute::where('tenant_id', $tenantId)->count(),
            'splitters' => CableSplitter::where('tenant_id', $tenantId)->count(),
            'used_ports' => SplitterPort::where('tenant_id', $tenantId)->whereNotNull('customer_id')->count(),
            'free_ports' => SplitterPort::where('tenant_id', $tenantId)->whereNull('customer_id')->count(),
            'open_issues' => CableIssue::where('tenant_id', $tenantId)->where('status', 'open')->count(),
        ];

        $routes = CableRoute::where('tenant_id', $tenantId)
            ->withCount(['splitters', 'openIssues'])
            ->with(['splitters' => fn ($q) => $q->withCount(['openIssues'])->with(['ports.customer'])->orderBy('name')])
            ->orderBy('name')
            ->get();

        $unassignedSplitters = CableSplitter::where('tenant_id', $tenantId)
            ->whereNull('cable_route_id')
            ->withCount(['openIssues'])
            ->with(['ports.customer'])
            ->orderBy('name')
            ->get();

        $openIssues = CableIssue::where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->with(['route', 'splitter'])
            ->latest()
            ->get();

        return view('livewire.cable-map', [
            'view' => $this->viewMode,
            'stats' => $stats,
            'routes' => $routes,
            'unassignedSplitters' => $unassignedSplitters,
            'openIssues' => $openIssues,
            'allRoutes' => CableRoute::where('tenant_id', $tenantId)->orderBy('name')->get(),
            'allSplitters' => CableSplitter::where('tenant_id', $tenantId)->orderBy('name')->get(),
            'customers' => $this->customers()->orderBy('name')->get(['id', 'name', 'phone']),
            'mapPayload' => $this->mapPayload($tenantId),
            'affectedByIssue' => fn (CableIssue $issue) => $this->affectedCustomers($issue),
        ]);
    }

    private function mapPayload(int $tenantId): array
    {
        $routes = CableRoute::where('tenant_id', $tenantId)
            ->whereNotNull('latitude')->whereNotNull('longitude')
            ->withCount('openIssues')
            ->get(['id', 'name', 'source', 'destination', 'fiber_cores', 'length_km', 'latitude', 'longitude']);

        $splitters = CableSplitter::where('tenant_id', $tenantId)
            ->whereNotNull('latitude')->whereNotNull('longitude')
            ->withCount(['customerPorts', 'openIssues'])
            ->with('route:id,name')
            ->get(['id', 'name', 'location', 'cable_route_id', 'latitude', 'longitude', 'port_count']);

        return [
            'routes' => $routes->map(fn (CableRoute $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'source' => $r->source,
                'destination' => $r->destination,
                'cores' => $r->fiber_cores,
                'km' => $r->length_km !== null ? (float) $r->length_km : null,
                'issues' => (int) $r->open_issues_count,
                'lat' => (float) $r->latitude,
                'lng' => (float) $r->longitude,
            ])->values(),
            'splitters' => $splitters->map(fn (CableSplitter $s) => [
                'id' => $s->id,
                'route_id' => $s->cable_route_id,
                'route' => $s->route?->name,
                'name' => $s->name,
                'location' => $s->location,
                'used' => (int) $s->customer_ports_count,
                'total' => (int) $s->port_count,
                'issues' => (int) $s->open_issues_count,
                'lat' => (float) $s->latitude,
                'lng' => (float) $s->longitude,
            ])->values(),
        ];
    }

    private function affectedCustomers(CableIssue $issue): \Illuminate\Support\Collection
    {
        $portQuery = SplitterPort::where('tenant_id', $this->tenantId())->whereNotNull('customer_id');

        if ($issue->cable_splitter_id) {
            $portQuery->where('cable_splitter_id', $issue->cable_splitter_id);
        } elseif ($issue->cable_route_id) {
            $splitterIds = CableSplitter::where('tenant_id', $this->tenantId())
                ->where('cable_route_id', $issue->cable_route_id)
                ->pluck('id');
            $portQuery->whereIn('cable_splitter_id', $splitterIds);
        } else {
            return collect();
        }

        return $portQuery->with('customer')->get()
            ->pluck('customer')
            ->filter()
            ->values();
    }

    private function resetForm(): void
    {
        $this->reset([
            'routeId', 'routeName', 'routeSource', 'routeDestination', 'routeCores', 'routeLength', 'routeNotes', 'routeLatitude', 'routeLongitude',
            'splitterId', 'splitterName', 'splitterLocation', 'splitterPortCount', 'splitterRouteId', 'splitterNotes', 'splitterLatitude', 'splitterLongitude', 'portAssignments',
            'issueRouteId', 'issueSplitterId', 'issueTitle', 'issueDescription',
        ]);
        $this->issueScope = 'route';
        $this->issueType = 'fiber_cut';
        $this->splitterPortCount = 8;
    }
}
