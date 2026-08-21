<div>
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Support tickets</h1>
            <p class="mt-2 text-sm text-slate-500">Cross-tenant ticket lifecycle, assignment, SLA, and performance.</p>
        </div>
        <button wire:click="create" class="bc-primary">New ticket</button>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <section class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Open</p><p class="mt-2 text-xl font-black text-white">{{ $performance['open'] }}</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Escalated</p><p class="mt-2 text-xl font-black text-rose-300">{{ $performance['escalated'] }}</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Avg response</p><p class="mt-2 text-xl font-black text-white">{{ $performance['avg_response_minutes'] }} min</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Avg resolution</p><p class="mt-2 text-xl font-black text-white">{{ $performance['avg_resolution_hours'] }} hr</p></div>
    </section>

    <div class="mb-5 grid gap-3 sm:grid-cols-2 lg:max-w-lg">
        <div><label class="bc-label" for="st-status-filter">Status</label><select id="st-status-filter" wire:model.live="statusFilter" class="bc-field"><option value="">All</option><option value="open">Open</option><option value="pending">Pending</option><option value="in_progress">In progress</option><option value="resolved">Resolved</option><option value="closed">Closed</option><option value="escalated">Escalated</option></select></div>
        <div><label class="bc-label" for="st-priority-filter">Priority</label><select id="st-priority-filter" wire:model.live="priorityFilter" class="bc-field"><option value="">All</option><option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
    </div>

    <div class="bc-table-wrap">
        <table class="bc-table">
            <thead><tr><th>Subject</th><th>Tenant</th><th>Category</th><th>Priority</th><th>Assigned</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        <td class="font-semibold text-white">{{ $ticket->subject }}</td>
                        <td>{{ $ticket->tenant?->name ?? 'Platform' }}</td>
                        <td class="capitalize">{{ $ticket->category }}</td>
                        <td><span class="capitalize font-semibold {{ match($ticket->priority) { 'urgent' => 'text-rose-300', 'high' => 'text-amber-300', default => 'text-slate-300' } }}">{{ $ticket->priority }}</span></td>
                        <td>{{ $ticket->assignee?->name ?? '—' }}</td>
                        <td>
                            <select wire:change="updateStatus({{ $ticket->id }}, $event.target.value)" class="bc-field text-xs">
                                @foreach(['open', 'pending', 'in_progress', 'resolved', 'closed', 'escalated'] as $status)
                                    <option value="{{ $status }}" @selected($ticket->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="text-right">
                            <div class="flex flex-wrap justify-end gap-3">
                                <select wire:change="assign({{ $ticket->id }}, $event.target.value)" class="bc-field text-xs">
                                    <option value="">Assign agent</option>
                                    @foreach($agents as $agent)<option value="{{ $agent->id }}" @selected($ticket->assigned_to === $agent->id)>{{ $agent->name }}</option>@endforeach
                                </select>
                                <button wire:click="viewDetail({{ $ticket->id }})" class="font-semibold text-teal-300">Open</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-12 text-center text-slate-600">No support tickets match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($tickets->hasPages())<div class="border-t border-white/10 p-4">{{ $tickets->links() }}</div>@endif
    </div>

    @if($viewMode === 'create')
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="cancelForm"></div>
            <div class="bc-panel relative max-h-[90vh] w-full max-w-lg overflow-y-auto p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">New support ticket</h2>
                <form wire:submit="save" class="mt-5 space-y-4">
                    <div><label class="bc-label" for="st-tenant">Tenant (optional)</label><select id="st-tenant" wire:model="tenantId" class="bc-field"><option value="">Platform-level</option>@foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach</select></div>
                    <div><label class="bc-label" for="st-subject">Subject</label><input id="st-subject" wire:model="subject" class="bc-field">@error('subject')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="st-description">Description</label><textarea id="st-description" wire:model="description" rows="4" class="bc-field"></textarea>@error('description')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div><label class="bc-label" for="st-category">Category</label><select id="st-category" wire:model="category" class="bc-field"><option value="billing">Billing</option><option value="technical">Technical</option><option value="network">Network</option><option value="account">Account</option><option value="other">Other</option></select></div>
                        <div><label class="bc-label" for="st-priority">Priority</label><select id="st-priority" wire:model="priority" class="bc-field"><option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
                        <div><label class="bc-label" for="st-sla">SLA (hours)</label><input id="st-sla" wire:model="slaHours" type="number" min="1" class="bc-field" placeholder="e.g. 24">@error('slaHours')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    </div>
                    <div class="flex justify-end gap-3"><button type="button" wire:click="cancelForm" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Create ticket</button></div>
                </form>
            </div>
        </div>
    @endif

    @if($detailTicket)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="closeDetail"></div>
            <div class="bc-panel relative max-h-[85vh] w-full max-w-xl overflow-y-auto p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">{{ $detailTicket->subject }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $detailTicket->tenant?->name ?? 'Platform' }} · {{ ucfirst($detailTicket->category) }} · {{ ucfirst($detailTicket->priority) }}</p>
                <p class="mt-3 text-sm text-slate-300">{{ $detailTicket->description }}</p>
                @if($detailTicket->sla_due_at)<p class="mt-2 text-xs text-amber-300">SLA due {{ $detailTicket->sla_due_at->format('d M Y, H:i') }}</p>@endif

                <h3 class="mt-5 text-xs font-bold uppercase tracking-wide text-slate-400">Replies</h3>
                <ul class="mt-3 space-y-3 text-sm">
                    @forelse($detailTicket->replies as $reply)
                        <li class="border-b border-white/10 pb-3"><div class="font-semibold text-teal-300">{{ $reply->user?->name ?? 'System' }}</div><div class="text-slate-400">{{ $reply->message }}</div><div class="text-xs text-slate-600">{{ $reply->created_at->format('d M Y, H:i') }}</div></li>
                    @empty
                        <li class="py-4 text-center text-slate-600">No replies yet.</li>
                    @endforelse
                </ul>

                <form wire:submit="reply" class="mt-4 space-y-3">
                    <textarea wire:model="replyMessage" rows="3" class="bc-field" placeholder="Write a reply..."></textarea>
                    @error('replyMessage')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror
                    <div class="flex justify-end gap-3"><button type="button" wire:click="closeDetail" class="bc-secondary">Close</button><button type="submit" class="bc-primary">Post reply</button></div>
                </form>
            </div>
        </div>
    @endif
</div>
