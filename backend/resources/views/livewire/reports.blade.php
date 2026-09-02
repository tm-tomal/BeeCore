<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">Analytics</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Business reports</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Collections, invoices, customers, and operating capacity for {{ \Carbon\Carbon::parse($period['from'])->format('d M Y') }} – {{ \Carbon\Carbon::parse($period['to'])->format('d M Y') }}.</p>
        </div>
        <div class="flex shrink-0 flex-col items-stretch gap-2 sm:flex-row sm:items-end sm:gap-3">
            <div>
                <label for="report-from" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">From</label>
                <input id="report-from" type="date" wire:model.live="from" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
            </div>
            <div>
                <label for="report-to" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">To</label>
                <input id="report-to" type="date" wire:model.live="to" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
            </div>
            <a href="{{ $printUrl }}" target="_blank" rel="noopener" class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Download PDF
            </a>
        </div>
    </header>

    <!-- Metric cards -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
        <x-dashboard-metric icon="wallet" label="Collections" :count="$metrics['collections']" decimals="2" currency value="{{ number_format($metrics['collections'], 2) }}" sub="{{ number_format($metrics['transactions']) }} transactions" />
        <x-dashboard-metric icon="receipt" label="Invoiced" :count="$metrics['invoiced']" decimals="2" currency value="{{ number_format($metrics['invoiced'], 2) }}" :trend="$metrics['collection_rate'].'% collected'" sub="Selected period" />
        <x-dashboard-metric icon="users" label="Customers" :count="$metrics['customers']" value="{{ number_format($metrics['customers']) }}" :trend="$metrics['active_customers'].' active'" sub="Total in workspace" />
        <x-dashboard-metric icon="signal" label="Online devices" :count="$metrics['online_devices']" value="{{ number_format($metrics['online_devices']) }}" sub="Network · {{ $metrics['resellers'] }} active resellers" />
    </section>

    <!-- Snapshot strip -->
    <section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="flex items-center justify-between gap-3">
                <span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Collection rate</span>
                <span class="inline-flex items-center gap-1.5 text-theme-sm font-bold {{ $metrics['collection_rate'] >= 80 ? 'text-success-600 dark:text-success-400' : ($metrics['collection_rate'] >= 50 ? 'text-warning-600 dark:text-warning-400' : 'text-error-600 dark:text-error-500') }}">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    {{ $metrics['collection_rate'] }}%
                </span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Average payment</span>
                <span class="text-theme-sm font-bold text-gray-800 dark:text-white/90">৳{{ number_format($metrics['avg_payment'], 2) }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Top method</span>
                <span class="max-w-[10rem] truncate text-theme-sm font-bold capitalize text-gray-800 dark:text-white/90">{{ $metrics['top_method'] ? str_replace('_', ' ', $metrics['top_method']) : '—' }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Top method value</span>
                <span class="text-theme-sm font-bold text-success-600 dark:text-success-400">৳{{ number_format($metrics['top_method_value'], 2) }}</span>
            </div>
        </div>
    </section>

    <!-- Detail panels -->
    <section class="grid grid-cols-1 gap-4 xl:grid-cols-2 md:gap-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Collections by method</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">How money is coming in this period.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-500">
                    <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    ৳{{ number_format($metrics['collections'], 2) }}
                </span>
            </div>

            @if($paymentMethods->isNotEmpty())
                <div class="space-y-5">
                    @foreach($paymentMethods as $index => $method)
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-4 text-theme-sm">
                                <span class="flex min-w-0 items-center gap-2.5">
                                    <span class="grid size-7 shrink-0 place-items-center rounded-lg border border-gray-200 text-theme-xs font-semibold text-gray-500 dark:border-gray-800 dark:text-gray-400">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="truncate capitalize text-gray-700 dark:text-gray-300">{{ str_replace('_', ' ', $method['payment_method']) }}</span>
                                    <span class="shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs text-gray-500 dark:bg-white/[0.05] dark:text-gray-400">{{ $method['transactions'] }} payments</span>
                                </span>
                                <span class="flex shrink-0 items-center gap-3">
                                    <span class="font-semibold text-gray-800 dark:text-white/90">৳{{ number_format($method['total'], 2) }}</span>
                                    <span class="w-11 text-right text-theme-xs text-gray-400">{{ $method['share'] }}%</span>
                                </span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-white/[0.06]">
                                <div class="h-2 rounded-full bg-brand-500" style="width: {{ max(3, ($method['total'] / $maxPaymentMethod) * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-xl border border-dashed border-gray-200 py-12 text-center dark:border-gray-800">
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">No payments in this period.</p>
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Invoice status</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Count and value by status this period.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 text-theme-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-500">
                    <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    ৳{{ number_format($metrics['invoiced'], 2) }}
                </span>
            </div>

            @if(count($invoiceStatuses) > 0)
                @php
                    $statusMeta = [
                        'paid' => ['bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500', 'check'],
                        'pending' => ['bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500', 'clock'],
                        'overdue' => ['bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500', 'alert'],
                        'cancelled' => ['bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400', 'x'],
                        'draft' => ['bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400', 'pen'],
                    ];
                    $statusIcon = [
                        'check' => '<polyline points="20 6 9 17 4 12"/>',
                        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
                        'alert' => '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
                        'x' => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
                        'pen' => '<path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/>',
                    ];
                @endphp
                <div class="space-y-3">
                    @foreach($invoiceStatuses as $row)
                        @php
                            $meta = $statusMeta[$row['status']] ?? ['bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400', 'x'];
                        @endphp
                        <div class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 bg-gray-50/40 px-4 py-3.5 dark:border-gray-800 dark:bg-white/[0.02]">
                            <span class="inline-flex items-center gap-2.5">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $meta[0] }}">
                                    <svg class="mr-1 size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $statusIcon[$meta[1]] ?? $statusIcon['x'] !!}</svg>
                                    {{ $row['status'] }}
                                </span>
                                <span class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $row['count'] }} invoice{{ $row['count'] === 1 ? '' : 's' }}</span>
                            </span>
                            <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">৳{{ number_format($row['value'], 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-xl border border-dashed border-gray-200 py-12 text-center dark:border-gray-800">
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">No invoices in this period.</p>
                </div>
            @endif
        </div>
    </section>

    <!-- PDF hint -->
    <p class="flex items-center justify-center gap-1.5 text-theme-xs text-gray-400 dark:text-gray-500">
        <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Download PDF opens a clean black &amp; white report for the current date range.
    </p>
</div>
