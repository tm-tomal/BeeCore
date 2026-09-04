<div class="space-y-6">
    @php
        $priorityTone = fn (string $p): string => match ($p) {
            'urgent' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
            'high' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
            default => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
        };
        $statusTone = fn (string $s): string => match ($s) {
            'resolved', 'closed' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
            'escalated' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
            default => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
        };
        $isResolved = fn (string $s): bool => in_array($s, ['resolved', 'closed'], true);
    @endphp

    @if($viewMode === 'index')
        <!-- Page header -->
        <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">{{ __('BeeCore support') }}</p>
                <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ __('Support tickets') }}</h1>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Ask the BeeCore team about billing, account, network or platform problems.') }}</p>
            </div>
            <button type="button" wire:click="createForm" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                {{ __('New ticket') }}
            </button>
        </header>

        @if(session()->has('message'))
            <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
                <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
            </div>
        @endif

        <!-- Stats -->
        <section class="grid grid-cols-2 gap-4 xl:grid-cols-4">
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                </span>
                <div>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Open') }}</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stats['open'] }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                </span>
                <div>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Escalated') }}</p>
                    <p class="text-2xl font-bold text-error-600 dark:text-error-500">{{ $stats['escalated'] }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
                <div>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Assigned') }}</p>
                    <p class="text-2xl font-bold text-brand-600 dark:text-brand-400">{{ $stats['assigned'] }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                    <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </span>
                <div>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Resolved') }}</p>
                    <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $stats['resolved'] }}</p>
                </div>
            </div>
        </section>

        <!-- Tickets -->
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">
                <div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Your tickets') }}</h2>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Showing :count tickets', ['count' => number_format($tickets->total())]) }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <select wire:model.live="statusFilter" class="h-10 w-40 appearance-none rounded-lg border border-gray-300 bg-transparent pl-3.5 pr-8 text-theme-sm text-gray-700 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option value="">{{ __('All statuses') }}</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}">{{ $statusLabels[$status] }}</option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute inset-y-0 right-2.5 my-auto size-4 stroke-current text-gray-400" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="relative">
                        <select wire:model.live="priorityFilter" class="h-10 w-36 appearance-none rounded-lg border border-gray-300 bg-transparent pl-3.5 pr-8 text-theme-sm text-gray-700 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option value="">{{ __('All priorities') }}</option>
                            @foreach(array_keys($priorityLabels) as $priority)
                                <option value="{{ $priority }}">{{ $priorityLabels[$priority] }}</option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute inset-y-0 right-2.5 my-auto size-4 stroke-current text-gray-400" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                </div>
            </div>

            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Ticket') }}</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Category') }}</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Priority') }}</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Status') }}</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($tickets as $ticket)
                            <tr class="cursor-pointer transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]" wire:click="viewDetail({{ $ticket->id }})">
                                <td class="px-5 py-4">
                                    <p class="flex items-center gap-2 text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                                        @if(! $isResolved($ticket->status))
                                            <span class="size-2 shrink-0 rounded-full bg-brand-500" title="{{ __('Active') }}"></span>
                                        @endif
                                        <span class="truncate">{{ $ticket->subject }}</span>
                                    </p>
                                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">
                                        {{ __('Opened') }} {{ $ticket->created_at->diffForHumans() }}
                                        · {{ $ticket->replies_count ?? 0 }} {{ __('replies') }}
                                        @if($ticket->assignee)
                                            · {{ $ticket->assignee->name }}
                                        @endif
                                    </p>
                                </td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $categoryLabels[$ticket->category] ?? $ticket->category }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $priorityTone($ticket->priority) }}">{{ $priorityLabels[$ticket->priority] ?? $ticket->priority }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $statusTone($ticket->status) }}">
                                        @if(! $isResolved($ticket->status))
                                            <span class="size-1.5 animate-pulse rounded-full bg-current"></span>
                                        @endif
                                        {{ $statusLabels[$ticket->status] ?? ucfirst($ticket->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <span class="inline-flex items-center gap-1 text-theme-xs font-semibold text-brand-600 dark:text-brand-400">
                                        {{ __('Open') }}
                                        <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                                        </span>
                                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ $statusFilter || $priorityFilter ? __('No tickets match your filters.') : __('No support tickets yet.') }}</p>
                                        @if(! $statusFilter && ! $priorityFilter)
                                            <button type="button" wire:click="createForm" class="mt-3 inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-theme-xs font-semibold text-white shadow-theme-xs transition hover:bg-brand-600">
                                                <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                                {{ __('Create your first ticket') }}
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tickets->hasPages())
                <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $tickets->links() }}</div>
            @endif
        </section>

    @elseif($viewMode === 'create')
        <!-- New ticket page -->
        <header class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <button type="button" wire:click="cancelForm" class="grid size-9 shrink-0 place-items-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                </button>
                <div>
                    <h1 class="text-title-sm font-bold text-gray-800 dark:text-white/90">{{ __('New support ticket') }}</h1>
                    <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Back') }}</p>
                </div>
            </div>
        </header>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-5 py-4 sm:px-6 dark:border-gray-800">
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Tell the BeeCore team what you need help with — they reply from the admin console.') }}</p>
            </div>
            <form wire:submit="save" class="p-5 sm:p-6">
                <div>
                    <label for="support-subject" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Subject') }}<span class="ml-0.5 text-error-500">*</span></label>
                    <input id="support-subject" type="text" wire:model="subject" class="h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ __('e.g. Unable to generate invoices') }}">
                    @error('subject') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:max-w-2xl">
                    <div>
                        <label for="support-category" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Category') }}</label>
                        <div class="relative">
                            <select id="support-category" wire:model="category" class="h-12 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 pr-9 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                @foreach($categoryLabels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <svg class="pointer-events-none absolute inset-y-0 right-3 my-auto size-4 stroke-current text-gray-400" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                    </div>
                    <div>
                        <label for="support-priority" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Priority') }}</label>
                        <div class="relative">
                            <select id="support-priority" wire:model="priority" class="h-12 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 pr-9 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                @foreach($priorityLabels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <svg class="pointer-events-none absolute inset-y-0 right-3 my-auto size-4 stroke-current text-gray-400" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <label for="support-description" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('What happened?') }}<span class="ml-0.5 text-error-500">*</span></label>
                    <textarea id="support-description" wire:model="description" rows="6" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ __('Describe the problem — what you expected and what actually happened. Add any error messages you saw.') }}"></textarea>
                    @error('description') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                </div>

                <div class="mt-4">
                    <p class="mb-1.5 text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Photos / videos') }}</p>
                    <x-media-uploader />
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-gray-100 pt-5 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('The BeeCore team will usually reply within one business day.') }}</p>
                    <div class="flex flex-col-reverse gap-3 sm:flex-row">
                        <button type="button" wire:click="cancelForm" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">{{ __('Cancel') }}</button>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                            <span wire:loading.remove wire:target="save">{{ __('Send to BeeCore team') }}</span>
                            <span wire:loading wire:target="save">{{ __('Sending...') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </section>

    @elseif($detailTicket)
        <!-- Ticket detail page -->
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
                        <h1 class="truncate text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $detailTicket->subject }}</h1>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $statusTone($detailTicket->status) }}">{{ $statusLabels[$detailTicket->status] ?? ucfirst($detailTicket->status) }}</span>
                    </div>
                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">
                        {{ __('Ticket') }} #{{ $detailTicket->id }} · {{ $categoryLabels[$detailTicket->category] ?? $detailTicket->category }} · {{ __('Opened') }} {{ $detailTicket->created_at->format('d M Y, H:i') }}
                        @if($detailTicket->sla_due_at) · {{ __('SLA due') }} {{ $detailTicket->sla_due_at->format('d M Y, H:i') }} @endif
                    </p>
                </div>
            </div>
        </header>

        <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
            <!-- Conversation -->
            <section class="min-w-0 space-y-4">
                <div class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-brand-500/10 text-theme-sm font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ strtoupper(substr(auth()->user()->name ?? 'Y', 0, 1)) }}</span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ __('You') }}</p>
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs text-gray-500 dark:bg-white/[0.05] dark:text-gray-400">{{ $detailTicket->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        <p class="mt-1.5 whitespace-pre-line text-theme-sm leading-6 text-gray-600 dark:text-gray-300">{{ $detailTicket->description }}</p>
                        @if($detailTicket->attachments->isNotEmpty())
                            <x-attachment-gallery :attachments="$detailTicket->attachments" />
                        @endif
                    </div>
                </div>

                @forelse($detailTicket->replies as $reply)
                    <div class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                        @php $mine = $reply->user_id === auth()->id(); @endphp
                        <span class="grid size-9 shrink-0 place-items-center rounded-full text-theme-sm font-bold {{ $mine ? 'bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ strtoupper(substr($mine ? (auth()->user()->name ?? 'Y') : ($reply->user?->name ?? 'B'), 0, 1)) }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $mine ? __('You') : ($reply->user?->name ?? 'BeeCore') }}</p>
                                @if(! $mine)
                                    <span class="inline-flex rounded-full bg-brand-50 px-2 py-0.5 text-theme-xs font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ __('BeeCore team') }}</span>
                                @endif
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs text-gray-500 dark:bg-white/[0.05] dark:text-gray-400">{{ $reply->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            <p class="mt-1.5 whitespace-pre-line text-theme-sm leading-6 text-gray-600 dark:text-gray-300">{{ $reply->message }}</p>
                            @if($reply->attachments->isNotEmpty())
                                <x-attachment-gallery :attachments="$reply->attachments" />
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 px-6 py-10 text-center dark:border-gray-700">
                        <p class="text-theme-sm font-medium text-gray-600 dark:text-gray-300">{{ __('No replies yet') }}</p>
                        <p class="mt-1 text-theme-xs text-gray-400 dark:text-gray-500">{{ __('Write a reply below and the BeeCore team will pick it up.') }}</p>
                    </div>
                @endforelse

                <!-- Reply -->
                <form wire:submit="reply" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                    <label for="reply-message" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Add a reply') }}</label>
                    <textarea id="reply-message" wire:model="replyMessage" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ __('Write your reply to the BeeCore team...') }}"></textarea>
                    @error('replyMessage') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    <div class="mt-3">
                        <x-media-uploader />
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-2">
                        <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                            <span wire:loading.remove wire:target="reply">{{ __('Send reply') }}</span>
                            <span wire:loading wire:target="reply">{{ __('Sending...') }}</span>
                        </button>
                    </div>
                </form>
            </section>

            <!-- Sidebar -->
            <aside class="space-y-4">
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                    <h2 class="text-theme-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ __('Ticket details') }}</h2>
                    <dl class="mt-3 space-y-3 text-theme-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Category') }}</dt>
                            <dd class="font-medium text-gray-800 dark:text-white/90">{{ $categoryLabels[$detailTicket->category] ?? $detailTicket->category }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Priority') }}</dt>
                            <dd><span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $priorityTone($detailTicket->priority) }}">{{ $priorityLabels[$detailTicket->priority] ?? $detailTicket->priority }}</span></dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Assigned to') }}</dt>
                            <dd class="font-medium text-gray-800 dark:text-white/90">{{ $detailTicket->assignee?->name ?? __('Not yet') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Replies') }}</dt>
                            <dd class="font-medium text-gray-800 dark:text-white/90">{{ $detailTicket->replies->count() }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                    <h2 class="text-theme-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ __('Update status') }}</h2>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($statuses as $status)
                            @if(in_array($status, ['resolved', 'closed'], true))
                                <button type="button" wire:click="updateStatus({{ $detailTicket->id }}, '{{ $status }}')" wire:confirm="{{ __('Mark this ticket as :status?', ['status' => $statusLabels[$status]]) }}" class="rounded-lg border px-3 py-2 text-theme-xs font-medium transition {{ $detailTicket->status === $status ? 'border-success-300 bg-success-50 text-success-700 dark:border-success-500/40 dark:bg-success-500/10 dark:text-success-400' : 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]' }}">{{ $statusLabels[$status] }}</button>
                            @else
                                <button type="button" wire:click="updateStatus({{ $detailTicket->id }}, '{{ $status }}')" class="rounded-lg border px-3 py-2 text-theme-xs font-medium transition {{ $detailTicket->status === $status ? 'border-brand-300 bg-brand-50 text-brand-700 dark:border-brand-500/40 dark:bg-brand-500/10 dark:text-brand-400' : 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]' }}">{{ $statusLabels[$status] }}</button>
                            @endif
                        @endforeach
                    </div>
                </section>
            </aside>
        </div>
    @endif
</div>
