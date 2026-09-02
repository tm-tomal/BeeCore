<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Forgot password | BeeCore</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        class="bg-gray-50 font-outfit antialiased dark:bg-gray-900"
        x-data="{ darkMode: localStorage.getItem('beecore_theme') === 'dark', toggleDark() { this.darkMode = !this.darkMode; localStorage.setItem('beecore_theme', this.darkMode ? 'dark' : 'light'); } }"
        :class="darkMode ? 'dark' : ''"
    >
        <div class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-8">
            <div class="w-full max-w-md">
                <!-- Brand -->
                <a href="{{ route('login') }}" class="mb-7 inline-flex items-center gap-2.5">
                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-500">
                        <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a5 5 0 0 1 4.9 6.2A6 6 0 0 1 18 14a6 6 0 0 1-12 0 6 6 0 0 1 1.1-7.8A5 5 0 0 1 12 2z"/><path d="M12 9l-2 4 2 1.5L14 13l-2-4z"/></svg>
                    </span>
                    <span class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">BeeCore</span>
                </a>

                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-lg sm:p-8 dark:border-gray-800 dark:bg-gray-900">
                    <h1 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-2xl">Reset your password</h1>
                    <p class="mt-1.5 text-theme-sm leading-6 text-gray-500 dark:text-gray-400">Enter your account email and we will send you a link to set a new password.</p>

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

                    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
                        @csrf

                        <div>
                            <label for="email" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Email address</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="you@company.com" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-500">
                        </div>

                        <button type="submit" class="flex h-11 w-full items-center justify-center rounded-lg bg-brand-500 text-theme-sm font-semibold text-white shadow-theme-md transition hover:bg-brand-600 active:scale-[0.99]">
                            Send reset link
                        </button>
                    </form>

                    <p class="mt-6 text-center text-theme-sm text-gray-500 dark:text-gray-400">
                        Remembered it?
                        <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">Back to sign in</a>
                    </p>
                </div>

                <p class="mt-6 text-center text-theme-xs leading-5 text-gray-400 dark:text-gray-500">Password reset links expire after 60 minutes.</p>
            </div>

            <!-- Dark mode toggle -->
            <button type="button" @click="toggleDark()" class="fixed right-5 bottom-5 z-50 flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 shadow-theme-md transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700" aria-label="Toggle dark mode">
                <svg class="hidden size-5 stroke-current dark:block" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                <svg class="size-5 stroke-current dark:hidden" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            </button>
        </div>
    </body>
</html>
