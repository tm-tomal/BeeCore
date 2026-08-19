<div>
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p><h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Platform users</h1><p class="mt-2 text-sm text-slate-500">Provision administrators and tenant staff with explicit workspace roles.</p></div>
        <button wire:click="create" class="bc-primary">Add user</button>
    </header>

    @if(session()->has('message'))<div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius: 6px">{{ session('message') }}</div>@endif

    <div class="mb-4 max-w-md"><label for="user-search" class="bc-label">Search users</label><input id="user-search" wire:model.live.debounce.300ms="search" type="search" class="bc-field" placeholder="Name or email"></div>

    <div class="bc-table-wrap">
        <table class="bc-table"><thead><tr><th>User</th><th>Role</th><th>Workspace</th><th>Status</th><th>Created</th><th class="text-right">Actions</th></tr></thead><tbody>
            @forelse($users as $user)
                <tr><td><div class="font-bold text-white">{{ $user->name }}</div><div class="text-xs text-slate-600">{{ $user->email }}</div></td><td class="capitalize">{{ str_replace('_', ' ', $user->role) }}</td><td>{{ $user->tenant?->name ?? 'BeeCore platform' }}</td><td class="capitalize {{ $user->status === 'active' ? 'text-emerald-300' : 'text-amber-300' }}">{{ $user->status }}</td><td>{{ $user->created_at->format('d M Y') }}</td><td><div class="flex justify-end gap-3"><button wire:click="edit({{ $user->id }})" class="font-semibold text-teal-300">Edit</button>@if($user->id !== auth()->id() && $user->status === 'active')<button wire:click="delete({{ $user->id }})" wire:confirm="Deactivate this user account?" class="font-semibold text-rose-300">Deactivate</button>@endif</div></td></tr>
            @empty<tr><td colspan="6" class="py-12 text-center text-slate-600">No matching users.</td></tr>@endforelse
        </tbody></table>
        @if($users->hasPages())<div class="border-t border-white/10 p-4">{{ $users->links() }}</div>@endif
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="user-dialog-title">
            <div class="fixed inset-0 bg-black/70" wire:click="$set('showModal', false)"></div>
            <div class="bc-panel relative max-h-[92vh] w-full max-w-xl overflow-y-auto p-5 sm:p-7" style="border-radius: 8px">
                <h2 id="user-dialog-title" class="text-xl font-bold text-white">{{ $userId ? 'Edit user' : 'Add user' }}</h2>
                <form wire:submit="save" class="mt-6 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2"><div><label for="user-name" class="bc-label">Full name</label><input id="user-name" wire:model="name" class="bc-field">@error('name')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror</div><div><label for="user-email" class="bc-label">Email</label><input id="user-email" wire:model="email" type="email" class="bc-field">@error('email')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror</div></div>
                    <div><label for="user-password" class="bc-label">{{ $userId ? 'New password (optional)' : 'Temporary password' }}</label><input id="user-password" wire:model="password" type="password" autocomplete="new-password" class="bc-field">@error('password')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div class="grid gap-4 sm:grid-cols-3"><div><label for="user-role" class="bc-label">Role</label><select id="user-role" wire:model.live="role" class="bc-field"><option value="super_admin">Super Admin</option><option value="tenant_admin">Tenant Admin</option><option value="finance">Finance</option><option value="support">Support</option><option value="network_engineer">Network Engineer</option><option value="reseller">Reseller</option></select></div><div><label for="user-tenant" class="bc-label">Tenant workspace</label><select id="user-tenant" wire:model="tenantId" class="bc-field" @disabled($role === 'super_admin')><option value="">{{ $role === 'super_admin' ? 'Platform access' : 'Select tenant' }}</option>@foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach</select>@error('tenantId')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror</div><div><label for="user-status" class="bc-label">Status</label><select id="user-status" wire:model="status" class="bc-field"><option value="active">Active</option><option value="inactive">Inactive</option></select></div></div>
                    <div class="flex justify-end gap-2 pt-3"><button type="button" wire:click="$set('showModal', false)" class="bc-secondary">Cancel</button><button type="submit" wire:loading.attr="disabled" class="bc-primary"><span wire:loading.remove wire:target="save">Save user</span><span wire:loading wire:target="save">Saving...</span></button></div>
                </form>
            </div>
        </div>
    @endif
</div>