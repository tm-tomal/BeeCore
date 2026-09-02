<div class="space-y-6">
    @php use Illuminate\Support\Facades\Storage; @endphp

    <!-- Page header -->
    <header>
        <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
        <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">System settings</h1>
        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Platform branding, locale defaults, invoicing, uploads, API, and security configuration.</p>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <!-- Branding -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Branding</h2>
            <div class="mt-5 space-y-5">
                <div>
                    <label for="ss-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Platform name</label>
                    <input id="ss-name" wire:model="platformName" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    @error('platformName') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="ss-logo" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Platform logo</label>
                        <input id="ss-logo" wire:model="logo" type="file" accept="image/*" class="w-full rounded-lg border border-gray-300 bg-transparent text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        @error('logo') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        @if($currentLogoPath)
                            <img src="{{ Storage::url($currentLogoPath) }}" alt="Current logo" class="mt-3 h-10 rounded-lg border border-gray-200 bg-white object-contain p-1 dark:border-gray-800">
                        @endif
                    </div>
                    <div>
                        <label for="ss-favicon" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Favicon</label>
                        <input id="ss-favicon" wire:model="favicon" type="file" accept="image/*" class="w-full rounded-lg border border-gray-300 bg-transparent text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        @error('favicon') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        @if($currentFaviconPath)
                            <img src="{{ Storage::url($currentFaviconPath) }}" alt="Current favicon" class="mt-3 h-8 rounded-lg border border-gray-200 bg-white object-contain p-1 dark:border-gray-800">
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Locale & formatting -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Locale &amp; formatting</h2>
            <div class="mt-5 space-y-5">
                <div class="grid gap-5 md:grid-cols-3">
                    <div>
                        <label for="ss-lang" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Default language</label>
                        <select id="ss-lang" wire:model="defaultLanguage" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                            @foreach($languages as $lang)
                                <option value="{{ $lang->code }}">{{ $lang->name }}</option>
                            @endforeach
                        </select>
                        @error('defaultLanguage') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="ss-currency" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Default currency</label>
                        <select id="ss-currency" wire:model="defaultCurrency" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                            @foreach($currencies as $currency)
                                <option value="{{ $currency->code }}">{{ $currency->name }}</option>
                            @endforeach
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
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Invoice settings</h2>
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

        <!-- File uploads & storage -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">File uploads &amp; storage</h2>
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
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">API &amp; rate limits</h2>
            <div class="mt-5 max-w-xl">
                <label for="ss-api-limit" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Default API rate limit (requests/min)</label>
                <input id="ss-api-limit" wire:model="apiRateLimitPerMinute" type="number" min="1" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                @error('apiRateLimitPerMinute') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Security & session -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Security &amp; session</h2>
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

        <div class="flex justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
            <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Save settings</button>
        </div>
    </form>
</div>
