<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Media / movie server</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Infrastructure health, per-tenant enablement, storage allocation, and content policy.</p>
        </div>
        @if($tab === 'servers')
            <div class="flex shrink-0 items-center gap-3">
                <button wire:click="create" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Add server</button>
            </div>
        @endif
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Tabs -->
    <div class="inline-flex flex-wrap items-center gap-1 rounded-xl bg-gray-100 p-1 dark:bg-gray-800">
        <button wire:click="$set('tab', 'servers')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'servers' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-white/[0.03] dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">Server infrastructure</button>
        <button wire:click="$set('tab', 'tenants')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'tenants' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-white/[0.03] dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">Tenant media</button>
    </div>

    @if($tab === 'servers')
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-y border-gray-100 bg-gray-50/60 dark:border-gray-800">
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Server</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Host</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Capacity</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Last checked</th>
                            <th scope="col" class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($servers as $server)
                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 align-middle text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $server->name }}</td>
                                <td class="px-5 py-4 align-middle"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $server->host }}</code></td>
                                <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">{{ number_format($server->storage_capacity_gb) }} GB</td>
                                <td class="px-5 py-4 align-middle">
                                    <span class="rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ match($server->status) { 'online' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500', 'degraded' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500', default => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500' } }}">{{ ucfirst($server->status) }}</span>
                                </td>
                                <td class="px-5 py-4 align-middle text-theme-xs text-gray-500 dark:text-gray-400">{{ $server->last_checked_at?->format('d M Y, H:i') ?? 'Never' }}</td>
                                <td class="px-5 py-4 align-middle">
                                    <div class="flex flex-wrap items-center justify-end gap-1">
                                        <button wire:click="checkHealth({{ $server->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-success-600 transition hover:bg-success-50 dark:text-success-500 dark:hover:bg-success-500/10">Mark online</button>
                                        <button wire:click="markDegraded({{ $server->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-warning-600 transition hover:bg-warning-50 dark:text-warning-500 dark:hover:bg-warning-500/10">Mark degraded</button>
                                        <button wire:click="edit({{ $server->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">Edit</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-4 align-middle">
                                    <div class="py-10 text-center">
                                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">No media servers registered.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="max-w-xs">
            <label for="ms-tenant" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Tenant</label>
            <select id="ms-tenant" wire:change="selectTenant($event.target.value)" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                <option value="">Select a tenant</option>
                @foreach($tenants as $tenant)<option value="{{ $tenant->id }}" @selected($selectedTenantId == $tenant->id)>{{ $tenant->name }}</option>@endforeach
            </select>
        </div>

        @if($selectedTenantId)
            <div class="grid gap-5 lg:grid-cols-[1fr_0.8fr]">
                <form wire:submit="saveTenantSettings" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Media module</h2>
                    <label class="inline-flex cursor-pointer items-center gap-2.5 text-theme-sm text-gray-700 dark:text-gray-400"><input wire:model="isEnabled" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 bg-transparent text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">Media module enabled</label>
                    <div>
                        <label for="ms-storage" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Storage allocation (GB)</label>
                        <input id="ms-storage" wire:model="storageAllocatedGb" type="number" min="0" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        @error('storageAllocatedGb')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="ms-policy" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Content policy</label>
                        <textarea id="ms-policy" wire:model="contentPolicy" rows="4" class="min-h-24 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                        @error('contentPolicy')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Save settings</button>
                </form>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Usage this period</h2>
                    <div class="mt-5 grid grid-cols-3 gap-3">
                        <div>
                            <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Storage</p>
                            <p class="mt-1 text-xl font-bold text-gray-800 dark:text-white/90">{{ $tenantSettings->storage_used_gb ?? 0 }} GB</p>
                            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">of {{ $tenantSettings->storage_allocated_gb ?? 0 }} GB</p>
                        </div>
                        <div>
                            <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Streaming</p>
                            <p class="mt-1 text-xl font-bold text-gray-800 dark:text-white/90">{{ $tenantSettings->streaming_used_gb ?? 0 }} GB</p>
                        </div>
                        <div>
                            <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Bandwidth</p>
                            <p class="mt-1 text-xl font-bold text-gray-800 dark:text-white/90">{{ $tenantSettings->bandwidth_used_gb ?? 0 }} GB</p>
                        </div>
                    </div>
                    <button wire:click="simulateUsage" class="mt-4 inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-theme-xs font-medium text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">Log test usage</button>

                    @if($mediaAddons->isNotEmpty())
                        <div class="mt-4 border-t border-gray-100 pt-4 dark:border-gray-800">
                            <h3 class="text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Media add-ons available</h3>
                            <ul class="mt-2 space-y-1 text-theme-sm text-gray-600 dark:text-gray-400">
                                @foreach($mediaAddons as $addon)<li>{{ $addon->name }} · ৳{{ number_format($addon->price, 2) }}/{{ str_replace('_', ' ', $addon->billing_cycle) }}</li>@endforeach
                            </ul>
                            <a href="{{ route('add-ons') }}" class="mt-2 inline-block text-theme-xs font-semibold text-brand-600 transition hover:text-brand-700 dark:text-brand-400">Manage in Add-ons →</a>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-gray-200 bg-white/50 px-5 py-12 text-center dark:border-gray-800 dark:bg-white/[0.02]">
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">Select a tenant to manage its media module.</p>
            </div>
        @endif
    @endif

    @if($viewMode === 'create')
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="cancelForm"></div>
            <div class="relative w-full max-w-md rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $serverId ? 'Edit media server' : 'Add media server' }}</h3>
                    <button type="button" wire:click="cancelForm" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form wire:submit="save" class="space-y-5">
                    <div>
                        <label for="mv-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Name</label>
                        <input id="mv-name" wire:model="name" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        @error('name')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="mv-host" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Host</label>
                        <input id="mv-host" wire:model="host" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="media1.beecore.internal">
                        @error('host')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="mv-capacity" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Storage capacity (GB)</label>
                        <input id="mv-capacity" wire:model="storageCapacityGb" type="number" min="1" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        @error('storageCapacityGb')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <button type="button" wire:click="cancelForm" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Save server</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
