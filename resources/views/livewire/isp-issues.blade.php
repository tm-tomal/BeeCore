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
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
                <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('New') }}</p>
                <p class="mt-1 text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $stats['new'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
                <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('In progress') }}</p>
                <p class="mt-1 text-2xl font-bold text-brand-600 dark:text-brand-400">{{ $stats['inProgress'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
                <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Resolved') }}</p>
                <p class="mt-1 text-2xl font-bold text-success-600 dark:text-success-400">{{ $stats['resolved'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
                <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Reported by customers') }}</p>
                <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stats['fromCustomers'] }}</p>
            </div>
        </section>

        <!-- Filters -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:max-w-lg">
            <div>
                <label for="iss-status-filter" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Status') }}</label>
                <select id="iss-status-filter" wire:model.live="statusFilter" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">{{ __('All') }}</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="iss-category-filter" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Category') }}</label>
                <select id="iss-category-filter" wire:model.live="categoryFilter" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">{{ __('All') }}</option>
                    @foreach($categories as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Issues table -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Issue') }}</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Reported by') }}</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Category') }}</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Priority') }}</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Status') }}</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($issues as $issue)
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="max-w-64 px-5 py-4">
                                    <div class="truncate text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $issue->subject }}</div>
                                    <div class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">
                                        {{ $issue->created_at->diffForHumans() }}
                                        @if($issue->source === 'public')
                                            <span class="ml-1 inline-flex rounded-full bg-brand-50 px-2 py-0.5 text-theme-xs font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ __('Customer report') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="text-theme-sm text-gray-700 dark:text-gray-300">{{ $issue->reporter_name }}</span>
                                    @if($issue->reporter_phone)
                                        <div class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $issue->reporter_phone }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $categories[$issue->category] ?? $issue->category }}</td>
                                <td class="px-5 py-4">
                                    @php $priorityLabel = ['low' => __('Low'), 'medium' => __('Medium'), 'high' => __('High'), 'urgent' => __('Urgent')][$issue->priority] ?? $issue->priority; @endphp
                                    <span class="rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $issue->priority === 'urgent' ? 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500' : ($issue->priority === 'high' ? 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500' : 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400') }}">{{ $priorityLabel }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <select wire:change="updateStatus({{ $issue->id }}, $event.target.value)" class="h-9 rounded-lg border border-gray-300 bg-transparent px-2.5 py-1.5 text-theme-xs text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                        @foreach($statuses as $value => $label)
                                            <option value="{{ $value }}" @selected($issue->status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" wire:click="viewIssue({{ $issue->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">{{ __('Open') }}</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-theme-sm text-gray-500 dark:text-gray-400">{{ $statusFilter || $categoryFilter ? __('No issues match your filters.') : __('No issues reported yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($issues->hasPages())
                <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $issues->links() }}</div>
            @endif
        </div>
    @else
        <!-- New issue form -->
        <div class="mx-auto max-w-3xl space-y-6">
            <div>
                <button type="button" wire:click="cancelForm" class="inline-flex items-center gap-2 text-theme-sm font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    {{ __('Back to issues') }}
                </button>
            </div>
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('New issue') }}</h2>
                <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Record a problem a customer reported or one your team found.') }}</p>

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
        </div>
    @endif

    <!-- Issue detail modal -->
    @if($detailIssue)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeDetail"></div>
            <div class="relative max-h-[85vh] w-full max-w-xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $detailIssue->subject }}</h2>
                        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                            {{ $categories[$detailIssue->category] ?? $detailIssue->category }}
                            · {{ $statuses[$detailIssue->status] ?? $detailIssue->status }}
                            · {{ $detailIssue->created_at->format('d M Y, h:i A') }}
                        </p>
                    </div>
                    <button type="button" wire:click="closeDetail" class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <dl class="mt-4 grid grid-cols-2 gap-3 rounded-xl border border-gray-200 bg-gray-50/60 p-4 text-theme-sm dark:border-gray-800 dark:bg-white/[0.02]">
                    <div>
                        <dt class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Reporter') }}</dt>
                        <dd class="mt-0.5 font-medium text-gray-800 dark:text-white/90">{{ $detailIssue->reporter_name }}@if($detailIssue->reporter_phone) · {{ $detailIssue->reporter_phone }}@endif</dd>
                    </div>
                    <div>
                        <dt class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Source') }}</dt>
                        <dd class="mt-0.5 font-medium text-gray-800 dark:text-white/90">{{ $detailIssue->source === 'public' ? __('Customer report') : __('Staff') }}</dd>
                    </div>
                    @if($detailIssue->customer)
                        <div>
                            <dt class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Subscriber') }}</dt>
                            <dd class="mt-0.5 font-medium text-gray-800 dark:text-white/90">{{ $detailIssue->customer->name }}</dd>
                        </div>
                    @endif
                    @if($detailIssue->creator)
                        <div>
                            <dt class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Created by') }}</dt>
                            <dd class="mt-0.5 font-medium text-gray-800 dark:text-white/90">{{ $detailIssue->creator->name }}</dd>
                        </div>
                    @endif
                </dl>

                @if($detailIssue->description)
                    <p class="mt-4 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-theme-sm text-gray-600 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300">{{ $detailIssue->description }}</p>
                @endif

                @if($detailIssue->attachments->isNotEmpty())
                    <x-attachment-gallery :attachments="$detailIssue->attachments" />
                @endif

                <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <span class="text-theme-xs font-medium uppercase tracking-wide text-gray-400">{{ __('Update status') }}</span>
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach(['new', 'in_progress', 'resolved', 'closed'] as $status)
                            <button type="button" wire:click="updateStatus({{ $detailIssue->id }}, '{{ $status }}')" class="rounded-lg px-3 py-1.5 text-theme-xs font-medium transition {{ $detailIssue->status === $status ? 'bg-brand-500 text-white' : 'border border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300' }}">{{ $statuses[$status] }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
