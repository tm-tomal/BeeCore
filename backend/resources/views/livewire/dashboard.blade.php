<div class="space-y-6">
    <!-- Page header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">{{ $isSaasView ? __('Platform control') : __('Operations overview') }}</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $workspaceName }}</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $isSaasView ? __('Manage tenants, access, and accountable platform activity.') : __('Live billing, customer, and network activity.') }}</p>
        </div>
        <div class="flex shrink-0 flex-col items-start gap-2 sm:items-end">
            <div class="inline-flex rounded-xl border border-gray-200 bg-white p-1 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <button type="button" wire:click="$set('range', '6m')"
                    class="rounded-lg px-3.5 py-1.5 text-theme-xs font-medium transition {{ $range === '6m' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}">6 months</button>
                <button type="button" wire:click="$set('range', '12m')"
                    class="rounded-lg px-3.5 py-1.5 text-theme-xs font-medium transition {{ $range === '12m' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}">12 months</button>
            </div>
            <div class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-theme-xs font-medium text-gray-500 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                <span class="size-2 rounded-full bg-success-500 animate-pulse"></span>
                Updated {{ now()->format('M d, H:i') }} · {{ $rangeLabel }}
            </div>
        </div>
    </div>

    <!-- Announcements -->
    @if($activeAnnouncements->isNotEmpty())
        <div class="space-y-3">
            @foreach($activeAnnouncements as $announcement)
                <div class="flex items-start gap-3 rounded-2xl border border-brand-100 bg-brand-50 px-4 py-3.5 dark:border-brand-500/20 dark:bg-brand-500/10">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-brand-500/15 text-brand-600 dark:text-brand-400">
                        <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ ucfirst($announcement->type) }} — {{ $announcement->title }}</p>
                        <p class="mt-0.5 text-theme-sm text-gray-600 dark:text-gray-400">{{ $announcement->body }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Metric cards -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
        @if($isSaasView)
            <x-dashboard-metric icon="buildings" label="Active tenants" :count="$metrics['tenants']" value="{{ number_format($metrics['tenants']) }}" :trend="'+'.$metrics['new_tenants'].' this month'" sub="{{ $metrics['trial_tenants'] }} trial · {{ $metrics['suspended_tenants'] }} suspended" />
            <x-dashboard-metric icon="wallet" label="SaaS MRR" :count="$metrics['saas_mrr']" decimals="2" currency value="{{ number_format($metrics['saas_mrr'], 2) }}" sub="৳{{ number_format($metrics['saas_arr'], 2) }} ARR" />
            <x-dashboard-metric icon="clock" label="Expiring soon" :count="$metrics['subscriptions_expiring']" value="{{ number_format($metrics['subscriptions_expiring']) }}" sub="Next 30 days" />
            <x-dashboard-metric icon="check" label="SaaS collected" :count="$metrics['saas_collected_this_month']" decimals="2" currency value="{{ number_format($metrics['saas_collected_this_month'], 2) }}" sub="৳{{ number_format($metrics['saas_collected_last_month'], 2) }} last month" :trend="($metrics['saas_collected_delta'] >= 0 ? '+' : '').$metrics['saas_collected_delta'].'% vs last month'" :trend-up="$metrics['saas_collected_delta'] >= 0" />
        @else
            <x-dashboard-metric icon="users" label="{{ __('Customers') }}" :count="$metrics['customers']" value="{{ number_format($metrics['customers']) }}" :trend="'+'.$metrics['new_customers'].' this month'" sub="{{ $metrics['active_customers'] }} active · {{ $metrics['suspended_customers'] }} suspended" />
            <x-dashboard-metric icon="wallet" label="{{ __('Collected this month') }}" :count="$metrics['monthly_revenue']" decimals="2" currency value="{{ number_format($metrics['monthly_revenue'], 2) }}" sub="৳{{ number_format($metrics['revenue_last_month'], 2) }} last month" :trend="($metrics['revenue_delta'] >= 0 ? '+' : '').$metrics['revenue_delta'].'% vs last month'" :trend-up="$metrics['revenue_delta'] >= 0" />
            <x-dashboard-metric icon="receipt" label="{{ __('Open invoices') }}" :count="$metrics['pending_billing']" value="{{ number_format($metrics['pending_billing']) }}" sub="৳{{ number_format($metrics['outstanding'], 2) }} billed · {{ $metrics['overdue_count'] }} overdue" />
            <x-dashboard-metric icon="signal" label="{{ __('Online devices') }}" :count="$metrics['online_devices']" value="{{ number_format($metrics['online_devices']) }}" sub="{{ $metrics['total_devices'] > 0 ? round($metrics['online_devices'] / $metrics['total_devices'] * 100) : 0 }}% of {{ number_format($metrics['total_devices']) }} devices" />
        @endif
    </section>

    <!-- Insight strip -->
    <section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            @if($isSaasView)
                <div class="flex items-center justify-between gap-3">
                    <span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Outstanding</span>
                    <span class="text-theme-sm font-bold text-warning-600 dark:text-warning-400">৳{{ number_format($insights['outstanding'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Overdue invoices</span>
                    <span class="text-theme-sm font-bold text-error-600 dark:text-error-400">{{ $insights['overdue'] }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">New tenants</span>
                    <span class="text-theme-sm font-bold text-success-600 dark:text-success-400">+{{ $insights['new_tenants'] }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Expiring (30d)</span>
                    <span class="text-theme-sm font-bold text-amber-600 dark:text-amber-400">{{ $insights['expiring'] }}</span>
                </div>
            @else
                <div class="flex items-center justify-between gap-3">
                    <span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('New customers') }}</span>
                    <span class="text-theme-sm font-bold text-success-600 dark:text-success-400">+{{ $insights['new_customers'] }}<span class="ml-1 font-medium text-theme-xs text-gray-400">({{ $insights['new_customers_pct'] >= 0 ? '+' : '' }}{{ $insights['new_customers_pct'] }}%)</span></span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Active share') }}</span>
                    <span class="text-theme-sm font-bold text-sky-600 dark:text-sky-400">{{ $insights['active_pct'] }}%</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Suspended') }}</span>
                    <span class="text-theme-sm font-bold text-gray-700 dark:text-gray-300">{{ $insights['suspended'] }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Overdue value') }}</span>
                    <span class="text-theme-sm font-bold text-error-600 dark:text-error-400">৳{{ number_format($insights['outstanding'], 2) }}</span>
                </div>
            @endif
        </div>
    </section>

    @if(!$isSaasView)
        <!-- Quick actions -->
        <section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
            <div class="flex flex-wrap items-center gap-2.5">
                <span class="mr-1 inline-flex items-center gap-1.5 text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    {{ __('Quick actions') }}
                </span>
                <a href="{{ route('customers') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-theme-xs font-medium text-gray-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    {{ __('Customers') }}
                </a>
                <a href="{{ route('billing') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-theme-xs font-medium text-gray-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    {{ __('Generate bills') }}
                </a>
                <a href="{{ route('payments') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-theme-xs font-medium text-gray-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    {{ __('Record payment') }}
                </a>
                <a href="{{ route('network') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-theme-xs font-medium text-gray-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="5" cy="5" r="3"/><circle cx="19" cy="5" r="3"/><circle cx="5" cy="19" r="3"/><circle cx="19" cy="19" r="3"/><path d="M7.5 6.5l9 11M6.5 7.5l11 9"/></svg>
                    Network
                </a>
                <a href="{{ route('resellers') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-theme-xs font-medium text-gray-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Resellers
                </a>
                <a href="{{ route('packages') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-theme-xs font-medium text-gray-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                    Packages
                </a>
            </div>
        </section>
    @endif

    <!-- Analytics charts -->
    @php
        $palette = ['#465FFF', '#7A5AF8', '#22C55E', '#F59E0B', '#EF4444', '#0EA5E9', '#98A2B3'];
        $axis = [
            'axisBorder' => ['show' => false],
            'axisTicks' => ['show' => false],
            'labels' => ['style' => ['fontSize' => '12px']],
        ];
        $chartEmpty = fn (array $data): bool => empty($data['values']) || (float) array_sum($data['values']) === 0.0;
    @endphp

    @if($isSaasView)
        @php
            $saasArea = [
                'series' => [['name' => 'Collected', 'data' => $charts['saas_collections']['values']]],
                'chart' => ['type' => 'area', 'height' => 300],
                'xaxis' => ['categories' => $charts['saas_collections']['labels']] + $axis,
                'stroke' => ['curve' => 'smooth', 'width' => 2.5],
                'colors' => ['#465FFF'],
                'fill' => ['type' => 'gradient', 'gradient' => ['shadeIntensity' => 1, 'opacityFrom' => 0.22, 'opacityTo' => 0.02, 'stops' => [0, 95]]],
            ];
            $tenantStatusDonut = [
                'series' => $charts['tenant_status']['values'],
                'labels' => $charts['tenant_status']['labels'],
                'chart' => ['type' => 'donut', 'height' => 280],
                'colors' => ['#22C55E', '#EF4444', '#F59E0B', '#98A2B3'],
                'stroke' => ['width' => 0],
                'plotOptions' => ['pie' => ['donut' => ['size' => '72%']]],
                'legend' => ['position' => 'bottom', 'fontSize' => '13px'],
            ];
            $planBar = [
                'series' => [['name' => 'Subscriptions', 'data' => $charts['plan_split']['values']]],
                'chart' => ['type' => 'bar', 'height' => 270],
                'xaxis' => ['categories' => $charts['plan_split']['labels']] + $axis,
                'colors' => ['#7A5AF8'],
                'plotOptions' => ['bar' => ['columnWidth' => '45%', 'borderRadius' => 5]],
            ];
            $invoiceDonut = [
                'series' => $charts['invoice_status']['values'],
                'labels' => $charts['invoice_status']['labels'],
                'chart' => ['type' => 'donut', 'height' => 270],
                'colors' => ['#22C55E', '#F59E0B', '#EF4444', '#98A2B3'],
                'stroke' => ['width' => 0],
                'plotOptions' => ['pie' => ['donut' => ['size' => '72%']]],
                'legend' => ['position' => 'bottom', 'fontSize' => '13px'],
            ];
        @endphp

        <section class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 xl:col-span-7">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">SaaS collections</h2>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Verified SaaS payments, last 12 months.</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-success-50 px-2.5 py-1 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                            Revenue
                        </span>
                    </div>
                    @if($chartEmpty($charts['saas_collections']))
                        <p class="grid h-[300px] place-items-center text-center text-theme-sm text-gray-500 dark:text-gray-400">No SaaS collections yet.</p>
                    @else
                        <div data-apex='@json($saasArea)'></div>
                    @endif
                </div>
            </div>
            <div class="col-span-12 xl:col-span-5">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Tenant status</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Active vs suspended ISP workspaces.</p>
                    @if($chartEmpty($charts['tenant_status']))
                        <p class="grid h-[280px] place-items-center text-center text-theme-sm text-gray-500 dark:text-gray-400">No tenants yet.</p>
                    @else
                        <div data-apex='@json($tenantStatusDonut)'></div>
                    @endif
                </div>
            </div>
            <div class="col-span-12 md:col-span-6 xl:col-span-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Plan distribution</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Active &amp; trialing subscriptions per plan.</p>
                    @if($chartEmpty($charts['plan_split']))
                        <p class="grid h-[270px] place-items-center text-center text-theme-sm text-gray-500 dark:text-gray-400">No active subscriptions yet.</p>
                    @else
                        <div data-apex='@json($planBar)'></div>
                    @endif
                </div>
            </div>
            <div class="col-span-12 md:col-span-6 xl:col-span-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Invoice value by status</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Where SaaS billing value sits today.</p>
                    @if($chartEmpty($charts['invoice_status']))
                        <p class="grid h-[270px] place-items-center text-center text-theme-sm text-gray-500 dark:text-gray-400">No SaaS invoices yet.</p>
                    @else
                        <div data-apex='@json($invoiceDonut)'></div>
                    @endif
                </div>
            </div>
        </section>
    @else
        @php
            $revenueArea = [
                'series' => [
                    ['name' => 'Collected', 'data' => $charts['revenue']['values']],
                    ['name' => 'Billed', 'data' => $charts['billed']['values']],
                ],
                'chart' => ['type' => 'area', 'height' => 300],
                'xaxis' => ['categories' => $charts['revenue']['labels']] + $axis,
                'stroke' => ['curve' => 'straight', 'width' => [2, 2]],
                'colors' => ['#465FFF', '#9CB9FF'],
                'fill' => ['type' => 'gradient', 'gradient' => ['shadeIntensity' => 1, 'opacityFrom' => 0.4, 'opacityTo' => 0.02, 'stops' => [0, 95]]],
                'legend' => ['show' => false],
                'markers' => ['size' => 0],
            ];
            $statusDonut = [
                'series' => $charts['customer_status']['values'],
                'labels' => $charts['customer_status']['labels'],
                'chart' => ['type' => 'donut', 'height' => 260],
                'colors' => ['#22C55E', '#F59E0B', '#EF4444', '#98A2B3'],
                'stroke' => ['width' => 0],
                'legend' => ['show' => false],
                'plotOptions' => ['pie' => ['donut' => ['size' => '82%']]],
            ];
            $signupsBar = [
                'series' => [['name' => 'New customers', 'data' => $charts['signups']['values']]],
                'chart' => ['type' => 'bar', 'height' => 270],
                'xaxis' => ['categories' => $charts['signups']['labels']] + $axis,
                'colors' => ['#7A5AF8'],
                'plotOptions' => ['bar' => ['columnWidth' => '45%', 'borderRadius' => 5]],
            ];
            $methodDonut = [
                'series' => $charts['methods']['values'],
                'labels' => $charts['methods']['labels'],
                'chart' => ['type' => 'donut', 'height' => 270],
                'colors' => ['#22C55E', '#0EA5E9', '#465FFF', '#F59E0B', '#7A5AF8'],
                'stroke' => ['width' => 0],
                'plotOptions' => ['pie' => ['donut' => ['size' => '72%']]],
                'legend' => ['position' => 'bottom', 'fontSize' => '13px'],
            ];
            $packageBar = [
                'series' => [['name' => 'Subscribers', 'data' => $charts['packages']['values']]],
                'chart' => ['type' => 'bar', 'height' => 270],
                'xaxis' => ['categories' => $charts['packages']['labels']] + $axis,
                'colors' => ['#0EA5E9'],
                'plotOptions' => ['bar' => ['columnWidth' => '45%', 'borderRadius' => 5]],
            ];
        @endphp

        <section class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 xl:col-span-7">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Revenue trend</h2>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Collected vs billed, last {{ $rangeLabel }}.</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span class="inline-flex items-center gap-1.5 text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                <span class="size-2 rounded-full bg-brand-500"></span> Collected
                            </span>
                            <span class="inline-flex items-center gap-1.5 text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                <span class="size-2 rounded-full" style="background:#9CB9FF"></span> Billed
                            </span>
                        </div>
                    </div>
                    @if($chartEmpty($charts['revenue']))
                        <p class="grid h-[300px] place-items-center text-center text-theme-sm text-gray-500 dark:text-gray-400">No collections yet in this period.</p>
                    @else
                        <div data-apex='@json($revenueArea)'></div>
                    @endif
                </div>
            </div>
            <div class="col-span-12 xl:col-span-5">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Customer status</h2>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Active, pending, inactive and suspended.</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs font-semibold text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">
                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            {{ array_sum($charts['customer_status']['values']) }} total
                        </span>
                    </div>
                    @if($chartEmpty($charts['customer_status']))
                        <p class="grid h-[280px] place-items-center text-center text-theme-sm text-gray-500 dark:text-gray-400">No customers yet.</p>
                    @else
                        @php
                            $customerLabels = $charts['customer_status']['labels'];
                            $customerValues = $charts['customer_status']['values'];
                            $customerTotal = (float) array_sum($customerValues);
                            $customerColors = [
                                'active' => '#22C55E', 'pending' => '#F59E0B', 'inactive' => '#98A2B3',
                                'suspended' => '#EF4444', 'terminated' => '#98A2B3', 'blocked' => '#111827',
                            ];
                        @endphp
                        <div class="relative">
                            <div data-apex='@json($statusDonut)'></div>
                            <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($customerTotal) }}</p>
                                    <p class="text-theme-xs font-medium text-gray-400 dark:text-gray-500">Total customers</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach($customerLabels as $index => $label)
                                @php
                                    $value = (int) ($customerValues[$index] ?? 0);
                                    $color = $customerColors[strtolower($label)] ?? '#64748B';
                                @endphp
                                <div class="flex items-center justify-between gap-2 rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 dark:border-gray-800 dark:bg-white/[0.02]">
                                    <span class="flex min-w-0 items-center gap-2 text-theme-xs text-gray-600 dark:text-gray-400">
                                        <span class="size-2.5 shrink-0 rounded-full" style="background:{{ $color }}"></span>
                                        <span class="truncate capitalize">{{ strtolower($label) }}</span>
                                    </span>
                                    <span class="flex shrink-0 items-center gap-2">
                                        <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ number_format($value) }}</span>
                                        <span class="w-9 text-right text-theme-xs text-gray-400">{{ $customerTotal > 0 ? round($value / $customerTotal * 100) : 0 }}%</span>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-span-12 md:col-span-6 xl:col-span-4">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">New customers</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Signups per month.</p>
                    @if($chartEmpty($charts['signups']))
                        <p class="grid h-[270px] place-items-center text-center text-theme-sm text-gray-500 dark:text-gray-400">No customers added yet.</p>
                    @else
                        <div data-apex='@json($signupsBar)'></div>
                    @endif
                </div>
            </div>
            <div class="col-span-12 md:col-span-6 xl:col-span-4">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Payment methods</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">How money is coming in.</p>
                    @if($chartEmpty($charts['methods']))
                        <p class="grid h-[270px] place-items-center text-center text-theme-sm text-gray-500 dark:text-gray-400">No payments recorded yet.</p>
                    @else
                        <div data-apex='@json($methodDonut)'></div>
                    @endif
                </div>
            </div>
            <div class="col-span-12 md:col-span-6 xl:col-span-4">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Popular packages</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Active subscribers per package.</p>
                    @if($chartEmpty($charts['packages']))
                        <p class="grid h-[270px] place-items-center text-center text-theme-sm text-gray-500 dark:text-gray-400">No active subscriptions yet.</p>
                    @else
                        <div data-apex='@json($packageBar)'></div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if($isSaasView)
        <!-- SaaS rows -->
        <section class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 xl:col-span-7">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Recent platform activity</h2>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Accountable actions across tenants.</p>
                        </div>
                        <a href="{{ route('audit-activity') }}" class="text-theme-sm shrink-0 font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">View audit log</a>
                    </div>
                    <div>
                        @forelse($recentAuditActivity as $log)
                            <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-3 last:border-0 dark:border-gray-800">
                                <div class="min-w-0">
                                    <div class="truncate text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ str_replace('.', ' · ', $log->action) }}</div>
                                    <div class="mt-0.5 truncate text-theme-xs text-gray-500 dark:text-gray-400">{{ $log->user?->name ?? 'System' }} · {{ $log->tenant?->name ?? 'Platform' }}</div>
                                </div>
                                <time class="shrink-0 text-theme-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->diffForHumans() }}</time>
                            </div>
                        @empty
                            <p class="py-10 text-center text-theme-sm text-gray-500 dark:text-gray-400">No platform activity recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-span-12 xl:col-span-5">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="mb-1 text-base font-semibold text-gray-800 dark:text-white/90">Platform controls</h2>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">Provision, monitor and manage the SaaS portfolio.</p>
                    <div class="mt-4 space-y-2">
                        <a href="{{ route('tenants') }}" class="flex items-center justify-between rounded-xl border border-gray-200 p-4 transition hover:border-brand-300 hover:bg-brand-50/40 dark:border-gray-800 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10">
                            <span class="flex items-center gap-3">
                                <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.7"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></span>
                                <span><span class="block text-theme-sm font-medium text-gray-800 dark:text-white/90">Tenant portfolio</span><span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">Provision, suspend, edit, or enter a workspace</span></span>
                            </span>
                            <span class="text-gray-400">→</span>
                        </a>
                        <a href="{{ route('saas-plans') }}" class="flex items-center justify-between rounded-xl border border-gray-200 p-4 transition hover:border-brand-300 hover:bg-brand-50/40 dark:border-gray-800 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10">
                            <span class="flex items-center gap-3">
                                <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.7"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg></span>
                                <span><span class="block text-theme-sm font-medium text-gray-800 dark:text-white/90">SaaS plans</span><span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">Manage pricing, trials, limits, and grace</span></span>
                            </span>
                            <span class="text-gray-400">→</span>
                        </a>
                        <a href="{{ route('platform-users') }}" class="flex items-center justify-between rounded-xl border border-gray-200 p-4 transition hover:border-brand-300 hover:bg-brand-50/40 dark:border-gray-800 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10">
                            <span class="flex items-center gap-3">
                                <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.7"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
                                <span><span class="block text-theme-sm font-medium text-gray-800 dark:text-white/90">Users and roles</span><span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">Control platform and tenant access</span></span>
                            </span>
                            <span class="text-gray-400">→</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @else
        <!-- Tenant rows -->
        <section class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 xl:col-span-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Recent collections</h2>
                        <a href="{{ route('payments') }}" class="text-theme-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">View payments</a>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($recentPayments as $payment)
                            <div class="flex items-center justify-between gap-4 py-3">
                                <div class="min-w-0">
                                    <div class="truncate text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->customer?->name ?? 'Customer' }}</div>
                                    <div class="mt-0.5 truncate text-theme-xs text-gray-500 dark:text-gray-400">{{ $payment->invoice?->invoice_number ?? strtoupper($payment->payment_method) }} · {{ $payment->payment_date->format('M d, H:i') }}</div>
                                </div>
                                <span class="shrink-0 text-theme-sm font-semibold text-success-600 dark:text-success-400">+৳{{ number_format($payment->amount, 2) }}</span>
                            </div>
                        @empty
                            <p class="py-10 text-center text-theme-sm text-gray-500 dark:text-gray-400">No collections in this workspace yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-span-12 xl:col-span-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Overdue queue</h2>
                        <a href="{{ route('billing') }}" class="text-theme-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">Open billing</a>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($overdueInvoices as $invoice)
                            <div class="flex items-center justify-between gap-4 py-3">
                                <div class="min-w-0">
                                    <div class="truncate text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $invoice->customer?->name ?? 'Deleted customer' }}</div>
                                    <div class="mt-0.5 truncate text-theme-xs text-gray-500 dark:text-gray-400">{{ $invoice->invoice_number }} · Due {{ $invoice->due_date?->format('M d') }}</div>
                                </div>
                                <span class="shrink-0 text-theme-sm font-semibold text-error-600 dark:text-error-400">৳{{ number_format($invoice->outstanding_amount, 2) }}</span>
                            </div>
                        @empty
                            <p class="py-10 text-center text-theme-sm text-gray-500 dark:text-gray-400">No overdue invoices.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    @endif
</div>
