<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Reports &amp; analytics</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Growth, revenue, distribution, and conversion reporting across the platform.</p>
        </div>
        <button wire:click="exportCsv" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Export CSV</button>
    </header>

    <!-- Revenue & tenant summary -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Active ISPs</p>
            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($activeTenants) }}</p>
            <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $suspendedTenants }} suspended</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">MRR / ARR</p>
            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">৳{{ number_format($mrr, 2) }}</p>
            <p class="mt-1 text-theme-xs text-success-600 dark:text-success-500">৳{{ number_format($arr, 2) }} ARR</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Collected this month</p>
            <p class="mt-2 text-2xl font-bold text-success-600 dark:text-success-500">৳{{ number_format($collectedThisMonth, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Add-on revenue</p>
            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">৳{{ number_format($addonRevenue, 2) }}</p>
        </div>
    </section>

    <!-- Usage summary (30 days) -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">SMS revenue (30d)</p>
            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">৳{{ number_format($smsRevenue, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Email sent (30d)</p>
            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($emailSent30d) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">API requests (30d)</p>
            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($apiRequests30d) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Storage used</p>
            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($storageUsedGb) }} GB</p>
        </div>
    </section>

    <!-- Conversion & retention summary -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Trial conversion</p>
            <p class="mt-2 text-2xl font-bold text-success-600 dark:text-success-500">{{ $trialConversionRate }}%</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Churn rate (month)</p>
            <p class="mt-2 text-2xl font-bold text-error-600 dark:text-error-500">{{ $churnRate }}%</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Payment success rate</p>
            <p class="mt-2 text-2xl font-bold text-success-600 dark:text-success-500">{{ $paymentSuccessRate }}%</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Payment failure rate</p>
            <p class="mt-2 text-2xl font-bold text-error-600 dark:text-error-500">{{ $paymentFailureRate }}%</p>
        </div>
    </section>

    <!-- Growth & distribution -->
    <section class="grid grid-cols-12 gap-4 md:gap-6">
        <div class="col-span-12 lg:col-span-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">ISP growth (6 months)</h2>
                <ul class="mt-4">
                    @forelse($ispGrowth as $month => $count)
                        <li class="flex items-center justify-between border-b border-gray-100 py-2.5 last:border-0 dark:border-gray-800">
                            <span class="text-theme-sm text-gray-600 dark:text-gray-400">{{ $month }}</span>
                            <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $count }}</span>
                        </li>
                    @empty
                        <li class="py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">No new ISPs in this window.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-span-12 lg:col-span-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Customer growth (6 months)</h2>
                <ul class="mt-4">
                    @forelse($customerGrowth as $month => $count)
                        <li class="flex items-center justify-between border-b border-gray-100 py-2.5 last:border-0 dark:border-gray-800">
                            <span class="text-theme-sm text-gray-600 dark:text-gray-400">{{ $month }}</span>
                            <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $count }}</span>
                        </li>
                    @empty
                        <li class="py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">No new customers in this window.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-span-12 lg:col-span-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Subscription status distribution</h2>
                <ul class="mt-4">
                    @forelse($subscriptionsByStatus as $status => $count)
                        <li class="flex items-center justify-between border-b border-gray-100 py-2.5 last:border-0 dark:border-gray-800">
                            <span class="text-theme-sm capitalize text-gray-600 dark:text-gray-400">{{ str_replace('_', ' ', $status) }}</span>
                            <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $count }}</span>
                        </li>
                    @empty
                        <li class="py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">No subscriptions yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-span-12 lg:col-span-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Plan distribution</h2>
                <ul class="mt-4">
                    @forelse($planDistribution as $plan => $count)
                        <li class="flex items-center justify-between border-b border-gray-100 py-2.5 last:border-0 dark:border-gray-800">
                            <span class="text-theme-sm text-gray-600 dark:text-gray-400">{{ $plan }}</span>
                            <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $count }}</span>
                        </li>
                    @empty
                        <li class="py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">No plans assigned yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-span-12">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Customer distribution by tenant (top 10)</h2>
                <ul class="mt-4">
                    @forelse($customerDistribution as $tenant => $count)
                        <li class="flex items-center justify-between border-b border-gray-100 py-2.5 last:border-0 dark:border-gray-800">
                            <span class="text-theme-sm text-gray-600 dark:text-gray-400">{{ $tenant }}</span>
                            <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $count }}</span>
                        </li>
                    @empty
                        <li class="py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">No customers yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </section>
</div>
