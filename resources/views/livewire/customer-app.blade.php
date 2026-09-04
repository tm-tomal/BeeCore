<div class="space-y-6">
    @php
        $eventIcon = fn (string $type): string => match ($type) {
            'session_start' => '<path d="M23 12a9 9 0 1 1-9-9"/><polyline points="23 3 12 14 9 11"/>',
            'crash' => '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
            default => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        };
        $eventTone = fn (string $type): string => match ($type) {
            'crash' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400',
            'active_user' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400',
            default => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
        };
    @endphp

    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Customer app</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Version policy, force update, maintenance mode, push notifications, and app usage.</p>
        </div>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Overview -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($stats['sessions']) }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Sessions · last 30 days</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-error-500/10 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold {{ $stats['crashes'] > 0 ? 'text-error-600 dark:text-error-400' : 'text-gray-800 dark:text-white/90' }}">{{ number_format($stats['crashes']) }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Crashes · last 30 days</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-success-500/10 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ number_format($stats['active_users']) }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Active tenants · last 30 days</p>
            </div>
        </div>
    </section>

    <form wire:submit="save" class="space-y-6">
        <!-- Version policy -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </span>
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">App version policy</h2>
            </div>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="ca-version" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Current version</label>
                    <input id="ca-version" wire:model="currentVersion" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="1.0.0">
                    @error('currentVersion')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="ca-min-version" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Minimum supported version</label>
                    <input id="ca-min-version" wire:model="minimumSupportedVersion" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="1.0.0">
                    @error('minimumSupportedVersion')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>
            </div>
            <label class="mt-5 inline-flex cursor-pointer items-center gap-3 text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                <span class="relative">
                    <input wire:model="forceUpdateEnabled" type="checkbox" class="peer sr-only">
                    <span class="block h-6 w-11 rounded-full bg-gray-200 transition peer-checked:bg-warning-500 dark:bg-gray-700"></span>
                    <span class="absolute left-1 top-1 size-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                </span>
                Force update below minimum version
            </label>
            <p class="mt-2 text-theme-xs text-gray-400 dark:text-gray-500">When on, devices running older than {{ $minimumSupportedVersion ?: 'the minimum' }} are asked to update before they can continue.</p>
        </div>

        <!-- Maintenance mode -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $maintenanceModeEnabled ? 'bg-warning-500/10 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.05] dark:text-gray-400' }}">
                        <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Maintenance mode</h2>
                        <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Block customer app access with a friendly message while you work.</p>
                    </div>
                </div>
                <label class="inline-flex shrink-0 cursor-pointer items-center gap-2.5 text-theme-sm font-medium {{ $maintenanceModeEnabled ? 'text-warning-600 dark:text-warning-400' : 'text-gray-500 dark:text-gray-400' }}">
                    <span class="relative">
                        <input wire:model.live="maintenanceModeEnabled" type="checkbox" class="peer sr-only">
                        <span class="block h-6 w-11 rounded-full bg-gray-200 transition peer-checked:bg-warning-500 dark:bg-gray-700"></span>
                        <span class="absolute left-1 top-1 size-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                    </span>
                    {{ $maintenanceModeEnabled ? 'Active' : 'Off' }}
                </label>
            </div>
            @if($maintenanceModeEnabled)
                <div class="mt-5 rounded-xl border border-warning-200 bg-warning-50/70 p-4 dark:border-warning-500/20 dark:bg-warning-500/10">
                    <label for="ca-maintenance-msg" class="mb-1.5 block text-theme-sm font-medium text-warning-700 dark:text-warning-300">Message shown to users</label>
                    <textarea id="ca-maintenance-msg" wire:model="maintenanceMessage" rows="2" class="w-full rounded-lg border border-warning-200 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-warning-500/60 focus:border-warning-400 focus:ring-3 focus:ring-warning-500/15 focus:outline-hidden dark:border-warning-500/30 dark:bg-gray-900 dark:text-white/90" placeholder="We'll be back shortly..."></textarea>
                    @error('maintenanceMessage')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>
            @endif
        </div>

        <!-- Push notifications -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-violet-500/10 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400">
                        <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Push notifications</h2>
                        <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Let ISPs reach their subscribers through the app.</p>
                    </div>
                </div>
                <label class="inline-flex shrink-0 cursor-pointer items-center gap-2.5 text-theme-sm font-medium {{ $pushNotificationsEnabled ? 'text-success-600 dark:text-success-400' : 'text-gray-500 dark:text-gray-400' }}">
                    <span class="relative">
                        <input wire:model="pushNotificationsEnabled" type="checkbox" class="peer sr-only">
                        <span class="block h-6 w-11 rounded-full bg-gray-200 transition peer-checked:bg-success-500 dark:bg-gray-700"></span>
                        <span class="absolute left-1 top-1 size-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                    </span>
                    {{ $pushNotificationsEnabled ? 'Enabled' : 'Disabled' }}
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="mr-auto text-theme-xs text-gray-400 dark:text-gray-500">Saved instantly for every installed customer app build.</p>
            <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save settings
            </button>
        </div>
    </form>

    <!-- Recent events -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Recent app events</h2>
                <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Latest telemetry reported by customer apps.</p>
            </div>
            <div class="flex flex-wrap gap-1.5">
                <button type="button" wire:click="logTestEvent('session_start')" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-theme-xs font-medium text-gray-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                    <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Log test session
                </button>
                <button type="button" wire:click="logTestEvent('crash')" class="inline-flex items-center gap-1.5 rounded-lg border border-error-200 bg-error-50 px-3 py-2 text-theme-xs font-medium text-error-600 transition hover:border-error-300 hover:bg-error-100 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">
                    <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Log test crash
                </button>
                <button type="button" wire:click="logTestEvent('active_user')" class="inline-flex items-center gap-1.5 rounded-lg border border-success-200 bg-success-50 px-3 py-2 text-theme-xs font-medium text-success-600 transition hover:border-success-300 hover:bg-success-100 dark:border-success-500/25 dark:bg-success-500/10 dark:text-success-400">
                    <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Log active tenant
                </button>
            </div>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($recentEvents as $event)
                <div class="flex items-center justify-between gap-4 px-5 py-3.5 transition-colors hover:bg-gray-50/60 dark:hover:bg-white/[0.02]">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="grid size-8 shrink-0 place-items-center rounded-lg {{ $eventTone($event->type) }}">
                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $eventIcon($event->type) !!}</svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-theme-sm font-medium capitalize text-gray-800 dark:text-white/90">{{ str_replace('_', ' ', $event->type) }}</span>
                            <span class="mt-0.5 block text-theme-xs text-gray-400 dark:text-gray-500">{{ $event->tenant?->name ?? 'Unattributed' }}</span>
                        </span>
                    </div>
                    <span class="shrink-0 rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs font-medium text-gray-500 dark:bg-white/[0.05] dark:text-gray-400">{{ $event->created_at->format('d M Y, H:i') }}</span>
                </div>
            @empty
                <div class="px-5 py-14 text-center">
                    <div class="mx-auto max-w-xs">
                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </span>
                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No app events recorded yet</p>
                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Events appear here once customer apps start reporting telemetry.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
