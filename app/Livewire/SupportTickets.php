<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class SupportTickets extends Component
{
    use WithPagination;

    public string $statusFilter = '';
    public string $priorityFilter = '';

    public string $viewMode = 'index';
    public ?int $tenantId = null;
    public string $subject = '';
    public string $description = '';
    public string $category = 'other';
    public string $priority = 'medium';
    public string $slaHours = '';

    public ?int $detailTicketId = null;
    public string $replyMessage = '';

    protected function rules(): array
    {
        return [
            'tenantId' => ['nullable', 'exists:tenants,id'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'category' => ['required', Rule::in(['billing', 'technical', 'network', 'account', 'other'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'slaHours' => ['nullable', 'integer', 'min:1', 'max:720'],
        ];
    }

    public function create(): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $this->reset(['tenantId', 'subject', 'description', 'slaHours']);
        $this->category = 'other';
        $this->priority = 'medium';
        $this->viewMode = 'create';
    }

    public function cancelForm(): void
    {
        $this->viewMode = 'index';
    }

    public function save(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate();

        $ticket = SupportTicket::create([
            'tenant_id' => $data['tenantId'] ?: null,
            'subject' => $data['subject'],
            'description' => $data['description'],
            'category' => $data['category'],
            'priority' => $data['priority'],
            'status' => 'open',
            'created_by' => auth()->id(),
            'sla_due_at' => filled($data['slaHours']) ? now()->addHours((int) $data['slaHours']) : null,
        ]);

        AuditLog::record('support_ticket.created', $ticket, ['priority' => $ticket->priority], tenantId: $ticket->tenant_id);

        $this->viewMode = 'index';
        session()->flash('message', 'Support ticket created.');
    }

    public function assign(int $ticketId, int $userId): void
    {
        $this->assertSuperAdmin();
        $ticket = SupportTicket::findOrFail($ticketId);
        $ticket->update(['assigned_to' => $userId, 'status' => $ticket->status === 'open' ? 'in_progress' : $ticket->status]);
        AuditLog::record('support_ticket.assigned', $ticket, ['assigned_to' => $userId], tenantId: $ticket->tenant_id);
        session()->flash('message', 'Ticket assigned.');
    }

    public function updateStatus(int $ticketId, string $status): void
    {
        $this->assertSuperAdmin();
        abort_unless(in_array($status, ['open', 'pending', 'in_progress', 'resolved', 'closed', 'escalated'], true), 422);

        $ticket = SupportTicket::findOrFail($ticketId);
        $attributes = ['status' => $status];
        if (in_array($status, ['resolved', 'closed'], true) && !$ticket->resolved_at) {
            $attributes['resolved_at'] = now();
        }
        $ticket->update($attributes);

        AuditLog::record('support_ticket.status_changed', $ticket, ['status' => $status], tenantId: $ticket->tenant_id);
        session()->flash('message', 'Ticket status updated.');
    }

    public function viewDetail(int $ticketId): void
    {
        $this->detailTicketId = $ticketId;
        $this->replyMessage = '';
    }

    public function closeDetail(): void
    {
        $this->detailTicketId = null;
    }

    public function reply(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate(['replyMessage' => ['required', 'string', 'max:2000']]);

        $ticket = SupportTicket::findOrFail($this->detailTicketId);

        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $data['replyMessage'],
            'created_at' => now(),
        ]);

        if (!$ticket->first_responded_at) {
            $ticket->update(['first_responded_at' => now()]);
        }

        AuditLog::record('support_ticket.replied', $ticket, tenantId: $ticket->tenant_id);
        $this->replyMessage = '';
        session()->flash('message', 'Reply posted.');

        // Ensure the composer really empties on screen (a Livewire morph can
        // otherwise leave the typed text inside a deferred textarea).
        $this->js('const el = document.getElementById("reply-message"); if (el) el.value = "";');
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPriorityFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->assertSuperAdmin();

        $ticketsQuery = SupportTicket::query();

        $respondedTickets = (clone $ticketsQuery)->whereNotNull('first_responded_at');
        $resolvedTickets = (clone $ticketsQuery)->whereNotNull('resolved_at');

        return view('livewire.support-tickets', [
            'tickets' => SupportTicket::query()->with(['tenant', 'assignee'])
                ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
                ->when($this->priorityFilter, fn ($q) => $q->where('priority', $this->priorityFilter))
                ->latest()->paginate(15),
            'tenants' => Tenant::query()->whereNull('archived_at')->orderBy('name')->get(),
            'agents' => User::query()->whereIn('role', [User::ROLE_SUPPORT, User::ROLE_SUPER_ADMIN])->where('status', 'active')->orderBy('name')->get(),
            'detailTicket' => $this->detailTicketId
                ? SupportTicket::with(['tenant', 'assignee', 'replies.user', 'attachments', 'replies.attachments'])->find($this->detailTicketId)
                : null,
            'performance' => [
                'open' => (clone $ticketsQuery)->whereNotIn('status', ['resolved', 'closed'])->count(),
                'escalated' => (clone $ticketsQuery)->where('status', 'escalated')->count(),
                'avg_response_minutes' => (int) $respondedTickets->get()->avg(fn ($t) => $t->created_at->diffInMinutes($t->first_responded_at)),
                'avg_resolution_hours' => (int) $resolvedTickets->get()->avg(fn ($t) => $t->created_at->diffInHours($t->resolved_at)),
            ],
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
