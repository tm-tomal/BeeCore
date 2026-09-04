<div class="space-y-6">
    @if($viewMode === 'index')
        <!-- Page header -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">{{ __('Customers') }}</p>
                <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ __('Customer directory') }}</h1>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Manage subscriber profiles, service status and recurring package assignments.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button wire:click="create" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    {{ __('Add Customer') }}
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

        <!-- Customers table -->
        <x-table heading="{{ __('All customers') }}" :description="__('Showing :count customers', ['count' => number_format($customers->total())])" :paginator="$customers">
            <x-slot:toolbar>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 dark:text-gray-500">
                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search customers...') }}" class="h-10 w-56 rounded-lg border border-gray-300 bg-transparent py-2 pl-10 pr-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    </div>
                    <select wire:model.live="statusFilter" class="h-10 w-40 appearance-none rounded-lg border border-gray-300 bg-transparent px-3.5 py-2 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">{{ __('All statuses') }}</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="pending">{{ __('Pending') }}</option>
                        <option value="inactive">{{ __('Inactive') }}</option>
                        <option value="suspended">{{ __('Suspended') }}</option>
                    </select>
                </div>
            </x-slot:toolbar>

            <table class="min-w-full">
                <thead class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <tr>
                        <th class="w-12 px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">#</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Customer') }}</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Package') }}</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Status') }}</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($customers as $customer)
                        @php
                            $subscription = $customer->activeSubscription;
                            $badge = match ($customer->status) {
                                'active' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
                                'pending' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                                'suspended' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
                                default => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
                            };
                            $statusLabel = ucfirst($customer->status);
                        @endphp
                        <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400">{{ $customers->firstItem() + $loop->index }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-brand-50 text-theme-sm font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                                    <div class="min-w-0">
                                        <div class="truncate text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $customer->name }}</div>
                                        <div class="mt-0.5 flex flex-wrap items-center gap-x-1.5 text-theme-xs text-gray-500 dark:text-gray-400">
                                            <span class="truncate">{{ $customer->email }}</span>
                                            @if($customer->phone)
                                                <span class="text-gray-300 dark:text-gray-700">•</span>
                                                <span class="truncate">{{ $customer->phone }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                @if($subscription)
                                    <div class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $subscription->package_name }}</div>
                                    <div class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">
                                        ৳{{ number_format((float) $subscription->price, 2) }} · {{ __(ucfirst($subscription->billing_cycle)) }}
                                        @if($subscription->next_billing_date)
                                            <span class="text-gray-400 dark:text-gray-500">· {{ __('Next') }} {{ $subscription->next_billing_date->format('d M Y') }}</span>
                                        @endif
                                    </div>
                                    @if($subscription->pppoe_username)
                                        <div class="mt-0.5 truncate text-theme-xs text-gray-400 dark:text-gray-500">{{ __('PPPoE user') }}: {{ $subscription->pppoe_username }}</div>
                                    @endif
                                @elseif($customer->package_name)
                                    <div class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $customer->package_name }}</div>
                                    <div class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('No active subscription') }}</div>
                                @else
                                    <span class="text-theme-sm text-gray-400 dark:text-gray-500">{{ __('No package') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $badge }}">{{ __($statusLabel) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('customers.show', $customer) }}" title="{{ __('View customer') }}" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <button type="button" wire:click="edit({{ $customer->id }})" title="{{ __('Edit customer') }}" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button
                                        type="button"
                                        title="{{ __('Delete customer') }}"
                                        @click="$dispatch('confirm-action', {
                                            title: '{{ __('Delete customer') }}',
                                            message: '{{ __('Permanently delete :name? Any active subscription will be cancelled.', ['name' => $customer->name]) }}',
                                            confirmText: '{{ __('Delete') }}',
                                            wireMethod: 'delete',
                                            wireParams: [{{ $customer->id }}],
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
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">{{ $search || $statusFilter ? __('No customers match your filters.') : __('No customers found yet.') }}</p>
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
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">{{ __('Customers') }}</p>
                <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $isEditing ? __('Edit customer') : __('Add customer') }}</h1>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $isEditing ? __('Update subscriber details and their recurring package.') : __('Register a new subscriber and optionally assign a package.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button wire:click="cancel" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    {{ __('Back to List') }}
                </button>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            <!-- Customer information -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Customer information') }}</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Full name and email are required. These are used to identify the subscriber.') }}</p>
                </div>

                <div>
                    <label for="customer-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Full name') }}<span class="ml-0.5 text-error-500">*</span></label>
                    <input id="customer-name" type="text" wire:model="name" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="Rahim Uddin">
                    @error('name') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="customer-email" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Email address') }}<span class="ml-0.5 text-error-500">*</span></label>
                        <input id="customer-email" type="email" wire:model="email" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="rahim@example.com">
                        @error('email') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="customer-phone" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Mobile number') }}</label>
                        <input id="customer-phone" type="text" wire:model="phone" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="+8801XXXXXXXXX">
                        @error('phone') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <!-- Account status -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Account status') }}</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Only Active keeps the recurring subscription running.') }}</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="customer-status" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Current status') }}<span class="ml-0.5 text-error-500">*</span></label>
                        <x-search-select id="customer-status" wireKey="status" :options="['active' => __('Active'), 'pending' => __('Pending'), 'inactive' => __('Inactive'), 'suspended' => __('Suspended')]" :value="$status" placeholder="{{ __('Select status') }}" :searchable="false" />
                        @error('status') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-start gap-3 rounded-lg border px-3.5 py-3 {{ $status === 'active' ? 'border-success-100 bg-success-50/70 dark:border-success-500/20 dark:bg-success-500/10' : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900' }}">
                        <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-lg {{ $status === 'active' ? 'bg-success-100 text-success-600 dark:bg-success-500/20 dark:text-success-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                            @if($status === 'active')
                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="8 12 11 15 16 9"/></svg>
                            @else
                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            @endif
                        </span>
                        <p class="text-theme-xs leading-4 text-gray-500 dark:text-gray-400">
                            {{ $status === 'active'
                                ? __('The assigned subscription will be billed on its schedule and kept active.')
                                : __('The assigned subscription will be paused until the customer is set back to Active.') }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Package & billing -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Package & billing') }}</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Choose an active package to enable a recurring billing schedule.') }}</p>
                </div>

                <div>
                    <label for="customer-package" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Assign package') }}</label>
                    <x-search-select wireKey="package_id" :options="$packageOptions" :value="$package_id" placeholder="{{ __('Select a package') }}" :live="true" />
                    @error('package_id') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>

                @if($package_id)
                    <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50/60 p-4 sm:p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="mb-4 flex items-center gap-2.5">
                            <span class="grid h-7 w-7 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </span>
                            <h3 class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Billing schedule') }}</h3>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="customer-billing-cycle" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Billing cycle') }}<span class="ml-0.5 text-error-500">*</span></label>
                                <x-search-select wireKey="billing_cycle" :options="['monthly' => __('Monthly'), 'quarterly' => __('Quarterly'), 'semiannual' => __('Half-yearly'), 'yearly' => __('Yearly')]" :value="$billing_cycle" placeholder="{{ __('Select cycle') }}" :searchable="false" />
                                @error('billing_cycle') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="customer-next-billing" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Next billing date') }}<span class="ml-0.5 text-error-500">*</span></label>
                                <input id="customer-next-billing" type="date" wire:model="next_billing_date" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                @error('next_billing_date') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    @if($isAutomatic)
                        <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50/60 p-4 sm:p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                            <div class="mb-4 flex items-center gap-2.5">
                                <span class="grid h-7 w-7 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </span>
                                <h3 class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ __('PPPoE / RADIUS login') }}</h3>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="customer-pppoe-user" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Login username') }}</label>
                                    <input id="customer-pppoe-user" type="text" wire:model="pppoe_username" autocomplete="off" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ __('e.g. customer_username') }}">
                                    @error('pppoe_username') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="customer-pppoe-pass" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Password') }}</label>
                                    <input id="customer-pppoe-pass" type="text" wire:model="pppoe_password" autocomplete="off" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ __('Leave blank to keep current') }}">
                                    @error('pppoe_password') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <p class="mt-3 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('PPPoE username and password are stored per subscription and used to authenticate this customer on your network (MikroTik / RADIUS).') }}</p>
                        </div>
                    @else
                        <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50/60 p-4 sm:p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                            <div class="mb-4 flex items-center gap-2.5">
                                <span class="grid h-7 w-7 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </span>
                                <h3 class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ __('PPPoE username') }}</h3>
                            </div>
                            <div>
                                <label for="customer-pppoe-user" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Login username') }}</label>
                                <input id="customer-pppoe-user" type="text" wire:model="pppoe_username" autocomplete="off" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ __('e.g. customer_username') }}">
                                @error('pppoe_username') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                            </div>
                            <p class="mt-3 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Keep the PPPoE username here so you can track and verify this subscriber on your side.') }}</p>
                        </div>
                    @endif
                @else
                    <p class="mt-3 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Saving without a package will cancel any existing active subscription.') }}</p>
                @endif
            </section>

            <!-- Address & map -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4 flex items-center gap-2.5">
                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Address & location') }}</h2>
                        <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Where is this subscriber connected? Fill the address and drop a pin on the map — it shows on their profile page.') }}</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label for="customer-house" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('House / Flat') }}</label>
                        <input id="customer-house" type="text" wire:model="address_house" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ __('e.g. House 12') }}">
                    </div>
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label for="customer-street" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Street / Road') }}</label>
                        <input id="customer-street" type="text" wire:model="address_street" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ __('e.g. Road 8, Banani') }}">
                    </div>
                    <div>
                        <label for="customer-area" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Area / Colony') }}</label>
                        <input id="customer-area" type="text" wire:model="address_area" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    </div>
                    <div>
                        <label for="customer-city" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('City / District') }}</label>
                        <input id="customer-city" type="text" wire:model="address_city" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ __('e.g. Dhaka') }}">
                    </div>
                    <div>
                        <label for="customer-postcode" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Postcode') }}</label>
                        <input id="customer-postcode" type="text" wire:model="address_postcode" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    </div>
                </div>

                <div class="mt-4">
                    <input type="hidden" id="customer-latitude" wire:model="address_latitude">
                    <input type="hidden" id="customer-longitude" wire:model="address_longitude">
                    <div wire:ignore>
                        <!-- Address search & autofill -->
                        <div class="mb-3 grid gap-2 sm:grid-cols-[minmax(0,1fr)_auto]">
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 dark:text-gray-500">
                                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                </span>
                                <input
                                    type="search"
                                    data-map-search
                                    autocomplete="off"
                                    placeholder="{{ __('Search a Bangladesh address — house, road, area or district...') }}"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-10 pr-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                >
                                <div data-map-results class="bee-map-results absolute left-0 right-0 z-30 mt-1.5 hidden max-h-72 overflow-y-auto rounded-xl border border-gray-200 bg-white p-1.5 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900"></div>
                            </div>
                            <button type="button" data-map-locate class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-lg border border-gray-200 px-3.5 text-theme-sm font-medium text-gray-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-700 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
                                {{ __('My location') }}
                            </button>
                        </div>

                        <div
                            id="customer-address-map"
                            data-address-map
                            data-editable="1"
                            data-default-zoom="13"
                            data-lat-input="#customer-latitude"
                            data-lng-input="#customer-longitude"
                            data-house-input="#customer-house"
                            data-street-input="#customer-street"
                            data-area-input="#customer-area"
                            data-city-input="#customer-city"
                            data-postcode-input="#customer-postcode"
                            class="relative z-0 h-72 w-full overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800"
                            aria-label="{{ __('Customer location map') }}"
                        ></div>
                    </div>
                    <p class="mt-2 flex items-center gap-1.5 text-theme-xs text-gray-500 dark:text-gray-400">
                        <svg class="size-3.5 shrink-0 stroke-current text-brand-500 dark:text-brand-400" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                        {{ __('Search an address, tap your location, or click/drag the pin. District auto-detects — the saved point is reused later for reports and the cable map.') }}
                    </p>
                </div>
            </section>

            <!-- Actions -->
            <div class="sticky bottom-4 z-40 flex flex-col-reverse gap-3 rounded-2xl border border-gray-200 bg-white/95 px-5 py-4 shadow-theme-lg backdrop-blur sm:flex-row sm:items-center sm:justify-between dark:border-gray-800 dark:bg-gray-900/95">
                <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Fields marked with') }} <span class="text-error-500">*</span> {{ __('are required.') }}</p>
                <div class="flex flex-col-reverse gap-3 sm:flex-row">
                    <button type="button" wire:click="cancel" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">{{ __('Cancel') }}</button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? __('Save changes') : __('Create customer') }}</span>
                        <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>
