<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">API management</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">API clients, tokens, rate limits, request logs, and webhooks.</p>
        </div>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    @if($issuedToken)
        <div class="flex items-start gap-3 rounded-xl border border-warning-200 bg-warning-50 px-4 py-3 dark:border-warning-500/20 dark:bg-warning-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4"/><path d="M10.36 5.64a4 4 0 0 1 5.66 0l.34.34a4 4 0 0 1 0 5.66l-4 4a4 4 0 0 1-5.66 0l-.34-.34a4 4 0 0 1 0-5.66"/><path d="M12 9v4"/><path d="m9 12 3 3 3-3"/></svg>
            <div class="min-w-0">
                <p class="text-theme-sm font-semibold text-warning-700 dark:text-warning-300">Copy this API token now — it will not be shown again.</p>
                <code class="mt-2 block break-all font-mono text-theme-sm text-gray-800 dark:text-white/90">{{ $issuedToken }}</code>
                <button wire:click="dismissToken" class="mt-2 inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-theme-xs font-medium text-warning-600 transition hover:bg-warning-50 dark:text-warning-400 dark:hover:bg-warning-500/10">Dismiss</button>
            </div>
        </div>
    @endif

    <!-- Tabs -->
    <div class="inline-flex flex-wrap items-center gap-1 rounded-xl bg-gray-100 p-1 dark:bg-gray-800">
        <button wire:click="$set('tab', 'clients')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'clients' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-white/[0.03] dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">API clients</button>
        <button wire:click="$set('tab', 'logs')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'logs' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-white/[0.03] dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">Request logs</button>
        <button wire:click="$set('tab', 'webhooks')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'webhooks' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-white/[0.03] dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">Webhooks</button>
    </div>

    @if($tab === 'clients')
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Create API client</h2>
            <form wire:submit="createClient" class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <input wire:model="clientName" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="Client name">
                <select wire:model="clientTenantId" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">Platform-level</option>
                    @foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach
                </select>
                <input wire:model="rateLimit" type="number" min="1" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="Requests/min">
                <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Create client</button>
            </form>
            @error('clientName')<p class="mt-2 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-y border-gray-100 bg-gray-50/60 dark:border-gray-800">
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Client</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Scope</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Rate limit</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Failed requests</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Last used</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                            <th scope="col" class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($clients as $client)
                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 align-middle text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $client->name }}</td>
                                <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">{{ $client->tenant?->name ?? 'Platform' }}</td>
                                <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">{{ $client->rate_limit_per_minute }}/min</td>
                                <td class="px-5 py-4 align-middle text-theme-sm {{ $client->failed_count > 0 ? 'font-medium text-error-600 dark:text-error-400' : 'text-gray-600 dark:text-gray-400' }}">{{ $client->failed_count }}</td>
                                <td class="px-5 py-4 align-middle text-theme-xs text-gray-500 dark:text-gray-400">{{ $client->last_used_at?->format('d M Y, H:i') ?? 'Never' }}</td>
                                <td class="px-5 py-4 align-middle">
                                    @if($client->is_active)
                                        <span class="rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Active</span>
                                    @else
                                        <span class="rounded-full bg-error-50 px-2.5 py-1 text-theme-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">Revoked</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    <div class="flex flex-wrap items-center justify-end gap-1">
                                        <button wire:click="simulateRequest({{ $client->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">Log request</button>
                                        <button wire:click="simulateRequest({{ $client->id }}, true)" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">Log failure</button>
                                        <button wire:click="toggleClientActive({{ $client->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium transition {{ $client->is_active ? 'text-warning-600 hover:bg-warning-50 dark:text-warning-500 dark:hover:bg-warning-500/10' : 'text-success-600 hover:bg-success-50 dark:text-success-500 dark:hover:bg-success-500/10' }}">{{ $client->is_active ? 'Revoke' : 'Reactivate' }}</button>
                                        <button wire:click="deleteClient({{ $client->id }})" wire:confirm="Delete this API client?" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-error-600 transition hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-4 align-middle">
                                    <div class="py-10 text-center">
                                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">No API clients yet.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($tab === 'logs')
        <div class="max-w-xs">
            <label for="api-status-filter" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
            <select id="api-status-filter" wire:model.live="statusFilter" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                <option value="">All</option>
                <option value="success">Success</option>
                <option value="failed">Failed</option>
            </select>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-y border-gray-100 bg-gray-50/60 dark:border-gray-800">
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Client</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Endpoint</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Method</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status code</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Requested at</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($logs as $log)
                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">{{ $log->client->name }}</td>
                                <td class="px-5 py-4 align-middle"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $log->endpoint }}</code></td>
                                <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">{{ $log->method }}</td>
                                <td class="px-5 py-4 align-middle font-mono text-theme-sm font-medium {{ $log->is_failed ? 'text-error-600 dark:text-error-400' : 'text-success-600 dark:text-success-400' }}">{{ $log->status_code }}</td>
                                <td class="px-5 py-4 align-middle text-theme-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-4 align-middle">
                                    <div class="py-10 text-center">
                                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">No API request logs match these filters.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())<div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $logs->links() }}</div>@endif
        </div>
    @else
        <div class="flex justify-end">
            <button wire:click="createWebhook" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Add webhook</button>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-y border-gray-100 bg-gray-50/60 dark:border-gray-800">
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Event</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">URL</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Scope</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Last triggered</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                            <th scope="col" class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($webhooks as $w)
                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 align-middle"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs font-medium text-brand-600 dark:bg-gray-800 dark:text-brand-400">{{ $w->event }}</code></td>
                                <td class="max-w-xs truncate px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">{{ $w->url }}</td>
                                <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">{{ $w->tenant?->name ?? 'Platform' }}</td>
                                <td class="px-5 py-4 align-middle text-theme-xs text-gray-500 dark:text-gray-400">{{ $w->last_triggered_at?->format('d M Y, H:i') ?? 'Never' }}</td>
                                <td class="px-5 py-4 align-middle">
                                    @if($w->is_active)
                                        <span class="rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Active</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs font-medium text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    <div class="flex flex-wrap items-center justify-end gap-1">
                                        <button wire:click="triggerTestWebhook({{ $w->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">Test</button>
                                        <button wire:click="viewWebhookLogs({{ $w->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">Logs</button>
                                        <button wire:click="toggleWebhookActive({{ $w->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium transition {{ $w->is_active ? 'text-warning-600 hover:bg-warning-50 dark:text-warning-500 dark:hover:bg-warning-500/10' : 'text-success-600 hover:bg-success-50 dark:text-success-500 dark:hover:bg-success-500/10' }}">{{ $w->is_active ? 'Deactivate' : 'Activate' }}</button>
                                        <button wire:click="editWebhook({{ $w->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">Edit</button>
                                        <button wire:click="deleteWebhook({{ $w->id }})" wire:confirm="Delete this webhook?" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-error-600 transition hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-4 align-middle">
                                    <div class="py-10 text-center">
                                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">No webhooks configured.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($webhookViewMode === 'create')
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="cancelWebhookForm"></div>
                <div class="relative max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $webhookId ? 'Edit webhook' : 'Add webhook' }}</h3>
                        <button type="button" wire:click="cancelWebhookForm" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <form wire:submit="saveWebhook" class="space-y-5">
                        <div>
                            <label for="wh-event" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Event</label>
                            <input id="wh-event" wire:model="webhookEvent" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="tenant.subscription.renewed">
                            @error('webhookEvent')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="wh-url" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">URL</label>
                            <input id="wh-url" wire:model="webhookUrl" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="https://example.com/webhooks">
                            @error('webhookUrl')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="wh-scope" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Scope</label>
                            <select id="wh-scope" wire:model="webhookTenantId" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                <option value="">Platform-level</option>
                                @foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label for="wh-secret" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Signing secret {{ $webhookId ? '(leave blank to keep current)' : '' }}</label>
                            <input id="wh-secret" wire:model="webhookSecret" type="password" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            @error('webhookSecret')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                            <button type="button" wire:click="cancelWebhookForm" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                            <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Save webhook</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if($webhookLogsForId)
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeWebhookLogs"></div>
                <div class="relative max-h-[80vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-5 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Webhook logs</h3>
                        <button type="button" wire:click="closeWebhookLogs" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <ul class="space-y-3 text-theme-sm">
                        @forelse($webhookLogs as $log)
                            <li class="flex items-start justify-between gap-4 border-b border-gray-100 pb-3 last:border-0 dark:border-gray-800">
                                <div class="min-w-0">
                                    <span class="font-mono text-theme-sm font-semibold {{ $log->success ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400' }}">{{ $log->status_code }}</span>
                                    @if($log->response_body)<div class="mt-1 break-words text-theme-xs text-gray-500 dark:text-gray-400">{{ $log->response_body }}</div>@endif
                                </div>
                                <time class="shrink-0 text-theme-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->format('d M Y, H:i') }}</time>
                            </li>
                        @empty
                            <li class="py-6 text-center text-theme-sm text-gray-500 dark:text-gray-400">No webhook logs yet.</li>
                        @endforelse
                    </ul>
                    <div class="mt-5 flex justify-end">
                        <button wire:click="closeWebhookLogs" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Close</button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
