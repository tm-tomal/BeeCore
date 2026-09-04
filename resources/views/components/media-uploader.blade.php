@php
    // Reusable image/video uploader. Requires a Livewire component with a
    // public "files" property (WithFileUploads). Uploading starts the moment a
    // file is picked — Livewire streams it to the private disk while the user
    // is still typing, so a 1 GB clip never blocks "send".
    $fileMeta = function ($file): array {
        $bytes = method_exists($file, 'getSize') ? (float) $file->getSize() : 0;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) { $bytes /= 1024; $i++; }
        return [
            'name' => method_exists($file, 'getClientOriginalName') ? (string) $file->getClientOriginalName() : 'file',
            'size' => round($bytes, $i > 1 ? 1 : 0).' '.$units[$i],
            'video' => str_starts_with((string) (method_exists($file, 'getMimeType') ? $file->getMimeType() : ''), 'video/'),
        ];
    };
@endphp
<div class="space-y-3"
    x-data="{ uploading: false, progress: 0 }"
    @window.livewire-upload-start="uploading = true; progress = 0"
    @window.livewire-upload-progress="progress = Math.round($event.detail.progress || 0)"
    @window.livewire-upload-finish="uploading = false"
    @window.livewire-upload-error="uploading = false">

    <div class="flex flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50/60 px-4 py-6 text-center transition hover:border-brand-400 hover:bg-brand-50/40 dark:border-gray-700 dark:bg-white/[0.02] dark:hover:border-brand-500/50 dark:hover:bg-brand-500/5">
        <svg class="size-6 stroke-gray-400 dark:stroke-gray-500" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        <label for="media-input" class="cursor-pointer text-theme-sm font-medium text-gray-700 hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-400">
            <span class="text-brand-600 dark:text-brand-400">{{ __('Add photos / videos') }}</span>
            <span class="mt-0.5 block text-theme-xs font-normal text-gray-400 dark:text-gray-500">{{ __('Images and videos up to 1 GB — upload starts instantly.') }}</span>
        </label>
        <input id="media-input" type="file" multiple accept="image/*,video/*" wire:model.live="files" class="hidden">
        <p class="text-theme-xs text-gray-400 dark:text-gray-500">{{ __('Up to 6 files · max 1 GB each') }}</p>
    </div>

    <div x-show="uploading" x-transition.opacity style="display: none;" class="rounded-lg border border-brand-100 bg-brand-50/50 px-3 py-2 dark:border-brand-500/20 dark:bg-brand-500/10">
        <div class="flex items-center justify-between gap-3 text-theme-xs">
            <span class="text-gray-500 dark:text-gray-400">{{ __('Uploading…') }}</span>
            <span class="font-semibold text-brand-600 dark:text-brand-400" x-text="progress + '%'">0%</span>
        </div>
        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/[0.08]">
            <div class="h-full rounded-full bg-brand-500 transition-all duration-200" :style="'width: ' + progress + '%'"></div>
        </div>
    </div>

    @if(! empty($files))
        <ul class="grid grid-cols-2 gap-2 sm:grid-cols-3">
            @foreach($files as $index => $file)
                @php $meta = $fileMeta($file); @endphp
                <li class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-2.5 py-2 dark:border-gray-800 dark:bg-white/[0.03]">
                    @if($meta['video'])
                        <svg class="size-7 shrink-0 stroke-violet-500" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                    @else
                        <svg class="size-7 shrink-0 stroke-emerald-500" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-theme-xs font-medium text-gray-800 dark:text-white/90" title="{{ $meta['name'] }}">{{ $meta['name'] }}</p>
                        <p class="text-theme-xs text-gray-400 dark:text-gray-500">{{ $meta['size'] }} · {{ __('ready') }}</p>
                    </div>
                    <button type="button" wire:click="removeFile({{ $index }})" title="{{ __('Remove') }}" class="grid size-6 shrink-0 place-items-center rounded-md text-gray-400 transition hover:bg-error-50 hover:text-error-600 dark:hover:bg-error-500/10">
                        <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </li>
            @endforeach
        </ul>
    @endif

    @php
        $mediaErrors = collect($errors->getMessages())
            ->filter(fn ($messages, $key) => str_starts_with((string) $key, 'files'))
            ->flatten()
            ->all();
    @endphp
    @if(! empty($mediaErrors))
        @foreach(array_unique($mediaErrors) as $mediaError)
            <p class="rounded-lg border border-error-200 bg-error-50 px-3 py-2 text-theme-xs text-error-700 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">{{ $mediaError }}</p>
        @endforeach
    @endif
</div>
