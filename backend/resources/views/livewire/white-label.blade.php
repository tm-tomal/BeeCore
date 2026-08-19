<div>
    @php use Illuminate\Support\Facades\Storage; @endphp
    <header class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
        <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">White label</h1>
        <p class="mt-2 text-sm text-slate-500">Per-tenant branding: logo, colors, custom domain, and where the branding is applied.</p>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <div class="mb-5 max-w-xs">
        <label class="bc-label" for="wl-tenant">Tenant</label>
        <select id="wl-tenant" wire:model.live="selectedTenantId" class="bc-field">
            <option value="">Select a tenant</option>
            @foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach
        </select>
    </div>

    @if($selectedTenantId)
        @php $tenant = $tenants->firstWhere('id', $selectedTenantId); @endphp
        <form wire:submit="save" class="space-y-6">
            <div class="bc-panel space-y-4 p-5" style="border-radius:8px">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-white">Branding status</h2>
                    <label class="inline-flex items-center gap-3 text-sm text-slate-300"><input wire:model="isEnabled" type="checkbox">White label enabled</label>
                </div>
                <p class="text-xs text-slate-500">Custom domain: <code class="text-teal-300">{{ $tenant->custom_domain ?? $tenant->subdomain.'.beecore.app' }}</code> (set during ISP onboarding / tenant edit)</p>
            </div>

            <div class="bc-panel space-y-4 p-5" style="border-radius:8px">
                <h2 class="font-bold text-white">Brand identity</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="bc-label" for="wl-brand-name">Brand name</label><input id="wl-brand-name" wire:model="brandName" class="bc-field">@error('brandName')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="wl-brand-color">Brand color</label><input id="wl-brand-color" wire:model="brandColor" type="color" class="bc-field h-11">@error('brandColor')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="bc-label" for="wl-logo">Logo</label><input id="wl-logo" wire:model="logo" type="file" accept="image/*" class="bc-field">@error('logo')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror @if($branding?->logo_path)<img src="{{ Storage::url($branding->logo_path) }}" alt="Current logo" class="mt-2 h-10">@endif</div>
                    <div><label class="bc-label" for="wl-favicon">Favicon</label><input id="wl-favicon" wire:model="favicon" type="file" accept="image/*" class="bc-field">@error('favicon')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror @if($branding?->favicon_path)<img src="{{ Storage::url($branding->favicon_path) }}" alt="Current favicon" class="mt-2 h-8">@endif</div>
                </div>
            </div>

            <div class="bc-panel space-y-4 p-5" style="border-radius:8px">
                <h2 class="font-bold text-white">Customer app branding</h2>
                <div><label class="bc-label" for="wl-app-name">App name</label><input id="wl-app-name" wire:model="appName" class="bc-field">@error('appName')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="bc-label" for="wl-app-icon">App icon</label><input id="wl-app-icon" wire:model="appIcon" type="file" accept="image/*" class="bc-field">@error('appIcon')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror @if($branding?->app_icon_path)<img src="{{ Storage::url($branding->app_icon_path) }}" alt="Current app icon" class="mt-2 h-10">@endif</div>
                    <div><label class="bc-label" for="wl-splash">Splash screen</label><input id="wl-splash" wire:model="splashScreen" type="file" accept="image/*" class="bc-field">@error('splashScreen')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror @if($branding?->splash_screen_path)<img src="{{ Storage::url($branding->splash_screen_path) }}" alt="Current splash screen" class="mt-2 h-10">@endif</div>
                </div>
            </div>

            <div class="bc-panel space-y-3 p-5" style="border-radius:8px">
                <h2 class="font-bold text-white">Apply branding to</h2>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="inline-flex items-center gap-3 text-sm text-slate-300"><input wire:model="loginBrandingEnabled" type="checkbox">Login page</label>
                    <label class="inline-flex items-center gap-3 text-sm text-slate-300"><input wire:model="dashboardBrandingEnabled" type="checkbox">Dashboard</label>
                    <label class="inline-flex items-center gap-3 text-sm text-slate-300"><input wire:model="emailBrandingEnabled" type="checkbox">Email</label>
                    <label class="inline-flex items-center gap-3 text-sm text-slate-300"><input wire:model="smsBrandingEnabled" type="checkbox">SMS</label>
                    <label class="inline-flex items-center gap-3 text-sm text-slate-300"><input wire:model="customerAppBrandingEnabled" type="checkbox">Customer app</label>
                </div>
            </div>

            <button type="submit" class="bc-primary">Save branding</button>
        </form>
    @else
        <p class="py-12 text-center text-slate-600">Select a tenant to manage its white-label branding.</p>
    @endif
</div>
