<div
    x-data="{
        open: false,
        title: 'Are you sure?',
        message: '',
        confirmText: 'Confirm',
        wireMethod: null,
        wireParams: [],
    }"
    @confirm-action.window="
        open = true;
        title = $event.detail.title ?? 'Are you sure?';
        message = $event.detail.message ?? '';
        confirmText = $event.detail.confirmText ?? 'Confirm';
        wireMethod = $event.detail.wireMethod ?? null;
        wireParams = $event.detail.wireParams ?? [];
    "
    x-cloak
    x-show="open"
    class="fixed inset-0 z-[100] flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
>
    <!-- Backdrop -->
    <div x-show="open" x-transition.opacity @click="open = false" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

    <!-- Dialog -->
    <div x-show="open" x-transition x-transition.origin.center class="shadow-theme-xl relative w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="p-5 sm:p-6">
            <div class="flex items-start gap-4">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                    <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90" x-text="title"></h3>
                    <p class="mt-1.5 text-theme-sm leading-5 text-gray-500 dark:text-gray-400" x-text="message"></p>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-gray-100 bg-gray-50/60 px-5 py-4 sm:flex-row sm:justify-end dark:border-gray-800 dark:bg-white/[0.02]">
            <button type="button" @click="open = false" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                Cancel
            </button>
            <button
                type="button"
                @click="
                    open = false;
                    if (wireMethod && window.Livewire?.first) {
                        const lw = window.Livewire.first();
                        if (lw && typeof lw[wireMethod] === 'function') { lw[wireMethod](...wireParams); }
                    }
                "
                class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-error-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-error-600"
                x-text="confirmText"
            ></button>
        </div>
    </div>
</div>
