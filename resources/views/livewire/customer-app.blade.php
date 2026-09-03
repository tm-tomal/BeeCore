<div class="space-y-6">
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

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Sessions (30d)</p>
            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stats['sessions'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Crashes (30d)</p>
            <p class="mt-2 text-2xl font-bold text-error-600 dark:text-error-400">{{ $stats['crashes'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Active tenants (30d)</p>
            <p class="mt-2 text-2xl font-bold text-success-600 dark:text-success-400">{{ $stats['active_users'] }}</p>
        </div>
    </section>

    <form wire:submit="save" class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">App version policy</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="ca-version" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Current version</label>
                    <input id="ca-version" wire:model="currentVersion" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    @error('currentVersion')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="ca-min-version" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Minimum supported version</label>
                    <input id="ca-min-version" wire:model="minimumSupportedVersion" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    @error('minimumSupportedVersion')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>
            </div>
            <label class="mt-5 inline-flex cursor-pointer items-center gap-2.5 text-theme-sm text-gray-700 dark:text-gray-400"><input wire:model="forceUpdateEnabled" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 bg-transparent text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">Force update below minimum version</label>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Maintenance mode</h2>
            <div class="mt-5 space-y-5">
                <label class="inline-flex cursor-pointer items-center gap-2.5 text-theme-sm text-gray-700 dark:text-gray-400"><input wire:model.live="maintenanceModeEnabled" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 bg-transparent text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">Enable maintenance mode</label>
                @if($maintenanceModeEnabled)
                    <div>
                        <label for="ca-maintenance-msg" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Message shown to users</label>
                        <textarea id="ca-maintenance-msg" wire:model="maintenanceMessage" rows="2" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                        @error('maintenanceMessage')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Push notifications</h2>
            <label class="mt-5 inline-flex cursor-pointer items-center gap-2.5 text-theme-sm text-gray-700 dark:text-gray-400"><input wire:model="pushNotificationsEnabled" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 bg-transparent text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">Push notifications enabled</label>
        </div>

        <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Save settings</button>
    </form>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Recent app events</h2>
            <div class="flex flex-wrap gap-1">
                <button wire:click="logTestEvent('session_start')" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">Log test session</button>
                <button wire:click="logTestEvent('crash')" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-error-600 transition hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10">Log test crash</button>
                <button wire:click="logTestEvent('active_user')" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-success-600 transition hover:bg-success-50 dark:text-success-500 dark:hover:bg-success-500/10">Log active tenant</button>
            </div>
        </div>
        <div class="mt-4">
            @forelse($recentEvents as $event)
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-3 last:border-0 dark:border-gray-800">
                    <span class="text-theme-sm capitalize {{ $event->type === 'crash' ? 'font-medium text-error-600 dark:text-error-400' : 'font-medium text-gray-800 dark:text-white/90' }}">{{ str_replace('_', ' ', $event->type) }}</span>
                    <span class="shrink-0 text-theme-xs text-gray-500 dark:text-gray-400">{{ $event->tenant?->name ?? 'Unattributed' }} · {{ $event->created_at->format('d M Y, H:i') }}</span>
                </div>
            @empty
                <div class="py-10 text-center">
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">No app events recorded yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
