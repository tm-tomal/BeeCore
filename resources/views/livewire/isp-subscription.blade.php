<div class="space-y-6">
    @php
        $sub = $subscription;
        $subStatus = $sub?->status ?? null;
        $currentPlan = $sub?->plan;
        $openInvoice = $openInvoice ?? null;
        $payable = $openInvoice && in_array($openInvoice->status, ['pending', 'overdue'], true) ? $openInvoice : null;
        $canPickPlans = ! $sub || in_array($subStatus, ['active', 'trialing', 'cancelled'], true);
        $showHistory = $sub && $subStatus !== 'cancelled';
        $daysLeft = $sub?->current_period_ends_at ? (int) today()->diffInDays($sub->current_period_ends_at, false) : null;
        $statusChip = match ($subStatus) {
            'active' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
            'trialing' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
            'pending_approval' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
            'past_due' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
            'suspended' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
            default => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
        };
    @endphp

    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">{{ __('Workspace') }}</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ __('My BeeCore subscription') }}</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ __('One plan, every BeeCore feature — pick the size that fits your ISP today and grow later.') }}</p>
        </div>
        @if($sub && ! in_array($subStatus, ['cancelled'], true) && ! $checkout)
            <span class="inline-flex w-fit items-center gap-1.5 rounded-full px-3 py-1.5 text-theme-xs font-medium capitalize {{ $statusChip }}">
                <span class="size-1.5 rounded-full bg-current"></span>
                {{ $subStatus === 'active' ? __('Active') : __(ucwords(str_replace('_', ' ', $subStatus))) }}
            </span>
        @endif
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    @if($checkout && $selectedPlan)
        @php
            $checkPlan = $selectedPlan;
            $checkCycle = $billingCycle;
            $cMonthly = (float) $checkPlan->monthly_price;
            $cYearly = (float) $checkPlan->yearly_price;
            $cRegular = round($cMonthly * 12, 2);
            $cSave = round($cRegular - $cYearly, 2);
            $cDiscount = $checkPlan->yearlyDiscountPercent();
            $cPrice = $checkCycle === 'yearly' ? $cYearly : $cMonthly;
            $isSwitchOrder = $sub && $subStatus !== 'cancelled' && $currentPlanId !== $checkPlan->id;
            $onlineMethods = collect($paymentMethods)->where('manual', false)->values();
            $manualMethods = collect($paymentMethods)->where('manual', true)->values();
            $chosen = collect($paymentMethods)->firstWhere('key', $checkoutGateway);
        @endphp

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">{{ __('Secure checkout') }}</p>
                    <h2 class="mt-1 text-title-sm font-bold text-gray-900 dark:text-white">{{ $isSwitchOrder ? __('Change to :name', ['name' => $checkPlan->name]) : __('Activate :name', ['name' => $checkPlan->name]) }}</h2>
                    <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                        {{ $isSwitchOrder
                            ? __('You are moving from :from to :to — the change applies as soon as payment is confirmed.', ['from' => $currentPlan?->name ?? __('your current plan'), 'to' => $checkPlan->name])
                            : __('Complete the payment below and your plan turns on right away.') }}
                    </p>
                </div>
                <button wire:click="cancelCheckout" class="inline-flex w-fit items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    {{ __('Back to plans') }}
                </button>
            </div>

            <!-- Simple 3-step walkthrough -->
            <ol class="mt-5 grid gap-2 text-theme-xs sm:grid-cols-3">
                <li class="flex items-center gap-2 rounded-lg bg-success-50 px-3 py-2.5 text-success-700 dark:bg-success-500/10 dark:text-success-400">
                    <span class="grid size-5 shrink-0 place-items-center rounded-full bg-success-500 text-white"><svg class="size-3 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
                    <span class="font-medium">{{ $checkPlan->name }}</span>
                </li>
                <li class="flex items-center gap-2 rounded-lg bg-brand-50 px-3 py-2.5 font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                    <span class="grid size-5 shrink-0 place-items-center rounded-full bg-brand-500 text-white">2</span>
                    {{ __('Pay') }}
                </li>
                <li class="flex items-center gap-2 rounded-lg bg-gray-100 px-3 py-2.5 text-gray-500 dark:bg-white/[0.04] dark:text-gray-400">
                    <span class="grid size-5 shrink-0 place-items-center rounded-full bg-gray-400 text-white dark:bg-gray-600">3</span>
                    {{ __('Plan active') }}
                </li>
            </ol>

            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-5">
                <!-- Order summary -->
                <div class="lg:col-span-2">
                    <div class="flex h-full flex-col rounded-xl border border-gray-200 bg-gray-50/60 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-theme-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Order summary') }}</p>
                                <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ $checkPlan->name }}</p>
                                <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">
                                    {{ $checkCycle === 'yearly' ? __('Yearly billing · billed once') : __('Monthly billing · renews every month') }}
                                    @if($checkPlan->customer_limit !== null)
                                        · {{ __(':count customers included', ['count' => number_format($checkPlan->customer_limit)]) }}
                                    @else
                                        · {{ __('Unlimited customers') }}
                                    @endif
                                </p>
                            </div>
                            <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">{{ $checkPlan->operationModeLabel() }}</span>
                        </div>

                        <dl class="mt-5 space-y-3 border-t border-gray-200 pt-4 text-theme-sm dark:border-gray-800">
                            @if($checkCycle === 'yearly')
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-gray-500 dark:text-gray-400">{{ __('Regular (12 × :amount)', ['amount' => '৳'.number_format($cMonthly, 0)]) }}</dt>
                                    <dd class="text-gray-500 dark:text-gray-400 line-through">৳{{ number_format($cRegular, 0) }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-3 text-success-600 dark:text-success-400">
                                    <dt>{{ __('Yearly discount (:pct%)', ['pct' => $cDiscount]) }}</dt>
                                    <dd>− ৳{{ number_format($cSave, 0) }}</dd>
                                </div>
                            @endif
                            <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-3 dark:border-gray-800">
                                <dt class="font-medium text-gray-800 dark:text-white/90">{{ $checkCycle === 'yearly' ? __('Total today (1 year)') : __('Total today (1 month)') }}</dt>
                                <dd class="text-title-sm font-bold text-gray-900 dark:text-white">৳{{ number_format($cPrice, 2) }}</dd>
                            </div>
                        </dl>

                        @if($isSwitchOrder)
                            <div class="mt-4 flex items-start gap-2.5 rounded-lg border border-warning-200 bg-warning-50 px-3.5 py-2.5 text-theme-xs text-warning-700 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-300">
                                <svg class="mt-0.5 size-4 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a5 5 0 0 1 4.9 6.2A6 6 0 0 1 18 14a6 6 0 0 1-12 0 6 6 0 0 1 1.1-7.8A5 5 0 0 1 12 2z"/><path d="M12 9l-2 4 2 1.5L14 13l-2-4z"/></svg>
                                <p>{{ __('A BeeCore invoice for the new amount is created when you switch. Your remaining days on the old plan are not wasted — the new cycle starts after your current period.') }}</p>
                            </div>
                        @else
                            <div class="mt-4 flex items-start gap-2.5 rounded-lg border border-brand-100 bg-brand-50/60 px-3.5 py-2.5 text-theme-xs text-gray-600 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-gray-300">
                                <svg class="mt-0.5 size-4 shrink-0 stroke-current text-brand-500 dark:text-brand-400" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <p>{{ __('Your first :cycle invoice is raised now and is covered by this payment.', ['cycle' => $checkCycle === 'yearly' ? __('yearly') : __('monthly')]) }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Payment step -->
                <div class="lg:col-span-3">
                    @if($onlineMethods->isNotEmpty())
                        <p class="mb-2 flex flex-wrap items-center gap-x-2 text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                            {{ __('1. Online payment') }}
                            <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-[10px] font-bold text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                <svg class="size-3 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                {{ __('instant activation') }}
                            </span>
                        </p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach($onlineMethods as $method)
                                @php $style = $this->paymentMethodStyle($method['provider']); @endphp
                                <button type="button" wire:click="selectGateway('{{ $method['key'] }}')"
                                    class="flex items-center gap-3 rounded-xl border p-4 text-left transition"
                                    @class([
                                        'border-brand-500 bg-brand-50/60 ring-2 ring-brand-500/20 dark:border-brand-500 dark:bg-brand-500/10' => $checkoutGateway === $method['key'],
                                        'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700' => $checkoutGateway !== $method['key'],
                                    ])>
                                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-gradient-to-br text-theme-sm font-bold text-white {{ $style['avatar'] }}">{{ $style['letter'] }}</span>
                                    <span class="min-w-0">
                                        <span class="block text-theme-sm font-semibold text-gray-900 dark:text-white">{{ $method['name'] }}</span>
                                        <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Pay now · plan activates automatically') }}</span>
                                    </span>
                                    <span aria-hidden="true" class="ml-auto grid size-5 shrink-0 place-items-center rounded-full border-2 transition {{ $checkoutGateway === $method['key'] ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10' : 'border-gray-300 dark:border-gray-700' }}">
                                        <span class="size-2.5 rounded-full transition {{ $checkoutGateway === $method['key'] ? 'bg-brand-500' : '' }}"></span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    @if($manualMethods->isNotEmpty())
                        <p class="mt-5 mb-2 flex flex-wrap items-center gap-x-2 text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                            {{ __('2. Bank transfer') }}
                            <span class="inline-flex items-center gap-1 rounded-full bg-warning-50 px-2 py-0.5 text-[10px] font-bold text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                                <svg class="size-3 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ __('verified by the BeeCore team') }}
                            </span>
                        </p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach($manualMethods as $method)
                                @php $style = $this->paymentMethodStyle($method['provider']); @endphp
                                <button type="button" wire:click="selectGateway('{{ $method['key'] }}')"
                                    class="flex items-center gap-3 rounded-xl border p-4 text-left transition"
                                    @class([
                                        'border-warning-500 bg-warning-50/60 ring-2 ring-warning-500/20 dark:border-warning-500 dark:bg-warning-500/10' => $checkoutGateway === $method['key'],
                                        'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700' => $checkoutGateway !== $method['key'],
                                    ])>
                                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-gradient-to-br text-theme-sm font-bold text-white {{ $style['avatar'] }}">{{ $style['letter'] }}</span>
                                    <span class="min-w-0">
                                        <span class="block text-theme-sm font-semibold text-gray-900 dark:text-white">{{ $method['name'] }}</span>
                                        <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Send the transfer · we activate it for you') }}</span>
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

                    @error('selectedPlanId')
                        <div class="mt-4 flex items-start gap-3 rounded-xl border border-error-200 bg-error-50 px-4 py-3 dark:border-error-500/20 dark:bg-error-500/10">
                            <svg class="mt-0.5 size-5 shrink-0 stroke-error-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <p class="text-theme-sm text-error-700 dark:text-error-300">{{ $message }}</p>
                        </div>
                    @enderror

                    @if($chosen && ($chosen['manual'] ?? false) && ! empty($chosen['account']))
                        @php $acct = $chosen['account']; @endphp
                        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 dark:border-emerald-500/25 dark:bg-emerald-500/10">
                            <p class="text-theme-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-400">{{ __('Transfer to this account') }}</p>
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
                            <p class="mt-3 flex items-start gap-1.5 text-theme-xs leading-5 text-emerald-700/90 dark:text-emerald-300/80">
                                <svg class="mt-0.5 size-3.5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                {{ __('Keep the transfer reference handy — after we see the money, your plan is switched on.') }}
                            </p>
                        </div>
                    @endif

                    <div class="mt-6 rounded-xl bg-gray-50 px-4 py-3.5 dark:bg-white/[0.02]">
                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-theme-xs leading-5 text-gray-500 dark:text-gray-400">
                                @if($chosen && ($chosen['manual'] ?? false))
                                    {{ __('No account chosen yet — pick a method above. After the BeeCore team verifies your transfer, the plan activates.') }}
                                @else
                                    {{ __('You will go to bKash to approve the payment. As soon as bKash confirms it, your plan is active.') }}
                                @endif
                            </p>
                            <button type="button" wire:click="confirmCheckout" wire:loading.attr="disabled" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                                @if($chosen && ($chosen['manual'] ?? false))
                                    <span wire:loading.remove wire:target="confirmCheckout">{{ __('Submit order — ৳:amount', ['amount' => number_format($cPrice, 0)]) }}</span>
                                @else
                                    <span wire:loading.remove wire:target="confirmCheckout">
                                        <svg class="mr-1.5 inline size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                                        {{ __('Pay ৳:amount with bKash', ['amount' => number_format($cPrice, 0)]) }}
                                    </span>
                                @endif
                                <span wire:loading wire:target="confirmCheckout">{{ __('Processing...') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @else
    <!-- Plain-language status strip: tells the user exactly what happens next -->
    @if(! $sub)
        <section class="overflow-hidden rounded-2xl border border-brand-100 bg-gradient-to-br from-brand-50 via-white to-white p-5 sm:p-6 dark:border-brand-500/20 dark:from-brand-500/[0.07] dark:via-white/[0.02] dark:to-white/[0.02]">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-4">
                    <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-brand-500 text-white shadow-theme-xs">
                        <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Your workspace is ready — it needs a plan to go live') }}</h2>
                        <p class="mt-1 max-w-2xl text-theme-sm leading-5 text-gray-500 dark:text-gray-400">{{ __('Pick one of the plans below. Every BeeCore feature is included on every plan — plans only change how many customers, staff and resellers you can manage.') }}</p>
                    </div>
                </div>
                <a href="#plan-catalogue" class="inline-flex shrink-0 items-center justify-center gap-2 self-start rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 lg:self-center">
                    {{ __('Choose your plan') }}
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
        </section>
    @elseif($subStatus === 'pending_approval')
        <section class="rounded-2xl border border-warning-200 bg-warning-50/70 p-5 dark:border-warning-500/20 dark:bg-warning-500/10 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-warning-500/15 text-warning-600 dark:bg-warning-500/20 dark:text-warning-400">
                        <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ __('Waiting for your payment to be confirmed') }}</h2>
                        <p class="mt-1 max-w-2xl text-theme-sm leading-5 text-gray-600 dark:text-gray-300">
                            @if($payable)
                                {{ __('You have an unpaid BeeCore invoice of ৳:amount (:invoice). Pay it online now for instant activation, or if you already sent a bank transfer, the BeeCore Account team will verify it shortly.', ['amount' => number_format($payable->amount, 2), 'invoice' => $payable->invoice_number]) }}
                            @else
                                {{ __('The BeeCore Account team is verifying your payment. Once confirmed, your plan turns on automatically — you do not need to do anything.') }}
                            @endif
                        </p>
                    </div>
                </div>
                @if($payable)
                    <a href="{{ route('bee-pay.saas-invoice', ['saasInvoice' => $payable]) }}" class="inline-flex shrink-0 items-center justify-center gap-2 self-start rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 sm:self-center">
                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        {{ __('Pay ৳:amount now', ['amount' => number_format($payable->amount, 0)]) }}
                    </a>
                @endif
            </div>
        </section>
    @elseif($subStatus === 'past_due')
        <section class="rounded-2xl border border-warning-200 bg-warning-50/70 p-5 dark:border-warning-500/20 dark:bg-warning-500/10 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-warning-500/15 text-warning-600 dark:bg-warning-500/20 dark:text-warning-400">
                        <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ __('One of your BeeCore invoices is overdue') }}</h2>
                        <p class="mt-1 max-w-2xl text-theme-sm leading-5 text-gray-600 dark:text-gray-300">
                            @if($payable)
                                {{ __('Invoice :invoice (৳:amount) is past its due date. Pay it now to keep :plan running without interruption.', ['invoice' => $payable->invoice_number, 'amount' => number_format($payable->amount, 2), 'plan' => $currentPlan?->name ?? __('your plan')]) }}
                            @else
                                {{ __('Your subscription is past due. Pay any open invoice below to keep your plan running.') }}
                            @endif
                        </p>
                    </div>
                </div>
                @if($payable)
                    <a href="{{ route('bee-pay.saas-invoice', ['saasInvoice' => $payable]) }}" class="inline-flex shrink-0 items-center justify-center gap-2 self-start rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 sm:self-center">
                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        {{ __('Pay ৳:amount now', ['amount' => number_format($payable->amount, 0)]) }}
                    </a>
                @endif
            </div>
        </section>
    @elseif($subStatus === 'suspended')
        <section class="rounded-2xl border border-error-200 bg-error-50/70 p-5 dark:border-error-500/20 dark:bg-error-500/10 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-error-500/15 text-error-600 dark:bg-error-500/20 dark:text-error-400">
                        <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ __('Your workspace is currently suspended') }}</h2>
                        <p class="mt-1 max-w-2xl text-theme-sm leading-5 text-gray-600 dark:text-gray-300">
                            @if($payable)
                                {{ __('Your :plan plan was paused because invoice :invoice (৳:amount) was not paid. Settle it and your workspace resumes instantly.', ['plan' => $currentPlan?->name ?? __('BeeCore'), 'invoice' => $payable->invoice_number, 'amount' => number_format($payable->amount, 2)]) }}
                            @else
                                {{ __('Your workspace was suspended for an unpaid invoice. Pay any open invoice below to reactivate it.') }}
                            @endif
                        </p>
                    </div>
                </div>
                @if($payable)
                    <a href="{{ route('bee-pay.saas-invoice', ['saasInvoice' => $payable]) }}" class="inline-flex shrink-0 items-center justify-center gap-2 self-start rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 sm:self-center">
                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        {{ __('Reactivate — pay ৳:amount', ['amount' => number_format($payable->amount, 0)]) }}
                    </a>
                @endif
            </div>
        </section>
    @elseif($subStatus === 'cancelled')
        <section class="rounded-2xl border border-gray-200 bg-gray-50/70 p-5 dark:border-gray-800 dark:bg-white/[0.02] sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-gray-200 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400">
                        <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v2m0 4h.01M5 19h14a2 2 0 0 0 1.84-2.83L13.84 4.4a2 2 0 0 0-3.68 0L3.16 16.17A2 2 0 0 0 5 19z"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ __('Your previous subscription has ended') }}</h2>
                        <p class="mt-1 max-w-2xl text-theme-sm leading-5 text-gray-600 dark:text-gray-300">{{ __('Pick a plan below to start again. Your workspace data is safe — a new subscription simply switches the lights back on.') }}</p>
                    </div>
                </div>
                <a href="#plan-catalogue" class="inline-flex shrink-0 items-center justify-center gap-2 self-start rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 sm:self-center">
                    {{ __('Choose a plan') }}
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
        </section>
    @elseif($subStatus === 'active' && $payable)
        <section class="rounded-2xl border border-warning-200 bg-warning-50/60 p-5 dark:border-warning-500/15 dark:bg-warning-500/[0.07] sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-warning-500/15 text-warning-600 dark:bg-warning-500/20 dark:text-warning-400">
                        <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ __('Your :plan plan is active — one invoice needs your attention', ['plan' => $currentPlan?->name ?? __('BeeCore')]) }}</h2>
                        <p class="mt-1 max-w-2xl text-theme-sm leading-5 text-gray-600 dark:text-gray-300">{{ __('Invoice :invoice (৳:amount, due :date) is still unpaid. You can keep working — paying just makes sure nothing interrupts.', ['invoice' => $payable->invoice_number, 'amount' => number_format($payable->amount, 2), 'date' => $payable->due_date?->format('d M Y') ?? '—']) }}</p>
                    </div>
                </div>
                <a href="{{ route('bee-pay.saas-invoice', ['saasInvoice' => $payable]) }}" class="inline-flex shrink-0 items-center justify-center gap-2 self-start rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 sm:self-center">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    {{ __('Pay ৳:amount now', ['amount' => number_format($payable->amount, 0)]) }}
                </a>
            </div>
        </section>
    @endif

    <!-- Current plan overview -->
    @if($sub && $subStatus !== 'cancelled')
        <section class="grid grid-cols-12 gap-4 md:gap-6" id="current-plan">
            <div class="col-span-12 xl:col-span-7">
                <div class="h-full rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex items-center gap-4">
                            <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-brand-500/10 text-2xl font-extrabold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                {{ strtoupper(substr($currentPlan?->name ?? 'Bee', 0, 1)) }}
                            </span>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $currentPlan?->name ?? __('Custom plan') }}</h2>
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $statusChip }}">{{ $subStatus === 'active' ? __('Active') : __(ucwords(str_replace('_', ' ', $subStatus))) }}</span>
                                </div>
                                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $currentPlan?->description }}</p>
                            </div>
                        </div>
                        <div class="shrink-0 text-left sm:text-right">
                            <p class="text-title-md font-bold text-gray-900 dark:text-white">৳{{ number_format((float) $sub->price, 2) }}</p>
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $sub->billing_cycle === 'yearly' ? __('billed once a year') : __('billed monthly') }}</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 rounded-xl border border-gray-200 bg-gray-50/60 p-4 sm:grid-cols-3 dark:border-gray-800 dark:bg-white/[0.02]">
                        <div>
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Current period') }}</p>
                            <p class="mt-1 text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                @if($sub->starts_at && $sub->current_period_ends_at)
                                    {{ $sub->starts_at->format('d M Y') }} → {{ $sub->current_period_ends_at->format('d M Y') }}
                                @else
                                    —
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Next renewal') }}</p>
                            <p class="mt-1 text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                @if($daysLeft !== null && $daysLeft >= 0 && $sub->current_period_ends_at)
                                    {{ $sub->current_period_ends_at->format('d M Y') }}
                                    <span class="ml-1 inline-flex rounded-full bg-success-50 px-2 py-0.5 text-[10px] font-bold text-success-700 dark:bg-success-500/15 dark:text-success-400">{{ $daysLeft === 1 ? __('tomorrow') : __('in :count days', ['count' => $daysLeft]) }}</span>
                                @else
                                    <span class="font-normal text-gray-400 dark:text-gray-500">{{ __('not set') }}</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Auto renew') }}</p>
                            <p class="mt-1 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $sub->auto_renew ? __('On — a new invoice is created automatically') : __('Off') }}</p>
                        </div>
                    </div>

                    @if($daysLeft !== null && $daysLeft >= 0 && $sub->auto_renew)
                        <p class="mt-3 flex items-center gap-1.5 text-theme-xs text-gray-500 dark:text-gray-400">
                            <svg class="size-3.5 stroke-current text-brand-500 dark:text-brand-400" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            {{ __('No action needed — your :plan plan renews automatically.', ['plan' => $currentPlan?->name ?? __('BeeCore')]) }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="col-span-12 xl:col-span-5">
                <div class="h-full rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('What this plan includes') }}</h2>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Your included limits — going over is still possible via overflow billing.') }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-1 text-theme-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            {{ __('Included') }}
                        </span>
                    </div>
                    <dl class="mt-4 space-y-3">
                        <div class="flex items-center justify-between gap-3 rounded-lg bg-gray-50/70 px-3 py-2.5 text-theme-sm dark:bg-white/[0.02]">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Customers') }}</dt>
                            <dd class="font-semibold text-gray-800 dark:text-white/90">{{ $currentPlan?->customer_limit !== null ? number_format($currentPlan->customer_limit) : __('Unlimited') }}</dd>
                        </div>
                        @if($currentPlan?->customer_limit !== null && (float) $currentPlan->overflow_rate > 0)
                            <p class="-mt-1 flex items-center gap-1.5 px-3 text-theme-xs text-gray-400 dark:text-gray-500">
                                <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                {{ __('Beyond this, ৳:rate per extra customer/month is billed on your invoice.', ['rate' => number_format($currentPlan->overflow_rate, 2)]) }}
                            </p>
                        @endif
                        <div class="flex items-center justify-between gap-3 rounded-lg bg-gray-50/70 px-3 py-2.5 text-theme-sm dark:bg-white/[0.02]">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Staff logins') }}</dt>
                            <dd class="font-semibold text-gray-800 dark:text-white/90">{{ $currentPlan?->staff_limit !== null ? number_format($currentPlan->staff_limit) : __('Unlimited') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-lg bg-gray-50/70 px-3 py-2.5 text-theme-sm dark:bg-white/[0.02]">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Resellers') }}</dt>
                            <dd class="font-semibold text-gray-800 dark:text-white/90">{{ $currentPlan?->reseller_limit !== null ? number_format($currentPlan->reseller_limit) : __('Unlimited') }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 flex items-start gap-2.5 rounded-xl border border-brand-100 bg-brand-50/60 px-4 py-3 dark:border-brand-500/20 dark:bg-brand-500/10">
                        <svg class="mt-0.5 size-4 shrink-0 stroke-current text-brand-500 dark:text-brand-400" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        <p class="text-theme-xs leading-5 text-gray-600 dark:text-gray-300">{{ __('Need more customers, staff or a custom setup? The BeeCore Account team can always adjust your plan — contact your Sales contact.') }}</p>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- Plan catalogue -->
    @if($canPickPlans)
        <section id="plan-catalogue" class="scroll-mt-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">
                        @if($sub)
                            {{ __('Ready to grow? Compare plans and switch') }}
                        @else
                            {{ __('Choose the plan that fits your ISP') }}
                        @endif
                    </h2>
                    <p class="mt-0.5 max-w-2xl text-theme-xs text-gray-500 dark:text-gray-400">
                        @if($sub)
                            {{ $billingCycle === 'yearly'
                                ? __('Yearly billing saves you :pct% — switch any time and the change applies from your next period.', ['pct' => $plans->first()?->yearlyDiscountPercent() ?? 20])
                                : __('Plans available for your operation type. Switching is instant once your payment is confirmed.') }}
                        @else
                            {{ $billingCycle === 'yearly'
                                ? __('Yearly billing — pay once, save :pct%, and relax for a full year.', ['pct' => $plans->first()?->yearlyDiscountPercent() ?? 20])
                                : __('All plans include every BeeCore feature. Start small and upgrade as you grow.') }}
                        @endif
                    </p>
                </div>
                <div class="flex shrink-0 flex-col items-start gap-2 sm:items-end">
                    <div class="inline-flex rounded-xl border border-gray-200 bg-gray-100 p-1 dark:border-gray-700 dark:bg-white/[0.04]" role="tablist" aria-label="{{ __('Billing cycle') }}">
                        <button
                            type="button"
                            role="tab"
                            wire:click="$set('billingCycle', 'monthly')"
                            aria-selected="{{ $billingCycle === 'monthly' ? 'true' : 'false' }}"
                            class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $billingCycle === 'monthly' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
                        >{{ __('Monthly') }}</button>
                        <button
                            type="button"
                            role="tab"
                            wire:click="$set('billingCycle', 'yearly')"
                            aria-selected="{{ $billingCycle === 'yearly' ? 'true' : 'false' }}"
                            class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $billingCycle === 'yearly' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
                        >
                            {{ __('Yearly') }}
                            <span class="inline-flex rounded-full bg-success-100 px-1.5 py-0.5 text-[10px] font-bold text-success-700 dark:bg-success-500/20 dark:text-success-400">{{ __('−20–30%') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
                @forelse($plans as $plan)
                    @php
                        $isCurrent = $currentPlanId === $plan->id;
                        $monthlyValue = (float) $plan->monthly_price;
                        $yearlyValue = (float) $plan->yearly_price;
                        $yearlyRegular = round($monthlyValue * 12, 2);
                        $yearlySavings = round($yearlyRegular - $yearlyValue, 2);
                        $yearlyMonthly = $yearlyValue > 0 ? round($yearlyValue / 12) : null;
                        $discountPct = $plan->yearlyDiscountPercent();
                    @endphp
                    <article class="relative flex flex-col rounded-xl border p-5 transition {{ $isCurrent ? 'border-brand-400 bg-brand-50/50 ring-2 ring-brand-500/15 dark:border-brand-500/60 dark:bg-brand-500/[0.06]' : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.02] dark:hover:border-gray-700' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                                    @if($isCurrent)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-brand-500 px-2.5 py-1 text-theme-xs font-semibold text-white">
                                            <svg class="size-3 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            {{ __('Your plan') }}
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-1 line-clamp-2 text-theme-xs text-gray-500 dark:text-gray-400">{{ $plan->description }}</p>
                            </div>
                            <div class="flex shrink-0 flex-col items-end gap-1.5">
                                @if($discountPct > 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-[10px] font-bold text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                        <svg class="size-3 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                                        {{ __('save :pct%/yr', ['pct' => $discountPct]) }}
                                    </span>
                                @endif
                                <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">{{ $plan->operationModeLabel() }}</span>
                            </div>
                        </div>

                        <div class="mt-4">
                            @if($billingCycle === 'yearly')
                                <div class="flex items-end gap-2">
                                    <span class="text-title-md font-bold text-gray-900 dark:text-white">৳{{ number_format($yearlyValue, 0) }}</span>
                                    <span class="pb-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('/year · billed once') }}</span>
                                </div>
                                <p class="mt-1 flex flex-wrap items-center gap-x-1.5 text-theme-xs text-gray-500 dark:text-gray-400">
                                    <span class="line-through text-gray-400 dark:text-gray-500">৳{{ number_format($yearlyRegular, 0) }}</span>
                                    {{ __('regular') }}
                                    <span class="font-semibold text-success-600 dark:text-success-400">{{ __('save ৳:amount', ['amount' => number_format($yearlySavings, 0)]) }}</span>
                                    @if($yearlyMonthly) · ≈ ৳{{ number_format($yearlyMonthly, 0) }}{{ __('/mo') }} @endif
                                </p>
                            @else
                                <div class="flex items-end gap-2">
                                    <span class="text-title-md font-bold text-gray-900 dark:text-white">৳{{ number_format($monthlyValue, 2) }}</span>
                                    <span class="pb-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('/month') }}</span>
                                </div>
                                @if($yearlyValue > 0)
                                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">
                                        {{ __('Yearly') }}: <span class="font-medium text-success-600 dark:text-success-400">৳{{ number_format($yearlyValue, 0) }}</span> — {{ __('save :pct%', ['pct' => $discountPct]) }}
                                    </p>
                                @endif
                            @endif
                        </div>

                        <dl class="mt-4 space-y-2 border-t border-gray-100 pt-4 text-theme-sm dark:border-gray-800">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('Included customers') }}</dt>
                                <dd class="font-semibold text-gray-800 dark:text-white/90">{{ $plan->customer_limit !== null ? number_format($plan->customer_limit) : __('Unlimited') }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('Overflow charge') }}</dt>
                                <dd class="font-semibold text-gray-800 dark:text-white/90">
                                    @if($plan->customer_limit !== null && (float) $plan->overflow_rate > 0)
                                        ৳{{ number_format($plan->overflow_rate, 2) }}{{ __('/customer') }}
                                    @else
                                        <span class="font-normal text-gray-400">—</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('Staff / resellers') }}</dt>
                                <dd class="font-semibold text-gray-800 dark:text-white/90">{{ $plan->staff_limit ?? __('Unlimited') }} / {{ $plan->reseller_limit ?? __('Unlimited') }}</dd>
                            </div>
                        </dl>

                        <p class="mt-3 flex items-center gap-1.5 text-theme-xs text-gray-500 dark:text-gray-400">
                            <svg class="size-3.5 stroke-current text-brand-500 dark:text-brand-400" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            {{ __('All BeeCore features unlocked') }}
                        </p>

                        <div class="mt-5 flex-1" aria-hidden="true"></div>
                        @if($isCurrent)
                            <button type="button" disabled class="inline-flex w-full cursor-default items-center justify-center gap-2 rounded-lg bg-gray-100 px-4 py-2.5 text-theme-sm font-medium text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">{{ __('Your current plan') }}</button>
                        @else
                            <button type="button" wire:click="openCheckout({{ $plan->id }})" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                {{ $sub ? __('Switch to :name', ['name' => $plan->name]) : __('Activate :name', ['name' => $plan->name]) }}
                            </button>
                        @endif
                    </article>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-300 px-6 py-12 text-center lg:col-span-2 xl:col-span-3 dark:border-gray-700">
                        <p class="text-theme-sm font-medium text-gray-600 dark:text-gray-300">{{ __('No plans are available for your workspace yet.') }}</p>
                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('The BeeCore Account team will publish plans for your operation type shortly.') }}</p>
                    </div>
                @endforelse
            </div>

            <p class="mt-5 flex items-center gap-2 rounded-xl bg-gray-50 px-4 py-3 text-theme-xs text-gray-500 dark:bg-white/[0.02] dark:text-gray-400">
                <svg class="size-4 shrink-0 stroke-current text-brand-500 dark:text-brand-400" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                {{ __('How it works: choose a plan → pay (bKash activates instantly, bank transfer is verified by our team) → your plan turns on. You can change plans any time.') }}
            </p>
        </section>
    @elseif($subStatus === 'pending_approval' || $subStatus === 'past_due' || $subStatus === 'suspended')
        <p class="text-theme-xs text-gray-400 dark:text-gray-500">{{ __('Plan changes become available again as soon as your account is active.') }}</p>
    @endif

    <!-- BeeCore invoices -->
    @if($showHistory)
        <x-table heading="{{ __('BeeCore invoices') }}" :description="$invoices->total() === 1 ? __('Showing :count invoice', ['count' => number_format($invoices->total())]) : __('Showing :count invoices', ['count' => number_format($invoices->total())])" :paginator="$invoices">
            <table class="min-w-full">
                <thead class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Invoice') }}</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Period') }}</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Due') }}</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Amount') }}</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Status') }}</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($invoices as $invoice)
                        @php
                            $paidAmount = (float) $invoice->payments->sum('amount');
                            $chip = match ($invoice->status) {
                                'paid' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
                                'overdue' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
                                'refunded', 'cancelled' => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
                                default => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                            };
                            $dueBadge = '';
                            if (in_array($invoice->status, ['pending', 'overdue'], true) && $invoice->due_date) {
                                $dueIn = (int) today()->diffInDays($invoice->due_date, false);
                                $dueBadge = $dueIn < 0 ? __(':days days overdue', ['days' => abs($dueIn)]) : ($dueIn === 0 ? __('due today') : __('due in :days days', ['days' => $dueIn]));
                            }
                        @endphp
                        <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4">
                                <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $invoice->invoice_number }}</span>
                                @if($invoice->subscription?->plan)
                                    <div class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $invoice->subscription->plan->name }}</div>
                                @elseif($invoice->addon?->addon)
                                    <div class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $invoice->addon->addon->name }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">
                                @if($invoice->period_start && $invoice->period_end)
                                    {{ $invoice->period_start->format('d M Y') }} – {{ $invoice->period_end->format('d M Y') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-theme-sm text-gray-600 dark:text-gray-400">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</span>
                                @if($dueBadge)
                                    <div class="mt-0.5 text-theme-xs {{ $invoice->status === 'overdue' ? 'font-semibold text-error-600 dark:text-error-400' : 'text-warning-600 dark:text-warning-400' }}">{{ $dueBadge }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">৳{{ number_format($invoice->amount, 2) }}</span>
                                @if($paidAmount > 0)
                                    <div class="mt-0.5 text-theme-xs font-normal text-success-600 dark:text-success-400">{{ __('paid :amount', ['amount' => '৳'.number_format($paidAmount, 2)]) }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $chip }}">{{ __(ucwords(str_replace('_', ' ', $invoice->status))) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                @if(in_array($invoice->status, ['pending', 'overdue'], true))
                                    <a href="{{ route('bee-pay.saas-invoice', ['saasInvoice' => $invoice]) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3 py-1.5 text-theme-xs font-semibold text-white shadow-theme-xs transition hover:bg-brand-600">
                                        <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                        {{ __('Pay now') }}
                                    </a>
                                @elseif($invoice->status === 'paid' && $paidAmount > 0)
                                    <span class="inline-flex items-center gap-1 text-theme-xs font-medium text-success-600 dark:text-success-400">
                                        <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        {{ __('Settled') }}
                                    </span>
                                @else
                                    <span class="text-theme-xs text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <p class="text-theme-sm text-gray-500 dark:text-gray-400">{{ __('No BeeCore invoices yet.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table>
    @endif
    @endif
</div>
