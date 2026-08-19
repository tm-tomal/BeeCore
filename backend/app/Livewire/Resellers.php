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
        $tenantId = app(CurrentTenant::class)->id();

        Reseller::create([
            'tenant_id' => $tenantId,
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
            'resellers' => Reseller::query()->where('tenant_id', app(CurrentTenant::class)->id())->latest()->paginate(10)
        ]);
    }
}
