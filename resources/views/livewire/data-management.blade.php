<div class="space-y-6">
    @php
        $fmtSize = fn (?int $bytes): string => $bytes === null ? '—' : ($bytes >= 1048576 ? number_format($bytes / 1048576, 2).' MB' : number_format($bytes / 1024, 1).' KB');
    @endphp

    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Data management</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Backups, tenant data export, retention policy, and customer import.</p>
        </div>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <x-plan-error-banner />

    <!-- Tabs -->
    <div class="flex flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-white p-1 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <button type="button" wire:click="$set('tab', 'backups')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'backups' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Backups
            <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'backups' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $backups->count() }}</span>
        </button>
        <button type="button" wire:click="$set('tab', 'exports')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'exports' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Data export
            <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'exports' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $exports->count() }}</span>
        </button>
        <button type="button" wire:click="$set('tab', 'import')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'import' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Data import
        </button>
        <button type="button" wire:click="$set('tab', 'retention')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'retention' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Retention policy
        </button>
    </div>

    @if($tab === 'backups')
        <!-- Backups -->
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                        <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Latest backups</h2>
                        <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Full database snapshots of the BeeCore platform.</p>
                    </div>
                </div>
                <button type="button" wire:click="runBackupNow" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    Run backup now
                </button>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Started</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Type</th>
                                <th class="px-5 py-3.5 text-center text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Size</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Triggered by</th>
                                <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($backups as $backup)
                                <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-4 text-theme-xs text-gray-500 dark:text-gray-400">{{ $backup->started_at->format('d M Y, H:i') }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs font-medium capitalize text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">{{ $backup->type }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if($backup->status === 'completed')
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-500"><span class="size-1.5 rounded-full bg-success-500"></span>Completed</span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-error-50 px-2.5 py-1 text-theme-xs font-semibold text-error-600 dark:bg-error-500/15 dark:text-error-500"><span class="size-1.5 rounded-full bg-error-500"></span>{{ ucfirst($backup->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right text-theme-sm font-medium text-gray-600 dark:text-gray-400">{{ $fmtSize($backup->size_bytes) }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $backup->triggeredBy?->name ?? 'System' }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <button type="button" wire:click="downloadBackup({{ $backup->id }})" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50 px-3 py-2 text-theme-xs font-semibold text-brand-600 transition hover:border-brand-300 hover:bg-brand-100 dark:border-brand-500/25 dark:bg-brand-500/10 dark:text-brand-400">
                                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                            Download
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-14 text-center">
                                        <div class="mx-auto max-w-xs">
                                            <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                                <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                                            </span>
                                            <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No backups recorded yet</p>
                                            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Run your first backup to protect the platform data.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif($tab === 'exports')
        <!-- Data export -->
        <div class="space-y-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-cyan-500/10 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-400">
                        <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Generate a data export</h2>
                        <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Hand a tenant a portable copy of their records for migration or backup.</p>
                    </div>
                </div>
                <form wire:submit="runExport" class="mt-5 grid gap-3 sm:grid-cols-3">
                    <select wire:model="exportTenantId" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">Full platform</option>
                        @foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach
                    </select>
                    <select wire:model="exportType" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="full">Full (customers + invoices)</option>
                        <option value="customers">Customers only</option>
                        <option value="invoices">Invoices only</option>
                    </select>
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Generate export</button>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Generated</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Scope</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Type</th>
                                <th class="px-5 py-3.5 text-center text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($exports as $export)
                                <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-4 text-theme-xs text-gray-500 dark:text-gray-400">{{ $export->created_at->format('d M Y, H:i') }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $export->tenant?->name ?? 'Full platform' }}</td>
                                    <td class="px-5 py-4"><span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs font-medium capitalize text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">{{ $export->type }}</span></td>
                                    <td class="px-5 py-4 text-center">
                                        @if($export->status === 'completed')
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-500"><span class="size-1.5 rounded-full bg-success-500"></span>Completed</span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-error-50 px-2.5 py-1 text-theme-xs font-semibold text-error-600 dark:bg-error-500/15 dark:text-error-500"><span class="size-1.5 rounded-full bg-error-500"></span>{{ ucfirst($export->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <button type="button" wire:click="downloadExport({{ $export->id }})" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50 px-3 py-2 text-theme-xs font-semibold text-brand-600 transition hover:border-brand-300 hover:bg-brand-100 dark:border-brand-500/25 dark:bg-brand-500/10 dark:text-brand-400">
                                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                            Download
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-14 text-center">
                                        <div class="mx-auto max-w-xs">
                                            <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                                <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                            </span>
                                            <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No data exports yet</p>
                                            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Exports you generate will be listed here for download.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-3.5 text-theme-xs text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                <svg class="mt-0.5 size-4 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                Tenant deletion is non-destructive — archive a tenant from the <a href="{{ route('tenants') }}" class="font-medium text-brand-600 underline dark:text-brand-400">Tenants</a> page; archived tenants retain all data for recovery.
            </div>
        </div>
    @elseif($tab === 'import')
        <!-- Data import -->
        <div class="max-w-lg space-y-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-success-500/10 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                    <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                </span>
                <div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Import customers from CSV</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Columns: <code class="rounded bg-gray-100 px-1 py-0.5 font-mono text-theme-xs text-gray-700 dark:bg-white/[0.05] dark:text-gray-300">name,email,phone,package_name</code> (header row required).</p>
                </div>
            </div>
            <div>
                <label for="dm-import-tenant" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Tenant</label>
                <select id="dm-import-tenant" wire:model="importTenantId" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">Select tenant</option>
                    @foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach
                </select>
                @error('importTenantId') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="dm-import-file" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">CSV file</label>
                <input id="dm-import-file" wire:model="importFile" type="file" accept=".csv,text/csv" class="w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-theme-sm text-gray-500 shadow-theme-xs transition-colors file:mr-5 file:border-0 file:bg-gray-50 file:px-3.5 file:py-3 file:text-theme-sm file:font-medium file:text-gray-700 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:hover:file:bg-white/[0.05]">
                @error('importFile') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
            </div>
            <button type="button" wire:click="importCustomers" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Import customers</button>
            @if(!is_null($lastImportedCount))
                <div class="flex items-start gap-2.5 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-theme-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300">
                    <svg class="mt-0.5 size-4 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Imported {{ $lastImportedCount }} customer(s) on the last run.
                </div>
            @endif
        </div>
    @else
        <!-- Retention policy -->
        <div class="max-w-md space-y-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-violet-500/10 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400">
                    <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </span>
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Data retention policy</h2>
            </div>
            <form wire:submit="saveRetention" class="space-y-5">
                <div>
                    <label for="dm-retention" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Retain records for (days)</label>
                    <input id="dm-retention" wire:model="retentionDays" type="number" min="30" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    @error('retentionDays') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Save policy</button>
            </form>
        </div>
    @endif
</div>
