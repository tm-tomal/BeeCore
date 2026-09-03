<?php

namespace App\Livewire;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Tenant;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Announcements extends Component
{
    use WithPagination;

    public string $viewMode = 'index';
    public ?int $announcementId = null;

    public string $title = '';
    public string $body = '';
    public string $type = 'general';
    public ?int $tenantId = null;
    public bool $dashboardChannel = true;
    public bool $emailChannel = false;
    public bool $smsChannel = false;
    public bool $pushChannel = false;
    public string $publishAt = '';

    public string $statusFilter = '';

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
            'type' => ['required', Rule::in(['general', 'maintenance', 'feature', 'payment', 'system'])],
            'tenantId' => ['nullable', 'exists:tenants,id'],
            'publishAt' => ['nullable', 'date'],
        ];
    }

    public function create(): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $this->reset(['announcementId', 'title', 'body', 'tenantId', 'publishAt']);
        $this->type = 'general';
        $this->dashboardChannel = true;
        $this->emailChannel = false;
        $this->smsChannel = false;
        $this->pushChannel = false;
        $this->viewMode = 'create';
    }

    public function edit(int $id): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $a = Announcement::findOrFail($id);
        $this->announcementId = $a->id;
        $this->title = $a->title;
        $this->body = $a->body;
        $this->type = $a->type;
        $this->tenantId = $a->tenant_id;
        $this->dashboardChannel = $a->dashboard_channel;
        $this->emailChannel = $a->email_channel;
        $this->smsChannel = $a->sms_channel;
        $this->pushChannel = $a->push_channel;
        $this->publishAt = $a->publish_at?->format('Y-m-d\TH:i') ?? '';
        $this->viewMode = 'create';
    }

    public function cancelForm(): void
    {
        $this->viewMode = 'index';
    }

    public function saveDraft(): void
    {
        $this->persist('draft');
    }

    public function schedule(): void
    {
        $this->validate(['publishAt' => ['required', 'date', 'after:now']]);
        $this->persist('scheduled');
    }

    public function publishNow(): void
    {
        $this->persist('published', now());
    }

    private function persist(string $status, $publishedAt = null): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate();

        $announcement = $this->announcementId ? Announcement::findOrFail($this->announcementId) : new Announcement(['created_by' => auth()->id()]);
        $announcement->fill([
            'title' => $data['title'],
            'body' => $data['body'],
            'type' => $data['type'],
            'tenant_id' => $data['tenantId'] ?: null,
            'status' => $status,
            'dashboard_channel' => $this->dashboardChannel,
            'email_channel' => $this->emailChannel,
            'sms_channel' => $this->smsChannel,
            'push_channel' => $this->pushChannel,
            'publish_at' => $data['publishAt'] ?: null,
            'published_at' => $publishedAt,
        ])->save();

        AuditLog::record($this->announcementId ? 'announcement.updated' : 'announcement.created', $announcement, ['status' => $status], tenantId: $announcement->tenant_id);

        $this->viewMode = 'index';
        session()->flash('message', 'Announcement '.$status.'.');
    }

    public function unpublish(int $id): void
    {
        $this->assertSuperAdmin();
        $a = Announcement::findOrFail($id);
        $a->update(['status' => 'draft', 'published_at' => null]);
        AuditLog::record('announcement.unpublished', $a, tenantId: $a->tenant_id);
        session()->flash('message', 'Announcement unpublished.');
    }

    public function delete(int $id): void
    {
        $this->assertSuperAdmin();
        $a = Announcement::findOrFail($id);
        $a->delete();
        AuditLog::record('announcement.deleted', null, ['title' => $a->title], tenantId: $a->tenant_id);
        session()->flash('message', 'Announcement deleted.');
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->assertSuperAdmin();

        return view('livewire.announcements', [
            'announcements' => Announcement::query()->with(['tenant', 'creator'])
                ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
                ->latest()->paginate(10),
            'tenants' => Tenant::query()->whereNull('archived_at')->orderBy('name')->get(),
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
