<div class="space-y-6">
    <!-- Page header -->
    <header>
        <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
        <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Data management</h1>
        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Backups, tenant data export, retention policy, and customer import.</p>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Tabs -->
    <div class="inline-flex flex-wrap items-center gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <button wire:click="$set('tab', 'backups')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'backups' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">Backups</button>
        <button wire:click="$set('tab', 'exports')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'exports' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">Data export</button>
        <button wire:click="$set('tab', 'import')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'import' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">Data import</button>
        <button wire:click="$set('tab', 'retention')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'retention' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">Retention policy</button>
    </div>

    @if($tab === 'backups')
        <!-- Backups -->
        <div class="space-y-4">
            <div class="flex justify-end">
                <button wire:click="runBackupNow" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Run backup now</button>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Started</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Type</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Size</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Triggered by</th>
                                <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($backups as $backup)
                                <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-4 text-theme-xs text-gray-500 dark:text-gray-400">{{ $backup->started_at->format('d M Y, H:i') }}</td>
                                    <td class="px-5 py-4 text-theme-sm capitalize text-gray-600 dark:text-gray-400">{{ $backup->type }}</td>
                                    <td class="px-5 py-4">
                                        @if($backup->status === 'completed')
                                            <span class="rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">{{ ucfirst($backup->status) }}</span>
                                        @else
                                            <span class="rounded-full bg-error-50 px-2.5 py-1 text-theme-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">{{ ucfirst($backup->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $backup->size_bytes ? number_format($backup->size_bytes / 1024, 1).' KB' : '—' }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $backup->triggeredBy?->name ?? 'System' }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <button wire:click="downloadBackup({{ $backup->id }})" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-theme-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">Download</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center text-theme-sm text-gray-500 dark:text-gray-400">No backups recorded yet.</td>
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
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Generate a data export</h2>
                <form wire:submit="runExport" class="mt-4 grid gap-3 sm:grid-cols-3">
                    <select wire:model="exportTenantId" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">Full platform</option>
                        @foreach($tenants as $tenant)
                            <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                        @endforeach
                    </select>
                    <select wire:model="exportType" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
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
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($exports as $export)
                                <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-4 text-theme-xs text-gray-500 dark:text-gray-400">{{ $export->created_at->format('d M Y, H:i') }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $export->tenant?->name ?? 'Full platform' }}</td>
                                    <td class="px-5 py-4 text-theme-sm capitalize text-gray-600 dark:text-gray-400">{{ $export->type }}</td>
                                    <td class="px-5 py-4">
                                        @if($export->status === 'completed')
                                            <span class="rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">{{ ucfirst($export->status) }}</span>
                                        @else
                                            <span class="rounded-full bg-error-50 px-2.5 py-1 text-theme-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">{{ ucfirst($export->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <button wire:click="downloadExport({{ $export->id }})" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-theme-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">Download</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-12 text-center text-theme-sm text-gray-500 dark:text-gray-400">No data exports generated yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="text-theme-xs text-gray-500 dark:text-gray-400">Tenant deletion is non-destructive — archive a tenant from the <a href="{{ route('tenants') }}" class="font-medium text-brand-600 underline dark:text-brand-400">Tenants</a> page; archived tenants retain all data for recovery.</p>
        </div>
    @elseif($tab === 'import')
        <!-- Data import -->
        <div class="max-w-lg space-y-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div>
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Import customers from CSV</h2>
                <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Columns: <code class="rounded bg-gray-100 px-1 py-0.5 font-mono text-theme-xs text-gray-700 dark:bg-white/[0.05] dark:text-gray-300">name,email,phone,package_name</code> (header row required).</p>
            </div>
            <div>
                <label for="dm-import-tenant" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Tenant</label>
                <select id="dm-import-tenant" wire:model="importTenantId" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">Select tenant</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                    @endforeach
                </select>
                @error('importTenantId') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="dm-import-file" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">CSV file</label>
                <input id="dm-import-file" wire:model="importFile" type="file" accept=".csv,text/csv" class="w-full rounded-lg border border-gray-300 bg-transparent text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                @error('importFile') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
            </div>
            <button wire:click="importCustomers" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Import customers</button>
            @if(!is_null($lastImportedCount))
                <p class="text-theme-sm text-success-600 dark:text-success-400">Imported {{ $lastImportedCount }} customer(s) on the last run.</p>
            @endif
        </div>
    @else
        <!-- Retention policy -->
        <div class="max-w-md space-y-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Data retention policy</h2>
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
