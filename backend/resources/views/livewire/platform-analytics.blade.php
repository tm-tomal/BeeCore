<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Platform analytics</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Deeper platform-wide metrics with point-in-time snapshot history.</p>
        </div>
        <button wire:click="recordSnapshotNow" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Record snapshot now</button>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Platform summary -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total tenants</p>
            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($totalTenants) }}</p>
            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ $activeTenants }} active · {{ $trialTenants }} trial · {{ $suspendedTenants }} suspended</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total customers</p>
            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($totalCustomers) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total resellers</p>
            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($totalResellers) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total revenue</p>
            <p class="mt-2 text-2xl font-bold text-success-600 dark:text-success-500">৳{{ number_format($totalRevenue, 2) }}</p>
        </div>
    </section>

    <!-- Revenue quality summary -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">MRR / ARR</p>
            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">৳{{ number_format($mrr, 2) }}</p>
            <p class="mt-1 text-theme-xs text-success-600 dark:text-success-500">৳{{ number_format($arr, 2) }} ARR</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">ARPU</p>
            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">৳{{ number_format($arpu, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Churn rate (month)</p>
            <p class="mt-2 text-2xl font-bold text-error-600 dark:text-error-500">{{ $churnRate }}%</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Add-on growth (6mo)</p>
            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $addonGrowth->sum() }}</p>
        </div>
    </section>

    <!-- Add-on growth & history -->
    <section class="grid grid-cols-12 gap-4 md:gap-6">
        <div class="col-span-12 xl:col-span-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Add-on assignment growth</h2>
                <ul class="mt-4">
                    @forelse($addonGrowth as $month => $count)
                        <li class="flex items-center justify-between border-b border-gray-100 py-2.5 last:border-0 dark:border-gray-800">
                            <span class="text-theme-sm text-gray-600 dark:text-gray-400">{{ $month }}</span>
                            <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $count }}</span>
                        </li>
                    @empty
                        <li class="py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">No add-ons assigned in this window.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="col-span-12 xl:col-span-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Analytics history</h2>
                <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                    <div class="w-full overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                                    <th class="px-4 py-3 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Recorded</th>
                                    <th class="px-4 py-3 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tenants</th>
                                    <th class="px-4 py-3 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">MRR</th>
                                    <th class="px-4 py-3 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">ARPU</th>
                                    <th class="px-4 py-3 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Churn</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($history as $snapshot)
                                    <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                        <td class="whitespace-nowrap px-4 py-3.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $snapshot->recorded_at->format('d M Y, H:i') }}</td>
                                        <td class="px-4 py-3.5 text-theme-sm text-gray-600 dark:text-gray-400">{{ $snapshot->active_tenants }}/{{ $snapshot->total_tenants }}</td>
                                        <td class="px-4 py-3.5 text-theme-sm font-medium text-gray-800 dark:text-white/90">৳{{ number_format($snapshot->mrr, 2) }}</td>
                                        <td class="px-4 py-3.5 text-theme-sm font-medium text-gray-800 dark:text-white/90">৳{{ number_format($snapshot->arpu, 2) }}</td>
                                        <td class="px-4 py-3.5 text-theme-sm text-gray-600 dark:text-gray-400">{{ $snapshot->churn_rate }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">No snapshots recorded yet.</td>
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
