<div class="space-y-6">
    @php
        $enabledGlobal = $features->where('is_globally_enabled')->count();
        $palette = ['text-sky-600', 'text-violet-600', 'text-emerald-600', 'text-rose-600', 'text-amber-600', 'text-cyan-600', 'text-fuchsia-600', 'text-lime-600'];
        $tintFor = function (string $key): string {
            $palette = ['bg-sky-50 dark:bg-sky-500/10', 'bg-violet-50 dark:bg-violet-500/10', 'bg-emerald-50 dark:bg-emerald-500/10', 'bg-rose-50 dark:bg-rose-500/10', 'bg-amber-50 dark:bg-amber-500/10', 'bg-cyan-50 dark:bg-cyan-500/10', 'bg-fuchsia-50 dark:bg-fuchsia-500/10', 'bg-lime-50 dark:bg-lime-500/10'];
            return $palette[crc32($key) % count($palette)];
        };
    @endphp

    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Feature &amp; modules</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Global feature flags, per-plan entitlements, and per-tenant overrides.</p>
        </div>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Overview -->
    <section class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        @php
            $overview = [
                ['label' => 'Feature modules', 'value' => number_format($features->count()), 'hint' => 'In the BeeCore catalog', 'icon' => 'grid', 'tone' => 'text-brand-600 dark:text-brand-400', 'chip' => 'bg-brand-500/10'],
                ['label' => 'Enabled platform-wide', 'value' => $enabledGlobal.' / '.$features->count(), 'hint' => 'On for every workspace', 'icon' => 'check', 'tone' => 'text-success-600 dark:text-success-400', 'chip' => 'bg-success-500/10'],
                ['label' => 'SaaS plans', 'value' => number_format($plans->count()), 'hint' => 'Entitlement matrices', 'icon' => 'layers', 'tone' => 'text-violet-600 dark:text-violet-400', 'chip' => 'bg-violet-500/10'],
                ['label' => 'Active tenants', 'value' => number_format($tenants->count()), 'hint' => 'Eligible for overrides', 'icon' => 'users', 'tone' => 'text-cyan-600 dark:text-cyan-400', 'chip' => 'bg-cyan-500/10'],
            ];
            $icons = [
                'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.2"/><rect x="14" y="3" width="7" height="7" rx="1.2"/><rect x="14" y="14" width="7" height="7" rx="1.2"/><rect x="3" y="14" width="7" height="7" rx="1.2"/>',
                'check' => '<polyline points="20 6 9 17 4 12"/>',
                'layers' => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>',
                'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            ];
        @endphp
        @foreach($overview as $stat)
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $stat['chip'] }}">
                        <svg class="size-4 stroke-current {{ $stat['tone'] }}" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$stat['icon']] !!}</svg>
                    </span>
                    <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</p>
                </div>
                <p class="mt-2.5 text-xl font-bold {{ $stat['tone'] }}">{{ $stat['value'] }}</p>
                <p class="mt-0.5 text-theme-xs text-gray-400 dark:text-gray-500">{{ $stat['hint'] }}</p>
            </div>
        @endforeach
    </section>

    <!-- Tabs -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="inline-flex flex-wrap items-center gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <button type="button" wire:click="$set('tab', 'catalog')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'catalog' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
                Global flags
                <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'catalog' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $features->count() }}</span>
            </button>
            <button type="button" wire:click="$set('tab', 'plans')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'plans' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
                Plan entitlements
                <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'plans' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $plans->count() }}</span>
            </button>
            <button type="button" wire:click="$set('tab', 'tenants')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'tenants' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
                Tenant overrides
                <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'tenants' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $tenants->count() }}</span>
            </button>
        </div>
        @if($tab === 'catalog')
            <p class="text-theme-xs text-gray-400 dark:text-gray-500">Turning a flag off hides the module everywhere; plan &amp; tenant rules still apply when it is on.</p>
        @endif
    </div>

    @if($tab === 'catalog')
        <!-- Global flags table -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Module</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($features as $feature)
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $tintFor($feature->key) }}">
                                            <svg class="size-4 stroke-current text-gray-600 dark:text-gray-300" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $feature->name }}</p>
                                            <p class="mt-0.5 truncate text-theme-xs text-gray-400 dark:text-gray-500">{{ $feature->description ?: $feature->key }}</p>
                                            <p class="mt-0.5"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs text-gray-500 dark:bg-white/[0.05] dark:text-gray-400">{{ $feature->key }}</code></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @if($feature->is_globally_enabled)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-500"><span class="size-1.5 rounded-full bg-success-500"></span>Enabled</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-error-50 px-2.5 py-1 text-theme-xs font-semibold text-error-600 dark:bg-error-500/15 dark:text-error-500"><span class="size-1.5 rounded-full bg-error-500"></span>Disabled</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end">
                                        <button type="button" wire:click="toggleGlobal({{ $feature->id }})" class="inline-flex items-center justify-center gap-1.5 rounded-lg border px-3 py-2 text-theme-xs font-semibold transition {{ $feature->is_globally_enabled ? 'border-error-200 bg-error-50 text-error-600 hover:border-error-300 hover:bg-error-100 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400' : 'border-success-200 bg-success-50 text-success-600 hover:border-success-300 hover:bg-success-100 dark:border-success-500/25 dark:bg-success-500/10 dark:text-success-400' }}">
                                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg>
                                            {{ $feature->is_globally_enabled ? 'Disable platform-wide' : 'Enable platform-wide' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                        </span>
                                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No feature modules found</p>
                                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">The module catalog is seeded automatically with new installs.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($tab === 'plans')
        @php $selectedPlan = $plans->firstWhere('id', $selectedPlanId); @endphp

        <div class="grid items-start gap-5 lg:grid-cols-[280px_1fr]">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">SaaS plan</p>
                <div class="mt-3 space-y-1">
                    @forelse($plans as $plan)
                        <button type="button" wire:click="$set('selectedPlanId', {{ $plan->id }})" class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2.5 text-left text-theme-sm font-medium transition {{ $selectedPlanId === $plan->id ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.04]' }}">
                            <span class="truncate">{{ $plan->name }}</span>
                            <svg class="size-4 shrink-0 stroke-current {{ $selectedPlanId === $plan->id ? 'text-brand-600 dark:text-brand-400' : 'text-gray-300 dark:text-gray-600' }}" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </button>
                    @empty
                        <p class="px-3 py-2 text-theme-xs text-gray-400">No active plans.</p>
                    @endforelse
                </div>
            </div>

            @if($selectedPlan)
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $selectedPlan->name }} entitlements</h2>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Which modules this plan includes for its subscribers.</p>
                        </div>
                        @php $included = $features->filter(fn ($f) => $planFeatures->has($f->id) ? $planFeatures[$f->id]->is_enabled : true)->count(); @endphp
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-500"><span class="size-1.5 rounded-full bg-success-500"></span>{{ $included }} of {{ $features->count() }} included</span>
                    </div>
                    <div class="w-full overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b border-gray-200 bg-gray-50/50 dark:border-gray-800 dark:bg-white/[0.02]">
                                    <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Module</th>
                                    <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Included in plan</th>
                                    <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($features as $feature)
                                    @php $enabled = $planFeatures->has($feature->id) ? $planFeatures[$feature->id]->is_enabled : true; $isDefault = ! $planFeatures->has($feature->id); @endphp
                                    <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-3">
                                                <span class="grid size-8 shrink-0 place-items-center rounded-lg {{ $tintFor($feature->key) }}">
                                                    <svg class="size-3.5 stroke-current text-gray-600 dark:text-gray-300" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $feature->name }}</p>
                                                    @if($feature->description)<p class="mt-0.5 truncate text-theme-xs text-gray-400 dark:text-gray-500">{{ $feature->description }}</p>@endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4">
                                            @if($enabled)
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-500"><span class="size-1.5 rounded-full bg-success-500"></span>Included</span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-error-50 px-2.5 py-1 text-theme-xs font-semibold text-error-600 dark:bg-error-500/15 dark:text-error-500"><span class="size-1.5 rounded-full bg-error-500"></span>Excluded</span>
                                            @endif
                                            @if($isDefault)
                                                <span class="ml-1.5 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:bg-white/[0.06] dark:text-gray-400">Default</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex items-center justify-end">
                                                <button type="button" wire:click="togglePlanFeature({{ $feature->id }})" class="inline-flex items-center justify-center gap-1.5 rounded-lg border px-3 py-2 text-theme-xs font-semibold transition {{ $enabled ? 'border-error-200 bg-error-50 text-error-600 hover:border-error-300 hover:bg-error-100 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400' : 'border-success-200 bg-success-50 text-success-600 hover:border-success-300 hover:bg-success-100 dark:border-success-500/25 dark:bg-success-500/10 dark:text-success-400' }}">
                                                    @if($enabled)
                                                        <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                    @else
                                                        <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                                    @endif
                                                    {{ $enabled ? 'Exclude' : 'Include' }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="grid place-items-center rounded-2xl border border-dashed border-gray-200 bg-white/50 px-5 py-16 text-center dark:border-gray-800 dark:bg-white/[0.02]">
                    <div class="max-w-xs">
                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                        </span>
                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">Select a plan to manage entitlements</p>
                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Pick a plan on the left to include or exclude modules per plan.</p>
                    </div>
                </div>
            @endif
        </div>
    @else
        @php $selectedTenant = $tenants->firstWhere('id', $selectedTenantId); @endphp

        <div class="grid items-start gap-5 lg:grid-cols-[280px_1fr]">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tenant</p>
                <div class="mt-3 space-y-1">
                    @forelse($tenants as $tenant)
                        <button type="button" wire:click="$set('selectedTenantId', {{ $tenant->id }})" class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2.5 text-left text-theme-sm font-medium transition {{ $selectedTenantId === $tenant->id ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.04]' }}">
                            <span class="truncate">{{ $tenant->name }}</span>
                            <svg class="size-4 shrink-0 stroke-current {{ $selectedTenantId === $tenant->id ? 'text-brand-600 dark:text-brand-400' : 'text-gray-300 dark:text-gray-600' }}" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </button>
                    @empty
                        <p class="px-3 py-2 text-theme-xs text-gray-400">No tenants yet.</p>
                    @endforelse
                </div>
            </div>

            @if($selectedTenant)
                @php
                    $overridesCount = $tenantOverrides->count();
                    $effectiveEnabled = collect($effectiveStates)->filter()->count();
                @endphp
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $selectedTenant->name }}</h2>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Effective outcome for this workspace — force a module on or off, or inherit the plan.</p>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-500"><span class="size-1.5 rounded-full bg-success-500"></span>{{ $effectiveEnabled }} active</span>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs font-semibold text-gray-500 dark:bg-white/[0.06] dark:text-gray-400">{{ $overridesCount }} override{{ $overridesCount === 1 ? '' : 's' }}</span>
                        </div>
                    </div>
                    <div class="w-full overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b border-gray-200 bg-gray-50/50 dark:border-gray-800 dark:bg-white/[0.02]">
                                    <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Module</th>
                                    <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Effective</th>
                                    <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Override</th>
                                    <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($features as $feature)
                                    @php $override = $tenantOverrides->get($feature->id); @endphp
                                    <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-3">
                                                <span class="grid size-8 shrink-0 place-items-center rounded-lg {{ $tintFor($feature->key) }}">
                                                    <svg class="size-3.5 stroke-current text-gray-600 dark:text-gray-300" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $feature->name }}</p>
                                                    @if($feature->description)<p class="mt-0.5 truncate text-theme-xs text-gray-400 dark:text-gray-500">{{ $feature->description }}</p>@endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4">
                                            @if(($effectiveStates[$feature->id] ?? true))
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-500"><span class="size-1.5 rounded-full bg-success-500"></span>Enabled</span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-error-50 px-2.5 py-1 text-theme-xs font-semibold text-error-600 dark:bg-error-500/15 dark:text-error-500"><span class="size-1.5 rounded-full bg-error-500"></span>Disabled</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            @if($override)
                                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $override->is_enabled ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400' : 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400' }}">
                                                    <svg class="size-3 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                                    {{ $override->is_enabled ? 'Force enabled' : 'Force disabled' }}
                                                </span>
                                            @else
                                                <span class="text-theme-xs text-gray-400 dark:text-gray-500">Inherits plan</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button type="button" wire:click="toggleTenantOverride({{ $feature->id }})" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-theme-xs font-semibold text-gray-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                                    {{ $override ? ($override->is_enabled ? 'Force disable' : 'Force enable') : 'Override' }}
                                                </button>
                                                @if($override)
                                                    <button type="button" wire:click="clearTenantOverride({{ $feature->id }})" title="Clear override" class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">
                                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="grid place-items-center rounded-2xl border border-dashed border-gray-200 bg-white/50 px-5 py-16 text-center dark:border-gray-800 dark:bg-white/[0.02]">
                    <div class="max-w-xs">
                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </span>
                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">Select a tenant to manage overrides</p>
                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Pick a tenant on the left to override individual modules.</p>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
