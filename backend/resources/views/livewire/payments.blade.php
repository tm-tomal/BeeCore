<div class="space-y-6">
    @if($viewMode === 'index')
        <!-- Page header -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">Collections</p>
                <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Payments</h1>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Allocate verified collections to open invoices.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button wire:click="create" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    Record Payment
                </button>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
                <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
            </div>
        @endif

        <!-- Collections summary -->
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total collected</p>
                <p class="mt-2 truncate text-2xl font-bold text-success-600 dark:text-success-500">৳{{ number_format($summary['collected'], 2) }}</p>
                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">All successful payments</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Today</p>
                <p class="mt-2 truncate text-2xl font-bold text-gray-800 dark:text-white/90">৳{{ number_format($summary['today'], 2) }}</p>
                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Collections since midnight</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">This month</p>
                <p class="mt-2 truncate text-2xl font-bold text-brand-600 dark:text-brand-400">৳{{ number_format($summary['month'], 2) }}</p>
                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Collected this month</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Cash collected</p>
                <p class="mt-2 truncate text-2xl font-bold text-gray-800 dark:text-white/90">৳{{ number_format($summary['cash'], 2) }}</p>
                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Cash-only collections</p>
            </div>
        </section>

        <!-- Payments table -->
        <x-table heading="All payments" :description="'Showing '.number_format($payments->total()).' payment'.($payments->total() === 1 ? '' : 's')" :paginator="$payments">
            <x-slot:toolbar>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 dark:text-gray-500">
                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search invoice, customer, tx id..." class="h-10 w-64 rounded-lg border border-gray-300 bg-transparent py-2 pl-10 pr-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    </div>
                    <select wire:model.live="methodFilter" class="h-10 w-40 appearance-none rounded-lg border border-gray-300 bg-transparent px-3.5 py-2 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">All methods</option>
                        <option value="cash">Cash</option>
                        <option value="bkash">bKash</option>
                        <option value="card">Card / Bank</option>
                    </select>
                </div>
            </x-slot:toolbar>

            <table class="min-w-full">
                <thead class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Payment</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Customer</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Invoice</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Date</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($payments as $payment)
                        @php
                            $methodLabel = match ($payment->payment_method) {
                                'bkash' => 'bKash',
                                'card' => 'Card / Bank',
                                default => 'Cash',
                            };
                            $methodChip = match ($payment->payment_method) {
                                'bkash' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
                                'card' => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
                                default => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
                            };
                        @endphp
                        <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400">
                                        @if($payment->payment_method === 'cash')
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
                                        @elseif($payment->payment_method === 'bkash')
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                        @else
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                                        @endif
                                    </span>
                                    <div class="min-w-0">
                                        <span class="inline-flex rounded-md px-2 py-0.5 text-theme-xs font-medium {{ $methodChip }}">{{ $methodLabel }}</span>
                                        @if($payment->transaction_id)
                                            <div class="mt-1 truncate text-theme-xs text-gray-500 dark:text-gray-400">Tx {{ $payment->transaction_id }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->customer->name ?? 'Deleted customer' }}</span>
                                @if($payment->customer)
                                    <div class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $payment->customer->email }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-theme-sm font-medium text-brand-600 dark:text-brand-400">{{ $payment->invoice?->invoice_number ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $payment->payment_date->format('d M Y, h:i A') }}</td>
                            <td class="px-5 py-4 text-right text-theme-sm font-semibold text-success-600 dark:text-success-400">৳{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <div class="mx-auto max-w-xs">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">{{ $search || $methodFilter ? 'No payments match your filters.' : 'No payments recorded yet.' }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table>

    @else
        <!-- Page header -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">Collections</p>
                <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Record payment</h1>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Allocate a collection to an open invoice.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button wire:click="cancel" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to List
                </button>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            <!-- Invoice & amount -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Invoice &amp; amount</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Choose the open invoice being paid and how much was received.</p>
                </div>

                @if($invoices->isEmpty())
                    <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
                        <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <p class="text-theme-sm text-success-700 dark:text-success-300">All invoices are settled. There are no open invoices to record a payment against.</p>
                    </div>
                @else
                    <div>
                        <label for="payment-invoice" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Open invoice<span class="ml-0.5 text-error-500">*</span></label>
                        <x-search-select wireKey="invoice_id" :options="$invoiceOptions" :value="$invoice_id" placeholder="Select an open invoice..." :live="true" />
                        @error('invoice_id') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    @if($invoice_id)
                        @php $selectedInvoice = $invoices->firstWhere('id', (int) $invoice_id); @endphp
                        @if($selectedInvoice)
                            <div class="mt-4 grid gap-4 rounded-xl border border-gray-200 bg-gray-50/60 p-4 sm:grid-cols-3 dark:border-gray-800 dark:bg-white/[0.02]">
                                <div>
                                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">Invoice</p>
                                    <p class="mt-1 text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $selectedInvoice->invoice_number }}</p>
                                </div>
                                <div>
                                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">Customer</p>
                                    <p class="mt-1 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $selectedInvoice->customer?->name ?? 'Deleted customer' }}</p>
                                </div>
                                <div>
                                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">Outstanding</p>
                                    <p class="mt-1 text-theme-sm font-semibold text-warning-600 dark:text-warning-500">৳{{ number_format($selectedInvoice->outstanding_amount, 2) }}</p>
                                </div>
                            </div>
                        @endif
                    @endif

                    <div class="mt-4">
                        <label for="payment-amount" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Amount received (BDT)<span class="ml-0.5 text-error-500">*</span></label>
                        <div class="relative max-w-sm">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-theme-sm font-medium text-gray-400 dark:text-gray-500">৳</span>
                            <input id="payment-amount" type="number" step="0.01" min="1" wire:model="amount" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-9 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="0.00">
                        </div>
                        <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">Cannot exceed the invoice's outstanding balance.</p>
                        @error('amount') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                @endif
            </section>

            <!-- Payment method -->
            @if($invoices->isNotEmpty())
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="mb-4">
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Payment method</h2>
                        <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Cash needs no reference. Gateway payments need a transaction ID.</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="payment-method" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Method<span class="ml-0.5 text-error-500">*</span></label>
                            <x-search-select wireKey="payment_method" :options="['cash' => 'Cash', 'bkash' => 'bKash', 'card' => 'Card / Bank']" :value="$payment_method" placeholder="Select method" :searchable="false" :live="true" />
                            @error('payment_method') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        @if($payment_method !== 'cash')
                            <div>
                                <label for="payment-transaction-id" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Transaction ID<span class="ml-0.5 text-error-500">*</span></label>
                                <input id="payment-transaction-id" type="text" wire:model="transaction_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="e.g. BKASH8H3K2L">
                                <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">Used for gateway reconciliation and duplicate detection.</p>
                                @error('transaction_id') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            @if($invoices->isNotEmpty() && $tenant)
                <x-payment-instructions :tenant="$tenant" title="What your customer sees" />
            @endif

            <!-- Actions -->
            <div class="sticky bottom-4 flex flex-col-reverse gap-3 rounded-2xl border border-gray-200 bg-white/95 px-5 py-4 shadow-theme-lg backdrop-blur sm:flex-row sm:items-center sm:justify-between dark:border-gray-800 dark:bg-gray-900/95">
                <p class="text-theme-xs text-gray-500 dark:text-gray-400">Fields marked with <span class="text-error-500">*</span> are required.</p>
                <div class="flex flex-col-reverse gap-3 sm:flex-row">
                    <button type="button" wire:click="cancel" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50" @disabled($invoices->isEmpty())>
                        <span wire:loading.remove wire:target="save">Record payment</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>
