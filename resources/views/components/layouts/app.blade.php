<!DOCTYPE html>
@php
    $user = auth()->user();
    $isSaasAdmin = $user?->isSuperAdmin() && !session('impersonated_tenant_id');
    $hasTenantContext = (bool) ($user?->tenant_id || session('impersonated_tenant_id'));

    $workspaceTenant = session('impersonated_tenant_id')
        ? \App\Models\Tenant::query()->whereKey(session('impersonated_tenant_id'))->first()
        : $user?->tenant()->where('status', 'active')->first();
    $workspaceAutomatic = $workspaceTenant?->isAutomatic() ?? true;

    $globalSearch = [['group' => 'Main', 'title' => 'Dashboard', 'subtitle' => 'Overview & live metrics', 'href' => route('dashboard')]];

    if ($isSaasAdmin) {
        foreach (config('super_admin_menu', []) as $menuGroup) {
            foreach ($menuGroup['items'] as $menuItem) {
                if (! isset($menuItem['route'])) {
                    continue;
                }
                $globalSearch[] = ['group' => $menuGroup['group'], 'title' => $menuItem['label'], 'subtitle' => 'Open '.$menuGroup['group'], 'href' => route($menuItem['route'])];
            }
        }
    }

    if ($hasTenantContext) {
        $tenantRole = $user->role;
        $tenantLinks = [
            ['route' => 'customers', 'label' => 'Customers', 'roles' => [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN, \App\Models\User::ROLE_SUPPORT, \App\Models\User::ROLE_NETWORK_ENGINEER]],
            ['route' => 'packages', 'label' => 'Packages', 'roles' => [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN]],
            ['route' => 'billing', 'label' => 'Billing', 'roles' => [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN, \App\Models\User::ROLE_FINANCE]],
            ['route' => 'payments', 'label' => 'Payments', 'roles' => [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN, \App\Models\User::ROLE_FINANCE]],
            ['route' => 'network', 'label' => 'Network', 'roles' => [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN, \App\Models\User::ROLE_NETWORK_ENGINEER], 'automaticOnly' => true],
            ['route' => 'resellers', 'label' => 'Resellers', 'roles' => [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN]],
            ['route' => 'reports', 'label' => 'Reports', 'roles' => [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN, \App\Models\User::ROLE_FINANCE, \App\Models\User::ROLE_SUPPORT, \App\Models\User::ROLE_NETWORK_ENGINEER]],
            ['route' => 'isp-settings', 'label' => 'Settings', 'roles' => [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN]],
            ['route' => 'isp-subscription', 'label' => 'My Subscription', 'roles' => [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN]],
            ['route' => 'isp-gateway', 'label' => 'Payment Gateway', 'roles' => [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN]],
        ];

        foreach ($tenantLinks as $link) {
            if (in_array($tenantRole, $link['roles'], true) && ! (($link['automaticOnly'] ?? false) && ! $workspaceAutomatic)) {
                $globalSearch[] = ['group' => 'ISP workspace', 'title' => $link['label'], 'subtitle' => ucfirst($link['route']), 'href' => route($link['route'])];
            }
        }

        $recentCustomers = \App\Models\Customer::query()
            ->where('tenant_id', $workspaceTenant?->id ?? $user->tenant_id)
            ->latest()->limit(6)->get(['name', 'email']);

        foreach ($recentCustomers as $customer) {
            $globalSearch[] = ['group' => 'Customers', 'title' => $customer->name, 'subtitle' => $customer->email ?: 'Customer record', 'href' => route('customers')];
        }
    } else {
        $recentTenants = \App\Models\Tenant::query()->where('status', 'active')->latest()->limit(6)->get();
        foreach ($recentTenants as $tenant) {
            $globalSearch[] = ['group' => 'Tenants', 'title' => $tenant->name, 'subtitle' => 'Active ISP workspace', 'href' => route('tenant-details', $tenant)];
        }
    }
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ config('app.name', 'BeeCore') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        class="font-outfit antialiased"
        x-data="{
            darkMode: localStorage.getItem('beecore_theme') === 'dark',
            sidebarOpen: false,
            sidebarCollapsed: localStorage.getItem('beecore_sidebar_collapsed') === '1',
            userMenuOpen: false,
            langOpen: false,
            globalSearchOpen: false,
            globalQuery: '',
            globalSearch: @js($globalSearch),
            toggleDark() {
                this.darkMode = !this.darkMode;
                localStorage.setItem('beecore_theme', this.darkMode ? 'dark' : 'light');
                window.dispatchEvent(new CustomEvent('beecore:theme'));
            },
            toggleSidebar() {
                if (window.matchMedia('(min-width: 1024px)').matches) {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                    localStorage.setItem('beecore_sidebar_collapsed', this.sidebarCollapsed ? '1' : '0');
                } else {
                    this.sidebarOpen = !this.sidebarOpen;
                }
            },
            onSlash($event) {
                const tag = ($event.target.tagName || '').toLowerCase();
                if (tag === 'input' || tag === 'textarea' || tag === 'select' || $event.target.isContentEditable) return;
                $event.preventDefault();
                this.openGlobalSearch();
            },
            openGlobalSearch() {
                this.globalSearchOpen = true;
                this.$nextTick(() => this.$refs.globalSearchInput?.focus());
            },
            filteredGlobalResults() {
                const q = this.globalQuery.trim().toLowerCase();
                if (!q) return this.globalSearch.slice(0, 8);
                return this.globalSearch
                    .filter((item) => (item.title + ' ' + item.subtitle + ' ' + item.group).toLowerCase().includes(q))
                    .slice(0, 10);
            },
        }"
        :class="darkMode ? 'dark bg-gray-900' : 'bg-gray-50'"
        @keydown.escape.window="sidebarOpen = false; userMenuOpen = false; langOpen = false; globalSearchOpen = false"
        @keydown.slash.window="onSlash($event)"
        @keydown.meta.k.window.prevent="openGlobalSearch()"
        @keydown.ctrl.k.window.prevent="openGlobalSearch()"
    >
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[999999] focus:rounded-lg focus:bg-brand-500 focus:px-4 focus:py-2 focus:text-white">{{ __('Skip to content') }}</a>

        <!-- Page Wrapper -->
        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <x-sidebar />
            <!-- Sidebar End -->

            <!-- Content Area -->
            <div class="relative flex flex-1 flex-col overflow-x-hidden overflow-y-auto">
                <!-- Small device overlay -->
                <div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-gray-900/60 backdrop-blur-sm lg:hidden"></div>

                <!-- Header -->
                <header class="sticky top-0 z-50 flex w-full border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex grow flex-col items-center justify-between lg:flex-row lg:px-6">
                        <div class="flex w-full items-center gap-2 border-b border-gray-200 px-3 py-3 sm:gap-3 lg:justify-start lg:border-b-0 lg:px-0 lg:py-4 dark:border-gray-800">
                            <!-- Sidebar toggle (mobile drawer + desktop collapse) -->
                            <button type="button" @click="toggleSidebar()" aria-label="Toggle navigation" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white">
                                <svg class="fill-current" width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M3.25 6C3.25 5.58579 3.58579 5.25 4 5.25L20 5.25C20.4142 5.25 20.75 5.58579 20.75 6C20.75 6.41421 20.4142 6.75 20 6.75L4 6.75C3.58579 6.75 3.25 6.41422 3.25 6ZM3.25 18C3.25 17.5858 3.58579 17.25 4 17.25L20 17.25C20.4142 17.25 20.75 17.5858 20.75 18C20.75 18.4142 20.4142 18.75 20 18.75L4 18.75C3.58579 18.75 3.25 18.4142 3.25 18ZM4 11.25C3.58579 11.25 3.25 11.5858 3.25 12C3.25 12.4142 3.58579 12.75 4 12.75L12 12.75C12.4142 12.75 12.75 12.4142 12.75 12C12.75 11.5858 12.4142 11.25 12 11.25L4 11.25Z"/></svg>
                            </button>

                            <!-- Brand (mobile) -->
                            <a href="{{ route('dashboard') }}" @click="sidebarOpen = false" class="flex shrink-0 items-center gap-2.5 lg:hidden">
                                <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-500 text-base font-bold text-white">B</span>
                                <span class="text-lg font-bold text-gray-800 dark:text-white">BeeCore</span>
                            </a>

                            <!-- Global search -->
                            <div class="relative min-w-0 flex-1 lg:max-w-md">
                                <button type="button" @click="openGlobalSearch()" class="flex h-10 w-full items-center gap-2.5 rounded-lg border border-gray-300 bg-transparent px-3.5 text-theme-sm text-gray-400 shadow-theme-xs transition hover:border-brand-300 hover:text-gray-500 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:placeholder:text-white/30 dark:hover:border-gray-600 dark:focus:border-brand-800 dark:hover:text-gray-300">
                                    <svg class="size-4 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                    <span class="min-w-0 flex-1 truncate text-left text-theme-sm">{{ __('Search pages, customers, tenants...') }}</span>
                                    <kbd class="hidden shrink-0 rounded border border-gray-200 bg-gray-50 px-1.5 py-0.5 text-theme-xs font-medium text-gray-400 sm:inline-block dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500">/</kbd>
                                </button>
                            </div>
                        </div>

                        <div class="flex w-full items-center justify-end gap-2 px-4 py-3 sm:gap-3 lg:px-0 lg:py-4">
                            @php
                                $languages = \App\Models\Language::query()->where('is_active', true)->orderByDesc('is_default')->orderBy('code')->get();
                                $currentLocale = auth()->user()?->language ?: session('locale', config('app.locale'));
                                $currentLang = $languages->firstWhere('code', $currentLocale) ?: $languages->first();
                            @endphp

                            <!-- Language switch (desktop only) -->
                            <div class="relative hidden lg:block" @click.outside="langOpen = false">
                                <button type="button" @click="langOpen = !langOpen" aria-label="Change language" title="Language" class="flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white">
                                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                </button>

                                <div x-show="langOpen" x-transition class="shadow-theme-lg absolute right-0 z-50 mt-3 w-52 rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800 dark:bg-gray-dark">
                                    <p class="px-3 pb-1.5 pt-1 text-theme-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ __('Language') }}</p>
                                    @forelse($languages as $lang)
                                        <form method="POST" action="{{ route('locale.switch') }}">
                                            @csrf
                                            <input type="hidden" name="locale" value="{{ $lang->code }}">
                                            <button type="submit" @click="langOpen = false" class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-theme-sm font-medium transition {{ ($currentLang && $currentLang->code === $lang->code) ? 'bg-gray-100 text-gray-900 dark:bg-white/5 dark:text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' }}">
                                                <span class="flex min-w-0 items-center gap-2.5">
                                                    <span class="grid size-6 shrink-0 place-items-center rounded border border-gray-200 text-theme-xs font-bold uppercase text-gray-500 dark:border-gray-700 dark:text-gray-400">{{ $lang->code }}</span>
                                                    <span class="truncate">{{ $lang->native_name ?: $lang->name }}</span>
                                                </span>
                                                @if($currentLang && $currentLang->code === $lang->code)
                                                    <svg class="size-4 shrink-0 stroke-brand-600 dark:stroke-brand-400" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                @endif
                                            </button>
                                        </form>
                                    @empty
                                        <p class="px-3 py-2 text-theme-xs text-gray-400">{{ __('No active languages.') }}</p>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Dark mode toggler (desktop only) -->
                            <button type="button" @click="toggleDark()" aria-label="Toggle dark mode" class="hidden h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 lg:flex dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white">
                                <svg class="hidden fill-current dark:block" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17.4547 11.97L18.1799 12.1611C18.265 11.8383 18.1265 11.4982 17.8401 11.3266C17.5538 11.1551 17.1885 11.1934 16.944 11.4207L17.4547 11.97ZM8.0306 2.5459L8.57989 3.05657C8.80718 2.81209 8.84554 2.44682 8.67398 2.16046C8.50243 1.8741 8.16227 1.73559 7.83948 1.82066L8.0306 2.5459ZM12.9154 13.0035C9.64678 13.0035 6.99707 10.3538 6.99707 7.08524H5.49707C5.49707 11.1823 8.81835 14.5035 12.9154 14.5035V13.0035ZM16.944 11.4207C15.8869 12.4035 14.4721 13.0035 12.9154 13.0035V14.5035C14.8657 14.5035 16.6418 13.7499 17.9654 12.5193L16.944 11.4207ZM16.7295 11.7789C15.9437 14.7607 13.2277 16.9586 10.0003 16.9586V18.4586C13.9257 18.4586 17.2249 15.7853 18.1799 12.1611L16.7295 11.7789ZM10.0003 16.9586C6.15734 16.9586 3.04199 13.8433 3.04199 10.0003H1.54199C1.54199 14.6717 5.32892 18.4586 10.0003 18.4586V16.9586ZM3.04199 10.0003C3.04199 6.77289 5.23988 4.05695 8.22173 3.27114L7.83948 1.82066C4.21532 2.77574 1.54199 6.07486 1.54199 10.0003H3.04199ZM6.99707 7.08524C6.99707 5.52854 7.5971 4.11366 8.57989 3.05657L7.48132 2.03522C6.25073 3.35885 5.49707 5.13487 5.49707 7.08524H6.99707Z"/></svg>
                                <svg class="fill-current dark:hidden" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.99998 1.5415C10.4142 1.5415 10.75 1.87729 10.75 2.2915V3.5415C10.75 3.95572 10.4142 4.2915 9.99998 4.2915C9.58577 4.2915 9.24998 3.95572 9.24998 3.5415V2.2915C9.24998 1.87729 9.58577 1.5415 9.99998 1.5415ZM10.0009 6.79327C8.22978 6.79327 6.79402 8.22904 6.79402 10.0001C6.79402 11.7712 8.22978 13.207 10.0009 13.207C11.772 13.207 13.2078 11.7712 13.2078 10.0001C13.2078 8.22904 11.772 6.79327 10.0009 6.79327ZM5.29402 10.0001C5.29402 7.40061 7.40135 5.29327 10.0009 5.29327C12.6004 5.29327 14.7078 7.40061 14.7078 10.0001C14.7078 12.5997 12.6004 14.707 10.0009 14.707C7.40135 14.707 5.29402 12.5997 5.29402 10.0001ZM15.9813 5.08035C16.2742 4.78746 16.2742 4.31258 15.9813 4.01969C15.6884 3.7268 15.2135 3.7268 14.9207 4.01969L14.0368 4.90357C13.7439 5.19647 13.7439 5.67134 14.0368 5.96423C14.3297 6.25713 14.8045 6.25713 15.0974 5.96423L15.9813 5.08035ZM18.4577 10.0001C18.4577 10.4143 18.1219 10.7501 17.7077 10.7501H16.4577C16.0435 10.7501 15.7077 10.4143 15.7077 10.0001C15.7077 9.58592 16.0435 9.25013 16.4577 9.25013H17.7077C18.1219 9.25013 18.4577 9.58592 18.4577 10.0001ZM14.9207 15.9806C15.2135 16.2735 15.6884 16.2735 15.9813 15.9806C16.2742 15.6877 16.2742 15.2128 15.9813 14.9199L15.0974 14.036C14.8045 13.7431 14.3297 13.7431 14.0368 14.036C13.7439 14.3289 13.7439 14.8038 14.0368 15.0967L14.9207 15.9806ZM9.99998 15.7088C10.4142 15.7088 10.75 16.0445 10.75 16.4588V17.7088C10.75 18.123 10.4142 18.4588 9.99998 18.4588C9.58577 18.4588 9.24998 18.123 9.24998 17.7088V16.4588C9.24998 16.0445 9.58577 15.7088 9.99998 15.7088ZM5.96356 15.0972C6.25646 14.8043 6.25646 14.3295 5.96356 14.0366C5.67067 13.7437 5.1958 13.7437 4.9029 14.0366L4.01902 14.9204C3.72613 15.2133 3.72613 15.6882 4.01902 15.9811C4.31191 16.274 4.78679 16.274 5.07968 15.9811L5.96356 15.0972ZM4.29224 10.0001C4.29224 10.4143 3.95645 10.7501 3.54224 10.7501H2.29224C1.87802 10.7501 1.54224 10.4143 1.54224 10.0001C1.54224 9.58592 1.87802 9.25013 2.29224 9.25013H3.54224C3.95645 9.25013 4.29224 9.58592 4.29224 10.0001ZM4.9029 5.9637C5.1958 6.25659 5.67067 6.25659 5.96356 5.9637C6.25646 5.6708 6.25646 5.19593 5.96356 4.90303L5.07968 4.01915C4.78679 3.72626 4.31191 3.72626 4.01902 4.01915C3.72613 4.31204 3.72613 4.78692 4.01902 5.07981L4.9029 5.9637Z"/></svg>
                            </button>

                            <!-- User dropdown -->
                            <div class="relative" @click.outside="userMenuOpen = false">
                                <button type="button" @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2 rounded-lg p-1 pr-2 transition hover:bg-gray-100 dark:hover:bg-gray-800">
                                    <span class="grid h-9 w-9 place-items-center rounded-full bg-brand-100 text-sm font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                                    <span class="hidden text-left sm:block">
                                        <span class="text-theme-sm block max-w-40 truncate font-medium text-gray-800 dark:text-white/90">{{ auth()->user()->name }}</span>
                                        <span class="text-theme-xs block capitalize text-gray-500 dark:text-gray-400">{{ str_replace('_', ' ', auth()->user()->role) }}</span>
                                    </span>
                                    <svg :class="userMenuOpen && 'rotate-180'" class="hidden stroke-gray-500 sm:block dark:stroke-gray-400" width="16" height="16" viewBox="0 0 18 20" fill="none"><path d="M4.3125 8.65625L9 13.3437L13.6875 8.65625" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>

                                <div x-show="userMenuOpen" x-transition class="shadow-theme-lg absolute right-0 z-50 mt-3 flex max-h-[85vh] w-[270px] flex-col overflow-y-auto rounded-2xl border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-dark">
                                    <div class="border-b border-gray-200 pb-3 dark:border-gray-800">
                                        <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ auth()->user()->name }}</p>
                                        <p class="text-theme-xs mt-0.5 truncate text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
                                    </div>
                                    <ul class="flex flex-col gap-1 py-2">
                                        <li>
                                            <a href="{{ route('my-profile') }}" @click="userMenuOpen = false" class="flex items-center gap-3 rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-200">
                                                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.7"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                                {{ __('My profile') }}
                                            </a>
                                        </li>
                                    </ul>

                                    <!-- Mobile/tablet: theme + language live in this menu (desktop uses the header icons) -->
                                    <div class="mb-1 border-t border-gray-100 pt-2 lg:hidden dark:border-gray-800">
                                        <button type="button" @click="toggleDark(); userMenuOpen = false" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">
                                            <svg x-show="!darkMode" x-cloak class="size-5 shrink-0 stroke-current text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="none" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                                            <svg x-show="darkMode" x-cloak class="size-5 shrink-0 stroke-current text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="none" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                                            <span class="flex-1 text-left" x-text="darkMode ? '{{ __('Light mode') }}' : '{{ __('Dark mode') }}'"></span>
                                        </button>

                                        <p class="px-3 pb-1.5 pt-2.5 text-theme-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ __('Language') }}</p>
                                        @forelse($languages as $lang)
                                            <form method="POST" action="{{ route('locale.switch') }}">
                                                @csrf
                                                <input type="hidden" name="locale" value="{{ $lang->code }}">
                                                <button type="submit" class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-theme-sm font-medium transition {{ ($currentLang && $currentLang->code === $lang->code) ? 'bg-gray-100 text-gray-900 dark:bg-white/5 dark:text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' }}">
                                                    <span class="flex min-w-0 items-center gap-2.5">
                                                        <span class="grid size-6 shrink-0 place-items-center rounded border border-gray-200 text-theme-xs font-bold uppercase text-gray-500 dark:border-gray-700 dark:text-gray-400">{{ $lang->code }}</span>
                                                        <span class="truncate">{{ $lang->native_name ?: $lang->name }}</span>
                                                    </span>
                                                    @if($currentLang && $currentLang->code === $lang->code)
                                                        <svg class="size-4 shrink-0 stroke-brand-600 dark:stroke-brand-400" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                    @endif
                                                </button>
                                            </form>
                                        @empty
                                            <p class="px-3 py-2 text-theme-xs text-gray-400">{{ __('No active languages.') }}</p>
                                        @endforelse
                                    </div>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-theme-sm font-medium text-error-600 hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10">
                                            <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.7"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                            {{ __('Sign out') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
                <!-- Header End -->

                <!-- Main Content -->
                <main id="main-content" class="flex-1 px-4 py-6 md:px-6 lg:py-8">
                    <div class="mx-auto w-full max-w-[1536px] space-y-6">
                        @if(session('impersonated_tenant_id'))
                            <div class="flex flex-col gap-3 rounded-2xl border border-warning-200 bg-warning-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between dark:border-warning-500/20 dark:bg-warning-500/10">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 place-items-center rounded-full bg-warning-100 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                                        <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                                    </span>
                                    <div>
                                        <p class="text-theme-sm font-semibold text-warning-700 dark:text-warning-400">{{ __('Viewing tenant workspace — :name', ['name' => session('impersonated_tenant_name')]) }}</p>
                                        <p class="text-theme-xs text-warning-600/80 dark:text-warning-500/70">{{ __('Actions here are performed as the tenant workspace and are fully audited.') }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('leave-impersonation') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-warning-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-warning-600">{{ __('Exit workspace') }}</a>
                            </div>
                        @endif

                        {{ $slot }}
                    </div>
                </main>
                <!-- Main Content End -->
            </div>
            <!-- Content Area End -->
        </div>
        <!-- Page Wrapper End -->

        <!-- Global search overlay -->
        <div
            x-cloak
            x-show="globalSearchOpen"
            x-transition.opacity
            class="fixed inset-0 z-[90] flex items-start justify-center px-4 pt-16 sm:pt-24"
            @click.self="globalSearchOpen = false"
            role="dialog"
            aria-modal="true"
            aria-label="Global search"
        >
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="globalSearchOpen = false"></div>

            <div class="relative w-full max-w-xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3 border-b border-gray-100 px-4 py-3.5 dark:border-gray-800">
                    <svg class="size-5 shrink-0 stroke-current text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input
                        x-ref="globalSearchInput"
                        x-model="globalQuery"
                        type="text"
                        placeholder="{{ __('Search pages, customers, tenants...') }}"
                        @keydown.escape.prevent="globalSearchOpen = false"
                        class="h-9 w-full bg-transparent text-theme-sm text-gray-800 placeholder:text-gray-400 focus:outline-hidden dark:text-white/90 dark:placeholder:text-white/30"
                    >
                    <kbd class="rounded border border-gray-200 bg-gray-50 px-1.5 py-0.5 text-theme-xs text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500">esc</kbd>
                </div>

                <div class="no-scrollbar max-h-[50vh] overflow-y-auto p-2">
                    <template x-for="(item, index) in filteredGlobalResults()" :key="item.group + ':' + item.title + ':' + index">
                        <div>
                            <p
                                x-show="index === 0 || filteredGlobalResults()[index - 1].group !== item.group"
                                x-text="item.group"
                                class="px-3 pt-3 pb-1.5 text-theme-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500"
                            ></p>
                            <a
                                :href="item.href"
                                @click="globalSearchOpen = false; globalQuery = ''"
                                class="flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 transition hover:bg-gray-100 dark:hover:bg-white/[0.04]"
                            >
                                <span class="min-w-0">
                                    <span class="block truncate text-theme-sm font-medium text-gray-800 dark:text-white/90" x-text="item.title"></span>
                                    <span class="mt-0.5 block truncate text-theme-xs text-gray-500 dark:text-gray-400" x-text="item.subtitle"></span>
                                </span>
                                <svg class="size-4 shrink-0 stroke-current text-gray-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </template>

                    <div x-show="filteredGlobalResults().length === 0" class="px-4 py-10 text-center">
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                            {{ __('No results for') }} "<span class="font-medium text-gray-700 dark:text-gray-200" x-text="globalQuery"></span>"
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Global sensitive-action confirmation modal -->
        <x-confirm-dialog />
    </body>
</html>
