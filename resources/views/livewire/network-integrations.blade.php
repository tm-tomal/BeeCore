<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Network integrations</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">MikroTik, RADIUS, OLT, and custom API integrations per tenant, with health checks and logs.</p>
        </div>
        @if($selectedTenantId)
            <div class="flex shrink-0 items-center gap-3">
                <button wire:click="create" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Add integration</button>
            </div>
        @endif
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <div class="max-w-xs">
        <label for="ni-tenant" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Tenant</label>
        <select id="ni-tenant" wire:change="selectTenant($event.target.value)" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
            <option value="">Select a tenant</option>
            @foreach($tenants as $tenant)<option value="{{ $tenant->id }}" @selected($selectedTenantId == $tenant->id)>{{ $tenant->name }}</option>@endforeach
        </select>
    </div>

    @if($selectedTenantId)
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-y border-gray-100 bg-gray-50/60 dark:border-gray-800">
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Host</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Version</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Health</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                            <th scope="col" class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($integrations as $i)
                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 align-middle text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $i->name }}</td>
                                <td class="px-5 py-4 align-middle text-theme-sm uppercase text-gray-600 dark:text-gray-400">{{ str_replace('_', ' ', $i->type) }}</td>
                                <td class="px-5 py-4 align-middle"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $i->host ?? '—' }}</code></td>
                                <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">{{ $i->version ?? '—' }}</td>
                                <td class="px-5 py-4 align-middle">
                                    <span class="rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ match($i->health_status) { 'online' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500', 'degraded' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500', 'offline' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500', default => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400' } }}">{{ ucfirst($i->health_status) }}</span>
                                    @if($i->last_checked_at)<div class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ $i->last_checked_at->format('d M Y, H:i') }}</div>@endif
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    @if($i->is_active)
                                        <span class="rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Enabled</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs font-medium text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">Disabled</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    <div class="flex flex-wrap items-center justify-end gap-1">
                                        <button wire:click="testConnection({{ $i->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">Test</button>
                                        <button wire:click="viewLogs({{ $i->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">Logs</button>
                                        <button wire:click="toggleActive({{ $i->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium transition {{ $i->is_active ? 'text-warning-600 hover:bg-warning-50 dark:text-warning-500 dark:hover:bg-warning-500/10' : 'text-success-600 hover:bg-success-50 dark:text-success-500 dark:hover:bg-success-500/10' }}">{{ $i->is_active ? 'Disable' : 'Enable' }}</button>
                                        <button wire:click="edit({{ $i->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">Edit</button>
                                        <button wire:click="delete({{ $i->id }})" wire:confirm="Remove this integration?" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-error-600 transition hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-4 align-middle">
                                    <div class="py-10 text-center">
                                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">No network integrations for this tenant yet.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-gray-200 bg-white/50 px-5 py-12 text-center dark:border-gray-800 dark:bg-white/[0.02]">
            <p class="text-theme-sm text-gray-500 dark:text-gray-400">Select a tenant to manage its network integrations.</p>
        </div>
    @endif

    @if($viewMode === 'create')
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="cancelForm"></div>
            <div class="relative max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $integrationId ? 'Edit integration' : 'Add integration' }}</h3>
                    <button type="button" wire:click="cancelForm" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form wire:submit="save" class="space-y-5">
                    <div>
                        <label for="ni-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Name</label>
                        <input id="ni-name" wire:model="name" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        @error('name')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="ni-type" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Type</label>
                            <select id="ni-type" wire:model="type" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
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
                        <textarea id="ni-creds" wire:model="credentialsText" rows="4" class="min-h-24 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
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
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Integration logs</h3>
                    <button type="button" wire:click="closeLogs" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <ul class="space-y-3 text-theme-sm">
                    @forelse($logs as $log)
                        <li class="flex items-start justify-between gap-4 border-b border-gray-100 pb-3 last:border-0 dark:border-gray-800">
                            <div class="min-w-0">
                                <span class="text-theme-sm font-semibold uppercase {{ $log->direction === 'failure' ? 'text-error-600 dark:text-error-400' : 'text-success-600 dark:text-success-400' }}">{{ $log->direction }}</span>
                                @if($log->message)<div class="mt-1 break-words text-theme-xs text-gray-500 dark:text-gray-400">{{ $log->message }}</div>@endif
                            </div>
                            <time class="shrink-0 text-theme-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->format('d M Y, H:i') }}</time>
                        </li>
                    @empty
                        <li class="py-6 text-center text-theme-sm text-gray-500 dark:text-gray-400">No logs recorded yet.</li>
                    @endforelse
                </ul>
                <div class="mt-5 flex justify-end">
                    <button wire:click="closeLogs" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Close</button>
                </div>
            </div>
        </div>
    @endif
</div>
