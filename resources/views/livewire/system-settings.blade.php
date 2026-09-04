<div class="space-y-6">
    @php use Illuminate\Support\Facades\Storage; @endphp

    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">System settings</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Platform branding, locale defaults, invoicing, uploads, API, and security configuration.</p>
        </div>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <!-- BeeCore company & support details -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01"/></svg>
                </span>
                <div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">BeeCore details &amp; support</h2>
                    <p class="mt-0.5 text-theme-sm text-gray-500 dark:text-gray-400">All public information about the platform — it powers the landing page, sign-in screen and shared contact blocks.</p>
                </div>
            </div>
            <div class="mt-5 space-y-5">
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="ss-tagline" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Tagline</label>
                        <input id="ss-tagline" wire:model="platformTagline" type="text" placeholder="e.g. Everything an ISP needs to run" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        @error('platformTagline') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="ss-hours" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Support hours</label>
                        <input id="ss-hours" wire:model="supportHours" type="text" placeholder="e.g. Sat–Thu, 9:00 AM – 6:00 PM" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        @error('supportHours') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label for="ss-about" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">About the platform</label>
                    <textarea id="ss-about" wire:model="platformAbout" rows="3" placeholder="Short description shown on the homepage and register page…" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                    @error('platformAbout') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="ss-support-email" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Support email</label>
                        <input id="ss-support-email" wire:model="contactEmail" type="email" placeholder="e.g. support@beecore.com" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        @error('contactEmail') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="ss-support-phone" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Support phone / mobile</label>
                        <input id="ss-support-phone" wire:model="contactPhone" type="text" placeholder="e.g. +880 1XXX-XXXXXX" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        @error('contactPhone') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="ss-website" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Website</label>
                        <input id="ss-website" wire:model="websiteUrl" type="url" placeholder="https://beecore.com" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        @error('websiteUrl') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="ss-facebook" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Facebook page</label>
                        <input id="ss-facebook" wire:model="facebookUrl" type="url" placeholder="https://facebook.com/beecore" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        @error('facebookUrl') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label for="ss-address" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Head office / address</label>
                    <textarea id="ss-address" wire:model="contactAddress" rows="2" placeholder="Company address shown on the landing page footer…" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                    @error('contactAddress') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Branding -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.2"/><rect x="14" y="3" width="7" height="7" rx="1.2"/><rect x="3" y="14" width="7" height="7" rx="1.2"/><rect x="14" y="14" width="7" height="7" rx="1.2"/></svg>
                </span>
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Branding</h2>
            </div>
            <div class="mt-5 space-y-5">
                <div>
                    <label for="ss-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Platform name</label>
                    <input id="ss-name" wire:model="platformName" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    @error('platformName') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="rounded-xl border border-gray-100 p-4 dark:border-gray-800">
                        <label for="ss-logo" class="mb-2 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Platform logo</label>
                        <input id="ss-logo" wire:model="logo" type="file" accept="image/*" class="h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-theme-sm text-gray-500 shadow-theme-xs transition-colors file:mr-5 file:border-0 file:bg-gray-50 file:px-3.5 file:py-3 file:text-theme-sm file:font-medium file:text-gray-700 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:hover:file:bg-white/[0.05]">
                        @error('logo') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        @if($currentLogoPath)
                            <div class="mt-3 flex items-center gap-3 rounded-lg bg-gray-50 p-2 dark:bg-white/[0.02]">
                                <img src="{{ Storage::url($currentLogoPath) }}" alt="Current logo" class="h-9 max-w-44 rounded object-contain dark:invert">
                                <span class="text-theme-xs text-gray-400 dark:text-gray-500">Current</span>
                            </div>
                        @endif
                    </div>
                    <div class="rounded-xl border border-gray-100 p-4 dark:border-gray-800">
                        <label for="ss-favicon" class="mb-2 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Favicon</label>
                        <input id="ss-favicon" wire:model="favicon" type="file" accept="image/*" class="h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-theme-sm text-gray-500 shadow-theme-xs transition-colors file:mr-5 file:border-0 file:bg-gray-50 file:px-3.5 file:py-3 file:text-theme-sm file:font-medium file:text-gray-700 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:hover:file:bg-white/[0.05]">
                        @error('favicon') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        @if($currentFaviconPath)
                            <div class="mt-3 flex items-center gap-3 rounded-lg bg-gray-50 p-2 dark:bg-white/[0.02]">
                                <img src="{{ Storage::url($currentFaviconPath) }}" alt="Current favicon" class="size-8 rounded object-contain dark:invert">
                                <span class="text-theme-xs text-gray-400 dark:text-gray-500">Current</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Locale & formatting -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-cyan-500/10 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-400">
                    <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </span>
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Locale &amp; formatting</h2>
            </div>
            <div class="mt-5 space-y-5">
                <div class="grid gap-5 md:grid-cols-3">
                    <div>
                        <label for="ss-lang" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Default language</label>
                        <select id="ss-lang" wire:model="defaultLanguage" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                            @foreach($languages as $lang)<option value="{{ $lang->code }}">{{ $lang->name }}</option>@endforeach
                        </select>
                        @error('defaultLanguage') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="ss-currency" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Default currency</label>
                        <select id="ss-currency" wire:model="defaultCurrency" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                            @foreach($currencies as $currency)<option value="{{ $currency->code }}">{{ $currency->name }}</option>@endforeach
                        </select>
                        @error('defaultCurrency') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="ss-timezone" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Default timezone</label>
                        <input id="ss-timezone" wire:model="defaultTimezone" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        @error('defaultTimezone') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="ss-date-format" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Date format</label>
                        <input id="ss-date-format" wire:model="dateFormat" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        @error('dateFormat') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="ss-time-format" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Time format</label>
                        <input id="ss-time-format" wire:model="timeFormat" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        @error('timeFormat') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice settings -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-violet-500/10 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400">
                    <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </span>
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Invoice settings</h2>
            </div>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div>
                    <label for="ss-invoice-prefix" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Invoice number prefix</label>
                    <input id="ss-invoice-prefix" wire:model="invoicePrefix" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    @error('invoicePrefix') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="ss-invoice-due" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Default due days</label>
                    <input id="ss-invoice-due" wire:model="invoiceDueDays" type="number" min="0" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    @error('invoiceDueDays') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Bee gateway -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-amber-500/10 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">
                    <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </span>
                <div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Bee Payment Gateway fee</h2>
                    <p class="mt-0.5 text-theme-sm text-gray-500 dark:text-gray-400">Processing fee shown to ISP customers. ISPs just enable the gateway — they cannot edit this.</p>
                </div>
            </div>
            <div class="mt-5 max-w-xs">
                <label for="ss-bee-fee" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Processing fee (%)</label>
                <div class="relative">
                    <input id="ss-bee-fee" wire:model="beeFeePercent" type="number" min="0" max="50" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-theme-sm text-gray-400">%</span>
                </div>
                <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">Deducted from each customer payment made through the Bee gateway.</p>
                @error('beeFeePercent') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- File uploads & storage -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-fuchsia-500/10 text-fuchsia-600 dark:bg-fuchsia-500/15 dark:text-fuchsia-400">
                    <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                </span>
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">File uploads &amp; storage</h2>
            </div>
            <div class="mt-5 grid gap-5 md:grid-cols-3">
                <div>
                    <label for="ss-upload-max" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Max upload size (MB)</label>
                    <input id="ss-upload-max" wire:model="fileUploadMaxMb" type="number" min="1" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    @error('fileUploadMaxMb') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="ss-file-types" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Allowed file types</label>
                    <input id="ss-file-types" wire:model="allowedFileTypes" type="text" placeholder="jpg,png,pdf" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    @error('allowedFileTypes') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="ss-storage-disk" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Storage disk</label>
                    <input id="ss-storage-disk" wire:model="storageDisk" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    @error('storageDisk') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- API & rate limits -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-sky-500/10 text-sky-600 dark:bg-sky-500/15 dark:text-sky-400">
                    <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                </span>
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">API &amp; rate limits</h2>
            </div>
            <div class="mt-5 max-w-xl">
                <label for="ss-api-limit" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Default API rate limit (requests/min)</label>
                <input id="ss-api-limit" wire:model="apiRateLimitPerMinute" type="number" min="1" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                @error('apiRateLimitPerMinute') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Security & session -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                    <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </span>
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Security &amp; session</h2>
            </div>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div>
                    <label for="ss-session-lifetime" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Session lifetime (minutes)</label>
                    <input id="ss-session-lifetime" wire:model="sessionLifetimeMinutes" type="number" min="5" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    @error('sessionLifetimeMinutes') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="ss-password-min" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Minimum password length</label>
                    <input id="ss-password-min" wire:model="passwordMinLength" type="number" min="6" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    @error('passwordMinLength') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Save bar -->
        <div class="flex items-center justify-end gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="mr-auto text-theme-xs text-gray-400 dark:text-gray-500">Settings apply across every workspace immediately.</p>
            <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save settings
            </button>
        </div>
    </form>
</div>
