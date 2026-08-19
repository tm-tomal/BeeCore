<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ config('app.name', 'BeeCore') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen text-slate-100 antialiased">
        <a href="#main-content" class="fixed left-4 top-4 z-[70] -translate-y-24 bg-teal-300 px-4 py-2 font-bold text-slate-950 focus:translate-y-0">Skip to content</a>
        <div x-data="{ navigationOpen: false }" @keydown.escape.window="navigationOpen = false" class="min-h-screen">
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-white/10 bg-[#07100f]/95 px-4 backdrop-blur lg:hidden">
                <button type="button" @click="navigationOpen = true" aria-label="Open navigation" class="grid h-10 w-10 place-items-center border border-white/10 bg-white/5" style="border-radius: 6px">
                    <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="text-lg font-black text-teal-300">BeeCore</div>
                <div class="grid h-9 w-9 place-items-center bg-emerald-400/15 text-sm font-bold text-emerald-300" style="border-radius: 6px">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
            </header>
            <div x-cloak x-show="navigationOpen" x-transition.opacity @click="navigationOpen = false" class="fixed inset-0 z-40 bg-black/65 lg:hidden"></div>
            <x-sidebar />
            <main id="main-content" class="min-w-0 px-4 py-6 sm:px-6 lg:ml-64 lg:px-8 lg:py-8">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
