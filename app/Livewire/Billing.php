<?php

namespace App\Livewire;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use App\Services\RecurringInvoiceGenerator;
use App\Support\AuthorizesRoles;
use App\Support\CurrentTenant;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;

#[Layout('components.layouts.app')]
class Billing extends Component
{
    use AuthorizesRoles, WithPagination;

    public function boot(): void
    {
        $this->authorizeRoles(User::ROLE_SUPER_ADMIN, User::ROLE_TENANT_ADMIN, User::ROLE_FINANCE);
    }

    public $viewMode = 'index';
    public $isEditing = false;
    public $invoiceId;

    public $search = '';
    public $statusFilter = '';
    public $viewingInvoice = null;

    public $customer_id = '';
    public $status = 'draft';
    public $subtotal = 0;
    public $tax_amount = 0;
    public $total = 0;
    public $due_date = '';
    public $items = [];

    protected function rules()
    {
        $tenantId = app(CurrentTenant::class)->id();

        return [
            'customer_id' => ['required', Rule::exists('customers', 'id')->where('tenant_id', $tenantId)],
            'status' => 'required|in:draft,pending,overdue,paid,cancelled',
            'tax_amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    public function updatedItems()
    {
        $this->calculateTotal();
    }

    public function updatedTaxAmount()
    {
        $this->calculateTotal();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    private function calculateTotal()
    {
        $this->subtotal = collect($this->items)->sum(
            fn ($item) => (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0)
        );
        $this->total = (float) $this->subtotal + (float) $this->tax_amount;
    }

    public function addItem(): void
    {
        $this->items[] = ['description' => '', 'quantity' => 1, 'unit_price' => 0];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
            $this->calculateTotal();
        }
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['customer_id', 'status', 'subtotal', 'tax_amount', 'total', 'due_date', 'invoiceId', 'items']);
        $this->isEditing = false;
        $this->items = [['description' => 'Internet service', 'quantity' => 1, 'unit_price' => 0]];
        $this->due_date = now()->addDays(7)->format('Y-m-d');
        $this->viewMode = 'create';
    }

    public function cancel()
    {
        $this->viewMode = 'index';
    }

    public function edit($id)
    {
        $this->resetValidation();
        $invoice = $this->invoices()->with('items')->findOrFail($id);
        $this->invoiceId = $invoice->id;
        $this->customer_id = $invoice->customer_id;
        $this->status = $invoice->status;
        $this->subtotal = $invoice->subtotal;
        $this->tax_amount = $invoice->tax_amount;
        $this->total = $invoice->total;
        $this->due_date = $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null;
        $this->items = $invoice->items->map(fn ($item) => [
            'description' => $item->description,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
        ])->all();

        if ($this->items === []) {
            $this->items = [['description' => 'Service', 'quantity' => 1, 'unit_price' => $invoice->subtotal]];
        }

        $this->isEditing = true;
        $this->viewMode = 'create';
    }

    public function viewInvoice($id)
    {
        $this->viewingInvoice = $this->invoices()->with(['customer', 'items', 'payments' => fn ($query) => $query->where('status', 'successful')])->findOrFail($id);
        $this->viewMode = 'view';
    }

    public function closeView(): void
    {
        $this->viewingInvoice = null;
        $this->viewMode = 'index';
    }

    public function save()
    {
        $this->validate();
        $this->calculateTotal();

        $tenantId = app(CurrentTenant::class)->id();

        DB::transaction(function () use ($tenantId) {
            $invoice = $this->isEditing
                ? $this->invoices()->lockForUpdate()->findOrFail($this->invoiceId)
                : new Invoice(['tenant_id' => $tenantId, 'invoice_number' => 'INV-'.strtoupper(Str::random(8))]);

            $invoice->fill([
                'customer_id' => $this->customer_id,
                'status' => $this->status,
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->tax_amount,
                'total' => $this->total,
                'due_date' => $this->due_date,
            ]);
            $invoice->save();

            $invoice->items()->delete();
            $invoice->items()->createMany(collect($this->items)->map(fn ($item) => [
                'tenant_id' => $tenantId,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => round((float) $item['quantity'] * (float) $item['unit_price'], 2),
            ])->all());
        });

        $this->viewMode = 'index';
        session()->flash('message', $this->isEditing ? 'Invoice updated successfully.' : 'Invoice generated successfully.');
    }

    public function generateRecurring(RecurringInvoiceGenerator $generator)
    {
        $tenantId = app(CurrentTenant::class)->id();
        $created = $generator->generateDueForTenant($tenantId);

        session()->flash('message', $created > 0
            ? 'Recurring billing ran — '.$created.' new invoice'.($created === 1 ? '' : 's').' generated. Customers already billed for the current period were skipped.'
            : 'Recurring billing ran — every due subscription is already billed. Nothing new to generate.');
    }

    public function delete($id)
    {
        $invoice = $this->invoices()->findOrFail($id);
        $invoice->delete();
        $this->viewingInvoice = null;
        $this->viewMode = 'index';
        session()->flash('message', 'Invoice deleted successfully.');
    }

    public function render()
    {
        $tenantId = app(CurrentTenant::class)->id();
        $openStatuses = ['draft', 'pending', 'overdue'];

        $openTotals = $this->invoices()
            ->whereIn('status', $openStatuses)
            ->selectRaw('COUNT(*) as invoice_count')
            ->selectRaw('COALESCE(SUM(total), 0) as open_total')
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'overdue' THEN total ELSE 0 END), 0) as overdue_total")
            ->first();

        $paidOnOpen = Payment::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'successful')
            ->whereHas('invoice', fn ($query) => $query->whereIn('status', $openStatuses))
            ->sum('amount');

        $paidOnOverdue = Payment::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'successful')
            ->whereHas('invoice', fn ($query) => $query->where('status', 'overdue'))
            ->sum('amount');

        $collected = Payment::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'successful')
            ->sum('amount');

        $summary = [
            'collected' => (float) $collected,
            'open_count' => (int) $openTotals->invoice_count,
            'outstanding' => max(0, (float) $openTotals->open_total - (float) $paidOnOpen),
            'overdue' => max(0, (float) $openTotals->overdue_total - (float) $paidOnOverdue),
        ];

        $invoices = $this->invoices()
            ->with(['customer', 'payments'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('invoice_number', 'like', '%'.$this->search.'%')
                        ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', '%'.$this->search.'%'));
                });
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);

        $customers = Customer::query()->where('tenant_id', $tenantId)->get();

        $tenant = Tenant::query()->find($tenantId);
        $branding = \App\Models\TenantBranding::query()->where('tenant_id', $tenantId)->first();

        return view('livewire.billing', [
            'invoices' => $invoices,
            'customers' => $customers,
            'tenant' => $tenant,
            'branding' => $branding,
            'customerOptions' => collect(['' => 'Select a customer...'])->union(
                $customers->mapWithKeys(fn ($customer) => [$customer->id => $customer->name.' — '.$customer->email])
            )->map(fn ($label, $value) => ['value' => (string) $value, 'label' => (string) $label])->values()->all(),
            'summary' => $summary,
        ]);
    }

    private function invoices()
    {
        return Invoice::query()->where('tenant_id', app(CurrentTenant::class)->id());
    }
}
