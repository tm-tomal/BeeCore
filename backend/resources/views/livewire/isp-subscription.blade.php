<div class="space-y-6">
    <!-- Page header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">Workspace</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">My BeeCore subscription</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Every plan includes all BeeCore features — choose by your ISP size and pay only for what you grow into.</p>
        </div>
    </div>

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
            $isSwitchOrder = $subscription && $subscription->status !== 'cancelled' && $currentPlanId !== $checkPlan->id;
            $onlineMethods = collect($paymentMethods)->where('manual', false)->values();
            $manualMethods = collect($paymentMethods)->where('manual', true)->values();
            $chosen = collect($paymentMethods)->firstWhere('key', $checkoutGateway);
        @endphp

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">Secure checkout</p>
                    <h2 class="mt-1 text-title-sm font-bold text-gray-900 dark:text-white">{{ $isSwitchOrder ? 'Change to '.$checkPlan->name : 'Subscribe to '.$checkPlan->name }}</h2>
                    <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                        {{ $isSwitchOrder
                            ? 'Your subscription will change after payment — from '.($subscription?->plan?->name ?? 'your current plan').'.'
                            : 'Complete payment below to activate your plan.' }}
                    </p>
                </div>
                <button wire:click="cancelCheckout" class="inline-flex w-fit items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to plans
                </button>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-5">
                <!-- Order summary -->
                <div class="lg:col-span-2">
                    <div class="flex h-full flex-col rounded-xl border border-gray-200 bg-gray-50/60 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-theme-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Order summary</p>
                                <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ $checkPlan->name }}</p>
                                <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $checkCycle === 'yearly' ? 'Yearly billing' : 'Monthly billing' }} · {{ $checkPlan->customer_limit !== null ? number_format($checkPlan->customer_limit).' customers' : 'Unlimited customers' }}</p>
                            </div>
                            <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">{{ $checkPlan->operationModeLabel() }}</span>
                        </div>

                        <dl class="mt-5 space-y-3 border-t border-gray-200 pt-4 text-theme-sm dark:border-gray-800">
                            @if($checkCycle === 'yearly')
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-gray-500 dark:text-gray-400">Regular (12 × ৳{{ number_format($cMonthly, 0) }})</dt>
                                    <dd class="text-gray-500 dark:text-gray-400 line-through">৳{{ number_format($cRegular, 0) }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-3 text-success-600 dark:text-success-400">
                                    <dt>Yearly discount ({{ $cDiscount }}%)</dt>
                                    <dd>− ৳{{ number_format($cSave, 0) }}</dd>
                                </div>
                            @endif
                            <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-3 dark:border-gray-800">
                                <dt class="font-medium text-gray-800 dark:text-white/90">Total {{ $checkCycle === 'yearly' ? '/ year' : '/ month' }}</dt>
                                <dd class="text-title-sm font-bold text-gray-900 dark:text-white">৳{{ number_format($cPrice, 2) }}</dd>
                            </div>
                            @if($checkCycle === 'yearly')
                                <p class="flex items-center gap-1.5 text-theme-xs text-success-600 dark:text-success-400">
                                    <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                                    You save ৳{{ number_format($cSave, 0) }} by paying yearly.
                                </p>
                            @endif
                        </dl>

                        <div class="mt-4 rounded-lg border border-brand-100 bg-brand-50/60 px-3.5 py-2.5 text-theme-xs text-gray-600 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-gray-300">
                            @if($isSwitchOrder)
                                Online payment applies immediately; manual orders are activated after the BeeCore Account team verifies your payment.
                            @else
                                An invoice for your first {{ $checkCycle }} period is raised with this order.
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payment methods -->
                <div class="lg:col-span-3">
                    @if($onlineMethods->isNotEmpty())
                        <p class="mb-2 text-theme-sm font-semibold text-gray-800 dark:text-white/90">Online payment <span class="font-normal text-theme-xs text-success-600 dark:text-success-400">· instant activation</span></p>
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
                                        <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">Online · instant activation</span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    @if($manualMethods->isNotEmpty())
                        <p class="mt-5 mb-2 text-theme-sm font-semibold text-gray-800 dark:text-white/90">Manual payment <span class="font-normal text-theme-xs text-warning-600 dark:text-warning-400">· BeeCore Account team verifies</span></p>
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
                                        <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">Bank transfer · manual verification</span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    @error('checkoutGateway')
                        <p class="mt-3 rounded-lg border border-error-200 bg-error-50 px-3.5 py-2.5 text-theme-xs text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">{{ $message }}</p>
                    @enderror

                    @if($chosen && ($chosen['manual'] ?? false) && !empty($chosen['account']))
                        @php $acct = $chosen['account']; @endphp
                        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 dark:border-emerald-500/25 dark:bg-emerald-500/10">
                            <p class="text-theme-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-400">Pay to this account</p>
                            <dl class="mt-2 grid gap-2 text-theme-sm sm:grid-cols-2">
                                @foreach(['bank_name' => 'Bank', 'account_name' => 'Account name', 'account_number' => 'Account number', 'routing_number' => 'Routing', 'branch_name' => 'Branch', 'swift_code' => 'SWIFT'] as $field => $label)
                                    @if(!empty($acct[$field]))
                                        <div>
                                            <dt class="text-theme-xs text-emerald-600/80 dark:text-emerald-500/70">{{ $label }}</dt>
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
                                After paying, the BeeCore Account team verifies your transfer and activates the plan.
                            @else
                                Payment is processed securely. Your plan activates immediately after payment.
                            @endif
                        </p>
                        <button type="button" wire:click="confirmCheckout" wire:loading.attr="disabled" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                            <span wire:loading.remove wire:target="confirmCheckout">{{ $chosen && ($chosen['manual'] ?? false) ? 'Submit order — pay ৳'.number_format($cPrice, 0) : 'Pay ৳'.number_format($cPrice, 0).' now' }}</span>
                            <span wire:loading wire:target="confirmCheckout">Processing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    @else
    <!-- Plan catalogue -->
    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="min-w-0">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $subscription ? 'Change your plan' : 'Choose your plan' }}</h2>
                <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">
                    {{ $billingCycle === 'yearly'
                        ? 'Yearly billing — pay for 12 months at a discounted rate.'
                        : ($subscription
                            ? 'Plans available for your operation type. The new price applies from your next billing period.'
                            : 'Pick a plan below to activate this workspace — an invoice for the first period will be created right away.') }}
                </p>
            </div>
            <div class="flex shrink-0 flex-col items-start gap-2 sm:items-end">
                @if(!$subscription)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-warning-50 px-3 py-1 text-theme-xs font-medium text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                        <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        No active subscription yet
                    </span>
                @endif
                <div class="inline-flex rounded-xl border border-gray-200 bg-gray-100 p-1 dark:border-gray-700 dark:bg-white/[0.04]" role="tablist" aria-label="Billing cycle">
                    <button
                        type="button"
                        role="tab"
                        wire:click="$set('billingCycle', 'monthly')"
                        aria-selected="{{ $billingCycle === 'monthly' ? 'true' : 'false' }}"
                        class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $billingCycle === 'monthly' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
                    >Monthly</button>
                    <button
                        type="button"
                        role="tab"
                        wire:click="$set('billingCycle', 'yearly')"
                        aria-selected="{{ $billingCycle === 'yearly' ? 'true' : 'false' }}"
                        class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $billingCycle === 'yearly' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
                    >
                        Yearly
                        <span class="inline-flex rounded-full bg-success-100 px-1.5 py-0.5 text-[10px] font-bold text-success-700 dark:bg-success-500/20 dark:text-success-400">−20–30%</span>
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
                                        Current plan
                                    </span>
                                @endif
                            </div>
                            <p class="mt-1 line-clamp-2 text-theme-xs text-gray-500 dark:text-gray-400">{{ $plan->description }}</p>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1.5">
                            @if($discountPct > 0)
                                <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-[10px] font-bold text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                    <svg class="size-3 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                                    Save {{ $discountPct }}%/yr
                                </span>
                            @endif
                            <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">{{ $plan->operationModeLabel() }}</span>
                        </div>
                    </div>

                    @if($billingCycle === 'yearly')
                        <div class="mt-4 flex items-end gap-2">
                            <span class="text-title-md font-bold text-gray-900 dark:text-white">৳{{ number_format($yearlyValue, 0) }}</span>
                            <span class="pb-1 text-theme-xs text-gray-500 dark:text-gray-400">/year · billed once</span>
                        </div>
                        <p class="mt-1 flex flex-wrap items-center gap-x-1.5 text-theme-xs text-gray-500 dark:text-gray-400">
                            <span class="line-through text-gray-400 dark:text-gray-500">৳{{ number_format($yearlyRegular, 0) }}</span>
                            regular
                            <span class="font-semibold text-success-600 dark:text-success-400">save ৳{{ number_format($yearlySavings, 0) }}</span>
                            @if($yearlyMonthly) · ≈ ৳{{ number_format($yearlyMonthly, 0) }}/mo @endif
                        </p>
                    @else
                        <div class="mt-4 flex items-end gap-2">
                            <span class="text-title-md font-bold text-gray-900 dark:text-white">৳{{ number_format($plan->monthly_price, 2) }}</span>
                            <span class="pb-1 text-theme-xs text-gray-500 dark:text-gray-400">/month</span>
                        </div>
                        @if($yearlyValue > 0)
                            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">
                                Yearly: <span class="font-medium text-success-600 dark:text-success-400">৳{{ number_format($yearlyValue, 0) }}</span> — save {{ $discountPct }}%
                            </p>
                        @endif
                    @endif

                    <dl class="mt-4 space-y-2 border-t border-gray-100 pt-4 text-theme-sm dark:border-gray-800">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Included customers</dt>
                            <dd class="font-semibold text-gray-800 dark:text-white/90">{{ $plan->customer_limit !== null ? number_format($plan->customer_limit) : 'Unlimited' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Overflow charge</dt>
                            <dd class="font-semibold text-gray-800 dark:text-white/90">
                                @if($plan->customer_limit !== null && (float) $plan->overflow_rate > 0)
                                    ৳{{ number_format($plan->overflow_rate, 2) }}/customer
                                @else
                                    <span class="font-normal text-gray-400">—</span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Staff / resellers</dt>
                            <dd class="font-semibold text-gray-800 dark:text-white/90">{{ $plan->staff_limit ?? 'Unlimited' }} / {{ $plan->reseller_limit ?? 'Unlimited' }}</dd>
                        </div>
                    </dl>

                    <p class="mt-3 flex items-center gap-1.5 text-theme-xs text-gray-500 dark:text-gray-400">
                        <svg class="size-3.5 stroke-current text-brand-500 dark:text-brand-400" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        All BeeCore features unlocked
                    </p>

                    <div class="mt-5 flex-1" aria-hidden="true"></div>
                    @if($isCurrent)
                        <button type="button" disabled class="inline-flex w-full cursor-default items-center justify-center gap-2 rounded-lg bg-gray-100 px-4 py-2.5 text-theme-sm font-medium text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">Your current plan</button>
                    @else
                        <button type="button" wire:click="openCheckout({{ $plan->id }})" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            {{ $subscription ? 'Switch to this plan' : 'Subscribe' }}
                        </button>
                    @endif
                </article>
        @empty
            <div class="rounded-xl border border-dashed border-gray-300 px-6 py-12 text-center lg:col-span-2 xl:col-span-3 dark:border-gray-700">
                <p class="text-theme-sm font-medium text-gray-600 dark:text-gray-300">No plans are available for your workspace yet.</p>
                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">The BeeCore Account team will publish plans for your operation type shortly.</p>
            </div>
        @endforelse
        </div>
    </section>

    @if($subscription)
        @php
            $plan = $subscription->plan;
            $statusChip = match ($subscription->status) {
                'active' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
                'trialing' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
                'past_due' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                'suspended' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
                default => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
            };
            $cycleLabel = ucfirst(str_replace('_', ' ', $subscription->billing_cycle));
            $daysLeft = $subscription->current_period_ends_at ? (int) today()->diffInDays($subscription->current_period_ends_at, false) : null;
        @endphp

        <section class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 xl:col-span-7">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex items-center gap-4">
                            <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-brand-500/10 text-2xl font-extrabold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                {{ strtoupper(substr($plan?->name ?? 'Bee', 0, 1)) }}
                            </span>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $plan?->name ?? 'Custom plan' }}</h2>
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $statusChip }}">{{ str_replace('_', ' ', $subscription->status) }}</span>
                                </div>
                                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $plan?->description }}</p>
                            </div>
                        </div>
                        <div class="shrink-0 text-left sm:text-right">
                            <p class="text-title-md font-bold text-gray-900 dark:text-white">৳{{ number_format($subscription->price, 2) }}</p>
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">per {{ $cycleLabel }}</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 rounded-xl border border-gray-200 bg-gray-50/60 p-4 sm:grid-cols-3 dark:border-gray-800 dark:bg-white/[0.02]">
                        <div>
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">Period</p>
                            <p class="mt-1 text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $subscription->starts_at?->format('d M Y') ?? '—' }}
                                @if($subscription->current_period_ends_at) → {{ $subscription->current_period_ends_at->format('d M Y') }} @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">Renews</p>
                            <p class="mt-1 text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                @if($daysLeft !== null && $daysLeft >= 0)
                                    {{ $subscription->current_period_ends_at->format('d M Y') }} · in {{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }}
                                @else
                                    Expired
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">Auto renew</p>
                            <p class="mt-1 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $subscription->auto_renew ? 'Enabled' : 'Disabled' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-12 xl:col-span-5">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Plan limits</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Your included usage on this plan.</p>
                    <dl class="mt-4 space-y-3">
                        <div class="flex items-center justify-between gap-3 text-theme-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Customers</dt>
                            <dd class="font-semibold text-gray-800 dark:text-white/90">{{ $plan?->customer_limit !== null ? number_format($plan->customer_limit) : 'Unlimited' }}</dd>
                        </div>
                        @if($plan?->customer_limit !== null && (float) $plan->overflow_rate > 0)
                            <div class="flex items-center justify-between gap-3 text-theme-sm">
                                <dt class="text-gray-500 dark:text-gray-400">Overflow (per extra customer)</dt>
                                <dd class="font-semibold text-brand-600 dark:text-brand-400">৳{{ number_format($plan->overflow_rate, 2) }}</dd>
                            </div>
                        @endif
                        <div class="flex items-center justify-between gap-3 text-theme-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Staff logins</dt>
                            <dd class="font-semibold text-gray-800 dark:text-white/90">{{ $plan?->staff_limit !== null ? number_format($plan->staff_limit) : 'Unlimited' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-theme-sm">
                            <dt class="text-gray-500 dark:text-gray-400">Resellers</dt>
                            <dd class="font-semibold text-gray-800 dark:text-white/90">{{ $plan?->reseller_limit !== null ? number_format($plan->reseller_limit) : 'Unlimited' }}</dd>
                        </div>
                    </dl>
                    <div class="mt-5 rounded-xl border border-brand-100 bg-brand-50/60 px-4 py-3 dark:border-brand-500/20 dark:bg-brand-500/10">
                        <p class="text-theme-xs leading-5 text-gray-600 dark:text-gray-300">Need a custom limit or help with billing? Contact the BeeCore Account team or your BeeCore Sales contact — they can always adjust your plan.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- BeeCore invoices -->
        <x-table heading="BeeCore invoices" :description="'Showing '.number_format($invoices->total()).' invoice'.($invoices->total() === 1 ? '' : 's')" :paginator="$invoices">
            <table class="min-w-full">
                <thead class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Invoice</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Period</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Due</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Amount</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
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
                        @endphp
                        <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4">
                                <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $invoice->invoice_number }}</span>
                                @if($invoice->subscription?->plan)
                                    <div class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $invoice->subscription->plan->name }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">
                                @if($invoice->period_start && $invoice->period_end)
                                    {{ $invoice->period_start->format('d M Y') }} – {{ $invoice->period_end->format('d M Y') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</td>
                            <td class="px-5 py-4 text-right">
                                <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">৳{{ number_format($invoice->amount, 2) }}</span>
                                @if($paidAmount > 0)
                                    <div class="mt-0.5 text-theme-xs font-normal text-success-600 dark:text-success-400">Paid ৳{{ number_format($paidAmount, 2) }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $chip }}">{{ $invoice->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <p class="text-theme-sm text-gray-500 dark:text-gray-400">No BeeCore invoices yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table>
    @endif

    <!-- Legacy subscribe dialog is no longer shown — plan selection opens the checkout page instead. -->
    @if(false)
        @php
            $isSwitch = $subscription && $subscription->status !== 'cancelled' && $currentPlanId !== $selectedPlan->id;
            $selectedPrice = $billingCycle === 'yearly' ? $selectedPlan->yearly_price : $selectedPlan->monthly_price;
            $selectedMonthly = (float) $selectedPlan->monthly_price;
            $selectedYearly = (float) $selectedPlan->yearly_price;
            $selectedRegular = round($selectedMonthly * 12, 2);
            $selectedSavings = round($selectedRegular - $selectedYearly, 2);
            $selectedDiscountPct = $selectedPlan->yearlyDiscountPercent();
            $yearlyMonthly = $selectedYearly > 0 ? round($selectedYearly / 12) : null;
        @endphp
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="subscribe-dialog-title">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeSubscribe"></div>
            <div class="relative max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-6 flex items-center justify-between">
                    <h3 id="subscribe-dialog-title" class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $isSwitch ? 'Switch to '.$selectedPlan->name : 'Subscribe to '.$selectedPlan->name }}</h3>
                    <button type="button" wire:click="closeSubscribe" class="grid size-8 place-items-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.05] dark:hover:text-gray-300">
                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-500/10 text-lg font-extrabold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ strtoupper(substr($selectedPlan->name, 0, 1)) }}</span>
                    <div class="min-w-0">
                        <p class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $selectedPlan->name }}</p>
                        <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">
                            {{ $selectedPlan->customer_limit !== null ? number_format($selectedPlan->customer_limit).' customers included' : 'Unlimited customers' }}
                            @if($selectedPlan->customer_limit !== null && (float) $selectedPlan->overflow_rate > 0)
                                · ৳{{ number_format($selectedPlan->overflow_rate, 2) }}/extra customer
                            @endif
                        </p>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <button type="button" wire:click="$set('billingCycle', 'monthly')"
                        class="rounded-xl border p-4 text-left transition"
                        @class([
                            'border-brand-500 bg-brand-50/60 ring-2 ring-brand-500/20 dark:border-brand-500 dark:bg-brand-500/10' => $billingCycle === 'monthly',
                            'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700' => $billingCycle !== 'monthly',
                        ])>
                        <span class="flex items-center gap-2 text-theme-sm font-semibold text-gray-900 dark:text-white">
                            <span class="grid size-4 place-items-center rounded-full border {{ $billingCycle === 'monthly' ? 'border-brand-500' : 'border-gray-300 dark:border-gray-700' }}">
                                <span class="size-2 rounded-full {{ $billingCycle === 'monthly' ? 'bg-brand-500' : '' }}"></span>
                            </span>
                            Monthly
                        </span>
                        <span class="mt-2 block text-title-sm font-bold text-gray-900 dark:text-white">৳{{ number_format($selectedPlan->monthly_price, 2) }}</span>
                        <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">billed every month · no discount</span>
                    </button>
                    <button type="button" wire:click="$set('billingCycle', 'yearly')"
                        class="rounded-xl border p-4 text-left transition"
                        @class([
                            'border-brand-500 bg-brand-50/60 ring-2 ring-brand-500/20 dark:border-brand-500 dark:bg-brand-500/10' => $billingCycle === 'yearly',
                            'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700' => $billingCycle !== 'yearly',
                        ])>
                        <span class="flex items-center gap-2 text-theme-sm font-semibold text-gray-900 dark:text-white">
                            <span class="grid size-4 place-items-center rounded-full border {{ $billingCycle === 'yearly' ? 'border-brand-500' : 'border-gray-300 dark:border-gray-700' }}">
                                <span class="size-2 rounded-full {{ $billingCycle === 'yearly' ? 'bg-brand-500' : '' }}"></span>
                            </span>
                            Yearly
                            <span class="inline-flex rounded-full bg-success-100 px-1.5 py-0.5 text-[10px] font-bold text-success-700 dark:bg-success-500/20 dark:text-success-400">−{{ $selectedDiscountPct }}%</span>
                        </span>
                        <span class="mt-2 block text-title-sm font-bold text-gray-900 dark:text-white">৳{{ number_format($selectedYearly, 0) }}</span>
                        <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">
                            @if($yearlyMonthly)≈ ৳{{ number_format($yearlyMonthly, 0) }}/mo · @endif billed once a year
                            @if($selectedSavings > 0)<span class="font-semibold text-success-600 dark:text-success-400"> · save ৳{{ number_format($selectedSavings, 0) }}</span>@endif
                        </span>
                    </button>
                </div>
                @error('selectedPlanId')
                    <p class="mt-3 rounded-lg border border-error-200 bg-error-50 px-3.5 py-2.5 text-theme-xs text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">{{ $message }}</p>
                @enderror

                <div class="mt-6 flex items-center justify-between gap-4 rounded-xl bg-gray-50 px-4 py-3 dark:bg-white/[0.02]">
                    <div>
                        <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $isSwitch ? 'New price from next billing period' : 'First period invoice' }}</p>
                        <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">৳{{ number_format($selectedPrice, 2) }} {{ $billingCycle === 'yearly' ? 'for 12 months' : '/month' }}</p>
                    </div>
                    <button type="button" wire:click="subscribe" wire:loading.attr="disabled" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                        <span wire:loading.remove wire:target="subscribe">{{ $isSwitch ? 'Confirm change' : 'Subscribe now' }}</span>
                        <span wire:loading wire:target="subscribe">Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
    @endif
</div>
