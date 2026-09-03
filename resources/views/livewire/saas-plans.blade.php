<div class="space-y-6">
    @if($viewMode === 'index')
        <!-- Page header -->
        <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">Commercial control</p>
                <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">SaaS plans</h1>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Define BeeCore pricing, trials, grace periods, and tenant limits.</p>
            </div>
            <div class="flex shrink-0 items-center gap-3">
                <button wire:click="create" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Create plan
                </button>
            </div>
        </header>

        @if(session()->has('message'))
            <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
                <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
            </div>
        @endif

        <!-- Plans table -->
        <x-table heading="All plans" :description="'Showing '.number_format($plans->count()).' plan'.($plans->count() === 1 ? '' : 's')">
            <x-slot:toolbar>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 dark:text-gray-500">
                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search plans..." class="h-10 w-56 rounded-lg border border-gray-300 bg-transparent py-2 pl-10 pr-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    </div>
                    <select wire:model.live="modeFilter" class="h-10 w-44 appearance-none rounded-lg border border-gray-300 bg-transparent px-3.5 py-2 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">All audiences</option>
                        <option value="automatic">Automatic ISPs</option>
                        <option value="manual">Manual ISPs</option>
                        <option value="both">Both types</option>
                    </select>
                </div>
            </x-slot:toolbar>

            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Plan</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Monthly</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Yearly</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Limits</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">For</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Trial / Grace</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Availability</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($plans as $plan)
                        <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4">
                                <div class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $plan->name }}</div>
                                <div class="mt-1 flex flex-wrap items-center gap-2 text-theme-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ $plan->slug }}</span>
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-500 dark:bg-white/[0.05] dark:text-gray-400">{{ $plan->subscriptions_count }} sub</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">৳{{ number_format($plan->monthly_price, 2) }}</td>
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">৳{{ number_format($plan->yearly_price, 2) }}</td>
                            <td class="px-5 py-4 text-theme-xs text-gray-600 dark:text-gray-400">
                                <div>{{ $plan->customer_limit ?? 'Unlimited' }} customers</div>
                                @if($plan->customer_limit !== null && (float) $plan->overflow_rate > 0)
                                    <div class="mt-0.5 font-medium text-brand-600 dark:text-brand-400">+ ৳{{ number_format($plan->overflow_rate, 2) }}/user above limit</div>
                                @endif
                                <div class="mt-0.5 text-gray-500 dark:text-gray-400">{{ $plan->staff_limit ?? 'Unlimited' }} staff · {{ $plan->reseller_limit ?? 'Unlimited' }} resellers</div>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $mode = $plan->operation_mode ?? 'both';
                                    $modeChip = match ($mode) {
                                        'automatic' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
                                        'manual' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                                        default => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
                                    };
                                    $modeLabel = match ($mode) {
                                        'automatic' => 'Automatic',
                                        'manual' => 'Manual',
                                        default => 'Both',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $modeChip }}">{{ $modeLabel }}</span>
                            </td>
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $plan->trial_days }}d / {{ $plan->grace_days }}d</td>
                            <td class="px-5 py-4">
                                <button
                                    type="button"
                                    role="switch"
                                    aria-label="{{ $plan->is_active ? 'Available - click to disable' : 'Disabled - click to enable' }}"
                                    aria-checked="{{ $plan->is_active ? 'true' : 'false' }}"
                                    wire:click="toggleActive({{ $plan->id }})"
                                    title="{{ $plan->is_active ? 'Available for assignment' : 'Not available for assignment' }}"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full transition-colors duration-200 focus:outline-hidden focus:ring-3 focus:ring-brand-500/20 {{ $plan->is_active ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700' }}"
                                >
                                    <span class="inline-block size-4 transform rounded-full bg-white shadow-theme-xs transition-transform duration-200 {{ $plan->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" wire:click="edit({{ $plan->id }})" title="Edit plan" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button
                                        type="button"
                                        title="Archive plan"
                                        @click="$dispatch('confirm-action', {
                                            title: 'Archive SaaS plan',
                                            message: 'Archive this plan? It will no longer be available for assignment.',
                                            confirmText: 'Archive',
                                            wireMethod: 'archive',
                                            wireParams: [{{ $plan->id }}],
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
                            <td colspan="8" class="px-5 py-12 text-center">
                                <div class="mx-auto max-w-xs">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">{{ $search ? 'No plans match your search.' : 'No SaaS plans configured yet.' }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table>
    @else
        <!-- Page header -->
        <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS plans</p>
                <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $isEditing ? 'Edit SaaS plan' : 'Create SaaS plan' }}</h1>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $isEditing ? 'Update pricing, limits and availability for this plan.' : 'Define a new plan with pricing, limits and trial settings.' }}</p>
            </div>
            <div class="flex shrink-0 items-center gap-3">
                <button wire:click="cancel" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to Plans
                </button>
            </div>
        </header>

        <form wire:submit="save" class="space-y-6">
            <!-- Pricing & identity -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Plan & pricing</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Name, slug and subscription prices.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="plan-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Name</label>
                        <input id="plan-name" wire:model.live="name" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="Professional">
                        @error('name') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="plan-slug" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Slug</label>
                        <input id="plan-slug" wire:model="slug" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="professional">
                        <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">Auto-generated from the name.</p>
                        @error('slug') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="plan-monthly-price" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Monthly price (৳)</label>
                        <input id="plan-monthly-price" wire:model.live="monthlyPrice" type="number" step="0.01" min="0" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="1999">
                        @error('monthlyPrice') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="plan-yearly-discount" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Yearly discount (%)</label>
                        <div class="relative">
                            <input id="plan-yearly-discount" wire:model.live="yearlyDiscountPercent" type="number" min="0" max="100" step="1" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        </div>
                        <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">Yearly price = 12 × monthly, minus this discount. Recommended 20–30%.</p>
                        @error('yearlyDiscountPercent') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                </div>
                @php
                    $monthly = max(0, (float) $monthlyPrice);
                    $yearlyDiscount = max(0, min(100, (int) $yearlyDiscountPercent));
                    $yearlyRegular = $monthly * 12;
                    $yearlyComputed = round($yearlyRegular * (1 - $yearlyDiscount / 100), 2);
                    $yearlySavings = round($yearlyRegular - $yearlyComputed, 2);
                @endphp
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-100 bg-brand-50/60 px-4 py-3.5 dark:border-brand-500/15 dark:bg-brand-500/[0.07]">
                    <div>
                        <p class="text-theme-xs font-medium uppercase tracking-wide text-brand-600 dark:text-brand-400">Yearly billing (auto-calculated)</p>
                        @if($monthly > 0)
                            <p class="mt-1 text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                                12 × ৳{{ number_format($monthly, 2) }} = ৳{{ number_format($yearlyRegular, 0) }}
                                <span class="text-gray-400 dark:text-gray-500">→</span>
                                <span class="text-brand-600 dark:text-brand-400">৳{{ number_format($yearlyComputed, 0) }}/year</span>
                                <span class="font-normal text-gray-500 dark:text-gray-400">(≈ ৳{{ number_format($yearlyComputed / 12, 0) }}/mo)</span>
                            </p>
                        @else
                            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Enter a monthly price to preview.</p>
                        @endif
                    </div>
                    @if($monthly > 0 && $yearlySavings > 0)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-success-100 px-3 py-1.5 text-theme-xs font-semibold text-success-700 dark:bg-success-500/15 dark:text-success-400">
                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                            Customer saves ৳{{ number_format($yearlySavings, 0) }} ({{ $yearlyDiscount }}%)
                        </span>
                    @endif
                </div>
                <div class="mt-4">
                    <label for="plan-description" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Description</label>
                    <textarea id="plan-description" wire:model="description" rows="2" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="What this plan includes…"></textarea>
                </div>
            </section>

            <!-- Audience -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Plan audience</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Which ISP operation type is this plan built for? Plans can be restricted to Automatic (network automation) or Manual (billing-only) workspaces — or offered to both.</p>
                </div>
                <div class="grid gap-3 lg:grid-cols-3">
                    <button type="button" wire:click="$set('operationMode', 'automatic')"
                        class="rounded-xl border p-4 text-left transition"
                        @class([
                            'border-brand-500 bg-brand-50/60 ring-2 ring-brand-500/20 dark:border-brand-500 dark:bg-brand-500/10' => $operationMode === 'automatic',
                            'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700' => $operationMode !== 'automatic',
                        ])>
                        <span class="grid h-9 w-9 place-items-center rounded-lg {{ $operationMode === 'automatic' ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">
                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        </span>
                        <span class="mt-3 block text-theme-sm font-semibold text-gray-900 dark:text-white">Automatic ISPs</span>
                        <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">Network automation — OLT, MikroTik &amp; integrations included.</span>
                    </button>
                    <button type="button" wire:click="$set('operationMode', 'manual')"
                        class="rounded-xl border p-4 text-left transition"
                        @class([
                            'border-brand-500 bg-brand-50/60 ring-2 ring-brand-500/20 dark:border-brand-500 dark:bg-brand-500/10' => $operationMode === 'manual',
                            'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700' => $operationMode !== 'manual',
                        ])>
                        <span class="grid h-9 w-9 place-items-center rounded-lg {{ $operationMode === 'manual' ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">
                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l-4 4v3h3l4-4"/><path d="M5 15l-2 6 6-2"/><path d="M21 11V7a2 2 0 0 0-2-2h-4l-4-2H5a2 2 0 0 0-2 2v4"/><path d="M15 21l2-6-6 2"/></svg>
                        </span>
                        <span class="mt-3 block text-theme-sm font-semibold text-gray-900 dark:text-white">Manual ISPs</span>
                        <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">Billing &amp; operations without network automation.</span>
                    </button>
                    <button type="button" wire:click="$set('operationMode', 'both')"
                        class="rounded-xl border p-4 text-left transition"
                        @class([
                            'border-brand-500 bg-brand-50/60 ring-2 ring-brand-500/20 dark:border-brand-500 dark:bg-brand-500/10' => $operationMode === 'both',
                            'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700' => $operationMode !== 'both',
                        ])>
                        <span class="grid h-9 w-9 place-items-center rounded-lg {{ $operationMode === 'both' ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">
                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        </span>
                        <span class="mt-3 block text-theme-sm font-semibold text-gray-900 dark:text-white">Both types</span>
                        <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">Available to every ISP workspace regardless of mode.</span>
                    </button>
                </div>
                @error('operationMode') <p class="mt-2 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
            </section>

            <!-- Limits -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Tenant limits</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Leave empty for unlimited.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="plan-customer-limit" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Customer limit</label>
                        <input id="plan-customer-limit" wire:model="customerLimit" type="number" min="1" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="Unlimited">
                        @error('customerLimit') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="plan-staff-limit" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Staff limit</label>
                        <input id="plan-staff-limit" wire:model="staffLimit" type="number" min="1" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="Unlimited">
                        @error('staffLimit') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="plan-reseller-limit" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Reseller limit</label>
                        <input id="plan-reseller-limit" wire:model="resellerLimit" type="number" min="0" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="Unlimited">
                        @error('resellerLimit') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                    <label for="plan-overflow-rate" class="flex items-center justify-between gap-3 text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                        <span>Overflow rate <span class="text-theme-xs font-normal text-gray-500 dark:text-gray-400">(৳ / customer / month)</span></span>
                        <span class="text-theme-xs font-normal text-gray-500 dark:text-gray-400">Charged only for customers above the included limit</span>
                    </label>
                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-theme-sm text-gray-400 dark:text-gray-500">৳</span>
                        <input id="plan-overflow-rate" wire:model="overflowRate" type="number" step="0.01" min="0" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-9 pr-4 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="3.00">
                    </div>
                    <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">e.g. 300 included + 50 overflow × ৳3.00 = ৳150 extra. Leave 0 when unused or customers are unlimited.</p>
                    @error('overflowRate') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
            </section>

            <!-- Trial & lifecycle -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Trial & billing lifecycle</h2>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label for="plan-trial-days" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Trial days</label>
                        <input id="plan-trial-days" wire:model="trialDays" type="number" min="0" max="365" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="0">
                        @error('trialDays') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="plan-grace-days" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Grace days</label>
                        <input id="plan-grace-days" wire:model="graceDays" type="number" min="0" max="90" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="0">
                        @error('graceDays') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-end">
                        <label class="mb-2.5 flex cursor-pointer select-none items-center gap-2.5 text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                            <input wire:model="isActive" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">
                            Available for assignment
                        </label>
                    </div>
                </div>
            </section>

            <!-- Actions -->
            <div class="sticky bottom-4 flex flex-col-reverse gap-3 rounded-2xl border border-gray-200 bg-white/95 px-5 py-4 shadow-theme-lg backdrop-blur sm:flex-row sm:items-center sm:justify-end dark:border-gray-800 dark:bg-gray-900/95">
                <button type="button" wire:click="cancel" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                    <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Save changes' : 'Create plan' }}</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </form>
    @endif
</div>
