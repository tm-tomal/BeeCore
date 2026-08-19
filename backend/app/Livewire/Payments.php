<?php
namespace App\Livewire;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\User;
use App\Services\PaymentAllocator;
use App\Support\AuthorizesRoles;
use App\Support\CurrentTenant;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Payments extends Component {
    use AuthorizesRoles, WithPagination;

    public function boot(): void {
        $this->authorizeRoles(User::ROLE_SUPER_ADMIN, User::ROLE_TENANT_ADMIN, User::ROLE_FINANCE);
    }
    
    public $showModal = false;
    public $invoice_id, $amount, $payment_method = 'cash', $transaction_id;
    
    protected function rules() {
        return [
            'invoice_id' => ['required', Rule::exists('invoices', 'id')->where('tenant_id', app(CurrentTenant::class)->id())],
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|required_unless:payment_method,cash|string|max:255'
        ];
    }

    public function create() {
        $this->reset(['invoice_id', 'amount', 'payment_method', 'transaction_id']);
        $this->showModal = true;
    }

    public function save(PaymentAllocator $allocator) {
        $this->validate();
        $tenantId = app(CurrentTenant::class)->id();

        $allocator->allocate($tenantId, (int) $this->invoice_id, (float) $this->amount, $this->payment_method, $this->transaction_id);

        $this->showModal = false;
        session()->flash('message', 'Payment recorded successfully.');
    }

    public function render() {
        return view('livewire.payments', [
            'payments' => Payment::query()->where('tenant_id', app(CurrentTenant::class)->id())->with(['customer', 'invoice'])->latest()->paginate(10),
            'invoices' => Invoice::query()
                ->where('tenant_id', app(CurrentTenant::class)->id())
                ->whereIn('status', ['pending', 'overdue'])
                ->with(['customer', 'payments'])
                ->latest()
                ->get()
        ]);
    }
}
