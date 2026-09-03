<div class="space-y-6">
    <!-- Page header -->
    <header>
        <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
        <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Security center</h1>
        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Active sessions, login history, suspicious activity, and IP blocking.</p>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    @if($suspiciousIps->isNotEmpty())
        <div class="rounded-xl border border-error-200 bg-error-50 px-4 py-3.5 dark:border-error-500/20 dark:bg-error-500/10">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-error-700 dark:text-error-300">Security alerts — suspicious activity (24h)</p>
            <ul class="mt-2 space-y-1 text-theme-sm text-error-700 dark:text-error-300">
                @foreach($suspiciousIps as $ip => $count)
                    <li>{{ $ip }} — {{ $count }} failed login attempts</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tabs -->
    <div class="inline-flex flex-wrap items-center gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <button wire:click="$set('tab', 'sessions')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'sessions' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">Active sessions</button>
        <button wire:click="$set('tab', 'logins')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'logins' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">Login history</button>
        <button wire:click="$set('tab', 'ip')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'ip' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">IP blocking</button>
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
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Last active</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($sessionRows as $row)
                            @php $user = $sessionUsers->get($row->user_id); @endphp
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $user?->name ?? 'Guest' }}</td>
                                <td class="px-5 py-4"><code class="font-mono text-theme-xs text-gray-600 dark:text-gray-400">{{ $row->ip_address }}</code></td>
                                <td class="max-w-xs truncate px-5 py-4 text-theme-xs text-gray-500 dark:text-gray-400">{{ $row->user_agent }}</td>
                                <td class="px-5 py-4 text-theme-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::createFromTimestamp($row->last_activity)->format('d M Y, H:i') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        @if($user)
                                            <button wire:click="forceLogoutUser({{ $user->id }})" wire:confirm="Log out all sessions for this user?" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-theme-sm font-medium text-warning-600 transition hover:bg-warning-50 dark:text-warning-500 dark:hover:bg-warning-500/10">Logout user</button>
                                        @endif
                                        <button wire:click="forceLogoutSession('{{ $row->id }}')" wire:confirm="Terminate this session?" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-theme-sm font-medium text-error-600 transition hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10">Terminate</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-theme-sm text-gray-500 dark:text-gray-400">No active sessions.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($tab === 'logins')
        <!-- Login history -->
        <div class="space-y-4">
            <div class="max-w-xs">
                <label for="login-filter" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
                <select id="login-filter" wire:model.live="loginStatusFilter" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">All</option>
                    <option value="success">Success</option>
                    <option value="failed">Failed</option>
                </select>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">IP address</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">When</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($loginAttempts as $attempt)
                                <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $attempt->email }}</td>
                                    <td class="px-5 py-4"><code class="font-mono text-theme-xs text-gray-600 dark:text-gray-400">{{ $attempt->ip_address }}</code></td>
                                    <td class="px-5 py-4">
                                        @if($attempt->successful)
                                            <span class="rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Success</span>
                                        @else
                                            <span class="rounded-full bg-error-50 px-2.5 py-1 text-theme-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">Failed</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-theme-xs text-gray-500 dark:text-gray-400">{{ $attempt->created_at->format('d M Y, H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-12 text-center text-theme-sm text-gray-500 dark:text-gray-400">No login attempts recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($loginAttempts->hasPages())
                    <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $loginAttempts->links() }}</div>
                @endif
            </div>
        </div>
    @else
        <!-- IP blocking -->
        <div class="space-y-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Block an IP address</h2>
                <form wire:submit="blockIp" class="mt-4 grid gap-3 sm:grid-cols-3">
                    <input wire:model="blockIpAddress" type="text" placeholder="203.0.113.10" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    <input wire:model="blockReason" type="text" placeholder="Reason (optional)" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Block IP</button>
                </form>
                @error('blockIpAddress')
                    <p class="mt-2 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">IP address</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Reason</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Blocked by</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Blocked at</th>
                                <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($blockedIps as $blocked)
                                <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-4"><code class="font-mono text-theme-xs font-semibold text-error-600 dark:text-error-400">{{ $blocked->ip_address }}</code></td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $blocked->reason ?? '—' }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $blocked->blockedBy?->name ?? 'System' }}</td>
                                    <td class="px-5 py-4 text-theme-xs text-gray-500 dark:text-gray-400">{{ $blocked->blocked_at->format('d M Y, H:i') }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <button wire:click="unblockIp({{ $blocked->id }})" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-theme-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">Unblock</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-12 text-center text-theme-sm text-gray-500 dark:text-gray-400">No IP addresses blocked.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
