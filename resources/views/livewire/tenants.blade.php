<div class="space-y-6">
    @if($viewMode === 'index')
        <!-- Page header -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
                <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Tenant portfolio</h1>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Provision and enter ISP workspaces.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button wire:click="create" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Add Tenant
                </button>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-theme-sm font-medium text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-400">
                {{ session('message') }}
            </div>
        @endif

        <!-- Tenants table -->
        <x-table heading="All tenants" :description="'Showing '.number_format($tenants->total()).' tenant'.($tenants->total() === 1 ? '' : 's')" :paginator="$tenants">
            <x-slot:toolbar>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 dark:text-gray-500">
                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search tenants..." class="h-10 w-56 rounded-lg border border-gray-300 bg-transparent py-2 pl-10 pr-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                </div>
            </x-slot:toolbar>

            <table class="min-w-full">
                <thead class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Name / Slug</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Currency</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Timezone</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($tenants as $tenant)
                        <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4">
                                <div class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $tenant->name }}</div>
                                <div class="mt-1 flex flex-wrap items-center gap-1.5 text-theme-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ $tenant->slug }}</span>
                                    <span class="rounded-full px-2 py-0.5 font-medium {{ $tenant->operation_mode === 'automatic' ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.05] dark:text-gray-400' }}">{{ $tenant->operation_mode === 'automatic' ? 'Automatic' : 'Manual' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <button
                                    type="button"
                                    role="switch"
                                    aria-label="{{ $tenant->status === 'active' ? 'Active - click to suspend' : 'Suspended - click to activate' }}"
                                    aria-checked="{{ $tenant->status === 'active' ? 'true' : 'false' }}"
                                    wire:click="toggleStatus({{ $tenant->id }})"
                                    title="{{ $tenant->status === 'active' ? 'Active - click to suspend' : 'Suspended - click to activate' }}"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full transition-colors duration-200 focus:outline-hidden focus:ring-3 focus:ring-brand-500/20 {{ $tenant->status === 'active' ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700' }}"
                                >
                                    <span class="inline-block size-4 transform rounded-full bg-white shadow-theme-xs transition-transform duration-200 {{ $tenant->status === 'active' ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-md bg-gray-100 px-2 py-1 text-theme-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $tenant->currency }}</span>
                            </td>
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $tenant->timezone }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('tenant-details', $tenant) }}" title="View details" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <button type="button" wire:click="impersonate({{ $tenant->id }})" title="Login as this tenant" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400" @disabled($tenant->status !== 'active')>
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                                    </button>
                                    <button type="button" wire:click="edit({{ $tenant->id }})" title="Edit" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button
                                        type="button"
                                        title="Archive tenant"
                                        @click="$dispatch('confirm-action', {
                                            title: 'Archive tenant',
                                            message: 'Archive this tenant and disable workspace access? This can only be restored by the SaaS owner.',
                                            confirmText: 'Archive',
                                            wireMethod: 'delete',
                                            wireParams: [{{ $tenant->id }}],
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
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">{{ $search ? 'No tenants match your search.' : 'No tenants found yet.' }}</p>
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
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">Tenants</p>
                <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $isEditing ? 'Edit ISP' : 'Create new ISP' }}</h1>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $isEditing ? 'Update the ISP workspace and its owner account.' : 'Register a new ISP workspace and its owner login.' }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button wire:click="cancel" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to List
                </button>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            <!-- Basic information -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Basic information</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Display name, operation model and workspace defaults.</p>
                </div>

                <!-- Operation model -->
                <div class="mb-4 grid gap-4 rounded-xl border border-gray-100 bg-gray-50/60 p-4 sm:grid-cols-2 dark:border-gray-800 dark:bg-white/[0.02]">
                    <div>
                        <label for="operationMode" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Operation model</label>
                        <x-search-select wireKey="operationMode" :options="['automatic' => 'Automatic', 'manual' => 'Manual']" :value="$operationMode" placeholder="Select operation model" :searchable="false" />
                        @error('operationMode') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-start gap-3 rounded-lg border px-3.5 py-3 {{ $operationMode === 'automatic' ? 'border-brand-100 bg-brand-50/70 dark:border-brand-500/20 dark:bg-brand-500/10' : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900' }}">
                        <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-lg {{ $operationMode === 'automatic' ? 'bg-brand-100 text-brand-600 dark:bg-brand-500/20 dark:text-brand-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                            @if($operationMode === 'automatic')
                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                            @else
                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l-4 4v3h3l4-4"/><path d="M5 15l-2 6 6-2"/><path d="M21 11V7a2 2 0 0 0-2-2h-4l-4-2H5a2 2 0 0 0-2 2v4"/><path d="M15 21l2-6-6 2"/><path d="M13 13l6-6"/><path d="M19 5l2 2-2 2-2-2z"/></svg>
                            @endif
                        </span>
                        <div class="min-w-0">
                            <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $operationMode === 'automatic' ? 'Automatic operations' : 'Manual operations' }}</p>
                            <p class="mt-0.5 text-theme-xs leading-4 text-gray-500 dark:text-gray-400">
                                {{ $operationMode === 'automatic'
                                    ? 'Unlocks network automation — OLT, MikroTik, RADIUS integrations and advanced network tooling.'
                                    : 'Runs on manual billing — no network automation. Billing, customers, payments and standard features stay available.' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label for="name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">ISP Name<span class="ml-0.5 text-error-500">*</span></label>
                        <input type="text" wire:model.live="name" id="name" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="Acme Network">
                        @error('name') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="slug" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Slug <span class="font-normal text-gray-400">(optional)</span></label>
                        <input type="text" wire:model="slug" id="slug" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="Auto from name">
                        <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">Auto-generated from the ISP name when left empty.</p>
                        @error('slug') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="status" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Status <span class="font-normal text-gray-400">(optional)</span></label>
                        <x-search-select wireKey="status" :options="['active' => 'Active', 'suspended' => 'Suspended']" :value="$status" placeholder="Select status" :searchable="false" />
                        @error('status') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="currency" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Currency <span class="font-normal text-gray-400">(optional)</span></label>
                        <input type="text" wire:model="currency" id="currency" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="BDT">
                        @error('currency') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="timezone" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Timezone <span class="font-normal text-gray-400">(optional)</span></label>
                        <input type="text" wire:model="timezone" id="timezone" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="Asia/Dhaka">
                        @error('timezone') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="language" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Language <span class="font-normal text-gray-400">(optional)</span></label>
                        <x-search-select wireKey="language" :options="$languages->pluck('name', 'code')" :value="$language" placeholder="Select language" />
                        @error('language') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <!-- Company information -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Company information</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">All fields in this section are optional.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="companyLegalName" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Company Legal Name</label>
                        <input type="text" wire:model="companyLegalName" id="companyLegalName" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="Acme Network Ltd.">
                        @error('companyLegalName') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="businessType" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Business Type</label>
                        <input type="text" wire:model="businessType" id="businessType" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="e.g. Limited company, Partnership">
                        @error('businessType') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <!-- Owner account (required) -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4 flex items-start gap-3">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                        <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">ISP owner account</h2>
                        <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Owner Full Name, Email and Phone are required. The owner signs in to this workspace with this account.</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label for="ownerName" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Owner Full Name<span class="ml-0.5 text-error-500">*</span></label>
                        <input type="text" wire:model="ownerName" id="ownerName" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="Rahim Uddin">
                        @error('ownerName') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="ownerEmail" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Email<span class="ml-0.5 text-error-500">*</span></label>
                        <input type="email" wire:model="ownerEmail" id="ownerEmail" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="rahim@acme.net">
                        @error('ownerEmail') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="ownerPhone" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Phone<span class="ml-0.5 text-error-500">*</span></label>
                        <input type="text" wire:model="ownerPhone" id="ownerPhone" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="+8801XXXXXXXXX">
                        @error('ownerPhone') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <div class="mb-1.5 flex items-center justify-between gap-2">
                            <label for="password" class="block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Password</label>
                            <button type="button" wire:click="generatePassword" class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-theme-xs font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">
                                <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                                Auto generate
                            </button>
                        </div>
                        <input type="password" wire:model="password" id="password" autocomplete="new-password" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ $isEditing ? 'Leave blank to keep current' : 'Auto-generated if left blank' }}">
                        @error('password') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="passwordConfirmation" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Confirm Password</label>
                        <input type="password" wire:model="passwordConfirmation" id="passwordConfirmation" autocomplete="new-password" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="Re-enter password">
                        @error('passwordConfirmation') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <p class="mt-3 text-theme-xs text-gray-500 dark:text-gray-400">{{ $isEditing ? 'Leave the password fields empty to keep the existing password.' : 'Leave the password fields empty and a secure password will be auto-generated and shown once after saving.' }}</p>
            </section>

            <!-- Contact information -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Contact information</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">All fields in this section are optional.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="contactPhone" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Contact Phone</label>
                        <input type="text" wire:model="contactPhone" id="contactPhone" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="+8801XXXXXXXXX">
                        @error('contactPhone') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="contactAddress" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Contact Address</label>
                        <textarea wire:model="contactAddress" id="contactAddress" rows="2" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="House, road, area, district"></textarea>
                        @error('contactAddress') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <!-- Domain setup -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Domain setup</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">All fields in this section are optional.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="subdomain" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Subdomain</label>
                        <input type="text" wire:model="subdomain" id="subdomain" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="acme">
                        <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">e.g. <code class="rounded bg-gray-100 px-1 py-0.5 dark:bg-gray-800">acme.beecore.app</code></p>
                        @error('subdomain') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="customDomain" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Custom Domain</label>
                        <input type="text" wire:model="customDomain" id="customDomain" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="portal.acme.net">
                        <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">Only set when the tenant uses its own domain.</p>
                        @error('customDomain') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <!-- Actions -->
            <div class="sticky bottom-4 flex flex-col-reverse gap-3 rounded-2xl border border-gray-200 bg-white/95 px-5 py-4 shadow-theme-lg backdrop-blur sm:flex-row sm:items-center sm:justify-between dark:border-gray-800 dark:bg-gray-900/95">
                <p class="text-theme-xs text-gray-500 dark:text-gray-400">Fields marked with <span class="text-error-500">*</span> are required.</p>
                <div class="flex flex-col-reverse gap-3 sm:flex-row">
                    <button type="button" wire:click="cancel" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Save changes' : 'Create ISP' }}</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>
