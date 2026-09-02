<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Roles &amp; permissions</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Custom roles alongside the six fixed system roles, with a granular permission catalog.</p>
        </div>
        @if($tab === 'roles')
            <button wire:click="createRole" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Create role</button>
        @else
            <button wire:click="createPermission" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Create permission</button>
        @endif
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Tabs -->
    <div class="inline-flex flex-wrap items-center gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <button wire:click="$set('tab', 'roles')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'roles' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">Roles</button>
        <button wire:click="$set('tab', 'permissions')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'permissions' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">Permission catalog</button>
    </div>

    @if($tab === 'roles')
        <!-- Roles -->
        <div class="space-y-4">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Role</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Key</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Type</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Permissions</th>
                                <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($roles as $role)
                                <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-4 text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $role->name }}</td>
                                    <td class="px-5 py-4"><code class="rounded-md bg-brand-50 px-1.5 py-0.5 font-mono text-theme-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ $role->key }}</code></td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $role->is_system ? 'System' : 'Custom' }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $role->permissions_count }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap items-center justify-end gap-1">
                                            <button wire:click="manageRole({{ $role->id }})" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-theme-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">Manage permissions</button>
                                            @if(!$role->is_system)
                                                <button wire:click="editRole({{ $role->id }})" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-theme-sm font-medium text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.03]">Edit</button>
                                                <button wire:click="deleteRole({{ $role->id }})" wire:confirm="Delete this role?" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-theme-sm font-medium text-error-600 transition hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10">Delete</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-12 text-center text-theme-sm text-gray-500 dark:text-gray-400">No roles found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create / edit role modal -->
        @if($viewMode === 'create')
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="cancelRoleForm"></div>
                <div class="relative w-full max-w-md rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $roleId ? 'Edit role' : 'Create role' }}</h2>
                    <form wire:submit="saveRole" class="mt-5 space-y-5">
                        <div>
                            <label for="role-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Name</label>
                            <input id="role-name" wire:model="roleName" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            @error('roleName') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="role-key" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Key</label>
                            <input id="role-key" wire:model="roleKey" type="text" placeholder="regional_manager" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            @error('roleKey') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="role-desc" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Description</label>
                            <textarea id="role-desc" wire:model="roleDescription" rows="2" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                        </div>
                        <div class="flex justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                            <button type="button" wire:click="cancelRoleForm" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                            <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Save role</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Manage role permissions modal -->
        @if($managingRole)
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeManageRole"></div>
                <div class="relative max-h-[85vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $managingRole->name }} permissions</h2>
                    <ul class="mt-5 divide-y divide-gray-100 text-theme-sm dark:divide-gray-800">
                        @foreach($permissions as $permission)
                            @php $assigned = $managingRole->permissions->contains('id', $permission->id); @endphp
                            <li class="flex items-center justify-between gap-4 py-3">
                                <div class="min-w-0">
                                    <div class="font-semibold text-gray-800 dark:text-white/90">{{ $permission->name }}</div>
                                    <div class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $permission->category }} · {{ $permission->scope }}</div>
                                </div>
                                <button wire:click="togglePermission({{ $managingRole->id }}, {{ $permission->id }})" class="shrink-0 rounded-lg px-2.5 py-1.5 text-theme-sm font-medium transition {{ $assigned ? 'text-error-600 hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10' : 'text-success-600 hover:bg-success-50 dark:text-success-500 dark:hover:bg-success-500/10' }}">{{ $assigned ? 'Remove' : 'Assign' }}</button>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-5 flex justify-end border-t border-gray-100 pt-5 dark:border-gray-800">
                        <button wire:click="closeManageRole" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Close</button>
                    </div>
                </div>
            </div>
        @endif
    @else
        <!-- Permission catalog -->
        <div class="space-y-4">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Permission</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Key</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Category</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Scope</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($permissions as $permission)
                                <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $permission->name }}</td>
                                    <td class="px-5 py-4"><code class="rounded-md bg-brand-50 px-1.5 py-0.5 font-mono text-theme-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ $permission->key }}</code></td>
                                    <td class="px-5 py-4 text-theme-sm capitalize text-gray-600 dark:text-gray-400">{{ $permission->category }}</td>
                                    <td class="px-5 py-4 text-theme-sm capitalize text-gray-600 dark:text-gray-400">{{ $permission->scope }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-12 text-center text-theme-sm text-gray-500 dark:text-gray-400">No permissions in the catalog.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create permission modal -->
        @if($permissionViewMode === 'create')
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="cancelPermissionForm"></div>
                <div class="relative w-full max-w-md rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Create permission</h2>
                    <form wire:submit="savePermission" class="mt-5 space-y-5">
                        <div>
                            <label for="perm-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Name</label>
                            <input id="perm-name" wire:model="permissionName" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            @error('permissionName') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="perm-key" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Key</label>
                            <input id="perm-key" wire:model="permissionKey" type="text" placeholder="module.action" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            @error('permissionKey') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="perm-category" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Category</label>
                                <select id="perm-category" wire:model="permissionCategory" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                    <option value="financial">Financial</option>
                                    <option value="network">Network</option>
                                    <option value="security">Security</option>
                                    <option value="audit">Audit</option>
                                    <option value="tenant">Tenant</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label for="perm-scope" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Scope</label>
                                <select id="perm-scope" wire:model="permissionScope" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                    <option value="platform">Platform-level</option>
                                    <option value="tenant">Tenant-level</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                            <button type="button" wire:click="cancelPermissionForm" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                            <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Save permission</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endif
</div>
