<?php
namespace App\Livewire;

use App\Models\Network as NetworkModel;
use App\Models\User;
use App\Support\AuthorizesRoles;
use App\Support\CurrentTenant;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Network extends Component {
    use AuthorizesRoles, WithPagination;

    public function boot(): void {
        \App\Support\TenantPermissions::assert('network');
    }
    
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
        $tenantId = app(CurrentTenant::class)->id();

        NetworkModel::create([
            'tenant_id' => $tenantId,
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
            'devices' => NetworkModel::query()->where('tenant_id', app(CurrentTenant::class)->id())->latest()->paginate(10)
        ]);
    }
}
