<div class="space-y-6">
    <!-- Page header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">{{ __('Workspace') }}</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $checkoutAddon ? __('Checkout') : __('Add-on marketplace') }}</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $checkoutAddon ? __('Review your order, pick a payment method and confirm.') : __('Buy extra features for your workspace — pay online with bKash, or send a bank transfer that BeeCore verifies.') }}</p>
        </div>
    </div>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="flex items-start gap-3 rounded-xl border border-error-200 bg-error-50 px-4 py-3 dark:border-error-500/20 dark:bg-error-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-error-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <p class="text-theme-sm text-error-700 dark:text-error-300">{{ session('error') }}</p>
        </div>
    @endif

    @if($checkoutAddon)
        @php
            $checkoutCycleLabel = ['one_time' => __('One-time'), 'monthly' => __('Monthly'), 'yearly' => __('Yearly')][$checkoutAddon->billing_cycle] ?? $checkoutAddon->billing_cycle;
            $checkoutPrice = (float) $checkoutAddon->price;
            $onlineMethods = collect($paymentMethods)->where('manual', false)->values();
            $manualMethods = collect($paymentMethods)->where('manual', true)->values();
            $chosen = collect($paymentMethods)->firstWhere('key', $checkoutGateway);
        @endphp

        <!-- Checkout page -->
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">{{ __('Secure checkout') }}</p>
                    <h2 class="mt-1 text-title-sm font-bold text-gray-900 dark:text-white">{{ __('Buy :name', ['name' => $checkoutAddon->name]) }}</h2>
                    <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Complete payment below to activate this add-on on your workspace.') }}</p>
                </div>
                <button type="button" wire:click="cancelCheckout" class="inline-flex w-fit items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    {{ __('Back to marketplace') }}
                </button>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-5">
                <!-- Order summary -->
                <div class="lg:col-span-2">
                    <div class="flex h-full flex-col rounded-xl border border-gray-200 bg-gray-50/60 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="flex items-center gap-3">
                            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand-500 to-indigo-500 text-base font-bold text-white">{{ strtoupper(substr($checkoutAddon->name, 0, 1)) }}</span>
                            <div class="min-w-0">
                                <p class="text-theme-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Order summary') }}</p>
                                <p class="mt-0.5 truncate text-lg font-bold text-gray-900 dark:text-white">{{ $checkoutAddon->name }}</p>
                                <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $categories[$checkoutAddon->category] ?? $checkoutAddon->category }} · {{ $checkoutCycleLabel }}</p>
                            </div>
                        </div>

                        @if($checkoutAddon->description)
                            <p class="mt-4 text-theme-sm leading-5 text-gray-600 dark:text-gray-400">{{ $checkoutAddon->description }}</p>
                        @endif

                        <dl class="mt-5 space-y-3 border-t border-gray-200 pt-4 text-theme-sm dark:border-gray-800">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('Billing') }}</dt>
                                <dd class="font-medium text-gray-800 dark:text-white/90">
                                    {{ $checkoutAddon->billing_cycle === 'one_time' ? __('One-time charge') : __('Recurring subscription charge') }}
                                </dd>
                            </div>
                            @if($checkoutAddon->usage_limit)
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-gray-500 dark:text-gray-400">{{ __('Usage') }}</dt>
                                    <dd class="font-medium text-gray-800 dark:text-white/90">{{ number_format($checkoutAddon->usage_limit) }} {{ $checkoutAddon->usage_unit }}</dd>
                                </div>
                            @endif
                            <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-3 dark:border-gray-800">
                                <dt class="font-medium text-gray-800 dark:text-white/90">{{ __('Amount') }}</dt>
                                <dd class="text-title-sm font-bold text-gray-900 dark:text-white">৳{{ number_format($checkoutPrice, 2) }}</dd>
                            </div>
                        </dl>

                        <div class="mt-4 rounded-lg border border-brand-100 bg-brand-50/60 px-3.5 py-2.5 text-theme-xs text-gray-600 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-gray-300">
                            @if($checkoutAddon->billing_cycle === 'one_time')
                                {{ __('Charged once. :name stays active until you remove it.', ['name' => $checkoutAddon->name]) }}
                            @else
                                {{ __('Billed with your BeeCore subscription. It renews automatically each :cycle until you cancel it.', ['cycle' => $checkoutAddon->billing_cycle === 'yearly' ? __('year') : __('month')]) }}
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payment methods -->
                <div class="lg:col-span-3">
                    @if($onlineMethods->isNotEmpty())
                        <p class="mb-2 text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Online payment') }} <span class="font-normal text-theme-xs text-success-600 dark:text-success-400">· {{ __('pay securely via bKash') }}</span></p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach($onlineMethods as $method)
                                @php
                                    $style = match ($method['provider'] ?? null) {
                                        'bkash' => ['letter' => 'bK', 'avatar' => 'from-pink-500 to-rose-500', 'label' => 'bKash'],
                                        default => ['letter' => 'On', 'avatar' => 'from-gray-400 to-gray-500', 'label' => 'Online'],
                                    };
                                @endphp
                                <button type="button" wire:click="selectGateway('{{ $method['key'] }}')"
                                    class="flex items-center gap-3 rounded-xl border p-4 text-left transition"
                                    @class([
                                        'border-brand-500 bg-brand-50/60 ring-2 ring-brand-500/20 dark:border-brand-500 dark:bg-brand-500/10' => $checkoutGateway === $method['key'],
                                        'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700' => $checkoutGateway !== $method['key'],
                                    ])>
                                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-gradient-to-br text-theme-sm font-bold text-white {{ $style['avatar'] }}">{{ $style['letter'] }}</span>
                                    <span class="min-w-0">
                                        <span class="block text-theme-sm font-semibold text-gray-900 dark:text-white">{{ $method['name'] }}</span>
                                        <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">{{ __('bKash') }} · {{ __('secure online payment') }}</span>
                                    </span>
                                    <span aria-hidden="true" class="ml-auto grid size-5 shrink-0 place-items-center rounded-full border-2 transition {{ $checkoutGateway === $method['key'] ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10' : 'border-gray-300 dark:border-gray-700' }}">
                                        <span class="size-2.5 rounded-full transition {{ $checkoutGateway === $method['key'] ? 'bg-brand-500' : '' }}"></span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    @if($manualMethods->isNotEmpty())
                        <p class="mt-5 mb-2 text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Manual payment') }} <span class="font-normal text-theme-xs text-warning-600 dark:text-warning-400">· {{ __('BeeCore Account team verifies') }}</span></p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach($manualMethods as $method)
                                <button type="button" wire:click="selectGateway('{{ $method['key'] }}')"
                                    class="flex items-center gap-3 rounded-xl border p-4 text-left transition"
                                    @class([
                                        'border-warning-500 bg-warning-50/60 ring-2 ring-warning-500/20 dark:border-warning-500 dark:bg-warning-500/10' => $checkoutGateway === $method['key'],
                                        'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700' => $checkoutGateway !== $method['key'],
                                    ])>
                                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-gradient-to-br text-theme-sm font-bold text-white from-emerald-500 to-teal-500">Ba</span>
                                    <span class="min-w-0">
                                        <span class="block text-theme-sm font-semibold text-gray-900 dark:text-white">{{ $method['name'] }}</span>
                                        <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Bank transfer') }} · {{ __('manual verification') }}</span>
                                    </span>
                                    <span aria-hidden="true" class="ml-auto grid size-5 shrink-0 place-items-center rounded-full border-2 transition {{ $checkoutGateway === $method['key'] ? 'border-warning-500 bg-warning-50 dark:bg-warning-500/10' : 'border-gray-300 dark:border-gray-700' }}">
                                        <span class="size-2.5 rounded-full transition {{ $checkoutGateway === $method['key'] ? 'bg-warning-500' : '' }}"></span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    @error('checkoutGateway')
                        <p class="mt-3 rounded-lg border border-error-200 bg-error-50 px-3.5 py-2.5 text-theme-xs text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">{{ $message }}</p>
                    @enderror

                    @if($chosen && ! empty($chosen['account']))
                        @php $acct = $chosen['account']; @endphp
                        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 dark:border-emerald-500/25 dark:bg-emerald-500/10">
                            <p class="text-theme-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-400">{{ __('Pay to this account') }}</p>
                            <dl class="mt-2 grid gap-2 text-theme-sm sm:grid-cols-2">
                                @foreach(['bank_name' => 'Bank', 'account_name' => 'Account name', 'account_number' => 'Account number', 'routing_number' => 'Routing', 'branch_name' => 'Branch', 'swift_code' => 'SWIFT'] as $field => $label)
                                    @if(!empty($acct[$field]))
                                        <div>
                                            <dt class="text-theme-xs text-emerald-600/80 dark:text-emerald-500/70">{{ __($label) }}</dt>
                                            <dd class="font-semibold text-emerald-900 dark:text-emerald-300">{{ $acct[$field] }}</dd>
                                        </div>
                                    @endif
                                @endforeach
                            </dl>
                        </div>
                    @endif

                    <div class="mt-6 flex flex-col-reverse gap-3 rounded-xl bg-gray-50 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between dark:bg-white/[0.02]">
                        <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                            @if($chosen && ($chosen['manual'] ?? false))
                                {{ __('After paying, the BeeCore Account team verifies your transfer and activates the add-on.') }}
                            @else
                                {{ __('You will be redirected to bKash to pay securely. :name activates as soon as bKash confirms the payment.', ['name' => $checkoutAddon->name]) }}
                            @endif
                        </p>
                        <button type="button" wire:click="confirmBuy" wire:loading.attr="disabled" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                            <span wire:loading.remove wire:target="confirmBuy">{{ $chosen && ($chosen['manual'] ?? false) ? __('Submit order — pay :amount', ['amount' => '৳'.number_format($checkoutPrice, 0)]) : __('Pay :amount now', ['amount' => '৳'.number_format($checkoutPrice, 0)]) }}</span>
                            <span wire:loading wire:target="confirmBuy">{{ __('Processing...') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    @else
        <!-- Clean sub-navigation: browse & buy in Store, review what you run in My add-ons -->
        <div class="inline-flex flex-wrap items-center gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]" role="tablist" aria-label="{{ __('Add-on views') }}">
            <button type="button" role="tab" wire:click="setTab('store')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'store' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                {{ __('Store') }}
            </button>
            <button type="button" role="tab" wire:click="setTab('my')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'my' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                {{ __('My add-ons') }}
                @if($summary['active'] + $summary['pending'] > 0)
                    <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'my' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $summary['active'] + $summary['pending'] }}</span>
                @endif
            </button>
        </div>

        @if($tab === 'my')
        <!-- My add-ons summary -->
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Active add-ons') }}</p>
                <p class="mt-1 text-2xl font-bold text-success-600 dark:text-success-400">{{ $summary['active'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Pending requests') }}</p>
                <p class="mt-1 text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $summary['pending'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Recurring spend / month') }}</p>
                <p class="mt-1 truncate text-2xl font-bold text-gray-800 dark:text-white/90">৳{{ number_format($summary['monthlySpend'], 2) }}</p>
            </div>
        </section>

        @if(($smsSummary['count'] ?? 0) > 0)
            <!-- SMS wallet -->
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="grid grid-cols-1 gap-5 p-5 sm:p-6 lg:grid-cols-3 lg:items-center">
                    <div class="flex items-center gap-4 lg:col-span-1">
                        <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-sky-500 to-cyan-500 text-white shadow-theme-xs">
                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('SMS wallet') }}</p>
                            <p class="mt-0.5 truncate text-lg font-bold text-gray-900 dark:text-white">{{ number_format($smsSummary['remaining']) }} {{ __('credits left') }}</p>
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $smsSummary['count'] }} {{ trans_choice('active package|active packages', $smsSummary['count']) }} · {{ __('each SMS you send spends 1 credit') }}</p>
                        </div>
                    </div>
                    <div class="lg:col-span-1">
                        <div class="flex items-center justify-between gap-3 text-theme-sm">
                            <span class="font-medium text-gray-800 dark:text-white/90">{{ number_format($smsSummary['remaining']) }} / {{ number_format($smsSummary['included']) }} {{ $smsSummary['remaining'] === 1 ? 'credit' : 'credits' }}</span>
                            <span class="font-semibold {{ $smsSummary['pctLeft'] < 10 ? 'text-error-600 dark:text-error-400' : ($smsSummary['pctLeft'] < 40 ? 'text-warning-600 dark:text-warning-400' : 'text-success-600 dark:text-success-400') }}">{{ $smsSummary['pctLeft'] }}% {{ __('left') }}</span>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/[0.06]">
                            <div class="h-full rounded-full transition-all {{ $smsSummary['pctLeft'] < 10 ? 'bg-error-500' : ($smsSummary['pctLeft'] < 40 ? 'bg-warning-500' : 'bg-success-500') }}" style="width: {{ $smsSummary['pctLeft'] }}%"></div>
                        </div>
                        <p class="mt-1.5 text-theme-xs text-gray-400 dark:text-gray-500">{{ $smsSummary['included'] }} {{ __('credits per period across your active SMS packages') }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 lg:col-span-1">
                        <div class="rounded-xl border border-gray-100 bg-gray-50/70 px-4 py-3 dark:border-gray-800 dark:bg-white/[0.02]">
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Sent so far') }}</p>
                            <p class="mt-1 text-xl font-bold text-gray-800 dark:text-white/90">{{ number_format($smsSummary['sent']) }}</p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50/70 px-4 py-3 dark:border-gray-800 dark:bg-white/[0.02]">
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Used from this pool') }}</p>
                            <p class="mt-1 text-xl font-bold text-gray-800 dark:text-white/90">{{ number_format($smsSummary['used']) }}</p>
                        </div>
                    </div>
                </div>
                @if($smsSummary['remaining'] < 1)
                    <div class="flex items-start gap-3 border-t border-error-100 bg-error-50/60 px-5 py-3.5 dark:border-error-500/20 dark:bg-error-500/10 sm:px-6">
                        <svg class="mt-0.5 size-4.5 shrink-0 stroke-error-600 dark:stroke-error-400" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <p class="text-theme-sm text-error-700 dark:text-error-400">{{ __('Your SMS wallet is empty — SMS sends are paused. Buy another SMS package above to keep sending notifications.') }}</p>
                    </div>
                @endif
            </section>
        @endif

        <!-- My add-ons (what you currently run or are waiting on) -->
        <section class="space-y-3">
            @forelse($mine as $item)
                @php
                    $itemStateTone = match ($item->status) {
                        'active' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400',
                        'requested', 'pending_approval' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400',
                        default => 'bg-gray-100 text-gray-500 dark:bg-white/[0.05] dark:text-gray-400',
                    };
                    $itemStateLabel = match ($item->status) {
                        'active' => __('Active'),
                        'pending_approval' => __('Awaiting payment'),
                        'requested' => __('Waiting for approval'),
                        default => __('Ended'),
                    };
                    $itemCycle = ['one_time' => __('One-time'), 'monthly' => __('Monthly'), 'yearly' => __('Yearly')][$item->billing_cycle] ?? $item->billing_cycle;
                    $itemInvoice = $openInvoiceByAddon[$item->id] ?? null;
                @endphp
                <article class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs transition sm:flex-row sm:items-center dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
                    <div class="flex min-w-0 flex-1 items-center gap-3">
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand-500 to-indigo-500 text-base font-bold text-white">{{ strtoupper(substr($item->addon?->name ?? __('Add-on'), 0, 1)) }}</span>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="truncate text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $item->addon?->name ?? __('Add-on') }}</p>
                                <span class="inline-flex rounded-full px-2 py-0.5 text-theme-xs font-medium {{ $itemStateTone }}">{{ $itemStateLabel }}</span>
                            </div>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">
                                {{ $categories[$item->addon?->category ?? ''] ?? $item->addon?->category ?? '' }}
                                @if($item->addon?->category) · {{ $itemCycle }} @endif
                                @if($item->starts_at) · {{ __('since :date', ['date' => $item->starts_at->format('d M Y')]) }} @endif
                            </p>
                        </div>
                    </div>

                    <dl class="grid shrink-0 grid-cols-2 gap-x-8 gap-y-1 text-theme-sm sm:flex sm:items-center sm:gap-8">
                        <div>
                            <dt class="text-theme-xs text-gray-400 dark:text-gray-500">{{ __('Price') }}</dt>
                            <dd class="font-semibold text-gray-800 dark:text-white/90">৳{{ number_format((float) $item->price, 2) }}<span class="text-theme-xs font-normal text-gray-400">/{{ $itemCycle }}</span></dd>
                        </div>
                        @if(in_array($item->billing_cycle, ['monthly', 'yearly'], true))
                            <div>
                                <dt class="text-theme-xs text-gray-400 dark:text-gray-500">{{ __('Renews') }}</dt>
                                <dd class="font-medium text-gray-800 dark:text-white/90">{{ $item->period_end?->format('d M Y') ?? '—' }}</dd>
                            </div>
                        @endif
                    </dl>

                    <div class="shrink-0 sm:min-w-44 sm:text-right">
                        @if($item->status === 'pending_approval' && $itemInvoice)
                            <a href="{{ route('bee-pay.saas-invoice', ['saasInvoice' => $itemInvoice]) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-warning-500 px-4 py-2.5 text-theme-xs font-semibold text-white shadow-theme-xs transition hover:bg-warning-600">
                                <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                {{ __('Pay ৳:amount', ['amount' => number_format((float) $itemInvoice->amount, 0)]) }}
                            </a>
                        @elseif($item->status === 'requested')
                            <span class="text-theme-xs font-medium text-warning-600 dark:text-warning-400">{{ __('BeeCore will confirm your request soon.') }}</span>
                        @elseif($item->status === 'pending_approval')
                            <span class="text-theme-xs font-medium text-warning-600 dark:text-warning-400">{{ __('Awaiting BeeCore approval') }}</span>
                        @elseif($item->status === 'active')
                            <span class="inline-flex items-center gap-1.5 text-theme-xs font-medium text-success-600 dark:text-success-400">
                                <span class="size-1.5 rounded-full bg-success-500"></span>
                                {{ __('Running on your workspace') }}
                            </span>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center dark:border-gray-700 dark:bg-white/[0.02]">
                    <p class="text-theme-sm font-medium text-gray-600 dark:text-gray-300">{{ __('You do not have any add-ons yet.') }}</p>
                    <button type="button" wire:click="setTab('store')" class="mt-3 inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-theme-xs font-semibold text-white shadow-theme-xs transition hover:bg-brand-600">{{ __('Browse the store') }}</button>
                </div>
            @endforelse
        </section>
        @endif

        @if($tab === 'store')
        <!-- Catalog -->
        <section class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Available add-ons') }}</h2>
                <select wire:model.live="categoryFilter" class="h-10 w-52 appearance-none rounded-lg border border-gray-300 bg-transparent px-3.5 py-2 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">{{ __('All categories') }}</option>
                    @foreach($categories as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            @if($catalog->isEmpty())
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white py-16 text-center dark:border-gray-700 dark:bg-white/[0.02]">
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">{{ __('No add-ons are available in this category yet.') }}</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($catalog as $addon)
                        @php
                            $state = $stateByAddon[$addon->id] ?? null;
                            $cycleLabel = ['one_time' => __('One-time'), 'monthly' => __('Monthly'), 'yearly' => __('Yearly')][$addon->billing_cycle] ?? $addon->billing_cycle;
                        @endphp
                        <div class="flex flex-col rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand-500 to-indigo-500 text-base font-bold text-white">{{ strtoupper(substr($addon->name, 0, 1)) }}</span>
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $addon->name }}</h3>
                                        <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $categories[$addon->category] ?? $addon->category }}</p>
                                    </div>
                                </div>
                                @if($state)
                                    @if($state->status === 'active')
                                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-400">
                                            <span class="size-1.5 rounded-full bg-success-500"></span>{{ __('Active') }}
                                        </span>
                                    @elseif(in_array($state->status, ['requested', 'pending_approval'], true))
                                        <span class="inline-flex shrink-0 items-center rounded-full bg-warning-50 px-2.5 py-1 text-theme-xs font-semibold text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">{{ __('Pending') }}</span>
                                    @else
                                        <span class="inline-flex shrink-0 items-center rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs font-semibold text-gray-500 dark:bg-white/[0.05] dark:text-gray-400">{{ __('Ended') }}</span>
                                    @endif
                                @endif
                            </div>

                            @if($addon->description)
                                <p class="mt-3 line-clamp-2 text-theme-sm leading-5 text-gray-500 dark:text-gray-400">{{ $addon->description }}</p>
                            @endif

                            <div class="mt-3 space-y-1 text-theme-xs text-gray-500 dark:text-gray-400">
                                <p>{{ __('Price') }}: <span class="font-semibold text-gray-800 dark:text-white/90">৳{{ number_format((float) $addon->price, 2) }}</span> / {{ $cycleLabel }}</p>
                                <p>{{ $addon->billing_cycle === 'one_time' ? __('One-time charge') : __('Recurring — billed with your BeeCore subscription') }}</p>
                                @if($addon->usage_limit)
                                    <p>{{ __('Usage') }}: {{ number_format($addon->usage_limit) }} {{ $addon->usage_unit }}</p>
                                @endif
                            </div>

                            <div class="mt-4 flex-1"></div>
                            <div class="border-t border-gray-100 pt-4 dark:border-gray-800">
                                @if($state && $state->status === 'active')
                                    <p class="text-center text-theme-sm font-medium text-success-600 dark:text-success-400">{{ __('Running on your workspace') }}</p>
                                @elseif($state && $state->status === 'pending_approval')
                                    @php $continueInvoice = $openInvoiceByAddon[$state->id] ?? null; @endphp
                                    @if($continueInvoice)
                                        <a href="{{ route('bee-pay.saas-invoice', ['saasInvoice' => $continueInvoice]) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-warning-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-warning-600">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                            {{ __('Continue payment — ৳:amount', ['amount' => number_format((float) $continueInvoice->amount, 0)]) }}
                                        </a>
                                    @else
                                        <p class="text-center text-theme-sm font-medium text-warning-600 dark:text-warning-400">{{ __('Awaiting BeeCore approval') }}</p>
                                    @endif
                                @elseif($state && $state->status === 'requested')
                                    <p class="text-center text-theme-sm font-medium text-warning-600 dark:text-warning-400">{{ __('Waiting for BeeCore approval') }}</p>
                                @else
                                    <button type="button" wire:click="buy({{ $addon->id }})" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                                        <span wire:loading.remove wire:target="buy">{{ __('Buy now') }}</span>
                                        <span wire:loading wire:target="buy">{{ __('Continue...') }}</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
        @endif
    @endif
</div>
