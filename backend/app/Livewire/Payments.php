<?php
namespace App\Livewire;

use App\Models\Payment;
use App\Models\Customer;
use App\Models\Tenant;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Payments extends Component {
    use WithPagination;
    
    public $showModal = false;
    public $customer_id, $amount, $payment_method = 'cash', $transaction_id;
    
    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'amount' => 'required|numeric|min:1',
        'payment_method' => 'required|string',
        'transaction_id' => 'nullable|string'
    ];

    public function create() {
        $this->reset(['customer_id', 'amount', 'payment_method', 'transaction_id']);
        $this->showModal = true;
    }

    public function save() {
        $this->validate();
        $tenant = Tenant::first();
        if(!$tenant) return;

        Payment::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $this->customer_id,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'transaction_id' => $this->transaction_id,
            'payment_date' => now(),
            'status' => 'successful'
        ]);

        $this->showModal = false;
        session()->flash('message', 'Payment recorded successfully.');
    }

    public function render() {
        return view('livewire.payments', [
            'payments' => Payment::with('customer')->latest()->paginate(10),
            'customers' => Customer::all()
        ]);
    }
}
