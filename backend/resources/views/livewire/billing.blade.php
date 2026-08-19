<div>
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">Billing</p>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Invoices</h1>
            <p class="mt-2 text-sm text-slate-500">Create line-item invoices and monitor outstanding balances.</p>
        </div>
        <div class="flex items-center gap-4">
            <button wire:click="create" class="bc-primary">
                Generate Invoice
            </button>
        </div>
    </header>

    @if (session()->has('message'))
        <div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-emerald-400">
            {{ session('message') }}
        </div>
    @endif

    <div class="bc-table-wrap">
        <table class="bc-table">
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
                        <td class="px-6 py-4 font-medium text-white">
                            {{ number_format($invoice->total, 2) }}
                            @if((float) $invoice->paid_amount > 0)
                                <div class="mt-1 text-xs text-emerald-400">Paid {{ number_format($invoice->paid_amount, 2) }}</div>
                                <div class="text-xs text-amber-400">Due {{ number_format($invoice->outstanding_amount, 2) }}</div>
                            @endif
                        </td>
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
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="invoice-dialog-title">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="bc-panel relative max-h-[92vh] w-full max-w-2xl overflow-y-auto p-5 sm:p-7" style="border-radius: 8px">
                <div class="mb-6 flex items-center justify-between">
                    <h3 id="invoice-dialog-title" class="text-xl font-bold text-white">{{ $isEditing ? 'Edit invoice' : 'Generate invoice' }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">Customer</label>
                            <select wire:model="customer_id" class="bc-field">
                                <option value="">Select a customer...</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})</option>
                                @endforeach
                            </select>
                    </div>
                    @error('customer_id') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">Status</label>
                            <select wire:model="status" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none">
                                <option value="draft">Draft</option>
                                <option value="pending">Pending</option>
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

                    <div class="space-y-3 border-t border-slate-800 pt-4">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-medium text-slate-300">Invoice Items</label>
                            <button type="button" wire:click="addItem" class="text-sm font-semibold text-cyan-400 hover:text-cyan-300">Add item</button>
                        </div>
                        @foreach($items as $index => $item)
                            <div wire:key="invoice-item-{{ $index }}" class="grid gap-2 sm:grid-cols-[1fr_90px_120px_36px]">
                                <input type="text" wire:model="items.{{ $index }}.description" placeholder="Description" class="rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
                                <input type="number" min="0.01" step="0.01" wire:model.live="items.{{ $index }}.quantity" placeholder="Qty" class="rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
                                <input type="number" min="0" step="0.01" wire:model.live="items.{{ $index }}.unit_price" placeholder="Unit price" class="rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
                                <button type="button" wire:click="removeItem({{ $index }})" title="Remove item" class="text-rose-400 hover:text-rose-300">×</button>
                            </div>
                            @error("items.$index.description") <div class="text-xs text-rose-400">{{ $message }}</div> @enderror
                        @endforeach
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div><label class="mb-2 block text-sm text-slate-400">Subtotal</label><div class="rounded-xl bg-slate-950 px-4 py-3 font-semibold text-white">{{ number_format((float) $subtotal, 2) }}</div></div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">Tax</label>
                            <input type="number" step="0.01" wire:model.live.debounce.300ms="tax_amount" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-600 focus:border-cyan-400 focus:outline-none">
                        </div>
                        <div><label class="mb-2 block text-sm font-medium text-cyan-400">Total (BDT)</label><div class="rounded-xl bg-slate-800/50 px-4 py-3 font-bold text-white">{{ number_format((float) $total, 2) }}</div></div>
                    </div>
                    @error('total') <div class="text-xs text-rose-400">{{ $message }}</div> @enderror

                    <div class="mt-8 flex justify-end gap-3 pt-4">
                        <button type="button" wire:click="$set('showModal', false)" class="bc-secondary">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="bc-primary"><span wire:loading.remove wire:target="save">{{ $isEditing ? 'Update invoice' : 'Generate invoice' }}</span><span wire:loading wire:target="save">Saving...</span></button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
