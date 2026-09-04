<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Security center</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Active sessions, login history, suspicious activity, and IP blocking.</p>
        </div>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    @if($suspiciousIps->isNotEmpty())
        <div class="flex items-start gap-3 rounded-2xl border border-error-200 bg-error-50 px-4 py-3.5 dark:border-error-500/20 dark:bg-error-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-error-600 dark:stroke-error-400" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <div class="min-w-0">
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-error-700 dark:text-error-300">Security alerts — suspicious activity (24h)</p>
                <ul class="mt-1.5 space-y-1 text-theme-sm text-error-700 dark:text-error-300">
                    @foreach($suspiciousIps as $ip => $count)
                        <li><code class="font-mono">{{ $ip }}</code> — {{ $count }} failed login attempts</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Tabs -->
    <div class="inline-flex flex-wrap items-center gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <button type="button" wire:click="$set('tab', 'sessions')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'sessions' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            Active sessions
            <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'sessions' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $sessionRows->count() }}</span>
        </button>
        <button type="button" wire:click="$set('tab', 'logins')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'logins' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            Login history
        </button>
        <button type="button" wire:click="$set('tab', 'ip')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'ip' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
            IP blocking
            <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'ip' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $blockedIps->count() }}</span>
        </button>
    </div>

    @if($tab === 'sessions')
        <!-- Active sessions -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">User</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">IP address</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Device / agent</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Last active</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($sessionRows as $row)
                            @php $user = $sessionUsers->get($row->user_id); @endphp
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-brand-500/10 text-theme-sm font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ $user ? strtoupper(substr($user->name, 0, 1)) : '?' }}</span>
                                        <div class="min-w-0">
                                            <p class="truncate text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $user?->name ?? 'Guest' }}</p>
                                            <p class="mt-0.5 truncate text-theme-xs text-gray-400 dark:text-gray-500">{{ $user?->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">{{ $row->ip_address }}</code></td>
                                <td class="max-w-xs truncate px-5 py-4 text-theme-xs text-gray-500 dark:text-gray-400">{{ $row->user_agent }}</td>
                                <td class="px-5 py-4 text-right text-theme-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::createFromTimestamp($row->last_activity)->format('d M Y, H:i') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($user)
                                            <button type="button" wire:click="forceLogoutUser({{ $user->id }})" wire:confirm="Log out all sessions for this user?" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-warning-200 bg-warning-50 px-3 py-2 text-theme-xs font-semibold text-warning-600 transition hover:border-warning-300 hover:bg-warning-100 dark:border-warning-500/25 dark:bg-warning-500/10 dark:text-warning-400">
                                                <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                                Logout user
                                            </button>
                                        @endif
                                        <button type="button" wire:click="forceLogoutSession('{{ $row->id }}')" wire:confirm="Terminate this session?" class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                                        </span>
                                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No active sessions</p>
                                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Signed-in users appear here while their session is live.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($tab === 'logins')
        <!-- Login history -->
        <div class="flex items-end justify-between gap-3">
            <div class="max-w-xs">
                <label for="login-filter" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
                <select id="login-filter" wire:model.live="loginStatusFilter" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">All attempts</option>
                    <option value="success">Success</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
            <p class="text-theme-xs text-gray-400 dark:text-gray-500">{{ $loginAttempts->total() }} attempt{{ $loginAttempts->total() === 1 ? '' : 's' }}</p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">IP address</th>
                            <th class="px-5 py-3.5 text-center text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">When</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($loginAttempts as $attempt)
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $attempt->email }}</td>
                                <td class="px-5 py-4"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">{{ $attempt->ip_address }}</code></td>
                                <td class="px-5 py-4 text-center">
                                    @if($attempt->successful)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-500"><span class="size-1.5 rounded-full bg-success-500"></span>Success</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-error-50 px-2.5 py-1 text-theme-xs font-semibold text-error-600 dark:bg-error-500/15 dark:text-error-500"><span class="size-1.5 rounded-full bg-error-500"></span>Failed</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right text-theme-xs text-gray-500 dark:text-gray-400">{{ $attempt->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                                        </span>
                                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No login attempts recorded yet</p>
                                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Sign-in events will be listed here for review.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($loginAttempts->hasPages())<div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $loginAttempts->links() }}</div>@endif
        </div>
    @else
        <!-- IP blocking -->
        <div class="space-y-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-error-500/10 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                        <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Block an IP address</h2>
                        <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Stop requests from a specific address across the platform.</p>
                    </div>
                </div>
                <form wire:submit="blockIp" class="mt-5 grid gap-3 sm:grid-cols-3">
                    <input wire:model="blockIpAddress" type="text" placeholder="203.0.113.10" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    <input wire:model="blockReason" type="text" placeholder="Reason (optional)" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Block IP</button>
                </form>
                @error('blockIpAddress')<p class="mt-2 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">IP address</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Reason</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Blocked by</th>
                                <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Blocked at</th>
                                <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($blockedIps as $blocked)
                                <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-4"><code class="rounded bg-error-50 px-1.5 py-0.5 font-mono text-theme-xs font-semibold text-error-600 dark:bg-error-500/15 dark:text-error-400">{{ $blocked->ip_address }}</code></td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $blocked->reason ?? '—' }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $blocked->blockedBy?->name ?? 'System' }}</td>
                                    <td class="px-5 py-4 text-right text-theme-xs text-gray-500 dark:text-gray-400">{{ $blocked->blocked_at->format('d M Y, H:i') }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <button type="button" wire:click="unblockIp({{ $blocked->id }})" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50 px-3 py-2 text-theme-xs font-semibold text-brand-600 transition hover:border-brand-300 hover:bg-brand-100 dark:border-brand-500/25 dark:bg-brand-500/10 dark:text-brand-400">
                                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 4l22 16M2 20l9.09-9.09M21 12a9 9 0 0 1-9 9c-1.7 0-3.3-.47-4.7-1.28"/><path d="M9.54 5.6A9 9 0 0 1 21 12"/></svg>
                                            Unblock
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-14 text-center">
                                        <div class="mx-auto max-w-xs">
                                            <span class="mx-auto grid size-12 place-items-center rounded-full bg-success-50 text-success-500 dark:bg-success-500/15">
                                                <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                            </span>
                                            <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No IP addresses blocked</p>
                                            <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Blocked addresses appear here with their reason and blocker.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
