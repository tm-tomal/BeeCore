<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">{{ __('Workspace') }}</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ __('ISP settings') }}</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Company details and the rules behind your invoices and billing.') }}</p>
        </div>
        <span class="inline-flex w-fit shrink-0 items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-theme-xs font-medium text-gray-500 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
            <span class="size-2 rounded-full {{ $workspace->isAutomatic() ? 'bg-brand-500' : 'bg-warning-500' }}"></span>
            {{ $workspace->operationModeLabel() }} {{ __('operations') }}
        </span>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <form wire:submit="save" class="grid items-start gap-6 lg:grid-cols-[240px_minmax(0,1fr)]">
        <!-- Settings navigation -->
        <aside class="lg:sticky lg:top-6">
            <nav class="grid grid-cols-2 gap-1.5 rounded-2xl border border-gray-200 bg-white p-1.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] lg:grid-cols-1">
                <button type="button" wire:click="$set('tab', 'company')" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-left transition {{ $tab === 'company' ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
                    <span class="grid size-8 shrink-0 place-items-center rounded-lg {{ $tab === 'company' ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">
                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </span>
                    <span>
                        <span class="block text-theme-sm font-semibold">{{ __('Company') }}</span>
                        <span class="hidden text-theme-xs text-gray-400 dark:text-gray-500 lg:block">{{ __('Name, contact, region') }}</span>
                    </span>
                </button>
                <button type="button" wire:click="$set('tab', 'billing')" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-left transition {{ $tab === 'billing' ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
                    <span class="grid size-8 shrink-0 place-items-center rounded-lg {{ $tab === 'billing' ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">
                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </span>
                    <span>
                        <span class="block text-theme-sm font-semibold">{{ __('Billing rules') }}</span>
                        <span class="hidden text-theme-xs text-gray-400 dark:text-gray-500 lg:block">{{ __('Invoices & suspension') }}</span>
                    </span>
                </button>
            </nav>

            <div class="mt-4 hidden rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] lg:block">
                <p class="text-theme-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ __('Workspace owner') }}</p>
                <p class="mt-2 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $workspace->owner_name ?: '—' }}</p>
                <p class="mt-0.5 break-all text-theme-xs text-gray-500 dark:text-gray-400">{{ $workspace->owner_email ?: '—' }}</p>
                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ $workspace->owner_phone ?: '—' }}</p>
            </div>
        </aside>

        <!-- Settings content -->
        <div class="min-w-0 space-y-6">
            @if($tab === 'company')
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="mb-5">
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Company profile') }}</h2>
                        <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Shown to your customers on invoices and the customer app.') }}</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="settings-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Company / business name') }}<span class="ml-0.5 text-error-500">*</span></label>
                            <input id="settings-name" type="text" wire:model="name" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="Acme Networks">
                            @error('name') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="settings-phone" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Contact phone') }}</label>
                            <input id="settings-phone" type="text" wire:model="contactPhone" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="+8801XXXXXXXXX">
                            @error('contactPhone') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="settings-address" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Business address') }}</label>
                            <input id="settings-address" type="text" wire:model="contactAddress" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ __('House, road, area, district') }}">
                            @error('contactAddress') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-4 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <p class="mb-3 text-theme-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ __('Region & language') }}</p>
                        <div class="grid gap-4 sm:grid-cols-3">
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
                    </div>
                </section>
            @else
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="mb-5">
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Billing rules') }}</h2>
                        <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('When invoices become due, how overdue is handled, and what customers see at the bottom.') }}</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label for="settings-grace" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Invoice due after') }}</label>
                            <div class="relative">
                                <input id="settings-grace" type="number" min="0" max="60" wire:model="graceDays" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-theme-xs text-gray-400 dark:text-gray-500">days</span>
                            </div>
                            <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Days after the billing period starts') }}</p>
                            @error('graceDays') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="settings-cutoff" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Billing cutoff day') }}</label>
                            <div class="relative">
                                <input id="settings-cutoff" type="number" min="1" max="28" wire:model="cutoffDay" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-theme-xs text-gray-400 dark:text-gray-500">of month</span>
                            </div>
                            <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('New subscriptions start billing on this day') }}</p>
                            @error('cutoffDay') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="settings-suspend-days" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Auto-suspend after') }}</label>
                            <div class="relative">
                                <input id="settings-suspend-days" type="number" min="1" max="90" wire:model="autoSuspendDays" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" @disabled(!$autoSuspend)>
                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-theme-xs text-gray-400 dark:text-gray-500">days</span>
                            </div>
                            <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Unpaid customers are suspended after this') }}</p>
                            @error('autoSuspendDays') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-between gap-4 rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 grid size-8 shrink-0 place-items-center rounded-lg {{ $autoSuspend ? 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">
                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                            </span>
                            <div>
                                <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ __('Auto-suspend unpaid customers') }}</p>
                                <p class="mt-0.5 max-w-md text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Suspend services automatically when an invoice stays unpaid past its due date.') }}</p>
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

                    <div class="mt-5">
                        <label for="settings-terms" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Invoice terms & footer note') }}</label>
                        <textarea id="settings-terms" wire:model="invoiceTerms" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ __('e.g. Payment is due within the stated period. Late payments may result in service suspension.') }}"></textarea>
                        <div class="mt-1.5 flex items-center justify-between gap-3">
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Shown at the bottom of customer invoices (print/PDF).') }}</p>
                            @error('invoiceTerms') <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>
            @endif

            <!-- Actions -->
            <div class="flex flex-col-reverse gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-4 shadow-theme-xs sm:flex-row sm:items-center sm:justify-between dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Changes apply to new invoices and workspace defaults immediately.') }}</p>
                <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                    <span wire:loading.remove wire:target="save">{{ __('Save settings') }}</span>
                    <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                </button>
            </div>
        </div>
    </form>
</div>
