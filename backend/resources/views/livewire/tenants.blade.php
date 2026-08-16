<div>
    <header class="mb-8 flex items-center justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.2em] text-cyan-300">Tenants</p>
            <h1 class="mt-2 text-3xl font-black text-white">Manage Tenants (ISPs)</h1>
        </div>
        <div class="flex items-center gap-4">
            <button wire:click="create" class="rounded-xl bg-cyan-500 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-cyan-400">
                Add Tenant
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
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider">Name / Slug</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider">Currency</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider">Timezone</th>
                    <th class="px-6 py-4 text-right font-semibold uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 text-slate-300">
                @forelse($tenants as $tenant)
                    <tr class="transition-colors hover:bg-slate-800/50">
                        <td class="px-6 py-4 font-medium text-white">
                            <div>{{ $tenant->name }}</div>
                            <div class="text-xs text-slate-500">{{ $tenant->slug }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($tenant->status === 'active')
                                <span class="rounded-full bg-emerald-500/15 px-2.5 py-1 text-xs font-semibold text-emerald-400">Active</span>
                            @else
                                <span class="rounded-full bg-amber-500/15 px-2.5 py-1 text-xs font-semibold text-amber-400">Suspended</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="rounded bg-slate-800 px-2 py-1 text-slate-300">{{ $tenant->currency }}</span>
                        </td>
                        <td class="px-6 py-4">{{ $tenant->timezone }}</td>
                        <td class="px-6 py-4 text-right">
                            <button wire:click="edit({{ $tenant->id }})" class="mr-3 text-cyan-400 hover:text-cyan-300">Edit</button>
                            <button wire:click="delete({{ $tenant->id }})" wire:confirm="Are you sure you want to delete this tenant?" class="text-rose-400 hover:text-rose-300">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">No tenants found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($tenants->hasPages())
            <div class="border-t border-slate-800 p-4">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>

    <!-- Create / Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative w-full max-w-lg rounded-3xl border border-slate-800 bg-slate-900 p-8 shadow-2xl">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-white">{{ $isEditing ? 'Edit Tenant' : 'Add Tenant' }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">ISP Name</label>
                        <input type="text" wire:model.live="name" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-600 focus:border-cyan-400 focus:outline-none" placeholder="Acme Network">
                        @error('name') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">Subdomain Slug</label>
                        <input type="text" wire:model="slug" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-600 focus:border-cyan-400 focus:outline-none" placeholder="acme-network">
                        @error('slug') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">Status</label>
                            <select wire:model="status" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none">
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                            </select>
                            @error('status') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-300">Currency</label>
                            <input type="text" wire:model="currency" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-600 focus:border-cyan-400 focus:outline-none" placeholder="BDT">
                            @error('currency') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">Timezone</label>
                        <input type="text" wire:model="timezone" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-600 focus:border-cyan-400 focus:outline-none" placeholder="Asia/Dhaka">
                        @error('timezone') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-8 flex justify-end gap-3 pt-4">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-xl px-4 py-2 font-medium text-slate-400 hover:text-white">Cancel</button>
                        <button type="submit" class="rounded-xl bg-cyan-500 px-6 py-2 font-medium text-slate-950 transition hover:bg-cyan-400">Save Tenant</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
