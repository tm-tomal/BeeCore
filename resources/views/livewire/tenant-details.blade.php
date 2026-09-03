<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">Tenant portfolio</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $tenant->name }}</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $tenant->slug }} · {{ ucfirst($tenant->status) }} · {{ $tenant->currency }} · {{ $tenant->timezone }}</p>
        </div>
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            <a href="{{ route('tenants') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Edit tenant</a>
        </div>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-theme-sm font-medium text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-400">
            {{ session('message') }}
        </div>
    @endif

    <!-- Usage stat cards -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-3 md:gap-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-sm text-gray-500 dark:text-gray-400">Customers</p>
            <p class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $counts['customers'] }}</p>
            @if($subscription?->plan?->customer_limit)
                <p class="mt-2 text-theme-xs {{ $counts['customers'] > $subscription->plan->customer_limit ? 'font-medium text-error-600 dark:text-error-400' : 'text-gray-500 dark:text-gray-400' }}">Limit {{ $subscription->plan->customer_limit }}</p>
            @endif
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-sm text-gray-500 dark:text-gray-400">Active staff</p>
            <p class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $counts['staff'] }}</p>
            @if($subscription?->plan?->staff_limit)
                <p class="mt-2 text-theme-xs {{ $counts['staff'] > $subscription->plan->staff_limit ? 'font-medium text-error-600 dark:text-error-400' : 'text-gray-500 dark:text-gray-400' }}">Limit {{ $subscription->plan->staff_limit }}</p>
            @endif
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-sm text-gray-500 dark:text-gray-400">Resellers</p>
            <p class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $counts['resellers'] }}</p>
            @if($subscription?->plan?->reseller_limit)
                <p class="mt-2 text-theme-xs {{ $counts['resellers'] > $subscription->plan->reseller_limit ? 'font-medium text-error-600 dark:text-error-400' : 'text-gray-500 dark:text-gray-400' }}">Limit {{ $subscription->plan->reseller_limit }}</p>
            @endif
        </div>
    </section>

    <!-- Subscription management -->
    <section class="grid grid-cols-1 gap-4 md:gap-6 xl:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">SaaS subscription</h2>
            @if($plans->isEmpty())
                <p class="mt-5 rounded-lg border border-warning-200 bg-warning-50 px-4 py-3 text-theme-sm font-medium text-warning-700 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-400">Create an active SaaS plan before assigning a subscription.</p>
            @else
                <form wire:submit="saveSubscription" class="mt-5 space-y-5">
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="sub-plan" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Plan</label>
                            <select id="sub-plan" wire:model="planId" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                <option value="">Select plan</option>
                                @foreach($plans as $plan)<option value="{{ $plan->id }}">{{ $plan->name }}</option>@endforeach
                            </select>
                            @error('planId')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="sub-cycle" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Billing cycle</label>
                            <select id="sub-cycle" wire:model="billingCycle" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div>
                            <label for="sub-status" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
                            <select id="sub-status" wire:model.live="subscriptionStatus" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                <option value="trialing">Trialing</option>
                                <option value="active">Active</option>
                                <option value="paused">Paused</option>
                                <option value="past_due">Past due</option>
                                <option value="suspended">Suspended</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label for="sub-starts" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Starts at</label>
                            <input id="sub-starts" wire:model="startsAt" type="date" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        </div>
                        @if($subscriptionStatus === 'trialing')
                            <div>
                                <label for="sub-trial-ends" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Trial ends at</label>
                                <input id="sub-trial-ends" wire:model="trialEndsAt" type="date" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            </div>
                        @endif
                    </div>
                    <label class="flex cursor-pointer select-none items-center gap-2.5 text-theme-sm font-normal text-gray-700 dark:text-gray-400">
                        <input wire:model="autoRenew" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900"> Auto renew subscription
                    </label>
                    <div>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Save subscription</button>
                    </div>
                </form>
            @endif
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Current terms</h2>
            @if($subscription)
                <dl class="mt-5 space-y-4 text-theme-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 dark:text-gray-400">Plan</dt>
                        <dd class="font-medium text-gray-800 dark:text-white/90">{{ $subscription->plan->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                        <dd>
                            @php
                                $badge = match ($subscription->status) {
                                    'active' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
                                    'cancelled' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
                                    'paused' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                    default => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                                };
                            @endphp
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-theme-xs font-medium capitalize {{ $badge }}">{{ str_replace('_', ' ', $subscription->status) }}</span>
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 dark:text-gray-400">Price</dt>
                        <dd class="font-medium text-gray-800 dark:text-white/90">৳{{ number_format($subscription->price, 2) }} / {{ $subscription->billing_cycle }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 dark:text-gray-400">Period ends</dt>
                        <dd class="font-medium text-gray-800 dark:text-white/90">{{ $subscription->current_period_ends_at?->format('d M Y') }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 dark:text-gray-400">Grace ends</dt>
                        <dd class="font-medium text-gray-800 dark:text-white/90">{{ $subscription->grace_ends_at?->format('d M Y') }}</dd>
                    </div>
                </dl>
                <h3 class="mt-7 text-theme-sm font-semibold text-gray-800 dark:text-white/90">History</h3>
                <div class="mt-3 space-y-3">
                    @foreach($subscription->events->sortByDesc('created_at')->take(6) as $event)
                        <div class="border-l-2 border-brand-100 pl-3 dark:border-brand-500/30">
                            <p class="text-theme-xs font-semibold text-gray-800 dark:text-white/90">{{ str_replace('.', ' ', $event->event) }}</p>
                            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ $event->from_status ?? 'new' }} → {{ $event->to_status }} · {{ $event->created_at->diffForHumans() }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-5 text-theme-sm text-gray-500 dark:text-gray-400">No SaaS subscription assigned.</p>
            @endif
        </div>
    </section>

    <!-- SaaS invoices -->
    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6 sm:py-5">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">SaaS invoices</h2>
        </div>
        <div class="w-full overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-y border-gray-100 bg-gray-50/60 dark:border-gray-800">
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Invoice</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Period</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Amount</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Due</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($invoices as $invoice)
                        <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 align-middle text-theme-sm">
                                <span class="font-medium text-gray-800 dark:text-white/90">{{ $invoice->invoice_number }}</span>
                            </td>
                            <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">{{ $invoice->period_start->format('d M Y') }} – {{ $invoice->period_end->format('d M Y') }}</td>
                            <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">৳{{ number_format($invoice->amount, 2) }}</td>
                            <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">{{ $invoice->due_date->format('d M Y') }}</td>
                            <td class="px-5 py-4 align-middle">
                                @php
                                    $invoiceBadge = match ($invoice->status) {
                                        'paid' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
                                        'overdue' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
                                        'cancelled' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                        default => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-theme-xs font-medium capitalize {{ $invoiceBadge }}">{{ $invoice->status }}</span>
                            </td>
                            <td class="px-5 py-4 align-middle">
                                <div class="flex items-center justify-end gap-1">
                                    @if(in_array($invoice->status, ['pending', 'overdue']))
                                        <button wire:click="markInvoicePaid({{ $invoice->id }})" wire:confirm="Mark this SaaS invoice as paid?" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-success-600 transition hover:bg-success-50 dark:text-success-400 dark:hover:bg-success-500/10">Mark paid</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-theme-sm text-gray-500 dark:text-gray-400">No SaaS invoices generated yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
