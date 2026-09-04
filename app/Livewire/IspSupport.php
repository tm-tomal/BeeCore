<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use App\Support\AuthorizesRoles;
use App\Support\CurrentTenant;
use App\Support\FileAttachments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class IspSupport extends Component
{
    use AuthorizesRoles, WithFileUploads, WithPagination;

    public string $statusFilter = '';

    public string $priorityFilter = '';

    public string $viewMode = 'index'; // index | create | detail

    public string $subject = '';

    public string $description = '';

    public string $category = 'other';

    public string $priority = 'medium';

    public ?int $detailTicketId = null;

    public string $replyMessage = '';

    /** @var array<int, mixed> */
    public array $files = [];

    public function boot(): void
    {
        \App\Support\TenantPermissions::assert('support');
    }

    private function tenantId(): int
    {
        return app(CurrentTenant::class)->id();
    }

    /* ---------- Create ---------- */

    public function createForm(): void
    {
        $this->resetValidation();
        $this->reset(['subject', 'description', 'files']);
        $this->category = 'other';
        $this->priority = 'medium';
        $this->viewMode = 'create';
    }

    public function cancelForm(): void
    {
        $this->files = [];
        $this->viewMode = 'index';
    }

    public function removeFile(int $index): void
    {
        unset($this->files[$index]);
        $this->files = array_values($this->files);
    }

    public function save(): void
    {
        $data = $this->validate(array_merge([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'category' => ['required', Rule::in(['billing', 'technical', 'network', 'account', 'other'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
        ], FileAttachments::uploadRules()));

        $ticket = SupportTicket::create([
            'tenant_id' => $this->tenantId(),
            'subject' => $data['subject'],
            'description' => $data['description'],
            'category' => $data['category'],
            'priority' => $data['priority'],
            'status' => 'open',
            'created_by' => auth()->id(),
        ]);

        if (! empty($this->files)) {
            FileAttachments::attach($ticket, array_values($this->files), $this->tenantId(), auth()->id());
        }

        AuditLog::record('support_ticket.created', $ticket, ['priority' => $ticket->priority], tenantId: $this->tenantId());

        $this->files = [];
        $this->viewMode = 'index';
        session()->flash('message', __('Support ticket sent to the BeeCore team.'));
    }

    /* ---------- Detail & replies ---------- */

    public function viewDetail(int $ticketId): void
    {
        $this->detailTicketId = $ticketId;
        $this->replyMessage = '';
        $this->files = [];
        $this->viewMode = 'detail';
    }

    public function closeDetail(): void
    {
        $this->detailTicketId = null;
        $this->files = [];
        $this->viewMode = 'index';
    }

    public function reply(): void
    {
        $data = $this->validate(array_merge(
            ['replyMessage' => ['required', 'string', 'max:2000']],
            FileAttachments::uploadRules()
        ));

        $ticket = $this->scopedTickets()->findOrFail($this->detailTicketId);

        $reply = SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $data['replyMessage'],
            'created_at' => now(),
        ]);

        if (! empty($this->files)) {
            FileAttachments::attach($reply, array_values($this->files), (int) $ticket->tenant_id, auth()->id());
        }

        if (in_array($ticket->status, ['resolved', 'closed'], true)) {
            $ticket->update(['status' => 'open']);
        }

        AuditLog::record('support_ticket.replied', $ticket, tenantId: $this->tenantId());
        $this->replyMessage = '';
        $this->files = [];
        session()->flash('message', __('Reply posted.'));

        // Ensure the composer really empties on screen (a Livewire morph can
        // otherwise leave the typed text inside a deferred textarea).
        $this->js('const el = document.getElementById("reply-message"); if (el) el.value = "";');
    }

    public function updateStatus(int $ticketId, string $status): void
    {
        abort_unless(in_array($status, ['open', 'pending', 'in_progress', 'resolved', 'closed'], true), 422);

        $ticket = $this->scopedTickets()->findOrFail($ticketId);

        $attributes = ['status' => $status];
        if (in_array($status, ['resolved', 'closed'], true) && ! $ticket->resolved_at) {
            $attributes['resolved_at'] = now();
        }
        $ticket->update($attributes);

        AuditLog::record('support_ticket.status_changed', $ticket, ['status' => $status], tenantId: $this->tenantId());
        session()->flash('message', __('Ticket status updated.'));
    }

    /* ---------- Filters ---------- */

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPriorityFilter(): void
    {
        $this->resetPage();
    }

    private function scopedTickets(): Builder
    {
        return SupportTicket::query()->where('tenant_id', $this->tenantId());
    }

    /* ---------- Render ---------- */

    public function render()
    {
        $baseQuery = SupportTicket::query()->where('tenant_id', $this->tenantId());

        return view('livewire.isp-support', [
            'tickets' => $baseQuery->withCount('replies')->with(['assignee', 'creator', 'replies.user'])
                ->when($this->statusFilter, fn (Builder $q) => $q->where('status', $this->statusFilter))
                ->when($this->priorityFilter, fn (Builder $q) => $q->where('priority', $this->priorityFilter))
                ->latest()
                ->paginate(10),
            'stats' => [
                'open' => (clone $baseQuery)->whereNotIn('status', ['resolved', 'closed'])->count(),
                'escalated' => (clone $baseQuery)->where('status', 'escalated')->count(),
                'assigned' => (clone $baseQuery)->whereNotNull('assigned_to')->whereNotIn('status', ['resolved', 'closed'])->count(),
                'resolved' => (clone $baseQuery)->whereIn('status', ['resolved', 'closed'])->count(),
            ],
            'detailTicket' => $this->detailTicketId
                ? SupportTicket::with(['assignee', 'creator', 'replies.user', 'attachments', 'replies.attachments'])->where('tenant_id', $this->tenantId())->find($this->detailTicketId)
                : null,
            'statuses' => ['open', 'pending', 'in_progress', 'resolved', 'closed', 'escalated'],
            'statusLabels' => [
                'open' => __('Open'),
                'pending' => __('Pending'),
                'in_progress' => __('In progress'),
                'resolved' => __('Resolved'),
                'closed' => __('Closed'),
                'escalated' => __('Escalated'),
            ],
            'priorityLabels' => [
                'low' => __('Low'),
                'medium' => __('Medium'),
                'high' => __('High'),
                'urgent' => __('Urgent'),
            ],
            'categoryLabels' => [
                'billing' => __('Billing'),
                'technical' => __('Technical'),
                'network' => __('Network'),
                'account' => __('Account'),
                'other' => __('Other'),
            ],
        ]);
    }
}
