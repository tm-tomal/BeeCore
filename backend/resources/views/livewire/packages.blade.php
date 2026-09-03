<div class="space-y-6">
    @if($viewMode === 'index')
        <!-- Page header -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">{{ __('Service catalog') }}</p>
                <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{!! __('Packages & IP plans') !!}</h1>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Define recurring package prices and connection profiles.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button wire:click="create" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    {{ __('Add Package') }}
                </button>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
                <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="flex items-start gap-3 rounded-xl border border-error-200 bg-error-50 px-4 py-3 dark:border-error-500/20 dark:bg-error-500/10">
                <svg class="mt-0.5 size-5 shrink-0 stroke-error-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <p class="text-theme-sm text-error-700 dark:text-error-300">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Packages table -->
        <x-table heading="{{ __('All packages') }}" :description="__('Showing :count packages', ['count' => number_format($packages->total())])" :paginator="$packages">
            <x-slot:toolbar>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 dark:text-gray-500">
                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search packages...') }}" class="h-10 w-56 rounded-lg border border-gray-300 bg-transparent py-2 pl-10 pr-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    </div>
                </div>
            </x-slot:toolbar>

            <table class="min-w-full">
                <thead class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Plan name') }}</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Connection') }}</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Price') }}</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Cost') }}</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Status') }}</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($packages as $package)
                        @php
                            $hasCost = $package->cost !== null;
                        @endphp
                        <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                        <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="truncate text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $package->name }}</div>
                                        <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-theme-xs text-gray-500 dark:text-gray-400">
                                            <span>{{ $package->bandwidth ?: __('Bandwidth not set') }}</span>
                                            @if($package->active_subscribers > 0)
                                                <span class="text-gray-300 dark:text-gray-700">•</span>
                                                <span>{{ __(':count active', ['count' => $package->active_subscribers]) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-md px-2 py-1 text-theme-xs font-medium {{ $package->type === 'dedicated_ip' ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400' : 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400' }}">
                                    {{ $package->type === 'dedicated_ip' ? __('Dedicated IP') : __('Shared / PPPoE') }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">৳{{ number_format($package->price, 2) }}</span>
                                <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">{{ __('per month') }}</span>
                            </td>
                            <td class="px-5 py-4 text-right text-theme-sm {{ $hasCost ? 'font-medium text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-500' }}">
                                {{ $hasCost ? '৳'.number_format($package->cost, 2) : '—' }}
                            </td>
                            <td class="px-5 py-4">
                                <button
                                    type="button"
                                    role="switch"
                                    aria-label="{{ $package->is_active ? __('Active - click to deactivate') : __('Inactive - click to activate') }}"
                                    aria-checked="{{ $package->is_active ? 'true' : 'false' }}"
                                    wire:click="toggleStatus({{ $package->id }})"
                                    title="{{ $package->is_active ? __('Active - click to deactivate') : __('Inactive - click to activate') }}"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full transition-colors duration-200 focus:outline-hidden focus:ring-3 focus:ring-brand-500/20 {{ $package->is_active ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700' }}"
                                >
                                    <span class="inline-block size-4 transform rounded-full bg-white shadow-theme-xs transition-transform duration-200 {{ $package->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" wire:click="edit({{ $package->id }})" title="{{ __('Edit package') }}" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button
                                        type="button"
                                        title="{{ __('Delete package') }}"
                                        @click="$dispatch('confirm-action', {
                                            title: '{{ __('Delete package') }}',
                                            message: '{{ __('Delete :name from your catalog? Packages used by subscribers must be deactivated instead.', ['name' => $package->name]) }}',
                                            confirmText: '{{ __('Delete') }}',
                                            wireMethod: 'delete',
                                            wireParams: [{{ $package->id }}],
                                        })"
                                        class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 hover:text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400 dark:hover:border-error-500/40 dark:hover:bg-error-500/15 dark:hover:text-error-300"
                                    >
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <div class="mx-auto max-w-xs">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">{{ $search ? __('No packages match your search.') : __('No packages created yet.') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table>

    @else
        <!-- Page header -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">{{ __('Service catalog') }}</p>
                <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $isEditing ? __('Edit package') : __('Create package') }}</h1>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $isEditing ? __('Update the connection profile and pricing.') : __('Add a new plan to your service catalog.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button wire:click="cancel" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    {{ __('Back to List') }}
                </button>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            <!-- Plan details -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Plan details') }}</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Name and price are required. Set your cost to see monthly profit margins.') }}</p>
                </div>

                <div>
                    <label for="package-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Package name') }}<span class="ml-0.5 text-error-500">*</span></label>
                    <input id="package-name" type="text" wire:model="name" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="10 Mbps Ultimate">
                    @error('name') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label for="package-price" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Monthly price (BDT)') }}<span class="ml-0.5 text-error-500">*</span></label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-theme-sm font-medium text-gray-400 dark:text-gray-500">৳</span>
                            <input id="package-price" type="number" step="0.01" min="0" wire:model="price" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-9 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="1000.00">
                        </div>
                        @error('price') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="package-cost" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Monthly cost (BDT)') }}</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-theme-sm font-medium text-gray-400 dark:text-gray-500">৳</span>
                            <input id="package-cost" type="number" step="0.01" min="0" wire:model="cost" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-9 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ __('What it costs you') }}">
                        </div>
                        <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Your delivery cost — used to calculate profit.') }}</p>
                        @error('cost') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="package-bandwidth" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Bandwidth') }}</label>
                        <input id="package-bandwidth" type="text" wire:model="bandwidth" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="10 Mbps">
                        @error('bandwidth') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <!-- Connection profile -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Connection profile') }}</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Choose how this plan connects and whether it can be assigned.') }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="package-type" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Connection type') }}<span class="ml-0.5 text-error-500">*</span></label>
                        <x-search-select wireKey="type" :options="['shared' => __('Shared / PPPoE'), 'dedicated_ip' => __('Dedicated IP')]" :value="$type" placeholder="{{ __('Select connection type') }}" :searchable="false" />
                        @error('type') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-start gap-3 rounded-lg border px-3.5 py-3 {{ $is_active ? 'border-success-100 bg-success-50/70 dark:border-success-500/20 dark:bg-success-500/10' : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900' }}">
                        <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-lg {{ $is_active ? 'bg-success-100 text-success-600 dark:bg-success-500/20 dark:text-success-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                            @if($is_active)
                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="8 12 11 15 16 9"/></svg>
                            @else
                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            @endif
                        </span>
                        <div class="min-w-0">
                            <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $is_active ? __('Active package') : __('Inactive package') }}</p>
                            <p class="mt-0.5 text-theme-xs leading-4 text-gray-500 dark:text-gray-400">
                                {{ $is_active
                                    ? __('This plan can be assigned to new customers and appears in billing dropdowns.')
                                    : __('Hidden from new assignments. Existing subscribers keep their service.') }}
                            </p>
                        </div>
                        <button
                            type="button"
                            role="switch"
                            aria-checked="{{ $is_active ? 'true' : 'false' }}"
                            wire:click="$toggle('is_active')"
                            class="relative ml-auto inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full transition-colors duration-200 focus:outline-hidden focus:ring-3 focus:ring-brand-500/20 {{ $is_active ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700' }}"
                        >
                            <span class="inline-block size-4 transform rounded-full bg-white shadow-theme-xs transition-transform duration-200 {{ $is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- Actions -->
            <div class="sticky bottom-4 flex flex-col-reverse gap-3 rounded-2xl border border-gray-200 bg-white/95 px-5 py-4 shadow-theme-lg backdrop-blur sm:flex-row sm:items-center sm:justify-between dark:border-gray-800 dark:bg-gray-900/95">
                <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Fields marked with') }} <span class="text-error-500">*</span> {{ __('are required.') }}</p>
                <div class="flex flex-col-reverse gap-3 sm:flex-row">
                    <button type="button" wire:click="cancel" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">{{ __('Cancel') }}</button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? __('Save changes') : __('Create package') }}</span>
                        <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>
