<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Tenant;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Customers extends Component
{
    use WithPagination;

    public $showModal = false;
    public $isEditing = false;
    public $customerId;

    public $name = '';
    public $email = '';
    public $phone = '';
    public $status = 'active';
    public $package_name = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:25',
        'status' => 'required|in:active,suspended,cancelled',
        'package_name' => 'nullable|string|max:255',
    ];

    public function mount()
    {
        // Ensure at least one tenant exists to associate customers with
        if (Tenant::count() === 0) {
            Tenant::create([
                'name' => 'Default Tenant',
                'slug' => 'default-tenant',
                'status' => 'active',
                'currency' => 'BDT',
                'timezone' => 'Asia/Dhaka',
            ]);
        }
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['name', 'email', 'phone', 'status', 'package_name', 'customerId']);
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $customer = Customer::findOrFail($id);
        $this->customerId = $customer->id;
        $this->name = $customer->name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->status = $customer->status;
        $this->package_name = $customer->package_name;
        
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $tenant = Tenant::first();

        if ($this->isEditing) {
            $customer = Customer::findOrFail($this->customerId);
            $customer->update([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'status' => $this->status,
                'package_name' => $this->package_name,
            ]);
        } else {
            Customer::create([
                'tenant_id' => $tenant->id,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'status' => $this->status,
                'package_name' => $this->package_name,
            ]);
        }

        $this->showModal = false;
        session()->flash('message', $this->isEditing ? 'Customer updated successfully.' : 'Customer created successfully.');
    }

    public function delete($id)
    {
        Customer::findOrFail($id)->delete();
        session()->flash('message', 'Customer deleted successfully.');
    }

    public function render()
    {
        return view('livewire.customers', [
            'customers' => Customer::latest()->paginate(10)
        ]);
    }
}
