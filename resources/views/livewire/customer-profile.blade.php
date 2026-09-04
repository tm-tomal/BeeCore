<div class="space-y-6">
    @php
        $sub = $subscription;
        $statusChip = match ($customer->status) {
            'active' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
            'pending' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
            'suspended' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
            default => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
        };
        $lat = $customer->latitude;
        $lng = $customer->longitude;
        $mapsUrl = $lat !== null && $lng !== null ? 'https://www.google.com/maps?q='.$lat.','.$lng : null;
        $openInvoices = $invoices->filter(fn ($inv) => in_array($inv->status, ['pending', 'overdue'], true))->values();
    @endphp

    <!-- Header -->
    <header class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-start gap-4">
            <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-brand-500/10 text-2xl font-extrabold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $customer->name }}</h1>
                    <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $statusChip }}">{{ __(ucfirst($customer->status)) }}</span>
                </div>
                <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-theme-sm text-gray-500 dark:text-gray-400">
                    @if($customer->phone)
                        <a href="tel:{{ $customer->phone }}" class="inline-flex items-center gap-1.5 transition hover:text-brand-600 dark:hover:text-brand-400">
                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            {{ $customer->phone }}
                        </a>
                    @endif
                    @if($customer->email)
                        <a href="mailto:{{ $customer->email }}" class="inline-flex items-center gap-1.5 transition hover:text-brand-600 dark:hover:text-brand-400">
                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            {{ $customer->email }}
                        </a>
                    @endif
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        {{ __('Member since') }} {{ $customer->created_at?->format('d M Y') ?? '—' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            <a href="{{ route('customers') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03] dark:hover:text-gray-100">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                {{ __('Back to customers') }}
            </a>
            <a href="{{ route('customers') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                {{ __('Edit in directory') }}
            </a>
        </div>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif
    @if(session()->has('error'))
        <div class="flex items-start gap-3 rounded-xl border border-error-200 bg-error-50 px-4 py-3 dark:border-error-500/20 dark:bg-error-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-error-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <p class="text-theme-sm text-error-700 dark:text-error-300">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Quick stats -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </span>
            <div>
                <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Total outstanding') }}</p>
                <p class="text-xl font-bold text-gray-800 dark:text-white/90">৳{{ number_format($outstanding, 2) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </span>
            <div>
                <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Next billing') }}</p>
                <p class="text-lg font-bold text-gray-800 dark:text-white/90">{{ $sub?->next_billing_date?->format('d M Y') ?? '—' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            </span>
            <div>
                <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('SMS wallet balance') }}</p>
                <p class="text-lg font-bold text-gray-800 dark:text-white/90">{{ number_format($smsBalance) }} {{ __('credits') }}</p>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-12 gap-6">
        <!-- Left: details -->
        <div class="col-span-12 space-y-6 xl:col-span-8">
            <!-- Service & connection -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="flex items-center gap-2.5">
                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                        <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Service & connection') }}</h2>
                        <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Current package and network login details.') }}</p>
                    </div>
                </div>

                @if($sub)
                    <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Package') }}</p>
                            <p class="mt-1 text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $sub->package_name }}</p>
                            <p class="text-theme-xs text-gray-400 dark:text-gray-500">{{ $sub->package?->bandwidth ? __('Bandwidth').': '.$sub->package->bandwidth : '&nbsp;' }}</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Price & cycle') }}</p>
                            <p class="mt-1 text-theme-sm font-semibold text-gray-800 dark:text-white/90">৳{{ number_format((float) $sub->price, 2) }}<span class="font-normal text-gray-400"> /{{ strtolower($sub->billing_cycle) }}</span></p>
                            <p class="text-theme-xs text-gray-400 dark:text-gray-500">{{ __(ucfirst($sub->billing_cycle)) }} billing</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Started') }}</p>
                            <p class="mt-1 text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $sub->started_at?->format('d M Y') ?? '—' }}</p>
                            <p class="text-theme-xs text-gray-400 dark:text-gray-500">{{ $sub->status === 'active' ? __('Active subscription') : __(ucfirst($sub->status)) }}</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Next renewal') }}</p>
                            <p class="mt-1 text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $sub->next_billing_date?->format('d M Y') ?? '—' }}</p>
                            <p class="text-theme-xs text-gray-400 dark:text-gray-500">{{ $sub->ended_at ? __('Ended').' '.$sub->ended_at->format('d M Y') : __('Renews automatically') }}</p>
                        </div>
                    </div>

                    <div x-data="{ reveal: false }" class="mt-4 rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('PPPoE / RADIUS login') }}</p>
                            @if($sub->pppoe_username)
                                <button type="button" @click="reveal = !reveal" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-theme-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                    <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <span x-text="reveal ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"></span>
                                </button>
                            @endif
                        </div>
                        @if($sub->pppoe_username)
                            <dl class="mt-3 grid gap-3 text-theme-sm sm:grid-cols-2">
                                <div>
                                    <dt class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Username') }}</dt>
                                    <dd class="mt-0.5 font-medium text-gray-800 dark:text-white/90">{{ $sub->pppoe_username }}</dd>
                                </div>
                                <div>
                                    <dt class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Password') }}</dt>
                                    <dd class="mt-0.5 font-medium text-gray-800 dark:text-white/90">
                                        <span x-show="reveal">{{ $sub->pppoe_password }}</span>
                                        <span x-show="!reveal">••••••••</span>
                                    </dd>
                                </div>
                            </dl>
                        @else
                            <p class="mt-2 text-theme-xs text-gray-400 dark:text-gray-500">{{ __('No PPPoE credentials saved for this subscription.') }}</p>
                        @endif
                    </div>
                @else
                    <div class="mt-4 rounded-xl border border-dashed border-gray-300 px-5 py-8 text-center dark:border-gray-700">
                        <p class="text-theme-sm font-medium text-gray-600 dark:text-gray-300">{{ __('No active subscription') }}</p>
                        <p class="mt-1 text-theme-xs text-gray-400 dark:text-gray-500">{{ __('This customer does not have an active recurring package.') }}</p>
                    </div>
                @endif
            </section>

            <!-- Address & location -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                            <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </span>
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Address & location') }}</h2>
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Where this subscriber is connected.') }}</p>
                        </div>
                    </div>
                    @if($mapsUrl)
                        <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-theme-xs font-medium text-gray-600 transition hover:bg-gray-50 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:text-brand-400">
                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            {{ __('Open in Google Maps') }}
                        </a>
                    @endif
                </div>

                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                        <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Address') }}</p>
                        @if($customer->full_address)
                            <p class="mt-1.5 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $customer->full_address }}</p>
                        @else
                            <p class="mt-1.5 text-theme-xs text-gray-400 dark:text-gray-500">{{ __('No address saved yet.') }}</p>
                        @endif
                        @if($lat !== null && $lng !== null)
                            <p class="mt-3 text-theme-xs text-gray-400 dark:text-gray-500">{{ $lat }}, {{ $lng }}</p>
                        @endif
                        @if(! $customer->full_address && ! $customer->has_map_coordinates)
                            <a href="{{ route('customers') }}" class="mt-3 inline-flex items-center gap-1.5 text-theme-xs font-semibold text-brand-600 transition hover:text-brand-700 dark:text-brand-400">
                                {{ __('Add address from the directory') }}
                                <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </a>
                        @endif
                    </div>
                    <div wire:ignore>
                        @if($customer->has_map_coordinates)
                            <div
                                data-address-map
                                data-editable="0"
                                data-lat="{{ $lat }}"
                                data-lng="{{ $lng }}"
                                class="h-56 w-full overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800"
                                aria-label="{{ __('Customer location map') }}"
                            ></div>
                        @else
                            <div class="flex h-56 w-full flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-gray-300 px-6 text-center dark:border-gray-700">
                                <span class="grid size-10 place-items-center rounded-xl bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </span>
                                <p class="text-theme-xs text-gray-400 dark:text-gray-500">{{ __('No map location set for this customer yet. Drop a pin when editing their profile.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            <!-- Recent invoices -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Recent invoices') }}</h2>
                        <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Last :count invoices for this customer.', ['count' => $invoices->count()]) }}</p>
                    </div>
                    @if($openInvoices->isNotEmpty())
                        <span class="inline-flex rounded-full bg-error-50 px-2.5 py-1 text-theme-xs font-semibold text-error-600 dark:bg-error-500/15 dark:text-error-400">{{ __(':count unpaid', ['count' => $openInvoices->count()]) }}</span>
                    @endif
                </div>
                <div class="mt-4 divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($invoices as $invoice)
                        @php
                            $invBadge = match ($invoice->status) {
                                'paid' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
                                'overdue' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
                                default => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                            };
                        @endphp
                        <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $invoice->invoice_number }}</p>
                                <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                                    @if($invoice->billing_period_start && $invoice->due_date)
                                        {{ $invoice->billing_period_start->format('d M Y') }} · due {{ $invoice->due_date->format('d M Y') }}
                                    @else
                                        {{ $invoice->created_at?->format('d M Y') ?? '—' }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">৳{{ number_format($invoice->total, 2) }}</span>
                                <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $invBadge }}">{{ __(ucfirst($invoice->status)) }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="py-6 text-center text-theme-sm text-gray-400 dark:text-gray-500">{{ __('No invoices yet for this customer.') }}</p>
                    @endforelse
                </div>
            </section>

            <!-- Recent payments -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Recent payments') }}</h2>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Last :count recorded payments.', ['count' => $payments->count()]) }}</p>
                </div>
                <div class="mt-4 divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($payments as $payment)
                        <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                            <div class="min-w-0">
                                <p class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ __(ucfirst($payment->payment_method ?? 'manual')) }} · {{ $payment->transaction_id ?? '—' }}</p>
                                <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $payment->payment_date?->format('d M Y h:i A') ?? '—' }}</p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 text-theme-sm font-semibold text-success-600 dark:text-success-400">
                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                ৳{{ number_format($payment->amount, 2) }}
                            </span>
                        </div>
                    @empty
                        <p class="py-6 text-center text-theme-sm text-gray-400 dark:text-gray-500">{{ __('No payments recorded yet.') }}</p>
                    @endforelse
                </div>
            </section>
        </div>

        <!-- Right: actions -->
        <div class="col-span-12 space-y-6 xl:col-span-4">
            <!-- Notification preferences -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="flex items-center gap-2.5">
                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                        <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Notifications') }}</h2>
                        <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Which channels can we use for this customer?') }}</p>
                    </div>
                </div>

                <div class="mt-4 space-y-3">
                    <button type="button" wire:click="toggleSms" class="flex w-full items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50/50 p-4 text-left transition hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02] dark:hover:bg-white/[0.04]">
                        <span class="flex items-center gap-3">
                            <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $customer->notify_sms ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400' : 'bg-gray-100 text-gray-400 dark:bg-white/[0.06] dark:text-gray-500' }}">
                                <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                            </span>
                            <span>
                                <span class="block text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ __('SMS') }}</span>
                                <span class="block text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Text messages to :phone', ['phone' => $customer->phone ?? __('—')]) }}</span>
                            </span>
                        </span>
                        <span class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition {{ $customer->notify_sms ? 'bg-success-500' : 'bg-gray-300 dark:bg-gray-700' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition {{ $customer->notify_sms ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </span>
                    </button>

                    <button type="button" wire:click="toggleEmail" class="flex w-full items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50/50 p-4 text-left transition hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02] dark:hover:bg-white/[0.04]">
                        <span class="flex items-center gap-3">
                            <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $customer->notify_email ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400' : 'bg-gray-100 text-gray-400 dark:bg-white/[0.06] dark:text-gray-500' }}">
                                <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </span>
                            <span>
                                <span class="block text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Email') }}</span>
                                <span class="block text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Messages to :email', ['email' => $customer->email ?? __('—')]) }}</span>
                            </span>
                        </span>
                        <span class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition {{ $customer->notify_email ? 'bg-success-500' : 'bg-gray-300 dark:bg-gray-700' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition {{ $customer->notify_email ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </span>
                    </button>
                </div>
                <p class="mt-3 flex items-start gap-1.5 text-theme-xs leading-4 text-gray-400 dark:text-gray-500">
                    <svg class="mt-0.5 size-3.5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    {{ __('Turning a channel off stops automated reminders and quick messages to this customer.') }}
                </p>
            </section>

            <!-- Send a message -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="flex items-center gap-2.5">
                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                        <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Send a message') }}</h2>
                        <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('One-off SMS or email to this customer.') }}</p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2 rounded-xl bg-gray-100 p-1 dark:bg-white/[0.04]" role="tablist">
                    <button type="button" wire:click="selectChannel('sms')" role="tab" class="rounded-lg px-3 py-2 text-theme-sm font-medium transition {{ $composeChannel === 'sms' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-800 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                        {{ __('SMS') }} @if(! $customer->phone || ! $customer->notify_sms)<span class="text-error-500">•</span>@endif
                    </button>
                    <button type="button" wire:click="selectChannel('email')" role="tab" class="rounded-lg px-3 py-2 text-theme-sm font-medium transition {{ $composeChannel === 'email' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-800 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                        {{ __('Email') }} @if(! $customer->email || ! $customer->notify_email)<span class="text-error-500">•</span>@endif
                    </button>
                </div>

                @if($composeChannel === 'sms')
                    @if(! $customer->phone)
                        <div class="mt-4 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-theme-xs text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">{{ __('Add a mobile number to this customer before sending SMS.') }}</div>
                    @elseif(! $customer->notify_sms)
                        <div class="mt-4 rounded-xl border border-warning-200 bg-warning-50 px-4 py-3 text-theme-xs text-warning-700 dark:border-warning-500/25 dark:bg-warning-500/10 dark:text-warning-400">{{ __('SMS notifications are off for this customer — enable them above to send.') }}</div>
                    @else
                        <div class="mt-4">
                            <label for="sms-message" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('SMS text') }}<span class="ml-0.5 text-error-500">*</span></label>
                            <textarea id="sms-message" wire:model="composeMessage" rows="4" maxlength="918" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ __('e.g. Dear :name, your internet bill for this month is due on…', ['name' => $customer->name]) }}"></textarea>
                            @error('composeMessage') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                            <p class="mt-1.5 flex items-center justify-between text-theme-xs text-gray-400 dark:text-gray-500">
                                <span>{{ __('Sent from your workspace SMS wallet (:credits credits available).', ['credits' => number_format($smsBalance)]) }}</span>
                                <span>{{ __('max 918 characters') }}</span>
                            </p>
                        </div>
                    @endif
                @else
                    @if(! $customer->email)
                        <div class="mt-4 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-theme-xs text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">{{ __('Add an email address to this customer before sending email.') }}</div>
                    @elseif(! $customer->notify_email)
                        <div class="mt-4 rounded-xl border border-warning-200 bg-warning-50 px-4 py-3 text-theme-xs text-warning-700 dark:border-warning-500/25 dark:bg-warning-500/10 dark:text-warning-400">{{ __('Email notifications are off for this customer — enable them above to send.') }}</div>
                    @else
                        <div class="mt-4 space-y-4">
                            <div>
                                <label for="email-subject" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Subject') }}<span class="ml-0.5 text-error-500">*</span></label>
                                <input id="email-subject" type="text" wire:model="composeSubject" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ __('e.g. Your internet bill is due') }}">
                                @error('composeSubject') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="email-body" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Message') }}<span class="ml-0.5 text-error-500">*</span></label>
                                <textarea id="email-body" wire:model="composeMessage" rows="5" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ __('Write your email message here…') }}"></textarea>
                                @error('composeMessage') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endif
                @endif

                <button type="button" wire:click="sendMessage" wire:loading.attr="disabled"
                    class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50"
                    @disabled(($composeChannel === 'sms' && (! $customer->phone || ! $customer->notify_sms)) || ($composeChannel === 'email' && (! $customer->email || ! $customer->notify_email)))>
                    <span wire:loading.remove wire:target="sendMessage">
                        <svg class="mr-1.5 inline size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        {{ $composeChannel === 'sms' ? __('Send SMS') : __('Send email') }}
                    </span>
                    <span wire:loading wire:target="sendMessage">{{ __('Sending...') }}</span>
                </button>
            </section>
        </div>
    </div>
</div>
