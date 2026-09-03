<div class="space-y-6">
    @php
        $statusBadge = function (string $status): string {
            return match ($status) {
                'paid' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
                'overdue' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
                'refunded' => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
                'cancelled' => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
                'pending' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                default => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
            };
        };
        $methodChip = function (string $method): string {
            return match ($method) {
                'bkash' => 'bg-pink-50 text-pink-600 ring-1 ring-inset ring-pink-100 dark:bg-pink-500/10 dark:text-pink-400 dark:ring-pink-500/25',
                'bank', 'manual' => 'bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/25',
                'cash' => 'bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-200 dark:bg-white/[0.05] dark:text-gray-400 dark:ring-gray-700',
                default => 'bg-brand-50 text-brand-600 ring-1 ring-inset ring-brand-100 dark:bg-brand-500/10 dark:text-brand-400 dark:ring-brand-500/25',
            };
        };
    @endphp

    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $detailInvoice ? 'Invoice details' : 'SaaS billing' }}</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                {{ $detailInvoice
                    ? 'Manage line items, payments and refunds for '.$detailInvoice->invoice_number.'.'
                    : 'Line-item invoice detail, discounts, credits, refunds, reminders, and financial totals across every tenant.' }}
            </p>
        </div>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="flex items-start gap-3 rounded-xl border border-error-200 bg-error-50 px-4 py-3 dark:border-error-500/20 dark:bg-error-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-error-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <p class="text-theme-sm text-error-700 dark:text-error-300">{{ session('error') }}</p>
        </div>
    @endif

    @if($detailInvoice)
        @php
            $inv = $detailInvoice;
            $invPaid = (float) $inv->payments->where('status', 'completed')->sum('amount');
            $invRefunded = (float) $inv->payments->flatMap->refunds->sum('amount');
            $invNet = max(0, $invPaid - $invRefunded);
            $invDue = max(0, (float) $inv->amount - $invNet);
            $payLink = in_array($inv->status, ['pending', 'overdue'], true) ? route('bee-pay.saas-invoice', ['saasInvoice' => $inv]) : null;
        @endphp

        <!-- Detail header -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <button wire:click="closeModals" class="grid h-10 w-10 shrink-0 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white" title="Back to invoices">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                </button>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $inv->invoice_number }}</h2>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $statusBadge($inv->status) }}">{{ $inv->status }}</span>
                    </div>
                    <p class="mt-0.5 text-theme-sm text-gray-500 dark:text-gray-400">
                        {{ $inv->tenant->name }}
                        @if($inv->subscription?->plan) · {{ $inv->subscription->plan->name }} @endif
                        @if($inv->addon?->addon) · {{ $inv->addon->addon->name }} add-on @endif
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('tenant-details', $inv->tenant) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    {{ __('Tenant') }}
                </a>
                @if(in_array($inv->status, ['pending', 'overdue'], true))
                    <button type="button" wire:click="openAdjustment({{ $inv->id }})" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>
                        {{ __('Adjust') }}
                    </button>
                    <button type="button" @click="$dispatch('confirm-action', { title: 'Send payment reminder', message: 'Log a payment reminder for this invoice?', confirmText: 'Remind', wireMethod: 'sendReminder', wireParams: [{{ $inv->id }}] })" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        {{ __('Remind') }}
                    </button>
                    <button type="button" @click="$dispatch('confirm-action', { title: 'Cancel invoice', message: 'Cancel this invoice? It can no longer be paid or adjusted.', confirmText: 'Cancel', wireMethod: 'cancelInvoice', wireParams: [{{ $inv->id }}] })" class="inline-flex items-center justify-center gap-2 rounded-lg border border-error-200 bg-error-50 px-4 py-2.5 text-theme-sm font-medium text-error-600 transition hover:border-error-300 hover:bg-error-100 hover:text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400 dark:hover:border-error-500/40 dark:hover:bg-error-500/15 dark:hover:text-error-300">
                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        {{ __('Cancel invoice') }}
                    </button>
                @endif
            </div>
        </div>

        <!-- Invoice paper -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <!-- meta strip -->
            <div class="grid grid-cols-2 gap-4 border-b border-gray-100 px-5 py-5 sm:grid-cols-4 sm:px-6 dark:border-gray-800">
                <div>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Issued') }}</p>
                    <p class="mt-1 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $inv->created_at?->format('d M Y') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Due date') }}</p>
                    <p class="mt-1 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $inv->due_date?->format('d M Y') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Period') }}</p>
                    <p class="mt-1 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $inv->period_start?->format('d M Y') }} → {{ $inv->period_end?->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Reminder') }}</p>
                    <p class="mt-1 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $inv->reminder_sent_at?->format('d M Y') ?? 'Not sent' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 px-5 py-5 sm:px-6 lg:grid-cols-5">
                <!-- Line items -->
                <div class="lg:col-span-3">
                    <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Line items') }}</p>
                    <div class="mt-3 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                        <table class="w-full">
                            <thead class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-white/[0.02]">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Description') }}</th>
                                    <th class="px-4 py-2.5 text-right text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($inv->items as $item)
                                    <tr>
                                        <td class="px-4 py-2.5">
                                            <span class="block text-theme-sm capitalize text-gray-800 dark:text-white/90">{{ $item->description }}</span>
                                            <span class="text-theme-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ $item->type }}{{ $item->creator ? ' · by '.$item->creator->name : '' }}</span>
                                        </td>
                                        <td class="px-4 py-2.5 text-right text-theme-sm font-semibold {{ $item->amount < 0 ? 'text-success-600 dark:text-success-400' : 'text-gray-800 dark:text-white/90' }}">৳{{ number_format($item->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="px-4 py-6 text-center text-theme-sm text-gray-500 dark:text-gray-400">{{ __('No line items recorded.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Balance -->
                <div class="lg:col-span-2">
                    <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Balance') }}</p>
                    <dl class="mt-3 space-y-2.5 rounded-xl border border-gray-200 bg-gray-50/60 p-4 text-theme-sm dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="flex items-center justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">{{ __('Total') }}</dt><dd class="font-semibold text-gray-800 dark:text-white/90">৳{{ number_format((float) $inv->amount, 2) }}</dd></div>
                        <div class="flex items-center justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">{{ __('Collected') }}</dt><dd class="font-semibold text-success-600 dark:text-success-400">৳{{ number_format($invPaid, 2) }}</dd></div>
                        @if($invRefunded > 0)
                            <div class="flex items-center justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">{{ __('Refunded') }}</dt><dd class="font-semibold text-error-600 dark:text-error-400">− ৳{{ number_format($invRefunded, 2) }}</dd></div>
                        @endif
                        <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-2.5 dark:border-gray-800"><dt class="font-medium text-gray-700 dark:text-gray-300">{{ __('Balance due') }}</dt><dd class="text-base font-bold text-gray-900 dark:text-white">৳{{ number_format($invDue, 2) }}</dd></div>
                    </dl>

                    @if($payLink)
                        <div class="mt-4 rounded-xl border border-pink-200 bg-pink-50/60 px-4 py-3.5 dark:border-pink-500/25 dark:bg-pink-500/10">
                            <p class="text-theme-xs font-semibold uppercase tracking-wide text-pink-600 dark:text-pink-400">{{ __('Online payment link') }}</p>
                            <p class="mt-1 text-theme-xs leading-5 text-gray-600 dark:text-gray-400">{{ __('Send to the tenant — they pay securely through bKash and it is recorded automatically.') }}</p>
                            <button type="button" onclick="copyBeePayLink('{{ $payLink }}', this)" title="Copy payment link" class="mt-3 inline-flex items-center justify-center gap-2 rounded-lg bg-pink-500 px-3.5 py-2 text-theme-xs font-semibold text-white shadow-theme-xs transition hover:bg-pink-600">
                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                {{ __('Copy payment link') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payments -->
            <div class="border-t border-gray-100 px-5 py-5 sm:px-6 dark:border-gray-800">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Payments & refunds') }}</p>
                    <div class="flex items-center gap-2">
                        <span class="text-theme-xs text-gray-400 dark:text-gray-500">{{ $inv->payments->count() }} payment{{ $inv->payments->count() === 1 ? '' : 's' }}</span>
                        @if(in_array($inv->status, ['pending', 'overdue'], true))
                            <button type="button" wire:click="openRecordPayment({{ $inv->id }})" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-success-500 px-3 py-2 text-theme-xs font-semibold text-white shadow-theme-xs transition hover:bg-success-600">
                                <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                {{ __('Record payment') }}
                            </button>
                        @endif
                    </div>
                </div>

                @forelse($inv->payments as $payment)
                    @php
                        $refundedAmt = (float) $payment->refunds->sum('amount');
                        $refundable = max(0, (float) $payment->amount - $refundedAmt);
                        $pStatus = $payment->status ?? 'pending';
                    @endphp
                    <div class="mt-3 rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-gray-100 text-sm font-bold uppercase text-gray-600 dark:bg-white/[0.06] dark:text-gray-400">{{ ucfirst(substr($payment->method ?: 'manual', 0, 2)) }}</span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-theme-sm font-semibold text-gray-900 dark:text-white">৳{{ number_format((float) $payment->amount, 2) }}</span>
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-theme-xs font-medium {{ $methodChip($payment->method) }}">{{ $payment->method ?: 'manual' }}</span>
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-theme-xs font-medium capitalize {{ $pStatus === 'completed' ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500' }}">{{ $pStatus }}</span>
                                    </div>
                                    <p class="mt-0.5 truncate text-theme-xs text-gray-500 dark:text-gray-400">
                                        {{ $payment->paid_at?->format('d M Y h:i A') ?? '—' }}
                                        @if($payment->reference) · {{ $payment->reference }} @endif
                                        @if($payment->verifiedBy) · verified by {{ $payment->verifiedBy->name }} @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex shrink-0 flex-wrap items-center gap-1.5">
                                @if($pStatus === 'pending')
                                    <button type="button" wire:click="verifyPayment({{ $payment->id }})" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-success-500 px-3 py-2 text-theme-xs font-semibold text-white shadow-theme-xs transition hover:bg-success-600">
                                        <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        {{ __('Verify') }}
                                    </button>
                                    <button type="button" @click="$dispatch('confirm-action', { title: 'Mark payment failed', message: 'Mark this pending payment as failed? The related order is closed when no other pending payment exists.', confirmText: 'Mark failed', wireMethod: 'markFailed', wireParams: [{{ $payment->id }}] })" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-error-200 bg-error-50 px-3 py-2 text-theme-xs font-medium text-error-600 transition hover:border-error-300 hover:bg-error-100 hover:text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400 dark:hover:border-error-500/40 dark:hover:bg-error-500/15 dark:hover:text-error-300">
                                        {{ __('Mark failed') }}
                                    </button>
                                @elseif($pStatus === 'completed' && $refundable > 0)
                                    <button type="button" wire:click="openRefund({{ $payment->id }})" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-theme-xs font-medium text-gray-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                        <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                        {{ __('Refund') }}
                                    </button>
                                @endif
                                @if($payment->refunds->isEmpty())
                                    <button type="button" @click="$dispatch('confirm-action', { title: 'Delete payment', message: 'Permanently delete this ৳{{ number_format((float) $payment->amount, 2) }} {{ $payment->method ?: 'manual' }} payment? The invoice status will be recalculated. Refunds, if any, block deletion.', confirmText: 'Delete payment', wireMethod: 'deletePayment', wireParams: [{{ $payment->id }}] })" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-error-200 bg-error-50 px-3 py-2 text-theme-xs font-medium text-error-600 transition hover:border-error-300 hover:bg-error-100 hover:text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400 dark:hover:border-error-500/40 dark:hover:bg-error-500/15 dark:hover:text-error-300">
                                        <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        {{ __('Delete') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                        @foreach($payment->refunds as $refund)
                            <div class="mt-2 flex items-center justify-between gap-3 rounded-lg bg-error-50/70 px-3 py-2 text-theme-xs text-error-700 dark:bg-error-500/10 dark:text-error-300">
                                <span>Refunded ৳{{ number_format((float) $refund->amount, 2) }}{{ $refund->reason ? ' · '.$refund->reason : '' }}</span>
                                <span class="text-error-500/80 dark:text-error-400/80">{{ $refund->refunded_at?->format('d M Y') }}</span>
                            </div>
                        @endforeach
                    </div>
                @empty
                    <div class="mt-3 rounded-xl border border-dashed border-gray-300 py-10 text-center dark:border-gray-700">
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">{{ __('No payments recorded yet.') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    @else
        <!-- Financial summary -->
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-3 md:gap-6 xl:grid-cols-5">
            @php
                $cards = [
                    ['label' => 'Collected', 'value' => $summary['collected'], 'icon' => 'M12 2a5 5 0 0 1 4.9 6.2A6 6 0 0 1 18 14a6 6 0 0 1-12 0 6 6 0 0 1 1.1-7.8A5 5 0 0 1 12 2z', 'tone' => 'text-success-600 dark:text-success-400', 'chip' => 'bg-success-500/10'],
                    ['label' => 'Outstanding', 'value' => $summary['outstanding'], 'icon' => 'M3 3h18v18H3zM3 9h18', 'tone' => 'text-warning-600 dark:text-warning-400', 'chip' => 'bg-warning-500/10'],
                    ['label' => 'Overdue', 'value' => $summary['overdue'], 'icon' => 'M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z', 'tone' => 'text-error-600 dark:text-error-400', 'chip' => 'bg-error-500/10'],
                    ['label' => 'Credits & discounts', 'value' => $summary['credits'], 'icon' => 'M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6', 'tone' => 'text-gray-700 dark:text-gray-200', 'chip' => 'bg-gray-500/10'],
                    ['label' => 'Refunds', 'value' => $summary['refunds'], 'icon' => 'M1 4v6h6M3.51 15a9 9 0 1 0 2.13-9.36L1 10', 'tone' => 'text-gray-700 dark:text-gray-200', 'chip' => 'bg-gray-500/10'],
                ];
            @endphp
            @foreach($cards as $card)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center gap-3">
                        <span class="grid size-9 place-items-center rounded-lg {{ $card['chip'] }}">
                            <svg class="size-4 stroke-current {{ $card['tone'] }}" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $card['icon'] }}"/></svg>
                        </span>
                        <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                    </div>
                    <p class="mt-3 truncate text-2xl font-bold {{ $card['tone'] }}">৳{{ number_format($card['value'], 2) }}</p>
                </div>
            @endforeach
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
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Invoice / type</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tenant</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Amount</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($invoices as $invoice)
                        <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4">
                                <button type="button" wire:click="viewInvoice({{ $invoice->id }})" class="text-left">
                                    <span class="flex flex-wrap items-center gap-2">
                                        @if($invoice->tenant_addon_id)
                                            <span class="inline-flex shrink-0 items-center gap-1 rounded-md bg-violet-50 px-2 py-0.5 text-theme-xs font-semibold text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                                                <svg class="size-3 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7H6a3 3 0 0 0 0 6h3v-1H8.5A2.5 2.5 0 0 1 6 14.5V10h10v1h1.5A2.5 2.5 0 0 1 18 13.5v0H6v-6"/></svg>
                                                {{ __('Add-on') }}
                                            </span>
                                        @else
                                            <span class="inline-flex shrink-0 items-center gap-1 rounded-md bg-brand-50 px-2 py-0.5 text-theme-xs font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                                <svg class="size-3 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                                {{ __('Subscription') }}
                                            </span>
                                        @endif
                                        <span class="text-theme-sm font-bold text-gray-800 hover:text-brand-600 dark:text-white/90 dark:hover:text-brand-400">{{ $invoice->invoice_number }}</span>
                                    </span>
                                    @if($invoice->addon?->addon?->name)
                                        <span class="mt-1.5 block text-theme-xs font-medium text-violet-600 dark:text-violet-400">{{ $invoice->addon->addon->name }}</span>
                                    @elseif($invoice->subscription?->plan)
                                        <span class="mt-1.5 block text-theme-xs font-medium text-brand-600 dark:text-brand-400">{{ $invoice->subscription->plan->name }} plan</span>
                                    @endif
                                    <span class="mt-0.5 flex items-center gap-1 text-theme-xs text-gray-400 dark:text-gray-500">
                                        <svg class="size-3 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        {{ $invoice->period_start->format('d M Y') }} – {{ $invoice->period_end->format('d M Y') }}
                                        @if($invoice->reminder_sent_at) · <span class="text-warning-500 dark:text-warning-400">Reminder {{ $invoice->reminder_sent_at->format('d M') }}</span> @endif
                                    </span>
                                </button>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2.5">
                                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-theme-xs font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ strtoupper(substr($invoice->tenant->name, 0, 1)) }}</span>
                                    <a href="{{ route('tenant-details', $invoice->tenant) }}" class="text-theme-sm font-medium text-gray-800 hover:text-brand-600 dark:text-white/90 dark:hover:text-brand-400">{{ $invoice->tenant->name }}</a>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">৳{{ number_format($invoice->amount, 2) }}</span>
                                @if($invoice->status === 'refunded')
                                    <div class="mt-0.5 text-theme-xs font-normal text-gray-400 dark:text-gray-500">Fully refunded</div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $statusBadge($invoice->status) }}">{{ $invoice->status }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap items-center justify-end gap-1.5">
                                    <button type="button" wire:click="viewInvoice({{ $invoice->id }})" title="View invoice" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    @if(in_array($invoice->status, ['pending', 'overdue']))
                                        <button type="button" title="Send payment reminder" @click="$dispatch('confirm-action', { title: 'Send payment reminder', message: 'Log a payment reminder for this invoice?', confirmText: 'Remind', wireMethod: 'sendReminder', wireParams: [{{ $invoice->id }}] })" class="grid h-8 w-8 place-items-center rounded-lg border border-warning-200 bg-warning-50 text-warning-600 transition hover:border-warning-300 hover:bg-warning-100 hover:text-warning-700 dark:border-warning-500/25 dark:bg-warning-500/10 dark:text-warning-400 dark:hover:border-warning-500/40 dark:hover:bg-warning-500/15 dark:hover:text-warning-300">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                        </button>
                                    @endif
                                    <button type="button" title="Delete invoice" @click="$dispatch('confirm-action', { title: 'Delete invoice', message: 'Permanently delete invoice {{ $invoice->invoice_number }}? All attached payments and refunds will also be removed. This cannot be undone.', confirmText: 'Delete invoice', wireMethod: 'deleteInvoice', wireParams: [{{ $invoice->id }}] })" class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 hover:text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400 dark:hover:border-error-500/40 dark:hover:bg-error-500/15 dark:hover:text-error-300">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <div class="mx-auto max-w-xs">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">{{ $search || $statusFilter ? 'No invoices match your filters.' : 'No SaaS invoices found.' }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table>
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

    @if($recordForInvoiceId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="record-payment-title">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeModals"></div>
            <div class="relative max-h-[92vh] w-full max-w-sm overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-6 flex items-center justify-between">
                    <h3 id="record-payment-title" class="text-lg font-semibold text-gray-800 dark:text-white/90">Record payment</h3>
                    <button type="button" wire:click="closeModals" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form wire:submit="recordPayment" class="space-y-5">
                    <div>
                        <label for="record-amount" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Amount</label>
                        <input id="record-amount" wire:model="recordAmount" type="number" step="0.01" min="0.01" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('recordAmount') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="record-method" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Method</label>
                        <select id="record-method" wire:model="recordMethod" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            <option value="manual">Manual / Bank transfer</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="cash">Cash</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label for="record-reference" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Reference / note</label>
                        <input id="record-reference" wire:model="recordReference" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('recordReference') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 px-4 py-3 dark:border-gray-700">
                        <input type="checkbox" wire:model="recordAsPending" class="mt-0.5 size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">
                        <span>
                            <span class="block text-theme-sm font-medium text-gray-800 dark:text-white/90">Record as pending</span>
                            <span class="block text-theme-xs text-gray-500 dark:text-gray-400">Funds are coming but not received yet — verify it later.</span>
                        </span>
                    </label>
                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <button type="button" wire:click="closeModals" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Save payment</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($refundForPaymentId && $refundingPayment)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="refund-dialog-title">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeModals"></div>
            <div class="relative max-h-[92vh] w-full max-w-sm overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-6 flex items-center justify-between">
                    <h3 id="refund-dialog-title" class="text-lg font-semibold text-gray-800 dark:text-white/90">Record refund</h3>
                    <button type="button" wire:click="closeModals" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="mb-5 rounded-xl border border-gray-200 bg-gray-50/60 p-4 text-theme-sm dark:border-gray-800 dark:bg-white/[0.02]">
                    @php
                        $alreadyRefunded = (float) $refundingPayment->refunds->sum('amount');
                        $refundable = max(0, (float) $refundingPayment->amount - $alreadyRefunded);
                    @endphp
                    <div class="flex items-center justify-between gap-3"><span class="text-gray-500 dark:text-gray-400">Payment</span><span class="font-semibold text-gray-800 dark:text-white/90">৳{{ number_format((float) $refundingPayment->amount, 2) }} · {{ $refundingPayment->method ?: 'manual' }}</span></div>
                    @if($alreadyRefunded > 0)
                        <div class="mt-1 flex items-center justify-between gap-3"><span class="text-gray-500 dark:text-gray-400">Already refunded</span><span class="font-semibold text-error-600 dark:text-error-400">৳{{ number_format($alreadyRefunded, 2) }}</span></div>
                    @endif
                    <div class="mt-1 flex items-center justify-between gap-3"><span class="font-medium text-gray-700 dark:text-gray-300">Refundable</span><span class="font-bold text-gray-900 dark:text-white">৳{{ number_format($refundable, 2) }}</span></div>
                </div>

                <form wire:submit="recordRefund" class="space-y-5">
                    <div>
                        <label for="refund-amount" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Amount</label>
                        <input id="refund-amount" wire:model="refundAmount" type="number" step="0.01" min="0.01" max="{{ $refundable }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
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
