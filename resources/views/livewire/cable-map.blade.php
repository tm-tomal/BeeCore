<div class="space-y-6">
    <!-- Page header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">{{ __('Network & coverage') }}</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ __('Cable & fiber map') }}</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Map your main fiber, splitters and ports so you can spot cut areas instantly.') }}</p>
        </div>
        @if($view === 'overview')
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" wire:click="createRoute" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    {{ __('Add fiber route') }}
                </button>
                <button type="button" wire:click="createSplitter" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    {{ __('Add splitter') }}
                </button>
                <button type="button" wire:click="createIssue('route')" class="inline-flex items-center justify-center gap-2 rounded-lg bg-error-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-error-600">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    {{ __('Report issue') }}
                </button>
            </div>
        @endif
    </div>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="flex items-start gap-3 rounded-xl border border-error-200 bg-error-50 px-4 py-3 dark:border-error-500/20 dark:bg-error-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-error-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <p class="text-theme-sm text-error-700 dark:text-error-300">{{ session('error') }}</p>
        </div>
    @endif

    @if($view === 'overview')
        <!-- Stats -->
        <section class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-5">
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Fiber routes') }}</p>
                <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($stats['routes']) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Splitters') }}</p>
                <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($stats['splitters']) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Used ports') }}</p>
                <p class="mt-1 text-2xl font-bold text-success-600 dark:text-success-400">{{ number_format($stats['used_ports']) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Free ports') }}</p>
                <p class="mt-1 text-2xl font-bold text-gray-400">{{ number_format($stats['free_ports']) }}</p>
            </div>
            <div class="rounded-2xl border {{ $stats['open_issues'] > 0 ? 'border-error-200 bg-error-50/60 dark:border-error-500/20 dark:bg-error-500/10' : 'border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]' }} p-4">
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Open issues') }}</p>
                <p class="mt-1 text-2xl font-bold {{ $stats['open_issues'] > 0 ? 'text-error-600 dark:text-error-400' : 'text-gray-800 dark:text-white/90' }}">{{ number_format($stats['open_issues']) }}</p>
            </div>
        </section>

        <!-- Open issues -->
        @if($openIssues->isNotEmpty())
            <section class="rounded-2xl border border-error-200 bg-error-50/50 p-4 dark:border-error-500/20 dark:bg-error-500/5 sm:p-5">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h2 class="flex items-center gap-2 text-base font-semibold text-gray-800 dark:text-white/90">
                        <svg class="size-5 stroke-error-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                        {{ __('Active issues') }}
                    </h2>
                </div>
                <div class="space-y-3">
                    @foreach($openIssues as $issue)
                        @php $affected = $affectedByIssue($issue); @endphp
                        <details class="group rounded-xl border border-error-200 bg-white dark:border-error-500/25 dark:bg-gray-900">
                            <summary class="flex cursor-pointer list-none flex-wrap items-center justify-between gap-3 px-4 py-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $issue->issue_type === 'fiber_cut' ? 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400' : 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400' }}">
                                        {{ $issue->issue_type === 'fiber_cut' ? __('Fiber cut') : ($issue->issue_type === 'maintenance' ? __('Maintenance') : __('Other')) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $issue->title }}</p>
                                        <p class="truncate text-theme-xs text-gray-500 dark:text-gray-400">
                                            {{ $issue->route?->name ?? $issue->splitter?->name ?? '—' }} · {{ $issue->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                                <span class="shrink-0 text-theme-xs font-semibold {{ $affected->count() > 0 ? 'text-error-600 dark:text-error-400' : 'text-gray-400' }}">
                                    {{ $affected->count() > 0 ? __(':count affected', ['count' => $affected->count()]) : __('No linked customers') }}
                                </span>
                            </summary>
                            @if($issue->description)
                                <p class="border-t border-gray-100 px-4 py-2.5 text-theme-xs text-gray-600 dark:border-gray-800 dark:text-gray-300">{{ $issue->description }}</p>
                            @endif
                            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">
                                @if($affected->isNotEmpty())
                                    <p class="mb-2 text-theme-xs font-medium uppercase tracking-wide text-gray-400">{{ __('Affected customers') }}</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($affected as $customer)
                                            <a href="{{ route('customers') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50/60 px-2.5 py-1 text-theme-xs text-gray-700 hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:bg-white/[0.02] dark:text-gray-300">
                                                <span class="grid size-5 place-items-center rounded-full bg-brand-50 text-[10px] font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                                                {{ $customer->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-theme-xs text-gray-400">{{ __('No customers are linked to this point yet.') }}</p>
                                @endif
                                <div class="mt-3 flex justify-end">
                                    <button type="button" wire:click="resolveIssue({{ $issue->id }})" class="inline-flex items-center gap-1.5 rounded-lg bg-success-500 px-3 py-1.5 text-theme-xs font-medium text-white transition hover:bg-success-600">
                                        <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        {{ __('Mark resolved') }}
                                    </button>
                                </div>
                            </div>
                        </details>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Map: routes -->
        @if($routes->isEmpty() && $unassignedSplitters->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white py-16 text-center dark:border-gray-700 dark:bg-white/[0.02]">
                <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-brand-50 text-brand-500 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="size-7 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
                </span>
                <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-white/90">{{ __('No cable map yet') }}</h3>
                <p class="mx-auto mt-1 max-w-sm text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Start by adding your main fiber routes, then attach splitters and link customers to ports.') }}</p>
                <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
                    <button type="button" wire:click="createRoute" class="rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">{{ __('Add first fiber route') }}</button>
                    <button type="button" wire:click="createSplitter" class="rounded-lg border border-gray-300 px-4 py-2.5 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">{{ __('Add first splitter') }}</button>
                </div>
            </div>
        @else
            @php $mappedPoints = count($mapPayload['routes']) + count($mapPayload['splitters']); @endphp
            <!-- Visual map -->
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <h2 class="flex items-center gap-2 text-base font-semibold text-gray-800 dark:text-white/90">
                            <svg class="size-5 stroke-brand-500 dark:stroke-brand-400" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
                            {{ __('Live network map') }}
                        </h2>
                        <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Every fiber route and splitter on one map — click a marker to edit it or report an issue.') }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-theme-xs text-gray-500 dark:text-gray-400">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="grid size-5 place-items-center rounded-full bg-brand-500 text-[9px] font-extrabold text-white">F</span>
                            {{ __('Fiber route') }}
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="size-3.5 rounded-full border-2 border-white bg-success-500 shadow-sm"></span>
                            {{ __('Splitter available') }}
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="size-3.5 rounded-full border-2 border-white bg-warning-500 shadow-sm"></span>
                            {{ __('Splitter full') }}
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="size-3.5 rounded-full border-2 border-white bg-error-500 shadow-sm"></span>
                            {{ __('Has open issue') }}
                        </span>
                    </div>
                </div>
                <div
                    data-cable-map
                    data-sig="{{ md5(json_encode($mapPayload)) }}"
                    data-payload='@json($mapPayload)'
                    class="relative h-[520px] w-full"
                >
                    @if($mappedPoints === 0)
                        <div class="pointer-events-none absolute inset-x-0 top-6 z-[900] flex justify-center px-4">
                            <p class="rounded-full border border-gray-200 bg-white/95 px-4 py-2 text-theme-xs font-medium text-gray-600 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900/95 dark:text-gray-300">{{ __('No points pinned yet — edit a route or splitter below and click the map to set its location.') }}</p>
                        </div>
                    @endif
                    <div wire:ignore class="cable-map-canvas absolute inset-0"></div>
                </div>
            </section>

            <div class="space-y-5">
                @foreach($routes as $route)
                    <section class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                        <!-- Route header -->
                        <div class="flex flex-col gap-3 border-b border-gray-100 p-5 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">
                            <div class="flex min-w-0 items-center gap-3.5">
                                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand-500 to-indigo-500 text-white">
                                    <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="5" cy="5" r="3"/><circle cx="19" cy="5" r="3"/><circle cx="5" cy="19" r="3"/><circle cx="19" cy="19" r="3"/><path d="M7.5 6.5l9 11M6.5 7.5l11 9"/></svg>
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="truncate text-base font-semibold text-gray-800 dark:text-white/90">{{ $route->name }}</h3>
                                        @if($route->open_issues_count > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-theme-xs font-semibold text-error-600 dark:bg-error-500/15 dark:text-error-400">
                                                <span class="size-1.5 rounded-full bg-error-500 animate-pulse"></span>
                                                {{ $route->open_issues_count }} {{ __('issue(s)') }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="mt-0.5 flex flex-wrap items-center gap-1.5 text-theme-xs text-gray-500 dark:text-gray-400">
                                        @if($route->source || $route->destination)
                                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $route->source ?: '?' }}</span>
                                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $route->destination ?: '?' }}</span>
                                        @endif
                                        @if($route->fiber_cores)
                                            <span>· {{ __(':count cores', ['count' => $route->fiber_cores]) }}</span>
                                        @endif
                                        @if($route->length_km)
                                            <span>· {{ number_format((float) $route->length_km, 2) }} km</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex shrink-0 flex-wrap items-center gap-2">
                                <button type="button" wire:click="createSplitter({{ $route->id }})" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-2 text-theme-xs font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                                    <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                    {{ __('Add splitter') }}
                                </button>
                                <button type="button" wire:click="createIssue('route', {{ $route->id }})" class="inline-flex items-center gap-1.5 rounded-lg border border-error-200 bg-error-50 px-3 py-2 text-theme-xs font-medium text-error-600 transition hover:bg-error-100 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">
                                    <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                    {{ __('Report issue') }}
                                </button>
                                <button type="button" wire:click="editRoute({{ $route->id }})" title="{{ __('Edit route') }}" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-500 hover:border-brand-300 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400">
                                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button
                                    type="button"
                                    title="{{ __('Delete route') }}"
                                    @click="$dispatch('confirm-action', {
                                        title: '{{ __('Delete fiber route') }}',
                                        message: '{{ __('Delete route :name? Its splitters stay but will be unassigned.', ['name' => $route->name]) }}',
                                        confirmText: '{{ __('Delete') }}',
                                        wireMethod: 'deleteRoute',
                                        wireParams: [{{ $route->id }}],
                                    })"
                                    class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 hover:bg-error-100 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400"
                                >
                                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Splitters -->
                        <div class="p-5">
                            @if($route->splitters->isEmpty())
                                <p class="rounded-xl border border-dashed border-gray-200 py-8 text-center text-theme-sm text-gray-400 dark:border-gray-700">{{ __('No splitters on this route yet.') }}</p>
                            @else
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                                    @foreach($route->splitters as $splitter)
                                        @include('livewire.cable-map-splitter-card', ['splitter' => $splitter])
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>
                @endforeach

                @if($unassignedSplitters->isNotEmpty())
                    <section class="rounded-2xl border border-dashed border-gray-300 bg-gray-50/40 p-5 dark:border-gray-700 dark:bg-white/[0.02]">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Unassigned splitters') }}</h3>
                            <span class="text-theme-xs text-gray-500">{{ __('Not attached to a fiber route yet') }}</span>
                        </div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                            @foreach($unassignedSplitters as $splitter)
                                @include('livewire.cable-map-splitter-card', ['splitter' => $splitter])
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        @endif

    @elseif($view === 'routeForm')
        <!-- Fiber route form -->
        <div class="mx-auto max-w-3xl space-y-6">
            <div>
                <button type="button" wire:click="showOverview" class="inline-flex items-center gap-2 text-theme-sm font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    {{ __('Back to map') }}
                </button>
            </div>
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $routeId ? __('Edit fiber route') : __('Add fiber route') }}</h2>
                <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('The main fiber line from your POP / source to the coverage area.') }}</p>

                <form wire:submit="saveRoute" class="mt-5 space-y-5">
                    <div>
                        <label for="route-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Route name') }}<span class="ml-0.5 text-error-500">*</span></label>
                        <input id="route-name" type="text" wire:model="routeName" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="{{ __('e.g. Badda Main Line') }}">
                        @error('routeName') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="route-source" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Source / POP') }}</label>
                            <input id="route-source" type="text" wire:model="routeSource" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="{{ __('e.g. Uttara POP') }}">
                            @error('routeSource') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="route-destination" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Destination / Area') }}</label>
                            <input id="route-destination" type="text" wire:model="routeDestination" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="{{ __('e.g. Banani') }}">
                            @error('routeDestination') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="route-cores" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Fiber cores') }}</label>
                            <input id="route-cores" type="number" min="1" wire:model="routeCores" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="{{ __('e.g. 8') }}">
                            @error('routeCores') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="route-length" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Length (km)') }}</label>
                            <input id="route-length" type="number" step="0.01" min="0" wire:model="routeLength" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="{{ __('e.g. 3.5') }}">
                            @error('routeLength') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="route-notes" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Notes') }}</label>
                        <textarea id="route-notes" rows="3" wire:model="routeNotes" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                        @error('routeNotes') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div wire:ignore class="rounded-xl border border-dashed border-gray-200 p-4 dark:border-gray-700">
                        <div class="mb-3 flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <span class="flex items-center gap-1.5 text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                    <svg class="size-4 stroke-brand-500 dark:stroke-brand-400" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    {{ __('Location on map') }}
                                </span>
                                <p data-coord-readout class="mt-1 text-theme-xs font-semibold {{ $routeLatitude ? 'text-brand-600 dark:text-brand-400' : 'text-gray-400' }}">@if($routeLatitude){{ $routeLatitude }}, {{ $routeLongitude }}@else{{ __('Not set yet') }}@endif</p>
                            </div>
                            <button type="button" data-clear-location class="rounded-lg border border-gray-200 px-3 py-1.5 text-theme-xs font-medium text-gray-500 transition hover:border-error-300 hover:text-error-600 dark:border-gray-700 dark:text-gray-400">{{ __('Clear') }}</button>
                        </div>
                        <div
                            data-location-picker
                            data-target-lat="route-latitude"
                            data-target-lng="route-longitude"
                            data-initial-lat="{{ $routeLatitude }}"
                            data-initial-lng="{{ $routeLongitude }}"
                            class="h-72 w-full overflow-hidden rounded-lg"
                        ></div>
                        <p class="mt-2 text-theme-xs text-gray-400 dark:text-gray-500">{{ __('Tip: click the map to drop the POP / starting point of this route.') }}</p>
                    </div>

                    <input type="hidden" id="route-latitude" wire:model="routeLatitude">
                    <input type="hidden" id="route-longitude" wire:model="routeLongitude">

                    <div class="flex flex-col-reverse gap-3 pt-1 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="showOverview" class="rounded-lg border border-gray-300 px-4 py-2.5 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">{{ __('Cancel') }}</button>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                            <span wire:loading.remove wire:target="saveRoute">{{ $routeId ? __('Save changes') : __('Add route') }}</span>
                            <span wire:loading wire:target="saveRoute">{{ __('Saving...') }}</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>

    @elseif($view === 'splitterForm')
        <!-- Splitter form -->
        <div class="mx-auto max-w-4xl space-y-6">
            <div>
                <button type="button" wire:click="showOverview" class="inline-flex items-center gap-2 text-theme-sm font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    {{ __('Back to map') }}
                </button>
            </div>
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $splitterId ? __('Edit splitter') : __('Add splitter') }}</h2>
                <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('A splitter takes the main fiber and gives access to your subscribers.') }}</p>

                <form wire:submit="saveSplitter" class="mt-5 space-y-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="splitter-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Splitter name') }}<span class="ml-0.5 text-error-500">*</span></label>
                            <input id="splitter-name" type="text" wire:model="splitterName" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="{{ __('e.g. Banani S1') }}">
                            @error('splitterName') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="splitter-location" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Location / Landmark') }}</label>
                            <input id="splitter-location" type="text" wire:model="splitterLocation" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="{{ __('e.g. Road 11, house 22') }}">
                            @error('splitterLocation') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="splitter-route" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Fiber route') }}</label>
                            <select id="splitter-route" wire:model="splitterRouteId" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">{{ __('Unassigned') }}</option>
                                @foreach($allRoutes as $option)
                                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                                @endforeach
                            </select>
                            @error('splitterRouteId') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="splitter-port-count" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Port count') }}<span class="ml-0.5 text-error-500">*</span></label>
                            <input id="splitter-port-count" type="number" min="1" max="256" wire:model="splitterPortCount" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @error('splitterPortCount') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="splitter-notes" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Notes') }}</label>
                        <textarea id="splitter-notes" rows="2" wire:model="splitterNotes" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                        @error('splitterNotes') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div wire:ignore class="rounded-xl border border-dashed border-gray-200 p-4 dark:border-gray-700">
                        <div class="mb-3 flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <span class="flex items-center gap-1.5 text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                    <svg class="size-4 stroke-brand-500 dark:stroke-brand-400" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    {{ __('Location on map') }}
                                </span>
                                <p data-coord-readout class="mt-1 text-theme-xs font-semibold {{ $splitterLatitude ? 'text-brand-600 dark:text-brand-400' : 'text-gray-400' }}">@if($splitterLatitude){{ $splitterLatitude }}, {{ $splitterLongitude }}@else{{ __('Not set yet') }}@endif</p>
                            </div>
                            <button type="button" data-clear-location class="rounded-lg border border-gray-200 px-3 py-1.5 text-theme-xs font-medium text-gray-500 transition hover:border-error-300 hover:text-error-600 dark:border-gray-700 dark:text-gray-400">{{ __('Clear') }}</button>
                        </div>
                        <div
                            data-location-picker
                            data-target-lat="splitter-latitude"
                            data-target-lng="splitter-longitude"
                            data-initial-lat="{{ $splitterLatitude }}"
                            data-initial-lng="{{ $splitterLongitude }}"
                            class="h-72 w-full overflow-hidden rounded-lg"
                        ></div>
                        <p class="mt-2 text-theme-xs text-gray-400 dark:text-gray-500">{{ __('Tip: click the map to drop this splitter’s exact position.') }}</p>
                    </div>

                    <input type="hidden" id="splitter-latitude" wire:model="splitterLatitude">
                    <input type="hidden" id="splitter-longitude" wire:model="splitterLongitude">

                    @if($splitterId)
                        <div>
                            <div class="mb-3">
                                <h3 class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Ports & customers') }}</h3>
                                <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Link each port to a customer. Customers left as “Free” stay open.') }}</p>
                            </div>
                            @php $splitter = $this->splitters()->with('ports')->find($this->splitterId); @endphp
                            @if($splitter && $splitter->ports->isNotEmpty())
                                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                                    <table class="min-w-full">
                                        <thead class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-white/[0.02]">
                                            <tr>
                                                <th class="w-20 px-4 py-2.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Port') }}</th>
                                                <th class="px-4 py-2.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Customer') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                            @foreach($splitter->ports as $port)
                                                <tr>
                                                    <td class="px-4 py-2 text-theme-sm font-medium text-gray-500 dark:text-gray-400">P{{ $port->port_number }}</td>
                                                    <td class="px-4 py-2">
                                                        <select wire:model="portAssignments.{{ $port->id }}" class="h-10 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-theme-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                                            <option value="">{{ __('Free') }}</option>
                                                            @foreach($customers as $customerOption)
                                                                <option value="{{ $customerOption->id }}">{{ $customerOption->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="flex flex-col-reverse gap-3 pt-1 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="showOverview" class="rounded-lg border border-gray-300 px-4 py-2.5 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">{{ __('Cancel') }}</button>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                            <span wire:loading.remove wire:target="saveSplitter">{{ $splitterId ? __('Save changes') : __('Add splitter') }}</span>
                            <span wire:loading wire:target="saveSplitter">{{ __('Saving...') }}</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>

    @elseif($view === 'issueForm')
        <!-- Report issue -->
        <div class="mx-auto max-w-3xl space-y-6">
            <div>
                <button type="button" wire:click="showOverview" class="inline-flex items-center gap-2 text-theme-sm font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    {{ __('Back to map') }}
                </button>
            </div>
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Report fiber cut / issue') }}</h2>
                <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Pick where the problem is and we will list every customer below it.') }}</p>

                <form wire:submit="saveIssue" class="mt-5 space-y-5">
                    <div>
                        <span class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Problem at') }}<span class="ml-0.5 text-error-500">*</span></span>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border px-4 py-3 {{ $issueScope === 'route' ? 'border-brand-300 bg-brand-50/60 dark:border-brand-500/40 dark:bg-brand-500/10' : 'border-gray-200 dark:border-gray-700' }}">
                                <span class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                        <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="5" cy="5" r="3"/><circle cx="19" cy="5" r="3"/><circle cx="5" cy="19" r="3"/><circle cx="19" cy="19" r="3"/><path d="M7.5 6.5l9 11M6.5 7.5l11 9"/></svg>
                                    </span>
                                    <span>
                                        <span class="block text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ __('Fiber route') }}</span>
                                        <span class="block text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Affects every splitter on the line') }}</span>
                                    </span>
                                </span>
                                <input type="radio" wire:model.live="issueScope" value="route" class="size-4 accent-brand-500">
                            </label>
                            <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border px-4 py-3 {{ $issueScope === 'splitter' ? 'border-brand-300 bg-brand-50/60 dark:border-brand-500/40 dark:bg-brand-500/10' : 'border-gray-200 dark:border-gray-700' }}">
                                <span class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                        <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h12a2 2 0 0 1 2 2v16l-4-2-4 2-4-2-4 2V4a2 2 0 0 1 2-2z"/></svg>
                                    </span>
                                    <span>
                                        <span class="block text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ __('Splitter') }}</span>
                                        <span class="block text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Only this splitter is affected') }}</span>
                                    </span>
                                </span>
                                <input type="radio" wire:model.live="issueScope" value="splitter" class="size-4 accent-brand-500">
                            </label>
                        </div>
                    </div>

                    @if($issueScope === 'route')
                        <div>
                            <label for="issue-route" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Fiber route') }}<span class="ml-0.5 text-error-500">*</span></label>
                            <select id="issue-route" wire:model="issueRouteId" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">{{ __('Select a route') }}</option>
                                @foreach($allRoutes as $option)
                                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                                @endforeach
                            </select>
                            @error('issueRouteId') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                    @else
                        <div>
                            <label for="issue-splitter" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Splitter') }}<span class="ml-0.5 text-error-500">*</span></label>
                            <select id="issue-splitter" wire:model="issueSplitterId" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">{{ __('Select a splitter') }}</option>
                                @foreach($allSplitters as $option)
                                    <option value="{{ $option->id }}">{{ $option->name }}@if($option->location) — {{ $option->location }}@endif</option>
                                @endforeach
                            </select>
                            @error('issueSplitterId') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div>
                        <label for="issue-type" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Issue type') }}<span class="ml-0.5 text-error-500">*</span></label>
                        <select id="issue-type" wire:model="issueType" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="fiber_cut">{{ __('Fiber cut') }}</option>
                            <option value="maintenance">{{ __('Planned maintenance') }}</option>
                            <option value="other">{{ __('Other') }}</option>
                        </select>
                        @error('issueType') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="issue-title" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Short title') }}<span class="ml-0.5 text-error-500">*</span></label>
                        <input id="issue-title" type="text" wire:model="issueTitle" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="{{ __('e.g. Fiber cut near Road 11') }}">
                        @error('issueTitle') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="issue-description" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Details') }}</label>
                        <textarea id="issue-description" rows="3" wire:model="issueDescription" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="{{ __('e.g. Cut by road digging, one splitter affected') }}"></textarea>
                        @error('issueDescription') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-col-reverse gap-3 pt-1 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="showOverview" class="rounded-lg border border-gray-300 px-4 py-2.5 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">{{ __('Cancel') }}</button>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-error-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-error-600">
                            <span wire:loading.remove wire:target="saveIssue">{{ __('Report issue') }}</span>
                            <span wire:loading wire:target="saveIssue">{{ __('Saving...') }}</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    @endif
</div>
