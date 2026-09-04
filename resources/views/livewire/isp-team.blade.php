<div class="space-y-6">
    @php
        $roleMeta = fn (string $role): array => $roleCatalog[$role] ?? ['label' => $role, 'description' => '', 'fixed' => false];
        $roleChip = fn (string $role): string => match ($role) {
            'tenant_admin' => 'bg-brand-50 text-brand-600 ring-1 ring-inset ring-brand-100 dark:bg-brand-500/10 dark:text-brand-400 dark:ring-brand-500/25',
            'finance' => 'bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/25',
            'support' => 'bg-sky-50 text-sky-600 ring-1 ring-inset ring-sky-100 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-500/25',
            'network_engineer' => 'bg-violet-50 text-violet-600 ring-1 ring-inset ring-violet-100 dark:bg-violet-500/10 dark:text-violet-400 dark:ring-violet-500/25',
            default => 'bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-200 dark:bg-white/[0.05] dark:text-gray-400 dark:ring-gray-700',
        };
        $roleIcon = fn (string $role): string => match ($role) {
            'tenant_admin' => 'M12 2a5 5 0 0 1 4.9 6.2A6 6 0 0 1 18 14a6 6 0 0 1-12 0 6 6 0 0 1 1.1-7.8A5 5 0 0 1 12 2z',
            'finance' => 'M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6',
            'support' => 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z',
            default => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h6l3 3 9-9V4h-3l-9 9-3-3H3z',
        };
    @endphp

    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">ISP workspace</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Team &amp; roles</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">The ISP Owner runs the workspace — staff are added and assigned to a role. Each active member uses one seat of your BeeCore plan.</p>
        </div>
        <div class="flex shrink-0 items-center gap-2">
            @if($limit !== null)
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-theme-xs font-semibold {{ $usage >= $limit ? 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400' : 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400' }}">
                    <span class="size-1.5 rounded-full {{ $usage >= $limit ? 'bg-error-500' : 'bg-success-500' }}"></span>
                    {{ $usage }} / {{ $limit }} seats used
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1.5 text-theme-xs font-semibold text-gray-500 dark:bg-white/[0.05] dark:text-gray-400">
                    <span class="size-1.5 rounded-full bg-gray-400"></span>
                    {{ $planName ? $planName.' plan' : 'No active plan' }}
                </span>
            @endif
        </div>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <x-plan-error-banner />

    <!-- Tabs -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="inline-flex rounded-xl border border-gray-200 bg-gray-100 p-1 dark:border-gray-700 dark:bg-white/[0.04]" role="tablist" aria-label="Team sections">
            <button type="button" role="tab" wire:click="switchTab('members')" aria-selected="{{ $tab === 'members' ? 'true' : 'false' }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'members' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Members
                <span class="rounded-full bg-gray-200 px-1.5 py-0.5 text-[10px] font-bold text-gray-600 dark:bg-white/[0.08] dark:text-gray-300">{{ $members->count() }}</span>
            </button>
            <button type="button" role="tab" wire:click="switchTab('roles')" aria-selected="{{ $tab === 'roles' ? 'true' : 'false' }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'roles' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Roles
            </button>
        </div>
        @if($tab === 'members')
            <button type="button" wire:click="openAdd()" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Add member
            </button>
        @endif
    </div>

    @if($tab === 'members')
        <!-- Members -->
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">All members</h2>
                <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Everyone with access to this workspace, grouped under the role they hold.</p>
            </div>
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50/50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Member</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Role</th>
                            <th class="px-5 py-3.5 text-center text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($members as $member)
                            @php
                                $isSelf = $member->id === $currentUser->id;
                            @endphp
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-full {{ $isSelf ? 'bg-brand-500 text-white' : 'bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400' }} text-theme-sm font-bold">{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                                        <div class="min-w-0">
                                            <p class="flex items-center gap-2 truncate text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                                                {{ $member->name }}
                                                @if($isSelf)
                                                    <span class="rounded-full bg-brand-50 px-1.5 py-0.5 text-[10px] font-bold uppercase text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">You</span>
                                                @endif
                                            </p>
                                            <p class="mt-0.5 truncate text-theme-xs text-gray-400 dark:text-gray-500">{{ $member->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $roleChip($member->role) }}">{{ $roleMeta($member->role)['label'] }}</span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if($member->status === 'active')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-500"><span class="size-1.5 rounded-full bg-success-500"></span>Active</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs font-semibold text-gray-500 dark:bg-white/[0.05] dark:text-gray-400"><span class="size-1.5 rounded-full bg-gray-400"></span>Inactive</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($member->role === 'tenant_admin')
                                        <p class="flex items-center justify-end gap-1 text-theme-xs font-medium text-gray-400 dark:text-gray-500">
                                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                            {{ $isSelf ? 'You are the ISP Owner' : 'ISP Owner' }}
                                        </p>
                                    @elseif($isSelf)
                                        <p class="text-right text-theme-xs text-gray-400 dark:text-gray-500">—</p>
                                    @else
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if($member->status === 'active')
                                                <button type="button" wire:click="toggleActive({{ $member->id }})" wire:confirm="Deactivate this member? They will lose access immediately." class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-theme-xs font-medium text-gray-700 transition hover:border-warning-300 hover:bg-warning-50 hover:text-warning-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-warning-500/40 dark:hover:bg-warning-500/10 dark:hover:text-warning-400">
                                                    <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="4.93 4.93 19.07 19.07"/></svg>
                                                    Deactivate
                                                </button>
                                            @else
                                                <button type="button" wire:click="toggleActive({{ $member->id }})" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-success-200 bg-success-50 px-3 py-2 text-theme-xs font-medium text-success-600 transition hover:border-success-300 hover:bg-success-100 dark:border-success-500/25 dark:bg-success-500/10 dark:text-success-400">
                                                    <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                    Activate
                                                </button>
                                            @endif
                                            <button type="button" wire:click="remove({{ $member->id }})" wire:confirm="Remove this member from the workspace?" class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">
                                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                        </span>
                                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No team members yet</p>
                                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Use “Add member” to give a role to your first staff member.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @else
        <!-- Roles overview -->
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex flex-col gap-1">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Roles in this workspace</h2>
                <p class="text-theme-xs text-gray-500 dark:text-gray-400">Each role decides what a member can do. Assign a member to a role, or add a fresh member below.</p>
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                @foreach($roleCatalog as $roleKey => $roleMetaItem)
                    @php
                        $assigned = $membersByRole->get($roleKey, collect());
                        $activeCount = $assigned->where('status', 'active')->count();
                    @endphp
                    <article class="flex flex-col rounded-xl border p-5 {{ $roleMetaItem['fixed'] ? 'border-brand-200 bg-brand-50/40 dark:border-brand-500/25 dark:bg-brand-500/[0.05]' : 'border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.02]' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ $roleChip($roleKey) }}">
                                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $roleIcon($roleKey) }}"/></svg>
                                </span>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-theme-sm font-bold text-gray-900 dark:text-white">{{ $roleMetaItem['label'] }}</h3>
                                        @if($roleMetaItem['fixed'])
                                            <span class="inline-flex items-center gap-1 rounded-full bg-brand-500 px-2 py-0.5 text-[10px] font-bold uppercase text-white">
                                                <svg class="size-3 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                                Fixed
                                            </span>
                                        @endif
                                    </div>
                                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $roleMetaItem['description'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center gap-3 border-t border-gray-100 pt-3 dark:border-gray-800">
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                                <span class="text-lg font-bold text-gray-800 dark:text-white/90">{{ $activeCount }}</span>
                                active of {{ $assigned->count() }}
                            </p>
                            @unless($roleMetaItem['fixed'])
                                <button type="button" wire:click="openAdd('{{ $roleKey }}')" class="ml-auto inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-theme-xs font-medium text-gray-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                    <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                    Assign member
                                </button>
                            @endunless
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @forelse($assigned as $assignedMember)
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-gray-50/70 px-2.5 py-1 text-theme-xs text-gray-600 dark:border-gray-700 dark:bg-white/[0.04] dark:text-gray-300">
                                    <span class="size-1.5 rounded-full {{ $assignedMember->status === 'active' ? 'bg-success-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                    {{ $assignedMember->name }}
                                    @if($assignedMember->id === $currentUser->id)
                                        <span class="font-semibold text-brand-600 dark:text-brand-400">(you)</span>
                                    @endif
                                </span>
                            @empty
                                <p class="text-theme-xs text-gray-400 dark:text-gray-500">{{ $roleMetaItem['fixed'] ? 'This workspace is owned by an ISP Owner.' : 'No members assigned to this role yet.' }}</p>
                            @endforelse
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Add member modal -->
    @if($showAddForm)
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="team-add-title">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeAdd"></div>
            <div class="relative max-h-[92vh] w-full max-w-md overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-5 flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                            <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </span>
                        <div>
                            <h2 id="team-add-title" class="text-base font-bold text-gray-800 dark:text-white/90">Add team member</h2>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Create a login and assign it to a role.</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeAdd" class="grid size-8 shrink-0 place-items-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.05] dark:hover:text-gray-300">
                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                @if($gateError)
                    <div class="mb-4 flex items-start gap-3 rounded-xl border border-error-200 bg-error-50 px-4 py-3 dark:border-error-500/20 dark:bg-error-500/10">
                        <svg class="mt-0.5 size-5 shrink-0 stroke-error-500" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <div class="min-w-0">
                            <p class="text-theme-sm font-medium text-error-700 dark:text-error-300">{{ $gateError['message'] }}</p>
                            <a href="{{ route('isp-subscription') }}" class="mt-1.5 inline-flex items-center gap-1.5 text-theme-sm font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                {{ __('View plans & upgrade') }}
                                <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                @endif

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label for="tm-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Name</label>
                        <input id="tm-name" wire:model="name" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="e.g. Rakib Hasan">
                        @error('name') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tm-email" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Email</label>
                        <input id="tm-email" wire:model="email" type="email" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="staff@yourisp.com">
                        @error('email') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tm-role" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Role</label>
                        <p class="mb-2 text-theme-xs text-gray-500 dark:text-gray-400">ISP Owner is fixed — this member gets one of the staff roles.</p>
                        <select id="tm-role" wire:model="role" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            @foreach($roleCatalog as $value => $meta)
                                @if(! $meta['fixed'])
                                    <option value="{{ $value }}">{{ $meta['label'] }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('role') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tm-password" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Temporary password</label>
                        <input id="tm-password" wire:model="password" type="password" autocomplete="new-password" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="Min 8 characters">
                        @error('password') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col-reverse gap-3 pt-1 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="closeAdd" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03] dark:hover:text-gray-100">Cancel</button>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                            Add member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
