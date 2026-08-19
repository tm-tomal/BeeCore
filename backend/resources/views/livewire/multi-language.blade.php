<div>
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Multi-language</h1>
            <p class="mt-2 text-sm text-slate-500">Manage supported languages, the platform default, and translation keys.</p>
        </div>
        @if($tab === 'languages')<button wire:click="create" class="bc-primary">Add language</button>@endif
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <div class="mb-5 flex gap-2">
        <button wire:click="$set('tab', 'languages')" class="px-4 py-2 text-sm font-bold {{ $tab === 'languages' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Languages</button>
        <button wire:click="$set('tab', 'translations')" class="px-4 py-2 text-sm font-bold {{ $tab === 'translations' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Translations</button>
    </div>

    @if($tab === 'languages')
        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Language</th><th>Code</th><th>Default</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @foreach($languages as $language)
                        <tr>
                            <td class="font-bold text-white">{{ $language->name }}@if($language->native_name)<span class="ml-2 text-xs text-slate-500">{{ $language->native_name }}</span>@endif</td>
                            <td><code class="text-teal-300">{{ $language->code }}</code></td>
                            <td>{{ $language->is_default ? 'Yes' : '—' }}</td>
                            <td><span class="font-semibold {{ $language->is_active ? 'text-emerald-300' : 'text-slate-500' }}">{{ $language->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-right">
                                <div class="flex flex-wrap justify-end gap-3">
                                    @if(!$language->is_default)<button wire:click="setDefault({{ $language->id }})" class="font-semibold text-slate-300">Make default</button>@endif
                                    <button wire:click="toggleActive({{ $language->id }})" class="font-semibold {{ $language->is_active ? 'text-amber-300' : 'text-emerald-300' }}">{{ $language->is_active ? 'Deactivate' : 'Activate' }}</button>
                                    <button wire:click="edit({{ $language->id }})" class="font-semibold text-teal-300">Edit</button>
                                    @if(!$language->is_default)<button wire:click="delete({{ $language->id }})" wire:confirm="Remove this language and its translations?" class="font-semibold text-rose-300">Delete</button>@endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="mb-5 border border-white/10 bg-white/[0.02] p-4" style="border-radius:6px">
            <form wire:submit="addKey" class="flex gap-3">
                <input wire:model="newKey" class="bc-field" placeholder="translation.key">
                <button type="submit" class="bc-primary">Add key</button>
            </form>
            @error('newKey')<p class="mt-2 text-xs text-rose-300">{{ $message }}</p>@enderror
        </div>

        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Key</th>@foreach($activeLanguages as $lang)<th>{{ $lang->name }}</th>@endforeach<th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($keys as $key)
                        <tr>
                            <td><code class="text-teal-300">{{ $key }}</code></td>
                            @foreach($activeLanguages as $lang)
                                @php $translation = $translationGrid->get($key, collect())->firstWhere('locale', $lang->code); @endphp
                                <td><input type="text" value="{{ $translation?->value }}" wire:change="updateValue('{{ $key }}', '{{ $lang->code }}', $event.target.value)" class="bc-field" placeholder="—"></td>
                            @endforeach
                            <td class="text-right"><button wire:click="deleteKey('{{ $key }}')" wire:confirm="Delete this translation key?" class="font-semibold text-rose-300">Delete</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $activeLanguages->count() + 2 }}" class="py-12 text-center text-slate-600">No translation keys yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if($viewMode === 'create')
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="cancelForm"></div>
            <div class="bc-panel relative w-full max-w-md p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">{{ $languageId ? 'Edit language' : 'Add language' }}</h2>
                <form wire:submit="save" class="mt-5 space-y-4">
                    <div><label class="bc-label" for="lang-code">Code</label><input id="lang-code" wire:model="code" class="bc-field" placeholder="en, bn, hi">@error('code')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="lang-name">Name</label><input id="lang-name" wire:model="name" class="bc-field">@error('name')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="lang-native">Native name (optional)</label><input id="lang-native" wire:model="nativeName" class="bc-field"></div>
                    <div class="flex justify-end gap-3"><button type="button" wire:click="cancelForm" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Save language</button></div>
                </form>
            </div>
        </div>
    @endif
</div>
