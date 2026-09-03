<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">SMS management</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Providers, sender IDs, tenant credit balances, delivery logs, and templates.</p>
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
        <button wire:click="$set('tab', 'providers')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'providers' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-white/[0.03] dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">Providers</button>
        <button wire:click="$set('tab', 'balances')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'balances' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-white/[0.03] dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">Tenant balances</button>
        <button wire:click="$set('tab', 'logs')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'logs' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-white/[0.03] dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">Delivery logs</button>
        <button wire:click="$set('tab', 'templates')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'templates' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-white/[0.03] dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">Templates</button>
    </div>

    @if($tab === 'providers')
        <div class="flex justify-end">
            <button wire:click="createProvider" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Add provider</button>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-y border-gray-100 bg-gray-50/60 dark:border-gray-800">
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Provider</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Sender ID</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Price/SMS</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                            <th scope="col" class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($providers as $p)
                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 align-middle text-theme-sm">
                                    <div class="font-medium text-gray-800 dark:text-white/90">{{ $p->name }}</div>
                                    <div class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $p->provider }}</div>
                                </td>
                                <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">{{ $p->sender_id ?? '—' }}</td>
                                <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">৳{{ number_format($p->price_per_sms, 4) }}</td>
                                <td class="px-5 py-4 align-middle">
                                    @if($p->is_active)
                                        <span class="rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Active</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs font-medium text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    <div class="flex flex-wrap items-center justify-end gap-1">
                                        <button wire:click="sendTestSms({{ $p->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">Test</button>
                                        <button wire:click="toggleProviderActive({{ $p->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium transition {{ $p->is_active ? 'text-warning-600 hover:bg-warning-50 dark:text-warning-500 dark:hover:bg-warning-500/10' : 'text-success-600 hover:bg-success-50 dark:text-success-500 dark:hover:bg-success-500/10' }}">{{ $p->is_active ? 'Deactivate' : 'Activate' }}</button>
                                        <button wire:click="editProvider({{ $p->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">Edit</button>
                                        <button wire:click="archiveProvider({{ $p->id }})" wire:confirm="Archive this provider?" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-error-600 transition hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10">Archive</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-4 align-middle">
                                    <div class="py-10 text-center">
                                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">No SMS providers configured.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($providerViewMode === 'create')
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="cancelProviderForm"></div>
                <div class="relative max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $providerId ? 'Edit provider' : 'Add provider' }}</h3>
                        <button type="button" wire:click="cancelProviderForm" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <form wire:submit="saveProvider" class="space-y-5">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="sms-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Name</label>
                                <input id="sms-name" wire:model.live="name" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                @error('name')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="sms-slug" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Slug</label>
                                <input id="sms-slug" wire:model="slug" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                @error('slug')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="sms-provider" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Provider</label>
                                <input id="sms-provider" wire:model="provider" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="twilio, banglalink, ssl_wireless">
                                @error('provider')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="sms-sender" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Sender ID</label>
                                <input id="sms-sender" wire:model="senderId" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                @error('senderId')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div>
                            <label for="sms-price" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Price per SMS</label>
                            <input id="sms-price" wire:model="pricePerSms" type="number" step="0.0001" min="0" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            @error('pricePerSms')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="sms-creds" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Credentials (one <code class="rounded bg-gray-100 px-1 py-0.5 font-mono text-theme-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">key=value</code> per line, encrypted at rest)</label>
                            <textarea id="sms-creds" wire:model="credentialsText" rows="4" class="min-h-24 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                            <button type="button" wire:click="cancelProviderForm" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                            <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Save provider</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @elseif($tab === 'balances')
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Add SMS credit</h2>
            <form wire:submit="addCredit" class="mt-4 grid gap-3 sm:grid-cols-3">
                <select wire:model="creditTenantId" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">Select tenant</option>
                    @foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach
                </select>
                <input wire:model="creditAmount" type="number" min="1" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="Credits">
                <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Add credit</button>
            </form>
            @error('creditTenantId')<p class="mt-2 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-y border-gray-100 bg-gray-50/60 dark:border-gray-800">
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Tenant</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($balances as $balance)
                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 align-middle text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $balance->tenant->name }}</td>
                                <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">{{ number_format($balance->balance) }} credits</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-5 py-4 align-middle">
                                    <div class="py-10 text-center">
                                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">No tenant SMS balances yet.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($tab === 'logs')
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Sent / delivered</p>
                <p class="mt-2 text-2xl font-bold text-success-600 dark:text-success-400">{{ $report['sent'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Failed</p>
                <p class="mt-2 text-2xl font-bold text-error-600 dark:text-error-400">{{ $report['failed'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">SMS cost</p>
                <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">৳{{ number_format($report['cost'], 2) }}</p>
            </div>
        </section>

        <div class="max-w-xs">
            <label for="sms-status-filter" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
            <select id="sms-status-filter" wire:model.live="statusFilter" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                <option value="">All</option>
                <option value="queued">Queued</option>
                <option value="sent">Sent</option>
                <option value="delivered">Delivered</option>
                <option value="failed">Failed</option>
            </select>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-y border-gray-100 bg-gray-50/60 dark:border-gray-800">
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Tenant</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Provider</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Recipient</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Cost</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Sent at</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($logs as $log)
                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">{{ $log->tenant?->name ?? 'Platform' }}</td>
                                <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">{{ $log->provider?->name ?? '—' }}</td>
                                <td class="px-5 py-4 align-middle"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $log->recipient }}</code></td>
                                <td class="px-5 py-4 align-middle">
                                    <span class="rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ match($log->status) { 'delivered', 'sent' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500', 'failed' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500', default => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500' } }}">{{ $log->status }}</span>
                                </td>
                                <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">৳{{ number_format($log->cost, 4) }}</td>
                                <td class="px-5 py-4 align-middle text-theme-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-4 align-middle">
                                    <div class="py-10 text-center">
                                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">No SMS logs match these filters.</p>
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
            <button wire:click="createTemplate" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Add template</button>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-y border-gray-100 bg-gray-50/60 dark:border-gray-800">
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Key</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Content</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                            <th scope="col" class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($templates as $t)
                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 align-middle"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs font-medium text-brand-600 dark:bg-gray-800 dark:text-brand-400">{{ $t->key }}</code></td>
                                <td class="px-5 py-4 align-middle text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $t->name }}</td>
                                <td class="max-w-xs truncate px-5 py-4 align-middle text-theme-sm text-gray-500 dark:text-gray-400">{{ $t->content }}</td>
                                <td class="px-5 py-4 align-middle">
                                    @if($t->is_active)
                                        <span class="rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">Active</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs font-medium text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    <div class="flex flex-wrap items-center justify-end gap-1">
                                        <button wire:click="toggleTemplateActive({{ $t->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium transition {{ $t->is_active ? 'text-warning-600 hover:bg-warning-50 dark:text-warning-500 dark:hover:bg-warning-500/10' : 'text-success-600 hover:bg-success-50 dark:text-success-500 dark:hover:bg-success-500/10' }}">{{ $t->is_active ? 'Deactivate' : 'Activate' }}</button>
                                        <button wire:click="editTemplate({{ $t->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">Edit</button>
                                        <button wire:click="deleteTemplate({{ $t->id }})" wire:confirm="Delete this template?" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-error-600 transition hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-4 align-middle">
                                    <div class="py-10 text-center">
                                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">No SMS templates yet.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($templateViewMode === 'create')
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="cancelTemplateForm"></div>
                <div class="relative w-full max-w-md rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $templateId ? 'Edit template' : 'Add template' }}</h3>
                        <button type="button" wire:click="cancelTemplateForm" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <form wire:submit="saveTemplate" class="space-y-5">
                        <div>
                            <label for="tpl-key" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Key</label>
                            <input id="tpl-key" wire:model="templateKey" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="welcome_sms">
                            @error('templateKey')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="tpl-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Name</label>
                            <input id="tpl-name" wire:model="templateName" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            @error('templateName')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="tpl-content" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Content</label>
                            <textarea id="tpl-content" wire:model="templateContent" rows="4" class="min-h-24 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                            @error('templateContent')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                            <button type="button" wire:click="cancelTemplateForm" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                            <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Save template</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endif
</div>
