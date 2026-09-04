<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Audit activity</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Review authentication, impersonation, billing, and record changes across workspaces.</p>
        </div>
    </header>

    <!-- Filters -->
    <div class="grid grid-cols-1 gap-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:grid-cols-2 lg:grid-cols-3 lg:max-w-none">
        <div>
            <label for="audit-search" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Action</label>
            <div class="relative">
                <svg class="pointer-events-none absolute inset-y-0 left-0 ml-4 size-5 self-center stroke-current text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input id="audit-search" wire:model.live.debounce.300ms="search" type="search" placeholder="e.g. auth.login" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-11 pr-4 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
            </div>
        </div>
        <div>
            <label for="audit-tenant" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Workspace</label>
            <select id="audit-tenant" wire:model.live="tenantId" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                <option value="">All workspaces</option>
                @foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach
            </select>
        </div>
        <div class="flex items-end justify-end sm:col-span-2 lg:col-span-1">
            <p class="text-theme-xs text-gray-400 dark:text-gray-500">{{ $logs->total() }} event{{ $logs->total() === 1 ? '' : 's' }}</p>
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
                        <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">IP address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($logs as $log)
                        <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="whitespace-nowrap px-5 py-4 text-theme-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-5 py-4"><code class="rounded-md bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs font-semibold text-gray-700 dark:bg-white/[0.05] dark:text-gray-300">{{ $log->action }}</code></td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2.5">
                                    <span class="grid size-7 shrink-0 place-items-center rounded-full bg-brand-500/10 text-theme-xs font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ $log->user?->name ? strtoupper(substr($log->user->name, 0, 1)) : 'S' }}</span>
                                    <span class="text-theme-sm text-gray-600 dark:text-gray-400">{{ $log->user?->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $log->tenant_id ? 'bg-cyan-50 text-cyan-600 dark:bg-cyan-500/10 dark:text-cyan-400' : 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400' }}">{{ $log->tenant?->name ?? 'Platform' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="max-w-48 truncate text-theme-sm text-gray-600 dark:text-gray-400">{{ $log->subject_type ? class_basename($log->subject_type).' #'.$log->subject_id : '—' }}</div>
                            </td>
                            <td class="px-5 py-4 text-right"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs text-gray-500 dark:bg-white/[0.05] dark:text-gray-400">{{ $log->ip_address ?? '—' }}</code></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-14 text-center">
                                <div class="mx-auto max-w-xs">
                                    <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                        <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                    </span>
                                    <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No audit activity matches these filters</p>
                                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Platform actions are recorded here automatically.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())<div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $logs->links() }}</div>@endif
    </div>
</div>
