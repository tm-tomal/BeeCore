@props(['wireKey' => null, 'options' => [], 'value' => '', 'placeholder' => __('Select an option'), 'searchable' => true, 'live' => false, 'id' => null])

@php
    $rawOptions = collect($options);
    $btnId = $id ?: ($wireKey ?? null);
    $prebuilt = $rawOptions->isNotEmpty() && $rawOptions->every(fn ($option) => is_array($option) && array_key_exists('value', $option) && array_key_exists('label', $option));
    $mappedOptions = $prebuilt
        ? $rawOptions->map(fn ($option) => ['value' => (string) $option['value'], 'label' => (string) $option['label']])->values()->all()
        : $rawOptions->map(fn ($label, $value) => ['value' => (string) $value, 'label' => (string) $label])->values()->all();
@endphp

<div
    data-bee-search-select
    x-data="{
        open: false,
        search: '',
        selected: @js((string) $value),
        options: @js($mappedOptions),
        placeholder: @js((string) $placeholder),
        searchable: @js((bool) $searchable),
        labelFor(v) {
            const match = this.options.find((o) => o.value === String(v));
            return match ? match.label : null;
        },
        filtered() {
            const q = this.search.toLowerCase().trim();
            if (!q) return this.options;
            return this.options.filter((o) => o.label.toLowerCase().includes(q) || o.value.toLowerCase().includes(q));
        },
        choose(option) {
            this.selected = option.value;
            this.search = '';
            this.open = false;
            if (this.$refs.native) {
                this.$refs.native.value = option.value;
                this.$refs.native.dispatchEvent(new Event('change', { bubbles: true }));
            }
        },
    }"
    class="relative"
    @click.outside="open = false"
    x-cloak
>
    <!-- Trigger -->
    <button
        type="button"
        @if($btnId) id="{{ $btnId }}" @endif
        @click="open = !open"
        class="flex h-11 w-full items-center justify-between gap-2 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-left text-theme-sm text-gray-800 shadow-theme-xs transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
    >
        <span class="truncate" :class="labelFor(selected) ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-white/30'" x-text="labelFor(selected) ?? placeholder"></span>
        <svg class="size-4 shrink-0 stroke-current text-gray-400" :class="open && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
    </button>

    <!-- Dropdown -->
    <div
        x-show="open"
        x-transition
        class="absolute left-0 right-0 z-30 mt-1.5 max-h-72 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-900"
    >
        <div x-show="searchable" class="border-b border-gray-100 p-2 dark:border-gray-800">
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </span>
                <input
                    type="search"
                    x-model="search"
                    placeholder="{{ __('Search...') }}"
                    class="h-9 w-full rounded-lg border border-gray-200 bg-transparent py-2 pl-9 pr-3 text-theme-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                >
            </div>
        </div>
        <ul class="custom-scrollbar max-h-56 overflow-y-auto p-1.5">
            <template x-for="option in filtered()" :key="option.value">
                <li>
                    <button
                        type="button"
                        @click="choose(option)"
                        class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-left text-theme-sm transition"
                        :class="selected === option.value ? 'bg-brand-50 font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5'"
                    >
                        <span x-text="option.label"></span>
                        <svg x-show="selected === option.value" class="size-4 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </button>
                </li>
            </template>
            <li x-show="filtered().length === 0">
                <p class="px-3 py-6 text-center text-theme-sm text-gray-500 dark:text-gray-400">{{ __('No matching options.') }}</p>
            </li>
        </ul>
    </div>

    @if($wireKey)
        <select @if($live) wire:model.live="{{ $wireKey }}" @else wire:model="{{ $wireKey }}" @endif x-ref="native" class="sr-only" tabindex="-1" aria-hidden="true">
            @foreach($mappedOptions as $option)
                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </select>
    @endif
</div>
