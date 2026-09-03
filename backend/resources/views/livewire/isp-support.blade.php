<div class="space-y-6">
    <!-- Page header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">{{ __('BeeCore support') }}</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ __('Support') }}</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Ask the BeeCore team about billing, account, network or platform problems — they reply from the admin console.') }}</p>
        </div>
        @if($viewMode === 'index')
            <button type="button" wire:click="createForm" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                {{ __('New ticket') }}
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
        <!-- Stats -->
        <section class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
                <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Open') }}</p>
                <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stats['open'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
                <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Escalated') }}</p>
                <p class="mt-1 text-2xl font-bold text-error-600 dark:text-error-500">{{ $stats['escalated'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
                <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Assigned') }}</p>
                <p class="mt-1 text-2xl font-bold text-brand-600 dark:text-brand-400">{{ $stats['assigned'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
                <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Resolved') }}</p>
                <p class="mt-1 text-2xl font-bold text-success-600 dark:text-success-400">{{ $stats['resolved'] }}</p>
            </div>
        </section>

        <!-- Filters -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:max-w-lg">
            <div>
                <label for="sp-status-filter" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Status') }}</label>
                <select id="sp-status-filter" wire:model.live="statusFilter" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">{{ __('All') }}</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}">{{ $statusLabels[$status] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="sp-priority-filter" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Priority') }}</label>
                <select id="sp-priority-filter" wire:model.live="priorityFilter" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">{{ __('All') }}</option>
                    @foreach(array_keys($priorityLabels) as $priority)
                        <option value="{{ $priority }}">{{ $priorityLabels[$priority] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Tickets table -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Subject') }}</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Category') }}</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Priority') }}</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Assigned to') }}</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Status') }}</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($tickets as $ticket)
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">
                                    <div class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $ticket->subject }}</div>
                                    <div class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ $ticket->created_at->diffForHumans() }} · {{ $ticket->replies_count ?? 0 }} {{ __('reply(s)') }}</div>
                                </td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $categoryLabels[$ticket->category] ?? $ticket->category }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-theme-xs font-medium {{ $ticket->priority === 'urgent' ? 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500' : ($ticket->priority === 'high' ? 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500' : 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400') }}">{{ $priorityLabels[$ticket->priority] ?? $ticket->priority }}</span>
                                </td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $ticket->assignee?->name ?? '—' }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-theme-xs font-medium {{ in_array($ticket->status, ['resolved', 'closed']) ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400' : ($ticket->status === 'escalated' ? 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500' : 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500') }}">
                                        @if(! in_array($ticket->status, ['resolved', 'closed']))
                                            <span class="size-1.5 rounded-full bg-current animate-pulse"></span>
                                        @endif
                                        {{ $statusLabels[$ticket->status] ?? ucfirst($ticket->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" wire:click="viewDetail({{ $ticket->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">{{ __('Open') }}</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-theme-sm text-gray-500 dark:text-gray-400">{{ $statusFilter || $priorityFilter ? __('No tickets match your filters.') : __('No support tickets yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tickets->hasPages())
                <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $tickets->links() }}</div>
            @endif
        </div>
    @else
        <!-- New ticket form -->
        <div class="mx-auto max-w-3xl space-y-6">
            <div>
                <button type="button" wire:click="cancelForm" class="inline-flex items-center gap-2 text-theme-sm font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    {{ __('Back to tickets') }}
                </button>
            </div>
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('New support ticket') }}</h2>
                <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Tell the BeeCore team what you need help with.') }}</p>

                <form wire:submit="save" class="mt-5 space-y-5">
                    <div>
                        <label for="support-subject" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Subject') }}<span class="ml-0.5 text-error-500">*</span></label>
                        <input id="support-subject" type="text" wire:model="subject" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="{{ __('e.g. Unable to generate invoices') }}">
                        @error('subject') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="support-category" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Category') }}</label>
                            <select id="support-category" wire:model="category" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                @foreach($categoryLabels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="support-priority" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Priority') }}</label>
                            <select id="support-priority" wire:model="priority" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                @foreach($priorityLabels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="support-description" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Details') }}<span class="ml-0.5 text-error-500">*</span></label>
                        <textarea id="support-description" wire:model="description" rows="5" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" placeholder="{{ __('Describe the problem — what you expected and what actually happened.') }}"></textarea>
                        @error('description') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-col-reverse gap-3 pt-1 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="cancelForm" class="rounded-lg border border-gray-300 px-4 py-2.5 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">{{ __('Cancel') }}</button>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                            <span wire:loading.remove wire:target="save">{{ __('Send to BeeCore team') }}</span>
                            <span wire:loading wire:target="save">{{ __('Sending...') }}</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    @endif

    <!-- Ticket detail modal -->
    @if($detailTicket)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeDetail"></div>
            <div class="relative max-h-[85vh] w-full max-w-xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="truncate text-lg font-semibold text-gray-800 dark:text-white/90">{{ $detailTicket->subject }}</h2>
                        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                            {{ $categoryLabels[$detailTicket->category] ?? $detailTicket->category }}
                            · {{ $priorityLabels[$detailTicket->priority] ?? $detailTicket->priority }}
                            · {{ $statusLabels[$detailTicket->status] ?? $detailTicket->status }}
                            @if($detailTicket->assignee)
                                · {{ __('Assigned to :name', ['name' => $detailTicket->assignee->name]) }}
                            @endif
                        </p>
                    </div>
                    <button type="button" wire:click="closeDetail" class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <p class="mt-4 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-theme-sm text-gray-600 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300">{{ $detailTicket->description }}</p>
                @if($detailTicket->sla_due_at)
                    <p class="mt-3 text-theme-xs font-medium text-warning-600 dark:text-warning-400">{{ __('SLA due :date', ['date' => $detailTicket->sla_due_at->format('d M Y, H:i')]) }}</p>
                @endif

                <h3 class="mt-6 text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Replies') }}</h3>
                <ul class="mt-3 divide-y divide-gray-100 text-theme-sm dark:divide-gray-800">
                    @forelse($detailTicket->replies as $reply)
                        <li class="py-3.5 first:pt-0 last:pb-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold text-gray-800 dark:text-white/90">{{ $reply->user_id === auth()->id() ? __('You') : ($reply->user?->name ?? 'BeeCore') }}</span>
                                @if($reply->user_id !== auth()->id())
                                    <span class="rounded-full bg-brand-50 px-2 py-0.5 text-theme-xs font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ __('BeeCore team') }}</span>
                                @endif
                            </div>
                            <p class="mt-0.5 text-gray-600 dark:text-gray-400">{{ $reply->message }}</p>
                            <div class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ $reply->created_at->format('d M Y, H:i') }}</div>
                        </li>
                    @empty
                        <li class="py-6 text-center text-gray-500 dark:text-gray-400">{{ __('No replies yet.') }}</li>
                    @endforelse
                </ul>

                <form wire:submit="reply" class="mt-5 space-y-3">
                    <textarea wire:model="replyMessage" rows="3" placeholder="{{ __('Write a reply...') }}" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                    @error('replyMessage') <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    <div class="flex justify-end gap-3">
                        <button type="button" wire:click="closeDetail" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ __('Close') }}</button>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                            <span wire:loading.remove wire:target="reply">{{ __('Send reply') }}</span>
                            <span wire:loading wire:target="reply">{{ __('Sending...') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
