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

    public $showModal = false;
    public $isEditing = false;
    public $packageId;

    public $name = '';
    public $price = '';
    public $bandwidth = '';
    public $type = 'shared';
    public $is_active = true;

    protected function rules() {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'bandwidth' => 'nullable|string|max:255',
            'type' => 'required|in:shared,dedicated_ip',
            'is_active' => 'boolean',
        ];
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['name', 'price', 'bandwidth', 'type', 'is_active', 'packageId']);
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $package = $this->packages()->findOrFail($id);
        $this->packageId = $package->id;
        $this->name = $package->name;
        $this->price = $package->price;
        $this->bandwidth = $package->bandwidth;
        $this->type = $package->type;
        $this->is_active = $package->is_active;
        
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $tenantId = app(CurrentTenant::class)->id();

        if ($this->isEditing) {
            $this->packages()->findOrFail($this->packageId)->update([
                'name' => $this->name,
                'price' => $this->price,
                'bandwidth' => $this->bandwidth,
                'type' => $this->type,
                'is_active' => $this->is_active,
            ]);
        } else {
            Package::create([
                'tenant_id' => $tenantId,
                'name' => $this->name,
                'price' => $this->price,
                'bandwidth' => $this->bandwidth,
                'type' => $this->type,
                'is_active' => $this->is_active,
            ]);
        }

        $this->showModal = false;
        session()->flash('message', $this->isEditing ? 'Package updated successfully.' : 'Package created successfully.');
    }

    public function delete($id)
    {
        $this->packages()->findOrFail($id)->delete();
        session()->flash('message', 'Package deleted successfully.');
    }

    public function render()
    {
        return view('livewire.packages', [
            'packages' => $this->packages()->latest()->paginate(10)
        ]);
    }

    private function packages()
    {
        return Package::query()->where('tenant_id', app(CurrentTenant::class)->id());
    }
}
