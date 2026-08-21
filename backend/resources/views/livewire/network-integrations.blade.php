<div>
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Network integrations</h1>
            <p class="mt-2 text-sm text-slate-500">MikroTik, RADIUS, OLT, and custom API integrations per tenant, with health checks and logs.</p>
        </div>
        @if($selectedTenantId)<button wire:click="create" class="bc-primary">Add integration</button>@endif
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <div class="mb-5 max-w-xs">
        <label class="bc-label" for="ni-tenant">Tenant</label>
        <select id="ni-tenant" wire:change="selectTenant($event.target.value)" class="bc-field">
            <option value="">Select a tenant</option>
            @foreach($tenants as $tenant)<option value="{{ $tenant->id }}" @selected($selectedTenantId == $tenant->id)>{{ $tenant->name }}</option>@endforeach
        </select>
    </div>

    @if($selectedTenantId)
        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Name</th><th>Type</th><th>Host</th><th>Version</th><th>Health</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($integrations as $i)
                        <tr>
                            <td class="font-bold text-white">{{ $i->name }}</td>
                            <td class="uppercase">{{ str_replace('_', ' ', $i->type) }}</td>
                            <td><code class="text-slate-400">{{ $i->host ?? '—' }}</code></td>
                            <td>{{ $i->version ?? '—' }}</td>
                            <td><span class="font-semibold {{ match($i->health_status) { 'online' => 'text-emerald-300', 'degraded' => 'text-amber-300', 'offline' => 'text-rose-300', default => 'text-slate-500' } }}">{{ ucfirst($i->health_status) }}</span>@if($i->last_checked_at)<div class="text-[10px] text-slate-600">{{ $i->last_checked_at->format('d M Y, H:i') }}</div>@endif</td>
                            <td><span class="font-semibold {{ $i->is_active ? 'text-emerald-300' : 'text-slate-500' }}">{{ $i->is_active ? 'Enabled' : 'Disabled' }}</span></td>
                            <td class="text-right">
                                <div class="flex flex-wrap justify-end gap-3">
                                    <button wire:click="testConnection({{ $i->id }})" class="font-semibold text-slate-300">Test</button>
                                    <button wire:click="viewLogs({{ $i->id }})" class="font-semibold text-slate-300">Logs</button>
                                    <button wire:click="toggleActive({{ $i->id }})" class="font-semibold {{ $i->is_active ? 'text-amber-300' : 'text-emerald-300' }}">{{ $i->is_active ? 'Disable' : 'Enable' }}</button>
                                    <button wire:click="edit({{ $i->id }})" class="font-semibold text-teal-300">Edit</button>
                                    <button wire:click="delete({{ $i->id }})" wire:confirm="Remove this integration?" class="font-semibold text-rose-300">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-12 text-center text-slate-600">No network integrations for this tenant yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <p class="py-12 text-center text-slate-600">Select a tenant to manage its network integrations.</p>
    @endif

    @if($viewMode === 'create')
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="cancelForm"></div>
            <div class="bc-panel relative max-h-[90vh] w-full max-w-lg overflow-y-auto p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">{{ $integrationId ? 'Edit integration' : 'Add integration' }}</h2>
                <form wire:submit="save" class="mt-5 space-y-4">
                    <div><label class="bc-label" for="ni-name">Name</label><input id="ni-name" wire:model="name" class="bc-field">@error('name')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="bc-label" for="ni-type">Type</label><select id="ni-type" wire:model="type" class="bc-field"><option value="mikrotik">MikroTik</option><option value="radius">RADIUS</option><option value="olt">OLT</option><option value="custom_api">Custom API</option></select></div>
                        <div><label class="bc-label" for="ni-version">Version</label><input id="ni-version" wire:model="version" class="bc-field" placeholder="e.g. RouterOS 7.14">@error('version')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    </div>
                    <div><label class="bc-label" for="ni-host">Host / endpoint</label><input id="ni-host" wire:model="host" class="bc-field" placeholder="192.168.1.1 or https://api.example.com">@error('host')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="ni-creds">Credentials (one <code>key=value</code> per line, encrypted at rest)</label><textarea id="ni-creds" wire:model="credentialsText" rows="4" class="bc-field"></textarea></div>
                    <div class="flex justify-end gap-3"><button type="button" wire:click="cancelForm" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Save integration</button></div>
                </form>
            </div>
        </div>
    @endif

    @if($logsForId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="closeLogs"></div>
            <div class="bc-panel relative max-h-[80vh] w-full max-w-lg overflow-y-auto p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">Integration logs</h2>
                <ul class="mt-5 space-y-3 text-sm">
                    @forelse($logs as $log)
                        <li class="border-b border-white/10 pb-3">
                            <div class="flex items-center justify-between"><span class="font-semibold uppercase {{ $log->direction === 'failure' ? 'text-rose-300' : 'text-emerald-300' }}">{{ $log->direction }}</span><span class="text-xs text-slate-600">{{ $log->created_at->format('d M Y, H:i') }}</span></div>
                            @if($log->message)<div class="mt-1 text-slate-400">{{ $log->message }}</div>@endif
                        </li>
                    @empty
                        <li class="py-6 text-center text-slate-600">No logs recorded yet.</li>
                    @endforelse
                </ul>
                <div class="mt-5 flex justify-end"><button wire:click="closeLogs" class="bc-secondary">Close</button></div>
            </div>
        </div>
    @endif
</div>
