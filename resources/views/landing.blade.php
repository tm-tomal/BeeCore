@php
    use App\Models\SystemSetting;

    $brandName = SystemSetting::get('platform_name', 'BeeCore');
    $brandTagline = SystemSetting::get('platform_tagline', 'Everything an ISP needs — billing, customers and network.');
    $brandAbout = SystemSetting::get('platform_about', 'BeeCore is a multi-tenant ISP operations platform that brings customer management, billing, collections, network maps and issue tracking together — built for Bangladeshi ISPs and ready for the whole region.');
    $contactEmail = SystemSetting::get('contact_email', '');
    $contactPhone = SystemSetting::get('contact_phone', '');
    $contactAddress = SystemSetting::get('contact_address', '');
    $supportHours = SystemSetting::get('support_hours', '');
    $websiteUrl = SystemSetting::get('website_url', '');
    $facebookUrl = SystemSetting::get('facebook_url', '');
    $hasContact = $contactEmail || $contactPhone || $contactAddress || $supportHours;
    $locale = app()->getLocale();
    $localeBn = $locale === 'bn';
@endphp
<!DOCTYPE html>
<html lang="{{ $localeBn ? 'bn' : 'en' }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $brandName }} — {{ $brandTagline }}</title>
        <meta name="description" content="{{ $brandAbout }}">
        @vite(['resources/css/app.css'])
    </head>
    <body class="font-outfit antialiased bg-gray-50">
        <!-- ===== Navigation ===== -->
        <header class="sticky top-0 z-40 border-b border-gray-100 bg-white/90 backdrop-blur">
            <nav class="mx-auto flex h-16 w-full max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5">
                    <span class="grid size-9 place-items-center rounded-lg bg-brand-500 shadow-theme-xs">
                        <svg class="size-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a5 5 0 0 1 4.9 6.2A6 6 0 0 1 18 14a6 6 0 0 1-12 0 6 6 0 0 1 1.1-7.8A5 5 0 0 1 12 2z"/><path d="M12 9l-2 4 2 1.5L14 13l-2-4z"/></svg>
                    </span>
                    <span class="text-lg font-bold tracking-tight text-gray-900">{{ $brandName }}</span>
                </a>

                <div class="hidden items-center gap-8 text-theme-sm font-medium text-gray-600 md:flex">
                    <a href="#features" class="transition hover:text-brand-600">{{ __('Features') }}</a>
                    <a href="#how" class="transition hover:text-brand-600">{{ __('How it works') }}</a>
                    <a href="#contact" class="transition hover:text-brand-600">{{ __('Contact') }}</a>
                </div>

                <div class="flex items-center gap-2.5">
                    <div class="mr-1 inline-flex items-center rounded-lg border border-gray-200 bg-gray-50 p-0.5 dark:border-gray-700" aria-label="{{ __('Language') }}">
                        <a href="{{ route('locale.switch', ['locale' => 'en']) }}" class="inline-flex h-7 items-center justify-center rounded-md px-2.5 text-theme-xs font-semibold transition {{ $localeBn ? 'text-gray-500 hover:text-gray-800' : 'bg-brand-500 text-white' }}">EN</a>
                        <a href="{{ route('locale.switch', ['locale' => 'bn']) }}" class="inline-flex h-7 items-center justify-center rounded-md px-2.5 text-theme-xs font-semibold transition {{ $localeBn ? 'bg-brand-500 text-white' : 'text-gray-500 hover:text-gray-800' }}">বাং</a>
                    </div>
                    <a href="{{ route('login') }}" class="hidden h-10 items-center justify-center rounded-lg px-3 text-theme-sm font-semibold text-gray-700 transition hover:bg-gray-100 sm:inline-flex">{{ __('Sign in') }}</a>
                    <a href="{{ route('register') }}" class="inline-flex h-10 items-center justify-center rounded-lg bg-brand-500 px-3.5 text-theme-sm font-semibold text-white shadow-theme-xs transition hover:bg-brand-600 sm:px-4">{{ __('Create workspace') }}</a>
                </div>
            </nav>
        </header>

        <!-- ===== Hero ===== -->
        <section class="relative overflow-hidden bg-brand-950">
            <div class="pointer-events-none absolute -top-32 -right-32 h-[28rem] w-[28rem] rounded-full bg-brand-500/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-40 -left-24 h-96 w-96 rounded-full bg-brand-400/10 blur-3xl"></div>
            <div class="pointer-events-none absolute inset-0 opacity-[0.05]" style="background-image:linear-gradient(rgba(255,255,255,.6) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.6) 1px,transparent 1px);background-size:44px 44px"></div>

            <div class="relative mx-auto grid w-full max-w-7xl items-center gap-14 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:gap-10 lg:px-8 lg:py-28">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-theme-xs font-semibold text-brand-200">{{ __('Built for Internet Service Providers') }}</span>
                    <h1 class="mt-5 text-4xl font-bold leading-[1.12] tracking-tight text-white sm:text-5xl">
                        {{ $brandName }} —<br>
                        <span class="text-brand-400">{{ __('every part of your ISP,') }}</span><br>
                        {{ __('working as one.') }}
                    </h1>
                    <p class="mt-5 max-w-xl text-base leading-7 text-gray-300">{{ $brandAbout }}</p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('register') }}" class="inline-flex h-12 items-center justify-center gap-2 rounded-lg bg-brand-500 px-6 text-theme-sm font-semibold text-white shadow-theme-md transition hover:bg-brand-400">
                            {{ __('Start your workspace free') }}
                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                        <a href="#features" class="inline-flex h-12 items-center justify-center rounded-lg border border-white/15 bg-white/5 px-6 text-theme-sm font-semibold text-white transition hover:bg-white/10">{{ __('Explore features') }}</a>
                    </div>
                    <div class="mt-9 flex flex-wrap items-center gap-x-6 gap-y-2 text-theme-xs text-gray-400">
                        <span class="inline-flex items-center gap-1.5"><svg class="size-4 stroke-current text-success-400" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>{{ __('Billing & collections') }}</span>
                        <span class="inline-flex items-center gap-1.5"><svg class="size-4 stroke-current text-success-400" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>{{ __('Customer maps') }}</span>
                        <span class="inline-flex items-center gap-1.5"><svg class="size-4 stroke-current text-success-400" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>{{ __('Network operations') }}</span>
                        <span class="inline-flex items-center gap-1.5"><svg class="size-4 stroke-current text-success-400" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>{{ __('Issue tracking') }}</span>
                    </div>
                </div>

                <!-- Mini product preview -->
                <div class="relative hidden lg:block">
                    <div class="absolute -inset-3 rounded-3xl bg-brand-500/20 blur-2xl"></div>
                    <div class="relative rounded-2xl border border-white/10 bg-white p-5 shadow-theme-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-theme-xs font-semibold text-gray-400">{{ __('Dashboard') }}</p>
                                <p class="text-base font-bold text-gray-900">{{ __('Good morning, Dhaka ISP') }}</p>
                            </div>
                            <span class="grid size-9 place-items-center rounded-lg bg-brand-500 text-white"><svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></span>
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-3">
                            <div class="rounded-xl border border-gray-100 p-3">
                                <p class="text-theme-xs text-gray-400">{{ __('Collected') }}</p>
                                <p class="mt-1 text-sm font-bold text-gray-900">৳842,500</p>
                            </div>
                            <div class="rounded-xl border border-gray-100 p-3">
                                <p class="text-theme-xs text-gray-400">{{ __('Customers') }}</p>
                                <p class="mt-1 text-sm font-bold text-gray-900">1,280</p>
                            </div>
                            <div class="rounded-xl border border-gray-100 p-3">
                                <p class="text-theme-xs text-gray-400">{{ __('Online') }}</p>
                                <p class="mt-1 text-sm font-bold text-success-600">1,196</p>
                            </div>
                        </div>

                        <div class="mt-4 space-y-2">
                            <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2">
                                <span class="flex items-center gap-2 text-theme-xs text-gray-600"><span class="size-2 rounded-full bg-success-500"></span>{{ __('House 12, Road 8, Banani') }}</span>
                                <span class="rounded-md bg-success-50 px-2 py-0.5 text-theme-xs font-semibold text-success-600">{{ __('Paid') }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2">
                                <span class="flex items-center gap-2 text-theme-xs text-gray-600"><span class="size-2 rounded-full bg-success-500"></span>{{ __('House 42, Road 3, Dhanmondi') }}</span>
                                <span class="rounded-md bg-success-50 px-2 py-0.5 text-theme-xs font-semibold text-success-600">{{ __('Paid') }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2">
                                <span class="flex items-center gap-2 text-theme-xs text-gray-600"><span class="size-2 rounded-full bg-amber-400"></span>{{ __('House 7, Road 11, Banani') }}</span>
                                <span class="rounded-md bg-amber-50 px-2 py-0.5 text-theme-xs font-semibold text-amber-600">{{ __('Due') }}</span>
                            </div>
                        </div>

                        <div class="mt-4 rounded-lg border border-brand-100 bg-brand-50/60 p-3">
                            <div class="flex items-center justify-between">
                                <p class="text-theme-xs font-semibold text-gray-700">{{ __('Revenue this month') }}</p>
                                <span class="text-theme-xs font-bold text-brand-600">৳1.2M</span>
                            </div>
                            <div class="mt-2 flex h-14 items-end gap-1.5">
                                @foreach([34, 48, 40, 62, 55, 74, 68, 84, 78, 96, 90, 100] as $i)
                                    <div class="flex-1 rounded-sm {{ $i >= 90 ? 'bg-brand-500' : 'bg-brand-300' }}" style="height:{{ $i }}%"></div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== Features ===== -->
        <section id="features" class="mx-auto w-full max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-theme-sm font-semibold uppercase tracking-widest text-brand-600">{{ __('Features') }}</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">{{ __('One system runs your whole ISP') }}</h2>
                <p class="mt-4 text-theme-sm leading-6 text-gray-500">{{ __('Stop juggling spreadsheets, phone calls and paper bills. Everything stays in sync.') }}</p>
            </div>

            <div class="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-theme-xs transition hover:-translate-y-1 hover:shadow-theme-lg">
                    <span class="grid size-10 place-items-center rounded-lg bg-brand-50 text-brand-600"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
                    <h3 class="mt-4 text-base font-semibold text-gray-900">{{ __('Customer management') }}</h3>
                    <p class="mt-1.5 text-theme-sm leading-6 text-gray-500">{{ __('Keep profiles, contacts, address and a map pin for every subscriber — ready to reuse in reports and cable maps.') }}</p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-theme-xs transition hover:-translate-y-1 hover:shadow-theme-lg">
                    <span class="grid size-10 place-items-center rounded-lg bg-success-50 text-success-600"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></span>
                    <h3 class="mt-4 text-base font-semibold text-gray-900">{{ __('Billing & collections') }}</h3>
                    <p class="mt-1.5 text-theme-sm leading-6 text-gray-500">{{ __('Auto-generate invoices, take payments with bKash and BeePay, and see who is paid or overdue — all in one place.') }}</p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-theme-xs transition hover:-translate-y-1 hover:shadow-theme-lg">
                    <span class="grid size-10 place-items-center rounded-lg bg-violet-50 text-violet-600"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg></span>
                    <h3 class="mt-4 text-base font-semibold text-gray-900">{{ __('Network operations') }}</h3>
                    <p class="mt-1.5 text-theme-sm leading-6 text-gray-500">{{ __('Online-device checks, a cable map of your area and easy move and change flows — choose automatic or manual mode.') }}</p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-theme-xs transition hover:-translate-y-1 hover:shadow-theme-lg">
                    <span class="grid size-10 place-items-center rounded-lg bg-amber-50 text-amber-600"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span>
                    <h3 class="mt-4 text-base font-semibold text-gray-900">{{ __('Issue & support tracking') }}</h3>
                    <p class="mt-1.5 text-theme-sm leading-6 text-gray-500">{{ __('Customers can report problems without login; assign a technician and let them reply — everything stays as one thread.') }}</p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-theme-xs transition hover:-translate-y-1 hover:shadow-theme-lg">
                    <span class="grid size-10 place-items-center rounded-lg bg-cyan-50 text-cyan-600"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></span>
                    <h3 class="mt-4 text-base font-semibold text-gray-900">{{ __('Reports & insights') }}</h3>
                    <p class="mt-1.5 text-theme-sm leading-6 text-gray-500">{{ __('Quick revenue, collection and growth snapshots with presets — see the health of the business in seconds.') }}</p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-theme-xs transition hover:-translate-y-1 hover:shadow-theme-lg">
                    <span class="grid size-10 place-items-center rounded-lg bg-fuchsia-50 text-fuchsia-600"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
                    <h3 class="mt-4 text-base font-semibold text-gray-900">{{ __('Team & roles') }}</h3>
                    <p class="mt-1.5 text-theme-sm leading-6 text-gray-500">{{ __('Invite staff, assign roles like support or network engineer and decide exactly what each person can do.') }}</p>
                </div>
            </div>
        </section>

        <!-- ===== How it works ===== -->
        <section id="how" class="border-y border-gray-100 bg-white">
            <div class="mx-auto w-full max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-theme-sm font-semibold uppercase tracking-widest text-brand-600">{{ __('How it works') }}</p>
                    <h2 class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">{{ __('Live in three steps') }}</h2>
                </div>

                <div class="mt-12 grid gap-8 md:grid-cols-3">
                    <div class="relative rounded-2xl border border-gray-100 p-6">
                        <span class="absolute -top-4 left-6 grid size-8 place-items-center rounded-lg bg-brand-500 text-theme-sm font-bold text-white shadow-theme-xs">1</span>
                        <h3 class="text-base font-semibold text-gray-900">{{ __('Create your workspace') }}</h3>
                        <p class="mt-2 text-theme-sm leading-6 text-gray-500">{{ __('Sign up with your company details — we provision a dedicated space with your own settings and team.') }}</p>
                    </div>
                    <div class="relative rounded-2xl border border-gray-100 p-6">
                        <span class="absolute -top-4 left-6 grid size-8 place-items-center rounded-lg bg-brand-500 text-theme-sm font-bold text-white shadow-theme-xs">2</span>
                        <h3 class="text-base font-semibold text-gray-900">{{ __('Add customers & packages') }}</h3>
                        <p class="mt-2 text-theme-sm leading-6 text-gray-500">{{ __('Import your subscriber list, pin them on the map and set billing packages, due dates and rates.') }}</p>
                    </div>
                    <div class="relative rounded-2xl border border-gray-100 p-6">
                        <span class="absolute -top-4 left-6 grid size-8 place-items-center rounded-lg bg-brand-500 text-theme-sm font-bold text-white shadow-theme-xs">3</span>
                        <h3 class="text-base font-semibold text-gray-900">{{ __('Collect & grow') }}</h3>
                        <p class="mt-2 text-theme-sm leading-6 text-gray-500">{{ __('Generate invoices, collect with bKash or BeePay, and watch revenue reports update automatically.') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== CTA ===== -->
        <section class="mx-auto w-full max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl bg-brand-950 px-6 py-14 text-center sm:px-16">
                <div class="pointer-events-none absolute -top-24 left-1/4 h-72 w-72 rounded-full bg-brand-500/25 blur-3xl"></div>
                <div class="relative">
                    <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">{{ __('Ready to bring your ISP online?') }}</h2>
                    <p class="mx-auto mt-4 max-w-xl text-theme-sm leading-6 text-gray-300">{{ __('Set up your workspace in minutes. If your area or business is new to us, our team is here to help you switch smoothly.') }}</p>
                    <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                        <a href="{{ route('register') }}" class="inline-flex h-12 items-center justify-center rounded-lg bg-white px-6 text-theme-sm font-semibold text-brand-700 shadow-theme-md transition hover:bg-gray-100">{{ __('Create your workspace') }}</a>
                        <a href="{{ route('login') }}" class="inline-flex h-12 items-center justify-center rounded-lg border border-white/15 bg-white/5 px-6 text-theme-sm font-semibold text-white transition hover:bg-white/10">{{ __('Sign in to') }} {{ $brandName }}</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== Contact ===== -->
        @if($hasContact)
            <section id="contact" class="border-t border-gray-100 bg-white">
                <div class="mx-auto w-full max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="text-theme-sm font-semibold uppercase tracking-widest text-brand-600">{{ __('Contact') }}</p>
                        <h2 class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">{{ __('Talk to the') }} {{ $brandName }} {{ __('team') }}</h2>
                        <p class="mt-4 text-theme-sm leading-6 text-gray-500">{{ __('Questions about a workspace, a demo or switching from manual operations? We answer quickly.') }}</p>
                    </div>

                    <div class="mx-auto mt-12 grid max-w-4xl gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        @if($contactEmail)
                            <div class="rounded-2xl border border-gray-100 bg-gray-50/60 p-5 text-center">
                                <span class="mx-auto grid size-10 place-items-center rounded-full bg-brand-50 text-brand-600"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span>
                                <p class="mt-3 text-theme-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Email') }}</p>
                                <p class="mt-1 break-all text-theme-sm font-semibold text-gray-800"><a href="mailto:{{ $contactEmail }}" class="hover:text-brand-600">{{ $contactEmail }}</a></p>
                            </div>
                        @endif
                        @if($contactPhone)
                            <div class="rounded-2xl border border-gray-100 bg-gray-50/60 p-5 text-center">
                                <span class="mx-auto grid size-10 place-items-center rounded-full bg-brand-50 text-brand-600"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
                                <p class="mt-3 text-theme-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Phone') }}</p>
                                <p class="mt-1 text-theme-sm font-semibold text-gray-800"><a href="tel:{{ preg_replace('/[^+0-9]/', '', $contactPhone) }}" class="hover:text-brand-600">{{ $contactPhone }}</a></p>
                            </div>
                        @endif
                        @if($supportHours)
                            <div class="rounded-2xl border border-gray-100 bg-gray-50/60 p-5 text-center">
                                <span class="mx-auto grid size-10 place-items-center rounded-full bg-brand-50 text-brand-600"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
                                <p class="mt-3 text-theme-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Support hours') }}</p>
                                <p class="mt-1 text-theme-sm font-semibold text-gray-800">{{ $supportHours }}</p>
                            </div>
                        @endif
                        @if($contactAddress)
                            <div class="rounded-2xl border border-gray-100 bg-gray-50/60 p-5 text-center">
                                <span class="mx-auto grid size-10 place-items-center rounded-full bg-brand-50 text-brand-600"><svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
                                <p class="mt-3 text-theme-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Address') }}</p>
                                <p class="mt-1 text-theme-sm font-semibold text-gray-800">{{ $contactAddress }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        <!-- ===== Footer ===== -->
        <footer class="border-t border-gray-100 bg-white">
            <div class="mx-auto flex w-full max-w-7xl flex-col items-center justify-between gap-6 px-4 py-10 sm:px-6 lg:flex-row lg:px-8">
                <div class="flex items-center gap-2.5">
                    <span class="grid size-8 place-items-center rounded-lg bg-brand-500">
                        <svg class="size-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a5 5 0 0 1 4.9 6.2A6 6 0 0 1 18 14a6 6 0 0 1-12 0 6 6 0 0 1 1.1-7.8A5 5 0 0 1 12 2z"/><path d="M12 9l-2 4 2 1.5L14 13l-2-4z"/></svg>
                    </span>
                    <span class="text-base font-bold tracking-tight text-gray-900">{{ $brandName }}</span>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-x-7 gap-y-2 text-theme-sm text-gray-500">
                    <a href="{{ route('register') }}" class="transition hover:text-brand-600">{{ __('Create workspace') }}</a>
                    <a href="{{ route('login') }}" class="transition hover:text-brand-600">{{ __('Sign in') }}</a>
                    <a href="#features" class="transition hover:text-brand-600">{{ __('Features') }}</a>
                    <a href="#contact" class="transition hover:text-brand-600">{{ __('Contact') }}</a>
                    @if($websiteUrl)<a href="{{ $websiteUrl }}" target="_blank" rel="noopener" class="transition hover:text-brand-600">{{ __('Website') }}</a>@endif
                    @if($facebookUrl)<a href="{{ $facebookUrl }}" target="_blank" rel="noopener" class="transition hover:text-brand-600">{{ __('Facebook') }}</a>@endif
                </div>

                <div class="flex items-center gap-3">
                    <div class="inline-flex items-center rounded-lg border border-gray-200 bg-gray-50 p-0.5" aria-label="{{ __('Language') }}">
                        <a href="{{ route('locale.switch', ['locale' => 'en']) }}" class="inline-flex h-7 items-center justify-center rounded-md px-2.5 text-theme-xs font-semibold transition {{ $localeBn ? 'text-gray-500 hover:text-gray-800' : 'bg-brand-500 text-white' }}">EN</a>
                        <a href="{{ route('locale.switch', ['locale' => 'bn']) }}" class="inline-flex h-7 items-center justify-center rounded-md px-2.5 text-theme-xs font-semibold transition {{ $localeBn ? 'bg-brand-500 text-white' : 'text-gray-500 hover:text-gray-800' }}">বাং</a>
                    </div>
                    <p class="text-theme-xs text-gray-400">© {{ date('Y') }} {{ $brandName }}. {{ __('All rights reserved.') }}</p>
                </div>
            </div>
        </footer>
    </body>
</html>
