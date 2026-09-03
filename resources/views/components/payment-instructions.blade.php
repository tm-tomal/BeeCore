@props(['tenant' => null, 'title' => __('Payment instructions')])

@php
    $config = $tenant?->settings['collection'] ?? [];
    $mode = $config['mode'] ?? 'bee';
    $fee = \App\Models\SystemSetting::beeFeePercent();
    $methods = $config['methods'] ?? [];
@endphp

<div class="rounded-xl border border-gray-200 bg-gray-50/60 px-4 py-3.5 dark:border-gray-800 dark:bg-white/[0.02]">
    <p class="text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $title }}</p>

    @if($mode === 'bee')
        <div class="mt-2 flex items-start gap-2.5">
            <span class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-md bg-brand-500/15 text-brand-600 dark:text-brand-400">
                <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a5 5 0 0 1 4.9 6.2A6 6 0 0 1 18 14a6 6 0 0 1-12 0 6 6 0 0 1 1.1-7.8A5 5 0 0 1 12 2z"/></svg>
            </span>
            <p class="text-theme-xs leading-5 text-gray-600 dark:text-gray-400">
                {{ __('Pay securely through the Bee Payment Gateway. A :fee% processing fee applies.', ['fee' => $fee]) }}
            </p>
        </div>
    @else
        <div class="mt-2 space-y-2">
            @php
                $hasMethod = collect($methods)->contains(fn ($m) => ($m['enabled'] ?? false) && ($m['number'] ?? $m['details'] ?? null));
            @endphp
            @if(! $hasMethod)
                <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Pay directly to this ISP. Contact them for account details.') }}</p>
            @endif
            @if(($methods['bkash']['enabled'] ?? false) && ($methods['bkash']['number'] ?? null))
                <div class="flex items-center justify-between gap-3 text-theme-sm">
                    <span class="font-medium text-gray-700 dark:text-gray-300">bKash</span>
                    <span class="font-semibold text-gray-800 dark:text-white/90">{{ $methods['bkash']['number'] }}</span>
                </div>
            @endif
            @if(($methods['nagad']['enabled'] ?? false) && ($methods['nagad']['number'] ?? null))
                <div class="flex items-center justify-between gap-3 text-theme-sm">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Nagad</span>
                    <span class="font-semibold text-gray-800 dark:text-white/90">{{ $methods['nagad']['number'] }}</span>
                </div>
            @endif
            @if(($methods['bank']['enabled'] ?? false) && ($methods['bank']['details'] ?? null))
                <p class="text-theme-xs leading-5 text-gray-600 dark:text-gray-400">{{ $methods['bank']['details'] }}</p>
            @endif
        </div>
    @endif
</div>
