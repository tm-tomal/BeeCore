<div>
    @if($viewMode === 'index')
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">Customers</p>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Customer directory</h1>
            <p class="mt-2 text-sm text-slate-500">Manage service status and recurring package assignments.</p>
        </div>
        <div class="flex items-center gap-4">
            <button wire:click="create" class="bc-primary">
                Add Customer
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
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider">Name</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider">Package</th>
                    <th class="px-6 py-4 text-right font-semibold uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 text-slate-300">
                @forelse($customers as $customer)
                    <tr class="transition-colors hover:bg-slate-800/50">
                        <td class="px-6 py-4 font-medium text-white">{{ $customer->name }}</td>
                        <td class="px-6 py-4">
                            <div>{{ $customer->email }}</div>
                            <div class="text-slate-500">{{ $customer->phone }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($customer->status === 'active')
                                <span class="rounded-full bg-emerald-500/15 px-2.5 py-1 text-xs font-semibold text-emerald-400">Active</span>
                            @elseif($customer->status === 'suspended')
                                <span class="rounded-full bg-amber-500/15 px-2.5 py-1 text-xs font-semibold text-amber-400">Suspended</span>
                            @else
                                <span class="rounded-full bg-rose-500/15 px-2.5 py-1 text-xs font-semibold text-rose-400">Cancelled</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div>{{ $customer->activeSubscription?->package_name ?? $customer->package_name ?? 'N/A' }}</div>
                            @if($customer->activeSubscription)
                                <div class="mt-1 text-xs text-slate-500">{{ ucfirst($customer->activeSubscription->billing_cycle) }} · Next {{ $customer->activeSubscription->next_billing_date->format('M d, Y') }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button wire:click="edit({{ $customer->id }})" class="mr-3 text-cyan-400 hover:text-cyan-300">Edit</button>
                            <button wire:click="delete({{ $customer->id }})" wire:confirm="Are you sure you want to delete this customer?" class="text-rose-400 hover:text-rose-300">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">No customers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($customers->hasPages())
            <div class="border-t border-slate-800 p-4">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    @else

    <!-- Page-wise Create / Edit View -->
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.2em] text-cyan-300">Customers</p>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">{{ $isEditing ? 'Edit customer' : 'Add customer' }}</h1>
        </div>
        <div class="flex items-center gap-4">
            <button wire:click="cancel" class="bc-secondary">
                Back to List
            </button>
        </div>
    </header>

    <div class="bc-panel max-w-4xl p-5 sm:p-8" style="border-radius: 8px">
        <form wire:submit="save" class="space-y-6">
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-300">Name</label>
                <input type="text" wire:model="name" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-600 focus:border-cyan-400 focus:outline-none" placeholder="John Doe">
                @error('name') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-300">Email Address</label>
                    <input type="email" wire:model="email" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-600 focus:border-cyan-400 focus:outline-none" placeholder="john@example.com">
                    @error('email') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-300">Contact Number</label>
                    <input type="text" wire:model="phone" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-600 focus:border-cyan-400 focus:outline-none" placeholder="+8801...">
                    @error('phone') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid gap-5 border-t border-white/10 pt-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-300">Assign Package</label>
                    <select wire:model.live="package_id" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none">
                        <option value="">No recurring package</option>
                        @foreach($packages as $package)
                            <option value="{{ $package->id }}">{{ $package->name }} · {{ number_format($package->price, 2) }} BDT</option>
                        @endforeach
                    </select>
                    @error('package_id') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-300">Current Status</label>
                    <select wire:model="status" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none">
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                    @error('status') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>
            </div>

            @if($package_id)
                <div class="grid gap-5 border border-white/10 bg-black/10 p-5 md:grid-cols-2" style="border-radius: 6px">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">Billing Cycle</label>
                        <select wire:model="billing_cycle" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white">
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="semiannual">Half-yearly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                        @error('billing_cycle') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">Next Billing Date</label>
                        <input type="date" wire:model="next_billing_date" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white">
                        @error('next_billing_date') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>
                </div>
            @endif

            <div class="mt-8 flex justify-end gap-3 pt-4">
                <button type="button" wire:click="cancel" class="bc-secondary">Cancel</button>
                <button type="submit" wire:loading.attr="disabled" class="bc-primary"><span wire:loading.remove wire:target="save">Save customer</span><span wire:loading wire:target="save">Saving...</span></button>
            </div>
        </form>
    </div>
    @endif
</div>
