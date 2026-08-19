<div>
    <header class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
        <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">SaaS payments</h1>
        <p class="mt-2 text-sm text-slate-500">Record manual payments, verify pending transactions, and search transaction history across every tenant.</p>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <section class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Completed</p><p class="mt-2 text-xl font-black text-emerald-300">৳{{ number_format($report['completed'], 2) }}</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Pending verification</p><p class="mt-2 text-xl font-black text-amber-300">৳{{ number_format($report['pending'], 2) }}</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Failed payments</p><p class="mt-2 text-xl font-black text-rose-300">{{ $report['failed'] }}</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Refunded</p><p class="mt-2 text-xl font-black text-slate-200">৳{{ number_format($report['refunded'], 2) }}</p></div>
    </section>

    <div class="mb-5 border border-white/10 bg-white/[0.02] p-4" style="border-radius:6px">
        <div class="mb-3 flex items-center justify-between"><h2 class="text-sm font-bold text-white">Record a manual payment</h2></div>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($unpaidInvoices as $invoice)
                <div class="flex items-center justify-between border border-white/10 p-3 text-sm" style="border-radius:6px">
                    <div><div class="font-semibold text-slate-200">{{ $invoice->tenant->name }}</div><div class="text-xs text-slate-600">{{ $invoice->invoice_number }} · ৳{{ number_format($invoice->amount, 2) }}</div></div>
                    <button wire:click="openRecordPayment({{ $invoice->id }})" class="font-semibold text-teal-300">Record</button>
                </div>
            @empty
                <p class="text-sm text-slate-600">No pending or overdue invoices right now.</p>
            @endforelse
        </div>
    </div>

    <div class="mb-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div><label class="bc-label" for="pay-search">Search</label><input id="pay-search" wire:model.live.debounce.300ms="search" class="bc-field" placeholder="Tenant, invoice, reference"></div>
        <div><label class="bc-label" for="pay-status">Status</label><select id="pay-status" wire:model.live="statusFilter" class="bc-field"><option value="">All</option><option value="pending">Pending</option><option value="completed">Completed</option><option value="failed">Failed</option></select></div>
        <div><label class="bc-label" for="pay-method">Method</label><select id="pay-method" wire:model.live="methodFilter" class="bc-field"><option value="">All methods</option>@foreach($methods as $method)<option value="{{ $method }}">{{ ucfirst($method) }}</option>@endforeach</select></div>
        <div class="grid grid-cols-2 gap-2"><div><label class="bc-label" for="pay-from">From</label><input id="pay-from" wire:model.live="dateFrom" type="date" class="bc-field"></div><div><label class="bc-label" for="pay-to">To</label><input id="pay-to" wire:model.live="dateTo" type="date" class="bc-field"></div></div>
    </div>

    <div class="bc-table-wrap">
        <table class="bc-table">
            <thead><tr><th>Tenant</th><th>Invoice</th><th>Amount</th><th>Method</th><th>Reference</th><th>Status</th><th>Paid at</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td><a href="{{ route('tenant-details', $payment->tenant) }}" class="font-semibold text-teal-300">{{ $payment->tenant->name }}</a></td>
                        <td><code class="text-slate-400">{{ $payment->invoice?->invoice_number ?? '—' }}</code></td>
                        <td>৳{{ number_format($payment->amount, 2) }}@if($payment->refunds->isNotEmpty())<div class="text-[10px] text-rose-300">Refunded ৳{{ number_format($payment->refunds->sum('amount'), 2) }}</div>@endif</td>
                        <td class="capitalize">{{ $payment->method }}</td>
                        <td>{{ $payment->reference ?? '—' }}</td>
                        <td><span class="capitalize font-semibold {{ match($payment->status) { 'completed' => 'text-emerald-300', 'failed' => 'text-rose-300', default => 'text-amber-300' } }}">{{ $payment->status }}</span></td>
                        <td>{{ $payment->paid_at?->format('d M Y, H:i') }}</td>
                        <td class="text-right">
                            @if($payment->status === 'pending')
                                <div class="flex justify-end gap-3">
                                    <button wire:click="verifyPayment({{ $payment->id }})" wire:confirm="Verify this payment?" class="font-semibold text-teal-300">Verify</button>
                                    <button wire:click="markFailed({{ $payment->id }})" wire:confirm="Mark this payment as failed?" class="font-semibold text-rose-300">Mark failed</button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-12 text-center text-slate-600">No payments match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($payments->hasPages())<div class="border-t border-white/10 p-4">{{ $payments->links() }}</div>@endif
    </div>

    @if($recordForInvoiceId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="closeModals"></div>
            <div class="bc-panel relative w-full max-w-md p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">Record payment</h2>
                <form wire:submit="recordPayment" class="mt-5 space-y-4">
                    <div><label class="bc-label" for="rec-amount">Amount</label><input id="rec-amount" wire:model="recordAmount" type="number" step="0.01" min="0.01" class="bc-field">@error('recordAmount')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="rec-method">Method</label><select id="rec-method" wire:model="recordMethod" class="bc-field"><option value="manual">Manual</option><option value="bank_transfer">Bank transfer</option><option value="mobile_banking">Mobile banking</option><option value="card">Card</option></select></div>
                    <div><label class="bc-label" for="rec-reference">Reference (optional)</label><input id="rec-reference" wire:model="recordReference" class="bc-field"></div>
                    <label class="inline-flex items-center gap-3 text-sm text-slate-300"><input wire:model="recordAsPending" type="checkbox">Record as pending (needs verification before it settles the invoice)</label>
                    <div class="flex justify-end gap-3"><button type="button" wire:click="closeModals" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Save payment</button></div>
                </form>
            </div>
        </div>
    @endif
</div>
