<div class="space-y-6">
    @php
        $serviceMeta = [
            'database' => ['icon' => 'database', 'tint' => 'bg-sky-500/10 text-sky-600 dark:bg-sky-500/15 dark:text-sky-400'],
            'cache' => ['icon' => 'zap', 'tint' => 'bg-amber-500/10 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400'],
            'queue' => ['icon' => 'clock', 'tint' => 'bg-violet-500/10 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400'],
            'scheduler' => ['icon' => 'repeat', 'tint' => 'bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400'],
            'storage' => ['icon' => 'box', 'tint' => 'bg-cyan-500/10 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-400'],
            'mail' => ['icon' => 'mail', 'tint' => 'bg-fuchsia-500/10 text-fuchsia-600 dark:bg-fuchsia-500/15 dark:text-fuchsia-400'],
            'session' => ['icon' => 'lock', 'tint' => 'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400'],
        ];
        $iconSvg = function (string $key): string {
            return match ($key) {
                'database' => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>',
                'zap' => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
                'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
                'repeat' => '<polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>',
                'box' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
                'mail' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
                'lock' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
                default => '<circle cx="12" cy="12" r="10"/>',
            };
        };
        $healthy = collect($results)->where('status', 'ok')->count();
        $degraded = collect($results)->where('status', 'degraded')->count();
        $down = collect($results)->count() - $healthy - $degraded;
    @endphp

    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">System health</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Live status of the database, cache, queue, scheduler, and storage.</p>
        </div>
        <button type="button" wire:click="runHeartbeatNow" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
            Refresh scheduler heartbeat
        </button>
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
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-success-500/10 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $healthy }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Healthy</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-warning-500/10 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold {{ $degraded ? 'text-warning-600 dark:text-warning-400' : 'text-gray-800 dark:text-white/90' }}">{{ $degraded }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Degraded</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-error-500/10 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold {{ $down ? 'text-error-600 dark:text-error-400' : 'text-gray-800 dark:text-white/90' }}">{{ $down }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Unavailable</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ collect($results)->count() }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Services checked</p>
            </div>
        </div>
    </section>

    @if(count($alerts))
        <div class="flex items-start gap-3 rounded-2xl border border-error-200 bg-error-50 px-4 py-3.5 dark:border-error-500/20 dark:bg-error-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-error-600 dark:stroke-error-400" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <div class="min-w-0">
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-error-700 dark:text-error-300">System alerts</p>
                <ul class="mt-1.5 space-y-1 text-theme-sm text-error-700 dark:text-error-300">
                    @foreach($alerts as $alert)<li>{{ $alert }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Service status -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($results as $service => $result)
            @php
                $meta = $serviceMeta[$service] ?? $serviceMeta['database'];
                $status = $result['status'] ?? 'unknown';
                $tone = match ($status) { 'ok' => ['dot' => 'bg-success-500', 'text' => 'text-success-600 dark:text-success-400', 'label' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500'], 'degraded' => ['dot' => 'bg-warning-500', 'text' => 'text-warning-600 dark:text-warning-400', 'label' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500'], default => ['dot' => 'bg-error-500', 'text' => 'text-error-600 dark:text-error-400', 'label' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500'] };
            @endphp
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $meta['tint'] }}">
                            <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $iconSvg($meta['icon']) !!}</svg>
                        </span>
                        <p class="text-theme-sm font-semibold capitalize text-gray-800 dark:text-white/90">{{ ucfirst(str_replace('_', ' ', $service)) }}</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-theme-xs font-semibold capitalize {{ $tone['label'] }}">
                        <span class="size-1.5 rounded-full {{ $tone['dot'] }}"></span>{{ ucfirst($status) }}
                    </span>
                </div>
                <p class="mt-4 text-theme-sm text-gray-500 dark:text-gray-400">{{ $result['detail'] }}</p>
            </div>
        @endforeach
    </section>
</div>
