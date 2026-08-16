<div>
    <header class="mb-8 flex items-center justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.2em] text-cyan-300">Billing</p>
            <h1 class="mt-2 text-3xl font-black text-white">Manage Invoices</h1>
        </div>
        <div class="flex items-center gap-4">
            <button wire:click="create" class="rounded-xl bg-cyan-500 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-cyan-400">
                Generate Invoice
            </button>
        </div>
    </header>

    @if (session()->has('message'))
        <div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-emerald-400">
            {{ session('message') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-800 bg-slate-950/50 text-slate-400">
                <tr>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider">Invoice #</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider">Due Date</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider">Total (৳)</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-right font-semibold uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 text-slate-300">
                @forelse($invoices as $invoice)
                    <tr class="transition-colors hover:bg-slate-800/50">
                        <td class="px-6 py-4 font-medium text-white">{{ $invoice->invoice_number }}</td>
                        <td class="px-6 py-4">
                            @if($invoice->customer)
                                <div>{{ $invoice->customer->name }}</div>
                                <div class="text-xs text-slate-500">{{ $invoice->customer->email }}</div>
                            @else
                                <span class="text-slate-500">Deleted Customer</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-400">
                            {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 font-medium text-white">{{ number_format($invoice->total, 2) }}</td>
                        <td class="px-6 py-4">
                            @if($invoice->status === 'paid')
                                <span class="rounded-full bg-emerald-500/15 px-2.5 py-1 text-xs font-semibold text-emerald-400">Paid</span>
                            @elseif($invoice->status === 'overdue')
                                <span class="rounded-full bg-rose-500/15 px-2.5 py-1 text-xs font-semibold text-rose-400">Overdue</span>
                            @elseif($invoice->status === 'cancelled')
                                <span class="rounded-full bg-slate-500/15 px-2.5 py-1 text-xs font-semibold text-slate-400">Cancelled</span>
                            @else
                                <span class="rounded-full bg-amber-500/15 px-2.5 py-1 text-xs font-semibold text-amber-400">{{ ucfirst($invoice->status) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button wire:click="edit({{ $invoice->id }})" class="mr-3 text-cyan-400 hover:text-cyan-300">Edit</button>
                            <button wire:click="delete({{ $invoice->id }})" wire:confirm="Are you sure you want to delete this invoice?" class="text-rose-400 hover:text-rose-300">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">No invoices found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($invoices->hasPages())
            <div class="border-t border-slate-800 p-4">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

    <!-- Create / Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative w-full max-w-lg rounded-3xl border border-slate-800 bg-slate-900 p-8 shadow-2xl">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-white">{{ $isEditing ? 'Edit Invoice' : 'Generate Invoice' }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">Customer</label>
                        <select wire:model="customer_id" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none">
                            <option value="">Select a customer...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})</option>
                            @endforeach
                        </select>
                        @error('customer_id') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">Status</label>
                            <select wire:model="status" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none">
                                <option value="draft">Draft</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="overdue">Overdue</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            @error('status') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">Due Date</label>
                            <input type="date" wire:model="due_date" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none">
                            @error('due_date') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 border-t border-slate-800 pt-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">Subtotal</label>
                            <input type="number" step="0.01" wire:model.live.debounce.300ms="subtotal" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-600 focus:border-cyan-400 focus:outline-none">
                        </div>
                        
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">Tax</label>
                            <input type="number" step="0.01" wire:model.live.debounce.300ms="tax_amount" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-600 focus:border-cyan-400 focus:outline-none">
                        </div>
                        
                        <div>
                            <label class="mb-2 block text-sm font-medium text-cyan-400">Total (BDT)</label>
                            <input type="number" step="0.01" wire:model="total" readonly class="w-full rounded-xl border border-slate-700 bg-slate-800/50 px-4 py-3 font-bold text-white focus:outline-none">
                        </div>
                    </div>
                    @error('total') <div class="text-xs text-rose-400">{{ $message }}</div> @enderror

                    <div class="mt-8 flex justify-end gap-3 pt-4">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-xl px-4 py-2 font-medium text-slate-400 hover:text-white">Cancel</button>
                        <button type="submit" class="rounded-xl bg-cyan-500 px-6 py-2 font-medium text-slate-950 transition hover:bg-cyan-400">{{ $isEditing ? 'Update Invoice' : 'Generate' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
