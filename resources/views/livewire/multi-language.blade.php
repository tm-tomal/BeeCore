<div class="space-y-6">
    @if($viewMode === 'create')
        <!-- Page header -->
        <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">Multi-language</p>
                <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $languageId ? 'Edit language' : 'Add language' }}</h1>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $languageId ? 'Update language details and native name.' : 'Register a new language for the platform.' }}</p>
            </div>
            <div class="flex shrink-0 items-center gap-3">
                <button wire:click="cancelForm" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to Languages
                </button>
            </div>
        </header>

        <form wire:submit="save" class="space-y-6">
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Language details</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">ISO-style code and display names.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400" for="lang-code">Code</label>
                        <input id="lang-code" wire:model.live="code" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="en, bn, hi">
                        <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">Used as the translation locale.</p>
                        @error('code')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400" for="lang-name">Name</label>
                        <input id="lang-name" wire:model="name" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="English">
                        @error('name')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400" for="lang-native">Native name</label>
                        <input id="lang-native" wire:model="nativeName" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="বাংলা">
                        @error('nativeName')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <div class="sticky bottom-4 flex flex-col-reverse gap-3 rounded-2xl border border-gray-200 bg-white/95 px-5 py-4 shadow-theme-lg backdrop-blur sm:flex-row sm:items-center sm:justify-end dark:border-gray-800 dark:bg-gray-900/95">
                <button type="button" wire:click="cancelForm" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">Cancel</button>
                <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                    <span wire:loading.remove wire:target="save">{{ $languageId ? 'Save changes' : 'Save language' }}</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </form>
    @else
        <!-- Page header -->
        <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
                <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Multi-language</h1>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Manage supported languages, the platform default, and translation keys.</p>
            </div>
            @if($tab === 'languages')
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <button wire:click="create" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                        Add language
                    </button>
                </div>
            @endif
        </header>

        @if(session()->has('message'))
            <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-theme-sm font-medium text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-400">
                <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                <span>{{ session('message') }}</span>
            </div>
        @endif

        <!-- Tabs -->
        <div class="inline-flex flex-wrap items-center gap-1 rounded-xl bg-gray-100 p-1 dark:bg-gray-800">
            <button wire:click="$set('tab', 'languages')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'languages' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-white/[0.03] dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">Languages</button>
            <button wire:click="$set('tab', 'translations')" class="rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'translations' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-white/[0.03] dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">Translations</button>
        </div>

        @if($tab === 'languages')
            <x-table heading="All languages" :description="'Showing '.number_format($languages->count()).' language'.($languages->count() === 1 ? '' : 's')">
                <x-slot:toolbar>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 dark:text-gray-500">
                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search languages..." class="h-10 w-56 rounded-lg border border-gray-300 bg-transparent py-2 pl-10 pr-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    </div>
                </x-slot:toolbar>

                <table class="min-w-full">
                    <thead>
                        <tr class="border-y border-gray-100 bg-gray-50/60 dark:border-gray-800">
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Language</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Code</th>
                            <th scope="col" class="px-5 py-3.5 text-center text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Default</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                            <th scope="col" class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($languages as $language)
                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 align-middle text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $language->name }}@if($language->native_name)<span class="ml-2 text-theme-xs font-normal text-gray-500 dark:text-gray-400">{{ $language->native_name }}</span>@endif</td>
                                <td class="px-5 py-4 align-middle"><code class="font-mono text-theme-sm font-medium text-brand-600 dark:text-brand-400">{{ $language->code }}</code></td>
                                <td class="px-5 py-4 align-middle text-center">
                                    @if($language->is_default)
                                        <span title="Default language" class="inline-grid h-8 w-8 place-items-center rounded-lg bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                                            <svg class="size-4 fill-current" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        </span>
                                    @else
                                        <button type="button" wire:click="setDefault({{ $language->id }})" title="Make default" @disabled(!$language->is_active) class="inline-grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-300 transition hover:border-warning-300 hover:bg-warning-50 hover:text-warning-500 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-800 dark:text-gray-600 dark:hover:border-warning-500/40 dark:hover:bg-warning-500/10 dark:hover:text-warning-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        </button>
                                    @endif
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    <button
                                        type="button"
                                        role="switch"
                                        aria-label="{{ $language->is_active ? 'Active - click to deactivate' : 'Inactive - click to activate' }}"
                                        aria-checked="{{ $language->is_active ? 'true' : 'false' }}"
                                        wire:click="toggleActive({{ $language->id }})"
                                        title="{{ $language->is_default && $language->is_active ? 'The default language cannot be deactivated' : ($language->is_active ? 'Click to deactivate' : 'Click to activate') }}"
                                        @disabled($language->is_default && $language->is_active)
                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full transition-colors duration-200 focus:outline-hidden focus:ring-3 focus:ring-brand-500/20 disabled:cursor-not-allowed disabled:opacity-40 {{ $language->is_active ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700' }}"
                                    >
                                        <span class="inline-block size-4 transform rounded-full bg-white shadow-theme-xs transition-transform duration-200 {{ $language->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" wire:click="edit({{ $language->id }})" title="Edit language" class="grid h-8 w-8 place-items-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                        @if(!$language->is_default)
                                            <button
                                                type="button"
                                                title="Delete language"
                                                @click="$dispatch('confirm-action', {
                                                    title: 'Delete language',
                                                    message: 'Remove {{ $language->name }} and all of its translations?',
                                                    confirmText: 'Delete',
                                                    wireMethod: 'delete',
                                                    wireParams: [{{ $language->id }}],
                                                })"
                                                class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 hover:text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400 dark:hover:border-error-500/40 dark:hover:bg-error-500/15 dark:hover:text-error-300"
                                            >
                                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-4 align-middle">
                                    <div class="py-10 text-center">
                                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">{{ $search ? 'No languages match your search.' : 'No languages configured yet.' }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-table>
        @else
            <!-- Add translation key -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Add translation key</h2>
                <form wire:submit="addKey" class="flex flex-col gap-3 sm:flex-row sm:items-start">
                    <div class="w-full">
                        <input wire:model="newKey" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="translation.key">
                        @error('newKey')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Add key</button>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="w-full overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-y border-gray-100 bg-gray-50/60 dark:border-gray-800">
                                <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Key</th>
                                @foreach($activeLanguages as $lang)
                                    <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $lang->name }}</th>
                                @endforeach
                                <th scope="col" class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($keys as $key)
                                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-4 align-middle"><code class="font-mono text-theme-sm font-medium text-brand-600 dark:text-brand-400">{{ $key }}</code></td>
                                    @foreach($activeLanguages as $lang)
                                        @php $translation = $translationGrid->get($key, collect())->firstWhere('locale', $lang->code); @endphp
                                        <td class="px-5 py-4 align-middle">
                                            <input type="text" value="{{ $translation?->value }}" wire:change="updateValue('{{ $key }}', '{{ $lang->code }}', $event.target.value)" class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-1.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="—">
                                        </td>
                                    @endforeach
                                    <td class="px-5 py-4 align-middle">
                                        <div class="flex items-center justify-end">
                                            <button
                                                type="button"
                                                title="Delete translation key"
                                                @click="$dispatch('confirm-action', {
                                                    title: 'Delete translation key',
                                                    message: 'Delete this translation key and all its translations?',
                                                    confirmText: 'Delete',
                                                    wireMethod: 'deleteKey',
                                                    wireParams: ['{{ $key }}'],
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
                                    <td colspan="{{ $activeLanguages->count() + 2 }}" class="px-5 py-4 align-middle">
                                        <div class="py-10 text-center">
                                            <p class="text-theme-sm text-gray-500 dark:text-gray-400">No translation keys yet.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</div>
