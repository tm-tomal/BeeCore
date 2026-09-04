<div class="space-y-6">
    @php
        $onlineServers = $servers->where('status', 'online')->count();
        $totalCapacity = (int) $servers->sum('storage_capacity_gb');
        $degradedServers = $servers->where('status', 'degraded')->count();
    @endphp

    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Media / movie server</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Infrastructure health, per-tenant enablement, storage allocation, and content policy.</p>
        </div>
        @if($tab === 'servers')
            <button type="button" wire:click="create" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add server
            </button>
        @endif
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Tabs -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="inline-flex flex-wrap items-center gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <button type="button" wire:click="$set('tab', 'servers')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'servers' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
                Server infrastructure
                <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'servers' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $servers->count() }}</span>
            </button>
            <button type="button" wire:click="$set('tab', 'tenants')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'tenants' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
                Tenant media
                <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'tenants' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $tenants->count() }}</span>
            </button>
        </div>
    </div>

    @if($tab === 'servers')
        <!-- Overview -->
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
                </span>
                <div>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $servers->count() }}</p>
                    <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Servers</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-success-500/10 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </span>
                <div>
                    <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $onlineServers }}<span class="text-base font-medium text-gray-400"> / {{ $servers->count() }}</span></p>
                    <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Online</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-cyan-500/10 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-400">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                </span>
                <div>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($totalCapacity) }} <span class="text-base font-medium text-gray-400">GB</span></p>
                    <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Total capacity{{ $degradedServers ? ' · '.$degradedServers.' degraded' : '' }}</p>
                </div>
            </div>
        </section>

        <!-- Servers table -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Server</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Host</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Capacity</th>
                            <th class="px-5 py-3.5 text-center text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Last checked</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($servers as $server)
                            @php
                                $statusBadge = match($server->status) {
                                    'online' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
                                    'degraded' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                                    default => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
                                };
                            @endphp
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $server->status === 'online' ? 'bg-success-500/10 text-success-600 dark:bg-success-500/15 dark:text-success-400' : ($server->status === 'degraded' ? 'bg-warning-500/10 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400' : 'bg-error-500/10 text-error-600 dark:bg-error-500/15 dark:text-error-400') }}">
                                            <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $server->name }}</p>
                                            <p class="mt-0.5 truncate text-theme-xs text-gray-400 dark:text-gray-500">Added {{ $server->created_at?->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">{{ $server->host }}</code></td>
                                <td class="px-5 py-4 text-right text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ number_format($server->storage_capacity_gb) }} <span class="text-theme-xs font-medium text-gray-400">GB</span></td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-theme-xs font-semibold capitalize {{ $statusBadge }}">
                                        <span class="size-1.5 rounded-full bg-current"></span>{{ $server->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right text-theme-xs text-gray-500 dark:text-gray-400">{{ $server->last_checked_at?->format('d M Y, H:i') ?? 'Never' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($server->status !== 'online')
                                            <button type="button" wire:click="checkHealth({{ $server->id }})" title="Mark online" class="grid h-8 w-8 place-items-center rounded-lg border border-success-200 bg-success-50 text-success-600 transition hover:border-success-300 hover:bg-success-100 dark:border-success-500/25 dark:bg-success-500/10 dark:text-success-400">
                                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            </button>
                                        @endif
                                        @if($server->status !== 'degraded')
                                            <button type="button" wire:click="markDegraded({{ $server->id }})" title="Mark degraded" class="grid h-8 w-8 place-items-center rounded-lg border border-warning-200 bg-warning-50 text-warning-600 transition hover:border-warning-300 hover:bg-warning-100 dark:border-warning-500/25 dark:bg-warning-500/10 dark:text-warning-400">
                                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                            </button>
                                        @endif
                                        <button type="button" wire:click="edit({{ $server->id }})" title="Edit server" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
                                        </span>
                                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No media servers registered</p>
                                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Add your first media server to start allocating storage to tenants.</p>
                                        <button type="button" wire:click="create" class="mt-4 inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Add server</button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- Tenant media -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <label for="ms-tenant" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Tenant workspace</label>
            <div class="relative max-w-md">
                <svg class="pointer-events-none absolute inset-y-0 left-0 ml-4 size-5 self-center stroke-current text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                <select id="ms-tenant" wire:change="selectTenant($event.target.value)" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent py-2.5 pl-11 pr-4 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    <option value="">Select a tenant</option>
                    @foreach($tenants as $tenant)<option value="{{ $tenant->id }}" @selected($selectedTenantId == $tenant->id)>{{ $tenant->name }}</option>@endforeach
                </select>
            </div>
        </div>

        @if($selectedTenantId)
            @php $selectedTenant = $tenants->firstWhere('id', $selectedTenantId); @endphp
            <div class="grid gap-5 lg:grid-cols-[1fr_0.8fr]">
                <form wire:submit="saveTenantSettings" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $isEnabled ? 'bg-success-500/10 text-success-600 dark:bg-success-500/15 dark:text-success-400' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.05] dark:text-gray-400' }}">
                                <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/><polygon points="10 8 16 11 10 14"/></svg>
                            </span>
                            <div>
                                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Media module · {{ $selectedTenant?->name ?? 'Tenant' }}</h2>
                                <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Enable the movie/media module for this workspace.</p>
                            </div>
                        </div>
                        <label class="inline-flex shrink-0 cursor-pointer items-center gap-2.5 text-theme-sm font-medium {{ $isEnabled ? 'text-success-600 dark:text-success-400' : 'text-gray-500 dark:text-gray-400' }}">
                            <span class="relative">
                                <input wire:model="isEnabled" type="checkbox" class="peer sr-only">
                                <span class="block h-6 w-11 rounded-full bg-gray-200 transition peer-checked:bg-success-500 dark:bg-gray-700"></span>
                                <span class="absolute left-1 top-1 size-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                            </span>
                            {{ $isEnabled ? 'Enabled' : 'Disabled' }}
                        </label>
                    </div>

                    <div>
                        <label for="ms-storage" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Storage allocation (GB)</label>
                        <input id="ms-storage" wire:model="storageAllocatedGb" type="number" min="0" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        @error('storageAllocatedGb')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="ms-policy" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Content policy</label>
                        <textarea id="ms-policy" wire:model="contentPolicy" rows="4" class="min-h-24 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="Which content is allowed / restricted for this tenant"></textarea>
                        @error('contentPolicy')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Save settings
                    </button>
                </form>

                <div class="space-y-5">
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Usage this period</h2>
                            @if($isEnabled)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-500"><span class="size-1.5 rounded-full bg-success-500"></span>Active</span>
                            @endif
                        </div>
                        @php
                            $alloc = (int) ($tenantSettings->storage_allocated_gb ?? 0);
                            $used = (int) ($tenantSettings->storage_used_gb ?? 0);
                            $pct = $alloc > 0 ? min(100, (int) round($used / $alloc * 100)) : 0;
                        @endphp
                        <div class="mt-5 space-y-5">
                            <div>
                                <div class="flex items-center justify-between text-theme-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Storage used</span>
                                    <span class="font-semibold text-gray-800 dark:text-white/90">{{ number_format($used) }} / {{ number_format($alloc) }} GB</span>
                                </div>
                                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/[0.06]">
                                    <div class="h-full rounded-full {{ $pct >= 90 ? 'bg-error-500' : 'bg-brand-500' }}" style="width: {{ $pct }}%"></div>
                                </div>
                                <p class="mt-1 text-theme-xs text-gray-400 dark:text-gray-500">{{ $pct }}% of the allocated {{ $alloc }} GB is in use.</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl border border-gray-100 p-3.5 dark:border-gray-800">
                                    <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Streaming</p>
                                    <p class="mt-1 text-lg font-bold text-violet-600 dark:text-violet-400">{{ number_format($tenantSettings->streaming_used_gb ?? 0) }} <span class="text-theme-xs font-medium text-gray-400">GB</span></p>
                                </div>
                                <div class="rounded-xl border border-gray-100 p-3.5 dark:border-gray-800">
                                    <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Bandwidth</p>
                                    <p class="mt-1 text-lg font-bold text-cyan-600 dark:text-cyan-400">{{ number_format($tenantSettings->bandwidth_used_gb ?? 0) }} <span class="text-theme-xs font-medium text-gray-400">GB</span></p>
                                </div>
                            </div>
                        </div>
                        <button type="button" wire:click="simulateUsage" class="mt-4 inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-theme-xs font-medium text-gray-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                            Log test usage
                        </button>
                    </div>

                    @if($mediaAddons->isNotEmpty())
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Media add-ons available</h3>
                                <a href="{{ route('add-ons') }}" class="inline-flex items-center gap-1 text-theme-xs font-semibold text-brand-600 transition hover:text-brand-700 dark:text-brand-400">Manage
                                    <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                            <div class="mt-3 space-y-2">
                                @foreach($mediaAddons as $addon)
                                    <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 px-3.5 py-2.5 dark:border-gray-800">
                                        <div class="flex min-w-0 items-center gap-2.5">
                                            <span class="grid size-7 shrink-0 place-items-center rounded-lg bg-fuchsia-500/10 text-fuchsia-600 dark:bg-fuchsia-500/15 dark:text-fuchsia-400">
                                                <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
                                            </span>
                                            <p class="truncate text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $addon->name }}</p>
                                        </div>
                                        <span class="shrink-0 text-theme-xs font-semibold text-gray-500 dark:text-gray-400">৳{{ number_format($addon->price, 2) }}/{{ str_replace('_', ' ', $addon->billing_cycle) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="grid place-items-center rounded-2xl border border-dashed border-gray-200 bg-white/50 px-5 py-16 text-center dark:border-gray-800 dark:bg-white/[0.02]">
                <div class="max-w-xs">
                    <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                        <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/><polygon points="10 8 16 11 10 14"/></svg>
                    </span>
                    <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">Select a tenant to manage its media module</p>
                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Enable the module, allocate storage and set content rules for one workspace.</p>
                </div>
            </div>
        @endif
    @endif

    @if($viewMode === 'create')
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="cancelForm"></div>
            <div class="relative max-h-[92vh] w-full max-w-md overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $serverId ? 'Edit media server' : 'Add media server' }}</h3>
                        <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $serverId ? 'Update the media server details.' : 'Register a media server for tenant storage.' }}</p>
                    </div>
                    <button type="button" wire:click="cancelForm" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form wire:submit="save" class="space-y-5">
                    <div>
                        <label for="mv-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Name</label>
                        <input id="mv-name" wire:model="name" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="Dhaka media node 1">
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
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            {{ $serverId ? 'Save server' : 'Add server' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
