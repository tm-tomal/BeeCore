<div>
    <header class="mb-8 flex items-center justify-between">
        <div><p class="text-sm uppercase tracking-[0.2em] text-cyan-300">Payments</p><h1 class="mt-2 text-3xl font-black text-white">Manage Payments</h1></div>
        <button wire:click="create" class="rounded-xl bg-cyan-500 px-4 py-2 font-semibold text-slate-950 transition hover:bg-cyan-400">Record Payment</button>
    </header>
    @if (session()->has('message'))<div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-emerald-400">{{ session('message') }}</div>@endif
    <div class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900">
        <table class="w-full text-left text-sm"><thead class="border-b border-slate-800 bg-slate-950/50 text-slate-400"><tr>
            <th class="px-6 py-4 font-semibold">Customer</th><th class="px-6 py-4 font-semibold">Amount (BDT)</th><th class="px-6 py-4 font-semibold">Method</th><th class="px-6 py-4 font-semibold">Date</th>
        </tr></thead><tbody class="divide-y divide-slate-800 text-slate-300">
            @forelse($payments as $payment)
            <tr class="hover:bg-slate-800/50">
                <td class="px-6 py-4 font-medium text-white">{{ $payment->customer->name ?? 'N/A' }}</td>
                <td class="px-6 py-4 font-bold text-emerald-400">{{ number_format($payment->amount, 2) }}</td>
                <td class="px-6 py-4"><span class="capitalize">{{ $payment->payment_method }}</span><div class="text-xs text-slate-500">{{ $payment->transaction_id }}</div></td>
                <td class="px-6 py-4">{{ $payment->payment_date->format('M d, Y h:i A') }}</td>
            </tr>
            @empty <tr><td colspan="4" class="p-6 text-center text-slate-500">No payments found.</td></tr> @endforelse
        </tbody></table>
        <div class="p-4">{{ $payments->links() }}</div>
    </div>
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
        <div class="relative w-full max-w-lg rounded-3xl border border-slate-800 bg-slate-900 p-8 shadow-2xl">
            <h3 class="mb-6 text-2xl font-bold text-white">Record Payment</h3>
            <form wire:submit="save" class="space-y-4">
                <div><label class="mb-2 block text-sm text-slate-300">Customer</label>
                    <select wire:model="customer_id" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-white">
                        <option value="">Select customer...</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="mb-2 block text-sm text-slate-300">Amount (BDT)</label><input type="number" step="0.01" wire:model="amount" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-white"></div>
                <div><label class="mb-2 block text-sm text-slate-300">Method</label>
                    <select wire:model="payment_method" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-white"><option value="cash">Cash</option><option value="bkash">bKash</option><option value="card">Card / Bank</option></select>
                </div>
                <div class="flex justify-end gap-3 pt-4"><button type="submit" class="rounded-xl bg-cyan-500 px-6 py-2 font-medium text-slate-950">Save Payment</button></div>
            </form>
        </div>
    </div>
    @endif
</div>
