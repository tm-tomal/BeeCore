<div>
    <header class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
        <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Security center</h1>
        <p class="mt-2 text-sm text-slate-500">Active sessions, login history, suspicious activity, and IP blocking.</p>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    @if($suspiciousIps->isNotEmpty())
        <div class="mb-5 border border-rose-400/25 bg-rose-400/10 p-4" style="border-radius:6px">
            <p class="text-xs font-bold uppercase text-rose-300">Security alerts — suspicious activity (24h)</p>
            <ul class="mt-2 space-y-1 text-sm text-rose-200">@foreach($suspiciousIps as $ip => $count)<li>{{ $ip }} — {{ $count }} failed login attempts</li>@endforeach</ul>
        </div>
    @endif

    <div class="mb-5 flex flex-wrap gap-2">
        <button wire:click="$set('tab', 'sessions')" class="px-4 py-2 text-sm font-bold {{ $tab === 'sessions' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Active sessions</button>
        <button wire:click="$set('tab', 'logins')" class="px-4 py-2 text-sm font-bold {{ $tab === 'logins' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Login history</button>
        <button wire:click="$set('tab', 'ip')" class="px-4 py-2 text-sm font-bold {{ $tab === 'ip' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">IP blocking</button>
    </div>

    @if($tab === 'sessions')
        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>User</th><th>IP address</th><th>Device / agent</th><th>Last active</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($sessionRows as $row)
                        @php $user = $sessionUsers->get($row->user_id); @endphp
                        <tr>
                            <td class="font-semibold text-white">{{ $user?->name ?? 'Guest' }}</td>
                            <td><code class="text-slate-400">{{ $row->ip_address }}</code></td>
                            <td class="max-w-xs truncate text-xs text-slate-500">{{ $row->user_agent }}</td>
                            <td class="text-xs text-slate-500">{{ \Carbon\Carbon::createFromTimestamp($row->last_activity)->format('d M Y, H:i') }}</td>
                            <td class="text-right">
                                <div class="flex justify-end gap-3">
                                    @if($user)<button wire:click="forceLogoutUser({{ $user->id }})" wire:confirm="Log out all sessions for this user?" class="font-semibold text-amber-300">Logout user</button>@endif
                                    <button wire:click="forceLogoutSession('{{ $row->id }}')" wire:confirm="Terminate this session?" class="font-semibold text-rose-300">Terminate</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 text-center text-slate-600">No active sessions.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif($tab === 'logins')
        <div class="mb-5 max-w-xs"><label class="bc-label" for="login-filter">Status</label><select id="login-filter" wire:model.live="loginStatusFilter" class="bc-field"><option value="">All</option><option value="success">Success</option><option value="failed">Failed</option></select></div>

        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Email</th><th>IP address</th><th>Status</th><th>When</th></tr></thead>
                <tbody>
                    @forelse($loginAttempts as $attempt)
                        <tr>
                            <td>{{ $attempt->email }}</td>
                            <td><code class="text-slate-400">{{ $attempt->ip_address }}</code></td>
                            <td><span class="font-semibold {{ $attempt->successful ? 'text-emerald-300' : 'text-rose-300' }}">{{ $attempt->successful ? 'Success' : 'Failed' }}</span></td>
                            <td class="text-xs text-slate-500">{{ $attempt->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-12 text-center text-slate-600">No login attempts recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($loginAttempts->hasPages())<div class="border-t border-white/10 p-4">{{ $loginAttempts->links() }}</div>@endif
        </div>
    @else
        <div class="mb-5 border border-white/10 bg-white/[0.02] p-4" style="border-radius:6px">
            <h2 class="mb-3 text-sm font-bold text-white">Block an IP address</h2>
            <form wire:submit="blockIp" class="grid gap-3 sm:grid-cols-3">
                <input wire:model="blockIpAddress" class="bc-field" placeholder="203.0.113.10">
                <input wire:model="blockReason" class="bc-field" placeholder="Reason (optional)">
                <button type="submit" class="bc-primary">Block IP</button>
            </form>
            @error('blockIpAddress')<p class="mt-2 text-xs text-rose-300">{{ $message }}</p>@enderror
        </div>

        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>IP address</th><th>Reason</th><th>Blocked by</th><th>Blocked at</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($blockedIps as $blocked)
                        <tr>
                            <td><code class="text-rose-300">{{ $blocked->ip_address }}</code></td>
                            <td>{{ $blocked->reason ?? '—' }}</td>
                            <td>{{ $blocked->blockedBy?->name ?? 'System' }}</td>
                            <td class="text-xs text-slate-500">{{ $blocked->blocked_at->format('d M Y, H:i') }}</td>
                            <td class="text-right"><button wire:click="unblockIp({{ $blocked->id }})" class="font-semibold text-teal-300">Unblock</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 text-center text-slate-600">No IP addresses blocked.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
