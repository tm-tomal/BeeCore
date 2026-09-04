<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment result | BeeCore</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 font-outfit antialiased dark:bg-gray-900">
        <div class="flex min-h-screen items-center justify-center px-4 py-10">
            <div class="w-full max-w-md text-center">
                <span class="mx-auto grid h-16 w-16 place-items-center rounded-full {{ $ok ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400' : 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400' }}">
                    @if($ok)
                        <svg class="size-8 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    @else
                        <svg class="size-8 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    @endif
                </span>
                <h1 class="mt-5 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $ok ? 'Payment successful' : 'Payment not completed' }}</h1>
                <p class="mt-2 text-theme-sm text-gray-500 dark:text-gray-400">{{ $message }}</p>

                @if($reference)
                    <p class="mt-4 text-theme-xs text-gray-400 dark:text-gray-500">Reference: {{ $reference }}</p>
                @endif
                @if($trxID)
                    <p class="mt-1 text-theme-xs text-gray-400 dark:text-gray-500">bKash Transaction ID: {{ $trxID }}</p>
                @endif

                @if(! empty($retryUrl))
                    <a href="{{ $retryUrl }}" class="mt-6 inline-flex items-center justify-center gap-2 rounded-lg bg-pink-500 px-6 py-3 text-theme-sm font-semibold text-white shadow-theme-xs transition hover:bg-pink-600">
                        Try again
                    </a>
                @elseif(! empty($redirectUrl))
                    <p id="redirect-hint" class="mt-6 text-theme-xs text-gray-400 dark:text-gray-500">
                        Redirecting in <span id="redirect-seconds">4</span> seconds…
                    </p>
                    <a href="{{ $redirectUrl }}" class="mt-6 inline-flex items-center justify-center gap-2 rounded-lg bg-pink-500 px-6 py-3 text-theme-sm font-semibold text-white shadow-theme-xs transition hover:bg-pink-600">
                        Continue
                    </a>
                @elseif($ok)
                    <p class="mt-6 text-theme-xs text-gray-400 dark:text-gray-500">You can close this window — the merchant has been notified.</p>
                @else
                    <a href="{{ url('/') }}" class="mt-6 inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-6 py-3 text-theme-sm font-semibold text-white shadow-theme-xs transition hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                        Return home
                    </a>
                @endif
            </div>
        </div>

        @if(! empty($redirectUrl))
            <script>
                const redirectUrl = @json($redirectUrl);
                const seconds = document.getElementById('redirect-seconds');
                let remaining = 4;
                const timer = setInterval(() => {
                    remaining -= 1;
                    if (seconds) seconds.textContent = remaining;
                    if (remaining <= 0) {
                        clearInterval(timer);
                        window.location.assign(redirectUrl);
                    }
                }, 1000);
            </script>
        @endif
    </body>
</html>
