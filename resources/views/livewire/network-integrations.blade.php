<div class="space-y-6">
    @php
        $typeChip = fn (string $type): string => match ($type) {
            'mikrotik' => 'bg-amber-50 text-amber-600 ring-1 ring-inset ring-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/25',
            'radius' => 'bg-sky-50 text-sky-600 ring-1 ring-inset ring-sky-100 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-500/25',
            'olt' => 'bg-violet-50 text-violet-600 ring-1 ring-inset ring-violet-100 dark:bg-violet-500/10 dark:text-violet-400 dark:ring-violet-500/25',
            default => 'bg-teal-50 text-teal-600 ring-1 ring-inset ring-teal-100 dark:bg-teal-500/10 dark:text-teal-400 dark:ring-teal-500/25',
        };
    @endphp

    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Network integrations</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">MikroTik, RADIUS, OLT, and custom API integrations per tenant, with health checks and logs.</p>
        </div>
        @if($selectedTenantId)
            <button type="button" wire:click="create" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add integration
            </button>
        @endif
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Tenant picker -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <label for="ni-tenant" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Tenant workspace</label>
        <div class="relative max-w-md">
            <svg class="pointer-events-none absolute inset-y-0 left-0 ml-4 size-5 self-center stroke-current text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            <select id="ni-tenant" wire:change="selectTenant($event.target.value)" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent py-2.5 pl-11 pr-4 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                <option value="">Select a tenant</option>
                @foreach($tenants as $tenant)<option value="{{ $tenant->id }}" @selected($selectedTenantId == $tenant->id)>{{ $tenant->name }}</option>@endforeach
            </select>
        </div>
    </div>

    @if($selectedTenantId)
        @php
            $onlineCount = $integrations->where('health_status', 'online')->count();
            $enabledCount = $integrations->where('is_active', true)->count();
        @endphp
        <!-- Overview -->
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </span>
                <div>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $integrations->count() }}</p>
                    <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Integrations</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-success-500/10 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </span>
                <div>
                    <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $onlineCount }}<span class="text-base font-medium text-gray-400"> / {{ $integrations->count() }}</span></p>
                    <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Healthy</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-cyan-500/10 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-400">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                </span>
                <div>
                    <p class="text-2xl font-bold text-cyan-600 dark:text-cyan-400">{{ $enabledCount }}</p>
                    <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Enabled</p>
                </div>
            </div>
        </section>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Integration</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Type</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Host</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Health</th>
                            <th class="px-5 py-3.5 text-center text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($integrations as $i)
                            @php $healthBadge = match($i->health_status) { 'online' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500', 'degraded' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500', 'offline' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500', default => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400' }; @endphp
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $typeChip($i->type) }} font-bold uppercase">{{ strtoupper(substr($i->type, 0, 2)) }}</span>
                                        <div class="min-w-0">
                                            <p class="truncate text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $i->name }}</p>
                                            @if($i->version)<p class="mt-0.5 truncate text-theme-xs text-gray-400 dark:text-gray-500">Version {{ $i->version }}</p>@endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $typeChip($i->type) }}">{{ str_replace('_', ' ', $i->type) }}</span></td>
                                <td class="px-5 py-4"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">{{ $i->host ?? '—' }}</code></td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-theme-xs font-semibold capitalize {{ $healthBadge }}">
                                        <span class="size-1.5 rounded-full bg-current"></span>{{ ucfirst($i->health_status) }}
                                    </span>
                                    @if($i->last_checked_at)<div class="mt-1 text-theme-xs text-gray-400 dark:text-gray-500">Checked {{ $i->last_checked_at->format('d M Y, H:i') }}</div>@endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if($i->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-500"><span class="size-1.5 rounded-full bg-success-500"></span>Enabled</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs font-semibold text-gray-500 dark:bg-white/[0.05] dark:text-gray-400"><span class="size-1.5 rounded-full bg-gray-400"></span>Disabled</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" wire:click="testConnection({{ $i->id }})" title="Test connection" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-success-300 hover:bg-success-50 hover:text-success-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-success-500/40 dark:hover:bg-success-500/10 dark:hover:text-success-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                        </button>
                                        <button type="button" wire:click="viewLogs({{ $i->id }})" title="View logs" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                        </button>
                                        <button type="button" wire:click="toggleActive({{ $i->id }})" title="{{ $i->is_active ? 'Disable' : 'Enable' }}" class="grid h-8 w-8 place-items-center rounded-lg border {{ $i->is_active ? 'border-warning-200 bg-warning-50 text-warning-600 hover:border-warning-300 hover:bg-warning-100 dark:border-warning-500/25 dark:bg-warning-500/10 dark:text-warning-400' : 'border-success-200 bg-success-50 text-success-600 hover:border-success-300 hover:bg-success-100 dark:border-success-500/25 dark:bg-success-500/10 dark:text-success-400' }}">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                        </button>
                                        <button type="button" wire:click="edit({{ $i->id }})" title="Edit" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                        <button type="button" wire:click="delete({{ $i->id }})" wire:confirm="Remove this integration?" title="Delete" class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/></svg>
                                        </span>
                                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No network integrations yet</p>
                                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Connect MikroTik, RADIUS, OLT or a custom API for this tenant.</p>
                                        <button type="button" wire:click="create" class="mt-4 inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Add integration</button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="grid place-items-center rounded-2xl border border-dashed border-gray-200 bg-white/50 px-5 py-16 text-center dark:border-gray-800 dark:bg-white/[0.02]">
            <div class="max-w-xs">
                <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                    <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/></svg>
                </span>
                <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">Select a tenant to manage integrations</p>
                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Connect the tenant's network gear and API endpoints here.</p>
            </div>
        </div>
    @endif

    @if($viewMode === 'create')
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="cancelForm"></div>
            <div class="relative max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $integrationId ? 'Edit integration' : 'Add integration' }}</h3>
                        <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Connect a network system or API for the selected tenant.</p>
                    </div>
                    <button type="button" wire:click="cancelForm" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form wire:submit="save" class="space-y-5">
                    <div>
                        <label for="ni-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Name</label>
                        <input id="ni-name" wire:model="name" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="Core MikroTik router">
                        @error('name')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="ni-type" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Type</label>
                            <select id="ni-type" wire:model="type" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                <option value="mikrotik">MikroTik</option>
                                <option value="radius">RADIUS</option>
                                <option value="olt">OLT</option>
                                <option value="custom_api">Custom API</option>
                            </select>
                        </div>
                        <div>
                            <label for="ni-version" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Version</label>
                            <input id="ni-version" wire:model="version" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="e.g. RouterOS 7.14">
                            @error('version')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label for="ni-host" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Host / endpoint</label>
                        <input id="ni-host" wire:model="host" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="192.168.1.1 or https://api.example.com">
                        @error('host')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="ni-creds" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Credentials (one <code class="rounded bg-gray-100 px-1 py-0.5 font-mono text-theme-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">key=value</code> per line, encrypted at rest)</label>
                        <textarea id="ni-creds" wire:model="credentialsText" rows="4" class="min-h-24 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="username=admin&#10;password=…"></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <button type="button" wire:click="cancelForm" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Save integration</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($logsForId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeLogs"></div>
            <div class="relative max-h-[80vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Integration logs</h3>
                        <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Connection &amp; health events for this integration.</p>
                    </div>
                    <button type="button" wire:click="closeLogs" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($logs as $log)
                        <div class="flex items-start justify-between gap-4 py-3 first:pt-0 last:pb-0">
                            <div class="min-w-0">
                                @if($log->direction === 'failure')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-error-50 px-2.5 py-1 text-theme-xs font-semibold uppercase text-error-600 dark:bg-error-500/15 dark:text-error-500"><span class="size-1.5 rounded-full bg-error-500"></span>Failure</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold uppercase text-success-600 dark:bg-success-500/15 dark:text-success-500"><span class="size-1.5 rounded-full bg-success-500"></span>{{ $log->direction }}</span>
                                @endif
                                @if($log->message)<div class="mt-1.5 break-words text-theme-xs text-gray-500 dark:text-gray-400">{{ $log->message }}</div>@endif
                            </div>
                            <time class="shrink-0 text-theme-xs text-gray-400 dark:text-gray-500">{{ $log->created_at->format('d M Y, H:i') }}</time>
                        </div>
                    @empty
                        <div class="py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">No logs recorded yet.</div>
                    @endforelse
                </div>
                <div class="mt-5 flex justify-end">
                    <button wire:click="closeLogs" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Close</button>
                </div>
            </div>
        </div>
    @endif
</div>
