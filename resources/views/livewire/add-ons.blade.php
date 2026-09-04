<div class="space-y-6">
    @php
        $categories = \App\Livewire\AddOns::CATEGORIES;
        $categoryChip = function (string $key): string {
            return match ($key) {
                'sms' => 'bg-sky-50 text-sky-600 ring-1 ring-inset ring-sky-100 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-500/25',
                'email' => 'bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-400 dark:ring-indigo-500/25',
                'storage' => 'bg-blue-50 text-blue-600 ring-1 ring-inset ring-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/25',
                'media' => 'bg-fuchsia-50 text-fuchsia-600 ring-1 ring-inset ring-fuchsia-100 dark:bg-fuchsia-500/10 dark:text-fuchsia-400 dark:ring-fuchsia-500/25',
                'white_label' => 'bg-violet-50 text-violet-600 ring-1 ring-inset ring-violet-100 dark:bg-violet-500/10 dark:text-violet-400 dark:ring-violet-500/25',
                'custom_domain' => 'bg-cyan-50 text-cyan-600 ring-1 ring-inset ring-cyan-100 dark:bg-cyan-500/10 dark:text-cyan-400 dark:ring-cyan-500/25',
                'branded_app' => 'bg-amber-50 text-amber-600 ring-1 ring-inset ring-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/25',
                'premium_support' => 'bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/25',
                'network_integration' => 'bg-teal-50 text-teal-600 ring-1 ring-inset ring-teal-100 dark:bg-teal-500/10 dark:text-teal-400 dark:ring-teal-500/25',
                'infrastructure' => 'bg-slate-50 text-slate-600 ring-1 ring-inset ring-slate-100 dark:bg-slate-500/10 dark:text-slate-400 dark:ring-slate-500/25',
                'custom_dev' => 'bg-rose-50 text-rose-600 ring-1 ring-inset ring-rose-100 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/25',
                default => 'bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-200 dark:bg-white/[0.05] dark:text-gray-400 dark:ring-gray-700',
            };
        };
        $cycleLabel = fn (string $cycle) => ['one_time' => 'One-time', 'monthly' => 'Monthly', 'yearly' => 'Yearly'][$cycle] ?? $cycle;
        $cycleShort = fn (string $cycle) => ['one_time' => 'one time', 'monthly' => 'mo', 'yearly' => 'yr'][$cycle] ?? $cycle;
    @endphp

    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Add-ons</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Sellable add-on catalog, tenant assignments, usage tracking, and revenue by add-on.</p>
        </div>
        <div class="flex shrink-0 items-center gap-3">
            @if($tab === 'catalog')
                <button type="button" wire:click="create" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Create add-on
                </button>
            @endif
        </div>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Stats -->
    <section class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-5">
        @php
            $statsCards = [
                ['label' => 'Catalog add-ons', 'value' => number_format($stats['catalog']), 'icon' => 'M20 7L9 18l-5-5', 'tone' => 'text-brand-600 dark:text-brand-400', 'chip' => 'bg-brand-500/10'],
                ['label' => 'Live in marketplace', 'value' => number_format($stats['active_catalog']), 'icon' => 'M22 11.08V12a10 10 0 1 1-5.93-9.14', 'tone' => 'text-success-600 dark:text-success-400', 'chip' => 'bg-success-500/10'],
                ['label' => 'Active assignments', 'value' => number_format($stats['active_assignments']), 'icon' => 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2', 'tone' => 'text-cyan-600 dark:text-cyan-400', 'chip' => 'bg-cyan-500/10'],
                ['label' => 'Pending approvals', 'value' => number_format($stats['pending_approvals']), 'icon' => 'M12 8v4l2 2', 'tone' => 'text-warning-600 dark:text-warning-400', 'chip' => 'bg-warning-500/10'],
                ['label' => 'Recurring / month', 'value' => '৳'.number_format($stats['revenue_monthly'], 0), 'icon' => 'M12 2a5 5 0 0 1 4.9 6.2A6 6 0 0 1 18 14a6 6 0 0 1-12 0 6 6 0 0 1 1.1-7.8A5 5 0 0 1 12 2z', 'tone' => 'text-gray-800 dark:text-white/90', 'chip' => 'bg-gray-500/10'],
            ];
        @endphp
        @foreach($statsCards as $card)
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $card['chip'] }}">
                        <svg class="size-4 stroke-current {{ $card['tone'] }}" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $card['icon'] }}"/></svg>
                    </span>
                    <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                </div>
                <p class="mt-2.5 text-xl font-bold {{ $card['tone'] }}">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </section>

    <!-- Tabs -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="inline-flex flex-wrap items-center gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <button type="button" wire:click="$set('tab', 'catalog')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'catalog' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
                Catalog
                <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'catalog' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $stats['catalog'] }}</span>
            </button>
            <button type="button" wire:click="$set('tab', 'assignments')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'assignments' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
                Tenant assignments
                @if($stats['pending_approvals'] > 0)
                    <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'assignments' ? 'bg-warning-400/90 text-gray-900' : 'bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-400' }}">{{ $stats['pending_approvals'] }}</span>
                @endif
            </button>
        </div>
        @if($tab === 'catalog' && $stats['catalog'] > 0)
            <p class="text-theme-xs text-gray-400 dark:text-gray-500">{{ $stats['active_catalog'] }} of {{ $stats['catalog'] }} add-ons are live in the ISP marketplace.</p>
        @endif
    </div>

    @if($tab === 'catalog')
        <!-- Catalog table -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Add-on</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Category</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Price</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Usage</th>
                            <th class="px-5 py-3.5 text-center text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tenants</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Revenue</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($addons as $addon)
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-gradient-to-br from-brand-500 to-indigo-500 text-theme-sm font-bold text-white">{{ strtoupper(substr($addon->name, 0, 1)) }}</span>
                                        <div class="min-w-0">
                                            <p class="truncate text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $addon->name }}</p>
                                            <p class="mt-0.5 truncate text-theme-xs text-gray-400 dark:text-gray-500">{{ $addon->description ?: $addon->slug }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $categoryChip($addon->category) }}">{{ $categories[$addon->category] ?? $addon->category }}</span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">৳{{ number_format($addon->price, 2) }}</span>
                                    <span class="block text-theme-xs text-gray-400 dark:text-gray-500">/ {{ $cycleLabel($addon->billing_cycle) }}</span>
                                </td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">
                                    @if($addon->usage_limit)
                                        {{ number_format($addon->usage_limit) }} {{ $addon->usage_unit }}
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">Unlimited</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if($addon->is_active)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-500">
                                            <span class="size-1.5 rounded-full bg-success-500"></span>Live
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs font-semibold text-gray-500 dark:bg-white/[0.05] dark:text-gray-400">
                                            <span class="size-1.5 rounded-full bg-gray-400"></span>Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <span class="inline-flex items-center justify-center gap-1 rounded-lg bg-gray-100 px-2 py-1 text-theme-xs font-bold text-gray-700 dark:bg-white/[0.06] dark:text-gray-300">{{ $addon->active_assignments }}</span>
                                </td>
                                <td class="px-5 py-4 text-right text-theme-sm font-medium text-success-600 dark:text-success-400">৳{{ number_format($revenueByAddon[$addon->id]->total ?? 0, 0) }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" wire:click="edit({{ $addon->id }})" title="Edit add-on" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                        <button type="button" wire:click="toggleActive({{ $addon->id }})" title="{{ $addon->is_active ? 'Take offline' : 'Publish to marketplace' }}" class="grid h-8 w-8 place-items-center rounded-lg border {{ $addon->is_active ? 'border-warning-200 bg-warning-50 text-warning-600 hover:border-warning-300 hover:bg-warning-100 dark:border-warning-500/25 dark:bg-warning-500/10 dark:text-warning-400' : 'border-success-200 bg-success-50 text-success-600 hover:border-success-300 hover:bg-success-100 dark:border-success-500/25 dark:bg-success-500/10 dark:text-success-400' }}">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                        </button>
                                        <button type="button" wire:click="archive({{ $addon->id }})" wire:confirm="Archive this add-on? It will be removed from the catalog and can no longer be sold." title="Archive" class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 hover:text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400 dark:hover:border-error-500/40 dark:hover:bg-error-500/15 dark:hover:text-error-300">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                                        </span>
                                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No add-ons in the catalog yet</p>
                                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Create your first sellable add-on to open the ISP marketplace.</p>
                                        <button type="button" wire:click="create" class="mt-4 inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Create add-on</button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- Assign add-on to tenant -->
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                </span>
                <div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Assign add-on to tenant</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Activate an add-on directly for a tenant without waiting for a marketplace purchase.</p>
                </div>
            </div>
            <form wire:submit="assignAddon" class="mt-5 grid items-end gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label for="assign-tenant" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Tenant</label>
                    <select id="assign-tenant" wire:model="assignTenantId" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        <option value="">Select tenant</option>
                        @foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach
                    </select>
                    @error('assignTenantId') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="assign-addon" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Add-on</label>
                    <select id="assign-addon" wire:model="assignAddonId" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        <option value="">Select add-on</option>
                        @foreach($activeAddons as $addon)<option value="{{ $addon->id }}">{{ $addon->name }} (৳{{ number_format($addon->price, 2) }}/{{ $cycleShort($addon->billing_cycle) }})</option>@endforeach
                    </select>
                    @error('assignAddonId') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="assign-cycle" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Billing cycle</label>
                    <select id="assign-cycle" wire:model="assignBillingCycle" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                        <option value="one_time">One-time</option>
                    </select>
                </div>
                <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Assign add-on
                </button>
            </form>
        </section>

        <!-- Assignments -->
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Tenant assignments</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Latest {{ $assignments->count() }} records — direct grants and marketplace purchases.</p>
                </div>
                @if($stats['active_assignments'] > 0)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-400">
                        <span class="size-1.5 rounded-full bg-success-500"></span>{{ $stats['active_assignments'] }} active
                    </span>
                @endif
            </div>
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50/50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tenant</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Add-on</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Price</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Usage</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($assignments as $assignment)
                            @php
                                $statusTone = match ($assignment->status) {
                                    'active' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
                                    'requested', 'pending_approval' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                                    default => 'bg-gray-100 text-gray-500 dark:bg-white/[0.05] dark:text-gray-400',
                                };
                                $pct = $assignment->addon->usage_limit ? min(100, (int) round($assignment->usage_used / $assignment->addon->usage_limit * 100)) : null;
                                $smsWallet = $assignment->addon->category === 'sms' ? ($smsWalletByTenant[$assignment->tenant_id] ?? null) : null;
                                $smsPct = $smsWallet && ($smsWallet['included'] ?? 0) > 0 ? min(100, (int) round($smsWallet['used'] / $smsWallet['included'] * 100)) : 0;
                            @endphp
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2.5">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-theme-xs font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ strtoupper(substr($assignment->tenant->name, 0, 1)) }}</span>
                                        <a href="{{ route('tenant-details', $assignment->tenant) }}" class="text-theme-sm font-medium text-gray-800 hover:text-brand-600 dark:text-white/90 dark:hover:text-brand-400">{{ $assignment->tenant->name }}</a>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $assignment->addon->name }}</span>
                                    <span class="ml-2 inline-flex rounded-full px-2 py-0.5 text-theme-xs font-medium {{ $categoryChip($assignment->addon->category) }}">{{ $categories[$assignment->addon->category] ?? $assignment->addon->category }}</span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">৳{{ number_format($assignment->price, 2) }}</span>
                                    <span class="block text-theme-xs text-gray-400 dark:text-gray-500">/ {{ $cycleLabel($assignment->billing_cycle) }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    @if($smsWallet && ($smsWallet['included'] ?? 0) > 0)
                                        <div class="max-w-44">
                                            <div class="flex items-center justify-between gap-2 text-theme-xs">
                                                <span class="font-semibold text-gray-800 dark:text-white/90">{{ number_format($smsWallet['remaining']) }} {{ $assignment->addon->usage_unit ?: 'credits' }} left</span>
                                                <span class="text-gray-500 dark:text-gray-400">{{ $smsPct }}% used</span>
                                            </div>
                                            <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/[0.06]">
                                                <div class="h-full rounded-full {{ $smsPct >= 90 ? 'bg-error-500' : 'bg-brand-500' }}" style="width: {{ $smsPct }}%"></div>
                                            </div>
                                            <p class="mt-1 text-theme-xs text-gray-400 dark:text-gray-500">{{ number_format($smsWallet['used']) }} of {{ number_format($smsWallet['included']) }} {{ $assignment->addon->usage_unit ?: 'credits' }} used</p>
                                        </div>
                                    @elseif($pct !== null)
                                        <div class="max-w-40">
                                            <div class="flex items-center justify-between gap-2 text-theme-xs">
                                                <span class="text-gray-500 dark:text-gray-400">{{ number_format($assignment->usage_used) }} / {{ number_format($assignment->addon->usage_limit) }} {{ $assignment->addon->usage_unit }}</span>
                                                <span class="font-semibold {{ $pct >= 90 ? 'text-error-600 dark:text-error-400' : 'text-gray-600 dark:text-gray-300' }}">{{ $pct }}%</span>
                                            </div>
                                            <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/[0.06]">
                                                <div class="h-full rounded-full {{ $pct >= 90 ? 'bg-error-500' : 'bg-brand-500' }}" style="width: {{ $pct }}%"></div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-theme-sm text-gray-500 dark:text-gray-400">{{ number_format($assignment->usage_used) }} used</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $statusTone }}">{{ $assignment->status === 'pending_approval' ? 'Awaiting payment' : str_replace('_', ' ', $assignment->status) }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if(in_array($assignment->status, ['requested', 'pending_approval'], true))
                                            <button type="button" wire:click="approveRequest({{ $assignment->id }})" wire:confirm="Approve this add-on request? It will activate for the tenant immediately." class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-success-200 bg-success-50 px-3 py-2 text-theme-xs font-semibold text-success-600 transition hover:border-success-300 hover:bg-success-100 hover:text-success-700 dark:border-success-500/25 dark:bg-success-500/10 dark:text-success-400 dark:hover:border-success-500/40 dark:hover:bg-success-500/15 dark:hover:text-success-300">
                                                <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                Approve
                                            </button>
                                            <button type="button" wire:click="declineRequest({{ $assignment->id }})" wire:confirm="Decline this add-on request?" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-error-200 bg-error-50 px-3 py-2 text-theme-xs font-medium text-error-600 transition hover:border-error-300 hover:bg-error-100 hover:text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400 dark:hover:border-error-500/40 dark:hover:bg-error-500/15 dark:hover:text-error-300">
                                                {{ __('Decline') }}
                                            </button>
                                        @elseif($assignment->status === 'active')
                                            @if(! $smsWallet)
                                                <button type="button" wire:click="openUsage({{ $assignment->id }})" title="Log usage" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-theme-xs font-medium text-gray-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                                    <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                                                    Usage
                                                </button>
                                            @endif
                                            <button type="button" wire:click="cancelAssignment({{ $assignment->id }})" wire:confirm="Cancel this add-on assignment?" title="Cancel assignment" class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 hover:text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400 dark:hover:border-error-500/40 dark:hover:bg-error-500/15 dark:hover:text-error-300">
                                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                        </span>
                                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No add-ons assigned yet</p>
                                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Use the form above to grant an add-on directly to a tenant.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @if($viewMode === 'create')
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="addon-dialog-title">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="cancelForm"></div>
            <div class="relative max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 id="addon-dialog-title" class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $addonId ? 'Edit add-on' : 'Create add-on' }}</h3>
                        <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $addonId ? 'Update the marketplace listing details.' : 'Define a new add-on ISPs can buy from the marketplace.' }}</p>
                    </div>
                    <button type="button" wire:click="cancelForm" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form wire:submit="save" class="space-y-5">
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="ad-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Name</label>
                            <input id="ad-name" wire:model.live="name" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="e.g. SMS Booster 10k">
                            @error('name') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="ad-slug" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Slug</label>
                            <input id="ad-slug" wire:model="slug" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            @error('slug') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label for="ad-category" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Category</label>
                        <select id="ad-category" wire:model="category" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            @foreach($categories as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label for="ad-desc" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Description</label>
                        <textarea id="ad-desc" wire:model="description" rows="2" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="What does the tenant get?"></textarea>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="ad-price" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Price (৳)</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-theme-sm text-gray-400">৳</span>
                                <input id="ad-price" wire:model="price" type="number" step="0.01" min="0" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-9 pr-4 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            </div>
                            @error('price') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="ad-cycle" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Billing cycle</label>
                            <select id="ad-cycle" wire:model="billingCycle" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                <option value="one_time">One-time</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="ad-limit" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Usage limit (optional)</label>
                            <input id="ad-limit" wire:model="usageLimit" type="number" min="1" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="Unlimited">
                            @error('usageLimit') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="ad-unit" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Usage unit</label>
                            <input id="ad-unit" wire:model="usageUnit" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="MB, credits, seats">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <button type="button" wire:click="cancelForm" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            {{ $addonId ? 'Save changes' : 'Create add-on' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($usageForAssignmentId)
        @php $usageAssignment = $assignments->firstWhere('id', $usageForAssignmentId); @endphp
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="usage-dialog-title">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeModals"></div>
            <div class="relative max-h-[92vh] w-full max-w-sm overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 id="usage-dialog-title" class="text-lg font-semibold text-gray-800 dark:text-white/90">Log usage</h3>
                        @if($usageAssignment)
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $usageAssignment->tenant->name }} · {{ $usageAssignment->addon->name }}</p>
                        @endif
                    </div>
                    <button type="button" wire:click="closeModals" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                @if($usageAssignment && $usageAssignment->addon->usage_limit)
                    <div class="mb-5 rounded-xl border border-gray-200 bg-gray-50/60 px-4 py-3 text-theme-sm dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-500 dark:text-gray-400">Used so far</span>
                            <span class="font-semibold text-gray-800 dark:text-white/90">{{ number_format($usageAssignment->usage_used) }} / {{ number_format($usageAssignment->addon->usage_limit) }} {{ $usageAssignment->addon->usage_unit }}</span>
                        </div>
                    </div>
                @endif
                <form wire:submit="recordUsage" class="space-y-5">
                    <div>
                        <label for="usage-amount" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Amount to add{{ $usageAssignment?->addon->usage_unit ? ' ('.$usageAssignment->addon->usage_unit.')' : '' }}</label>
                        <input id="usage-amount" wire:model="usageAmount" type="number" min="1" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('usageAmount') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <button type="button" wire:click="closeModals" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Save usage</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
