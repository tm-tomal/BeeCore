<div class="space-y-6">
    @if($viewMode === 'index')
        <!-- Page header -->
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
                <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Payment gateways</h1>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Register the collection providers BeeCore accepts — bKash, Nagad, Stripe and Bank — with their credentials and webhook settings.</p>
            </div>
            <button wire:click="create" class="inline-flex min-h-10 shrink-0 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Add gateway
            </button>
        </header>

        @if(session()->has('message'))
            <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
                <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
            </div>
        @endif

        <!-- Summary metrics -->
        <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Total gateways</p>
                <p class="mt-1.5 text-title-sm font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Active</p>
                <p class="mt-1.5 text-title-sm font-bold text-success-600 dark:text-success-400">{{ $stats['active'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Sandbox mode</p>
                <p class="mt-1.5 text-title-sm font-bold text-warning-600 dark:text-warning-400">{{ $stats['sandbox'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Success rate (tests)</p>
                <p class="mt-1.5 text-title-sm font-bold text-gray-900 dark:text-white">{{ $stats['success_rate'] !== null ? $stats['success_rate'].'%' : '—' }}</p>
            </div>
        </div>

        <!-- Gateway cards -->
        @if($gateways->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white/50 px-6 py-16 text-center dark:border-gray-700 dark:bg-white/[0.02]">
                <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400">
                    <svg class="size-7 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </span>
                <h2 class="mt-4 text-base font-semibold text-gray-800 dark:text-white/90">No payment gateways configured yet</h2>
                <p class="mx-auto mt-1 max-w-sm text-theme-sm text-gray-500 dark:text-gray-400">Add your first collection method — bKash, Nagad, Stripe or a Bank Account — to start accepting BeeCore payments.</p>
                <button wire:click="create" class="mt-5 inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Add gateway</button>
            </div>
        @else
            <div class="grid gap-4 lg:grid-cols-2">
                @foreach($gateways as $gateway)
                    @php
                        $meta = $providers[$gateway->provider] ?? null;
                        $total = $gateway->success_count + $gateway->failed_count;
                        $successPct = $total > 0 ? (int) round($gateway->success_count / $total * 100) : null;
                        $configured = count(array_filter($gateway->credentials ?? [], fn ($v) => filled($v)));
                    @endphp
                    <article class="flex flex-col rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
                        <div class="flex items-start gap-4">
                            <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-gradient-to-br text-base font-bold text-white shadow-theme-xs {{ $meta['avatar'] ?? 'from-gray-400 to-gray-500' }}">
                                {{ $meta['letter'] ?? strtoupper(substr($gateway->provider, 0, 2)) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-theme-sm font-bold text-gray-900 dark:text-white">{{ $gateway->name }}</h3>
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $meta['chip'] ?? 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400' }}">{{ $meta['label'] ?? ucfirst($gateway->provider) }}</span>
                                </div>
                                <p class="mt-0.5 truncate text-theme-xs text-gray-500 dark:text-gray-400">{{ $gateway->slug }}</p>
                            </div>
                            <div class="flex shrink-0 flex-col items-end gap-2">
                                @if(($meta['mode_supported'] ?? true) !== false)
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $gateway->mode === 'sandbox' ? 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500' : 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' }}">{{ $gateway->mode }}</span>
                                @endif
                                <button
                                    type="button"
                                    role="switch"
                                    aria-checked="{{ $gateway->is_active ? 'true' : 'false' }}"
                                    wire:click="toggleActive({{ $gateway->id }})"
                                    title="{{ $gateway->is_active ? 'Deactivate gateway' : 'Activate gateway' }}"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full transition-colors duration-200 focus:outline-hidden focus:ring-3 focus:ring-brand-500/20 {{ $gateway->is_active ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700' }}"
                                >
                                    <span class="inline-block size-4 transform rounded-full bg-white shadow-theme-xs transition-transform duration-200 {{ $gateway->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-1.5">
                            @php
                                $visibleFields = ($meta['fields'] ?? []);
                                $fieldLabels = collect($visibleFields)->pluck('label', 'key');
                                $secretFieldKeys = collect($visibleFields)->where('secret', true)->pluck('key')->all();
                            @endphp
                            @forelse(($gateway->credentials ?? []) as $key => $value)
                                @if(!filled($value)) @continue @endif
                                @if(($meta['fields'] ?? []) && !isset($fieldLabels[$key])) @continue @endif
                                @php
                                    $looksSecret = in_array($key, $secretFieldKeys, true) || (bool) preg_match('/secret|password|token|private|(^|_|\.)key$/i', $key);
                                @endphp
                                <span class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-theme-xs text-gray-600 dark:border-gray-800 dark:bg-white/[0.05] dark:text-gray-400">
                                    {{ $fieldLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}
                                    @if($looksSecret)
                                        <span class="font-medium tracking-widest text-gray-500 dark:text-gray-400">••••••</span>
                                    @else
                                        <span class="max-w-[9rem] truncate font-medium text-gray-800 dark:text-white/90">{{ $value }}</span>
                                    @endif
                                </span>
                            @empty
                                <span class="text-theme-xs text-gray-400 dark:text-gray-500">{{ $configured }} credential field{{ $configured === 1 ? '' : 's' }} stored</span>
                            @endforelse
                            @if($gateway->webhook_url)
                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-brand-100 bg-brand-50 px-2.5 py-1 text-theme-xs text-brand-600 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-400">
                                    <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                    Webhook
                                </span>
                            @endif
                        </div>

                        <div class="mt-5 flex items-center justify-between gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                            <div class="text-theme-xs text-gray-500 dark:text-gray-400">
                                @if($total > 0)
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $total }} test{{ $total === 1 ? '' : 's' }}</span>
                                    <span class="mx-1.5 text-gray-300 dark:text-gray-700">·</span>
                                    <span class="text-success-600 dark:text-success-400">{{ $gateway->success_count }} ok</span>
                                    <span class="mx-1.5 text-gray-300 dark:text-gray-700">·</span>
                                    <span class="text-error-600 dark:text-error-500">{{ $gateway->failed_count }} failed</span>
                                    <span class="mx-1.5 text-gray-300 dark:text-gray-700">·</span>
                                    <span class="font-semibold {{ $successPct >= 90 ? 'text-success-600 dark:text-success-400' : ($successPct >= 50 ? 'text-warning-600 dark:text-warning-400' : 'text-error-600 dark:text-error-500') }}">{{ $successPct }}%</span>
                                @else
                                    <span>No connection tests yet</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" wire:click="testConnection({{ $gateway->id }})" title="Test connection" class="grid size-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                    <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                </button>
                                <button type="button" wire:click="viewLogs({{ $gateway->id }})" title="View logs" class="grid size-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                    <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                                </button>
                                <button type="button" wire:click="edit({{ $gateway->id }})" title="Edit gateway" class="grid size-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                    <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button
                                    type="button"
                                    wire:click="archive({{ $gateway->id }})"
                                    wire:confirm="Archive this gateway? It will be removed from active gateways."
                                    title="Archive gateway"
                                    class="grid size-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 hover:text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400 dark:hover:border-error-500/40 dark:hover:bg-error-500/15 dark:hover:text-error-300"
                                >
                                    <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        @if($logsForId)
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="gateway-logs-title">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeLogs"></div>
                <div class="relative max-h-[80vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-5 flex items-center justify-between">
                        <h3 id="gateway-logs-title" class="text-lg font-semibold text-gray-800 dark:text-white/90">Gateway logs</h3>
                        <button type="button" wire:click="closeLogs" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($logs as $log)
                            <li class="py-3.5">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="inline-flex items-center gap-2 text-theme-sm font-semibold {{ $log->status === 'success' ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400' }}">
                                        @if($log->status === 'success')
                                            <span class="grid size-5 place-items-center rounded-full bg-success-100 text-success-600 dark:bg-success-500/15"><svg class="size-3 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
                                        @else
                                            <span class="grid size-5 place-items-center rounded-full bg-error-100 text-error-600 dark:bg-error-500/15"><svg class="size-3 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span>
                                        @endif
                                        {{ $log->event }}
                                    </span>
                                    <span class="shrink-0 text-theme-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->format('d M Y, H:i') }}</span>
                                </div>
                                @if($log->metadata)
                                    <div class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ implode(' · ', array_filter(array_map(fn ($v) => is_scalar($v) ? (string) $v : '', $log->metadata))) }}</div>
                                @endif
                            </li>
                        @empty
                            <li class="py-6 text-center text-theme-sm text-gray-500 dark:text-gray-400">No logs recorded yet.</li>
                        @endforelse
                    </ul>
                    <div class="mt-5 flex justify-end"><button wire:click="closeLogs" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Close</button></div>
                </div>
            </div>
        @endif
    @else
        @php $currentMeta = $catalogProvider; @endphp

        <!-- Page header -->
        <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">Payment gateways</p>
                <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $gatewayId ? 'Edit gateway' : 'Add payment gateway' }}</h1>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $gatewayId ? 'Update the provider, mode and credentials for this gateway.' : 'Pick a provider, fill the credentials and activate the gateway.' }}</p>
            </div>
            <div class="flex shrink-0 items-center gap-3">
                <button wire:click="cancel" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to Gateways
                </button>
            </div>
        </header>

        <form wire:submit="save" class="space-y-6">
            <!-- Provider -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Provider</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Choose the collection provider this gateway belongs to.</p>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach($providers as $key => $preset)
                        <button type="button" wire:click="selectProvider('{{ $key }}')"
                            class="flex flex-col items-center gap-2 rounded-xl border p-3.5 text-center transition"
                            @class([
                                'border-brand-500 bg-brand-50/60 ring-2 ring-brand-500/20 dark:border-brand-500 dark:bg-brand-500/10' => $provider === $key && $isKnownProvider,
                                'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700' => !($provider === $key && $isKnownProvider),
                            ])>
                            <span class="grid size-10 place-items-center rounded-lg bg-gradient-to-br text-theme-sm font-bold text-white {{ $preset['avatar'] }}">{{ $preset['letter'] }}</span>
                            <span class="text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ $preset['label'] }}</span>
                        </button>
                    @endforeach
                </div>
                <button type="button" wire:click="selectProvider('custom')"
                    class="mt-3 inline-flex items-center gap-1.5 text-theme-xs font-medium text-gray-500 transition hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-400">
                    <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    {{ $gatewayId && !$isKnownProvider ? 'Using a legacy/custom provider' : 'Use a custom or other provider' }}
                </button>

                @if($isKnownProvider)
                    <div class="mt-4 flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50/60 p-3.5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-gradient-to-br text-theme-sm font-bold text-white {{ $currentMeta['avatar'] }}">{{ $currentMeta['letter'] }}</span>
                        <div class="min-w-0">
                            <p class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $currentMeta['label'] }}</p>
                            <p class="mt-0.5 text-theme-xs leading-4 text-gray-500 dark:text-gray-400">{{ $currentMeta['hint'] }}</p>
                        </div>
                    </div>
                @else
                    <div class="mt-4">
                        <label for="gw-provider" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Provider key</label>
                        <input id="gw-provider" wire:model="provider" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="stripe, bkash, nagad, sslcommerz, manual">
                        @error('provider') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                @endif
            </section>

            <!-- Gateway details -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Gateway details</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Name, identifier, environment and webhook routing.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="gw-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Name</label>
                        <input id="gw-name" wire:model.live="name" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('name') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="gw-slug" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Slug</label>
                        <input id="gw-slug" wire:model="slug" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">Auto-generated from the name.</p>
                        @error('slug') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                @if($isKnownProvider && $currentMeta['mode_supported'] === false)
                    <p class="mt-4 rounded-lg border border-success-100 bg-success-50 px-3.5 py-2.5 text-theme-xs text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300">Bank transfers are manual — no sandbox mode or webhook is needed.</p>
                @else
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="gw-mode" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Mode</label>
                            <select id="gw-mode" wire:model="mode" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                <option value="sandbox">Sandbox / test</option>
                                <option value="live">Live</option>
                            </select>
                        </div>
                        <div>
                            <label for="gw-webhook" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Webhook URL</label>
                            <input id="gw-webhook" wire:model="webhookUrl" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="https://...">
                            @error('webhookUrl') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="gw-secret" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Webhook signing secret {{ $gatewayId ? '(leave blank to keep current)' : '' }}</label>
                        <input id="gw-secret" wire:model="webhookSecret" type="password" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    </div>
                @endif
            </section>

            <!-- Credentials -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Credentials</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">
                        Stored encrypted at rest{{ $isKnownProvider && $gatewayId ? ' — leave a secret field blank to keep the stored value' : '' }}.
                    </p>
                </div>
                @if($isKnownProvider)
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach($currentMeta['fields'] as $field)
                            <div @if($field['type'] === 'textarea') class="sm:col-span-2" @endif>
                                <label for="gw-cred-{{ $field['key'] }}" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ $field['label'] }}</label>
                                @if($field['type'] === 'textarea')
                                    <textarea id="gw-cred-{{ $field['key'] }}" wire:model="credentialValues.{{ $field['key'] }}" rows="3" placeholder="{{ $field['placeholder'] }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 font-mono text-theme-sm text-gray-800 shadow-theme-xs placeholder:font-sans placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"></textarea>
                                @else
                                    <input id="gw-cred-{{ $field['key'] }}" wire:model="credentialValues.{{ $field['key'] }}" type="{{ $field['type'] }}" placeholder="{{ $field['placeholder'] }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 font-mono text-theme-sm text-gray-800 shadow-theme-xs placeholder:font-sans placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                @endif
                                @error('credentialValues.'.$field['key']) <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                @else
                    <div>
                        <label for="gw-credentials" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Credentials <span class="text-theme-xs text-gray-500 dark:text-gray-400">(one <code>key=value</code> per line, encrypted at rest)</span></label>
                        <textarea id="gw-credentials" wire:model="credentialsText" rows="6" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 font-mono text-theme-sm text-gray-800 shadow-theme-xs placeholder:font-sans placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="api_key=...&#10;api_secret=..."></textarea>
                    </div>
                @endif
            </section>

            <!-- Actions -->
            <div class="sticky bottom-4 flex flex-col-reverse gap-3 rounded-2xl border border-gray-200 bg-white/95 px-5 py-4 shadow-theme-lg backdrop-blur sm:flex-row sm:items-center sm:justify-end dark:border-gray-800 dark:bg-gray-900/95">
                <button type="button" wire:click="cancel" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                    <span wire:loading.remove wire:target="save">{{ $gatewayId ? 'Save changes' : 'Create gateway' }}</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </form>
    @endif
</div>
