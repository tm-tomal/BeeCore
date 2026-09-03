<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Subscriptions</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Manually renew, pause, resume, cancel, extend trials, and change plans across every tenant subscription.</p>
        </div>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Subscriptions table -->
    <x-table heading="All subscriptions" :description="'Showing '.number_format($subscriptions->total()).' subscription'.($subscriptions->total() === 1 ? '' : 's')" :paginator="$subscriptions">
        <x-slot:toolbar>
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 dark:text-gray-500">
                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search tenant or plan..." class="h-10 w-56 rounded-lg border border-gray-300 bg-transparent py-2 pl-10 pr-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                </div>
                <select id="sub-status-filter" wire:model.live="statusFilter" class="h-10 w-44 appearance-none rounded-lg border border-gray-300 bg-transparent px-3.5 py-2 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">All statuses</option>
                    <option value="trialing">Trialing</option>
                    <option value="active">Active</option>
                    <option value="paused">Paused</option>
                    <option value="past_due">Past due</option>
                    <option value="suspended">Suspended</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </x-slot:toolbar>

        <table class="min-w-full">
            <thead>
                <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tenant</th>
                    <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Plan</th>
                    <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Cycle</th>
                    <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                    <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Period end</th>
                    <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Trial end</th>
                    <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Auto-renew</th>
                    <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($subscriptions as $subscription)
                    <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                        <td class="px-5 py-4">
                            <a href="{{ route('tenant-details', $subscription->tenant) }}" class="text-theme-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">{{ $subscription->tenant->name }}</a>
                        </td>
                        <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $subscription->plan->name }}</td>
                        <td class="px-5 py-4 text-theme-sm capitalize text-gray-600 dark:text-gray-400">{{ $subscription->billing_cycle }}</td>
                        <td class="px-5 py-4">
                            @php
                                $badge = match($subscription->status) {
                                    'active' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
                                    'trialing' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                                    'paused' => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
                                    'cancelled' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
                                    default => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                                };
                            @endphp
                            <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $badge }}">{{ str_replace('_', ' ', $subscription->status) }}</span>
                        </td>
                        <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $subscription->current_period_ends_at?->format('d M Y') ?? '—' }}</td>
                        <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $subscription->trial_ends_at?->format('d M Y') ?? '—' }}</td>
                        <td class="px-5 py-4">
                            <span class="text-theme-xs font-medium {{ $subscription->auto_renew ? 'text-success-600 dark:text-success-400' : 'text-gray-500 dark:text-gray-400' }}">{{ $subscription->auto_renew ? 'Auto' : 'Manual' }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-1.5">
                                @if($subscription->status !== 'cancelled')
                                    <button type="button" title="Renew for one billing cycle" @click="$dispatch('confirm-action', { title: 'Renew subscription', message: 'Renew this subscription for one more billing cycle?', confirmText: 'Renew', wireMethod: 'renew', wireParams: [{{ $subscription->id }}] })" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                                    </button>
                                    <button type="button" wire:click="openChangePlan({{ $subscription->id }})" title="Change plan" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                                    </button>
                                @endif
                                @if($subscription->status === 'trialing')
                                    <button type="button" wire:click="openExtendTrial({{ $subscription->id }})" title="Extend trial" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="12" y1="15" x2="12" y2="19"/><line x1="10" y1="17" x2="14" y2="17"/></svg>
                                    </button>
                                @endif
                                @if(in_array($subscription->status, ['active', 'trialing'], true))
                                    <button type="button" title="Pause subscription" @click="$dispatch('confirm-action', { title: 'Pause subscription', message: 'Pause this subscription? Billing and automation will stop until resumed.', confirmText: 'Pause', wireMethod: 'pause', wireParams: [{{ $subscription->id }}] })" class="grid h-8 w-8 place-items-center rounded-lg border border-warning-200 bg-warning-50 text-warning-600 transition hover:border-warning-300 hover:bg-warning-100 hover:text-warning-700 dark:border-warning-500/25 dark:bg-warning-500/10 dark:text-warning-400 dark:hover:border-warning-500/40 dark:hover:bg-warning-500/15 dark:hover:text-warning-300">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round"><rect x="6" y="4" width="4" height="16" rx="1"/><rect x="14" y="4" width="4" height="16" rx="1"/></svg>
                                    </button>
                                @endif
                                @if($subscription->status === 'paused')
                                    <button type="button" title="Resume subscription" @click="$dispatch('confirm-action', { title: 'Resume subscription', message: 'Resume this subscription and reactivate the tenant?', confirmText: 'Resume', wireMethod: 'resume', wireParams: [{{ $subscription->id }}] })" class="grid h-8 w-8 place-items-center rounded-lg border border-success-200 bg-success-50 text-success-600 transition hover:border-success-300 hover:bg-success-100 hover:text-success-700 dark:border-success-500/25 dark:bg-success-500/10 dark:text-success-400 dark:hover:border-success-500/40 dark:hover:bg-success-500/15 dark:hover:text-success-300">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                                    </button>
                                @endif
                                @if($subscription->status !== 'cancelled')
                                    <button type="button" title="Cancel subscription" @click="$dispatch('confirm-action', { title: 'Cancel subscription', message: 'Cancel this subscription? The tenant will no longer be billed.', confirmText: 'Cancel', wireMethod: 'cancel', wireParams: [{{ $subscription->id }}] })" class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 hover:text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400 dark:hover:border-error-500/40 dark:hover:bg-error-500/15 dark:hover:text-error-300">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                    </button>
                                @endif
                                <button type="button" wire:click="viewHistory({{ $subscription->id }})" title="View history" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:text-gray-400 dark:hover:border-gray-700 dark:hover:bg-white/[0.05] dark:hover:text-gray-200">
                                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center">
                            <div class="mx-auto max-w-xs">
                                <p class="text-theme-sm text-gray-500 dark:text-gray-400">{{ $search || $statusFilter ? 'No subscriptions match your filters.' : 'No tenant subscriptions found.' }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table>

    @if($changePlanForId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="change-plan-dialog-title">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeModals"></div>
            <div class="relative max-h-[92vh] w-full max-w-md overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-6 flex items-center justify-between">
                    <h3 id="change-plan-dialog-title" class="text-lg font-semibold text-gray-800 dark:text-white/90">Change plan</h3>
                    <button type="button" wire:click="closeModals" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form wire:submit="changePlan" class="space-y-5">
                    <div>
                        <label for="new-plan" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">New plan</label>
                        <select id="new-plan" wire:model="newPlanId" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            @foreach($plans as $plan)<option value="{{ $plan->id }}">{{ $plan->name }}</option>@endforeach
                        </select>
                        @error('newPlanId') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="new-cycle" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Billing cycle</label>
                        <select id="new-cycle" wire:model="newBillingCycle" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">Any remaining value on the current period is logged as a prorated credit in the subscription history.</p>
                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <button type="button" wire:click="closeModals" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Save plan change</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($extendTrialForId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="extend-trial-dialog-title">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeModals"></div>
            <div class="relative max-h-[92vh] w-full max-w-sm overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-6 flex items-center justify-between">
                    <h3 id="extend-trial-dialog-title" class="text-lg font-semibold text-gray-800 dark:text-white/90">Extend trial</h3>
                    <button type="button" wire:click="closeModals" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form wire:submit="extendTrial" class="space-y-5">
                    <div>
                        <label for="extend-days" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Additional days</label>
                        <input id="extend-days" wire:model="extendTrialDays" type="number" min="1" max="365" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('extendTrialDays') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <button type="button" wire:click="closeModals" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Extend trial</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($historyForId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="subscription-history-dialog-title">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeModals"></div>
            <div class="relative max-h-[80vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-5 flex items-center justify-between">
                    <h3 id="subscription-history-dialog-title" class="text-lg font-semibold text-gray-800 dark:text-white/90">Subscription history</h3>
                    <button type="button" wire:click="closeModals" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($historyEvents as $event)
                        <li class="py-3.5">
                            <div class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $event->event }}</div>
                            <div class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $event->created_at->format('d M Y, H:i') }} · {{ $event->user?->name ?? 'System' }}</div>
                            @if($event->from_status || $event->to_status)<div class="mt-0.5 text-theme-xs text-gray-400 dark:text-gray-500">{{ $event->from_status ?? '—' }} → {{ $event->to_status }}</div>@endif
                        </li>
                    @empty
                        <li class="py-6 text-center text-theme-sm text-gray-500 dark:text-gray-400">No history recorded yet.</li>
                    @endforelse
                </ul>
                <div class="mt-5 flex justify-end"><button wire:click="closeModals" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Close</button></div>
            </div>
        </div>
    @endif
</div>
