<div class="space-y-6">
    @php
        $activeClients = $clients->where('is_active', true)->count();
        $activeWebhooks = $webhooks->where('is_active', true)->count();
    @endphp

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
        <div class="flex items-start gap-3 rounded-2xl border border-warning-200 bg-warning-50 px-4 py-3.5 dark:border-warning-500/20 dark:bg-warning-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-warning-600 dark:stroke-warning-400" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <div class="min-w-0 flex-1">
                <p class="text-theme-sm font-semibold text-warning-700 dark:text-warning-300">Copy this API token now — it will not be shown again.</p>
                <code class="mt-2 block break-all rounded-lg bg-white/70 px-3 py-2 font-mono text-theme-sm text-gray-800 dark:bg-gray-900/60 dark:text-white/90">{{ $issuedToken }}</code>
                <button type="button" wire:click="dismissToken" class="mt-2 inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-theme-xs font-semibold text-warning-600 transition hover:bg-warning-100 dark:text-warning-400 dark:hover:bg-warning-500/15">Dismiss</button>
            </div>
        </div>
    @endif

    <!-- Overview -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $clients->count() }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">API clients</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-success-500/10 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $activeClients }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Active clients</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-cyan-500/10 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><line x1="8" y1="10" x2="16" y2="10"/><line x1="8" y1="14" x2="12" y2="14"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-cyan-600 dark:text-cyan-400">{{ $activeWebhooks }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Active webhooks</p>
            </div>
        </div>
    </section>

    <!-- Tabs -->
    <div class="flex flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-white p-1 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <button type="button" wire:click="$set('tab', 'clients')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'clients' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
            API clients
            <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'clients' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $clients->count() }}</span>
        </button>
        <button type="button" wire:click="$set('tab', 'logs')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'logs' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
            Request logs
        </button>
        <button type="button" wire:click="$set('tab', 'webhooks')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'webhooks' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
            Webhooks
            <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'webhooks' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $webhooks->count() }}</span>
        </button>
    </div>

    @if($tab === 'clients')
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </span>
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Create API client</h2>
            </div>
            <form wire:submit="createClient" class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <input wire:model="clientName" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="Client name">
                <select wire:model="clientTenantId" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">Platform-level</option>
                    @foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach
                </select>
                <div class="relative">
                    <input wire:model="rateLimit" type="number" min="1" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="Requests/min">
                    <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-theme-xs text-gray-400">/min</span>
                </div>
                <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Create client</button>
            </form>
            @error('clientName')<p class="mt-2 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Client</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Scope</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Rate limit</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Failed requests</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Last used</th>
                            <th class="px-5 py-3.5 text-center text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($clients as $client)
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-gray-100 text-gray-500 dark:bg-white/[0.05] dark:text-gray-400">
                                            <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $client->name }}</p>
                                            <p class="mt-0.5 text-theme-xs text-gray-400 dark:text-gray-500">Created {{ $client->created_at?->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $client->tenant_id ? 'bg-cyan-50 text-cyan-600 dark:bg-cyan-500/10 dark:text-cyan-400' : 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400' }}">{{ $client->tenant?->name ?? 'Platform' }}</span>
                                </td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $client->rate_limit_per_minute }}<span class="text-theme-xs text-gray-400"> /min</span></td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-theme-xs font-bold {{ $client->failed_count > 0 ? 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $client->failed_count }}</span>
                                </td>
                                <td class="px-5 py-4 text-theme-xs text-gray-500 dark:text-gray-400">{{ $client->last_used_at?->format('d M Y, H:i') ?? 'Never' }}</td>
                                <td class="px-5 py-4 text-center">
                                    @if($client->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-500"><span class="size-1.5 rounded-full bg-success-500"></span>Active</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-error-50 px-2.5 py-1 text-theme-xs font-semibold text-error-600 dark:bg-error-500/15 dark:text-error-500"><span class="size-1.5 rounded-full bg-error-500"></span>Revoked</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" wire:click="simulateRequest({{ $client->id }})" title="Log a request" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-success-300 hover:bg-success-50 hover:text-success-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-success-500/40 dark:hover:bg-success-500/10 dark:hover:text-success-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                        </button>
                                        <button type="button" wire:click="simulateRequest({{ $client->id }}, true)" title="Log a failure" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-error-300 hover:bg-error-50 hover:text-error-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-error-500/40 dark:hover:bg-error-500/10 dark:hover:text-error-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                        </button>
                                        <button type="button" wire:click="toggleClientActive({{ $client->id }})" title="{{ $client->is_active ? 'Revoke' : 'Reactivate' }}" class="grid h-8 w-8 place-items-center rounded-lg border {{ $client->is_active ? 'border-warning-200 bg-warning-50 text-warning-600 hover:border-warning-300 hover:bg-warning-100 dark:border-warning-500/25 dark:bg-warning-500/10 dark:text-warning-400' : 'border-success-200 bg-success-50 text-success-600 hover:border-success-300 hover:bg-success-100 dark:border-success-500/25 dark:bg-success-500/10 dark:text-success-400' }}">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                        </button>
                                        <button type="button" wire:click="deleteClient({{ $client->id }})" wire:confirm="Delete this API client?" title="Delete" class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                                        </span>
                                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No API clients yet</p>
                                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Create a client above to issue an API token for integrations.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($tab === 'logs')
        <div class="flex items-end justify-between gap-3">
            <div class="max-w-xs">
                <label for="api-status-filter" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
                <select id="api-status-filter" wire:model.live="statusFilter" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">All requests</option>
                    <option value="success">Success</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
            <p class="text-theme-xs text-gray-400 dark:text-gray-500">Showing {{ $logs->total() }} request log{{ $logs->total() === 1 ? '' : 's' }}</p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Client</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Endpoint</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Method</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status code</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Requested at</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($logs as $log)
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $log->client->name }}</td>
                                <td class="px-5 py-4"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">{{ $log->endpoint }}</code></td>
                                <td class="px-5 py-4"><span class="inline-flex rounded bg-gray-100 px-2 py-0.5 font-mono text-theme-xs font-bold uppercase text-gray-600 dark:bg-white/[0.06] dark:text-gray-300">{{ $log->method }}</span></td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 font-mono text-theme-xs font-semibold {{ $log->is_failed ? 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400' : 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400' }}">
                                        <span class="size-1.5 rounded-full bg-current"></span>{{ $log->status_code }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right text-theme-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                        </span>
                                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No request logs match these filters</p>
                                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Requests appear here as API clients are used.</p>
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
            <button type="button" wire:click="createWebhook" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add webhook
            </button>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Event</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">URL</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Scope</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Last triggered</th>
                            <th class="px-5 py-3.5 text-center text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($webhooks as $w)
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4"><code class="rounded bg-gray-100 px-2 py-1 font-mono text-theme-xs font-semibold text-brand-600 dark:bg-white/[0.06] dark:text-brand-400">{{ $w->event }}</code></td>
                                <td class="max-w-xs truncate px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $w->url }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $w->tenant_id ? 'bg-cyan-50 text-cyan-600 dark:bg-cyan-500/10 dark:text-cyan-400' : 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400' }}">{{ $w->tenant?->name ?? 'Platform' }}</span>
                                </td>
                                <td class="px-5 py-4 text-theme-xs text-gray-500 dark:text-gray-400">{{ $w->last_triggered_at?->format('d M Y, H:i') ?? 'Never' }}</td>
                                <td class="px-5 py-4 text-center">
                                    @if($w->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-500"><span class="size-1.5 rounded-full bg-success-500"></span>Active</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs font-semibold text-gray-500 dark:bg-white/[0.05] dark:text-gray-400"><span class="size-1.5 rounded-full bg-gray-400"></span>Inactive</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" wire:click="triggerTestWebhook({{ $w->id }})" title="Send test delivery" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-success-300 hover:bg-success-50 hover:text-success-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-success-500/40 dark:hover:bg-success-500/10 dark:hover:text-success-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                        </button>
                                        <button type="button" wire:click="viewWebhookLogs({{ $w->id }})" title="View delivery logs" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                        </button>
                                        <button type="button" wire:click="toggleWebhookActive({{ $w->id }})" title="{{ $w->is_active ? 'Deactivate' : 'Activate' }}" class="grid h-8 w-8 place-items-center rounded-lg border {{ $w->is_active ? 'border-warning-200 bg-warning-50 text-warning-600 hover:border-warning-300 hover:bg-warning-100 dark:border-warning-500/25 dark:bg-warning-500/10 dark:text-warning-400' : 'border-success-200 bg-success-50 text-success-600 hover:border-success-300 hover:bg-success-100 dark:border-success-500/25 dark:bg-success-500/10 dark:text-success-400' }}">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                        </button>
                                        <button type="button" wire:click="editWebhook({{ $w->id }})" title="Edit" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                        <button type="button" wire:click="deleteWebhook({{ $w->id }})" wire:confirm="Delete this webhook?" title="Delete" class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><line x1="8" y1="10" x2="16" y2="10"/><line x1="8" y1="14" x2="12" y2="14"/></svg>
                                        </span>
                                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No webhooks configured</p>
                                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Subscribe to events so BeeCore notifies external systems.</p>
                                        <button type="button" wire:click="createWebhook" class="mt-4 inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Add webhook</button>
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
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $webhookId ? 'Edit webhook' : 'Add webhook' }}</h3>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Deliver a BeeCore event to an external endpoint over HTTPS.</p>
                        </div>
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
                            <select id="wh-scope" wire:model="webhookTenantId" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
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
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Webhook logs</h3>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Delivery attempts for this webhook endpoint.</p>
                        </div>
                        <button type="button" wire:click="closeWebhookLogs" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($webhookLogs as $log)
                            <div class="flex items-start justify-between gap-4 py-3 first:pt-0 last:pb-0">
                                <div class="min-w-0">
                                    @if($log->success)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 font-mono text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-400"><span class="size-1.5 rounded-full bg-success-500"></span>{{ $log->status_code }}</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-error-50 px-2.5 py-1 font-mono text-theme-xs font-semibold text-error-600 dark:bg-error-500/15 dark:text-error-400"><span class="size-1.5 rounded-full bg-error-500"></span>{{ $log->status_code }}</span>
                                    @endif
                                    @if($log->response_body)<div class="mt-1.5 break-words rounded-lg bg-gray-50 px-2.5 py-1.5 font-mono text-theme-xs text-gray-500 dark:bg-white/[0.02] dark:text-gray-400">{{ $log->response_body }}</div>@endif
                                </div>
                                <time class="shrink-0 text-theme-xs text-gray-400 dark:text-gray-500">{{ $log->created_at->format('d M Y, H:i') }}</time>
                            </div>
                        @empty
                            <div class="py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">No webhook logs yet.</div>
                        @endforelse
                    </div>
                    <div class="mt-5 flex justify-end">
                        <button wire:click="closeWebhookLogs" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Close</button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
