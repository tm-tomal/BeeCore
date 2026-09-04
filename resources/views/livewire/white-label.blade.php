<div class="space-y-6">
    @php
        $brandedCount = \App\Models\TenantBranding::query()->where('is_enabled', true)->count();
        $domainCount = $tenants->filter(fn ($t) => filled($t->custom_domain))->count();
    @endphp

    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">White label</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Per-tenant branding: logo, colors, custom domain, and where the branding is applied.</p>
        </div>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Overview -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.2"/><rect x="14" y="3" width="7" height="7" rx="1.2"/><rect x="3" y="14" width="7" height="7" rx="1.2"/><rect x="14" y="14" width="7" height="7" rx="1.2"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $tenants->count() }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Tenants</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-success-500/10 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7L9 18l-5-5"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $brandedCount }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">White label enabled</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-cyan-500/10 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $domainCount }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Custom domains</p>
            </div>
        </div>
    </section>

    <!-- Tenant picker -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <label for="wl-tenant" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Tenant workspace</label>
        <div class="relative max-w-md">
            <svg class="pointer-events-none absolute inset-y-0 left-0 ml-4 size-5 self-center stroke-current text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            <select id="wl-tenant" wire:model.live="selectedTenantId" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent py-2.5 pl-11 pr-4 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                <option value="">Select a tenant</option>
                @foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach
            </select>
        </div>
    </div>

    @if($selectedTenantId)
        @php
            $tenant = $tenants->firstWhere('id', $selectedTenantId);
            $domain = $tenant->custom_domain ?? ($tenant->subdomain ? $tenant->subdomain.'.beecore.app' : 'No domain set');
        @endphp
        <form wire:submit="save" class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="grid size-10 shrink-0 place-items-center rounded-lg {{ $isEnabled ? 'bg-success-500/10 text-success-600 dark:bg-success-500/15 dark:text-success-400' : 'bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500' }}">
                            <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </span>
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $tenant->name }}</h2>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">White-label branding status for this workspace.</p>
                        </div>
                    </div>
                    <label class="inline-flex cursor-pointer items-center gap-2.5 text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                        <span class="relative">
                            <input wire:model="isEnabled" type="checkbox" class="peer sr-only">
                            <span class="block h-6 w-11 rounded-full bg-gray-200 transition peer-checked:bg-success-500 dark:bg-gray-700"></span>
                            <span class="absolute left-1 top-1 size-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                        </span>
                        <span class="{{ $isEnabled ? 'text-success-600 dark:text-success-400' : 'text-gray-500 dark:text-gray-400' }}">{{ $isEnabled ? 'White label enabled' : 'White label disabled' }}</span>
                    </label>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-2 rounded-xl bg-gray-50 px-4 py-3 dark:bg-white/[0.02]">
                    <svg class="size-4 stroke-current text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">Resolved domain:</p>
                    <code class="rounded bg-white px-1.5 py-0.5 font-mono text-theme-xs text-brand-600 dark:bg-gray-800 dark:text-brand-400">{{ $domain }}</code>
                    <p class="text-theme-xs text-gray-400 dark:text-gray-500">(set during ISP onboarding / tenant edit)</p>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                        <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>
                    </span>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Brand identity</h2>
                </div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="wl-brand-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Brand name</label>
                        <input id="wl-brand-name" wire:model="brandName" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="{{ $tenant->name }}">
                        @error('brandName')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="wl-brand-color" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Brand color</label>
                        <div class="flex items-center gap-2">
                            <input id="wl-brand-color" wire:model="brandColor" type="color" class="h-11 w-16 shrink-0 cursor-pointer rounded-lg border border-gray-300 bg-transparent p-1 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900">
                            <div class="flex h-11 flex-1 items-center justify-between rounded-lg border border-gray-200 px-3.5 dark:border-gray-700">
                                <code class="font-mono text-theme-sm uppercase text-gray-600 dark:text-gray-300">{{ $brandColor }}</code>
                                <span class="size-4 rounded-full ring-1 ring-inset ring-black/10" style="background-color: {{ $brandColor }}"></span>
                            </div>
                        </div>
                        @error('brandColor')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-gray-100 p-4 dark:border-gray-800">
                        <label for="wl-logo" class="mb-2 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Logo</label>
                        <input id="wl-logo" wire:model="logo" type="file" accept="image/*" class="h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-theme-sm text-gray-500 shadow-theme-xs transition-colors file:mr-5 file:border-0 file:bg-gray-50 file:px-3.5 file:py-3 file:text-theme-sm file:font-medium file:text-gray-700 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:hover:file:bg-white/[0.05]">
                        @error('logo')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        @if($branding?->logo_path)
                            <div class="mt-3 flex items-center gap-3 rounded-lg bg-gray-50 p-2 dark:bg-white/[0.02]">
                                <img src="{{ Storage::url($branding->logo_path) }}" alt="Current logo" class="h-8 max-w-40 rounded object-contain dark:invert">
                                <span class="text-theme-xs text-gray-400 dark:text-gray-500">Current</span>
                            </div>
                        @endif
                    </div>
                    <div class="rounded-xl border border-gray-100 p-4 dark:border-gray-800">
                        <label for="wl-favicon" class="mb-2 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Favicon</label>
                        <input id="wl-favicon" wire:model="favicon" type="file" accept="image/*" class="h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-theme-sm text-gray-500 shadow-theme-xs transition-colors file:mr-5 file:border-0 file:bg-gray-50 file:px-3.5 file:py-3 file:text-theme-sm file:font-medium file:text-gray-700 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:hover:file:bg-white/[0.05]">
                        @error('favicon')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        @if($branding?->favicon_path)
                            <div class="mt-3 flex items-center gap-3 rounded-lg bg-gray-50 p-2 dark:bg-white/[0.02]">
                                <img src="{{ Storage::url($branding->favicon_path) }}" alt="Current favicon" class="size-8 rounded object-contain dark:invert">
                                <span class="text-theme-xs text-gray-400 dark:text-gray-500">Current</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-violet-500/10 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400">
                        <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    </span>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Customer app branding</h2>
                </div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="wl-app-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">App name</label>
                        <input id="wl-app-name" wire:model="appName" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="MyISP">
                        @error('appName')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-gray-100 p-4 dark:border-gray-800">
                        <label for="wl-app-icon" class="mb-2 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">App icon</label>
                        <input id="wl-app-icon" wire:model="appIcon" type="file" accept="image/*" class="h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-theme-sm text-gray-500 shadow-theme-xs transition-colors file:mr-5 file:border-0 file:bg-gray-50 file:px-3.5 file:py-3 file:text-theme-sm file:font-medium file:text-gray-700 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:hover:file:bg-white/[0.05]">
                        @error('appIcon')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        @if($branding?->app_icon_path)
                            <div class="mt-3 flex items-center gap-3 rounded-lg bg-gray-50 p-2 dark:bg-white/[0.02]">
                                <img src="{{ Storage::url($branding->app_icon_path) }}" alt="Current app icon" class="h-9 w-9 rounded-lg object-contain dark:invert">
                                <span class="text-theme-xs text-gray-400 dark:text-gray-500">Current</span>
                            </div>
                        @endif
                    </div>
                    <div class="rounded-xl border border-gray-100 p-4 dark:border-gray-800">
                        <label for="wl-splash" class="mb-2 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Splash screen</label>
                        <input id="wl-splash" wire:model="splashScreen" type="file" accept="image/*" class="h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-theme-sm text-gray-500 shadow-theme-xs transition-colors file:mr-5 file:border-0 file:bg-gray-50 file:px-3.5 file:py-3 file:text-theme-sm file:font-medium file:text-gray-700 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:hover:file:bg-white/[0.05]">
                        @error('splashScreen')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        @if($branding?->splash_screen_path)
                            <div class="mt-3 flex items-center gap-3 rounded-lg bg-gray-50 p-2 dark:bg-white/[0.02]">
                                <img src="{{ Storage::url($branding->splash_screen_path) }}" alt="Current splash screen" class="h-10 max-w-48 rounded object-cover">
                                <span class="text-theme-xs text-gray-400 dark:text-gray-500">Current</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-amber-500/10 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">
                        <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    </span>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Apply branding to</h2>
                </div>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    @php
                        $surfaces = [
                            ['key' => 'loginBrandingEnabled', 'label' => 'Login page', 'icon' => 'lock', 'bind' => $loginBrandingEnabled],
                            ['key' => 'dashboardBrandingEnabled', 'label' => 'Dashboard', 'icon' => 'grid', 'bind' => $dashboardBrandingEnabled],
                            ['key' => 'emailBrandingEnabled', 'label' => 'Email', 'icon' => 'mail', 'bind' => $emailBrandingEnabled],
                            ['key' => 'smsBrandingEnabled', 'label' => 'SMS', 'icon' => 'chat', 'bind' => $smsBrandingEnabled],
                            ['key' => 'customerAppBrandingEnabled', 'label' => 'Customer app', 'icon' => 'phone', 'bind' => $customerAppBrandingEnabled],
                        ];
                        $sIcons = [
                            'lock' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
                            'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.2"/><rect x="14" y="3" width="7" height="7" rx="1.2"/><rect x="14" y="14" width="7" height="7" rx="1.2"/><rect x="3" y="14" width="7" height="7" rx="1.2"/>',
                            'mail' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
                            'chat' => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>',
                            'phone' => '<rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>',
                        ];
                    @endphp
                    @foreach($surfaces as $surface)
                        <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border px-4 py-3 transition {{ $surface['bind'] ? 'border-success-200 bg-success-50/60 dark:border-success-500/25 dark:bg-success-500/10' : 'border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02] dark:hover:bg-white/[0.04]' }}">
                            <span class="flex items-center gap-3">
                                <span class="grid size-8 shrink-0 place-items-center rounded-lg {{ $surface['bind'] ? 'bg-success-500/15 text-success-600 dark:text-success-400' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.05] dark:text-gray-400' }}">
                                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $sIcons[$surface['icon']] !!}</svg>
                                </span>
                                <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ $surface['label'] }}</span>
                            </span>
                            <input wire:model="{{ $surface['key'] }}" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 bg-transparent text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="mr-auto text-theme-xs text-gray-400 dark:text-gray-500">Changes are applied to {{ $tenant->name }} right away.</p>
                <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save branding
                </button>
            </div>
        </form>
    @else
        <div class="grid place-items-center rounded-2xl border border-dashed border-gray-200 bg-white/50 px-5 py-16 text-center dark:border-gray-800 dark:bg-white/[0.02]">
            <div class="max-w-xs">
                <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                    <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>
                </span>
                <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">Select a tenant to manage branding</p>
                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Upload logos, pick a brand color and decide where your tenant's brand is applied.</p>
            </div>
        </div>
    @endif
</div>
