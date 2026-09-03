<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">System health</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Live status of the database, cache, queue, scheduler, and storage.</p>
        </div>
        <button wire:click="runHeartbeatNow" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Refresh scheduler heartbeat</button>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    @if(count($alerts))
        <div class="rounded-xl border border-error-200 bg-error-50 px-4 py-3.5 dark:border-error-500/20 dark:bg-error-500/10">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-error-700 dark:text-error-300">System alerts</p>
            <ul class="mt-2 space-y-1 text-theme-sm text-error-700 dark:text-error-300">
                @foreach($alerts as $alert)
                    <li>{{ $alert }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Service status -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 md:gap-6">
        @foreach($results as $service => $result)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $service)) }}</p>
                    <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ match($result['status']) { 'ok' => 'bg-success-500', 'degraded' => 'bg-warning-500', default => 'bg-error-500' } }}"></span>
                </div>
                <p class="mt-3 text-title-sm font-bold {{ match($result['status']) { 'ok' => 'text-success-600 dark:text-success-500', 'degraded' => 'text-warning-600 dark:text-warning-500', default => 'text-error-600 dark:text-error-500' } }}">{{ ucfirst($result['status']) }}</p>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $result['detail'] }}</p>
            </div>
        @endforeach
    </section>
</div>
