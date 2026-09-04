<div class="space-y-6">
    @php
        $roleChip = fn (string $role): string => match ($role) {
            'super_admin' => 'bg-violet-50 text-violet-600 ring-1 ring-inset ring-violet-100 dark:bg-violet-500/10 dark:text-violet-400 dark:ring-violet-500/25',
            'tenant_admin' => 'bg-brand-50 text-brand-600 ring-1 ring-inset ring-brand-100 dark:bg-brand-500/10 dark:text-brand-400 dark:ring-brand-500/25',
            'finance' => 'bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/25',
            'support' => 'bg-sky-50 text-sky-600 ring-1 ring-inset ring-sky-100 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-500/25',
            'network_engineer' => 'bg-amber-50 text-amber-600 ring-1 ring-inset ring-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/25',
            'reseller' => 'bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-200 dark:bg-white/[0.05] dark:text-gray-400 dark:ring-gray-700',
            default => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
        };
    @endphp

    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Platform users</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Provision administrators and tenant staff with explicit workspace roles.</p>
        </div>
        <button type="button" wire:click="create" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/></svg>
            Add user
        </button>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <x-plan-error-banner />

    <!-- Search -->
    <div class="rounded-2xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative w-full max-w-md">
                <svg class="pointer-events-none absolute inset-y-0 left-0 ml-4 size-5 self-center stroke-current text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input id="user-search" wire:model.live.debounce.300ms="search" type="search" placeholder="Search by name or email..." class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-11 pr-4 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
            </div>
            <p class="text-theme-xs text-gray-400 dark:text-gray-500">{{ $users->total() }} user{{ $users->total() === 1 ? '' : 's' }}</p>
        </div>
    </div>

    <!-- Users table -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="w-full overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">User</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Role</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Workspace</th>
                        <th class="px-5 py-3.5 text-center text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Created</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($users as $user)
                        <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="grid size-9 shrink-0 place-items-center rounded-full {{ $user->status === 'active' ? 'bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.05] dark:text-gray-400' }} text-theme-sm font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $user->name }}</p>
                                        <p class="mt-0.5 truncate text-theme-xs text-gray-400 dark:text-gray-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $roleChip($user->role) }}">{{ str_replace('_', ' ', $user->role) }}</span></td>
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $user->tenant?->name ?? 'BeeCore platform' }}</td>
                            <td class="px-5 py-4 text-center">
                                @if($user->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-500"><span class="size-1.5 rounded-full bg-success-500"></span>Active</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-warning-50 px-2.5 py-1 text-theme-xs font-semibold text-warning-600 dark:bg-warning-500/15 dark:text-warning-500"><span class="size-1.5 rounded-full bg-warning-500"></span>Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right text-theme-xs text-gray-500 dark:text-gray-400">{{ $user->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($user->tenant_id && $user->status === 'active')
                                        <button type="button" wire:click="impersonateTenant({{ $user->id }})" title="Login as {{ $user->tenant?->name ?? 'tenant' }} workspace" class="inline-flex h-8 items-center justify-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50 px-2.5 text-theme-xs font-semibold text-brand-600 transition hover:border-brand-300 hover:bg-brand-100 dark:border-brand-500/25 dark:bg-brand-500/10 dark:text-brand-400">
                                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                                            Login as workspace
                                        </button>
                                    @endif
                                    <button type="button" wire:click="edit({{ $user->id }})" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    @if($user->id !== auth()->id() && $user->status === 'active')
                                        <button type="button" wire:click="delete({{ $user->id }})" wire:confirm="Deactivate this user account?" title="Deactivate" class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-14 text-center">
                                <div class="mx-auto max-w-xs">
                                    <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                        <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    </span>
                                    <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No matching users</p>
                                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Try a different name or email, or add a new user.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())<div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $users->links() }}</div>@endif
    </div>

    <!-- Add / Edit user modal -->
    @if($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="user-dialog-title">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative max-h-[92vh] w-full max-w-xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl dark:border-gray-800 dark:bg-gray-900 sm:p-7">
                <div class="mb-6 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                            <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/></svg>
                        </span>
                        <div>
                            <h2 id="user-dialog-title" class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $userId ? 'Edit user' : 'Add user' }}</h2>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $userId ? 'Update this user account details.' : 'Provision a new account with a role and workspace.' }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="$set('showModal', false)" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form wire:submit="save" class="space-y-5">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="user-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Full name</label>
                            <input id="user-name" wire:model="name" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            @error('name') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="user-email" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Email</label>
                            <input id="user-email" wire:model="email" type="email" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            @error('email') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label for="user-password" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ $userId ? 'New password (optional)' : 'Temporary password' }}</label>
                        <input id="user-password" wire:model="password" type="password" autocomplete="new-password" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        @error('password') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-5 sm:grid-cols-3">
                        <div>
                            <label for="user-role" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Role</label>
                            <select id="user-role" wire:model.live="role" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                <option value="super_admin">Super Admin</option>
                                <option value="tenant_admin">Tenant Admin</option>
                                <option value="finance">Finance</option>
                                <option value="support">Support</option>
                                <option value="network_engineer">Network Engineer</option>
                                <option value="reseller">Reseller</option>
                            </select>
                        </div>
                        <div>
                            <label for="user-tenant" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Tenant workspace</label>
                            <select id="user-tenant" wire:model="tenantId" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" @disabled($role === 'super_admin')>
                                <option value="">{{ $role === 'super_admin' ? 'Platform access' : 'Select tenant' }}</option>
                                @foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach
                            </select>
                            @error('tenantId') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="user-status" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
                            <select id="user-status" wire:model="status" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <button type="button" wire:click="$set('showModal', false)" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                            <span wire:loading.remove wire:target="save">Save user</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
