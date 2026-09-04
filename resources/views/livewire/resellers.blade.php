<div class="space-y-6">
    @if($viewMode === 'index')
        <!-- Page header -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">{{ __('Channel operations') }}</p>
                <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ __('Resellers') }}</h1>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Maintain reseller contacts, balances, and access status.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button wire:click="create" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    {{ __('Add Reseller') }}
                </button>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
                <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
            </div>
        @endif

        <x-plan-error-banner />

        <!-- Reseller summary -->
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Total resellers') }}</p>
                <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($summary['total']) }}</p>
                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('All channel partners') }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Active') }}</p>
                <p class="mt-2 text-2xl font-bold text-success-600 dark:text-success-500">{{ number_format($summary['active']) }}</p>
                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Currently enabled') }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Suspended') }}</p>
                <p class="mt-2 text-2xl font-bold text-warning-600 dark:text-warning-500">{{ number_format($summary['suspended']) }}</p>
                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Access disabled') }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Combined balance') }}</p>
                <p class="mt-2 truncate text-2xl font-bold text-gray-800 dark:text-white/90">৳{{ number_format($summary['balance'], 2) }}</p>
                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Across all accounts') }}</p>
            </div>
        </section>

        <!-- Resellers table -->
        <x-table heading="{{ __('All resellers') }}" :description="__('Showing :count resellers', ['count' => number_format($resellers->total())])" :paginator="$resellers">
            <x-slot:toolbar>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 dark:text-gray-500">
                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search resellers...') }}" class="h-10 w-56 rounded-lg border border-gray-300 bg-transparent py-2 pl-10 pr-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    </div>
                </div>
            </x-slot:toolbar>

            <table class="min-w-full">
                <thead class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Reseller') }}</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Contact') }}</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Balance') }}</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Status') }}</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($resellers as $reseller)
                        <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-brand-50 text-theme-sm font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ strtoupper(substr($reseller->name, 0, 1)) }}</span>
                                    <div class="min-w-0">
                                        <div class="truncate text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $reseller->name }}</div>
                                        <div class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Added') }} {{ $reseller->created_at->format('d M Y') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">
                                <div>{{ $reseller->email ?: __('No email') }}</div>
                                <div class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $reseller->phone ?: __('No phone') }}</div>
                            </td>
                            <td class="px-5 py-4 text-right text-theme-sm font-semibold text-gray-800 dark:text-white/90">৳{{ number_format($reseller->balance, 2) }}</td>
                            <td class="px-5 py-4">
                                <button
                                    type="button"
                                    role="switch"
                                    aria-label="{{ $reseller->status === 'active' ? __('Active - click to suspend') : __('Suspended - click to activate') }}"
                                    aria-checked="{{ $reseller->status === 'active' ? 'true' : 'false' }}"
                                    wire:click="toggleStatus({{ $reseller->id }})"
                                    title="{{ $reseller->status === 'active' ? __('Active - click to suspend') : __('Suspended - click to activate') }}"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full transition-colors duration-200 focus:outline-hidden focus:ring-3 focus:ring-brand-500/20 {{ $reseller->status === 'active' ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700' }}"
                                >
                                    <span class="inline-block size-4 transform rounded-full bg-white shadow-theme-xs transition-transform duration-200 {{ $reseller->status === 'active' ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" wire:click="edit({{ $reseller->id }})" title="{{ __('Edit reseller') }}" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button
                                        type="button"
                                        title="{{ __('Delete reseller') }}"
                                        @click="$dispatch('confirm-action', {
                                            title: '{{ __('Delete reseller') }}',
                                            message: '{{ __('Delete :name? Their account and balance record will be removed.', ['name' => $reseller->name]) }}',
                                            confirmText: '{{ __('Delete') }}',
                                            wireMethod: 'delete',
                                            wireParams: [{{ $reseller->id }}],
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
                            <td colspan="5" class="px-5 py-12 text-center">
                                <div class="mx-auto max-w-xs">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">{{ $search ? __('No resellers match your search.') : __('No resellers created yet.') }}</p>
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
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">{{ __('Channel operations') }}</p>
                <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $isEditing ? __('Edit reseller') : __('Add reseller') }}</h1>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $isEditing ? __('Update contact details and access status.') : __('Register a new channel partner.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button wire:click="cancel" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    {{ __('Back to List') }}
                </button>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            <!-- Contact details -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Contact details') }}</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Name is required. Email and phone are optional but recommended.') }}</p>
                </div>
                <div>
                    <label for="reseller-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Full name') }}<span class="ml-0.5 text-error-500">*</span></label>
                    <input id="reseller-name" type="text" wire:model="name" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ __('e.g. Aziz Traders') }}">
                    @error('name') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="reseller-email" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Email address') }}</label>
                        <input id="reseller-email" type="email" wire:model="email" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="aziz@example.com">
                        @error('email') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="reseller-phone" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Phone number') }}</label>
                        <input id="reseller-phone" type="text" wire:model="phone" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="+8801XXXXXXXXX">
                        @error('phone') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <!-- Access status -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Access status') }}</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Suspended resellers cannot place orders or use the portal.') }}</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="reseller-status" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Status') }}<span class="ml-0.5 text-error-500">*</span></label>
                        <x-search-select wireKey="status" :options="['active' => __('Active'), 'suspended' => __('Suspended')]" :value="$status" placeholder="{{ __('Select status') }}" :searchable="false" />
                        @error('status') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-start gap-3 rounded-lg border px-3.5 py-3 {{ $status === 'active' ? 'border-success-100 bg-success-50/70 dark:border-success-500/20 dark:bg-success-500/10' : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900' }}">
                        <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-lg {{ $status === 'active' ? 'bg-success-100 text-success-600 dark:bg-success-500/20 dark:text-success-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                            @if($status === 'active')
                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="8 12 11 15 16 9"/></svg>
                            @else
                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                            @endif
                        </span>
                        <p class="text-theme-xs leading-4 text-gray-500 dark:text-gray-400">
                            {{ $status === 'active'
                                ? __('The reseller is enabled and can transact normally.')
                                : __('The reseller is suspended and cannot transact until reactivated.') }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Actions -->
            <div class="sticky bottom-4 flex flex-col-reverse gap-3 rounded-2xl border border-gray-200 bg-white/95 px-5 py-4 shadow-theme-lg backdrop-blur sm:flex-row sm:items-center sm:justify-between dark:border-gray-800 dark:bg-gray-900/95">
                <p class="text-theme-xs text-gray-500 dark:text-gray-400">{!! __('Fields marked with :mark are required.', ['mark' => '<span class="text-error-500">*</span>']) !!}</p>
                <div class="flex flex-col-reverse gap-3 sm:flex-row">
                    <button type="button" wire:click="cancel" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">{{ __('Cancel') }}</button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? __('Save changes') : __('Add reseller') }}</span>
                        <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>
