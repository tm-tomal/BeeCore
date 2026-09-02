@props([
    'icon' => 'grid',
    'label' => '',
    'value' => '',
    'sub' => '',
    'currency' => false,
    'count' => null,
    'decimals' => 0,
    'trend' => null,
    'trendUp' => true,
    'href' => null,
])

@php
    $paths = [
        'buildings' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        'wallet' => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'check' => '<polyline points="20 6 9 17 4 12"/>',
        'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'receipt' => '<path d="M6 2h12a2 2 0 0 1 2 2v16l-4-2-4 2-4-2-4 2V4a2 2 0 0 1 2-2z"/><line x1="9" y1="9" x2="15" y2="9"/><line x1="9" y1="13" x2="15" y2="13"/>',
        'signal' => '<path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/>',
        'activity' => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
        'alert' => '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'trending' => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.2"/><rect x="14" y="3" width="7" height="7" rx="1.2"/><rect x="14" y="14" width="7" height="7" rx="1.2"/><rect x="3" y="14" width="7" height="7" rx="1.2"/>',
    ];
    $accents = [
        'buildings' => 'from-brand-500 to-indigo-500 text-white',
        'wallet' => 'from-emerald-500 to-teal-500 text-white',
        'clock' => 'from-amber-500 to-orange-500 text-white',
        'check' => 'from-success-500 to-emerald-500 text-white',
        'users' => 'from-sky-500 to-blue-500 text-white',
        'receipt' => 'from-violet-500 to-purple-500 text-white',
        'signal' => 'from-pink-500 to-rose-500 text-white',
        'activity' => 'from-slate-500 to-slate-600 text-white',
        'alert' => 'from-error-500 to-rose-500 text-white',
        'trending' => 'from-teal-500 to-cyan-500 text-white',
        'grid' => 'from-gray-500 to-gray-600 text-white',
    ];
    $trendColor = $trendUp ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400' : 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400';
@endphp

<div class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs transition duration-200 hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-theme-md dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700 md:p-6">
    <div class="flex items-center gap-4">
        <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-gradient-to-br shadow-theme-xs {{ $accents[$icon] ?? $accents['grid'] }}">
            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $paths[$icon] ?? $paths['grid'] !!}</svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
            <h4 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">
                @if($currency)<span>৳</span>@endif
                <span
                    @if($count !== null) data-count="{{ $count }}" data-decimals="{{ $decimals }}" @endif
                >{{ $value }}</span>
            </h4>
            @if($sub)
                <p class="mt-1 truncate text-theme-xs text-gray-500 dark:text-gray-400">{{ $sub }}</p>
            @endif
            @if($trend !== null)
                <span class="mt-1.5 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $trendColor }}">
                    <svg class="size-3 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        {!! $trendUp ? '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>' : '<polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/>' !!}
                    </svg>
                    {{ $trend }}
                </span>
            @endif
        </div>
    </div>
</div>
