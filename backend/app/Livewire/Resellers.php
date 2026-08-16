<?php
namespace App\Livewire;

use App\Models\Reseller;
use App\Models\Tenant;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Resellers extends Component {
    use WithPagination;
    
    public $showModal = false;
    public $name, $email, $phone, $status = 'active';

    protected $rules = [
        'name' => 'required|string',
        'email' => 'nullable|email',
        'phone' => 'nullable|string',
        'status' => 'required|in:active,suspended'
    ];

    public function create() {
        $this->reset(['name', 'email', 'phone', 'status']);
        $this->showModal = true;
    }

    public function save() {
        $this->validate();
        $tenant = Tenant::first();
        if(!$tenant) return;

        Reseller::create([
            'tenant_id' => $tenant->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'balance' => 0
        ]);
        $this->showModal = false;
        session()->flash('message', 'Reseller created successfully.');
    }

    public function render() {
        return view('livewire.resellers', [
            'resellers' => Reseller::latest()->paginate(10)
        ]);
    }
}
