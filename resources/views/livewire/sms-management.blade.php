<div class="space-y-6">
    @php
        $activeProviders = $providers->where('is_active', true)->count();
        $totalCredits = (int) $balances->sum('balance');
        $channelDot = fn (string $status): string => match ($status) {
            'delivered', 'sent' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
            'failed' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
            default => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
        };
        $iconChip = fn (string $tone) => 'grid size-10 shrink-0 place-items-center rounded-lg '.$tone;
    @endphp

    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">SMS management</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Providers, sender IDs, tenant credit balances, delivery logs, and templates.</p>
        </div>
        @if($tab === 'providers')
            <button type="button" wire:click="createProvider" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add provider
            </button>
        @elseif($tab === 'templates')
            <button type="button" wire:click="createTemplate" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add template
            </button>
        @endif
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Overview -->
    <section class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="{{ $iconChip('bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400') }}">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $providers->count() }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Providers</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="{{ $iconChip('bg-success-500/10 text-success-600 dark:bg-success-500/15 dark:text-success-400') }}">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $activeProviders }}<span class="text-base font-medium text-gray-400"> / {{ $providers->count() }}</span></p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Active</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="{{ $iconChip('bg-fuchsia-500/10 text-fuchsia-600 dark:bg-fuchsia-500/15 dark:text-fuchsia-400') }}">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7L9 18l-5-5"/><circle cx="3" cy="21" r="1"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($totalCredits) }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Credits across tenants</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="{{ $iconChip('bg-cyan-500/10 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-400') }}">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $templates->count() }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Templates</p>
            </div>
        </div>
    </section>

    <!-- Tabs -->
    <div class="flex flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-white p-1 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <button type="button" wire:click="$set('tab', 'providers')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'providers' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
            Providers
            <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'providers' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $providers->count() }}</span>
        </button>
        <button type="button" wire:click="$set('tab', 'balances')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'balances' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            Tenant balances
            <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'balances' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $balances->count() }}</span>
        </button>
        <button type="button" wire:click="$set('tab', 'logs')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'logs' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Delivery logs
            <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'logs' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $logs->total() }}</span>
        </button>
        <button type="button" wire:click="$set('tab', 'templates')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'templates' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Templates
            <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'templates' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $templates->count() }}</span>
        </button>
    </div>

    @if($tab === 'providers')
        <!-- Providers -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Provider</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sender ID</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Price / SMS</th>
                            <th class="px-5 py-3.5 text-center text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($providers as $p)
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $p->is_active ? 'bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400' : 'bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500' }}">
                                            <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $p->name }}</p>
                                            <p class="mt-0.5 truncate font-mono text-theme-xs text-gray-400 dark:text-gray-500">{{ $p->provider }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">{{ $p->sender_id ?? '—' }}</code></td>
                                <td class="px-5 py-4 text-right text-theme-sm font-medium text-gray-800 dark:text-white/90">৳{{ number_format($p->price_per_sms, 4) }}</td>
                                <td class="px-5 py-4 text-center">
                                    @if($p->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-500"><span class="size-1.5 rounded-full bg-success-500"></span>Active</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs font-semibold text-gray-500 dark:bg-white/[0.05] dark:text-gray-400"><span class="size-1.5 rounded-full bg-gray-400"></span>Inactive</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        <button type="button" wire:click="openTestSms({{ $p->id }})" title="Send a real test SMS" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-success-300 hover:bg-success-50 hover:text-success-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-success-500/40 dark:hover:bg-success-500/10 dark:hover:text-success-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                        </button>
                                        <button type="button" wire:click="toggleProviderActive({{ $p->id }})" title="{{ $p->is_active ? 'Deactivate' : 'Activate' }}" class="grid h-8 w-8 place-items-center rounded-lg border {{ $p->is_active ? 'border-warning-200 bg-warning-50 text-warning-600 hover:border-warning-300 hover:bg-warning-100 dark:border-warning-500/25 dark:bg-warning-500/10 dark:text-warning-400' : 'border-success-200 bg-success-50 text-success-600 hover:border-success-300 hover:bg-success-100 dark:border-success-500/25 dark:bg-success-500/10 dark:text-success-400' }}">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                        </button>
                                        <button type="button" wire:click="editProvider({{ $p->id }})" title="Edit provider" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                        <button type="button" wire:click="archiveProvider({{ $p->id }})" wire:confirm="Archive this provider?" title="Archive provider" class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                                        </span>
                                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No SMS providers configured</p>
                                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Add your first gateway to start sending SMS through BeeCore.</p>
                                        <button type="button" wire:click="createProvider" class="mt-4 inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Add provider</button>
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
                        <div class="flex items-center gap-3">
                            <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
                            </span>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $providerId ? 'Edit provider' : 'Add provider' }}</h3>
                                <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Credentials are encrypted at rest.</p>
                            </div>
                        </div>
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
                                <input id="sms-provider" wire:model="provider" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="twilio, smsq.global">
                                @error('provider')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="sms-sender" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Sender ID</label>
                                <input id="sms-sender" wire:model="senderId" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                @error('senderId')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div>
                            <label for="sms-price" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Price per SMS (BDT)</label>
                            <div class="relative max-w-xs">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-theme-sm text-gray-400">৳</span>
                                <input id="sms-price" wire:model="pricePerSms" type="number" step="0.0001" min="0" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-8 pr-4 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>
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

        @if($testProviderId)
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="cancelTestSms"></div>
                <div class="relative max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-success-500/10 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                                <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            </span>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Send test SMS</h3>
                                <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Sends a real SMS — charged to the {{ $providers->firstWhere('id', $testProviderId)?->name ?? 'provider' }} account.</p>
                            </div>
                        </div>
                        <button type="button" wire:click="cancelTestSms" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <form wire:submit="sendTestSms" class="space-y-5">
                        <div>
                            <label for="test-recipient" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Recipient mobile</label>
                            <input id="test-recipient" wire:model="testRecipient" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="01XXXXXXXXX">
                            @error('testRecipient')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="test-message" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Message</label>
                            <textarea id="test-message" wire:model="testMessage" rows="3" class="min-h-24 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                            @error('testMessage')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                            <button type="button" wire:click="cancelTestSms" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                            <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                Send SMS
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @elseif($tab === 'balances')
        <!-- Tenant balances -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                </span>
                <div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Add SMS credit</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Credit a tenant's SMS wallet so their sends are covered.</p>
                </div>
            </div>
            <form wire:submit="addCredit" class="mt-5 grid gap-3 sm:grid-cols-3">
                <select wire:model="creditTenantId" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">Select tenant</option>
                    @foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach
                </select>
                <div class="relative">
                    <input wire:model="creditAmount" type="number" min="1" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="Credits">
                    <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-theme-xs text-gray-400">SMS</span>
                </div>
                <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Add credit</button>
            </form>
            @error('creditTenantId')<p class="mt-2 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tenant</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($balances as $balance)
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-brand-500/10 text-theme-sm font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ strtoupper(substr($balance->tenant->name, 0, 1)) }}</span>
                                        <p class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $balance->tenant->name }}</p>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-bold {{ $balance->balance > 0 ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.05] dark:text-gray-400' }}">{{ number_format($balance->balance) }} credits</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-5 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                        </span>
                                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No tenant SMS balances yet</p>
                                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Credit a tenant above; balances also fill automatically when SMS add-ons activate.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($tab === 'logs')
        <!-- Delivery logs -->
        <section class="grid grid-cols-2 gap-4 xl:grid-cols-3">
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-success-500/10 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
                <div>
                    <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ number_format($report['sent']) }}</p>
                    <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Sent / delivered</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-error-500/10 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                </span>
                <div>
                    <p class="text-2xl font-bold {{ $report['failed'] ? 'text-error-600 dark:text-error-400' : 'text-gray-800 dark:text-white/90' }}">{{ number_format($report['failed']) }}</p>
                    <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Failed</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </span>
                <div>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white/90">৳{{ number_format($report['cost'], 2) }}</p>
                    <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Total cost</p>
                </div>
            </div>
        </section>

        <div class="flex flex-wrap items-end justify-between gap-3">
            <div class="max-w-xs">
                <label for="sms-status-filter" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
                <select id="sms-status-filter" wire:model.live="statusFilter" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">All statuses</option>
                    <option value="queued">Queued</option>
                    <option value="sent">Sent</option>
                    <option value="delivered">Delivered</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
            <p class="text-theme-xs text-gray-400 dark:text-gray-500">{{ $logs->total() }} message{{ $logs->total() === 1 ? '' : 's' }}</p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tenant</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Provider</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Recipient</th>
                            <th class="px-5 py-3.5 text-center text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Cost</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sent at</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($logs as $log)
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $log->tenant?->name ?? 'Platform' }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $log->provider?->name ?? '—' }}</td>
                                <td class="px-5 py-4"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">{{ $log->recipient }}</code></td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-theme-xs font-semibold capitalize {{ $channelDot($log->status) }}">
                                        <span class="size-1.5 rounded-full bg-current"></span>{{ $log->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right text-theme-sm font-medium text-gray-800 dark:text-white/90">৳{{ number_format($log->cost, 4) }}</td>
                                <td class="px-5 py-4 text-right text-theme-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        </span>
                                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No SMS logs match these filters</p>
                                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">SMS deliveries are recorded here as they are sent.</p>
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
        <!-- Templates -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Template</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Content</th>
                            <th class="px-5 py-3.5 text-center text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($templates as $t)
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-fuchsia-500/10 text-fuchsia-600 dark:bg-fuchsia-500/15 dark:text-fuchsia-400">
                                            <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $t->name }}</p>
                                            <p class="mt-0.5 truncate font-mono text-theme-xs text-gray-400 dark:text-gray-500">{{ $t->key }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="max-w-sm truncate px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400" title="{{ $t->content }}">{{ $t->content }}</td>
                                <td class="px-5 py-4 text-center">
                                    @if($t->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-500"><span class="size-1.5 rounded-full bg-success-500"></span>Active</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs font-semibold text-gray-500 dark:bg-white/[0.05] dark:text-gray-400"><span class="size-1.5 rounded-full bg-gray-400"></span>Inactive</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        <button type="button" wire:click="toggleTemplateActive({{ $t->id }})" title="{{ $t->is_active ? 'Deactivate' : 'Activate' }}" class="grid h-8 w-8 place-items-center rounded-lg border {{ $t->is_active ? 'border-warning-200 bg-warning-50 text-warning-600 hover:border-warning-300 hover:bg-warning-100 dark:border-warning-500/25 dark:bg-warning-500/10 dark:text-warning-400' : 'border-success-200 bg-success-50 text-success-600 hover:border-success-300 hover:bg-success-100 dark:border-success-500/25 dark:bg-success-500/10 dark:text-success-400' }}">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                        </button>
                                        <button type="button" wire:click="editTemplate({{ $t->id }})" title="Edit template" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                        <button type="button" wire:click="deleteTemplate({{ $t->id }})" wire:confirm="Delete this template?" title="Delete template" class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                        </span>
                                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No SMS templates yet</p>
                                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Reusable message templates keep tenant communication consistent.</p>
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
                        <div class="flex items-center gap-3">
                            <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-fuchsia-500/10 text-fuchsia-600 dark:bg-fuchsia-500/15 dark:text-fuchsia-400">
                                <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </span>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $templateId ? 'Edit template' : 'Add template' }}</h3>
                        </div>
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
