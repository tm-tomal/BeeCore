<div>
    <header class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
        <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">API management</h1>
        <p class="mt-2 text-sm text-slate-500">API clients, tokens, rate limits, request logs, and webhooks.</p>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    @if($issuedToken)
        <div class="mb-5 border border-amber-400/25 bg-amber-400/10 p-4 text-sm text-amber-200" style="border-radius:6px">
            <p class="font-bold">Copy this API token now — it will not be shown again.</p>
            <code class="mt-2 block break-all text-teal-300">{{ $issuedToken }}</code>
            <button wire:click="dismissToken" class="mt-3 text-xs font-semibold text-slate-400">Dismiss</button>
        </div>
    @endif

    <div class="mb-5 flex flex-wrap gap-2">
        <button wire:click="$set('tab', 'clients')" class="px-4 py-2 text-sm font-bold {{ $tab === 'clients' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">API clients</button>
        <button wire:click="$set('tab', 'logs')" class="px-4 py-2 text-sm font-bold {{ $tab === 'logs' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Request logs</button>
        <button wire:click="$set('tab', 'webhooks')" class="px-4 py-2 text-sm font-bold {{ $tab === 'webhooks' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Webhooks</button>
    </div>

    @if($tab === 'clients')
        <div class="mb-5 border border-white/10 bg-white/[0.02] p-4" style="border-radius:6px">
            <h2 class="mb-3 text-sm font-bold text-white">Create API client</h2>
            <form wire:submit="createClient" class="grid gap-3 sm:grid-cols-4">
                <input wire:model="clientName" class="bc-field" placeholder="Client name">
                <select wire:model="clientTenantId" class="bc-field"><option value="">Platform-level</option>@foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach</select>
                <input wire:model="rateLimit" type="number" min="1" class="bc-field" placeholder="Requests/min">
                <button type="submit" class="bc-primary">Create client</button>
            </form>
            @error('clientName')<p class="mt-2 text-xs text-rose-300">{{ $message }}</p>@enderror
        </div>

        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Client</th><th>Scope</th><th>Rate limit</th><th>Failed requests</th><th>Last used</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td class="font-bold text-white">{{ $client->name }}</td>
                            <td>{{ $client->tenant?->name ?? 'Platform' }}</td>
                            <td>{{ $client->rate_limit_per_minute }}/min</td>
                            <td class="{{ $client->failed_count > 0 ? 'text-rose-300' : '' }}">{{ $client->failed_count }}</td>
                            <td class="text-xs text-slate-500">{{ $client->last_used_at?->format('d M Y, H:i') ?? 'Never' }}</td>
                            <td><span class="font-semibold {{ $client->is_active ? 'text-emerald-300' : 'text-rose-300' }}">{{ $client->is_active ? 'Active' : 'Revoked' }}</span></td>
                            <td class="text-right">
                                <div class="flex flex-wrap justify-end gap-3">
                                    <button wire:click="simulateRequest({{ $client->id }})" class="font-semibold text-slate-300">Log request</button>
                                    <button wire:click="simulateRequest({{ $client->id }}, true)" class="font-semibold text-slate-300">Log failure</button>
                                    <button wire:click="toggleClientActive({{ $client->id }})" class="font-semibold {{ $client->is_active ? 'text-amber-300' : 'text-emerald-300' }}">{{ $client->is_active ? 'Revoke' : 'Reactivate' }}</button>
                                    <button wire:click="deleteClient({{ $client->id }})" wire:confirm="Delete this API client?" class="font-semibold text-rose-300">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-12 text-center text-slate-600">No API clients yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif($tab === 'logs')
        <div class="mb-5 max-w-xs"><label class="bc-label" for="api-status-filter">Status</label><select id="api-status-filter" wire:model.live="statusFilter" class="bc-field"><option value="">All</option><option value="success">Success</option><option value="failed">Failed</option></select></div>

        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Client</th><th>Endpoint</th><th>Method</th><th>Status code</th><th>Requested at</th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->client->name }}</td>
                            <td><code class="text-slate-400">{{ $log->endpoint }}</code></td>
                            <td>{{ $log->method }}</td>
                            <td class="font-semibold {{ $log->is_failed ? 'text-rose-300' : 'text-emerald-300' }}">{{ $log->status_code }}</td>
                            <td class="text-xs text-slate-500">{{ $log->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 text-center text-slate-600">No API request logs match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($logs->hasPages())<div class="border-t border-white/10 p-4">{{ $logs->links() }}</div>@endif
        </div>
    @else
        <div class="mb-5 flex justify-end"><button wire:click="createWebhook" class="bc-primary">Add webhook</button></div>
        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Event</th><th>URL</th><th>Scope</th><th>Last triggered</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($webhooks as $w)
                        <tr>
                            <td><code class="text-teal-300">{{ $w->event }}</code></td>
                            <td class="max-w-xs truncate">{{ $w->url }}</td>
                            <td>{{ $w->tenant?->name ?? 'Platform' }}</td>
                            <td class="text-xs text-slate-500">{{ $w->last_triggered_at?->format('d M Y, H:i') ?? 'Never' }}</td>
                            <td><span class="font-semibold {{ $w->is_active ? 'text-emerald-300' : 'text-slate-500' }}">{{ $w->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-right">
                                <div class="flex flex-wrap justify-end gap-3">
                                    <button wire:click="triggerTestWebhook({{ $w->id }})" class="font-semibold text-slate-300">Test</button>
                                    <button wire:click="viewWebhookLogs({{ $w->id }})" class="font-semibold text-slate-300">Logs</button>
                                    <button wire:click="toggleWebhookActive({{ $w->id }})" class="font-semibold {{ $w->is_active ? 'text-amber-300' : 'text-emerald-300' }}">{{ $w->is_active ? 'Deactivate' : 'Activate' }}</button>
                                    <button wire:click="editWebhook({{ $w->id }})" class="font-semibold text-teal-300">Edit</button>
                                    <button wire:click="deleteWebhook({{ $w->id }})" wire:confirm="Delete this webhook?" class="font-semibold text-rose-300">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 text-center text-slate-600">No webhooks configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($webhookViewMode === 'create')
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-black/70" wire:click="cancelWebhookForm"></div>
                <div class="bc-panel relative max-h-[90vh] w-full max-w-lg overflow-y-auto p-6" style="border-radius:8px">
                    <h2 class="text-lg font-bold text-white">{{ $webhookId ? 'Edit webhook' : 'Add webhook' }}</h2>
                    <form wire:submit="saveWebhook" class="mt-5 space-y-4">
                        <div><label class="bc-label" for="wh-event">Event</label><input id="wh-event" wire:model="webhookEvent" class="bc-field" placeholder="tenant.subscription.renewed">@error('webhookEvent')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div><label class="bc-label" for="wh-url">URL</label><input id="wh-url" wire:model="webhookUrl" class="bc-field" placeholder="https://example.com/webhooks">@error('webhookUrl')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div><label class="bc-label" for="wh-scope">Scope</label><select id="wh-scope" wire:model="webhookTenantId" class="bc-field"><option value="">Platform-level</option>@foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach</select></div>
                        <div><label class="bc-label" for="wh-secret">Signing secret {{ $webhookId ? '(leave blank to keep current)' : '' }}</label><input id="wh-secret" wire:model="webhookSecret" type="password" class="bc-field">@error('webhookSecret')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div class="flex justify-end gap-3"><button type="button" wire:click="cancelWebhookForm" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Save webhook</button></div>
                    </form>
                </div>
            </div>
        @endif

        @if($webhookLogsForId)
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-black/70" wire:click="closeWebhookLogs"></div>
                <div class="bc-panel relative max-h-[80vh] w-full max-w-lg overflow-y-auto p-6" style="border-radius:8px">
                    <h2 class="text-lg font-bold text-white">Webhook logs</h2>
                    <ul class="mt-5 space-y-3 text-sm">
                        @forelse($webhookLogs as $log)
                            <li class="border-b border-white/10 pb-3">
                                <div class="flex items-center justify-between"><span class="font-semibold {{ $log->success ? 'text-emerald-300' : 'text-rose-300' }}">{{ $log->status_code }}</span><span class="text-xs text-slate-600">{{ $log->created_at->format('d M Y, H:i') }}</span></div>
                                @if($log->response_body)<div class="mt-1 text-slate-400">{{ $log->response_body }}</div>@endif
                            </li>
                        @empty
                            <li class="py-6 text-center text-slate-600">No webhook logs yet.</li>
                        @endforelse
                    </ul>
                    <div class="mt-5 flex justify-end"><button wire:click="closeWebhookLogs" class="bc-secondary">Close</button></div>
                </div>
            </div>
        @endif
    @endif
</div>
