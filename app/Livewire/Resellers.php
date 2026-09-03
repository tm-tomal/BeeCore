<?php
namespace App\Livewire;

use App\Models\Reseller;
use App\Models\User;
use App\Support\AuthorizesRoles;
use App\Support\CurrentTenant;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Resellers extends Component {
    use AuthorizesRoles, WithPagination;

    public function boot(): void {
        $this->authorizeRoles(User::ROLE_SUPER_ADMIN, User::ROLE_TENANT_ADMIN);
    }

    public $viewMode = 'index';
    public $isEditing = false;
    public $resellerId;
    public $search = '';

    public $name, $email, $phone, $status = 'active';

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:30',
        'status' => 'required|in:active,suspended'
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create() {
        $this->resetValidation();
        $this->reset(['name', 'email', 'phone', 'status', 'resellerId']);
        $this->status = 'active';
        $this->isEditing = false;
        $this->viewMode = 'create';
    }

    public function cancel() {
        $this->viewMode = 'index';
    }

    public function edit($id) {
        $this->resetValidation();
        $reseller = $this->resellers()->findOrFail($id);
        $this->resellerId = $reseller->id;
        $this->name = $reseller->name;
        $this->email = $reseller->email;
        $this->phone = $reseller->phone;
        $this->status = $reseller->status;
        $this->isEditing = true;
        $this->viewMode = 'create';
    }

    public function save() {
        $this->validate();
        $tenantId = app(CurrentTenant::class)->id();

        $attributes = [
            'name' => $this->name,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'status' => $this->status,
        ];

        if ($this->isEditing) {
            $this->resellers()->findOrFail($this->resellerId)->update($attributes);
        } else {
            Reseller::create($attributes + ['tenant_id' => $tenantId, 'balance' => 0]);
        }

        $this->viewMode = 'index';
        session()->flash('message', $this->isEditing ? 'Reseller updated successfully.' : 'Reseller created successfully.');
    }

    public function toggleStatus($id) {
        $reseller = $this->resellers()->findOrFail($id);
        $reseller->update(['status' => $reseller->status === 'active' ? 'suspended' : 'active']);
        session()->flash('message', $reseller->status === 'active' ? 'Reseller activated successfully.' : 'Reseller suspended successfully.');
    }

    public function delete($id) {
        $this->resellers()->findOrFail($id)->delete();
        session()->flash('message', 'Reseller deleted successfully.');
    }

    public function render() {
        $tenantId = app(CurrentTenant::class)->id();
        $resellers = $this->resellers()
            ->when($this->search !== '', fn ($query) => $query->where(function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('phone', 'like', '%'.$this->search.'%');
            }))
            ->latest()
            ->paginate(10);

        $summary = [
            'total' => Reseller::query()->where('tenant_id', $tenantId)->count(),
            'active' => Reseller::query()->where('tenant_id', $tenantId)->where('status', 'active')->count(),
            'suspended' => Reseller::query()->where('tenant_id', $tenantId)->where('status', 'suspended')->count(),
            'balance' => (float) Reseller::query()->where('tenant_id', $tenantId)->sum('balance'),
        ];

        return view('livewire.resellers', [
            'resellers' => $resellers,
            'summary' => $summary,
        ]);
    }

    private function resellers() {
        return Reseller::query()->where('tenant_id', app(CurrentTenant::class)->id());
    }
}
