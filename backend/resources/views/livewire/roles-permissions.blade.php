<div>
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Roles &amp; permissions</h1>
            <p class="mt-2 text-sm text-slate-500">Custom roles alongside the six fixed system roles, with a granular permission catalog.</p>
        </div>
        @if($tab === 'roles')<button wire:click="createRole" class="bc-primary">Create role</button>@else<button wire:click="createPermission" class="bc-primary">Create permission</button>@endif
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <div class="mb-5 flex gap-2">
        <button wire:click="$set('tab', 'roles')" class="px-4 py-2 text-sm font-bold {{ $tab === 'roles' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Roles</button>
        <button wire:click="$set('tab', 'permissions')" class="px-4 py-2 text-sm font-bold {{ $tab === 'permissions' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Permission catalog</button>
    </div>

    @if($tab === 'roles')
        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Role</th><th>Key</th><th>Type</th><th>Permissions</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td class="font-bold text-white">{{ $role->name }}</td>
                            <td><code class="text-teal-300">{{ $role->key }}</code></td>
                            <td>{{ $role->is_system ? 'System' : 'Custom' }}</td>
                            <td>{{ $role->permissions_count }}</td>
                            <td class="text-right">
                                <div class="flex flex-wrap justify-end gap-3">
                                    <button wire:click="manageRole({{ $role->id }})" class="font-semibold text-teal-300">Manage permissions</button>
                                    @if(!$role->is_system)
                                        <button wire:click="editRole({{ $role->id }})" class="font-semibold text-slate-300">Edit</button>
                                        <button wire:click="deleteRole({{ $role->id }})" wire:confirm="Delete this role?" class="font-semibold text-rose-300">Delete</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 text-center text-slate-600">No roles found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($viewMode === 'create')
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-black/70" wire:click="cancelRoleForm"></div>
                <div class="bc-panel relative w-full max-w-md p-6" style="border-radius:8px">
                    <h2 class="text-lg font-bold text-white">{{ $roleId ? 'Edit role' : 'Create role' }}</h2>
                    <form wire:submit="saveRole" class="mt-5 space-y-4">
                        <div><label class="bc-label" for="role-name">Name</label><input id="role-name" wire:model="roleName" class="bc-field">@error('roleName')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div><label class="bc-label" for="role-key">Key</label><input id="role-key" wire:model="roleKey" class="bc-field" placeholder="regional_manager">@error('roleKey')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div><label class="bc-label" for="role-desc">Description</label><textarea id="role-desc" wire:model="roleDescription" rows="2" class="bc-field"></textarea></div>
                        <div class="flex justify-end gap-3"><button type="button" wire:click="cancelRoleForm" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Save role</button></div>
                    </form>
                </div>
            </div>
        @endif

        @if($managingRole)
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-black/70" wire:click="closeManageRole"></div>
                <div class="bc-panel relative max-h-[85vh] w-full max-w-lg overflow-y-auto p-6" style="border-radius:8px">
                    <h2 class="text-lg font-bold text-white">{{ $managingRole->name }} permissions</h2>
                    <ul class="mt-5 space-y-2 text-sm">
                        @foreach($permissions as $permission)
                            @php $assigned = $managingRole->permissions->contains('id', $permission->id); @endphp
                            <li class="flex items-center justify-between border-b border-white/10 pb-2">
                                <div><span class="font-semibold text-slate-200">{{ $permission->name }}</span><div class="text-xs text-slate-600">{{ $permission->category }} · {{ $permission->scope }}</div></div>
                                <button wire:click="togglePermission({{ $managingRole->id }}, {{ $permission->id }})" class="font-semibold {{ $assigned ? 'text-rose-300' : 'text-emerald-300' }}">{{ $assigned ? 'Remove' : 'Assign' }}</button>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-5 flex justify-end"><button wire:click="closeManageRole" class="bc-secondary">Close</button></div>
                </div>
            </div>
        @endif
    @else
        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Permission</th><th>Key</th><th>Category</th><th>Scope</th></tr></thead>
                <tbody>
                    @forelse($permissions as $permission)
                        <tr>
                            <td class="font-semibold text-white">{{ $permission->name }}</td>
                            <td><code class="text-teal-300">{{ $permission->key }}</code></td>
                            <td class="capitalize">{{ $permission->category }}</td>
                            <td class="capitalize">{{ $permission->scope }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-12 text-center text-slate-600">No permissions in the catalog.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($permissionViewMode === 'create')
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-black/70" wire:click="cancelPermissionForm"></div>
                <div class="bc-panel relative w-full max-w-md p-6" style="border-radius:8px">
                    <h2 class="text-lg font-bold text-white">Create permission</h2>
                    <form wire:submit="savePermission" class="mt-5 space-y-4">
                        <div><label class="bc-label" for="perm-name">Name</label><input id="perm-name" wire:model="permissionName" class="bc-field">@error('permissionName')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div><label class="bc-label" for="perm-key">Key</label><input id="perm-key" wire:model="permissionKey" class="bc-field" placeholder="module.action">@error('permissionKey')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div><label class="bc-label" for="perm-category">Category</label><select id="perm-category" wire:model="permissionCategory" class="bc-field"><option value="financial">Financial</option><option value="network">Network</option><option value="security">Security</option><option value="audit">Audit</option><option value="tenant">Tenant</option><option value="other">Other</option></select></div>
                            <div><label class="bc-label" for="perm-scope">Scope</label><select id="perm-scope" wire:model="permissionScope" class="bc-field"><option value="platform">Platform-level</option><option value="tenant">Tenant-level</option></select></div>
                        </div>
                        <div class="flex justify-end gap-3"><button type="button" wire:click="cancelPermissionForm" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Save permission</button></div>
                    </form>
                </div>
            </div>
        @endif
    @endif
</div>
