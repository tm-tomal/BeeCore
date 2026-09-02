<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create your ISP workspace | BeeCore</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        class="font-outfit antialiased"
        x-data="{
            darkMode: localStorage.getItem('beecore_theme') === 'dark',
            step: 1,
            operationMode: '',
            toggleDark() { this.darkMode = !this.darkMode; localStorage.setItem('beecore_theme', this.darkMode ? 'dark' : 'light'); },
            validateStep(ref) {
                let valid = true;
                ref.querySelectorAll('input[required], select[required], textarea[required]').forEach((el) => {
                    if (!el.checkValidity()) { el.reportValidity(); valid = false; }
                });
                return valid;
            },
            next() {
                if (this.validateStep(this.$refs.step1)) { this.step = 2; this.$refs.step2?.scrollIntoView({ block: 'nearest' }); }
            },
            prev() { this.step = 1; window.scrollTo({ top: 0, behavior: 'smooth' }); },
            pickMode(mode) { this.operationMode = mode; },
        }"
        :class="darkMode ? 'dark bg-gray-900' : 'bg-white'"
    >
        <div class="relative flex h-screen flex-col overflow-hidden bg-white lg:flex-row dark:bg-gray-900">
            <!-- ===== Form column ===== -->
            <div class="flex w-full items-center justify-center overflow-y-auto px-6 py-8 sm:px-10 lg:w-1/2 lg:px-14">
                <div class="w-full max-w-md">
                    <!-- Brand -->
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2.5">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-500">
                            <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a5 5 0 0 1 4.9 6.2A6 6 0 0 1 18 14a6 6 0 0 1-12 0 6 6 0 0 1 1.1-7.8A5 5 0 0 1 12 2z"/><path d="M12 9l-2 4 2 1.5L14 13l-2-4z"/></svg>
                        </span>
                        <span class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">BeeCore</span>
                    </a>

                    <!-- Heading -->
                    <div class="mt-8">
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-[28px]">Create your ISP workspace</h1>
                        <p class="mt-1.5 text-theme-sm text-gray-500 dark:text-gray-400">Tell us about yourself first — your workspace is set up in a couple of steps.</p>
                    </div>

                    <!-- Stepper -->
                    <div class="mt-6 flex items-center gap-3">
                        <template x-for="(label, i) in ['Account owner', 'Business']" :key="i">
                            <div class="flex flex-1 items-center gap-3">
                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full text-theme-sm font-semibold transition"
                                    :class="step > i ? 'bg-brand-500 text-white' : (step === i + 1 ? 'border-2 border-brand-500 text-brand-600 dark:text-brand-400' : 'border border-gray-300 text-gray-400 dark:border-gray-700')"
                                    x-text="step > i ? '✓' : (i + 1)"></span>
                                <span class="hidden text-theme-sm font-medium sm:block" :class="step >= i + 1 ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-500'" x-text="label"></span>
                                <span x-show="i === 0" class="h-px flex-1 bg-gray-200 dark:bg-gray-800" :class="step > 1 && 'bg-brand-500'"></span>
                            </div>
                        </template>
                    </div>

                    @if($errors->any())
                        <div class="mt-6 rounded-xl border border-error-200 bg-error-50 px-4 py-3 dark:border-error-500/20 dark:bg-error-500/10">
                            @foreach($errors->all() as $error)
                                <p class="text-theme-sm text-error-700 dark:text-error-300">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" class="mt-7 space-y-5">
                        @csrf

                        <!-- Step 1: Account owner -->
                        <div x-ref="step1" x-show="step === 1" x-transition class="space-y-4">
                            <div>
                                <label for="ownerName" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Full name</label>
                                <input id="ownerName" type="text" name="ownerName" value="{{ old('ownerName') }}" required autofocus placeholder="Rahim Uddin" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-500">
                            </div>

                            <div>
                                <label for="ownerPhone" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Mobile number</label>
                                <input id="ownerPhone" type="tel" name="ownerPhone" value="{{ old('ownerPhone') }}" required placeholder="+8801XXXXXXXXX" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-500">
                            </div>

                            <div>
                                <label for="ownerEmail" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Email</label>
                                <input id="ownerEmail" type="email" name="ownerEmail" value="{{ old('ownerEmail') }}" required placeholder="you@company.com" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-500">
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="password" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Password</label>
                                    <input id="password" type="password" name="password" required minlength="8" placeholder="Min. 8 characters" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-500">
                                </div>
                                <div>
                                    <label for="password_confirmation" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Confirm password</label>
                                    <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" placeholder="Re-enter password" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-500">
                                </div>
                            </div>

                            <button type="button" @click="next()" class="flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-brand-500 text-theme-sm font-semibold text-white shadow-theme-md transition hover:bg-brand-600 active:scale-[0.99]">
                                Continue
                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </button>
                        </div>

                        <!-- Step 2: Business -->
                        <div x-ref="step2" x-show="step === 2" x-transition class="space-y-4">
                            <div>
                                <label for="name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Company / business name</label>
                                <input id="name" type="text" name="name" value="{{ old('name') }}" required placeholder="Acme Networks" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-500">
                            </div>

                            <div>
                                <span class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Software type</span>
                                <input type="hidden" name="operationMode" x-model="operationMode">
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <button type="button" @click="pickMode('automatic')"
                                        class="rounded-xl border p-4 text-left transition"
                                        :class="operationMode === 'automatic' ? 'border-brand-500 bg-brand-50/70 ring-2 ring-brand-500/20 dark:border-brand-500 dark:bg-brand-500/10' : 'border-gray-300 hover:border-gray-400 dark:border-gray-700 dark:hover:border-gray-600'">
                                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-100 text-brand-600 dark:bg-brand-500/20 dark:text-brand-300">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                        </span>
                                        <span class="mt-2.5 block text-theme-sm font-semibold text-gray-900 dark:text-white">Automation</span>
                                        <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">OLT, MikroTik &amp; network automation</span>
                                    </button>

                                    <button type="button" @click="pickMode('manual')"
                                        class="rounded-xl border p-4 text-left transition"
                                        :class="operationMode === 'manual' ? 'border-brand-500 bg-brand-50/70 ring-2 ring-brand-500/20 dark:border-brand-500 dark:bg-brand-500/10' : 'border-gray-300 hover:border-gray-400 dark:border-gray-700 dark:hover:border-gray-600'">
                                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h.01M12 16h.01"/></svg>
                                        </span>
                                        <span class="mt-2.5 block text-theme-sm font-semibold text-gray-900 dark:text-white">Manual</span>
                                        <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">Billing-focused, no network automation</span>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label for="businessAddress" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Business address</label>
                                <textarea id="businessAddress" name="businessAddress" rows="2" required placeholder="House, road, area, district" class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-500">{{ old('businessAddress') }}</textarea>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <button type="button" @click="prev()" class="flex h-11 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                                    Back
                                </button>
                                <button type="submit" class="flex h-11 items-center justify-center rounded-lg bg-brand-500 text-theme-sm font-semibold text-white shadow-theme-md transition hover:bg-brand-600 active:scale-[0.99]">
                                    Sign up
                                </button>
                            </div>
                        </div>
                    </form>

                    <p class="mt-6 text-center text-theme-sm text-gray-500 dark:text-gray-400">
                        Already have a workspace?
                        <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">Sign in</a>
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
                        Start your ISP<br>in minutes.
                    </h2>
                    <p class="mt-4 text-theme-sm leading-6 text-gray-300">Create a workspace with customer billing and payments today — then unlock network automation as your ISP grows.</p>

                    <ul class="mt-7 space-y-3">
                        <li class="flex items-center gap-3">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-success-500/15 text-success-400">
                                <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span class="text-theme-sm text-gray-200">Ready-to-use billing and packages</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-success-500/15 text-success-400">
                                <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span class="text-theme-sm text-gray-200">Automation or manual — your choice</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-success-500/15 text-success-400">
                                <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span class="text-theme-sm text-gray-200">Multi-currency and multi-language ready</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Dark mode toggle -->
            <button type="button" @click="toggleDark()" class="absolute right-5 bottom-5 z-50 flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 shadow-theme-md transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700" aria-label="Toggle dark mode">
                <svg class="hidden size-5 stroke-current dark:block" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                <svg class="size-5 stroke-current dark:hidden" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            </button>
        </div>
    </body>
</html>
