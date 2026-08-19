<div>
    <header class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
        <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Customer app</h1>
        <p class="mt-2 text-sm text-slate-500">Version policy, force update, maintenance mode, push notifications, and app usage.</p>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <section class="mb-6 grid gap-3 sm:grid-cols-3">
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Sessions (30d)</p><p class="mt-2 text-2xl font-black text-white">{{ $stats['sessions'] }}</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Crashes (30d)</p><p class="mt-2 text-2xl font-black text-rose-300">{{ $stats['crashes'] }}</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Active tenants (30d)</p><p class="mt-2 text-2xl font-black text-emerald-300">{{ $stats['active_users'] }}</p></div>
    </section>

    <form wire:submit="save" class="space-y-6">
        <div class="bc-panel space-y-4 p-5" style="border-radius:8px">
            <h2 class="font-bold text-white">App version policy</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="bc-label" for="ca-version">Current version</label><input id="ca-version" wire:model="currentVersion" class="bc-field">@error('currentVersion')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                <div><label class="bc-label" for="ca-min-version">Minimum supported version</label><input id="ca-min-version" wire:model="minimumSupportedVersion" class="bc-field">@error('minimumSupportedVersion')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
            </div>
            <label class="inline-flex items-center gap-3 text-sm text-slate-300"><input wire:model="forceUpdateEnabled" type="checkbox">Force update below minimum version</label>
        </div>

        <div class="bc-panel space-y-4 p-5" style="border-radius:8px">
            <h2 class="font-bold text-white">Maintenance mode</h2>
            <label class="inline-flex items-center gap-3 text-sm text-slate-300"><input wire:model.live="maintenanceModeEnabled" type="checkbox">Enable maintenance mode</label>
            @if($maintenanceModeEnabled)
                <div><label class="bc-label" for="ca-maintenance-msg">Message shown to users</label><textarea id="ca-maintenance-msg" wire:model="maintenanceMessage" rows="2" class="bc-field"></textarea>@error('maintenanceMessage')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
            @endif
        </div>

        <div class="bc-panel space-y-4 p-5" style="border-radius:8px">
            <h2 class="font-bold text-white">Push notifications</h2>
            <label class="inline-flex items-center gap-3 text-sm text-slate-300"><input wire:model="pushNotificationsEnabled" type="checkbox">Push notifications enabled</label>
        </div>

        <button type="submit" class="bc-primary">Save settings</button>
    </form>

    <div class="mt-6 bc-panel space-y-4 p-5" style="border-radius:8px">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-white">Recent app events</h2>
            <div class="flex gap-3">
                <button wire:click="logTestEvent('session_start')" class="text-xs font-semibold text-slate-300">Log test session</button>
                <button wire:click="logTestEvent('crash')" class="text-xs font-semibold text-rose-300">Log test crash</button>
                <button wire:click="logTestEvent('active_user')" class="text-xs font-semibold text-emerald-300">Log active tenant</button>
            </div>
        </div>
        <ul class="space-y-2 text-sm">
            @forelse($recentEvents as $event)
                <li class="flex items-center justify-between border-b border-white/10 pb-2">
                    <span class="capitalize {{ $event->type === 'crash' ? 'text-rose-300' : 'text-slate-300' }}">{{ str_replace('_', ' ', $event->type) }}</span>
                    <span class="text-xs text-slate-600">{{ $event->tenant?->name ?? 'Unattributed' }} · {{ $event->created_at->format('d M Y, H:i') }}</span>
                </li>
            @empty
                <li class="py-6 text-center text-slate-600">No app events recorded yet.</li>
            @endforelse
        </ul>
    </div>
</div>
