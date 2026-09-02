<?php
namespace App\Livewire;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Tenant;
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

    public $viewMode = 'index';
    public $search = '';
    public $methodFilter = '';
    public $invoice_id, $amount, $payment_method = 'cash', $transaction_id;

    protected function rules() {
        return [
            'invoice_id' => ['required', Rule::exists('invoices', 'id')->where('tenant_id', app(CurrentTenant::class)->id())],
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|required_unless:payment_method,cash|string|max:255'
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedMethodFilter(): void
    {
        $this->resetPage();
    }

    public function create() {
        $this->resetValidation();
        $this->reset(['invoice_id', 'amount', 'payment_method', 'transaction_id']);
        $this->payment_method = 'cash';
        $this->viewMode = 'create';
    }

    public function cancel() {
        $this->viewMode = 'index';
    }

    public function save(PaymentAllocator $allocator) {
        $this->validate();
        $tenantId = app(CurrentTenant::class)->id();

        $allocator->allocate($tenantId, (int) $this->invoice_id, (float) $this->amount, $this->payment_method, $this->transaction_id);

        $this->viewMode = 'index';
        session()->flash('message', 'Payment recorded successfully.');
    }

    public function render() {
        $tenantId = app(CurrentTenant::class)->id();
        $today = now()->startOfDay();
        $month = now()->startOfMonth();

        $collectedQuery = fn () => Payment::query()->where('tenant_id', $tenantId)->where('status', 'successful');

        $summary = [
            'collected' => (float) (clone $collectedQuery())->sum('amount'),
            'today' => (float) (clone $collectedQuery())->where('payment_date', '>=', $today)->sum('amount'),
            'month' => (float) (clone $collectedQuery())->where('payment_date', '>=', $month)->sum('amount'),
            'cash' => (float) (clone $collectedQuery())->where('payment_method', 'cash')->sum('amount'),
        ];

        $openInvoices = Invoice::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'overdue'])
            ->with(['customer', 'payments'])
            ->latest()
            ->get();

        $invoices = $openInvoices->map(fn ($invoice) => [
            'value' => (string) $invoice->id,
            'label' => $invoice->invoice_number.' — '.($invoice->customer?->name ?? 'Deleted customer').' (Due ৳'.number_format($invoice->outstanding_amount, 2).')',
        ]);

        return view('livewire.payments', [
            'tenant' => Tenant::query()->find($tenantId),
            'payments' => Payment::query()
                ->where('tenant_id', $tenantId)
                ->with(['customer', 'invoice'])
                ->when($this->search !== '', function ($query) {
                    $query->where(function ($query) {
                        $query->where('transaction_id', 'like', '%'.$this->search.'%')
                            ->orWhere('payment_method', 'like', '%'.$this->search.'%')
                            ->orWhereHas('invoice', fn ($invoice) => $invoice->where('invoice_number', 'like', '%'.$this->search.'%'))
                            ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', '%'.$this->search.'%'));
                    });
                })
                ->when($this->methodFilter !== '', fn ($query) => $query->where('payment_method', $this->methodFilter))
                ->latest()
                ->paginate(10),
            'invoiceOptions' => $invoices,
            'invoices' => $openInvoices,
            'summary' => $summary,
        ]);
    }
}
