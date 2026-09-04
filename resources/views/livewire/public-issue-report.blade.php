<div>
    <form wire:submit="save" class="space-y-4">
        <div>
            <label for="rep-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Your name') }}<span class="ml-0.5 text-error-500">*</span></label>
            <input id="rep-name" wire:model="name" type="text" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="{{ __('e.g. Rahim Uddin') }}">
            @error('name') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="rep-phone" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Phone number') }}<span class="ml-0.5 text-error-500">*</span></label>
            <input id="rep-phone" wire:model="phone" type="text" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="01XXXXXXXXX">
            @error('phone') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="rep-category" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('What kind of problem?') }}</label>
            <select id="rep-category" wire:model="category" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="connection">{{ __('No internet / slow connection') }}</option>
                <option value="network">{{ __('Network problem') }}</option>
                <option value="service">{{ __('Service / account') }}</option>
                <option value="billing">{{ __('Billing / payment') }}</option>
                <option value="other">{{ __('Other') }}</option>
            </select>
            @error('category') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="rep-subject" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Short description') }}<span class="ml-0.5 text-error-500">*</span></label>
            <input id="rep-subject" wire:model="subject" type="text" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="{{ __('e.g. No internet since this morning') }}">
            @error('subject') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="rep-details" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('More details') }}</label>
            <textarea id="rep-details" wire:model="description" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="{{ __('Optional — e.g. which area, since when, error messages.') }}"></textarea>
            @error('description') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <p class="mb-1.5 text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Photos / videos of the problem') }}</p>
            <x-media-uploader />
        </div>

        <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-3 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7z"/></svg>
            <span wire:loading.remove wire:target="save">{{ __('Send report') }}</span>
            <span wire:loading wire:target="save">{{ __('Sending…') }}</span>
        </button>
    </form>
</div>
