<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sign in | BeeCore</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        class="font-outfit antialiased"
        x-data="{ darkMode: localStorage.getItem('beecore_theme') === 'dark', showPassword: false, toggleDark() { this.darkMode = !this.darkMode; localStorage.setItem('beecore_theme', this.darkMode ? 'dark' : 'light'); } }"
        :class="darkMode ? 'dark bg-gray-900' : 'bg-white'"
    >
        <div class="relative flex h-screen flex-col overflow-hidden bg-white lg:flex-row dark:bg-gray-900">
            <!-- ===== Sign in column ===== -->
            <div class="flex w-full items-center justify-center overflow-y-auto px-6 py-8 sm:px-10 lg:w-1/2 lg:px-14">
                <div class="w-full max-w-md">
                    <!-- Brand -->
                    <a href="#" class="inline-flex items-center gap-2.5">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-500">
                            <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a5 5 0 0 1 4.9 6.2A6 6 0 0 1 18 14a6 6 0 0 1-12 0 6 6 0 0 1 1.1-7.8A5 5 0 0 1 12 2z"/><path d="M12 9l-2 4 2 1.5L14 13l-2-4z"/></svg>
                        </span>
                        <span class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">BeeCore</span>
                    </a>

                    <!-- Heading -->
                    <div class="mt-8">
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-[28px]">Sign in to your workspace</h1>
                        <p class="mt-1.5 text-theme-sm text-gray-500 dark:text-gray-400">Manage billing, customers and network operations from one platform.</p>
                    </div>

                    @if(session('status'))
                        <div class="mt-5 flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
                            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('status') }}</p>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mt-5 rounded-xl border border-error-200 bg-error-50 px-4 py-3 dark:border-error-500/20 dark:bg-error-500/10">
                            @foreach($errors->all() as $error)
                                <p class="text-theme-sm text-error-700 dark:text-error-300">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-4">
                        @csrf

                        <div>
                            <label for="email" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Email address</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 dark:text-gray-500">
                                    <svg class="size-[18px] stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                </span>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="you@company.com" class="h-11 w-full rounded-lg border border-gray-300 bg-white pl-10 pr-3.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-500">
                            </div>
                        </div>

                        <div>
                            <label for="password" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Password</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 dark:text-gray-500">
                                    <svg class="size-[18px] stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                </span>
                                <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="Enter your password" class="h-11 w-full rounded-lg border border-gray-300 bg-white pl-10 pr-11 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-500">
                                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-gray-400 transition hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300" aria-label="Toggle password visibility">
                                    <svg x-show="!showPassword" class="size-[18px] stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg x-show="showPassword" class="size-[18px] stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-0.5">
                            <label for="remember" class="flex cursor-pointer select-none items-center gap-2 text-theme-sm text-gray-600 dark:text-gray-400">
                                <input id="remember" type="checkbox" name="remember" class="h-4 w-4 rounded border-gray-300 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">
                                Keep me signed in
                            </label>
                            <a href="{{ route('password.request') }}" class="text-theme-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">Forgot password?</a>
                        </div>

                        <button type="submit" class="flex h-11 w-full items-center justify-center rounded-lg bg-brand-500 text-theme-sm font-semibold text-white shadow-theme-md transition hover:bg-brand-600 active:scale-[0.99]">
                            Sign in
                        </button>
                    </form>

                    <p class="mt-6 text-center text-theme-sm text-gray-500 dark:text-gray-400">
                        New to BeeCore?
                        <a href="{{ route('register') }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">Create your ISP workspace</a>
                    </p>
                </div>
            </div>

            <!-- ===== Brand panel ===== -->
            <div class="relative hidden overflow-hidden bg-brand-950 lg:flex lg:w-1/2 lg:items-center lg:justify-center">
                <div class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-brand-500/20 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-brand-400/10 blur-3xl"></div>
                <div class="pointer-events-none absolute inset-0 opacity-[0.05]" style="background-image:linear-gradient(rgba(255,255,255,.6) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.6) 1px,transparent 1px);background-size:44px 44px"></div>

                <div class="relative mx-auto max-w-md px-12">
                    <h2 class="text-3xl font-bold leading-tight tracking-tight text-white xl:text-4xl">
                        Every part of your ISP,<br>working as one.
                    </h2>
                    <p class="mt-4 text-theme-sm leading-6 text-gray-300">BeeCore connects customer management, billing and network operations — for manual ISPs today and automated ISPs tomorrow.</p>

                    <ul class="mt-7 space-y-3">
                        <li class="flex items-center gap-3">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-success-500/15 text-success-400">
                                <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span class="text-theme-sm text-gray-200">Billing and payments for every ISP</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-success-500/15 text-success-400">
                                <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span class="text-theme-sm text-gray-200">Automatic or manual network operations</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-success-500/15 text-success-400">
                                <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span class="text-theme-sm text-gray-200">A multi-tenant SaaS built for scale</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Dark mode toggle -->
            <button
                type="button"
                @click="toggleDark()"
                class="absolute right-5 bottom-5 z-50 flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 shadow-theme-md transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                aria-label="Toggle dark mode"
            >
                <svg class="hidden size-5 stroke-current dark:block" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                <svg class="size-5 stroke-current dark:hidden" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            </button>
        </div>
    </body>
</html>
