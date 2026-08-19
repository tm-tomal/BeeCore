<div>
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">Collections</p><h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Payments</h1><p class="mt-2 text-sm text-slate-500">Allocate verified collections to open invoices.</p></div>
        <button wire:click="create" class="bc-primary">Record payment</button>
    </header>
    @if (session()->has('message'))<div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-emerald-400">{{ session('message') }}</div>@endif
    <div class="bc-table-wrap">
        <table class="bc-table"><thead><tr>
            <th class="px-6 py-4 font-semibold">Invoice / Customer</th><th class="px-6 py-4 font-semibold">Amount (BDT)</th><th class="px-6 py-4 font-semibold">Method</th><th class="px-6 py-4 font-semibold">Date</th>
        </tr></thead><tbody class="divide-y divide-slate-800 text-slate-300">
            @forelse($payments as $payment)
            <tr class="hover:bg-slate-800/50">
                <td class="px-6 py-4 font-medium text-white"><div>{{ $payment->invoice->invoice_number ?? 'N/A' }}</div><div class="text-xs text-slate-500">{{ $payment->customer->name ?? 'N/A' }}</div></td>
                <td class="px-6 py-4 font-bold text-emerald-400">{{ number_format($payment->amount, 2) }}</td>
                <td class="px-6 py-4"><span class="capitalize">{{ $payment->payment_method }}</span><div class="text-xs text-slate-500">{{ $payment->transaction_id }}</div></td>
                <td class="px-6 py-4">{{ $payment->payment_date->format('M d, Y h:i A') }}</td>
            </tr>
            @empty <tr><td colspan="4" class="p-6 text-center text-slate-500">No payments found.</td></tr> @endforelse
        </tbody></table>
        <div class="p-4">{{ $payments->links() }}</div>
    </div>
    @if($showModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="payment-dialog-title">
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
        <div class="bc-panel relative max-h-[92vh] w-full max-w-lg overflow-y-auto p-5 sm:p-7" style="border-radius: 8px">
            <h3 id="payment-dialog-title" class="mb-6 text-xl font-bold text-white">Record payment</h3>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="mb-2 block text-sm text-slate-300">Pending Invoice</label>
                        <select wire:model="invoice_id" class="bc-field">
                            <option value="">Select invoice...</option>@foreach($invoices as $invoice)<option value="{{ $invoice->id }}">{{ $invoice->invoice_number }} · {{ $invoice->customer->name }} · Due {{ number_format($invoice->outstanding_amount, 2) }}</option>@endforeach
                        </select>
                </div>
                @error('invoice_id') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror

                <div><label class="mb-2 block text-sm text-slate-300">Amount (BDT)</label><input type="number" step="0.01" wire:model="amount" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-white"></div>
                <div><label class="mb-2 block text-sm text-slate-300">Method</label>
                    <select wire:model="payment_method" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-white"><option value="cash">Cash</option><option value="bkash">bKash</option><option value="card">Card / Bank</option></select>
                </div>
                <div><label class="mb-2 block text-sm text-slate-300">Transaction ID</label><input type="text" wire:model="transaction_id" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-white"><div class="mt-1 text-xs text-slate-500">Required for gateway reconciliation; optional for cash.</div>@error('transaction_id') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror</div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" wire:click="$set('showModal', false)" class="bc-secondary">Cancel</button>
                    <button type="submit" wire:loading.attr="disabled" class="bc-primary"><span wire:loading.remove wire:target="save">Save payment</span><span wire:loading wire:target="save">Saving...</span></button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
