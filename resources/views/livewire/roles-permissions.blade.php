<div class="space-y-6">
    @php
        $systemRoles = $roles->where('is_system', true)->count();
        $customRoles = $roles->where('is_system', false)->count();
    @endphp

    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Roles &amp; permissions</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Custom roles alongside the six fixed system roles, with a granular permission catalog.</p>
        </div>
        @if($tab === 'roles')
            <button type="button" wire:click="createRole" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create role
            </button>
        @else
            <button type="button" wire:click="createPermission" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create permission
            </button>
        @endif
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Overview -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $roles->count() }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Roles</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-violet-500/10 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 11 12 14 16 10"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $systemRoles }}<span class="text-base font-medium text-gray-400"> system</span></p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Fixed system roles</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-cyan-500/10 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $customRoles }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Custom roles</p>
            </div>
        </div>
    </section>

    <!-- Tabs -->
    <div class="inline-flex flex-wrap items-center gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <button type="button" wire:click="$set('tab', 'roles')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'roles' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Roles
            <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'roles' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $roles->count() }}</span>
        </button>
        <button type="button" wire:click="$set('tab', 'permissions')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'permissions' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Permission catalog
            <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'permissions' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $permissions->count() }}</span>
        </button>
    </div>

    @if($tab === 'roles')
        <!-- Roles -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Role</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Key</th>
                            <th class="px-5 py-3.5 text-center text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Type</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Permissions</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($roles as $role)
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $role->is_system ? 'bg-violet-500/10 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400' : 'bg-cyan-500/10 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-400' }}">
                                            <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                        </span>
                                        <p class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $role->name }}</p>
                                    </div>
                                </td>
                                <td class="px-5 py-4"><code class="rounded-md bg-brand-50 px-1.5 py-0.5 font-mono text-theme-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ $role->key }}</code></td>
                                <td class="px-5 py-4 text-center">
                                    @if($role->is_system)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-violet-50 px-2.5 py-1 text-theme-xs font-semibold text-violet-600 dark:bg-violet-500/15 dark:text-violet-400"><span class="size-1.5 rounded-full bg-violet-500"></span>System</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-cyan-50 px-2.5 py-1 text-theme-xs font-semibold text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-400"><span class="size-1.5 rounded-full bg-cyan-500"></span>Custom</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4"><span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs font-bold text-gray-700 dark:bg-white/[0.06] dark:text-gray-300">{{ $role->permissions_count }}</span></td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        <button type="button" wire:click="manageRole({{ $role->id }})" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50 px-3 py-2 text-theme-xs font-semibold text-brand-600 transition hover:border-brand-300 hover:bg-brand-100 dark:border-brand-500/25 dark:bg-brand-500/10 dark:text-brand-400">
                                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                            Manage permissions
                                        </button>
                                        @if(!$role->is_system)
                                            <button type="button" wire:click="editRole({{ $role->id }})" title="Edit role" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </button>
                                            <button type="button" wire:click="deleteRole({{ $role->id }})" wire:confirm="Delete this role?" title="Delete role" class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">
                                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-14 text-center text-theme-sm text-gray-500 dark:text-gray-400">No roles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create / edit role modal -->
        @if($viewMode === 'create')
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="cancelRoleForm"></div>
                <div class="relative w-full max-w-md rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                            </span>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $roleId ? 'Edit role' : 'Create role' }}</h2>
                        </div>
                        <button type="button" wire:click="cancelRoleForm" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <form wire:submit="saveRole" class="space-y-5">
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
                        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                            <button type="button" wire:click="cancelRoleForm" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                            <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Save role</button>
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
                    <div class="mb-5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $managingRole->name }} permissions</h2>
                                <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Assign or remove granular capabilities for this role.</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeManageRole" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($permissions as $permission)
                            @php $assigned = $managingRole->permissions->contains('id', $permission->id); @endphp
                            <div class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
                                <div class="min-w-0">
                                    <div class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $permission->name }}</div>
                                    <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-theme-xs text-gray-500 dark:text-gray-400">
                                        <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono dark:bg-white/[0.05]">{{ $permission->key }}</code>
                                        <span class="capitalize">{{ $permission->category }} · {{ $permission->scope }}</span>
                                    </div>
                                </div>
                                <button type="button" wire:click="togglePermission({{ $managingRole->id }}, {{ $permission->id }})" class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-lg border px-3 py-2 text-theme-xs font-semibold transition {{ $assigned ? 'border-error-200 bg-error-50 text-error-600 hover:border-error-300 hover:bg-error-100 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400' : 'border-success-200 bg-success-50 text-success-600 hover:border-success-300 hover:bg-success-100 dark:border-success-500/25 dark:bg-success-500/10 dark:text-success-400' }}">
                                    {{ $assigned ? 'Remove' : 'Assign' }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-5 flex justify-end border-t border-gray-100 pt-5 dark:border-gray-800">
                        <button wire:click="closeManageRole" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Close</button>
                    </div>
                </div>
            </div>
        @endif
    @else
        <!-- Permission catalog -->
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
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-gray-100 text-gray-500 dark:bg-white/[0.05] dark:text-gray-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                        </span>
                                        <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $permission->name }}</p>
                                    </div>
                                </td>
                                <td class="px-5 py-4"><code class="rounded-md bg-brand-50 px-1.5 py-0.5 font-mono text-theme-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ $permission->key }}</code></td>
                                <td class="px-5 py-4"><span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs font-medium capitalize text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">{{ $permission->category }}</span></td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $permission->scope === 'platform' ? 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400' : 'bg-cyan-50 text-cyan-600 dark:bg-cyan-500/10 dark:text-cyan-400' }} capitalize">{{ $permission->scope }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                        </span>
                                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No permissions in the catalog</p>
                                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Create granular capabilities to attach to custom roles.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create permission modal -->
        @if($permissionViewMode === 'create')
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="cancelPermissionForm"></div>
                <div class="relative w-full max-w-md rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </span>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Create permission</h2>
                        </div>
                        <button type="button" wire:click="cancelPermissionForm" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <form wire:submit="savePermission" class="space-y-5">
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
                                <select id="perm-category" wire:model="permissionCategory" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
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
                                <select id="perm-scope" wire:model="permissionScope" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                    <option value="platform">Platform-level</option>
                                    <option value="tenant">Tenant-level</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                            <button type="button" wire:click="cancelPermissionForm" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                            <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Save permission</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endif
</div>
