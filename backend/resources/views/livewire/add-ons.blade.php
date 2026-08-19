<div>
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Add-ons</h1>
            <p class="mt-2 text-sm text-slate-500">Sellable add-on catalog, tenant assignment, usage tracking, and revenue by add-on.</p>
        </div>
        @if($tab === 'catalog')<button wire:click="create" class="bc-primary">Create add-on</button>@endif
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <div class="mb-5 flex gap-2">
        <button wire:click="$set('tab', 'catalog')" class="px-4 py-2 text-sm font-bold {{ $tab === 'catalog' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Catalog</button>
        <button wire:click="$set('tab', 'assignments')" class="px-4 py-2 text-sm font-bold {{ $tab === 'assignments' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Tenant assignments</button>
    </div>

    @if($tab === 'catalog')
        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Add-on</th><th>Category</th><th>Price</th><th>Usage limit</th><th>Status</th><th>Active tenants</th><th>Revenue</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($addons as $addon)
                        <tr>
                            <td><div class="font-bold text-white">{{ $addon->name }}</div><div class="text-xs text-slate-600">{{ $addon->slug }}</div></td>
                            <td>{{ \App\Livewire\AddOns::CATEGORIES[$addon->category] ?? $addon->category }}</td>
                            <td>৳{{ number_format($addon->price, 2) }} / {{ str_replace('_', ' ', $addon->billing_cycle) }}</td>
                            <td>{{ $addon->usage_limit ? number_format($addon->usage_limit).' '.$addon->usage_unit : 'Unlimited' }}</td>
                            <td><span class="font-semibold {{ $addon->is_active ? 'text-emerald-300' : 'text-slate-500' }}">{{ $addon->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>{{ $addon->active_assignments }}</td>
                            <td>৳{{ number_format($revenueByAddon[$addon->id]->total ?? 0, 2) }}</td>
                            <td class="text-right">
                                <div class="flex flex-wrap justify-end gap-3">
                                    <button wire:click="toggleActive({{ $addon->id }})" class="font-semibold {{ $addon->is_active ? 'text-amber-300' : 'text-emerald-300' }}">{{ $addon->is_active ? 'Deactivate' : 'Activate' }}</button>
                                    <button wire:click="edit({{ $addon->id }})" class="font-semibold text-teal-300">Edit</button>
                                    <button wire:click="archive({{ $addon->id }})" wire:confirm="Archive this add-on?" class="font-semibold text-rose-300">Archive</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-12 text-center text-slate-600">No add-ons in the catalog yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="mb-5 border border-white/10 bg-white/[0.02] p-4" style="border-radius:6px">
            <h2 class="mb-3 text-sm font-bold text-white">Assign add-on to tenant</h2>
            <form wire:submit="assignAddon" class="grid gap-3 sm:grid-cols-4">
                <select wire:model="assignTenantId" class="bc-field"><option value="">Select tenant</option>@foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach</select>
                <select wire:model="assignAddonId" class="bc-field"><option value="">Select add-on</option>@foreach($activeAddons as $addon)<option value="{{ $addon->id }}">{{ $addon->name }} (৳{{ number_format($addon->price, 2) }})</option>@endforeach</select>
                <select wire:model="assignBillingCycle" class="bc-field"><option value="monthly">Monthly</option><option value="yearly">Yearly</option><option value="one_time">One-time</option></select>
                <button type="submit" class="bc-primary">Assign</button>
            </form>
            @error('assignTenantId')<p class="mt-2 text-xs text-rose-300">{{ $message }}</p>@enderror
            @error('assignAddonId')<p class="mt-2 text-xs text-rose-300">{{ $message }}</p>@enderror
        </div>

        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Tenant</th><th>Add-on</th><th>Price</th><th>Usage</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        <tr>
                            <td><a href="{{ route('tenant-details', $assignment->tenant) }}" class="font-semibold text-teal-300">{{ $assignment->tenant->name }}</a></td>
                            <td>{{ $assignment->addon->name }}</td>
                            <td>৳{{ number_format($assignment->price, 2) }} / {{ str_replace('_', ' ', $assignment->billing_cycle) }}</td>
                            <td>{{ number_format($assignment->usage_used) }}{{ $assignment->addon->usage_limit ? ' / '.number_format($assignment->addon->usage_limit).' '.$assignment->addon->usage_unit : '' }}</td>
                            <td><span class="font-semibold {{ $assignment->status === 'active' ? 'text-emerald-300' : 'text-slate-500' }}">{{ ucfirst($assignment->status) }}</span></td>
                            <td class="text-right">
                                @if($assignment->status === 'active')
                                    <div class="flex justify-end gap-3">
                                        <button wire:click="openUsage({{ $assignment->id }})" class="font-semibold text-slate-300">Log usage</button>
                                        <button wire:click="cancelAssignment({{ $assignment->id }})" wire:confirm="Cancel this add-on assignment?" class="font-semibold text-rose-300">Cancel</button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 text-center text-slate-600">No add-ons assigned yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if($viewMode === 'create')
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="cancelForm"></div>
            <div class="bc-panel relative max-h-[90vh] w-full max-w-lg overflow-y-auto p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">{{ $addonId ? 'Edit add-on' : 'Create add-on' }}</h2>
                <form wire:submit="save" class="mt-5 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="bc-label" for="ad-name">Name</label><input id="ad-name" wire:model.live="name" class="bc-field">@error('name')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div><label class="bc-label" for="ad-slug">Slug</label><input id="ad-slug" wire:model="slug" class="bc-field">@error('slug')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    </div>
                    <div><label class="bc-label" for="ad-category">Category</label><select id="ad-category" wire:model="category" class="bc-field">@foreach(\App\Livewire\AddOns::CATEGORIES as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                    <div><label class="bc-label" for="ad-desc">Description</label><textarea id="ad-desc" wire:model="description" class="bc-field" rows="2"></textarea></div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="bc-label" for="ad-price">Price</label><input id="ad-price" wire:model="price" type="number" step="0.01" min="0" class="bc-field">@error('price')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div><label class="bc-label" for="ad-cycle">Billing cycle</label><select id="ad-cycle" wire:model="billingCycle" class="bc-field"><option value="one_time">One-time</option><option value="monthly">Monthly</option><option value="yearly">Yearly</option></select></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="bc-label" for="ad-limit">Usage limit (optional)</label><input id="ad-limit" wire:model="usageLimit" type="number" min="1" class="bc-field" placeholder="Unlimited">@error('usageLimit')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div><label class="bc-label" for="ad-unit">Usage unit</label><input id="ad-unit" wire:model="usageUnit" class="bc-field" placeholder="MB, credits, seats"></div>
                    </div>
                    <div class="flex justify-end gap-3"><button type="button" wire:click="cancelForm" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Save add-on</button></div>
                </form>
            </div>
        </div>
    @endif

    @if($usageForAssignmentId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="closeModals"></div>
            <div class="bc-panel relative w-full max-w-sm p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">Log usage</h2>
                <form wire:submit="recordUsage" class="mt-5 space-y-4">
                    <div><label class="bc-label" for="usage-amount">Amount</label><input id="usage-amount" wire:model="usageAmount" type="number" min="1" class="bc-field">@error('usageAmount')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div class="flex justify-end gap-3"><button type="button" wire:click="closeModals" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Save</button></div>
                </form>
            </div>
        </div>
    @endif
</div>
