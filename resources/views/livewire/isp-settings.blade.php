<div class="space-y-6">
    <!-- Page header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">{{ __('Workspace') }}</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ __('ISP settings') }}</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Company profile, invoice & billing rules for this workspace.') }}</p>
        </div>
        <div class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-theme-xs font-medium text-gray-500 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
            <span class="h-2 w-2 rounded-full {{ $workspace->isAutomatic() ? 'bg-brand-500' : 'bg-warning-500' }}"></span>
            {{ $workspace->operationModeLabel() }} {{ __('operations') }}
        </div>
    </div>

    @if (session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <!-- Company profile -->
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="mb-4">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Company profile') }}</h2>
                <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Shown to your customers on invoices and the customer app.') }}</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="settings-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Company / business name') }}<span class="ml-0.5 text-error-500">*</span></label>
                    <input id="settings-name" type="text" wire:model="name" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="Acme Networks">
                    @error('name') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="settings-phone" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Contact phone') }}</label>
                    <input id="settings-phone" type="text" wire:model="contactPhone" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="+8801XXXXXXXXX">
                    @error('contactPhone') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="settings-address" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Business address') }}</label>
                    <textarea id="settings-address" wire:model="contactAddress" rows="2" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ __('House, road, area, district') }}"></textarea>
                    @error('contactAddress') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="settings-currency" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Currency') }}<span class="ml-0.5 text-error-500">*</span></label>
                    <input id="settings-currency" type="text" wire:model="currency" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="BDT">
                    @error('currency') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="settings-timezone" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Timezone') }}<span class="ml-0.5 text-error-500">*</span></label>
                    <input id="settings-timezone" type="text" wire:model="timezone" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="Asia/Dhaka">
                    @error('timezone') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="settings-language" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Default language') }}<span class="ml-0.5 text-error-500">*</span></label>
                    <input id="settings-language" type="text" wire:model="language" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="en">
                    @error('language') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <!-- Owner account (read only) -->
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="mb-4 flex items-start gap-3">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </span>
                <div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Workspace owner') }}</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('The account that owns this workspace. Managed from My Profile.') }}</p>
                </div>
            </div>
            <div class="grid gap-4 rounded-xl border border-gray-200 bg-gray-50/60 p-4 sm:grid-cols-3 dark:border-gray-800 dark:bg-white/[0.02]">
                <div>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Name') }}</p>
                    <p class="mt-1 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $workspace->owner_name ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Email') }}</p>
                    <p class="mt-1 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $workspace->owner_email ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Phone') }}</p>
                    <p class="mt-1 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $workspace->owner_phone ?: '—' }}</p>
                </div>
            </div>
        </section>

        <!-- Invoice & billing rules -->
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="mb-4">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Invoice & billing rules') }}</h2>
                <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('How recurring invoices are dated, when they go overdue, and when unpaid customers get suspended.') }}</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label for="settings-grace" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Invoice due after (days)') }}</label>
                    <input id="settings-grace" type="number" min="0" max="60" wire:model="graceDays" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Recurring invoices are due this many days after the billing period starts.') }}</p>
                    @error('graceDays') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="settings-cutoff" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Billing cutoff day') }}</label>
                    <input id="settings-cutoff" type="number" min="1" max="28" wire:model="cutoffDay" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Day of the month new subscriptions start billing from (1–28).') }}</p>
                    @error('cutoffDay') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="settings-suspend-days" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Suspend after (days past due)') }}</label>
                    <input id="settings-suspend-days" type="number" min="1" max="90" wire:model="autoSuspendDays" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" @disabled(!$autoSuspend)>
                    <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Unpaid customers are suspended automatically after this many days.') }}</p>
                    @error('autoSuspendDays') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-4 flex items-start justify-between gap-4 rounded-xl border border-gray-200 p-4 sm:items-center dark:border-gray-800">
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-lg {{ $autoSuspend ? 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">
                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    </span>
                    <div>
                        <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ __('Auto-suspend unpaid customers') }}</p>
                        <p class="mt-0.5 max-w-md text-theme-xs text-gray-500 dark:text-gray-400">{{ __('When a customer stays unpaid past the due date for the configured period, suspend their service automatically. Works alongside manual suspension.') }}</p>
                    </div>
                </div>
                <button
                    type="button"
                    role="switch"
                    aria-checked="{{ $autoSuspend ? 'true' : 'false' }}"
                    wire:click="$toggle('autoSuspend')"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full transition-colors duration-200 focus:outline-hidden focus:ring-3 focus:ring-brand-500/20 {{ $autoSuspend ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700' }}"
                >
                    <span class="inline-block size-4 transform rounded-full bg-white shadow-theme-xs transition-transform duration-200 {{ $autoSuspend ? 'translate-x-6' : 'translate-x-1' }}"></span>
                </button>
            </div>

            <div class="mt-4">
                <label for="settings-terms" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Invoice terms & footer note') }}</label>
                <textarea id="settings-terms" wire:model="invoiceTerms" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ __('e.g. Payment is due within the stated period. Late payments may result in service suspension.') }}"></textarea>
                <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Shown at the bottom of customer invoices (print/PDF).') }}</p>
                @error('invoiceTerms') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
            </div>
        </section>

        <!-- Actions -->
        <div class="sticky bottom-4 flex flex-col-reverse gap-3 rounded-2xl border border-gray-200 bg-white/95 px-5 py-4 shadow-theme-lg backdrop-blur sm:flex-row sm:items-center sm:justify-between dark:border-gray-800 dark:bg-gray-900/95">
            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Changes apply to new invoices and workspace defaults immediately.') }}</p>
            <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                <span wire:loading.remove wire:target="save">{{ __('Save settings') }}</span>
                <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
            </button>
        </div>
    </form>
</div>
