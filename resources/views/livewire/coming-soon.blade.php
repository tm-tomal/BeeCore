<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $label }}</h1>
            @if($description)
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
            @endif
        </div>
    </header>

    <div class="max-w-2xl rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <div class="inline-flex items-center gap-2 rounded-full border border-warning-200 bg-warning-50 px-3 py-1 text-theme-xs font-semibold uppercase tracking-wider text-warning-600 dark:border-warning-500/20 dark:bg-warning-500/15 dark:text-warning-500">
            <span aria-hidden="true">🚧</span> Coming soon
        </div>
        <p class="mt-4 text-theme-sm text-gray-500 dark:text-gray-400">This module is on the roadmap and is not implemented yet. Planned capabilities:</p>
        <ul class="mt-4 space-y-2.5">
            @foreach($features as $feature)
                <li class="flex items-start gap-2.5 text-theme-sm text-gray-600 dark:text-gray-400">
                    <span class="mt-0.5 shrink-0 text-brand-500 dark:text-brand-400" aria-hidden="true">•</span>
                    <span>{{ $feature }}</span>
                </li>
            @endforeach
        </ul>
    </div>
</div>
