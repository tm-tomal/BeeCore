<div>
    @if($viewMode === 'index')
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Tenant portfolio</h1>
            <p class="mt-2 text-sm text-slate-500">Provision and enter ISP workspaces.</p>
        </div>
        <div class="flex items-center gap-4">
            <button wire:click="create" class="bc-primary">
                Add Tenant
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
                            <a href="{{ route('tenant-details', $tenant) }}" class="mr-3 text-teal-300 hover:text-teal-200">View</a>
                            <button wire:click="impersonate({{ $tenant->id }})" class="mr-3 text-violet-400 hover:text-violet-300">Login As</button>
                            <button wire:click="edit({{ $tenant->id }})" class="mr-3 text-cyan-400 hover:text-cyan-300">Edit</button>
                            <button wire:click="delete({{ $tenant->id }})" wire:confirm="Archive this tenant and disable workspace access?" class="text-rose-400 hover:text-rose-300">Archive</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">No tenants found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($tenants->hasPages()) <div class="border-t border-slate-800 p-4">{{ $tenants->links() }}</div> @endif
    </div>

    @else

    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.2em] text-cyan-300">Tenants</p>
            <h1 class="mt-2 text-3xl font-black text-white">{{ $isEditing ? 'Edit Tenant' : 'Add New Tenant' }}</h1>
        </div>
        <button wire:click="cancel" class="bc-secondary">
            Back to List
        </button>
    </header>

    <div class="bc-panel max-w-4xl p-5 sm:p-8" style="border-radius: 8px">
        <form wire:submit="save" class="space-y-6">
            <div class="grid gap-5 md:grid-cols-2">
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
            </div>

            <div class="grid gap-5 border-t border-white/10 pt-5 md:grid-cols-2">
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

            <div class="pt-4 border-t border-slate-800">
                <label class="mb-2 block text-sm font-medium text-slate-300">Timezone</label>
                <input type="text" wire:model="timezone" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-600 focus:border-cyan-400 focus:outline-none" placeholder="Asia/Dhaka">
                @error('timezone') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
            </div>

            <div class="pt-4 border-t border-slate-800">
                <label class="mb-2 block text-sm font-medium text-slate-300">Language</label>
                <select wire:model="language" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none">
                    @foreach($languages as $lang)<option value="{{ $lang->code }}">{{ $lang->name }}</option>@endforeach
                </select>
                @error('language') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
            </div>

            <div class="mt-8 flex justify-end gap-3 pt-4">
                <button type="button" wire:click="cancel" class="bc-secondary">Cancel</button>
                <button type="submit" wire:loading.attr="disabled" class="bc-primary"><span wire:loading.remove wire:target="save">Save tenant</span><span wire:loading wire:target="save">Saving...</span></button>
            </div>
        </form>
    </div>
    @endif
</div>
