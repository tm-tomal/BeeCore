<div class="space-y-6">
    <!-- Page header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">{{ __('Workspace') }}</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ __('Customer payment gateway') }}</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Choose how your customers pay you — through the Bee gateway or directly to your own accounts.') }}</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <!-- Collection model -->
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="mb-4">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Collection model') }}</h2>
                <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Pick how payments from your customers are collected.') }}</p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <button
                    type="button"
                    wire:click="$set('collectionMode', 'bee')"
                    class="rounded-2xl border p-5 text-left transition"
                    :class="''"
                    @class([
                        'border-brand-500 bg-brand-50/60 ring-2 ring-brand-500/20 dark:border-brand-500 dark:bg-brand-500/10' => $collectionMode === 'bee',
                        'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700' => $collectionMode !== 'bee',
                    ])
                >
                    <div class="flex items-start justify-between gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $collectionMode === 'bee' ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">
                            <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a5 5 0 0 1 4.9 6.2A6 6 0 0 1 18 14a6 6 0 0 1-12 0 6 6 0 0 1 1.1-7.8A5 5 0 0 1 12 2z"/></svg>
                        </span>
                        <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full border-2 {{ $collectionMode === 'bee' ? 'border-brand-500 bg-brand-500' : 'border-gray-300 dark:border-gray-700' }}">
                            <span class="size-2.5 rounded-full {{ $collectionMode === 'bee' ? 'bg-white' : '' }}"></span>
                        </span>
                    </div>
                    <h3 class="mt-4 flex flex-wrap items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                        {{ __('Bee Payment Gateway') }}
                        <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2.5 py-0.5 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-400">
                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            {{ __('Recommended') }}
                        </span>
                    </h3>
                    <p class="mt-1.5 text-theme-xs leading-5 text-gray-500 dark:text-gray-400">{{ __('Your customers pay through BeeCore. BeeCore processes the payment and deducts a small per-payment fee — you never handle merchant accounts.') }}</p>
                    <span class="mt-3 inline-flex rounded-lg px-2.5 py-1 text-theme-xs font-medium text-brand-600 dark:text-brand-400">{{ __('Platform fee :fee%', ['fee' => $beeFeePercent]) }}</span>
                </button>

                <button
                    type="button"
                    wire:click="$set('collectionMode', 'own')"
                    class="rounded-2xl border p-5 text-left transition"
                    @class([
                        'border-brand-500 bg-brand-50/60 ring-2 ring-brand-500/20 dark:border-brand-500 dark:bg-brand-500/10' => $collectionMode === 'own',
                        'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700' => $collectionMode !== 'own',
                    ])
                >
                    <div class="flex items-start justify-between gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $collectionMode === 'own' ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">
                            <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                        </span>
                        <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full border-2 {{ $collectionMode === 'own' ? 'border-brand-500 bg-brand-500' : 'border-gray-300 dark:border-gray-700' }}">
                            <span class="size-2.5 rounded-full {{ $collectionMode === 'own' ? 'bg-white' : '' }}"></span>
                        </span>
                    </div>
                    <h3 class="mt-4 text-base font-semibold text-gray-900 dark:text-white">{{ __('Own gateway') }}</h3>
                    <p class="mt-1.5 text-theme-xs leading-5 text-gray-500 dark:text-gray-400">{{ __('Customers pay straight to your own bKash / Nagad / bank account. You record the payment in BeeCore. No BeeCore fee — you handle the money.') }}</p>
                    <span class="mt-3 inline-flex rounded-lg px-2.5 py-1 text-theme-xs font-medium text-gray-600 dark:text-gray-300">{{ __('Direct to your accounts') }}</span>
                </button>
            </div>

            <div x-cloak x-show="false" class="hidden"></div>

            @if($collectionMode === 'bee')
                <div class="mt-5 max-w-2xl rounded-xl border border-success-200 bg-success-50/60 px-4 py-3.5 dark:border-success-500/20 dark:bg-success-500/10">
                    <div class="flex items-start gap-2.5">
                        <svg class="mt-0.5 size-5 shrink-0 stroke-success-600 dark:stroke-success-400" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <div>
                            <p class="text-theme-sm font-medium text-success-700 dark:text-success-300">{{ __('Just save to enable — nothing to configure.') }}</p>
                            <p class="mt-0.5 text-theme-xs leading-5 text-success-700/80 dark:text-success-300/80">{{ __('BeeCore runs the gateway and deducts a :fee% processing fee from each customer payment. The fee is managed on the platform side.', ['fee' => $beeFeePercent]) }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </section>

        <!-- Own gateway details -->
        @if($collectionMode === 'own')
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Your payment accounts') }}</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Enable the methods your customers can use and share the details shown on invoices.') }}</p>
                </div>

                <div class="space-y-4">
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-pink-50 text-pink-600 dark:bg-pink-500/15 dark:text-pink-400">
                                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                </span>
                                <div>
                                    <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">bKash</p>
                                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Mobile wallet (Personal / Agent / Merchant)') }}</p>
                                </div>
                            </div>
                            <button type="button" role="switch" aria-checked="{{ $bkashEnabled ? 'true' : 'false' }}" wire:click="$toggle('bkashEnabled')" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full transition-colors duration-200 focus:outline-hidden focus:ring-3 focus:ring-brand-500/20 {{ $bkashEnabled ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700' }}">
                                <span class="inline-block size-4 transform rounded-full bg-white shadow-theme-xs transition-transform duration-200 {{ $bkashEnabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </div>
                        @if($bkashEnabled)
                            <div class="mt-3">
                                <label for="gateway-bkash" class="mb-1.5 block text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('bKash number / wallet') }}</label>
                                <input id="gateway-bkash" type="text" wire:model="bkashNumber" class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="01XXXXXXXXX">
                                @error('bkashNumber') <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>

                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-orange-50 text-orange-600 dark:bg-orange-500/15 dark:text-orange-400">
                                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                </span>
                                <div>
                                    <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">Nagad</p>
                                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Mobile wallet') }}</p>
                                </div>
                            </div>
                            <button type="button" role="switch" aria-checked="{{ $nagadEnabled ? 'true' : 'false' }}" wire:click="$toggle('nagadEnabled')" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full transition-colors duration-200 focus:outline-hidden focus:ring-3 focus:ring-brand-500/20 {{ $nagadEnabled ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700' }}">
                                <span class="inline-block size-4 transform rounded-full bg-white shadow-theme-xs transition-transform duration-200 {{ $nagadEnabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </div>
                        @if($nagadEnabled)
                            <div class="mt-3">
                                <label for="gateway-nagad" class="mb-1.5 block text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Nagad number / wallet') }}</label>
                                <input id="gateway-nagad" type="text" wire:model="nagadNumber" class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="01XXXXXXXXX">
                                @error('nagadNumber') <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>

                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                                </span>
                                <div>
                                    <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ __('Bank / Card') }}</p>
                                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Account transfer or card payment details') }}</p>
                                </div>
                            </div>
                            <button type="button" role="switch" aria-checked="{{ $bankEnabled ? 'true' : 'false' }}" wire:click="$toggle('bankEnabled')" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full transition-colors duration-200 focus:outline-hidden focus:ring-3 focus:ring-brand-500/20 {{ $bankEnabled ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700' }}">
                                <span class="inline-block size-4 transform rounded-full bg-white shadow-theme-xs transition-transform duration-200 {{ $bankEnabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </div>
                        @if($bankEnabled)
                            <div class="mt-3">
                                <label for="gateway-bank" class="mb-1.5 block text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Account / instruction details') }}</label>
                                <textarea id="gateway-bank" wire:model="bankDetails" rows="2" class="w-full rounded-lg border border-gray-300 bg-transparent px-3.5 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="e.g. bKash merchant 01XXXXXXXXX · Bank: X, A/C ..."></textarea>
                                @error('bankDetails') <p class="mt-1 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>
                    </div>
                </section>
            @endif

        <!-- Actions -->
        <div class="sticky bottom-4 flex flex-col-reverse gap-3 rounded-2xl border border-gray-200 bg-white/95 px-5 py-4 shadow-theme-lg backdrop-blur sm:flex-row sm:items-center sm:justify-between dark:border-gray-800 dark:bg-gray-900/95">
            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Customer invoices will show the active collection method.') }}</p>
            <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                <span wire:loading.remove wire:target="save">{{ __('Save gateway settings') }}</span>
                <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
            </button>
        </div>
    </form>
</div>
