@props(['attachments'])
@php
    $attachments = collect($attachments ?? [])->filter();
@endphp
@if($attachments->isNotEmpty())
    <div class="mt-4">
        <p class="text-theme-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ __('Attachments') }} · {{ $attachments->count() }}</p>
        <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3">
            @foreach($attachments as $attachment)
                <div class="group overflow-hidden rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                    @if($attachment->isImage())
                        <a href="{{ route('attachments.show', $attachment) }}" target="_blank" rel="noopener" class="block">
                            <img src="{{ route('attachments.show', $attachment) }}" loading="lazy" class="aspect-video w-full object-cover" alt="{{ $attachment->original_name }}">
                        </a>
                    @elseif($attachment->isVideo())
                        <video controls preload="metadata" playsinline class="aspect-video w-full bg-black" src="{{ route('attachments.show', $attachment) }}"></video>
                    @else
                        <div class="grid aspect-video w-full place-items-center bg-gray-100 dark:bg-white/[0.05]">
                            <svg class="size-8 stroke-gray-400" viewBox="0 0 24 24" fill="none" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        </div>
                    @endif
                    <div class="flex items-center gap-2 px-2.5 py-2">
                        <p class="min-w-0 flex-1 truncate text-theme-xs font-medium text-gray-700 dark:text-gray-300" title="{{ $attachment->original_name }}">{{ $attachment->original_name }}</p>
                        <span class="shrink-0 text-theme-xs text-gray-400 dark:text-gray-500">{{ $attachment->humanSize() }}</span>
                        <a href="{{ route('attachments.show', ['attachment' => $attachment, 'download' => 1]) }}" title="{{ __('Download') }}" class="grid size-6 shrink-0 place-items-center rounded-md text-gray-400 transition hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
