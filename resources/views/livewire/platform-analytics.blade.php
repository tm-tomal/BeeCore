<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Platform analytics</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Deeper platform-wide metrics with point-in-time snapshot history.</p>
        </div>
        <button type="button" wire:click="recordSnapshotNow" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
            Record snapshot now
        </button>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Platform summary -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </span>
            <div class="min-w-0">
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($totalTenants) }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Tenants</p>
                <p class="mt-0.5 truncate text-theme-xs text-gray-400 dark:text-gray-500">{{ $activeTenants }} active · {{ $trialTenants }} trial · {{ $suspendedTenants }} suspended</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-cyan-500/10 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($totalCustomers) }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Customers</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-violet-500/10 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($totalResellers) }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Resellers</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-success-500/10 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-success-600 dark:text-success-400">৳{{ number_format($totalRevenue, 2) }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Total revenue</p>
            </div>
        </div>
    </section>

    <!-- Revenue quality summary -->
    <section class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">MRR / ARR</p>
                <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-success-500/10 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                </span>
            </div>
            <p class="mt-2 text-xl font-bold text-gray-800 dark:text-white/90">৳{{ number_format($mrr, 2) }}</p>
            <p class="mt-0.5 text-theme-xs text-success-600 dark:text-success-500">৳{{ number_format($arr, 2) }} ARR</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">ARPU</p>
            <p class="mt-2 text-xl font-bold text-gray-800 dark:text-white/90">৳{{ number_format($arpu, 2) }}</p>
            <p class="mt-0.5 text-theme-xs text-gray-400 dark:text-gray-500">Per active tenant</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Churn rate</p>
            <p class="mt-2 text-xl font-bold {{ (float) $churnRate > 0 ? 'text-error-600 dark:text-error-400' : 'text-success-600 dark:text-success-400' }}">{{ $churnRate }}%</p>
            <p class="mt-0.5 text-theme-xs text-gray-400 dark:text-gray-500">Per month</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Add-ons · 6mo</p>
            <p class="mt-2 text-xl font-bold text-gray-800 dark:text-white/90">{{ number_format($addonGrowth->sum()) }}</p>
            <p class="mt-0.5 text-theme-xs text-gray-400 dark:text-gray-500">Assigned total</p>
        </div>
    </section>

    <!-- Add-on growth & history -->
    <section class="grid grid-cols-12 gap-4 md:gap-6">
        <div class="col-span-12 xl:col-span-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                        <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Add-on assignment growth</h2>
                        <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">New assignments per month over the last six months.</p>
                    </div>
                </div>
                @php $addonMax = max(1, max(array_values($addonGrowth->all() ?: [0]))); @endphp
                <div class="mt-5 space-y-3">
                    @forelse($addonGrowth as $month => $count)
                        <div>
                            <div class="flex items-center justify-between text-theme-sm">
                                <span class="font-medium text-gray-600 dark:text-gray-300">{{ $month }}</span>
                                <span class="font-semibold text-gray-800 dark:text-white/90">{{ $count }}</span>
                            </div>
                            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/[0.06]">
                                <div class="h-full rounded-full bg-brand-500" style="width: {{ (int) round($count / $addonMax * 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center text-theme-sm text-gray-500 dark:text-gray-400">No add-ons assigned in this window.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-span-12 xl:col-span-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-cyan-500/10 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-400">
                        <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Analytics history</h2>
                        <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Point-in-time snapshots recorded for trend comparison.</p>
                    </div>
                </div>
                <div class="mt-5 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                    <div class="w-full overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                                    <th class="px-4 py-3 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Recorded</th>
                                    <th class="px-4 py-3 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tenants</th>
                                    <th class="px-4 py-3 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">MRR</th>
                                    <th class="px-4 py-3 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">ARPU</th>
                                    <th class="px-4 py-3 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Churn</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($history as $snapshot)
                                    <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                        <td class="whitespace-nowrap px-4 py-3.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $snapshot->recorded_at->format('d M Y, H:i') }}</td>
                                        <td class="px-4 py-3.5">
                                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs font-semibold text-gray-600 dark:bg-white/[0.06] dark:text-gray-300">{{ $snapshot->active_tenants }}/{{ $snapshot->total_tenants }}</span>
                                        </td>
                                        <td class="px-4 py-3.5 text-right text-theme-sm font-medium text-gray-800 dark:text-white/90">৳{{ number_format($snapshot->mrr, 2) }}</td>
                                        <td class="px-4 py-3.5 text-right text-theme-sm font-medium text-gray-800 dark:text-white/90">৳{{ number_format($snapshot->arpu, 2) }}</td>
                                        <td class="px-4 py-3.5 text-right text-theme-sm {{ $snapshot->churn_rate > 0 ? 'text-error-600 dark:text-error-400' : 'text-gray-600 dark:text-gray-400' }}">{{ $snapshot->churn_rate }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-12 text-center text-theme-sm text-gray-500 dark:text-gray-400">No snapshots recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
