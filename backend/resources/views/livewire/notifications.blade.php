<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Notifications</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Lifecycle notification events, channel configuration, and delivery logs.</p>
        </div>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Tabs -->
    <div class="inline-flex flex-wrap items-center gap-1 rounded-xl bg-gray-100 p-1 dark:bg-gray-800">
        <button wire:click="$set('tab', 'events')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'events' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-white/[0.03] dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">Notification events</button>
        <button wire:click="$set('tab', 'logs')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'logs' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-white/[0.03] dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">Delivery logs</button>
    </div>

    @if($tab === 'events')
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-y border-gray-100 bg-gray-50/60 dark:border-gray-800">
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Event</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Email</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">SMS</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Push</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                            <th scope="col" class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($events as $event)
                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 align-middle text-theme-sm">
                                    <div class="font-medium text-gray-800 dark:text-white/90">{{ $event->name }}</div>
                                    <div class="mt-0.5 font-mono text-theme-xs text-gray-500 dark:text-gray-400">{{ $event->key }}</div>
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    <button wire:click="toggleChannel({{ $event->id }}, 'email')" class="rounded-full px-2.5 py-1 text-theme-xs font-medium transition {{ $event->email_enabled ? 'bg-success-50 text-success-600 hover:bg-success-100 dark:bg-success-500/15 dark:text-success-500 dark:hover:bg-success-500/25' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 dark:bg-white/[0.05] dark:text-gray-400 dark:hover:bg-white/[0.1]' }}">{{ $event->email_enabled ? 'On' : 'Off' }}</button>
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    <button wire:click="toggleChannel({{ $event->id }}, 'sms')" class="rounded-full px-2.5 py-1 text-theme-xs font-medium transition {{ $event->sms_enabled ? 'bg-success-50 text-success-600 hover:bg-success-100 dark:bg-success-500/15 dark:text-success-500 dark:hover:bg-success-500/25' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 dark:bg-white/[0.05] dark:text-gray-400 dark:hover:bg-white/[0.1]' }}">{{ $event->sms_enabled ? 'On' : 'Off' }}</button>
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    <button wire:click="toggleChannel({{ $event->id }}, 'push')" class="rounded-full px-2.5 py-1 text-theme-xs font-medium transition {{ $event->push_enabled ? 'bg-success-50 text-success-600 hover:bg-success-100 dark:bg-success-500/15 dark:text-success-500 dark:hover:bg-success-500/25' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 dark:bg-white/[0.05] dark:text-gray-400 dark:hover:bg-white/[0.1]' }}">{{ $event->push_enabled ? 'On' : 'Off' }}</button>
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    @if($event->is_active)
                                        <span class="rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Enabled</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs font-medium text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">Disabled</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    <div class="flex flex-wrap items-center justify-end gap-1">
                                        <button wire:click="sendTest({{ $event->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">Send test</button>
                                        <button wire:click="toggleActive({{ $event->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium transition {{ $event->is_active ? 'text-warning-600 hover:bg-warning-50 dark:text-warning-500 dark:hover:bg-warning-500/10' : 'text-success-600 hover:bg-success-50 dark:text-success-500 dark:hover:bg-success-500/10' }}">{{ $event->is_active ? 'Disable' : 'Enable' }}</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="grid gap-3 sm:grid-cols-2 lg:max-w-lg">
            <div>
                <label for="notif-status-filter" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
                <select id="notif-status-filter" wire:model.live="statusFilter" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">All</option>
                    <option value="sent">Sent</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
            <div>
                <label for="notif-channel-filter" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Channel</label>
                <select id="notif-channel-filter" wire:model.live="channelFilter" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">All</option>
                    <option value="email">Email</option>
                    <option value="sms">SMS</option>
                    <option value="push">Push</option>
                </select>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-y border-gray-100 bg-gray-50/60 dark:border-gray-800">
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Event</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Channel</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Tenant</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Recipient</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Sent at</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($logs as $log)
                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 align-middle"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs font-medium text-brand-600 dark:bg-gray-800 dark:text-brand-400">{{ $log->event_key }}</code></td>
                                <td class="px-5 py-4 align-middle text-theme-sm uppercase text-gray-600 dark:text-gray-400">{{ $log->channel }}</td>
                                <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">{{ $log->tenant?->name ?? 'Platform' }}</td>
                                <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">{{ $log->recipient ?? '—' }}</td>
                                <td class="px-5 py-4 align-middle">
                                    <span class="rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $log->status === 'sent' ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500' }}">{{ $log->status }}</span>
                                </td>
                                <td class="px-5 py-4 align-middle text-theme-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-4 align-middle">
                                    <div class="py-10 text-center">
                                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">No notification logs match these filters.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())<div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $logs->links() }}</div>@endif
        </div>
    @endif
</div>
