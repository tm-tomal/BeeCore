<div class="space-y-6">
    @php
        $iconCards = [
            ['label' => 'Active ISPs', 'value' => number_format($activeTenants), 'icon' => 'briefcase', 'tone' => 'bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400'],
            ['label' => 'MRR', 'value' => '৳'.number_format($mrr, 2), 'icon' => 'repeat', 'tone' => 'bg-success-500/10 text-success-600 dark:bg-success-500/15 dark:text-success-400'],
            ['label' => 'Collected this month', 'value' => '৳'.number_format($collectedThisMonth, 2), 'icon' => 'dollar', 'tone' => 'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400'],
            ['label' => 'Add-on revenue', 'value' => '৳'.number_format($addonRevenue, 2), 'icon' => 'box', 'tone' => 'bg-violet-500/10 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400'],
        ];
        $svgIcons = [
            'briefcase' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
            'repeat' => '<polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>',
            'dollar' => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
            'box' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/>',
        ];
        $panelFor = fn (string $key): array => [
            'sms' => ['icon' => 'chat', 'tone' => 'bg-fuchsia-500/10 text-fuchsia-600 dark:bg-fuchsia-500/15 dark:text-fuchsia-400'],
            'email' => ['icon' => 'mail', 'tone' => 'bg-sky-500/10 text-sky-600 dark:bg-sky-500/15 dark:text-sky-400'],
            'api' => ['icon' => 'code', 'tone' => 'bg-cyan-500/10 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-400'],
            'storage' => ['icon' => 'database', 'tone' => 'bg-amber-500/10 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400'],
        ][$key] ?? ['icon' => 'chart', 'tone' => 'bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400'];
        $iconSvg = function (string $key): string {
            return match ($key) {
                'chat' => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>',
                'mail' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
                'code' => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
                'database' => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>',
                'chart' => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
                default => '<circle cx="12" cy="12" r="10"/>',
            };
        };
        $maxOf = fn (iterable $items) => max(1, max(collect($items)->values()->all() ?: [0]));
    @endphp

    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Reports &amp; analytics</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Growth, revenue, distribution, and conversion reporting across the platform.</p>
        </div>
        <button type="button" wire:click="exportCsv" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export CSV
        </button>
    </header>

    <!-- Revenue & tenant summary -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($iconCards as $card)
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="grid size-10 shrink-0 place-items-center rounded-lg {{ $card['tone'] }}">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $svgIcons[$card['icon']] !!}</svg>
                </span>
                <div class="min-w-0">
                    <p class="truncate text-xl font-bold text-gray-800 dark:text-white/90">{{ $card['value'] }}</p>
                    <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                </div>
            </div>
        @endforeach
    </section>

    <!-- Usage summary (30 days) -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $usage = [
                ['label' => 'SMS revenue (30d)', 'value' => '৳'.number_format($smsRevenue, 2), 'key' => 'sms'],
                ['label' => 'Email sent (30d)', 'value' => number_format($emailSent30d), 'key' => 'email'],
                ['label' => 'API requests (30d)', 'value' => number_format($apiRequests30d), 'key' => 'api'],
                ['label' => 'Storage used', 'value' => number_format($storageUsedGb).' GB', 'key' => 'storage'],
            ];
        @endphp
        @foreach($usage as $item)
            @php $meta = $panelFor($item['key']); @endphp
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="grid size-10 shrink-0 place-items-center rounded-lg {{ $meta['tone'] }}">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $iconSvg($meta['icon']) !!}</svg>
                </span>
                <div>
                    <p class="text-xl font-bold text-gray-800 dark:text-white/90">{{ $item['value'] }}</p>
                    <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ $item['label'] }}</p>
                </div>
            </div>
        @endforeach
    </section>

    <!-- Conversion & retention summary -->
    <section class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        @php
            $health = [
                ['label' => 'Trial conversion', 'value' => $trialConversionRate.'%', 'ok' => true],
                ['label' => 'Churn rate (month)', 'value' => $churnRate.'%', 'ok' => false],
                ['label' => 'Payment success', 'value' => $paymentSuccessRate.'%', 'ok' => true],
                ['label' => 'Payment failure', 'value' => $paymentFailureRate.'%', 'ok' => false],
            ];
        @endphp
        @foreach($health as $item)
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $item['label'] }}</p>
                <p class="mt-2 text-xl font-bold {{ $item['ok'] ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400' }}">{{ $item['value'] }}</p>
            </div>
        @endforeach
    </section>

    <!-- Growth & distribution -->
    <section class="grid grid-cols-12 gap-4 md:gap-6">
        @php $ispMax = $maxOf($ispGrowth); $customerMax = $maxOf($customerGrowth); @endphp
        <div class="col-span-12 lg:col-span-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                        <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </span>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">ISP growth (6 months)</h2>
                </div>
                <div class="mt-5 space-y-3">
                    @forelse($ispGrowth as $month => $count)
                        <div>
                            <div class="flex items-center justify-between text-theme-sm">
                                <span class="font-medium text-gray-600 dark:text-gray-300">{{ $month }}</span>
                                <span class="rounded-full bg-brand-50 px-2 py-0.5 font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ $count }}</span>
                            </div>
                            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/[0.06]">
                                <div class="h-full rounded-full bg-brand-500" style="width: {{ (int) round($count / $ispMax * 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">No new ISPs in this window.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-cyan-500/10 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-400">
                        <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </span>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Customer growth (6 months)</h2>
                </div>
                <div class="mt-5 space-y-3">
                    @forelse($customerGrowth as $month => $count)
                        <div>
                            <div class="flex items-center justify-between text-theme-sm">
                                <span class="font-medium text-gray-600 dark:text-gray-300">{{ $month }}</span>
                                <span class="rounded-full bg-cyan-50 px-2 py-0.5 font-semibold text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-400">{{ $count }}</span>
                            </div>
                            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/[0.06]">
                                <div class="h-full rounded-full bg-cyan-500" style="width: {{ (int) round($count / $customerMax * 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">No new customers in this window.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-violet-500/10 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400">
                        <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    </span>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Subscription status distribution</h2>
                </div>
                <div class="mt-5 space-y-2.5">
                    @forelse($subscriptionsByStatus as $status => $count)
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 px-3.5 py-2.5 dark:border-gray-800">
                            <span class="text-theme-sm capitalize text-gray-600 dark:text-gray-400">{{ str_replace('_', ' ', $status) }}</span>
                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs font-bold text-gray-700 dark:bg-white/[0.06] dark:text-gray-300">{{ $count }}</span>
                        </div>
                    @empty
                        <div class="py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">No subscriptions yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-success-500/10 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                        <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/></svg>
                    </span>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Plan distribution</h2>
                </div>
                <div class="mt-5 space-y-2.5">
                    @forelse($planDistribution as $plan => $count)
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 px-3.5 py-2.5 dark:border-gray-800">
                            <span class="text-theme-sm text-gray-600 dark:text-gray-400">{{ $plan }}</span>
                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs font-bold text-gray-700 dark:bg-white/[0.06] dark:text-gray-300">{{ $count }}</span>
                        </div>
                    @empty
                        <div class="py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">No plans assigned yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-span-12">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-warning-500/10 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                        <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </span>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Customer distribution by tenant (top 10)</h2>
                </div>
                <div class="mt-5 grid gap-2.5 sm:grid-cols-2">
                    @forelse($customerDistribution as $tenant => $count)
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 px-3.5 py-2.5 dark:border-gray-800">
                            <span class="min-w-0 truncate text-theme-sm text-gray-600 dark:text-gray-400">{{ $tenant }}</span>
                            <span class="inline-flex shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs font-bold text-gray-700 dark:bg-white/[0.06] dark:text-gray-300">{{ $count }}</span>
                        </div>
                    @empty
                        <div class="col-span-2 py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">No customers yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>
