<div class="space-y-6">
    <!-- Page header -->
    <header>
        <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
        <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Audit activity</h1>
        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Review authentication, impersonation, billing, and record changes across workspaces.</p>
    </header>

    <!-- Filters -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:max-w-3xl">
        <div>
            <label for="audit-search" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Action</label>
            <input id="audit-search" wire:model.live.debounce.300ms="search" type="search" placeholder="e.g. auth.login" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
        </div>
        <div>
            <label for="audit-tenant" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Tenant</label>
            <select id="audit-tenant" wire:model.live="tenantId" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                <option value="">All workspaces</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Audit log table -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="w-full overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Time</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Action</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actor</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Workspace</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Subject</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">IP address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($logs as $log)
                        <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="whitespace-nowrap px-5 py-4 text-theme-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-5 py-4"><code class="rounded-md bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs font-semibold text-gray-700 dark:bg-white/[0.05] dark:text-gray-300">{{ $log->action }}</code></td>
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $log->tenant?->name ?? 'Platform' }}</td>
                            <td class="px-5 py-4">
                                <div class="max-w-48 truncate text-theme-sm text-gray-600 dark:text-gray-400">{{ $log->subject_type ? class_basename($log->subject_type).' #'.$log->subject_id : '—' }}</div>
                            </td>
                            <td class="px-5 py-4"><code class="font-mono text-theme-xs text-gray-500 dark:text-gray-400">{{ $log->ip_address ?? '—' }}</code></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-theme-sm text-gray-500 dark:text-gray-400">No audit activity matches these filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
