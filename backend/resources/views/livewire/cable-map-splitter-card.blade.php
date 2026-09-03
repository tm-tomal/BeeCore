@php
    $ports = $splitter->ports ?? collect();
    $used = $ports->whereNotNull('customer_id')->count();
    $total = (int) $splitter->port_count;
    $free = max(0, $total - $used);
    $pct = $total > 0 ? (int) round($used / $total * 100) : 0;
    $openCount = (int) $splitter->open_issues_count;
@endphp

<div class="flex flex-col rounded-xl border border-gray-200 bg-gray-50/40 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h4 class="truncate text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $splitter->name }}</h4>
                @if($openCount > 0)
                    <span class="inline-flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-theme-xs font-semibold text-error-600 dark:bg-error-500/15 dark:text-error-400">
                        <span class="size-1.5 rounded-full bg-error-500 animate-pulse"></span>{{ $openCount }}
                    </span>
                @endif
            </div>
            @if($splitter->location)
                <p class="mt-0.5 flex items-center gap-1 truncate text-theme-xs text-gray-500 dark:text-gray-400">
                    <svg class="size-3.5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    {{ $splitter->location }}
                </p>
            @endif
        </div>
        <div class="flex shrink-0 items-center gap-1.5">
            <button type="button" wire:click="editSplitter({{ $splitter->id }})" title="{{ __('Edit splitter') }}" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </button>
            <button type="button" wire:click="createIssue('splitter', null, {{ $splitter->id }})" title="{{ __('Report issue') }}" class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-white text-error-500 transition hover:bg-error-50 dark:border-error-500/25 dark:bg-gray-900 dark:text-error-400">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </button>
            <button
                type="button"
                title="{{ __('Delete splitter') }}"
                @click="$dispatch('confirm-action', {
                    title: '{{ __('Delete splitter') }}',
                    message: '{{ __('Delete splitter :name? All port links will be removed.', ['name' => $splitter->name]) }}',
                    confirmText: '{{ __('Delete') }}',
                    wireMethod: 'deleteSplitter',
                    wireParams: [{{ $splitter->id }}],
                })"
                class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:bg-error-100 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400"
            >
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </button>
        </div>
    </div>

    <div class="mt-4">
        <div class="mb-1.5 flex items-center justify-between text-theme-xs">
            <span class="font-medium text-gray-500 dark:text-gray-400">{{ __(':used of :total ports used', ['used' => $used, 'total' => $total]) }}</span>
            <span class="font-semibold {{ $free > 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400' }}">{{ $free }} {{ __('free') }}</span>
        </div>
        <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
            <div class="h-full rounded-full bg-brand-500 transition-all" style="width: {{ $pct }}%"></div>
        </div>
        <div class="mt-3 flex flex-wrap gap-1.5">
            @foreach($ports->take(12) as $port)
                @if($port->customer_id !== null)
                    <span title="{{ __('Used by :name', ['name' => $port->customer?->name ?? __('Customer')]) }}" class="grid size-6 cursor-default place-items-center rounded-md bg-success-100 text-[10px] font-semibold text-success-700 dark:bg-success-500/20 dark:text-success-400">{{ $port->port_number }}</span>
                @else
                    <span title="{{ __('Free port :number', ['number' => $port->port_number]) }}" class="grid size-6 cursor-default place-items-center rounded-md bg-gray-100 text-[10px] font-medium text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">{{ $port->port_number }}</span>
                @endif
            @endforeach
            @if($total > 12)
                <span class="grid h-6 place-items-center rounded-md bg-gray-100 px-1.5 text-[10px] font-medium text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">+{{ $total - 12 }}</span>
            @endif
        </div>
    </div>
</div>
