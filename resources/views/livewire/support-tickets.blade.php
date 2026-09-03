<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Support tickets</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Cross-tenant ticket lifecycle, assignment, SLA, and performance.</p>
        </div>
        <button wire:click="create" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">New ticket</button>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-success-500" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Performance summary -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Open</p>
            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $performance['open'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Escalated</p>
            <p class="mt-2 text-2xl font-bold text-error-600 dark:text-error-500">{{ $performance['escalated'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Avg response</p>
            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $performance['avg_response_minutes'] }} min</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Avg resolution</p>
            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $performance['avg_resolution_hours'] }} hr</p>
        </div>
    </section>

    <!-- Filters -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:max-w-lg">
        <div>
            <label for="st-status-filter" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
            <select id="st-status-filter" wire:model.live="statusFilter" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                <option value="">All</option>
                <option value="open">Open</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
                <option value="escalated">Escalated</option>
            </select>
        </div>
        <div>
            <label for="st-priority-filter" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Priority</label>
            <select id="st-priority-filter" wire:model.live="priorityFilter" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                <option value="">All</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
            </select>
        </div>
    </div>

    <!-- Tickets table -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="w-full overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Subject</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tenant</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Category</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Priority</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Assigned</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($tickets as $ticket)
                        <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 text-theme-sm font-semibold text-gray-800 dark:text-white/90">{{ $ticket->subject }}</td>
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $ticket->tenant?->name ?? 'Platform' }}</td>
                            <td class="px-5 py-4 text-theme-sm capitalize text-gray-600 dark:text-gray-400">{{ $ticket->category }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ match($ticket->priority) { 'urgent' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500', 'high' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500', default => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400' } }}">{{ $ticket->priority }}</span>
                            </td>
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $ticket->assignee?->name ?? '—' }}</td>
                            <td class="px-5 py-4">
                                <select wire:change="updateStatus({{ $ticket->id }}, $event.target.value)" class="h-9 min-w-36 rounded-lg border border-gray-300 bg-transparent px-2.5 py-1.5 text-theme-xs text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    @foreach(['open', 'pending', 'in_progress', 'resolved', 'closed', 'escalated'] as $status)
                                        <option value="{{ $status }}" @selected($ticket->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <select wire:change="assign({{ $ticket->id }}, $event.target.value)" class="h-9 min-w-36 rounded-lg border border-gray-300 bg-transparent px-2.5 py-1.5 text-theme-xs text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                        <option value="">Assign agent</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->id }}" @selected($ticket->assigned_to === $agent->id)>{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                    <button wire:click="viewDetail({{ $ticket->id }})" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-theme-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">Open</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-theme-sm text-gray-500 dark:text-gray-400">No support tickets match these filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $tickets->links() }}</div>
        @endif
    </div>

    <!-- New ticket modal -->
    @if($viewMode === 'create')
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="cancelForm"></div>
            <div class="relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">New support ticket</h2>
                <form wire:submit="save" class="mt-5 space-y-5">
                    <div>
                        <label for="st-tenant" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Tenant (optional)</label>
                        <select id="st-tenant" wire:model="tenantId" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                            <option value="">Platform-level</option>
                            @foreach($tenants as $tenant)
                                <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                            @endforeach
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
                            <select id="st-category" wire:model="category" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                <option value="billing">Billing</option>
                                <option value="technical">Technical</option>
                                <option value="network">Network</option>
                                <option value="account">Account</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label for="st-priority" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Priority</label>
                            <select id="st-priority" wire:model="priority" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
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
                    <div class="flex justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <button type="button" wire:click="cancelForm" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Create ticket</button>
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
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $detailTicket->subject }}</h2>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $detailTicket->tenant?->name ?? 'Platform' }} · {{ ucfirst($detailTicket->category) }} · {{ ucfirst($detailTicket->priority) }}</p>
                <p class="mt-4 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-theme-sm text-gray-600 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300">{{ $detailTicket->description }}</p>
                @if($detailTicket->sla_due_at)
                    <p class="mt-3 text-theme-xs font-medium text-warning-600 dark:text-warning-400">SLA due {{ $detailTicket->sla_due_at->format('d M Y, H:i') }}</p>
                @endif

                <h3 class="mt-6 text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Replies</h3>
                <ul class="mt-3 divide-y divide-gray-100 text-theme-sm dark:divide-gray-800">
                    @forelse($detailTicket->replies as $reply)
                        <li class="py-3.5 first:pt-0 last:pb-0">
                            <div class="font-semibold text-gray-800 dark:text-white/90">{{ $reply->user?->name ?? 'System' }}</div>
                            <div class="mt-0.5 text-gray-600 dark:text-gray-400">{{ $reply->message }}</div>
                            <div class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ $reply->created_at->format('d M Y, H:i') }}</div>
                        </li>
                    @empty
                        <li class="py-6 text-center text-gray-500 dark:text-gray-400">No replies yet.</li>
                    @endforelse
                </ul>

                <form wire:submit="reply" class="mt-5 space-y-3">
                    <textarea wire:model="replyMessage" rows="3" placeholder="Write a reply..." class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                    @error('replyMessage') <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    <div class="flex justify-end gap-3">
                        <button type="button" wire:click="closeDetail" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Close</button>
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Post reply</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
