<?php

namespace App\Livewire;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\User;
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

    public $showModal = false;
    public $isEditing = false;
    public $invoiceId;

    public $customer_id = '';
    public $status = 'draft';
    public $subtotal = 0;
    public $tax_amount = 0;
    public $total = 0;
    public $due_date = '';
    public $items = [];

    protected function rules() {
        $tenantId = app(CurrentTenant::class)->id();

        return [
            'customer_id' => ['required', Rule::exists('customers', 'id')->where('tenant_id', $tenantId)],
            'status' => 'required|in:draft,pending,overdue,cancelled',
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

    private function calculateTotal()
    {
        $this->subtotal = collect($this->items)->sum(
            fn ($item) => (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0)
        );
        $this->total = (float)$this->subtotal + (float)$this->tax_amount;
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
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $invoice = $this->invoices()->with('items')->findOrFail($id);
        abort_if($invoice->payments()->where('status', 'successful')->exists(), 409, 'Invoices with payments cannot be edited.');
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
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();
        $this->calculateTotal();

        $tenantId = app(CurrentTenant::class)->id();

        DB::transaction(function () use ($tenantId) {
            $invoice = $this->isEditing
                ? $this->invoices()->lockForUpdate()->findOrFail($this->invoiceId)
                : new Invoice(['tenant_id' => $tenantId, 'invoice_number' => 'INV-' . strtoupper(Str::random(8))]);

            if ($this->isEditing && $invoice->payments()->where('status', 'successful')->exists()) {
                throw ValidationException::withMessages(['invoiceId' => 'Invoices with payments cannot be edited.']);
            }

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

        $this->showModal = false;
        session()->flash('message', $this->isEditing ? 'Invoice updated successfully.' : 'Invoice generated successfully.');
    }

    public function delete($id)
    {
        $invoice = $this->invoices()->findOrFail($id);
        abort_if($invoice->payments()->exists(), 409, 'Invoices with payments cannot be deleted.');
        $invoice->delete();
        session()->flash('message', 'Invoice deleted successfully.');
    }

    public function render()
    {
        return view('livewire.billing', [
            'invoices' => $this->invoices()->with(['customer', 'payments'])->latest()->paginate(10),
            'customers' => Customer::query()->where('tenant_id', app(CurrentTenant::class)->id())->get(),
        ]);
    }

    private function invoices()
    {
        return Invoice::query()->where('tenant_id', app(CurrentTenant::class)->id());
    }
}
