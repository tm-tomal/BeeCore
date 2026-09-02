<?php

namespace App\Livewire;

use App\Models\Package;
use App\Models\User;
use App\Support\AuthorizesRoles;
use App\Support\CurrentTenant;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Packages extends Component
{
    use AuthorizesRoles, WithPagination;

    public function boot(): void
    {
        $this->authorizeRoles(User::ROLE_SUPER_ADMIN, User::ROLE_TENANT_ADMIN);
    }

    public $viewMode = 'index';
    public $isEditing = false;
    public $packageId;

    public $search = '';

    public $name = '';
    public $price = '';
    public $cost = '';
    public $bandwidth = '';
    public $type = 'shared';
    public $is_active = true;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'bandwidth' => 'nullable|string|max:255',
            'type' => 'required|in:shared,dedicated_ip',
            'is_active' => 'boolean',
        ];
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['name', 'price', 'cost', 'bandwidth', 'type', 'is_active', 'packageId']);
        $this->is_active = true;
        $this->isEditing = false;
        $this->viewMode = 'create';
    }

    public function cancel()
    {
        $this->viewMode = 'index';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function edit($id)
    {
        $this->resetValidation();
        $package = $this->packages()->findOrFail($id);
        $this->packageId = $package->id;
        $this->name = $package->name;
        $this->price = (string) $package->price;
        $this->cost = $package->cost !== null ? (string) $package->cost : '';
        $this->bandwidth = $package->bandwidth;
        $this->type = $package->type;
        $this->is_active = $package->is_active;

        $this->isEditing = true;
        $this->viewMode = 'create';
    }

    public function save()
    {
        $this->validate();

        $tenantId = app(CurrentTenant::class)->id();
        $attributes = [
            'name' => $this->name,
            'price' => $this->price,
            'cost' => $this->cost !== '' ? $this->cost : null,
            'bandwidth' => $this->bandwidth,
            'type' => $this->type,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            $this->packages()->findOrFail($this->packageId)->update($attributes);
        } else {
            Package::create(['tenant_id' => $tenantId] + $attributes);
        }

        $this->viewMode = 'index';
        session()->flash('message', $this->isEditing ? 'Package updated successfully.' : 'Package created successfully.');
    }

    public function toggleStatus($id)
    {
        $package = $this->packages()->findOrFail($id);
        $package->update(['is_active' => ! $package->is_active]);

        session()->flash('message', $package->is_active ? 'Package activated successfully.' : 'Package deactivated successfully.');
    }

    public function delete($id)
    {
        $package = $this->packages()->withCount('subscriptions')->findOrFail($id);

        if ($package->subscriptions_count > 0) {
            session()->flash('message', 'This package is used by subscribers and cannot be deleted. Deactivate it instead.');
            return;
        }

        $package->delete();
        session()->flash('message', 'Package deleted successfully.');
    }

    public function render()
    {
        $tenantId = app(CurrentTenant::class)->id();

        $packages = Package::query()
            ->where('tenant_id', $tenantId)
            ->withCount(['subscriptions as active_subscribers' => fn ($query) => $query->where('status', 'active')])
            ->when($this->search !== '', fn ($query) => $query->where(function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('bandwidth', 'like', '%'.$this->search.'%');
            }))
            ->latest()
            ->paginate(10);

        return view('livewire.packages', [
            'packages' => $packages,
        ]);
    }

    private function packages()
    {
        return Package::query()->where('tenant_id', app(CurrentTenant::class)->id());
    }
}
