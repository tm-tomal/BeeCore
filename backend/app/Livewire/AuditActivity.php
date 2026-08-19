<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Tenant;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class AuditActivity extends Component
{
    use WithPagination;

    public string $search = '';
    public string $tenantId = '';

    public function updated($property): void
    {
        if (in_array($property, ['search', 'tenantId'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        return view('livewire.audit-activity', [
            'logs' => AuditLog::query()
                ->with(['user', 'tenant'])
                ->when($this->search, fn ($query) => $query->where('action', 'like', "%{$this->search}%"))
                ->when($this->tenantId !== '', fn ($query) => $query->where('tenant_id', $this->tenantId))
                ->latest('created_at')
                ->paginate(20),
            'tenants' => Tenant::query()->orderBy('name')->get(),
        ]);
    }
}