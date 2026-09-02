<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">SaaS payments</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Record manual payments, verify pending transactions, and search transaction history across every tenant.</p>
        </div>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Payments summary -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Completed</p>
            <p class="mt-2 truncate text-2xl font-bold text-success-600 dark:text-success-500">৳{{ number_format($report['completed'], 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Pending verification</p>
            <p class="mt-2 truncate text-2xl font-bold text-warning-600 dark:text-warning-500">৳{{ number_format($report['pending'], 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Failed payments</p>
            <p class="mt-2 text-2xl font-bold text-error-600 dark:text-error-500">{{ $report['failed'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Refunded</p>
            <p class="mt-2 truncate text-2xl font-bold text-gray-800 dark:text-white/90">৳{{ number_format($report['refunded'], 2) }}</p>
        </div>
    </section>

    <!-- Record a manual payment -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Record a manual payment</h2>
        </div>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($unpaidInvoices as $invoice)
                <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50/50 p-3.5 text-theme-sm dark:border-gray-800 dark:bg-white/[0.02]">
                    <div class="min-w-0">
                        <div class="truncate font-medium text-gray-800 dark:text-white/90">{{ $invoice->tenant->name }}</div>
                        <div class="mt-0.5 truncate text-theme-xs text-gray-500 dark:text-gray-400">{{ $invoice->invoice_number }} · ৳{{ number_format($invoice->amount, 2) }}</div>
                    </div>
                    <button wire:click="openRecordPayment({{ $invoice->id }})" class="shrink-0 rounded-lg px-3 py-2 text-theme-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">Record</button>
                </div>
            @empty
                <p class="col-span-full py-4 text-center text-theme-sm text-gray-500 dark:text-gray-400">No pending or overdue invoices right now.</p>
            @endforelse
        </div>
    </div>

    <!-- Filters -->
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div>
            <label for="pay-search" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Search</label>
            <input id="pay-search" wire:model.live.debounce.300ms="search" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="Tenant, invoice, reference">
        </div>
        <div>
            <label for="pay-status" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
            <select id="pay-status" wire:model.live="statusFilter" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
                <option value="failed">Failed</option>
            </select>
        </div>
        <div>
            <label for="pay-method" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Method</label>
            <select id="pay-method" wire:model.live="methodFilter" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                <option value="">All methods</option>
                @foreach($methods as $method)<option value="{{ $method }}">{{ ucfirst($method) }}</option>@endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label for="pay-from" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">From</label>
                <input id="pay-from" wire:model.live="dateFrom" type="date" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
            </div>
            <div>
                <label for="pay-to" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">To</label>
                <input id="pay-to" wire:model.live="dateTo" type="date" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
            </div>
        </div>
    </div>

    <!-- Payments table -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="w-full overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tenant</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Invoice</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Amount</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Method</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Reference</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Paid at</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($payments as $payment)
                        <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4">
                                <a href="{{ route('tenant-details', $payment->tenant) }}" class="text-theme-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">{{ $payment->tenant->name }}</a>
                            </td>
                            <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400">{{ $payment->invoice?->invoice_number ?? '—' }}</td>
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">
                                ৳{{ number_format($payment->amount, 2) }}
                                @if($payment->refunds->isNotEmpty())<div class="mt-0.5 text-theme-xs text-error-600 dark:text-error-400">Refunded ৳{{ number_format($payment->refunds->sum('amount'), 2) }}</div>@endif
                            </td>
                            <td class="px-5 py-4 text-theme-sm capitalize text-gray-600 dark:text-gray-400">{{ $payment->method }}</td>
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $payment->reference ?? '—' }}</td>
                            <td class="px-5 py-4">
                                @php
                                    $badge = match($payment->status) {
                                        'completed' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
                                        'failed' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
                                        default => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $badge }}">{{ $payment->status }}</span>
                            </td>
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $payment->paid_at?->format('d M Y, H:i') }}</td>
                            <td class="px-5 py-4">
                                @if($payment->status === 'pending')
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" title="Verify payment" @click="$dispatch('confirm-action', { title: 'Verify payment', message: 'Verify this payment and settle its invoice if fully covered?', confirmText: 'Verify', wireMethod: 'verifyPayment', wireParams: [{{ $payment->id }}] })" class="grid h-8 w-8 place-items-center rounded-lg border border-success-200 bg-success-50 text-success-600 transition hover:border-success-300 hover:bg-success-100 hover:text-success-700 dark:border-success-500/25 dark:bg-success-500/10 dark:text-success-400 dark:hover:border-success-500/40 dark:hover:bg-success-500/15 dark:hover:text-success-300">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                        </button>
                                        <button type="button" title="Mark payment failed" @click="$dispatch('confirm-action', { title: 'Mark payment failed', message: 'Mark this payment as failed? It will not settle the invoice.', confirmText: 'Mark failed', wireMethod: 'markFailed', wireParams: [{{ $payment->id }}] })" class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 hover:text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400 dark:hover:border-error-500/40 dark:hover:bg-error-500/15 dark:hover:text-error-300">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                        </button>
                                    </div>
                                @else
                                    <span class="block text-right text-theme-xs text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-theme-sm text-gray-500 dark:text-gray-400">No payments match these filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $payments->links() }}</div>
        @endif
    </div>

    @if($recordForInvoiceId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="record-payment-dialog-title">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeModals"></div>
            <div class="relative max-h-[92vh] w-full max-w-md overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-6 flex items-center justify-between">
                    <h3 id="record-payment-dialog-title" class="text-lg font-semibold text-gray-800 dark:text-white/90">Record payment</h3>
                    <button type="button" wire:click="closeModals" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form wire:submit="recordPayment" class="space-y-5">
                    <div>
                        <label for="rec-amount" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Amount</label>
                        <input id="rec-amount" wire:model="recordAmount" type="number" step="0.01" min="0.01" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('recordAmount') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="rec-method" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Method</label>
                        <select id="rec-method" wire:model="recordMethod" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            <option value="manual">Manual</option>
                            <option value="bank_transfer">Bank transfer</option>
                            <option value="mobile_banking">Mobile banking</option>
                            <option value="card">Card</option>
                        </select>
                    </div>
                    <div>
                        <label for="rec-reference" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Reference (optional)</label>
                        <input id="rec-reference" wire:model="recordReference" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    </div>
                    <label class="flex cursor-pointer select-none items-center gap-2.5 text-theme-sm font-normal text-gray-700 dark:text-gray-400">
                        <input wire:model="recordAsPending" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900"> Record as pending (needs verification before it settles the invoice)
                    </label>
                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <button type="button" wire:click="closeModals" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Save payment</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
