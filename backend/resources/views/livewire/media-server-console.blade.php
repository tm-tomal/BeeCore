<div>
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Media / movie server</h1>
            <p class="mt-2 text-sm text-slate-500">Infrastructure health, per-tenant enablement, storage allocation, and content policy.</p>
        </div>
        @if($tab === 'servers')<button wire:click="create" class="bc-primary">Add server</button>@endif
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <div class="mb-5 flex gap-2">
        <button wire:click="$set('tab', 'servers')" class="px-4 py-2 text-sm font-bold {{ $tab === 'servers' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Server infrastructure</button>
        <button wire:click="$set('tab', 'tenants')" class="px-4 py-2 text-sm font-bold {{ $tab === 'tenants' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Tenant media</button>
    </div>

    @if($tab === 'servers')
        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Server</th><th>Host</th><th>Capacity</th><th>Status</th><th>Last checked</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($servers as $server)
                        <tr>
                            <td class="font-bold text-white">{{ $server->name }}</td>
                            <td><code class="text-slate-400">{{ $server->host }}</code></td>
                            <td>{{ number_format($server->storage_capacity_gb) }} GB</td>
                            <td><span class="font-semibold {{ match($server->status) { 'online' => 'text-emerald-300', 'degraded' => 'text-amber-300', default => 'text-rose-300' } }}">{{ ucfirst($server->status) }}</span></td>
                            <td class="text-xs text-slate-500">{{ $server->last_checked_at?->format('d M Y, H:i') ?? 'Never' }}</td>
                            <td class="text-right">
                                <div class="flex flex-wrap justify-end gap-3">
                                    <button wire:click="checkHealth({{ $server->id }})" class="font-semibold text-emerald-300">Mark online</button>
                                    <button wire:click="markDegraded({{ $server->id }})" class="font-semibold text-amber-300">Mark degraded</button>
                                    <button wire:click="edit({{ $server->id }})" class="font-semibold text-teal-300">Edit</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 text-center text-slate-600">No media servers registered.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="mb-5 max-w-xs">
            <label class="bc-label" for="ms-tenant">Tenant</label>
            <select id="ms-tenant" wire:change="selectTenant($event.target.value)" class="bc-field">
                <option value="">Select a tenant</option>
                @foreach($tenants as $tenant)<option value="{{ $tenant->id }}" @selected($selectedTenantId == $tenant->id)>{{ $tenant->name }}</option>@endforeach
            </select>
        </div>

        @if($selectedTenantId)
            <div class="grid gap-5 lg:grid-cols-[1fr_0.8fr]">
                <form wire:submit="saveTenantSettings" class="bc-panel space-y-4 p-5" style="border-radius:8px">
                    <h2 class="font-bold text-white">Media module</h2>
                    <label class="inline-flex items-center gap-3 text-sm text-slate-300"><input wire:model="isEnabled" type="checkbox">Media module enabled</label>
                    <div><label class="bc-label" for="ms-storage">Storage allocation (GB)</label><input id="ms-storage" wire:model="storageAllocatedGb" type="number" min="0" class="bc-field">@error('storageAllocatedGb')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="ms-policy">Content policy</label><textarea id="ms-policy" wire:model="contentPolicy" rows="4" class="bc-field"></textarea>@error('contentPolicy')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <button type="submit" class="bc-primary">Save settings</button>
                </form>

                <div class="bc-panel space-y-4 p-5" style="border-radius:8px">
                    <h2 class="font-bold text-white">Usage this period</h2>
                    <div class="grid grid-cols-3 gap-3 text-sm">
                        <div><p class="text-xs uppercase text-slate-500">Storage</p><p class="text-xl font-black text-white">{{ $tenantSettings->storage_used_gb ?? 0 }} GB</p><p class="text-xs text-slate-600">of {{ $tenantSettings->storage_allocated_gb ?? 0 }} GB</p></div>
                        <div><p class="text-xs uppercase text-slate-500">Streaming</p><p class="text-xl font-black text-white">{{ $tenantSettings->streaming_used_gb ?? 0 }} GB</p></div>
                        <div><p class="text-xs uppercase text-slate-500">Bandwidth</p><p class="text-xl font-black text-white">{{ $tenantSettings->bandwidth_used_gb ?? 0 }} GB</p></div>
                    </div>
                    <button wire:click="simulateUsage" class="text-xs font-semibold text-slate-400">Log test usage</button>

                    @if($mediaAddons->isNotEmpty())
                        <div class="border-t border-white/10 pt-4">
                            <h3 class="text-xs font-bold uppercase text-slate-500">Media add-ons available</h3>
                            <ul class="mt-2 space-y-1 text-sm text-slate-300">@foreach($mediaAddons as $addon)<li>{{ $addon->name }} · ৳{{ number_format($addon->price, 2) }}/{{ str_replace('_', ' ', $addon->billing_cycle) }}</li>@endforeach</ul>
                            <a href="{{ route('add-ons') }}" class="mt-2 inline-block text-xs font-semibold text-teal-300">Manage in Add-ons →</a>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <p class="py-12 text-center text-slate-600">Select a tenant to manage its media module.</p>
        @endif
    @endif

    @if($viewMode === 'create')
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="cancelForm"></div>
            <div class="bc-panel relative w-full max-w-md p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">{{ $serverId ? 'Edit media server' : 'Add media server' }}</h2>
                <form wire:submit="save" class="mt-5 space-y-4">
                    <div><label class="bc-label" for="mv-name">Name</label><input id="mv-name" wire:model="name" class="bc-field">@error('name')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="mv-host">Host</label><input id="mv-host" wire:model="host" class="bc-field" placeholder="media1.beecore.internal">@error('host')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="mv-capacity">Storage capacity (GB)</label><input id="mv-capacity" wire:model="storageCapacityGb" type="number" min="1" class="bc-field">@error('storageCapacityGb')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div class="flex justify-end gap-3"><button type="button" wire:click="cancelForm" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Save server</button></div>
                </form>
            </div>
        </div>
    @endif
</div>
