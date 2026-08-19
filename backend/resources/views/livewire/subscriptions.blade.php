<div>
    <header class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
        <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Subscriptions</h1>
        <p class="mt-2 text-sm text-slate-500">Manually renew, pause, resume, cancel, extend trials, and change plans across every tenant subscription.</p>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <div class="mb-5 max-w-xs">
        <label class="bc-label" for="sub-status-filter">Status</label>
        <select id="sub-status-filter" wire:model.live="statusFilter" class="bc-field">
            <option value="">All statuses</option>
            <option value="trialing">Trialing</option>
            <option value="active">Active</option>
            <option value="paused">Paused</option>
            <option value="past_due">Past due</option>
            <option value="suspended">Suspended</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <div class="bc-table-wrap">
        <table class="bc-table">
            <thead><tr><th>Tenant</th><th>Plan</th><th>Cycle</th><th>Status</th><th>Period end</th><th>Trial end</th><th>Auto-renew</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @forelse($subscriptions as $subscription)
                    <tr>
                        <td><a href="{{ route('tenant-details', $subscription->tenant) }}" class="font-semibold text-teal-300">{{ $subscription->tenant->name }}</a></td>
                        <td>{{ $subscription->plan->name }}</td>
                        <td class="capitalize">{{ $subscription->billing_cycle }}</td>
                        <td><span class="capitalize font-semibold {{ match($subscription->status) { 'active' => 'text-emerald-300', 'trialing' => 'text-teal-300', 'paused' => 'text-amber-300', 'cancelled' => 'text-slate-500', default => 'text-rose-300' } }}">{{ str_replace('_', ' ', $subscription->status) }}</span></td>
                        <td>{{ $subscription->current_period_ends_at?->format('d M Y') ?? '—' }}</td>
                        <td>{{ $subscription->trial_ends_at?->format('d M Y') ?? '—' }}</td>
                        <td>{{ $subscription->auto_renew ? 'Yes' : 'No' }}</td>
                        <td class="text-right">
                            <div class="flex flex-wrap justify-end gap-3">
                                @if($subscription->status !== 'cancelled')
                                    <button wire:click="renew({{ $subscription->id }})" wire:confirm="Renew this subscription for one billing cycle?" class="font-semibold text-teal-300">Renew</button>
                                    <button wire:click="openChangePlan({{ $subscription->id }})" class="font-semibold text-slate-300">Change plan</button>
                                @endif
                                @if($subscription->status === 'trialing')
                                    <button wire:click="openExtendTrial({{ $subscription->id }})" class="font-semibold text-slate-300">Extend trial</button>
                                @endif
                                @if(in_array($subscription->status, ['active', 'trialing'], true))
                                    <button wire:click="pause({{ $subscription->id }})" wire:confirm="Pause this subscription?" class="font-semibold text-amber-300">Pause</button>
                                @endif
                                @if($subscription->status === 'paused')
                                    <button wire:click="resume({{ $subscription->id }})" wire:confirm="Resume this subscription?" class="font-semibold text-emerald-300">Resume</button>
                                @endif
                                @if($subscription->status !== 'cancelled')
                                    <button wire:click="cancel({{ $subscription->id }})" wire:confirm="Cancel this subscription?" class="font-semibold text-rose-300">Cancel</button>
                                @endif
                                <button wire:click="viewHistory({{ $subscription->id }})" class="font-semibold text-slate-400">History</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-12 text-center text-slate-600">No tenant subscriptions found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($subscriptions->hasPages())<div class="border-t border-white/10 p-4">{{ $subscriptions->links() }}</div>@endif
    </div>

    @if($changePlanForId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="closeModals"></div>
            <div class="bc-panel relative w-full max-w-md p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">Change plan</h2>
                <form wire:submit="changePlan" class="mt-5 space-y-4">
                    <div><label class="bc-label" for="new-plan">New plan</label><select id="new-plan" wire:model="newPlanId" class="bc-field">@foreach($plans as $plan)<option value="{{ $plan->id }}">{{ $plan->name }}</option>@endforeach</select>@error('newPlanId')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="new-cycle">Billing cycle</label><select id="new-cycle" wire:model="newBillingCycle" class="bc-field"><option value="monthly">Monthly</option><option value="yearly">Yearly</option></select></div>
                    <p class="text-xs text-slate-500">Any remaining value on the current period is logged as a prorated credit in the subscription history.</p>
                    <div class="flex justify-end gap-3"><button type="button" wire:click="closeModals" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Save plan change</button></div>
                </form>
            </div>
        </div>
    @endif

    @if($extendTrialForId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="closeModals"></div>
            <div class="bc-panel relative w-full max-w-sm p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">Extend trial</h2>
                <form wire:submit="extendTrial" class="mt-5 space-y-4">
                    <div><label class="bc-label" for="extend-days">Additional days</label><input id="extend-days" wire:model="extendTrialDays" type="number" min="1" max="365" class="bc-field">@error('extendTrialDays')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div class="flex justify-end gap-3"><button type="button" wire:click="closeModals" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Extend trial</button></div>
                </form>
            </div>
        </div>
    @endif

    @if($historyForId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="closeModals"></div>
            <div class="bc-panel relative max-h-[80vh] w-full max-w-lg overflow-y-auto p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">Subscription history</h2>
                <ul class="mt-5 space-y-3 text-sm">
                    @forelse($historyEvents as $event)
                        <li class="border-b border-white/10 pb-3">
                            <div class="font-semibold text-teal-300">{{ $event->event }}</div>
                            <div class="text-xs text-slate-500">{{ $event->created_at->format('d M Y, H:i') }} · {{ $event->user?->name ?? 'System' }}</div>
                            @if($event->from_status || $event->to_status)<div class="text-xs text-slate-400">{{ $event->from_status ?? '—' }} → {{ $event->to_status }}</div>@endif
                        </li>
                    @empty
                        <li class="py-6 text-center text-slate-600">No history recorded yet.</li>
                    @endforelse
                </ul>
                <div class="mt-5 flex justify-end"><button wire:click="closeModals" class="bc-secondary">Close</button></div>
            </div>
        </div>
    @endif
</div>
