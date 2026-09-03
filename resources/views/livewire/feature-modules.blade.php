<div class="space-y-6">
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
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Tabs -->
    <div class="inline-flex flex-wrap items-center gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <button wire:click="$set('tab', 'catalog')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'catalog' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">Global flags</button>
        <button wire:click="$set('tab', 'plans')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'plans' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">Plan entitlements</button>
        <button wire:click="$set('tab', 'tenants')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'tenants' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">Tenant overrides</button>
    </div>

    @if($tab === 'catalog')
        <!-- Global flags table -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Feature</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Key</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Global status</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($features as $feature)
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $feature->name }}</td>
                                <td class="px-5 py-4"><code class="rounded-md bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">{{ $feature->key }}</code></td>
                                <td class="px-5 py-4">
                                    @if($feature->is_globally_enabled)
                                        <span class="rounded-full px-2.5 py-1 text-theme-xs font-medium bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500">Enabled</span>
                                    @else
                                        <span class="rounded-full px-2.5 py-1 text-theme-xs font-medium bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">Disabled</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end">
                                        <button wire:click="toggleGlobal({{ $feature->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium {{ $feature->is_globally_enabled ? 'text-error-600 hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10' : 'text-success-600 hover:bg-success-50 dark:text-success-400 dark:hover:bg-success-500/10' }} transition">{{ $feature->is_globally_enabled ? 'Disable platform-wide' : 'Enable platform-wide' }}</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($tab === 'plans')
        <div class="max-w-xs">
            <label for="fm-plan" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">SaaS plan</label>
            <select id="fm-plan" wire:model.live="selectedPlanId" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                <option value="">Select a plan</option>
                @foreach($plans as $plan)<option value="{{ $plan->id }}">{{ $plan->name }}</option>@endforeach
            </select>
        </div>

        @if($selectedPlanId)
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Feature</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Included in plan</th>
                                <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($features as $feature)
                                @php $enabled = $planFeatures->has($feature->id) ? $planFeatures[$feature->id]->is_enabled : true; @endphp
                                <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $feature->name }}</td>
                                    <td class="px-5 py-4">
                                        @if($enabled)
                                            <span class="rounded-full px-2.5 py-1 text-theme-xs font-medium bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500">Included</span>
                                        @else
                                            <span class="rounded-full px-2.5 py-1 text-theme-xs font-medium bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">Excluded</span>
                                        @endif
                                        @if(!$planFeatures->has($feature->id))<span class="ml-2 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:bg-white/[0.05] dark:text-gray-400">Default</span>@endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-end">
                                            <button wire:click="togglePlanFeature({{ $feature->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium {{ $enabled ? 'text-error-600 hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10' : 'text-success-600 hover:bg-success-50 dark:text-success-400 dark:hover:bg-success-500/10' }} transition">{{ $enabled ? 'Exclude' : 'Include' }}</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <p class="py-12 text-center text-theme-sm text-gray-500 dark:text-gray-400">Select a plan to manage its feature entitlements.</p>
        @endif
    @else
        <div class="max-w-xs">
            <label for="fm-tenant" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Tenant</label>
            <select id="fm-tenant" wire:model.live="selectedTenantId" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                <option value="">Select a tenant</option>
                @foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach
            </select>
        </div>

        @if($selectedTenantId)
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="w-full overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Feature</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Effective</th>
                                <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Override</th>
                                <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($features as $feature)
                                @php $override = $tenantOverrides->get($feature->id); @endphp
                                <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $feature->name }}</td>
                                    <td class="px-5 py-4">
                                        @if(($effectiveStates[$feature->id] ?? true))
                                            <span class="rounded-full px-2.5 py-1 text-theme-xs font-medium bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500">Enabled</span>
                                        @else
                                            <span class="rounded-full px-2.5 py-1 text-theme-xs font-medium bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">Disabled</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-theme-xs text-gray-500 dark:text-gray-400">{{ $override ? ($override->is_enabled ? 'Force enabled' : 'Force disabled') : 'Inherits plan' }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-end gap-1">
                                            <button wire:click="toggleTenantOverride({{ $feature->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">{{ $override ? ($override->is_enabled ? 'Force disable' : 'Force enable') : 'Override' }}</button>
                                            @if($override)<button wire:click="clearTenantOverride({{ $feature->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Clear</button>@endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <p class="py-12 text-center text-theme-sm text-gray-500 dark:text-gray-400">Select a tenant to manage feature overrides.</p>
        @endif
    @endif
</div>
