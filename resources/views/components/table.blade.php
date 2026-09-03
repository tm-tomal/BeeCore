@props(['heading' => null, 'description' => null, 'paginator' => null])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]']) }}>
    @if($heading !== null || $description !== null || isset($toolbar))
        <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="min-w-0">
                @if($heading !== null)
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $heading }}</h3>
                @endif
                @if($description !== null)
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $description }}</p>
                @endif
            </div>
            @isset($toolbar)
                <div class="flex flex-wrap items-center gap-2">{{ $toolbar }}</div>
            @endisset
        </div>
    @endif

    <div class="w-full overflow-x-auto">
        {{ $slot }}
    </div>

    @if($paginator && $paginator->hasPages())
        <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
            {{ $paginator->links() }}
        </div>
    @endif
</div>
