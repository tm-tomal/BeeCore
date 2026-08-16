<?php
namespace App\Livewire;

use App\Models\Network as NetworkModel;
use App\Models\Tenant;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Network extends Component {
    use WithPagination;
    
    public $showModal = false;
    public $name, $ip_address, $device_type = 'mikrotik', $location, $status = 'online';

    protected $rules = [
        'name' => 'required|string',
        'ip_address' => 'required|ip',
        'device_type' => 'required|string',
        'location' => 'nullable|string',
        'status' => 'required|in:online,offline,maintenance'
    ];

    public function create() {
        $this->reset(['name', 'ip_address', 'device_type', 'location', 'status']);
        $this->showModal = true;
    }

    public function save() {
        $this->validate();
        $tenant = Tenant::first();
        if(!$tenant) return;

        NetworkModel::create([
            'tenant_id' => $tenant->id,
            'name' => $this->name,
            'ip_address' => $this->ip_address,
            'device_type' => $this->device_type,
            'location' => $this->location,
            'status' => $this->status
        ]);
        $this->showModal = false;
        session()->flash('message', 'Network device added successfully.');
    }

    public function render() {
        return view('livewire.network', [
            'devices' => NetworkModel::latest()->paginate(10)
        ]);
    }
}
