<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ __('Report a problem') }} | {{ $tenant->name }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-gray-50 font-outfit antialiased dark:bg-gray-900">
        <div class="flex min-h-screen items-center justify-center px-4 py-10">
            <div class="w-full max-w-lg">
                <!-- Brand -->
                <div class="text-center">
                    <span class="mx-auto grid h-12 w-12 place-items-center rounded-xl bg-brand-500 text-white">
                        <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a5 5 0 0 1 4.9 6.2A6 6 0 0 1 18 14a6 6 0 0 1-12 0 6 6 0 0 1 1.1-7.8A5 5 0 0 1 12 2z"/><path d="M12 9l-2 4 2 1.5L14 13l-2-4z"/></svg>
                    </span>
                    <h1 class="mt-4 text-xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $tenant->name }}</h1>
                    <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Report an internet or service problem — it goes straight to the ISP.') }}</p>
                </div>

                <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                    @if(session('status'))
                        <div class="mb-4 flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
                            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('status') }}</p>
                        </div>
                    @endif

                    <livewire:public-issue-report :tenant="$tenant" />
                </div>

                <p class="mt-4 text-center text-theme-xs text-gray-400 dark:text-gray-500">Powered by <span class="font-semibold text-gray-500 dark:text-gray-400">BeeCore</span></p>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
