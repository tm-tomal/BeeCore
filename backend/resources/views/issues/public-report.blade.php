<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ __('Report a problem') }} | {{ $tenant->name }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 font-outfit antialiased dark:bg-gray-900">
        <div class="flex min-h-screen items-center justify-center px-4 py-10">
            <div class="w-full max-w-lg">
                <!-- Brand -->
                <div class="text-center">
                    <span class="mx-auto grid h-12 w-12 place-items-center rounded-xl bg-brand-500 text-white">
                        <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a5 5 0 0 1 4.9 6.2A6 6 0 0 1 18 14a6 6 0 0 1-12 0 6 6 0 0 1 1.1-7.8A5 5 0 0 1 12 2z"/><path d="M12 9l-2 4 2 1.5L14 13l-2-4z"/></svg>
                    </span>
                    <h1 class="mt-4 text-xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $tenant->name }}</h1>
                    <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Report an internet or service problem — it goes straight to the ISP.') }}</p>
                </div>

                <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                    @if(session('status'))
                        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
                            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('status') }}</p>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-4 rounded-xl border border-error-200 bg-error-50 px-4 py-3 dark:border-error-500/20 dark:bg-error-500/10">
                            @foreach($errors->all() as $error)
                                <p class="text-theme-sm text-error-700 dark:text-error-300">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('issues.public.store', ['tenant' => $tenant->slug]) }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="rep-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Your name') }}<span class="ml-0.5 text-error-500">*</span></label>
                            <input id="rep-name" name="name" type="text" value="{{ old('name') }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="{{ __('e.g. Rahim Uddin') }}">
                        </div>
                        <div>
                            <label for="rep-phone" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Phone number') }}<span class="ml-0.5 text-error-500">*</span></label>
                            <input id="rep-phone" name="phone" type="text" value="{{ old('phone') }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="01XXXXXXXXX">
                        </div>
                        <div>
                            <label for="rep-category" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('What kind of problem?') }}</label>
                            <select id="rep-category" name="category" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="connection" @selected(old('category') === 'connection')>{{ __('No internet / slow connection') }}</option>
                                <option value="network" @selected(old('category') === 'network')>{{ __('Network problem') }}</option>
                                <option value="service" @selected(old('category') === 'service')>{{ __('Service / account') }}</option>
                                <option value="billing" @selected(old('category') === 'billing')>{{ __('Billing / payment') }}</option>
                                <option value="other" @selected(old('category') === 'other')>{{ __('Other') }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="rep-subject" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Short description') }}<span class="ml-0.5 text-error-500">*</span></label>
                            <input id="rep-subject" name="subject" type="text" value="{{ old('subject') }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="{{ __('e.g. No internet since this morning') }}">
                        </div>
                        <div>
                            <label for="rep-details" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('More details') }}</label>
                            <textarea id="rep-details" name="description" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="{{ __('Optional — e.g. which area, since when, error messages.') }}">{{ old('description') }}</textarea>
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-3 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7z"/></svg>
                            {{ __('Send report') }}
                        </button>
                    </form>
                </div>

                <p class="mt-4 text-center text-theme-xs text-gray-400 dark:text-gray-500">Powered by <span class="font-semibold text-gray-500 dark:text-gray-400">BeeCore</span></p>
            </div>
        </div>
    </body>
</html>
