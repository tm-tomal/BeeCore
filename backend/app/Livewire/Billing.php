<?php

namespace App\Livewire;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Tenant;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;

#[Layout('components.layouts.app')]
class Billing extends Component
{
    use WithPagination;

    public $showModal = false;
    public $isEditing = false;
    public $invoiceId;

    public $customer_id = '';
    public $status = 'draft';
    public $subtotal = 0;
    public $tax_amount = 0;
    public $total = 0;
    public $due_date = '';

    protected function rules() {
        return [
            'customer_id' => 'required|exists:customers,id',
            'status' => 'required|in:draft,pending,paid,overdue,cancelled',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'due_date' => 'required|date',
        ];
    }

    public function updatedSubtotal()
    {
        $this->calculateTotal();
    }

    public function updatedTaxAmount()
    {
        $this->calculateTotal();
    }

    private function calculateTotal()
    {
        $this->total = (float)$this->subtotal + (float)$this->tax_amount;
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['customer_id', 'status', 'subtotal', 'tax_amount', 'total', 'due_date', 'invoiceId']);
        $this->isEditing = false;
        
        $this->due_date = now()->addDays(7)->format('Y-m-d');
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $invoice = Invoice::findOrFail($id);
        $this->invoiceId = $invoice->id;
        $this->customer_id = $invoice->customer_id;
        $this->status = $invoice->status;
        $this->subtotal = $invoice->subtotal;
        $this->tax_amount = $invoice->tax_amount;
        $this->total = $invoice->total;
        $this->due_date = $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null;
        
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $tenant = Tenant::first();
        if (!$tenant) {
            session()->flash('message', 'No tenant found. Please create a tenant first.');
            return;
        }

        if ($this->isEditing) {
            Invoice::findOrFail($this->invoiceId)->update([
                'customer_id' => $this->customer_id,
                'status' => $this->status,
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->tax_amount,
                'total' => $this->total,
                'due_date' => $this->due_date,
            ]);
        } else {
            Invoice::create([
                'tenant_id' => $tenant->id,
                'customer_id' => $this->customer_id,
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'status' => $this->status,
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->tax_amount,
                'total' => $this->total,
                'due_date' => $this->due_date,
            ]);
        }

        $this->showModal = false;
        session()->flash('message', $this->isEditing ? 'Invoice updated successfully.' : 'Invoice generated successfully.');
    }

    public function delete($id)
    {
        Invoice::findOrFail($id)->delete();
        session()->flash('message', 'Invoice deleted successfully.');
    }

    public function render()
    {
        return view('livewire.billing', [
            'invoices' => Invoice::with('customer')->latest()->paginate(10),
            'customers' => Customer::all(),
        ]);
    }
}
