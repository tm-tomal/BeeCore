<div class="space-y-6">
    @php
        $brandName = ($branding?->is_enabled && $branding->brand_name) ? $branding->brand_name : ($tenant?->name ?? 'BeeCore');
        $brandLogo = ($branding?->is_enabled && $branding->logo_path)
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($branding->logo_path)
            : null;
        $brandFooter = $branding?->is_enabled && $branding->brand_name ? $branding->brand_name : ($tenant?->name ?? 'BeeCore');
        $invoiceBadge = function (string $status): string {
            return match ($status) {
                'paid' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
                'overdue' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
                'cancelled' => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
                'pending' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
                default => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
            };
        };
    @endphp

    @if($viewMode === 'index')
        <!-- Page header -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">Billing</p>
                <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Invoices</h1>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Create line-item invoices, run recurring billing and monitor outstanding balances.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    @click="$dispatch('confirm-action', {
                        title: 'Run recurring billing',
                        message: 'Generate invoices now for every active subscription that is due? Customers already billed for the current period will be skipped — safe to run anytime.',
                        confirmText: 'Generate now',
                        wireMethod: 'generateRecurring',
                        wireParams: [],
                    })"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                >
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                    Run recurring billing
                </button>
                <button wire:click="create" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Generate Invoice
                </button>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
                <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
            </div>
        @endif

        <!-- Billing summary -->
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Open invoices</p>
                <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $summary['open_count'] }}</p>
                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Draft, pending &amp; overdue</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Collected</p>
                <p class="mt-2 truncate text-2xl font-bold text-success-600 dark:text-success-500">৳{{ number_format($summary['collected'], 2) }}</p>
                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Successful payments</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Outstanding</p>
                <p class="mt-2 truncate text-2xl font-bold text-warning-600 dark:text-warning-500">৳{{ number_format($summary['outstanding'], 2) }}</p>
                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Yet to collect</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Overdue</p>
                <p class="mt-2 truncate text-2xl font-bold text-error-600 dark:text-error-500">৳{{ number_format($summary['overdue'], 2) }}</p>
                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Past due balance</p>
            </div>
        </section>

        <!-- Invoices table -->
        <x-table heading="All invoices" :description="'Showing '.number_format($invoices->total()).' invoice'.($invoices->total() === 1 ? '' : 's')" :paginator="$invoices">
            <x-slot:toolbar>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 dark:text-gray-500">
                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search invoice or customer..." class="h-10 w-64 rounded-lg border border-gray-300 bg-transparent py-2 pl-10 pr-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    </div>
                    <select wire:model.live="statusFilter" class="h-10 w-40 appearance-none rounded-lg border border-gray-300 bg-transparent px-3.5 py-2 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">All statuses</option>
                        <option value="draft">Draft</option>
                        <option value="pending">Pending</option>
                        <option value="overdue">Overdue</option>
                        <option value="paid">Paid</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </x-slot:toolbar>

            <table class="min-w-full">
                <thead class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Invoice</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Customer</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Due date</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($invoices as $invoice)
                        <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4">
                                <button type="button" wire:click="viewInvoice({{ $invoice->id }})" class="flex items-center gap-3 text-left">
                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                    </span>
                                    <span>
                                        <span class="block text-theme-sm font-semibold text-gray-800 hover:text-brand-600 dark:text-white/90 dark:hover:text-brand-400">{{ $invoice->invoice_number }}</span>
                                        <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">{{ $invoice->created_at->format('d M Y') }}</span>
                                    </span>
                                </button>
                            </td>
                            <td class="px-5 py-4">
                                @if($invoice->customer)
                                    <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $invoice->customer->name }}</span>
                                    <div class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $invoice->customer->email }}</div>
                                @else
                                    <span class="text-theme-xs text-gray-500 dark:text-gray-400">Deleted customer</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '—' }}</td>
                            <td class="px-5 py-4 text-right">
                                <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">৳{{ number_format($invoice->total, 2) }}</span>
                                @if((float) $invoice->paid_amount > 0)
                                    <div class="mt-0.5 text-theme-xs font-normal text-success-600 dark:text-success-400">Paid ৳{{ number_format($invoice->paid_amount, 2) }}</div>
                                @endif
                                @if((float) $invoice->outstanding_amount > 0)
                                    <div class="text-theme-xs font-normal text-warning-600 dark:text-warning-400">Due ৳{{ number_format($invoice->outstanding_amount, 2) }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $invoiceBadge($invoice->status) }}">{{ $invoice->status }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" wire:click="viewInvoice({{ $invoice->id }})" title="Open full page" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <button type="button" wire:click="edit({{ $invoice->id }})" title="Edit invoice" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button
                                        type="button"
                                        title="Delete invoice"
                                        @click="$dispatch('confirm-action', {
                                            title: 'Delete invoice',
                                            message: 'Delete invoice {{ $invoice->invoice_number }}? This cannot be undone. Payment history is kept but detached from this invoice.',
                                            confirmText: 'Delete',
                                            wireMethod: 'delete',
                                            wireParams: [{{ $invoice->id }}],
                                        })"
                                        class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 hover:text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400 dark:hover:border-error-500/40 dark:hover:bg-error-500/15 dark:hover:text-error-300"
                                    >
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <div class="mx-auto max-w-xs">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">{{ $search || $statusFilter ? 'No invoices match your filters.' : 'No invoices found yet.' }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table>

    @elseif($viewMode === 'view')
        @php
            $inv = $viewingInvoice;
            $invPaid = (float) $inv->paid_amount;
            $invOutstanding = (float) $inv->outstanding_amount;
        @endphp

        <!-- View header -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <button wire:click="closeView" class="grid h-10 w-10 shrink-0 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white" title="Back to invoices">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                </button>
                <div>
                    <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">Invoice</p>
                    <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $inv->invoice_number }}</h1>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('billing.invoice-print', $inv) }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Print / Download PDF
                </a>
                <button wire:click="edit({{ $inv->id }})" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit
                </button>
                <button
                    type="button"
                    @click="$dispatch('confirm-action', {
                        title: 'Delete invoice',
                        message: 'Delete invoice {{ $inv->invoice_number }}? This cannot be undone. Payment history is kept but detached from this invoice.',
                        confirmText: 'Delete',
                        wireMethod: 'delete',
                        wireParams: [{{ $inv->id }}],
                    })"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-error-200 bg-error-50 px-4 py-2.5 text-theme-sm font-medium text-error-600 transition hover:border-error-300 hover:bg-error-100 hover:text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400 dark:hover:border-error-500/40 dark:hover:bg-error-500/15 dark:hover:text-error-300"
                >
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    Delete
                </button>
            </div>
        </div>

        @if((float) $invPaid > 0)
            <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
                <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="8 12 11 15 16 9"/></svg>
                <p class="text-theme-sm text-success-700 dark:text-success-300">This invoice has received ৳{{ number_format($invPaid, 2) }} in payments. Editing or deleting it keeps the payment history — adjust only when you intend to.</p>
            </div>
        @endif

        <!-- Invoice paper -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <!-- Header: brand + status -->
            <div class="flex flex-col gap-6 border-b border-gray-100 px-5 py-6 sm:flex-row sm:items-start sm:justify-between sm:px-8 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    @if($brandLogo)
                        <img src="{{ $brandLogo }}" alt="{{ $brandName }}" class="h-12 w-auto max-w-40 object-contain">
                    @else
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl text-lg font-bold text-white" style="background-color:{{ $branding?->brand_color ?? '#465FFF' }}">{{ strtoupper(substr($brandName, 0, 1)) }}</span>
                    @endif
                    <div>
                        <p class="text-base font-bold text-gray-900 dark:text-white">{{ $brandName }}</p>
                        @if($tenant?->contact_address)
                            <p class="mt-0.5 max-w-xs text-theme-xs leading-5 text-gray-500 dark:text-gray-400">{{ $tenant->contact_address }}</p>
                        @endif
                    </div>
                </div>
                <div class="text-left sm:text-right">
                    <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Invoice</p>
                    <p class="text-title-sm mt-0.5 font-bold text-gray-900 dark:text-white">{{ $inv->invoice_number }}</p>
                    <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $invoiceBadge($inv->status) }}">{{ $inv->status }}</span>
                </div>
            </div>

            <!-- Bill to / issued -->
            <div class="grid gap-6 px-5 py-6 sm:grid-cols-3 sm:px-8">
                <div>
                    <p class="text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Billed to</p>
                    <p class="mt-2 text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $inv->customer?->name ?? 'Deleted customer' }}</p>
                    @if($inv->customer?->email)<p class="mt-0.5 text-theme-sm text-gray-600 dark:text-gray-400">{{ $inv->customer->email }}</p>@endif
                    @if($inv->customer?->phone)<p class="mt-0.5 text-theme-sm text-gray-600 dark:text-gray-400">{{ $inv->customer->phone }}</p>@endif
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between gap-4 text-theme-sm">
                        <span class="text-gray-500 dark:text-gray-400">Issued</span>
                        <span class="font-medium text-gray-800 dark:text-white/90">{{ $inv->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between gap-4 text-theme-sm">
                        <span class="text-gray-500 dark:text-gray-400">Due date</span>
                        <span class="font-medium text-gray-800 dark:text-white/90">{{ $inv->due_date?->format('d M Y') ?? '—' }}</span>
                    </div>
                    @if($inv->billing_period_start)
                        <div class="flex justify-between gap-4 text-theme-sm">
                            <span class="text-gray-500 dark:text-gray-400">Period</span>
                            <span class="font-medium text-gray-800 dark:text-white/90">{{ $inv->billing_period_start->format('M Y') }}</span>
                        </div>
                    @endif
                </div>
                <div class="space-y-2 sm:ml-auto sm:w-52">
                    <div class="flex justify-between gap-4 text-theme-sm">
                        <span class="text-gray-500 dark:text-gray-400">Total</span>
                        <span class="font-semibold text-gray-800 dark:text-white/90">৳{{ number_format($inv->total, 2) }}</span>
                    </div>
                    <div class="flex justify-between gap-4 text-theme-sm">
                        <span class="text-gray-500 dark:text-gray-400">Paid</span>
                        <span class="font-medium text-success-600 dark:text-success-400">৳{{ number_format($invPaid, 2) }}</span>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-gray-100 pt-2 text-theme-sm dark:border-gray-800">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Balance due</span>
                        <span class="font-bold text-gray-900 dark:text-white">৳{{ number_format($invOutstanding, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Line items -->
            <div class="overflow-x-auto px-5 pb-6 sm:px-8">
                <table class="w-full min-w-[540px]">
                    <thead>
                        <tr class="border-y border-gray-200 bg-gray-50/70 text-left dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="py-3 pr-4 text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Description</th>
                            <th class="w-16 py-3 pr-4 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Qty</th>
                            <th class="w-28 py-3 pr-4 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Rate</th>
                            <th class="w-28 py-3 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($inv->items as $item)
                            <tr>
                                <td class="py-3 pr-4 text-theme-sm text-gray-800 dark:text-white/90">{{ $item->description }}</td>
                                <td class="py-3 pr-4 text-right text-theme-sm text-gray-600 dark:text-gray-400">{{ $item->quantity }}</td>
                                <td class="py-3 pr-4 text-right text-theme-sm text-gray-600 dark:text-gray-400">৳{{ number_format($item->unit_price, 2) }}</td>
                                <td class="py-3 text-right text-theme-sm font-medium text-gray-800 dark:text-white/90">৳{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-theme-sm text-gray-500 dark:text-gray-400">No line items on this invoice.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4 ml-auto w-full max-w-xs space-y-2">
                    <div class="flex justify-between text-theme-sm text-gray-600 dark:text-gray-400"><span>Subtotal</span><span>৳{{ number_format($inv->subtotal, 2) }}</span></div>
                    <div class="flex justify-between text-theme-sm text-gray-600 dark:text-gray-400"><span>Tax</span><span>৳{{ number_format($inv->tax_amount, 2) }}</span></div>
                    <div class="flex justify-between border-t border-gray-200 pt-2 text-theme-sm font-semibold text-gray-800 dark:border-gray-800 dark:text-white/90"><span>Total</span><span>৳{{ number_format($inv->total, 2) }}</span></div>
                </div>
            </div>

            <!-- Payments -->
            @if($inv->payments->isNotEmpty())
                <div class="border-t border-gray-100 px-5 py-5 sm:px-8 dark:border-gray-800">
                    <p class="text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Payment history</p>
                    <div class="mt-2 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                        <table class="w-full">
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($inv->payments as $payment)
                                    <tr>
                                        <td class="px-4 py-2.5 text-theme-sm text-gray-800 capitalize dark:text-white/90">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                        <td class="px-4 py-2.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $payment->transaction_id }}</td>
                                        <td class="px-4 py-2.5 text-right text-theme-xs text-gray-500 dark:text-gray-400">{{ $payment->payment_date->format('d M Y') }}</td>
                                        <td class="px-4 py-2.5 text-right text-theme-sm font-medium text-success-600 dark:text-success-400">৳{{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="px-5 pb-5 sm:px-8">
                <x-payment-instructions :tenant="$tenant" title="Payment instructions" />
            </div>

            <!-- Footer: brand / thanks -->
            <div class="border-t border-gray-100 bg-gray-50/50 px-5 py-5 sm:flex sm:items-center sm:justify-between sm:px-8 dark:border-gray-800 dark:bg-white/[0.02]">
                <div class="min-w-0">
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">Thank you for your business.</p>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Questions about this invoice? Contact <span class="font-medium text-gray-700 dark:text-gray-300">{{ $brandFooter }}</span>.</p>
                </div>
                <p class="mt-3 shrink-0 text-theme-xs font-medium text-gray-400 sm:mt-0 dark:text-gray-500">{{ $inv->invoice_number }} · generated by {{ $brandFooter }}</p>
            </div>
        </div>

    @else
        <!-- Page header -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">Billing</p>
                <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $isEditing ? 'Edit invoice' : 'Generate invoice' }}</h1>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $isEditing ? 'Adjust the line items, tax and due date.' : 'Create a line-item invoice for a customer.' }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button wire:click="{{ $isEditing ? 'closeView' : 'cancel' }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to List
                </button>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            <!-- Customer & details -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Customer &amp; details</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Choose who is billed and when it becomes due.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="invoice-customer" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Customer<span class="ml-0.5 text-error-500">*</span></label>
                        <x-search-select wireKey="customer_id" :options="$customerOptions" :value="$customer_id" placeholder="Select a customer..." />
                        @error('customer_id') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="invoice-status" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Status<span class="ml-0.5 text-error-500">*</span></label>
                        <x-search-select wireKey="status" :options="['draft' => 'Draft', 'pending' => 'Pending', 'overdue' => 'Overdue', 'paid' => 'Paid', 'cancelled' => 'Cancelled']" :value="$status" placeholder="Select status" :searchable="false" />
                        @error('status') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="invoice-due-date" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Due date<span class="ml-0.5 text-error-500">*</span></label>
                        <input id="invoice-due-date" type="date" wire:model="due_date" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('due_date') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <!-- Invoice items -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Invoice items</h2>
                        <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Add at least one line item with quantity and unit price.</p>
                    </div>
                    <button type="button" wire:click="addItem" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-xs font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                        <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                        Add item
                    </button>
                </div>

                <div class="space-y-3">
                    @foreach($items as $index => $item)
                        <div wire:key="invoice-item-{{ $index }}" class="grid gap-2.5 rounded-xl border border-gray-100 bg-gray-50/60 p-3 sm:grid-cols-[1fr_80px_110px_110px_36px] dark:border-gray-800 dark:bg-white/[0.02]">
                            <div>
                                <input type="text" wire:model="items.{{ $index }}.description" placeholder="Description" class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                @error("items.$index.description") <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <input type="number" min="0.01" step="0.01" wire:model.live="items.{{ $index }}.quantity" placeholder="Qty" class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                @error("items.$index.quantity") <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <input type="number" min="0" step="0.01" wire:model.live="items.{{ $index }}.unit_price" placeholder="Unit price" class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                @error("items.$index.unit_price") <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex items-center justify-end pr-1 text-theme-sm font-medium text-gray-800 dark:text-white/90">৳{{ number_format((float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0), 2) }}</div>
                            <button type="button" wire:click="removeItem({{ $index }})" @disabled(count($items) <= 1) title="Remove item" class="grid h-10 w-10 place-items-center self-center rounded-lg text-gray-400 transition hover:bg-error-50 hover:text-error-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-error-500/10 dark:hover:text-error-400">
                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            </section>

            <!-- Totals -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Totals</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Subtotal is auto-calculated from line items. Total includes tax.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-theme-xs font-medium text-gray-500 dark:text-gray-400">Subtotal</label>
                        <div class="rounded-lg border border-gray-200 bg-gray-50/60 px-4 py-3 text-theme-sm font-semibold text-gray-800 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white/90">৳{{ number_format((float) $subtotal, 2) }}</div>
                    </div>
                    <div>
                        <label for="invoice-tax" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Tax amount (BDT)</label>
                        <input id="invoice-tax" type="number" min="0" step="0.01" wire:model.live.debounce.300ms="tax_amount" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="0.00">
                        @error('tax_amount') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-theme-xs font-medium text-gray-500 dark:text-gray-400">Total</label>
                        <div class="rounded-lg border border-brand-200 bg-brand-50/60 px-4 py-3 text-theme-sm font-bold text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-400">৳{{ number_format((float) $total, 2) }}</div>
                    </div>
                </div>
            </section>

            <!-- Actions -->
            <div class="sticky bottom-4 flex flex-col-reverse gap-3 rounded-2xl border border-gray-200 bg-white/95 px-5 py-4 shadow-theme-lg backdrop-blur sm:flex-row sm:items-center sm:justify-between dark:border-gray-800 dark:bg-gray-900/95">
                <p class="text-theme-xs text-gray-500 dark:text-gray-400">Fields marked with <span class="text-error-500">*</span> are required.</p>
                <div class="flex flex-col-reverse gap-3 sm:flex-row">
                    <button type="button" wire:click="{{ $isEditing ? 'closeView' : 'cancel' }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Update invoice' : 'Generate invoice' }}</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>
