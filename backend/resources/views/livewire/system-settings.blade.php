<div>
    @php use Illuminate\Support\Facades\Storage; @endphp
    <header class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
        <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">System settings</h1>
        <p class="mt-2 text-sm text-slate-500">Platform branding, locale defaults, invoicing, uploads, API, and security configuration.</p>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="bc-panel space-y-4 p-5" style="border-radius:8px">
            <h2 class="font-bold text-white">Branding</h2>
            <div><label class="bc-label" for="ss-name">Platform name</label><input id="ss-name" wire:model="platformName" class="bc-field">@error('platformName')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="bc-label" for="ss-logo">Platform logo</label><input id="ss-logo" wire:model="logo" type="file" accept="image/*" class="bc-field">@error('logo')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror @if($currentLogoPath)<img src="{{ Storage::url($currentLogoPath) }}" alt="Current logo" class="mt-2 h-10">@endif</div>
                <div><label class="bc-label" for="ss-favicon">Favicon</label><input id="ss-favicon" wire:model="favicon" type="file" accept="image/*" class="bc-field">@error('favicon')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror @if($currentFaviconPath)<img src="{{ Storage::url($currentFaviconPath) }}" alt="Current favicon" class="mt-2 h-8">@endif</div>
            </div>
        </div>

        <div class="bc-panel space-y-4 p-5" style="border-radius:8px">
            <h2 class="font-bold text-white">Locale &amp; formatting</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <div><label class="bc-label" for="ss-lang">Default language</label><select id="ss-lang" wire:model="defaultLanguage" class="bc-field">@foreach($languages as $lang)<option value="{{ $lang->code }}">{{ $lang->name }}</option>@endforeach</select>@error('defaultLanguage')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                <div><label class="bc-label" for="ss-currency">Default currency</label><select id="ss-currency" wire:model="defaultCurrency" class="bc-field">@foreach($currencies as $currency)<option value="{{ $currency->code }}">{{ $currency->name }}</option>@endforeach</select>@error('defaultCurrency')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                <div><label class="bc-label" for="ss-timezone">Default timezone</label><input id="ss-timezone" wire:model="defaultTimezone" class="bc-field">@error('defaultTimezone')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="bc-label" for="ss-date-format">Date format</label><input id="ss-date-format" wire:model="dateFormat" class="bc-field">@error('dateFormat')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                <div><label class="bc-label" for="ss-time-format">Time format</label><input id="ss-time-format" wire:model="timeFormat" class="bc-field">@error('timeFormat')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
            </div>
        </div>

        <div class="bc-panel space-y-4 p-5" style="border-radius:8px">
            <h2 class="font-bold text-white">Invoice settings</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="bc-label" for="ss-invoice-prefix">Invoice number prefix</label><input id="ss-invoice-prefix" wire:model="invoicePrefix" class="bc-field">@error('invoicePrefix')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                <div><label class="bc-label" for="ss-invoice-due">Default due days</label><input id="ss-invoice-due" wire:model="invoiceDueDays" type="number" min="0" class="bc-field">@error('invoiceDueDays')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
            </div>
        </div>

        <div class="bc-panel space-y-4 p-5" style="border-radius:8px">
            <h2 class="font-bold text-white">File uploads &amp; storage</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <div><label class="bc-label" for="ss-upload-max">Max upload size (MB)</label><input id="ss-upload-max" wire:model="fileUploadMaxMb" type="number" min="1" class="bc-field">@error('fileUploadMaxMb')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                <div><label class="bc-label" for="ss-file-types">Allowed file types</label><input id="ss-file-types" wire:model="allowedFileTypes" class="bc-field" placeholder="jpg,png,pdf">@error('allowedFileTypes')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                <div><label class="bc-label" for="ss-storage-disk">Storage disk</label><input id="ss-storage-disk" wire:model="storageDisk" class="bc-field">@error('storageDisk')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
            </div>
        </div>

        <div class="bc-panel space-y-4 p-5" style="border-radius:8px">
            <h2 class="font-bold text-white">API &amp; rate limits</h2>
            <div><label class="bc-label" for="ss-api-limit">Default API rate limit (requests/min)</label><input id="ss-api-limit" wire:model="apiRateLimitPerMinute" type="number" min="1" class="bc-field">@error('apiRateLimitPerMinute')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
        </div>

        <div class="bc-panel space-y-4 p-5" style="border-radius:8px">
            <h2 class="font-bold text-white">Security &amp; session</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="bc-label" for="ss-session-lifetime">Session lifetime (minutes)</label><input id="ss-session-lifetime" wire:model="sessionLifetimeMinutes" type="number" min="5" class="bc-field">@error('sessionLifetimeMinutes')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                <div><label class="bc-label" for="ss-password-min">Minimum password length</label><input id="ss-password-min" wire:model="passwordMinLength" type="number" min="6" class="bc-field">@error('passwordMinLength')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
            </div>
        </div>

        <button type="submit" class="bc-primary">Save settings</button>
    </form>
</div>
