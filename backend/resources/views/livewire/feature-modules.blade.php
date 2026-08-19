<div>
    <header class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
        <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Feature &amp; modules</h1>
        <p class="mt-2 text-sm text-slate-500">Global feature flags, per-plan entitlements, and per-tenant overrides.</p>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <div class="mb-5 flex gap-2">
        <button wire:click="$set('tab', 'catalog')" class="px-4 py-2 text-sm font-bold {{ $tab === 'catalog' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Global flags</button>
        <button wire:click="$set('tab', 'plans')" class="px-4 py-2 text-sm font-bold {{ $tab === 'plans' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Plan entitlements</button>
        <button wire:click="$set('tab', 'tenants')" class="px-4 py-2 text-sm font-bold {{ $tab === 'tenants' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Tenant overrides</button>
    </div>

    @if($tab === 'catalog')
        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Feature</th><th>Key</th><th>Global status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @foreach($features as $feature)
                        <tr>
                            <td class="font-semibold text-white">{{ $feature->name }}</td>
                            <td><code class="text-slate-500">{{ $feature->key }}</code></td>
                            <td><span class="font-semibold {{ $feature->is_globally_enabled ? 'text-emerald-300' : 'text-rose-300' }}">{{ $feature->is_globally_enabled ? 'Enabled' : 'Disabled' }}</span></td>
                            <td class="text-right"><button wire:click="toggleGlobal({{ $feature->id }})" class="font-semibold {{ $feature->is_globally_enabled ? 'text-rose-300' : 'text-emerald-300' }}">{{ $feature->is_globally_enabled ? 'Disable platform-wide' : 'Enable platform-wide' }}</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif($tab === 'plans')
        <div class="mb-5 max-w-xs"><label class="bc-label" for="fm-plan">SaaS plan</label><select id="fm-plan" wire:model.live="selectedPlanId" class="bc-field"><option value="">Select a plan</option>@foreach($plans as $plan)<option value="{{ $plan->id }}">{{ $plan->name }}</option>@endforeach</select></div>

        @if($selectedPlanId)
            <div class="bc-table-wrap">
                <table class="bc-table">
                    <thead><tr><th>Feature</th><th>Included in plan</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                        @foreach($features as $feature)
                            @php $enabled = $planFeatures->has($feature->id) ? $planFeatures[$feature->id]->is_enabled : true; @endphp
                            <tr>
                                <td class="font-semibold text-white">{{ $feature->name }}</td>
                                <td><span class="font-semibold {{ $enabled ? 'text-emerald-300' : 'text-slate-500' }}">{{ $enabled ? 'Included' : 'Excluded' }}</span>@if(!$planFeatures->has($feature->id))<span class="ml-2 text-[10px] uppercase text-slate-600">Default</span>@endif</td>
                                <td class="text-right"><button wire:click="togglePlanFeature({{ $feature->id }})" class="font-semibold {{ $enabled ? 'text-rose-300' : 'text-emerald-300' }}">{{ $enabled ? 'Exclude' : 'Include' }}</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="py-12 text-center text-slate-600">Select a plan to manage its feature entitlements.</p>
        @endif
    @else
        <div class="mb-5 max-w-xs"><label class="bc-label" for="fm-tenant">Tenant</label><select id="fm-tenant" wire:model.live="selectedTenantId" class="bc-field"><option value="">Select a tenant</option>@foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach</select></div>

        @if($selectedTenantId)
            <div class="bc-table-wrap">
                <table class="bc-table">
                    <thead><tr><th>Feature</th><th>Effective</th><th>Override</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                        @foreach($features as $feature)
                            @php $override = $tenantOverrides->get($feature->id); @endphp
                            <tr>
                                <td class="font-semibold text-white">{{ $feature->name }}</td>
                                <td><span class="font-semibold {{ ($effectiveStates[$feature->id] ?? true) ? 'text-emerald-300' : 'text-rose-300' }}">{{ ($effectiveStates[$feature->id] ?? true) ? 'Enabled' : 'Disabled' }}</span></td>
                                <td class="text-xs text-slate-500">{{ $override ? ($override->is_enabled ? 'Force enabled' : 'Force disabled') : 'Inherits plan' }}</td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-3">
                                        <button wire:click="toggleTenantOverride({{ $feature->id }})" class="font-semibold text-teal-300">{{ $override ? ($override->is_enabled ? 'Force disable' : 'Force enable') : 'Override' }}</button>
                                        @if($override)<button wire:click="clearTenantOverride({{ $feature->id }})" class="font-semibold text-slate-400">Clear</button>@endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="py-12 text-center text-slate-600">Select a tenant to manage feature overrides.</p>
        @endif
    @endif
</div>
