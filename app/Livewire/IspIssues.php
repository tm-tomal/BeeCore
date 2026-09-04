<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Issue;
use App\Models\Tenant;
use App\Models\User;
use App\Support\AuthorizesRoles;
use App\Support\CurrentTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class IspIssues extends Component
{
    use AuthorizesRoles, WithPagination;

    public string $statusFilter = '';

    public string $categoryFilter = '';

    public string $viewMode = 'index'; // index | create

    // Create form
    public string $subject = '';

    public string $category = Issue::CATEGORY_CONNECTION;

    public string $priority = 'medium';

    public string $description = '';

    public string $reporterName = '';

    public string $reporterPhone = '';

    public ?int $customerId = null;

    public ?int $detailIssueId = null;

    public function boot(): void
    {
        \App\Support\TenantPermissions::assert('issues');
    }

    private function tenantId(): int
    {
        return app(CurrentTenant::class)->id();
    }

    public function createForm(): void
    {
        $this->resetValidation();
        $this->reset(['subject', 'description', 'reporterPhone', 'customerId']);
        $this->category = Issue::CATEGORY_CONNECTION;
        $this->priority = 'medium';
        $this->reporterName = auth()->user()?->name ?? '';
        $this->viewMode = 'create';
    }

    public function cancelForm(): void
    {
        $this->viewMode = 'index';
    }

    public function updatedCustomerId($value): void
    {
        if (! $value) {
            return;
        }
        $customer = Customer::query()->where('tenant_id', $this->tenantId())->find($value);
        if ($customer) {
            $this->reporterName = $customer->name;
            $this->reporterPhone = (string) ($customer->phone ?? '');
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in([Issue::CATEGORY_CONNECTION, Issue::CATEGORY_NETWORK, Issue::CATEGORY_SERVICE, Issue::CATEGORY_BILLING, Issue::CATEGORY_OTHER])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'description' => ['nullable', 'string', 'max:2000'],
            'reporterName' => ['required', 'string', 'max:255'],
            'reporterPhone' => ['nullable', 'string', 'max:30'],
            'customerId' => ['nullable', Rule::exists('customers', 'id')->where('tenant_id', $this->tenantId())],
        ]);

        $issue = Issue::create([
            'tenant_id' => $this->tenantId(),
            'customer_id' => $data['customerId'] ?: null,
            'created_by' => auth()->id(),
            'reporter_name' => $data['reporterName'],
            'reporter_phone' => $data['reporterPhone'] ?: null,
            'subject' => $data['subject'],
            'category' => $data['category'],
            'priority' => $data['priority'],
            'status' => Issue::STATUS_NEW,
            'source' => 'staff',
            'description' => $data['description'] ?: null,
        ]);

        AuditLog::record('issue.created', $issue, ['category' => $issue->category], tenantId: $this->tenantId());

        $this->viewMode = 'index';
        session()->flash('message', __('Issue created.'));
    }

    public function viewIssue(int $id): void
    {
        $this->detailIssueId = $id;
    }

    public function closeDetail(): void
    {
        $this->detailIssueId = null;
    }

    public function updateStatus(int $id, string $status): void
    {
        abort_unless(in_array($status, [Issue::STATUS_NEW, Issue::STATUS_IN_PROGRESS, Issue::STATUS_RESOLVED, Issue::STATUS_CLOSED], true), 422);

        $issue = Issue::query()->where('tenant_id', $this->tenantId())->findOrFail($id);

        $attributes = ['status' => $status];
        if (in_array($status, [Issue::STATUS_RESOLVED, Issue::STATUS_CLOSED], true)) {
            $attributes['resolved_at'] = $issue->resolved_at ?? now();
        } elseif ($issue->status !== Issue::STATUS_NEW) {
            $attributes['resolved_at'] = null;
        }

        $issue->update($attributes);

        AuditLog::record('issue.status_changed', $issue, ['status' => $status], tenantId: $this->tenantId());
        session()->flash('message', __('Issue status updated.'));
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $tenantId = $this->tenantId();
        $base = Issue::query()->where('tenant_id', $tenantId);

        $workspace = Tenant::query()->find($tenantId);

        return view('livewire.isp-issues', [
            'issues' => $base->with(['customer', 'creator'])
                ->when($this->statusFilter, fn (Builder $q) => $q->where('status', $this->statusFilter))
                ->when($this->categoryFilter, fn (Builder $q) => $q->where('category', $this->categoryFilter))
                ->latest()
                ->paginate(12),
            'stats' => [
                'new' => (clone $base)->where('status', Issue::STATUS_NEW)->count(),
                'inProgress' => (clone $base)->where('status', Issue::STATUS_IN_PROGRESS)->count(),
                'resolved' => (clone $base)->whereIn('status', [Issue::STATUS_RESOLVED, Issue::STATUS_CLOSED])->count(),
                'fromCustomers' => (clone $base)->where('source', 'public')->count(),
            ],
            'detailIssue' => $this->detailIssueId
                ? Issue::with(['customer', 'creator'])->where('tenant_id', $tenantId)->find($this->detailIssueId)
                : null,
            'customers' => Customer::query()->where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name', 'phone']),
            'categories' => $this->categoryLabels(),
            'statuses' => $this->statusLabels(),
            'publicUrl' => $workspace ? route('issues.public.report', ['tenant' => $workspace->slug]) : null,
        ]);
    }

    public function categoryLabels(): array
    {
        return [
            Issue::CATEGORY_CONNECTION => __('Connection / internet'),
            Issue::CATEGORY_NETWORK => __('Network problem'),
            Issue::CATEGORY_SERVICE => __('Service / account'),
            Issue::CATEGORY_BILLING => __('Billing / payment'),
            Issue::CATEGORY_OTHER => __('Other'),
        ];
    }

    public function statusLabels(): array
    {
        return [
            Issue::STATUS_NEW => __('New'),
            Issue::STATUS_IN_PROGRESS => __('In progress'),
            Issue::STATUS_RESOLVED => __('Resolved'),
            Issue::STATUS_CLOSED => __('Closed'),
        ];
    }

    public function priorityLabels(): array
    {
        return [
            'low' => __('Low'),
            'medium' => __('Medium'),
            'high' => __('High'),
            'urgent' => __('Urgent'),
        ];
    }
}
