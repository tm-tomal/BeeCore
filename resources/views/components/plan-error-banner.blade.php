@if(is_array($gate = session('plan_error')) && isset($gate['message']) && $gate['message'] !== '')
    <div class="flex flex-col gap-3 rounded-xl border border-warning-200 bg-warning-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between dark:border-warning-500/20 dark:bg-warning-500/10">
        <div class="flex min-w-0 items-start gap-3">
            <svg class="mt-0.5 size-5 shrink-0 stroke-warning-600 dark:stroke-warning-400" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <p class="text-theme-sm text-warning-700 dark:text-warning-300">{{ $gate['message'] }}</p>
        </div>
        @if(! empty($gate['actionUrl']))
            <a href="{{ $gate['actionUrl'] }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-warning-500 px-4 py-2 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-warning-600">
                {{ $gate['actionLabel'] ?? __('View plans & upgrade') }}
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        @endif
    </div>
@endif
