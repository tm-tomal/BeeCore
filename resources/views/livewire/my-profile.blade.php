<div class="space-y-6">
    @php use Illuminate\Support\Str; @endphp

    <!-- Page header -->
    <header>
        <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">{{ __('Account') }}</p>
        <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ __('My profile') }}</h1>
        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Profile information, security, notification preferences, language, and timezone.') }}</p>
    </header>

    @if (session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-theme-sm font-medium text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-400">
            {{ session('message') }}
        </div>
    @endif

    @if($issuedTwoFactorSecret)
        <div class="flex items-start gap-3 rounded-xl border border-warning-200 bg-warning-50 px-4 py-3 dark:border-warning-500/20 dark:bg-warning-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-warning-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <div>
                <p class="text-theme-sm font-semibold text-warning-700 dark:text-warning-300">{{ __('Save this two-factor secret now — it will not be shown again.') }}</p>
                <code class="mt-2 block break-all text-theme-sm font-semibold text-warning-600 dark:text-warning-400">{{ $issuedTwoFactorSecret }}</code>
            </div>
        </div>
    @endif

    <!-- Identity + workspace card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <span class="grid h-14 w-14 shrink-0 place-items-center rounded-full bg-brand-500/15 text-xl font-bold text-brand-600 dark:bg-brand-500/20 dark:text-brand-400">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ auth()->user()->name }}</h2>
                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-theme-xs font-medium capitalize text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">{{ $roleLabel }}</span>
                    </div>
                    <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
                </div>
            </div>

            @if($workspace)
                <div class="flex flex-col items-start gap-3 rounded-xl border border-gray-200 bg-gray-50/60 px-4 py-3 sm:min-w-64 sm:items-stretch dark:border-gray-800 dark:bg-white/[0.02]">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="grid h-7 w-7 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                            </span>
                            <span class="truncate text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $workspace->name }}</span>
                        </div>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-theme-xs font-medium {{ $workspace->isAutomatic() ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400' : 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500' }}">{{ $workspace->operationModeLabel() }}</span>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <a href="{{ route('isp-settings') }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-theme-xs font-medium text-gray-600 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:text-brand-400">{{ __('Settings') }}</a>
                        <a href="{{ route('isp-gateway') }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-theme-xs font-medium text-gray-600 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:text-brand-400">{{ __('Gateway') }}</a>
                        <a href="{{ route('isp-subscription') }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-theme-xs font-medium text-gray-600 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:text-brand-400">{{ __('Subscription') }}</a>
                    </div>
                </div>
            @else
                <span class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-theme-xs font-medium text-gray-500 dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-400">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    {{ __('SaaS platform account') }}
                </span>
            @endif
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2 md:gap-6">
        <!-- Profile information -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </span>
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Profile information') }}</h2>
            </div>
            <form wire:submit="saveProfile" class="mt-5 space-y-5">
                <div>
                    <label for="mp-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Name') }}</label>
                    <input id="mp-name" wire:model="name" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    @error('name') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="mp-email" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Email') }}</label>
                    <input id="mp-email" wire:model="email" type="email" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    @error('email') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="mp-language" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Language') }}</label>
                        <select id="mp-language" wire:model="language" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            @foreach($languages as $lang)<option value="{{ $lang->code }}">{{ $lang->name }}</option>@endforeach
                        </select>
                        @error('language') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="mp-timezone" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Timezone') }}</label>
                        <input id="mp-timezone" wire:model="timezone" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('timezone') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                </div>
                <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">{{ __('Save profile') }}</button>
            </form>
        </div>

        <!-- Change password -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-error-500/10 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                    <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </span>
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Change password') }}</h2>
            </div>
            <form wire:submit="changePassword" class="mt-5 space-y-5">
                <div>
                    <label for="mp-current-password" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Current password') }}</label>
                    <input id="mp-current-password" wire:model="currentPassword" type="password" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    @error('currentPassword') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="mp-new-password" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('New password') }}</label>
                    <input id="mp-new-password" wire:model="newPassword" type="password" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    @error('newPassword') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="mp-new-password-confirm" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Confirm new password') }}</label>
                    <input id="mp-new-password-confirm" wire:model="newPasswordConfirmation" type="password" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    @error('newPasswordConfirmation') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">{{ __('Change password') }}</button>
            </form>
        </div>

        <!-- Two-factor authentication -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-success-500/10 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                    <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </span>
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Two-factor authentication') }}</h2>
            </div>
            <div class="mt-5 space-y-4">
                @if(auth()->user()->two_factor_enabled)
                    <p class="text-theme-sm text-success-600 dark:text-success-400">{{ __('Two-factor authentication is enabled.') }}</p>
                    <button wire:click="disableTwoFactor" wire:confirm="{{ __('Disable two-factor authentication?') }}" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">{{ __('Disable 2FA') }}</button>
                @else
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Two-factor authentication is currently disabled.') }}</p>
                    <button wire:click="enableTwoFactor" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">{{ __('Enable 2FA') }}</button>
                @endif
            </div>
        </div>

        <!-- Notification preferences -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-violet-500/10 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400">
                    <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </span>
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Notification preferences') }}</h2>
            </div>
            <form wire:submit="saveNotificationPreferences" class="mt-5 space-y-4">
                <label class="flex items-center gap-3 text-theme-sm text-gray-700 dark:text-gray-400">
                    <input wire:model="notifyEmail" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">{{ __('Email notifications') }}
                </label>
                <label class="flex items-center gap-3 text-theme-sm text-gray-700 dark:text-gray-400">
                    <input wire:model="notifySms" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">{{ __('SMS notifications') }}
                </label>
                <label class="flex items-center gap-3 text-theme-sm text-gray-700 dark:text-gray-400">
                    <input wire:model="notifyPush" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">{{ __('Push notifications') }}
                </label>
                <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">{{ __('Save preferences') }}</button>
            </form>
        </div>

        <!-- Active sessions -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 lg:col-span-2">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                        <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    </span>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Active sessions') }}</h2>
                </div>
                <button wire:click="terminateOtherSessions" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-error-50 hover:text-error-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-error-500/10 dark:hover:text-error-400">{{ __('Terminate other sessions') }}</button>
            </div>
            <ul class="mt-4">
                @foreach($sessions as $s)
                    <li class="flex items-center justify-between gap-4 border-b border-gray-100 py-3 last:border-0 dark:border-gray-800">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-gray-100 text-gray-500 dark:bg-white/[0.05] dark:text-gray-400">
                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                            </span>
                            <div class="min-w-0">
                                <code class="block text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $s->ip_address }}</code>
                                <span class="mt-0.5 block truncate text-theme-xs text-gray-400 dark:text-gray-500">{{ Str::limit($s->user_agent, 60) }}</span>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            @if($s->id === $currentSessionId)<span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-theme-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ __('This device') }}</span>@endif
                            <span class="text-theme-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::createFromTimestamp($s->last_activity)->format('d M Y, H:i') }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Login history -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 lg:col-span-2">
            <div class="flex items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-cyan-500/10 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-400">
                    <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                </span>
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Login history') }}</h2>
            </div>
            <ul class="mt-4">
                @forelse($loginHistory as $attempt)
                    <li class="flex items-center justify-between gap-4 rounded-xl border border-gray-100 px-4 py-3 last:border-0 dark:border-gray-800">
                        <div class="flex min-w-0 items-center gap-3">
                            @if($attempt->successful)
                                <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </span>
                                <span class="rounded-full bg-success-50 px-2.5 py-0.5 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-400">{{ __('Success') }}</span>
                            @else
                                <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                </span>
                                <span class="rounded-full bg-error-50 px-2.5 py-0.5 text-theme-xs font-semibold text-error-600 dark:bg-error-500/15 dark:text-error-400">{{ __('Failed') }}</span>
                            @endif
                        </div>
                        <div class="flex min-w-0 shrink items-center gap-3 text-theme-xs text-gray-500 dark:text-gray-400">
                            <code class="shrink-0 rounded bg-gray-100 px-1.5 py-0.5 font-mono dark:bg-white/[0.05]">{{ $attempt->ip_address }}</code>
                            <span class="shrink-0">{{ $attempt->created_at->format('d M Y, H:i') }}</span>
                        </div>
                    </li>
                @empty
                    <li class="py-10 text-center text-theme-sm text-gray-500 dark:text-gray-400">{{ __('No login history yet.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
