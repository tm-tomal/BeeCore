<div>
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">Service catalog</p>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Packages & IP plans</h1>
            <p class="mt-2 text-sm text-slate-500">Define recurring package prices and connection profiles.</p>
        </div>
        <div class="flex items-center gap-4">
            <button wire:click="create" class="bc-primary">
                Add Package
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
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider">Plan Name</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider">Bandwidth</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider">Type</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider">Price (৳)</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-right font-semibold uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 text-slate-300">
                @forelse($packages as $package)
                    <tr class="transition-colors hover:bg-slate-800/50">
                        <td class="px-6 py-4 font-medium text-white">{{ $package->name }}</td>
                        <td class="px-6 py-4 text-slate-400">{{ $package->bandwidth ?: 'N/A' }}</td>
                        <td class="px-6 py-4">
                            @if($package->type === 'dedicated_ip')
                                <span class="rounded bg-violet-500/15 px-2 py-1 text-xs font-semibold text-violet-400">Dedicated IP</span>
                            @else
                                <span class="rounded bg-slate-800 px-2 py-1 text-xs font-semibold text-slate-300">Shared/PPPoE</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-medium text-white">{{ number_format($package->price, 2) }}</td>
                        <td class="px-6 py-4">
                            @if($package->is_active)
                                <span class="rounded-full bg-emerald-500/15 px-2.5 py-1 text-xs font-semibold text-emerald-400">Active</span>
                            @else
                                <span class="rounded-full bg-rose-500/15 px-2.5 py-1 text-xs font-semibold text-rose-400">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button wire:click="edit({{ $package->id }})" class="mr-3 text-cyan-400 hover:text-cyan-300">Edit</button>
                            <button wire:click="delete({{ $package->id }})" wire:confirm="Are you sure you want to delete this plan?" class="text-rose-400 hover:text-rose-300">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">No packages created yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($packages->hasPages())
            <div class="border-t border-slate-800 p-4">
                {{ $packages->links() }}
            </div>
        @endif
    </div>

    <!-- Create / Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="package-dialog-title">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="bc-panel relative max-h-[92vh] w-full max-w-lg overflow-y-auto p-5 sm:p-7" style="border-radius: 8px">
                <div class="mb-6 flex items-center justify-between">
                    <h3 id="package-dialog-title" class="text-xl font-bold text-white">{{ $isEditing ? 'Edit package' : 'Create package' }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">Package Name</label>
                        <input type="text" wire:model="name" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-600 focus:border-cyan-400 focus:outline-none" placeholder="10 Mbps Ultimate">
                        @error('name') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">Price (BDT)</label>
                            <input type="number" step="0.01" wire:model="price" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-600 focus:border-cyan-400 focus:outline-none" placeholder="1000.00">
                            @error('price') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">Bandwidth</label>
                            <input type="text" wire:model="bandwidth" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-600 focus:border-cyan-400 focus:outline-none" placeholder="10 Mbps">
                            @error('bandwidth') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">Connection Type</label>
                            <select wire:model="type" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none">
                                <option value="shared">Shared / PPPoE</option>
                                <option value="dedicated_ip">Dedicated IP</option>
                            </select>
                            @error('type') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">Status</label>
                            <div class="mt-3 flex items-center">
                                <input type="checkbox" wire:model="is_active" id="is_active" class="h-5 w-5 rounded border-slate-600 bg-slate-950 text-cyan-500 focus:ring-cyan-500">
                                <label for="is_active" class="ml-2 text-sm text-slate-300">Active Package</label>
                            </div>
                            @error('is_active') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3 pt-4">
                        <button type="button" wire:click="$set('showModal', false)" class="bc-secondary">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="bc-primary"><span wire:loading.remove wire:target="save">Save package</span><span wire:loading wire:target="save">Saving...</span></button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
