<div>
    <header class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
        <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Notifications</h1>
        <p class="mt-2 text-sm text-slate-500">Lifecycle notification events, channel configuration, and delivery logs.</p>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <div class="mb-5 flex gap-2">
        <button wire:click="$set('tab', 'events')" class="px-4 py-2 text-sm font-bold {{ $tab === 'events' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Notification events</button>
        <button wire:click="$set('tab', 'logs')" class="px-4 py-2 text-sm font-bold {{ $tab === 'logs' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Delivery logs</button>
    </div>

    @if($tab === 'events')
        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Event</th><th>Email</th><th>SMS</th><th>Push</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @foreach($events as $event)
                        <tr>
                            <td><div class="font-bold text-white">{{ $event->name }}</div><div class="text-xs text-slate-600">{{ $event->key }}</div></td>
                            <td><button wire:click="toggleChannel({{ $event->id }}, 'email')" class="font-semibold {{ $event->email_enabled ? 'text-emerald-300' : 'text-slate-500' }}">{{ $event->email_enabled ? 'On' : 'Off' }}</button></td>
                            <td><button wire:click="toggleChannel({{ $event->id }}, 'sms')" class="font-semibold {{ $event->sms_enabled ? 'text-emerald-300' : 'text-slate-500' }}">{{ $event->sms_enabled ? 'On' : 'Off' }}</button></td>
                            <td><button wire:click="toggleChannel({{ $event->id }}, 'push')" class="font-semibold {{ $event->push_enabled ? 'text-emerald-300' : 'text-slate-500' }}">{{ $event->push_enabled ? 'On' : 'Off' }}</button></td>
                            <td><span class="font-semibold {{ $event->is_active ? 'text-emerald-300' : 'text-rose-300' }}">{{ $event->is_active ? 'Enabled' : 'Disabled' }}</span></td>
                            <td class="text-right">
                                <div class="flex flex-wrap justify-end gap-3">
                                    <button wire:click="sendTest({{ $event->id }})" class="font-semibold text-slate-300">Send test</button>
                                    <button wire:click="toggleActive({{ $event->id }})" class="font-semibold {{ $event->is_active ? 'text-rose-300' : 'text-emerald-300' }}">{{ $event->is_active ? 'Disable' : 'Enable' }}</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="mb-5 grid gap-3 sm:grid-cols-2 lg:max-w-lg">
            <div><label class="bc-label" for="notif-status-filter">Status</label><select id="notif-status-filter" wire:model.live="statusFilter" class="bc-field"><option value="">All</option><option value="sent">Sent</option><option value="failed">Failed</option></select></div>
            <div><label class="bc-label" for="notif-channel-filter">Channel</label><select id="notif-channel-filter" wire:model.live="channelFilter" class="bc-field"><option value="">All</option><option value="email">Email</option><option value="sms">SMS</option><option value="push">Push</option></select></div>
        </div>

        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Event</th><th>Channel</th><th>Tenant</th><th>Recipient</th><th>Status</th><th>Sent at</th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td><code class="text-teal-300">{{ $log->event_key }}</code></td>
                            <td class="uppercase">{{ $log->channel }}</td>
                            <td>{{ $log->tenant?->name ?? 'Platform' }}</td>
                            <td>{{ $log->recipient ?? '—' }}</td>
                            <td><span class="capitalize font-semibold {{ $log->status === 'sent' ? 'text-emerald-300' : 'text-rose-300' }}">{{ $log->status }}</span></td>
                            <td class="text-xs text-slate-500">{{ $log->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 text-center text-slate-600">No notification logs match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($logs->hasPages())<div class="border-t border-white/10 p-4">{{ $logs->links() }}</div>@endif
        </div>
    @endif
</div>
