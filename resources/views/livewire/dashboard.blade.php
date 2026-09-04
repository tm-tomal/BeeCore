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
                    class="rounded-lg px-3.5 py-1.5 text-theme-xs font-medium transition {{ $range === '6m' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}">{{ __(':months months', ['months' => 6]) }}</button>
                <button type="button" wire:click="$set('range', '12m')"
                    class="rounded-lg px-3.5 py-1.5 text-theme-xs font-medium transition {{ $range === '12m' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}">{{ __(':months months', ['months' => 12]) }}</button>
            </div>
            <div class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-theme-xs font-medium text-gray-500 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                <span class="size-2 rounded-full bg-success-500 animate-pulse"></span>
                {{ __('Updated') }} {{ now()->translatedFormat('M d, H:i') }} · {{ $rangeLabel }}
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
            <x-dashboard-metric icon="buildings" label="{{ __('Active tenants') }}" :count="$metrics['tenants']" value="{{ number_format($metrics['tenants']) }}" :href="route('tenants')" />
            <x-dashboard-metric icon="wallet" label="{{ __('SaaS MRR') }}" :amount="$metrics['saas_mrr']" currency />
            <x-dashboard-metric icon="clock" label="{{ __('Expiring soon') }}" :count="$metrics['subscriptions_expiring']" value="{{ number_format($metrics['subscriptions_expiring']) }}" />
            <x-dashboard-metric icon="check" label="{{ __('SaaS collected') }}" :amount="$metrics['saas_collected_this_month']" currency />
        @else
            <x-dashboard-metric icon="users" label="{{ __('Customers') }}" :count="$metrics['customers']" value="{{ number_format($metrics['customers']) }}" :href="route('customers')" />
            <x-dashboard-metric icon="wallet" label="{{ __('Collected this month') }}" :amount="$metrics['monthly_revenue']" currency :href="route('payments')" />
            <x-dashboard-metric icon="receipt" label="{{ __('Open invoices') }}" :count="$metrics['pending_billing']" value="{{ number_format($metrics['pending_billing']) }}" :href="route('billing')" />
            <x-dashboard-metric icon="signal" label="{{ __('Online devices') }}" :count="$metrics['online_devices']" value="{{ number_format($metrics['online_devices']) }}" :href="route('network')" />
        @endif
    </section>

    @if($isSaasView)
        <!-- Platform at a glance -->
        <section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Outstanding') }}</span>
                    <span class="text-theme-sm font-bold text-warning-600 dark:text-warning-400">৳{{ number_format($insights['outstanding'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Overdue invoices') }}</span>
                    <span class="text-theme-sm font-bold text-error-600 dark:text-error-400">{{ $insights['overdue'] }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('New tenants') }}</span>
                    <span class="text-theme-sm font-bold text-success-600 dark:text-success-400">+{{ $insights['new_tenants'] }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Expiring (30d)') }}</span>
                    <span class="text-theme-sm font-bold text-amber-600 dark:text-amber-400">{{ $insights['expiring'] }}</span>
                </div>
            </div>
        </section>
    @endif

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
                    {{ __('Network') }}
                </a>
                <a href="{{ route('resellers') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-theme-xs font-medium text-gray-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    {{ __('Resellers') }}
                </a>
                <a href="{{ route('packages') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-theme-xs font-medium text-gray-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                    {{ __('Packages') }}
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

        <section class="grid grid-cols-12 gap-4 md:gap-6 [&_.rounded-2xl]:h-full">
            <div class="col-span-12 xl:col-span-7">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('SaaS collections') }}</h2>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Verified SaaS payments, last 12 months.') }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-success-50 px-2.5 py-1 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                            {{ __('Revenue') }}
                        </span>
                    </div>
                    @if($chartEmpty($charts['saas_collections']))
                        <p class="grid h-[300px] place-items-center text-center text-theme-sm text-gray-500 dark:text-gray-400">{{ __('No SaaS collections yet.') }}</p>
                    @else
                        <div data-apex='@json($saasArea)'></div>
                    @endif
                </div>
            </div>
            <div class="col-span-12 xl:col-span-5">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Tenant status') }}</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Active vs suspended ISP workspaces.') }}</p>
                    @if($chartEmpty($charts['tenant_status']))
                        <p class="grid h-[280px] place-items-center text-center text-theme-sm text-gray-500 dark:text-gray-400">{{ __('No tenants yet.') }}</p>
                    @else
                        <div data-apex='@json($tenantStatusDonut)'></div>
                    @endif
                </div>
            </div>
            <div class="col-span-12 md:col-span-6 xl:col-span-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Plan distribution') }}</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Active & trialing subscriptions per plan.') }}</p>
                    @if($chartEmpty($charts['plan_split']))
                        <p class="grid h-[270px] place-items-center text-center text-theme-sm text-gray-500 dark:text-gray-400">{{ __('No active subscriptions yet.') }}</p>
                    @else
                        <div data-apex='@json($planBar)'></div>
                    @endif
                </div>
            </div>
            <div class="col-span-12 md:col-span-6 xl:col-span-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Invoice value by status') }}</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Where SaaS billing value sits today.') }}</p>
                    @if($chartEmpty($charts['invoice_status']))
                        <p class="grid h-[270px] place-items-center text-center text-theme-sm text-gray-500 dark:text-gray-400">{{ __('No SaaS invoices yet.') }}</p>
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
                'chart' => ['type' => 'area', 'height' => 320, 'toolbar' => ['show' => false], 'zoom' => ['enabled' => false]],
                'xaxis' => ['categories' => $charts['revenue']['labels']] + $axis,
                'yaxis' => ['min' => 0, 'forceNiceScale' => true, 'labels' => ['style' => ['fontSize' => '12px']]],
                'stroke' => ['curve' => 'smooth', 'width' => [2.5, 2]],
                'colors' => ['#465FFF', '#9CB9FF'],
                'fill' => ['type' => 'gradient', 'gradient' => ['shadeIntensity' => 1, 'opacityFrom' => 0.35, 'opacityTo' => 0.02, 'stops' => [0, 95]]],
                'legend' => ['show' => false],
                'markers' => ['size' => 0, 'hover' => ['size' => 6, 'strokeWidth' => 2, 'strokeColors' => ['#fff']]],
            ];
            $donutColorMap = [
                'active' => '#22C55E', 'pending' => '#F59E0B', 'inactive' => '#98A2B3',
                'suspended' => '#EF4444', 'terminated' => '#64748B', 'blocked' => '#111827',
            ];
            $donutColors = collect($charts['customer_status']['labels'])
                ->map(fn (string $label) => (string) ($donutColorMap[strtolower($label)] ?? '#98A2B3'))
                ->values()->all();
            $statusDonut = [
                'series' => $charts['customer_status']['values'],
                'labels' => $charts['customer_status']['labels'],
                'chart' => ['type' => 'donut', 'height' => 280],
                'colors' => $donutColors,
                'stroke' => ['width' => 0],
                'legend' => ['show' => false],
                'dataLabels' => ['enabled' => false],
                'plotOptions' => ['pie' => ['donut' => ['size' => '78%', 'labels' => ['show' => false]]]],
            ];
            $signupsBar = [
                'series' => [['name' => 'New customers', 'data' => $charts['signups']['values']]],
                'chart' => ['type' => 'bar', 'height' => 300, 'toolbar' => ['show' => false]],
                'xaxis' => ['categories' => $charts['signups']['labels']] + $axis,
                'colors' => ['#7A5AF8'],
                'plotOptions' => ['bar' => ['columnWidth' => '45%', 'borderRadius' => 6]],
                'dataLabels' => ['enabled' => false],
                'fill' => ['type' => 'gradient', 'gradient' => ['shade' => 'light', 'type' => 'vertical', 'shadeIntensity' => 0.45, 'opacityFrom' => 1, 'opacityTo' => 0.65, 'stops' => [0, 100]]],
            ];
            $methodDonutColors = ['#22C55E', '#0EA5E9', '#465FFF', '#F59E0B', '#7A5AF8'];
            $methodDonut = [
                'series' => $charts['methods']['values'],
                'labels' => $charts['methods']['labels'],
                'chart' => ['type' => 'donut', 'height' => 230],
                'colors' => $methodDonutColors,
                'stroke' => ['width' => 0],
                'legend' => ['show' => false],
                'dataLabels' => ['enabled' => false],
                'plotOptions' => ['pie' => ['donut' => ['size' => '74%', 'labels' => ['show' => false]]]],
            ];
            $packageBar = [
                'series' => [['name' => 'Subscribers', 'data' => $charts['packages']['values']]],
                'chart' => ['type' => 'bar', 'height' => 300, 'toolbar' => ['show' => false]],
                'xaxis' => ['categories' => $charts['packages']['labels']] + $axis,
                'colors' => ['#0EA5E9'],
                'plotOptions' => ['bar' => ['columnWidth' => '45%', 'borderRadius' => 6]],
                'dataLabels' => ['enabled' => false],
                'fill' => ['type' => 'gradient', 'gradient' => ['shade' => 'light', 'type' => 'vertical', 'shadeIntensity' => 0.45, 'opacityFrom' => 1, 'opacityTo' => 0.65, 'stops' => [0, 100]]],
            ];

            $revenueCollectedTotal = (float) array_sum($charts['revenue']['values']);
            $revenueBilledTotal = (float) array_sum($charts['billed']['values']);
            $revenueRate = $revenueBilledTotal > 0 ? (int) round($revenueCollectedTotal / $revenueBilledTotal * 100) : 0;

            $signupTotal = (int) array_sum($charts['signups']['values']);
            $signupBest = $charts['signups']['values'] ? max($charts['signups']['values']) : 0;
            $signupBestLabel = '';
            if ($charts['signups']['values']) {
                $signupBestIndex = array_search($signupBest, $charts['signups']['values'], true);
                $signupBestLabel = $signupBestIndex !== false ? (string) ($charts['signups']['labels'][$signupBestIndex] ?? '') : '';
            }

            $packageTotal = (int) array_sum($charts['packages']['values']);
            $packageBest = $charts['packages']['values'] ? max($charts['packages']['values']) : 0;
            $packageBestLabel = '';
            if ($charts['packages']['values']) {
                $packageBestIndex = array_search($packageBest, $charts['packages']['values'], true);
                $packageBestLabel = $packageBestIndex !== false ? (string) ($charts['packages']['labels'][$packageBestIndex] ?? '') : '';
            }
        @endphp

        <section class="grid grid-cols-12 gap-4 md:gap-6 [&_.rounded-2xl]:h-full">
            <div class="col-span-12 xl:col-span-7">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Revenue trend') }}</h2>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Collected vs billed, last :months.', ['months' => $rangeLabel]) }}</p>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1">
                            <span class="inline-flex items-center gap-1.5 text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                <span class="size-2 rounded-full bg-brand-500"></span>
                                {{ __('Collected') }}
                                <span class="font-semibold text-gray-800 dark:text-white/90">৳{{ number_format($revenueCollectedTotal, 0) }}</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                <span class="size-2 rounded-full" style="background:#9CB9FF"></span>
                                {{ __('Billed') }}
                                <span class="font-semibold text-gray-800 dark:text-white/90">৳{{ number_format($revenueBilledTotal, 0) }}</span>
                            </span>
                            <span class="mt-0.5 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-theme-xs font-semibold {{ $revenueRate >= 70 ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500' }}">
                                <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                                {{ __(':rate% of billed collected', ['rate' => $revenueRate]) }}
                            </span>
                        </div>
                    </div>
                    @if($chartEmpty($charts['revenue']))
                        <p class="grid h-[300px] place-items-center text-center text-theme-sm text-gray-500 dark:text-gray-400">{{ __('No collections yet in this period.') }}</p>
                    @else
                        <div data-apex='@json($revenueArea)'></div>
                    @endif
                </div>
            </div>
            <div class="col-span-12 xl:col-span-5">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Customer status') }}</h2>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Active, pending, inactive and suspended.') }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs font-semibold text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">
                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            {{ __(':count total', ['count' => array_sum($charts['customer_status']['values'])]) }}
                        </span>
                    </div>
                    @if($chartEmpty($charts['customer_status']))
                        <p class="grid h-[280px] place-items-center text-center text-theme-sm text-gray-500 dark:text-gray-400">{{ __('No customers yet.') }}</p>
                    @else
                        @php
                            $customerLabels = $charts['customer_status']['labels'];
                            $customerValues = $charts['customer_status']['values'];
                            $customerTotal = (float) array_sum($customerValues);
                            $customerColors = [
                                'active' => '#22C55E', 'pending' => '#F59E0B', 'inactive' => '#98A2B3',
                                'suspended' => '#EF4444', 'terminated' => '#98A2B3', 'blocked' => '#111827',
                            ];
                            $customerStatusNames = [
                                'active' => __('Active'), 'pending' => __('Pending'), 'inactive' => __('Inactive'),
                                'suspended' => __('Suspended'), 'terminated' => __('Terminated'), 'blocked' => __('Blocked'),
                            ];
                        @endphp
                        <div class="relative">
                            <div data-apex='@json($statusDonut)'></div>
                            <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <p data-count="{{ $customerTotal }}" class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($customerTotal) }}</p>
                                    <p class="text-theme-xs font-medium text-gray-400 dark:text-gray-500">{{ __('Total customers') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach($customerLabels as $index => $label)
                                @php
                                    $value = (int) ($customerValues[$index] ?? 0);
                                    $color = $customerColors[strtolower($label)] ?? '#64748B';
                                @endphp
                                <div class="rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 dark:border-gray-800 dark:bg-white/[0.02]">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="flex min-w-0 items-center gap-2 text-theme-xs text-gray-600 dark:text-gray-400">
                                            <span class="size-2.5 shrink-0 rounded-full" style="background:{{ $color }}"></span>
                                            <span class="truncate capitalize">{{ $customerStatusNames[strtolower($label)] ?? $label }}</span>
                                        </span>
                                        <span class="flex shrink-0 items-center gap-2">
                                            <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ number_format($value) }}</span>
                                            <span class="w-9 text-right text-theme-xs text-gray-400">{{ $customerTotal > 0 ? round($value / $customerTotal * 100) : 0 }}%</span>
                                        </span>
                                    </div>
                                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-200/70 dark:bg-white/[0.06]">
                                        <div class="h-full rounded-full" style="width:{{ $customerTotal > 0 ? round($value / $customerTotal * 100) : 0 }}%;background:{{ $color }}"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-span-12 md:col-span-6 xl:col-span-4">
                <div class="flex h-full flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('New customers') }}</h2>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Signups per month') }}</p>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 text-theme-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            {{ number_format($signupTotal) }}
                        </span>
                    </div>
                    <div class="mt-2 flex-1">
                        @if($chartEmpty($charts['signups']))
                            <p class="grid h-full place-items-center text-center text-theme-sm text-gray-500 dark:text-gray-400">{{ __('No customers added yet.') }}</p>
                        @else
                            <div data-apex='@json($signupsBar)'></div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-span-12 md:col-span-6 xl:col-span-4">
                <div class="flex h-full flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Payment methods') }}</h2>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('How money is coming in') }}</p>
                        </div>
                        @php $methodsTotalChip = (float) array_sum($charts['methods']['values']); @endphp
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-sky-50 px-2.5 py-1 text-theme-xs font-semibold text-sky-600 dark:bg-sky-500/15 dark:text-sky-400">
                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                            ৳{{ number_format($methodsTotalChip, 0) }}
                        </span>
                    </div>
                    @php
                        $methodsLabels = $charts['methods']['labels'];
                        $methodsValues = $charts['methods']['values'];
                        $methodsTotal = (float) array_sum($methodsValues);
                        $methodSegments = [];
                        $methodRunning = 0.0;
                        $methodCount = count($methodsLabels);
                        foreach ($methodsLabels as $index => $label) {
                            $methodValue = (float) ($methodsValues[$index] ?? 0);
                            $methodColor = $methodDonutColors[$index % count($methodDonutColors)] ?? '#98A2B3';
                            $methodEnd = $methodsTotal > 0 ? $methodRunning + (($methodValue / $methodsTotal) * 100) : 0.0;
                            if ($index === $methodCount - 1) {
                                $methodEnd = 100.0;
                            }
                            if ($methodEnd > $methodRunning) {
                                $methodSegments[] = $methodColor.' '.round($methodRunning, 2).'% '.round($methodEnd, 2).'%';
                            }
                            $methodRunning = $methodEnd;
                        }
                        $methodRing = $methodSegments ? implode(',', $methodSegments) : '#E4E7EC 0% 100%';
                    @endphp
                    <div class="flex flex-1 flex-col items-center justify-center py-2">
                        <div class="relative size-40">
                            <div class="absolute inset-0 rounded-full" style="background:conic-gradient({{ $methodRing }});-webkit-mask:radial-gradient(closest-side, transparent 70%, #000 71%);mask:radial-gradient(closest-side, transparent 70%, #000 71%);"></div>
                            <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <p class="flex items-baseline justify-center gap-0.5 text-2xl font-bold tabular-nums text-gray-900 dark:text-white">
                                        <span class="text-sm font-semibold text-gray-400 dark:text-gray-500">৳</span>
                                        <span data-count="{{ $methodsTotal }}">{{ number_format($methodsTotal, 0) }}</span>
                                    </p>
                                    <p class="mt-0.5 text-theme-xs font-medium text-gray-400 dark:text-gray-500">{{ __('Total collected') }}</p>
                                </div>
                            </div>
                        </div>
                        @if($methodCount > 0)
                            <div class="mt-4 w-full max-w-56 space-y-2">
                                @foreach($methodsLabels as $index => $label)
                                    @php
                                        $methodValue = (float) ($methodsValues[$index] ?? 0);
                                        $methodPct = $methodsTotal > 0 ? round($methodValue / $methodsTotal * 100) : 0;
                                        $methodColor = $methodDonutColors[$index % count($methodDonutColors)] ?? '#98A2B3';
                                    @endphp
                                    <div class="flex items-center justify-between gap-3 text-theme-xs">
                                        <span class="flex min-w-0 items-center gap-2 text-gray-600 dark:text-gray-400">
                                            <span class="size-2 shrink-0 rounded-full" style="background:{{ $methodColor }}"></span>
                                            <span class="truncate">{{ $label }}</span>
                                        </span>
                                        <span class="shrink-0 font-semibold text-gray-500 dark:text-gray-400">{{ $methodPct }}%</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-4 text-center text-theme-xs text-gray-400 dark:text-gray-500">{{ __('No payments recorded yet.') }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-span-12 md:col-span-6 xl:col-span-4">
                <div class="flex h-full flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Popular packages') }}</h2>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Active subscribers per package') }}</p>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-sky-50 px-2.5 py-1 text-theme-xs font-semibold text-sky-600 dark:bg-sky-500/15 dark:text-sky-400">
                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                            {{ number_format($packageTotal) }}
                        </span>
                    </div>
                    <div class="mt-2 flex-1">
                        @if($chartEmpty($charts['packages']))
                            <p class="grid h-full place-items-center text-center text-theme-sm text-gray-500 dark:text-gray-400">{{ __('No active subscriptions yet.') }}</p>
                        @else
                            <div data-apex='@json($packageBar)'></div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if($isSaasView)
        <!-- SaaS rows -->
        <section class="grid grid-cols-12 gap-4 md:gap-6 [&_.rounded-2xl]:h-full">
            <div class="col-span-12 xl:col-span-7">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Recent platform activity') }}</h2>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Accountable actions across tenants.') }}</p>
                        </div>
                        <a href="{{ route('audit-activity') }}" class="text-theme-sm shrink-0 font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">{{ __('View audit log') }}</a>
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
                            <p class="py-10 text-center text-theme-sm text-gray-500 dark:text-gray-400">{{ __('No platform activity recorded yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-span-12 xl:col-span-5">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="mb-1 text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Platform controls') }}</h2>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Provision, monitor and manage the SaaS portfolio.') }}</p>
                    <div class="mt-4 space-y-2">
                        <a href="{{ route('tenants') }}" class="flex items-center justify-between rounded-xl border border-gray-200 p-4 transition hover:border-brand-300 hover:bg-brand-50/40 dark:border-gray-800 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10">
                            <span class="flex items-center gap-3">
                                <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.7"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></span>
                                <span><span class="block text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ __('Tenant portfolio') }}</span><span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Provision, suspend, edit, or enter a workspace') }}</span></span>
                            </span>
                            <span class="text-gray-400">→</span>
                        </a>
                        <a href="{{ route('saas-plans') }}" class="flex items-center justify-between rounded-xl border border-gray-200 p-4 transition hover:border-brand-300 hover:bg-brand-50/40 dark:border-gray-800 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10">
                            <span class="flex items-center gap-3">
                                <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.7"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg></span>
                                <span><span class="block text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ __('SaaS plans') }}</span><span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Manage pricing, trials, limits, and grace') }}</span></span>
                            </span>
                            <span class="text-gray-400">→</span>
                        </a>
                        <a href="{{ route('platform-users') }}" class="flex items-center justify-between rounded-xl border border-gray-200 p-4 transition hover:border-brand-300 hover:bg-brand-50/40 dark:border-gray-800 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10">
                            <span class="flex items-center gap-3">
                                <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.7"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
                                <span><span class="block text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ __('Users and roles') }}</span><span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Control platform and tenant access') }}</span></span>
                            </span>
                            <span class="text-gray-400">→</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @else
        <!-- Tenant rows -->
        <section class="grid grid-cols-12 gap-4 md:gap-6 [&_.rounded-2xl]:h-full">
            <div class="col-span-12 xl:col-span-6">
                <div class="flex h-full flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Recent collections') }}</h2>
                        <a href="{{ route('payments') }}" class="inline-flex shrink-0 items-center gap-1 text-theme-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">
                            {{ __('View payments') }}
                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                    <div class="flex-1 divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($recentPayments as $payment)
                            <div class="flex items-center justify-between gap-4 py-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-success-50 text-theme-sm font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-400">{{ mb_strtoupper(mb_substr($payment->customer?->name ?: __('C'), 0, 1)) }}</span>
                                    <div class="min-w-0">
                                        <div class="truncate text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->customer?->name ?? __('Customer') }}</div>
                                        <div class="mt-0.5 truncate text-theme-xs text-gray-500 dark:text-gray-400">{{ $payment->invoice?->invoice_number ?? strtoupper($payment->payment_method) }} · {{ $payment->payment_date->format('M d, H:i') }}</div>
                                    </div>
                                </div>
                                <span class="shrink-0 text-theme-sm font-semibold text-success-600 dark:text-success-400">+৳{{ number_format($payment->amount, 2) }}</span>
                            </div>
                        @empty
                            <p class="m-auto max-w-xs py-10 text-center text-theme-sm text-gray-500 dark:text-gray-400">{{ __('No collections in this workspace yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-span-12 xl:col-span-6">
                <div class="flex h-full flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Overdue queue') }}</h2>
                        <a href="{{ route('billing') }}" class="inline-flex shrink-0 items-center gap-1 text-theme-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">
                            {{ __('Open billing') }}
                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                    <div class="flex-1 divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($overdueInvoices as $invoice)
                            <div class="flex items-center justify-between gap-4 py-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-error-50 text-theme-sm font-semibold text-error-600 dark:bg-error-500/15 dark:text-error-400">{{ mb_strtoupper(mb_substr($invoice->customer?->name ?: __('C'), 0, 1)) }}</span>
                                    <div class="min-w-0">
                                        <div class="truncate text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $invoice->customer?->name ?? __('Deleted customer') }}</div>
                                        <div class="mt-0.5 truncate text-theme-xs text-gray-500 dark:text-gray-400">{{ $invoice->invoice_number }} · {{ __('Due') }} {{ $invoice->due_date?->format('M d') }}</div>
                                    </div>
                                </div>
                                <span class="shrink-0 text-theme-sm font-semibold text-error-600 dark:text-error-400">৳{{ number_format($invoice->outstanding_amount, 2) }}</span>
                            </div>
                        @empty
                            <p class="m-auto max-w-xs py-10 text-center text-theme-sm text-gray-500 dark:text-gray-400">{{ __('No overdue invoices.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    @endif
</div>
