<?php

namespace App\Livewire;

use App\Models\Package;
use App\Models\Tenant;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Packages extends Component
{
    use WithPagination;

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
        $package = Package::findOrFail($id);
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

        $tenant = Tenant::first();

        if ($this->isEditing) {
            Package::findOrFail($this->packageId)->update([
                'name' => $this->name,
                'price' => $this->price,
                'bandwidth' => $this->bandwidth,
                'type' => $this->type,
                'is_active' => $this->is_active,
            ]);
        } else {
            Package::create([
                'tenant_id' => $tenant->id,
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
        Package::findOrFail($id)->delete();
        session()->flash('message', 'Package deleted successfully.');
    }

    public function render()
    {
        return view('livewire.packages', [
            'packages' => Package::latest()->paginate(10)
        ]);
    }
}
