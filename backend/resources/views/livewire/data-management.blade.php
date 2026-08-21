<div>
    <header class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
        <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Data management</h1>
        <p class="mt-2 text-sm text-slate-500">Backups, tenant data export, retention policy, and customer import.</p>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <div class="mb-5 flex flex-wrap gap-2">
        <button wire:click="$set('tab', 'backups')" class="px-4 py-2 text-sm font-bold {{ $tab === 'backups' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Backups</button>
        <button wire:click="$set('tab', 'exports')" class="px-4 py-2 text-sm font-bold {{ $tab === 'exports' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Data export</button>
        <button wire:click="$set('tab', 'import')" class="px-4 py-2 text-sm font-bold {{ $tab === 'import' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Data import</button>
        <button wire:click="$set('tab', 'retention')" class="px-4 py-2 text-sm font-bold {{ $tab === 'retention' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Retention policy</button>
    </div>

    @if($tab === 'backups')
        <div class="mb-5 flex justify-end"><button wire:click="runBackupNow" class="bc-primary">Run backup now</button></div>
        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Started</th><th>Type</th><th>Status</th><th>Size</th><th>Triggered by</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($backups as $backup)
                        <tr>
                            <td class="text-xs text-slate-500">{{ $backup->started_at->format('d M Y, H:i') }}</td>
                            <td class="capitalize">{{ $backup->type }}</td>
                            <td><span class="font-semibold {{ $backup->status === 'completed' ? 'text-emerald-300' : 'text-rose-300' }}">{{ ucfirst($backup->status) }}</span></td>
                            <td>{{ $backup->size_bytes ? number_format($backup->size_bytes / 1024, 1).' KB' : '—' }}</td>
                            <td>{{ $backup->triggeredBy?->name ?? 'System' }}</td>
                            <td class="text-right"><button wire:click="downloadBackup({{ $backup->id }})" class="font-semibold text-teal-300">Download</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 text-center text-slate-600">No backups recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif($tab === 'exports')
        <div class="mb-5 border border-white/10 bg-white/[0.02] p-4" style="border-radius:6px">
            <h2 class="mb-3 text-sm font-bold text-white">Generate a data export</h2>
            <form wire:submit="runExport" class="grid gap-3 sm:grid-cols-3">
                <select wire:model="exportTenantId" class="bc-field"><option value="">Full platform</option>@foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach</select>
                <select wire:model="exportType" class="bc-field"><option value="full">Full (customers + invoices)</option><option value="customers">Customers only</option><option value="invoices">Invoices only</option></select>
                <button type="submit" class="bc-primary">Generate export</button>
            </form>
        </div>

        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Generated</th><th>Scope</th><th>Type</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($exports as $export)
                        <tr>
                            <td class="text-xs text-slate-500">{{ $export->created_at->format('d M Y, H:i') }}</td>
                            <td>{{ $export->tenant?->name ?? 'Full platform' }}</td>
                            <td class="capitalize">{{ $export->type }}</td>
                            <td><span class="font-semibold {{ $export->status === 'completed' ? 'text-emerald-300' : 'text-rose-300' }}">{{ ucfirst($export->status) }}</span></td>
                            <td class="text-right"><button wire:click="downloadExport({{ $export->id }})" class="font-semibold text-teal-300">Download</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 text-center text-slate-600">No data exports generated yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="mt-4 text-xs text-slate-600">Tenant deletion is non-destructive — archive a tenant from the <a href="{{ route('tenants') }}" class="text-teal-300 underline">Tenants</a> page; archived tenants retain all data for recovery.</p>
    @elseif($tab === 'import')
        <div class="bc-panel max-w-lg space-y-4 p-5" style="border-radius:8px">
            <h2 class="font-bold text-white">Import customers from CSV</h2>
            <p class="text-xs text-slate-500">Columns: <code>name,email,phone,package_name</code> (header row required).</p>
            <div><label class="bc-label" for="dm-import-tenant">Tenant</label><select id="dm-import-tenant" wire:model="importTenantId" class="bc-field"><option value="">Select tenant</option>@foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach</select>@error('importTenantId')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
            <div><label class="bc-label" for="dm-import-file">CSV file</label><input id="dm-import-file" wire:model="importFile" type="file" accept=".csv,text/csv" class="bc-field">@error('importFile')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
            <button wire:click="importCustomers" class="bc-primary">Import customers</button>
            @if(!is_null($lastImportedCount))<p class="text-sm text-emerald-300">Imported {{ $lastImportedCount }} customer(s) on the last run.</p>@endif
        </div>
    @else
        <div class="bc-panel max-w-md space-y-4 p-5" style="border-radius:8px">
            <h2 class="font-bold text-white">Data retention policy</h2>
            <form wire:submit="saveRetention" class="space-y-4">
                <div><label class="bc-label" for="dm-retention">Retain records for (days)</label><input id="dm-retention" wire:model="retentionDays" type="number" min="30" class="bc-field">@error('retentionDays')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                <button type="submit" class="bc-primary">Save policy</button>
            </form>
        </div>
    @endif
</div>
