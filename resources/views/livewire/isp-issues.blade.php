<div class="space-y-6">
    <!-- Page header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">{{ __('Customer service') }}</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ __('Issues') }}</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Every reported problem in one list — your team and your customers can both add issues.') }}</p>
        </div>
        @if($viewMode === 'index')
            <button type="button" wire:click="createForm" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                {{ __('New issue') }}
            </button>
        @endif
    </div>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    @if($viewMode === 'index')
        <!-- Customer report link -->
        @if($publicUrl)
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-2.5">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                            <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        </span>
                        <div>
                            <p class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Share this link so customers can report problems') }}</p>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('No login needed — customers give their name and phone and the report lands here as a new issue.') }}</p>
                        </div>
                    </div>
                    <div class="flex w-full items-center gap-2 sm:w-auto">
                        <input type="text" readonly value="{{ $publicUrl }}" class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-theme-xs text-gray-600 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 sm:w-80" onclick="this.select()">
                        <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="inline-flex h-10 shrink-0 items-center justify-center gap-1.5 rounded-lg border border-gray-300 px-3 text-theme-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            {{ __('Open') }}
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Stats -->
        <section class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4M16 2v4"/><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18"/></svg>
                </span>
                <div>
                    <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('New') }}</p>
                    <p class="text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $stats['new'] }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </span>
                <div>
                    <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('In progress') }}</p>
                    <p class="text-2xl font-bold text-brand-600 dark:text-brand-400">{{ $stats['inProgress'] }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </span>
                <div>
                    <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Resolved') }}</p>
                    <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $stats['resolved'] }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
                <div>
                    <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Reported by customers') }}</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stats['fromCustomers'] }}</p>
                </div>
            </div>
        </section>

        <!-- Issues table -->
        @php
            $prioTone = fn (string $p): string => match ($p) {
                'urgent' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
                'high' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                'medium' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
                default => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
            };
            $statusTone = fn (string $s): string => match ($s) {
                'new' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                'in_progress' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
                'resolved', 'closed' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
                default => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
            };
            $priorityLabels = [
                'low' => __('Low'),
                'medium' => __('Medium'),
                'high' => __('High'),
                'urgent' => __('Urgent'),
            ];
        @endphp

        <x-table heading="{{ __('All issues') }}" :description="__('Showing :count issues', ['count' => number_format($issues->total())])" :paginator="$issues">
            <x-slot:toolbar>
                <div class="flex flex-wrap items-center gap-2">
                    <select wire:model.live="statusFilter" class="h-10 w-40 appearance-none rounded-lg border border-gray-300 bg-transparent px-3.5 py-2 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="categoryFilter" class="h-10 w-48 appearance-none rounded-lg border border-gray-300 bg-transparent px-3.5 py-2 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </x-slot:toolbar>

            <table class="min-w-full">
                <thead class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Issue') }}</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Reported by') }}</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Category') }}</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Priority') }}</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Status') }}</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($issues as $issue)
                        <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="max-w-72 px-5 py-4">
                                <p class="flex items-center gap-2 text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                    @if(! in_array($issue->status, ['resolved', 'closed'], true))
                                        <span class="size-2 shrink-0 rounded-full bg-brand-500" title="{{ __('Active') }}"></span>
                                    @endif
                                    <span class="truncate">{{ $issue->subject }}</span>
                                </p>
                                <div class="mt-1 flex flex-wrap items-center gap-x-1.5 gap-y-0.5 pl-4 text-theme-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ $issue->created_at->diffForHumans() }}</span>
                                    @if($issue->source === 'public')
                                        <span class="text-gray-300 dark:text-gray-700">•</span>
                                        <span class="rounded-full bg-brand-50 px-2 py-0.5 font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ __('Customer report') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-brand-50 text-theme-sm font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ mb_strtoupper(mb_substr($issue->reporter_name ?: '?', 0, 1)) }}</span>
                                    <div class="min-w-0">
                                        <div class="truncate text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $issue->reporter_name }}</div>
                                        @if($issue->reporter_phone)
                                            <div class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $issue->reporter_phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $categories[$issue->category] ?? $issue->category }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $prioTone($issue->priority) }}">{{ $priorityLabels[$issue->priority] ?? $issue->priority }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $statusTone($issue->status) }}">{{ $statuses[$issue->status] ?? ucfirst($issue->status) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end">
                                    <button type="button" wire:click="viewIssue({{ $issue->id }})" class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-theme-sm font-semibold text-brand-600 transition hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">
                                        {{ __('Open') }}
                                        <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-14 text-center">
                                <div class="mx-auto max-w-xs">
                                    <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                        <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                                    </span>
                                    <p class="mt-3 text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ $statusFilter || $categoryFilter ? __('No issues match your filters.') : __('No issues reported yet.') }}</p>
                                    @if(! $statusFilter && ! $categoryFilter)
                                        <button type="button" wire:click="createForm" class="mt-4 inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-theme-xs font-semibold text-white shadow-theme-xs transition hover:bg-brand-600">
                                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                            {{ __('Create an issue') }}
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table>
    @elseif($viewMode === 'create')
        <!-- New issue form -->
        <div class="space-y-6">
            <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('New issue') }}</h2>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Record a problem a customer reported or one your team found.') }}</p>
                        </div>
                        <button type="button" wire:click="cancelForm" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-theme-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                            {{ __('Back to issues') }}
                        </button>
                    </div>

                    <form wire:submit="save" class="mt-5 space-y-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="issue-customer" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Subscriber (optional)') }}</label>
                            <select id="issue-customer" wire:model="customerId" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">{{ __('Select a subscriber...') }}</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}{{ $customer->phone ? ' — '.$customer->phone : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="issue-category" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Category') }}</label>
                            <select id="issue-category" wire:model="category" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                @foreach($categories as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="issue-subject" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Subject') }}<span class="ml-0.5 text-error-500">*</span></label>
                        <input id="issue-subject" type="text" wire:model="subject" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="{{ __('e.g. No internet in Banani since morning') }}">
                        @error('subject') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="issue-description" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Details') }}</label>
                        <textarea id="issue-description" wire:model="description" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                        @error('description') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="issue-reporter" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Reporter name') }}<span class="ml-0.5 text-error-500">*</span></label>
                            <input id="issue-reporter" type="text" wire:model="reporterName" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @error('reporterName') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="issue-phone" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Phone') }}</label>
                            <input id="issue-phone" type="text" wire:model="reporterPhone" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="01XXXXXXXXX">
                            @error('reporterPhone') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="issue-priority" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Priority') }}</label>
                        <select id="issue-priority" wire:model="priority" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 sm:max-w-52">
                            <option value="low">{{ __('Low') }}</option>
                            <option value="medium">{{ __('Medium') }}</option>
                            <option value="high">{{ __('High') }}</option>
                            <option value="urgent">{{ __('Urgent') }}</option>
                        </select>
                    </div>

                    <div class="flex flex-col-reverse gap-3 pt-1 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="cancelForm" class="rounded-lg border border-gray-300 px-4 py-2.5 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">{{ __('Cancel') }}</button>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                            <span wire:loading.remove wire:target="save">{{ __('Create issue') }}</span>
                            <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                        </button>
                    </div>
                </form>
            </section>

            <aside class="space-y-4">
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="text-theme-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ __('What happens next') }}</h3>
                    <ol class="mt-3 space-y-3">
                        <li class="flex items-start gap-2.5 text-theme-sm text-gray-600 dark:text-gray-300">
                            <span class="grid size-5 shrink-0 place-items-center rounded-full bg-brand-500/10 text-theme-xs font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">1</span>
                            {{ __('Open the issue and assign a staff member to fix it.') }}
                        </li>
                        <li class="flex items-start gap-2.5 text-theme-sm text-gray-600 dark:text-gray-300">
                            <span class="grid size-5 shrink-0 place-items-center rounded-full bg-brand-500/10 text-theme-xs font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">2</span>
                            {{ __('The assigned member posts progress updates on the issue.') }}
                        </li>
                        <li class="flex items-start gap-2.5 text-theme-sm text-gray-600 dark:text-gray-300">
                            <span class="grid size-5 shrink-0 place-items-center rounded-full bg-success-500/10 text-theme-xs font-bold text-success-600 dark:bg-success-500/15 dark:text-success-400">3</span>
                            {{ __('Mark it resolved when the problem is fixed.') }}
                        </li>
                    </ol>
                </section>
            </aside>
        </div>
    @elseif($detailIssue)
        @php
            $prioChip = fn (string $p): string => match ($p) {
                'urgent' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
                'high' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                'medium' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
                default => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
            };
            $detailStatusChip = fn (string $s): string => match ($s) {
                'new' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                'in_progress' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
                'resolved', 'closed' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
                default => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
            };
            $actor = auth()->user();
            $canManageIssue = $actor?->isSuperAdmin() || $actor?->role === \App\Models\User::ROLE_TENANT_ADMIN;
            $canReplyIssue = $canManageIssue || ($actor && (int) $detailIssue->assigned_to === (int) $actor->id);
            $isMine = fn (?int $userId): bool => $actor && (int) $userId === (int) $actor->id;
            $avatarTone = fn (?int $userId): string => $isMine($userId)
                ? 'bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400'
                : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400';
            $staffChip = fn (?string $role): string => match ($role) {
                \App\Models\User::ROLE_TENANT_ADMIN => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
                \App\Models\User::ROLE_NETWORK_ENGINEER => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
                default => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
            };
            $assignee = $detailIssue->assignee;
        @endphp
        <div class="space-y-6">
            @if(session()->has('message'))
                <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
                    <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
                </div>
            @endif

            <header class="flex flex-wrap items-start justify-between gap-3">
                <div class="flex min-w-0 items-start gap-3">
                    <button type="button" wire:click="closeDetail" class="mt-0.5 grid size-9 shrink-0 place-items-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    </button>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="truncate text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $detailIssue->subject }}</h1>
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $detailStatusChip($detailIssue->status) }}">
                                @if(! in_array($detailIssue->status, ['resolved', 'closed'], true))
                                    <span class="size-1.5 animate-pulse rounded-full bg-current"></span>
                                @endif
                                {{ $statuses[$detailIssue->status] ?? ucfirst($detailIssue->status) }}
                            </span>
                        </div>
                        <p class="mt-1 flex flex-wrap items-center gap-x-1.5 gap-y-0.5 text-theme-xs text-gray-500 dark:text-gray-400">
                            <span>{{ __('Issue') }} #{{ $detailIssue->id }}</span>
                            <span>·</span>
                            <span>{{ $categories[$detailIssue->category] ?? $detailIssue->category }}</span>
                            <span>·</span>
                            <span class="inline-flex items-center gap-1 capitalize">
                                <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                                {{ $detailIssue->priority }}
                            </span>
                            <span>·</span>
                            <span>{{ $detailIssue->created_at->format('d M Y, h:i A') }}</span>
                            @if($detailIssue->source === 'public')
                                <span>·</span>
                                <span class="font-medium text-brand-600 dark:text-brand-400">{{ __('Customer report') }}</span>
                            @endif
                        </p>
                    </div>
                </div>
            </header>

            <div class="grid items-start gap-6 lg:grid-cols-3">
                <!-- Conversation -->
                <section class="min-w-0 space-y-4 lg:col-span-2">
                    <!-- Original report -->
                    <article class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-brand-500/10 text-theme-sm font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ mb_strtoupper(mb_substr($detailIssue->reporter_name ?: '?', 0, 1)) }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $detailIssue->reporter_name }}</p>
                                @if($detailIssue->source === 'public')
                                    <span class="rounded-full bg-brand-50 px-2 py-0.5 text-theme-xs font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ __('Customer report') }}</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs font-medium text-gray-600 dark:bg-white/[0.06] dark:text-gray-400">{{ __('Reported by your team') }}</span>
                                @endif
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs text-gray-500 dark:bg-white/[0.05] dark:text-gray-400">{{ $detailIssue->created_at->format('d M Y, h:i A') }}</span>
                            </div>
                            <p class="mt-2.5 whitespace-pre-line text-theme-sm leading-6 text-gray-700 dark:text-gray-300">
                                {{ $detailIssue->description ?: __('No additional details were provided.') }}
                            </p>
                            @if($detailIssue->attachments->isNotEmpty())
                                <div class="mt-3"><x-attachment-gallery :attachments="$detailIssue->attachments" /></div>
                            @endif
                        </div>
                    </article>

                    <!-- Updates -->
                    @forelse($detailIssue->replies->sortBy('id') as $reply)
                        <article class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                            <span class="grid size-9 shrink-0 place-items-center rounded-full text-theme-sm font-bold {{ $avatarTone($reply->user_id) }}">{{ mb_strtoupper(mb_substr($reply->user?->name ?: __('Team'), 0, 1)) }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                                        {{ $reply->user?->name ?: __('Team member') }}
                                        @if($isMine($reply->user_id))
                                            <span class="ml-0.5 font-normal text-gray-400 dark:text-gray-500">({{ __('you') }})</span>
                                        @endif
                                    </p>
                                    @if($reply->user)
                                        <span class="rounded-full px-2 py-0.5 text-theme-xs font-medium {{ $staffChip($reply->user->role) }}">{{ \App\Models\User::roleLabel($reply->user->role) }}</span>
                                    @endif
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs text-gray-500 dark:bg-white/[0.05] dark:text-gray-400">{{ $reply->created_at->format('d M Y, h:i A') }}</span>
                                </div>
                                <p class="mt-2 whitespace-pre-line text-theme-sm leading-6 text-gray-700 dark:text-gray-300">{{ $reply->message }}</p>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-300 px-6 py-10 text-center dark:border-gray-700">
                            <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                            </span>
                            <p class="mt-3 text-theme-sm font-medium text-gray-600 dark:text-gray-300">{{ __('No updates yet') }}</p>
                            <p class="mt-1 text-theme-xs text-gray-400 dark:text-gray-500">{{ __('The member fixing this issue posts their progress here.') }}</p>
                        </div>
                    @endforelse

                    <!-- Composer -->
                    @if($canReplyIssue)
                        <form wire:submit="reply" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <label for="issue-reply" class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Post an update') }}</label>
                                @if($detailIssue->status === \App\Models\Issue::STATUS_NEW)
                                    <span class="text-theme-xs text-gray-400 dark:text-gray-500">{{ __('The first update moves this issue to In progress.') }}</span>
                                @endif
                            </div>
                            <textarea id="issue-reply" wire:model="replyMessage" rows="4" class="mt-2.5 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ __('Share what was checked, what got fixed, or what the customer still needs...') }}"></textarea>
                            @error('replyMessage') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                            <div class="mt-3 flex justify-end">
                                <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                    <span wire:loading.remove wire:target="reply">{{ __('Send update') }}</span>
                                    <span wire:loading wire:target="reply">{{ __('Sending...') }}</span>
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 text-center shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                            <p class="text-theme-xs text-gray-400 dark:text-gray-500">{{ __('Only the assigned member or the ISP admin can post updates on this issue.') }}</p>
                        </div>
                    @endif
                </section>

                <!-- Sidebar -->
                <aside class="space-y-4">
                    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                        <h2 class="text-theme-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ __('Report info') }}</h2>
                        <dl class="mt-3 space-y-3 text-theme-sm">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('Category') }}</dt>
                                <dd class="font-medium text-gray-800 dark:text-white/90">{{ $categories[$detailIssue->category] ?? $detailIssue->category }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('Priority') }}</dt>
                                <dd><span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $prioChip($detailIssue->priority) }}">{{ ucfirst($detailIssue->priority) }}</span></dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('Source') }}</dt>
                                <dd class="font-medium text-gray-800 dark:text-white/90">{{ $detailIssue->source === 'public' ? __('Customer') : __('Your team') }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('Reported') }}</dt>
                                <dd class="text-right font-medium text-gray-800 dark:text-white/90">{{ $detailIssue->created_at->format('d M Y') }}</dd>
                            </div>
                            @if($detailIssue->creator)
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-gray-500 dark:text-gray-400">{{ __('Created by') }}</dt>
                                    <dd class="font-medium text-gray-800 dark:text-white/90">{{ $detailIssue->creator->name }}</dd>
                                </div>
                            @endif
                        </dl>
                    </section>

                    @if($detailIssue->customer || $detailIssue->reporter_phone)
                        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                            <h2 class="text-theme-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ __('Contact') }}</h2>
                            <dl class="mt-3 space-y-3 text-theme-sm">
                                @if($detailIssue->customer)
                                    <div class="flex items-center justify-between gap-3">
                                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Subscriber') }}</dt>
                                        <dd class="text-right font-medium text-gray-800 dark:text-white/90">{{ $detailIssue->customer->name }}</dd>
                                    </div>
                                @endif
                                @if($detailIssue->reporter_phone)
                                    <div class="flex items-center justify-between gap-3">
                                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Phone') }}</dt>
                                        <dd class="font-medium text-gray-800 dark:text-white/90"><a href="tel:{{ $detailIssue->reporter_phone }}" class="transition hover:text-brand-600 dark:hover:text-brand-400">{{ $detailIssue->reporter_phone }}</a></dd>
                                    </div>
                                @endif
                            </dl>
                        </section>
                    @endif

                    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                        <h2 class="text-theme-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ __('Assigned to fix') }}</h2>

                        @if($assignee)
                            <div class="mt-3 flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/70 p-3 dark:border-gray-800 dark:bg-white/[0.02]">
                                <span class="grid size-9 shrink-0 place-items-center rounded-full text-theme-sm font-bold {{ $avatarTone($assignee->id) }}">{{ mb_strtoupper(mb_substr($assignee->name ?: '?', 0, 1)) }}</span>
                                <div class="min-w-0">
                                    <p class="truncate text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $assignee->name }}</p>
                                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ \App\Models\User::roleLabel($assignee->role) }}</p>
                                </div>
                                @if($isMine($assignee->id))
                                    <span class="ml-auto shrink-0 rounded-full bg-brand-50 px-2 py-0.5 text-theme-xs font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ __('You') }}</span>
                                @endif
                            </div>
                        @else
                            <p class="mt-3 rounded-xl border border-dashed border-gray-200 px-3 py-3 text-center text-theme-xs text-gray-400 dark:border-gray-700 dark:text-gray-500">{{ __('No one is working on this yet.') }}</p>
                        @endif

                        @if($canManageIssue)
                            <div class="mt-3 border-t border-gray-100 pt-3 dark:border-gray-800">
                                <label for="assignee-select" class="mb-1.5 block text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Choose who will fix this') }}</label>
                                <select id="assignee-select" wire:model="assignToUserId" class="h-10 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="">{{ __('Not assigned') }}</option>
                                    @foreach($assignableStaff as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->name }} — {{ \App\Models\User::roleLabel($staff->role) }}</option>
                                    @endforeach
                                </select>
                                <div class="mt-2.5 flex items-center gap-2">
                                    <button type="button" wire:click="saveAssignment" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3 py-2 text-theme-xs font-semibold text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50" @disabled(! $assignToUserId)>
                                        <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        {{ $assignee ? __('Save changes') : __('Assign') }}
                                    </button>
                                    @if($assignee)
                                        <button type="button" wire:click="unassign" class="rounded-lg border border-gray-200 px-3 py-2 text-theme-xs font-semibold text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">{{ __('Unassign') }}</button>
                                    @endif
                                </div>
                                <p class="mt-2 text-theme-xs text-gray-400 dark:text-gray-500">{{ __('Only this member and the ISP admin can post updates.') }}</p>
                            </div>
                        @endif
                    </section>

                    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                        <h2 class="text-theme-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ __('Update status') }}</h2>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            @foreach(['new', 'in_progress'] as $status)
                                <button type="button" wire:click="updateStatus({{ $detailIssue->id }}, '{{ $status }}')" class="rounded-lg px-3 py-2 text-theme-xs font-medium transition {{ $detailIssue->status === $status ? 'bg-brand-500 text-white shadow-theme-xs' : 'border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]' }}">{{ $statuses[$status] }}</button>
                            @endforeach
                            @foreach(['resolved', 'closed'] as $status)
                                <button type="button" wire:click="updateStatus({{ $detailIssue->id }}, '{{ $status }}')" wire:confirm="{{ __('Mark this issue as :status?', ['status' => $statuses[$status]]) }}" class="rounded-lg px-3 py-2 text-theme-xs font-medium transition {{ $detailIssue->status === $status ? 'bg-success-500 text-white shadow-theme-xs' : 'border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]' }}">{{ $statuses[$status] }}</button>
                            @endforeach
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    @endif
</div>
