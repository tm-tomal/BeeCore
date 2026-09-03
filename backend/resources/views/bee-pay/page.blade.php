<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pay with bKash | BeeCore</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 font-outfit antialiased dark:bg-gray-900">
        <div class="flex min-h-screen items-center justify-center px-4 py-10">
            <div class="w-full max-w-md">
                <div class="text-center">
                    <span class="mx-auto grid h-12 w-12 place-items-center rounded-xl bg-brand-500 text-white">
                        <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a5 5 0 0 1 4.9 6.2A6 6 0 0 1 18 14a6 6 0 0 1-12 0 6 6 0 0 1 1.1-7.8A5 5 0 0 1 12 2z"/><path d="M12 9l-2 4 2 1.5L14 13l-2-4z"/></svg>
                    </span>
                    <h1 class="mt-4 text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ $intent->kind === \App\Models\BeePaymentIntent::KIND_INVOICE
                            ? ($intent->tenant?->name ?? 'BeeCore')
                            : 'BeeCore' }}
                    </h1>
                    <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                        {{ $intent->kind === \App\Models\BeePaymentIntent::KIND_INVOICE ? 'Pay your invoice securely' : 'Pay your BeeCore subscription' }}
                    </p>
                </div>

                <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                    @if($error)
                        <div class="mb-4 rounded-xl border border-error-200 bg-error-50 px-4 py-3 dark:border-error-500/20 dark:bg-error-500/10">
                            <p class="text-theme-sm text-error-700 dark:text-error-300">{{ $error }}</p>
                        </div>
                    @endif

                    @if($intent->status === \App\Models\BeePaymentIntent::STATUS_SUCCESS)
                        <div class="text-center">
                            <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                                <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <p class="mt-3 text-theme-sm font-medium text-success-600 dark:text-success-400">This payment was already completed.</p>
                            @if($intent->bkash_trx_id)
                                <p class="mt-1 text-theme-xs text-gray-400 dark:text-gray-500">bKash Transaction ID: {{ $intent->bkash_trx_id }}</p>
                            @endif
                        </div>
                    @elseif($intent->status === \App\Models\BeePaymentIntent::STATUS_PROCESSING)
                        <div class="text-center">
                            <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                                <svg class="size-6 animate-spin stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            </span>
                            <p class="mt-3 text-theme-sm font-medium text-warning-600 dark:text-warning-400">Payment in progress</p>
                            <p class="mt-1 text-theme-xs leading-5 text-gray-500 dark:text-gray-400">You were redirected to bKash to approve this payment. Complete it in the bKash window, then check the result below.</p>
                        </div>

                        <div class="mt-5 flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50/60 px-4 py-3.5 dark:border-gray-800 dark:bg-white/[0.02]">
                            <span class="text-theme-sm text-gray-500 dark:text-gray-400">Amount</span>
                            <span class="text-lg font-bold text-gray-800 dark:text-white/90">৳{{ number_format((float) $intent->amount, 2) }}</span>
                        </div>
                        <p class="mt-3 text-center text-theme-xs text-gray-500 dark:text-gray-400">Reference: {{ $intent->merchant_invoice_number }}</p>

                        <form method="POST" action="{{ route('bee-pay.check', ['intent' => $intent->token]) }}" class="mt-5">
                            @csrf
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-3 text-theme-sm font-semibold text-white shadow-theme-xs transition hover:bg-brand-600">
                                I already paid — check status
                            </button>
                        </form>
                        <form method="POST" action="{{ route('bee-pay.bkash', ['intent' => $intent->token]) }}" class="mt-3">
                            @csrf
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                Start a new bKash attempt
                            </button>
                        </form>
                        <p class="mt-3 text-center text-theme-xs text-gray-400 dark:text-gray-500">Only start a new attempt if you did <strong>not</strong> finish the payment in the bKash window.</p>
                    @elseif($intent->status === \App\Models\BeePaymentIntent::STATUS_FAILED)
                        <div class="text-center">
                            <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                                <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            </span>
                            <p class="mt-3 text-theme-sm font-medium text-error-600 dark:text-error-400">Your previous attempt was not completed.</p>
                        </div>
                        <p class="mt-4 text-theme-xs text-gray-500 dark:text-gray-400">No money was taken. You can safely pay again below.</p>
                    @endif

                    @if(! in_array($intent->status, [\App\Models\BeePaymentIntent::STATUS_SUCCESS, \App\Models\BeePaymentIntent::STATUS_PROCESSING], true))
                        <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50/60 px-4 py-3.5 dark:border-gray-800 dark:bg-white/[0.02]">
                            <span class="text-theme-sm text-gray-500 dark:text-gray-400">Amount due</span>
                            <span class="text-lg font-bold text-gray-800 dark:text-white/90">৳{{ number_format((float) $intent->amount, 2) }}</span>
                        </div>
                        <p class="mt-3 text-theme-xs text-gray-500 dark:text-gray-400">Reference: {{ $intent->merchant_invoice_number }}</p>
                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">A {{ $fee }}% BeeCore processing fee applies.</p>

                        <form method="POST" action="{{ route('bee-pay.bkash', ['intent' => $intent->token]) }}" class="mt-5">
                            @csrf
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-pink-500 px-4 py-3 text-theme-sm font-semibold text-white shadow-theme-xs transition hover:bg-pink-600">
                                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                Pay with bKash
                            </button>
                        </form>
                        <p class="mt-3 text-center text-theme-xs text-gray-400 dark:text-gray-500">You will be redirected to bKash to complete the payment securely.</p>
                    @endif
                </div>

                <p class="mt-4 text-center text-theme-xs text-gray-400 dark:text-gray-500">Powered by <span class="font-semibold text-gray-500 dark:text-gray-400">BeeCore</span></p>
            </div>
        </div>
    </body>
</html>
