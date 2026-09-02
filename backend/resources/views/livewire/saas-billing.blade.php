<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">SaaS billing</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Line-item invoice detail, discounts, credits, refunds, reminders, and financial totals across every tenant.</p>
        </div>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Billing summary -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-3 md:gap-6 xl:grid-cols-5">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Collected</p>
            <p class="mt-2 truncate text-2xl font-bold text-success-600 dark:text-success-500">৳{{ number_format($summary['collected'], 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Outstanding</p>
            <p class="mt-2 truncate text-2xl font-bold text-warning-600 dark:text-warning-500">৳{{ number_format($summary['outstanding'], 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Overdue</p>
            <p class="mt-2 truncate text-2xl font-bold text-error-600 dark:text-error-500">৳{{ number_format($summary['overdue'], 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Credits &amp; discounts</p>
            <p class="mt-2 truncate text-2xl font-bold text-gray-800 dark:text-white/90">৳{{ number_format($summary['credits'], 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Refunds</p>
            <p class="mt-2 truncate text-2xl font-bold text-gray-800 dark:text-white/90">৳{{ number_format($summary['refunds'], 2) }}</p>
        </div>
    </section>

    <!-- Invoices table -->
    <x-table heading="SaaS invoices" :description="'Showing '.number_format($invoices->total()).' invoice'.($invoices->total() === 1 ? '' : 's')" :paginator="$invoices">
        <x-slot:toolbar>
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 dark:text-gray-500">
                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search invoice or tenant..." class="h-10 w-56 rounded-lg border border-gray-300 bg-transparent py-2 pl-10 pr-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                </div>
                <select id="billing-status-filter" wire:model.live="statusFilter" class="h-10 w-44 appearance-none rounded-lg border border-gray-300 bg-transparent px-3.5 py-2 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">All statuses</option>
                    <option value="pending">Pending</option>
                    <option value="overdue">Overdue</option>
                    <option value="paid">Paid</option>
                    <option value="refunded">Refunded</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </x-slot:toolbar>

        <table class="min-w-full">
            <thead>
                <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Invoice</th>
                    <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tenant</th>
                    <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Period</th>
                    <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Amount</th>
                    <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Due</th>
                    <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                    <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($invoices as $invoice)
                    <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                        <td class="px-5 py-4">
                            <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $invoice->invoice_number }}</span>
                            @if($invoice->reminder_sent_at)<div class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Reminder sent {{ $invoice->reminder_sent_at->format('d M') }}</div>@endif
                        </td>
                        <td class="px-5 py-4">
                            <a href="{{ route('tenant-details', $invoice->tenant) }}" class="text-theme-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">{{ $invoice->tenant->name }}</a>
                        </td>
                        <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $invoice->period_start->format('d M Y') }} – {{ $invoice->period_end->format('d M Y') }}</td>
                        <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">৳{{ number_format($invoice->amount, 2) }}</td>
                        <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $invoice->due_date->format('d M Y') }}</td>
                        <td class="px-5 py-4">
                            @php
                                $badge = match($invoice->status) {
                                    'paid' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
                                    'overdue' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
                                    'cancelled' => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
                                    'refunded' => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
                                    default => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                                };
                            @endphp
                            <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $badge }}">{{ $invoice->status }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap items-center justify-end gap-1.5">
                                <button type="button" wire:click="viewInvoice({{ $invoice->id }})" title="Invoice detail" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                                @if(in_array($invoice->status, ['pending', 'overdue']))
                                    <button type="button" wire:click="openAdjustment({{ $invoice->id }})" title="Add adjustment" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>
                                    </button>
                                    <button type="button" title="Send payment reminder" @click="$dispatch('confirm-action', { title: 'Send payment reminder', message: 'Log a payment reminder for this invoice?', confirmText: 'Remind', wireMethod: 'sendReminder', wireParams: [{{ $invoice->id }}] })" class="grid h-8 w-8 place-items-center rounded-lg border border-warning-200 bg-warning-50 text-warning-600 transition hover:border-warning-300 hover:bg-warning-100 hover:text-warning-700 dark:border-warning-500/25 dark:bg-warning-500/10 dark:text-warning-400 dark:hover:border-warning-500/40 dark:hover:bg-warning-500/15 dark:hover:text-warning-300">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                    </button>
                                    <button type="button" title="Cancel invoice" @click="$dispatch('confirm-action', { title: 'Cancel invoice', message: 'Cancel this invoice? It can no longer be paid or adjusted.', confirmText: 'Cancel', wireMethod: 'cancelInvoice', wireParams: [{{ $invoice->id }}] })" class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 hover:text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400 dark:hover:border-error-500/40 dark:hover:bg-error-500/15 dark:hover:text-error-300">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center">
                            <div class="mx-auto max-w-xs">
                                <p class="text-theme-sm text-gray-500 dark:text-gray-400">{{ $search || $statusFilter ? 'No invoices match your filters.' : 'No SaaS invoices found.' }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-table>

    @if($detailInvoice)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="invoice-detail-title">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeModals"></div>
            <div class="relative max-h-[85vh] w-full max-w-xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 id="invoice-detail-title" class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $detailInvoice->invoice_number }}</h3>
                        <p class="mt-0.5 text-theme-sm text-gray-500 dark:text-gray-400">{{ $detailInvoice->tenant->name }} · {{ ucfirst($detailInvoice->status) }}</p>
                    </div>
                    <button type="button" wire:click="closeModals" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <h4 class="mt-6 text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Line items</h4>
                <ul class="mt-3 divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($detailInvoice->items as $item)
                        <li class="flex items-center justify-between gap-4 py-2.5">
                            <div class="min-w-0">
                                <span class="block truncate text-theme-sm capitalize text-gray-700 dark:text-gray-300">{{ $item->description }}</span>
                                <div class="text-theme-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $item->type }}</div>
                            </div>
                            <span class="shrink-0 text-theme-sm font-semibold {{ $item->amount < 0 ? 'text-success-600 dark:text-success-400' : 'text-gray-800 dark:text-white/90' }}">৳{{ number_format($item->amount, 2) }}</span>
                        </li>
                    @empty
                        <li class="py-4 text-center text-theme-sm text-gray-500 dark:text-gray-400">No line items recorded.</li>
                    @endforelse
                </ul>
                <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3 text-theme-sm font-bold text-gray-800 dark:border-gray-800 dark:text-white/90"><span>Total</span><span>৳{{ number_format($detailInvoice->amount, 2) }}</span></div>

                <h4 class="mt-6 text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Payments &amp; refunds</h4>
                <ul class="mt-3 divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($detailInvoice->payments as $payment)
                        <li class="py-3">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90">৳{{ number_format($payment->amount, 2) }} · {{ $payment->method }}</span>
                                <span class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $payment->paid_at->format('d M Y') }}</span>
                            </div>
                            @foreach($payment->refunds as $refund)
                                <div class="mt-1 text-theme-xs text-error-600 dark:text-error-400">Refunded ৳{{ number_format($refund->amount, 2) }}{{ $refund->reason ? ' · '.$refund->reason : '' }}</div>
                            @endforeach
                            <button wire:click="openRefund({{ $payment->id }})" class="mt-2 rounded-lg px-2 py-1 text-theme-xs font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">Record refund</button>
                        </li>
                    @empty
                        <li class="py-4 text-center text-theme-sm text-gray-500 dark:text-gray-400">No payments recorded yet.</li>
                    @endforelse
                </ul>

                <div class="mt-5 flex justify-end"><button wire:click="closeModals" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Close</button></div>
            </div>
        </div>
    @endif

    @if($adjustmentForInvoiceId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="adjustment-dialog-title">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeModals"></div>
            <div class="relative max-h-[92vh] w-full max-w-md overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-6 flex items-center justify-between">
                    <h3 id="adjustment-dialog-title" class="text-lg font-semibold text-gray-800 dark:text-white/90">Add discount, credit, or adjustment</h3>
                    <button type="button" wire:click="closeModals" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form wire:submit="addAdjustment" class="space-y-5">
                    <div>
                        <label for="adj-type" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Type</label>
                        <select id="adj-type" wire:model="adjustmentType" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            <option value="discount">Discount</option>
                            <option value="credit">Credit</option>
                            <option value="adjustment">Adjustment (charge)</option>
                        </select>
                    </div>
                    <div>
                        <label for="adj-desc" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Description</label>
                        <input id="adj-desc" wire:model="adjustmentDescription" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('adjustmentDescription') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="adj-amount" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Amount</label>
                        <input id="adj-amount" wire:model="adjustmentAmount" type="number" step="0.01" min="0.01" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('adjustmentAmount') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <button type="button" wire:click="closeModals" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($refundForPaymentId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="refund-dialog-title">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeModals"></div>
            <div class="relative max-h-[92vh] w-full max-w-sm overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-6 flex items-center justify-between">
                    <h3 id="refund-dialog-title" class="text-lg font-semibold text-gray-800 dark:text-white/90">Record refund</h3>
                    <button type="button" wire:click="closeModals" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form wire:submit="recordRefund" class="space-y-5">
                    <div>
                        <label for="refund-amount" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Amount</label>
                        <input id="refund-amount" wire:model="refundAmount" type="number" step="0.01" min="0.01" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('refundAmount') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="refund-reason" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Reason</label>
                        <input id="refund-reason" wire:model="refundReason" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <button type="button" wire:click="closeModals" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Save refund</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
