<div class="space-y-6">
    @php use Illuminate\Support\Facades\Storage; @endphp

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

    <div class="max-w-xs">
        <label for="wl-tenant" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Tenant</label>
        <select id="wl-tenant" wire:model.live="selectedTenantId" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
            <option value="">Select a tenant</option>
            @foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach
        </select>
    </div>

    @if($selectedTenantId)
        @php $tenant = $tenants->firstWhere('id', $selectedTenantId); @endphp
        <form wire:submit="save" class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Branding status</h2>
                    <label class="inline-flex cursor-pointer items-center gap-2.5 text-theme-sm text-gray-700 dark:text-gray-400"><input wire:model="isEnabled" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 bg-transparent text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">White label enabled</label>
                </div>
                <p class="mt-3 text-theme-sm text-gray-500 dark:text-gray-400">Custom domain: <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs text-brand-600 dark:bg-gray-800 dark:text-brand-400">{{ $tenant->custom_domain ?? $tenant->subdomain.'.beecore.app' }}</code> (set during ISP onboarding / tenant edit)</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Brand identity</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="wl-brand-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Brand name</label>
                        <input id="wl-brand-name" wire:model="brandName" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        @error('brandName')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="wl-brand-color" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Brand color</label>
                        <input id="wl-brand-color" wire:model="brandColor" type="color" class="h-11 w-full cursor-pointer rounded-lg border border-gray-300 bg-transparent px-1.5 py-1.5 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900">
                        @error('brandColor')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="wl-logo" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Logo</label>
                        <input id="wl-logo" wire:model="logo" type="file" accept="image/*" class="h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-theme-sm text-gray-500 shadow-theme-xs transition-colors file:mr-5 file:border-0 file:bg-gray-50 file:px-3.5 file:py-3 file:text-theme-sm file:font-medium file:text-gray-700 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:hover:file:bg-white/[0.05]">
                        @error('logo')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        @if($branding?->logo_path)<img src="{{ Storage::url($branding->logo_path) }}" alt="Current logo" class="mt-3 h-10">@endif
                    </div>
                    <div>
                        <label for="wl-favicon" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Favicon</label>
                        <input id="wl-favicon" wire:model="favicon" type="file" accept="image/*" class="h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-theme-sm text-gray-500 shadow-theme-xs transition-colors file:mr-5 file:border-0 file:bg-gray-50 file:px-3.5 file:py-3 file:text-theme-sm file:font-medium file:text-gray-700 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:hover:file:bg-white/[0.05]">
                        @error('favicon')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        @if($branding?->favicon_path)<img src="{{ Storage::url($branding->favicon_path) }}" alt="Current favicon" class="mt-3 h-8">@endif
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Customer app branding</h2>
                <div class="mt-5">
                    <label for="wl-app-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">App name</label>
                    <input id="wl-app-name" wire:model="appName" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    @error('appName')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="wl-app-icon" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">App icon</label>
                        <input id="wl-app-icon" wire:model="appIcon" type="file" accept="image/*" class="h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-theme-sm text-gray-500 shadow-theme-xs transition-colors file:mr-5 file:border-0 file:bg-gray-50 file:px-3.5 file:py-3 file:text-theme-sm file:font-medium file:text-gray-700 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:hover:file:bg-white/[0.05]">
                        @error('appIcon')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        @if($branding?->app_icon_path)<img src="{{ Storage::url($branding->app_icon_path) }}" alt="Current app icon" class="mt-3 h-10">@endif
                    </div>
                    <div>
                        <label for="wl-splash" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Splash screen</label>
                        <input id="wl-splash" wire:model="splashScreen" type="file" accept="image/*" class="h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-theme-sm text-gray-500 shadow-theme-xs transition-colors file:mr-5 file:border-0 file:bg-gray-50 file:px-3.5 file:py-3 file:text-theme-sm file:font-medium file:text-gray-700 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:hover:file:bg-white/[0.05]">
                        @error('splashScreen')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        @if($branding?->splash_screen_path)<img src="{{ Storage::url($branding->splash_screen_path) }}" alt="Current splash screen" class="mt-3 h-10">@endif
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Apply branding to</h2>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <label class="inline-flex cursor-pointer items-center gap-2.5 text-theme-sm text-gray-700 dark:text-gray-400"><input wire:model="loginBrandingEnabled" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 bg-transparent text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">Login page</label>
                    <label class="inline-flex cursor-pointer items-center gap-2.5 text-theme-sm text-gray-700 dark:text-gray-400"><input wire:model="dashboardBrandingEnabled" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 bg-transparent text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">Dashboard</label>
                    <label class="inline-flex cursor-pointer items-center gap-2.5 text-theme-sm text-gray-700 dark:text-gray-400"><input wire:model="emailBrandingEnabled" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 bg-transparent text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">Email</label>
                    <label class="inline-flex cursor-pointer items-center gap-2.5 text-theme-sm text-gray-700 dark:text-gray-400"><input wire:model="smsBrandingEnabled" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 bg-transparent text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">SMS</label>
                    <label class="inline-flex cursor-pointer items-center gap-2.5 text-theme-sm text-gray-700 dark:text-gray-400"><input wire:model="customerAppBrandingEnabled" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 bg-transparent text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">Customer app</label>
                </div>
            </div>

            <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Save branding</button>
        </form>
    @else
        <div class="rounded-2xl border border-dashed border-gray-200 bg-white/50 px-5 py-12 text-center dark:border-gray-800 dark:bg-white/[0.02]">
            <p class="text-theme-sm text-gray-500 dark:text-gray-400">Select a tenant to manage its white-label branding.</p>
        </div>
    @endif
</div>
