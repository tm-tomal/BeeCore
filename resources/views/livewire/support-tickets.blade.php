<div class="space-y-6">
    @php
        $statusChip = fn (string $status): string => match ($status) {
            'open' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/15 dark:text-sky-500',
            'pending' => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
            'in_progress' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
            'resolved' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
            'escalated' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
            default => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
        };
        $priorityChip = fn (string $priority): string => match ($priority) {
            'urgent' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
            'high' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
            'medium' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
            default => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400',
        };
    @endphp

    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Support tickets</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Cross-tenant ticket lifecycle, assignment, SLA, and performance.</p>
        </div>
        <button type="button" wire:click="create" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            New ticket
        </button>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Performance summary -->
    <section class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $performance['open'] }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Open tickets</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-error-500/10 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold {{ $performance['escalated'] ? 'text-error-600 dark:text-error-400' : 'text-gray-800 dark:text-white/90' }}">{{ $performance['escalated'] }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Escalated</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-success-500/10 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $performance['avg_response_minutes'] }}<span class="text-base font-medium text-gray-400"> min</span></p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Avg response</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-violet-500/10 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $performance['avg_resolution_hours'] }}<span class="text-base font-medium text-gray-400"> hr</span></p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Avg resolution</p>
            </div>
        </div>
    </section>

    <!-- Filters -->
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:max-w-lg">
            <div>
                <label for="st-status-filter" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
                <select id="st-status-filter" wire:model.live="statusFilter" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">All statuses</option>
                    @foreach(['open', 'pending', 'in_progress', 'resolved', 'closed', 'escalated'] as $status)
                        <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="st-priority-filter" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Priority</label>
                <select id="st-priority-filter" wire:model.live="priorityFilter" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">All priorities</option>
                    @foreach(['low', 'medium', 'high', 'urgent'] as $priority)
                        <option value="{{ $priority }}">{{ ucfirst($priority) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <p class="text-theme-xs text-gray-400 dark:text-gray-500">Showing {{ $tickets->total() }} ticket{{ $tickets->total() === 1 ? '' : 's' }}</p>
    </div>

    <!-- Tickets table -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="w-full overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Ticket</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Category</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Priority</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Agent</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($tickets as $ticket)
                        <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $statusChip($ticket->status) }}">
                                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $ticket->subject }}</p>
                                        <p class="mt-0.5 truncate text-theme-xs text-gray-400 dark:text-gray-500">{{ $ticket->tenant?->name ?? 'Platform' }} · {{ $ticket->created_at?->format('d M Y') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4"><span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs font-medium capitalize text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">{{ $ticket->category }}</span></td>
                            <td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ $priorityChip($ticket->priority) }}">{{ $ticket->priority }}</span></td>
                            <td class="px-5 py-4">
                                <select wire:change="updateStatus({{ $ticket->id }}, $event.target.value)" class="h-9 min-w-36 appearance-none rounded-lg border border-gray-300 bg-transparent px-2.5 py-1.5 text-theme-xs text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    @foreach(['open', 'pending', 'in_progress', 'resolved', 'closed', 'escalated'] as $status)
                                        <option value="{{ $status }}" @selected($ticket->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-5 py-4">
                                <select wire:change="assign({{ $ticket->id }}, $event.target.value)" class="h-9 min-w-36 appearance-none rounded-lg border border-gray-300 bg-transparent px-2.5 py-1.5 text-theme-xs text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="">Assign agent</option>
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}" @selected($ticket->assigned_to === $agent->id)>{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end">
                                    <button type="button" wire:click="viewDetail({{ $ticket->id }})" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50 px-3 py-2 text-theme-xs font-semibold text-brand-600 transition hover:border-brand-300 hover:bg-brand-100 dark:border-brand-500/25 dark:bg-brand-500/10 dark:text-brand-400">
                                        <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        Open
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-14 text-center">
                                <div class="mx-auto max-w-xs">
                                    <span class="mx-auto grid size-12 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/[0.05] dark:text-gray-500">
                                        <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                    </span>
                                    <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No tickets match these filters</p>
                                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Create a ticket or change the filters to see more.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())<div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $tickets->links() }}</div>@endif
    </div>

    <!-- New ticket modal -->
    @if($viewMode === 'create')
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="cancelForm"></div>
            <div class="relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                <div class="mb-6 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                            <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </span>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">New support ticket</h2>
                            <p class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">Open a ticket on behalf of a workspace or the platform.</p>
                        </div>
                    </div>
                    <button type="button" wire:click="cancelForm" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form wire:submit="save" class="space-y-5">
                    <div>
                        <label for="st-tenant" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Tenant (optional)</label>
                        <select id="st-tenant" wire:model="tenantId" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                            <option value="">Platform-level</option>
                            @foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label for="st-subject" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Subject</label>
                        <input id="st-subject" wire:model="subject" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        @error('subject') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="st-description" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Description</label>
                        <textarea id="st-description" wire:model="description" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                        @error('description') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-5 sm:grid-cols-3">
                        <div>
                            <label for="st-category" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Category</label>
                            <select id="st-category" wire:model="category" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                <option value="billing">Billing</option>
                                <option value="technical">Technical</option>
                                <option value="network">Network</option>
                                <option value="account">Account</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label for="st-priority" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Priority</label>
                            <select id="st-priority" wire:model="priority" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label for="st-sla" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">SLA (hours)</label>
                            <input id="st-sla" wire:model="slaHours" type="number" min="1" placeholder="e.g. 24" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            @error('slaHours') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <button type="button" wire:click="cancelForm" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Create ticket</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Ticket detail modal -->
    @if($detailTicket)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeDetail"></div>
            <div class="relative max-h-[85vh] w-full max-w-xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                            <svg class="size-4.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <h2 class="truncate text-lg font-semibold text-gray-800 dark:text-white/90">{{ $detailTicket->subject }}</h2>
                            <p class="mt-0.5 flex flex-wrap items-center gap-1.5 text-theme-xs text-gray-500 dark:text-gray-400">
                                <span>{{ $detailTicket->tenant?->name ?? 'Platform' }}</span>
                                <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 font-medium capitalize text-gray-600 dark:bg-white/[0.05] dark:text-gray-400">{{ $detailTicket->category }}</span>
                                <span class="inline-flex rounded-full px-2 py-0.5 font-medium capitalize {{ $priorityChip($detailTicket->priority) }}">{{ $detailTicket->priority }}</span>
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeDetail" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <p class="mt-5 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-theme-sm leading-relaxed text-gray-600 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300">{{ $detailTicket->description }}</p>
                @if($detailTicket->attachments->isNotEmpty())
                    <x-attachment-gallery :attachments="$detailTicket->attachments" />
                @endif
                @if($detailTicket->sla_due_at)
                    <p class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-warning-50 px-2.5 py-1 text-theme-xs font-semibold text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                        <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        SLA due {{ $detailTicket->sla_due_at->format('d M Y, H:i') }}
                    </p>
                @endif

                <h3 class="mt-6 text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Replies</h3>
                <div class="mt-3 space-y-3">
                    @forelse($detailTicket->replies as $reply)
                        <div class="rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $reply->user?->name ?? 'System' }}</span>
                                <span class="text-theme-xs text-gray-400 dark:text-gray-500">{{ $reply->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            <p class="mt-1.5 text-theme-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $reply->message }}</p>
                            @if($reply->attachments->isNotEmpty())
                                <x-attachment-gallery :attachments="$reply->attachments" />
                            @endif
                        </div>
                    @empty
                        <div class="py-6 text-center text-theme-sm text-gray-500 dark:text-gray-400">No replies yet.</div>
                    @endforelse
                </div>

                <form wire:submit="reply" class="mt-5 space-y-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <textarea id="reply-message" wire:model="replyMessage" rows="3" placeholder="Write a reply..." class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                    @error('replyMessage') <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    <div class="flex items-center justify-end gap-3">
                        <button type="button" wire:click="closeDetail" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Close</button>
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            Post reply
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
