<?php

namespace App\Livewire;

use App\Models\Tenant;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Tenants extends Component
{
    use WithPagination;

    public $showModal = false;
    public $isEditing = false;
    public $tenantId;

    public $name = '';
    public $slug = '';
    public $status = 'active';
    public $currency = 'BDT';
    public $timezone = 'Asia/Dhaka';

    protected function rules() {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tenants,slug' . ($this->isEditing ? ',' . $this->tenantId : ''),
            'status' => 'required|in:active,suspended',
            'currency' => 'required|string|max:10',
            'timezone' => 'required|string|max:50',
        ];
    }

    public function updatedName($value)
    {
        if (!$this->isEditing) {
            $this->slug = Str::slug($value);
        }
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['name', 'slug', 'status', 'currency', 'timezone', 'tenantId']);
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $tenant = Tenant::findOrFail($id);
        $this->tenantId = $tenant->id;
        $this->name = $tenant->name;
        $this->slug = $tenant->slug;
        $this->status = $tenant->status;
        $this->currency = $tenant->currency;
        $this->timezone = $tenant->timezone;
        
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            Tenant::findOrFail($this->tenantId)->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'status' => $this->status,
                'currency' => $this->currency,
                'timezone' => $this->timezone,
            ]);
        } else {
            Tenant::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'status' => $this->status,
                'currency' => $this->currency,
                'timezone' => $this->timezone,
            ]);
        }

        $this->showModal = false;
        session()->flash('message', $this->isEditing ? 'Tenant updated successfully.' : 'Tenant created successfully.');
    }

    public function delete($id)
    {
        Tenant::findOrFail($id)->delete();
        session()->flash('message', 'Tenant deleted successfully.');
    }

    public function render()
    {
        return view('livewire.tenants', [
            'tenants' => Tenant::latest()->paginate(10)
        ]);
    }
}
