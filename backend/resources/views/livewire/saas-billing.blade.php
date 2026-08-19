<div>
    <header class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
        <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">SaaS billing</h1>
        <p class="mt-2 text-sm text-slate-500">Line-item invoice detail, discounts, credits, refunds, reminders, and financial totals across every tenant.</p>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <section class="mb-6 grid gap-3 sm:grid-cols-3 lg:grid-cols-5">
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Collected</p><p class="mt-2 text-xl font-black text-emerald-300">৳{{ number_format($summary['collected'], 2) }}</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Outstanding</p><p class="mt-2 text-xl font-black text-amber-300">৳{{ number_format($summary['outstanding'], 2) }}</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Overdue</p><p class="mt-2 text-xl font-black text-rose-300">৳{{ number_format($summary['overdue'], 2) }}</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Credits &amp; discounts</p><p class="mt-2 text-xl font-black text-slate-200">৳{{ number_format($summary['credits'], 2) }}</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Refunds</p><p class="mt-2 text-xl font-black text-slate-200">৳{{ number_format($summary['refunds'], 2) }}</p></div>
    </section>

    <div class="mb-5 max-w-xs">
        <label class="bc-label" for="billing-status-filter">Status</label>
        <select id="billing-status-filter" wire:model.live="statusFilter" class="bc-field">
            <option value="">All statuses</option>
            <option value="pending">Pending</option>
            <option value="overdue">Overdue</option>
            <option value="paid">Paid</option>
            <option value="refunded">Refunded</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <div class="bc-table-wrap">
        <table class="bc-table">
            <thead><tr><th>Invoice</th><th>Tenant</th><th>Period</th><th>Amount</th><th>Due</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td><code class="text-teal-300">{{ $invoice->invoice_number }}</code>@if($invoice->reminder_sent_at)<div class="text-[10px] text-slate-600">Reminder sent {{ $invoice->reminder_sent_at->format('d M') }}</div>@endif</td>
                        <td><a href="{{ route('tenant-details', $invoice->tenant) }}" class="font-semibold text-teal-300">{{ $invoice->tenant->name }}</a></td>
                        <td>{{ $invoice->period_start->format('d M Y') }} – {{ $invoice->period_end->format('d M Y') }}</td>
                        <td>৳{{ number_format($invoice->amount, 2) }}</td>
                        <td>{{ $invoice->due_date->format('d M Y') }}</td>
                        <td><span class="capitalize font-semibold {{ match($invoice->status) { 'paid' => 'text-emerald-300', 'overdue' => 'text-rose-300', 'cancelled' => 'text-slate-500', 'refunded' => 'text-slate-400', default => 'text-amber-300' } }}">{{ $invoice->status }}</span></td>
                        <td class="text-right">
                            <div class="flex flex-wrap justify-end gap-3">
                                <button wire:click="viewInvoice({{ $invoice->id }})" class="font-semibold text-slate-300">Detail</button>
                                @if(in_array($invoice->status, ['pending', 'overdue']))
                                    <button wire:click="openAdjustment({{ $invoice->id }})" class="font-semibold text-teal-300">Adjust</button>
                                    <button wire:click="sendReminder({{ $invoice->id }})" wire:confirm="Log a payment reminder for this invoice?" class="font-semibold text-amber-300">Remind</button>
                                    <button wire:click="cancelInvoice({{ $invoice->id }})" wire:confirm="Cancel this invoice?" class="font-semibold text-rose-300">Cancel</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-12 text-center text-slate-600">No SaaS invoices found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($invoices->hasPages())<div class="border-t border-white/10 p-4">{{ $invoices->links() }}</div>@endif
    </div>

    @if($detailInvoice)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="closeModals"></div>
            <div class="bc-panel relative max-h-[85vh] w-full max-w-xl overflow-y-auto p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">{{ $detailInvoice->invoice_number }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $detailInvoice->tenant->name }} · {{ ucfirst($detailInvoice->status) }}</p>

                <h3 class="mt-5 text-xs font-bold uppercase tracking-wide text-slate-400">Line items</h3>
                <ul class="mt-3 divide-y divide-white/10 text-sm">
                    @forelse($detailInvoice->items as $item)
                        <li class="flex items-center justify-between py-2">
                            <div><span class="capitalize text-slate-300">{{ $item->description }}</span><div class="text-[10px] uppercase text-slate-600">{{ $item->type }}</div></div>
                            <span class="font-semibold {{ $item->amount < 0 ? 'text-emerald-300' : 'text-slate-200' }}">৳{{ number_format($item->amount, 2) }}</span>
                        </li>
                    @empty
                        <li class="py-4 text-center text-slate-600">No line items recorded.</li>
                    @endforelse
                </ul>
                <div class="mt-3 flex justify-between border-t border-white/10 pt-3 text-sm font-bold text-white"><span>Total</span><span>৳{{ number_format($detailInvoice->amount, 2) }}</span></div>

                <h3 class="mt-6 text-xs font-bold uppercase tracking-wide text-slate-400">Payments &amp; refunds</h3>
                <ul class="mt-3 space-y-3 text-sm">
                    @forelse($detailInvoice->payments as $payment)
                        <li class="border-b border-white/10 pb-3">
                            <div class="flex items-center justify-between"><span class="text-slate-300">৳{{ number_format($payment->amount, 2) }} · {{ $payment->method }}</span><span class="text-xs text-slate-600">{{ $payment->paid_at->format('d M Y') }}</span></div>
                            @foreach($payment->refunds as $refund)
                                <div class="mt-1 text-xs text-rose-300">Refunded ৳{{ number_format($refund->amount, 2) }}{{ $refund->reason ? ' · '.$refund->reason : '' }}</div>
                            @endforeach
                            <button wire:click="openRefund({{ $payment->id }})" class="mt-2 text-xs font-semibold text-teal-300">Record refund</button>
                        </li>
                    @empty
                        <li class="py-4 text-center text-slate-600">No payments recorded yet.</li>
                    @endforelse
                </ul>

                <div class="mt-5 flex justify-end"><button wire:click="closeModals" class="bc-secondary">Close</button></div>
            </div>
        </div>
    @endif

    @if($adjustmentForInvoiceId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="closeModals"></div>
            <div class="bc-panel relative w-full max-w-md p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">Add discount, credit, or adjustment</h2>
                <form wire:submit="addAdjustment" class="mt-5 space-y-4">
                    <div><label class="bc-label" for="adj-type">Type</label><select id="adj-type" wire:model="adjustmentType" class="bc-field"><option value="discount">Discount</option><option value="credit">Credit</option><option value="adjustment">Adjustment (charge)</option></select></div>
                    <div><label class="bc-label" for="adj-desc">Description</label><input id="adj-desc" wire:model="adjustmentDescription" class="bc-field">@error('adjustmentDescription')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="adj-amount">Amount</label><input id="adj-amount" wire:model="adjustmentAmount" type="number" step="0.01" min="0.01" class="bc-field">@error('adjustmentAmount')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div class="flex justify-end gap-3"><button type="button" wire:click="closeModals" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Save</button></div>
                </form>
            </div>
        </div>
    @endif

    @if($refundForPaymentId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="closeModals"></div>
            <div class="bc-panel relative w-full max-w-sm p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">Record refund</h2>
                <form wire:submit="recordRefund" class="mt-5 space-y-4">
                    <div><label class="bc-label" for="refund-amount">Amount</label><input id="refund-amount" wire:model="refundAmount" type="number" step="0.01" min="0.01" class="bc-field">@error('refundAmount')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="refund-reason">Reason</label><input id="refund-reason" wire:model="refundReason" class="bc-field"></div>
                    <div class="flex justify-end gap-3"><button type="button" wire:click="closeModals" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Save refund</button></div>
                </form>
            </div>
        </div>
    @endif
</div>
