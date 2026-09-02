<?php

namespace Tests\Feature;

use App\Livewire\Billing;
use App\Livewire\Payments;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PaymentAllocator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class BillingLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_total_is_derived_from_line_items_and_tax(): void
    {
        [$tenant, $user, $customer] = $this->billingActor('line-items');

        Livewire::actingAs($user)
            ->test(Billing::class)
            ->set('customer_id', $customer->id)
            ->set('status', 'pending')
            ->set('items', [
                ['description' => 'Internet', 'quantity' => 2, 'unit_price' => 500],
                ['description' => 'Static IP', 'quantity' => 1, 'unit_price' => 200],
            ])
            ->set('tax_amount', 60)
            ->set('due_date', now()->addWeek()->format('Y-m-d'))
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::query()->where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertSame('1200.00', (string) $invoice->subtotal);
        $this->assertSame('1260.00', (string) $invoice->total);
        $this->assertCount(2, $invoice->items);
    }

    public function test_partial_and_full_payments_update_outstanding_and_status(): void
    {
        [$tenant, $user, $customer] = $this->billingActor('allocation');
        $invoice = $this->invoice($tenant, $customer, 1000);
        $allocator = app(PaymentAllocator::class);

        $allocator->allocate($tenant->id, $invoice->id, 400, 'cash');
        $invoice->refresh();
        $this->assertSame('pending', $invoice->status);
        $this->assertSame('400.00', $invoice->paid_amount);
        $this->assertSame('600.00', $invoice->outstanding_amount);

        $allocator->allocate($tenant->id, $invoice->id, 600, 'bkash', 'BKASH-1001');
        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame('0.00', $invoice->outstanding_amount);
    }

    public function test_overpayment_is_rejected(): void
    {
        [$tenant, $user, $customer] = $this->billingActor('overpayment');
        $invoice = $this->invoice($tenant, $customer, 500);

        $this->expectException(ValidationException::class);
        app(PaymentAllocator::class)->allocate($tenant->id, $invoice->id, 501, 'cash');
    }

    public function test_duplicate_gateway_transaction_is_rejected(): void
    {
        [$tenant, $user, $customer] = $this->billingActor('duplicate');
        $first = $this->invoice($tenant, $customer, 500, 'INV-DUP-1');
        $second = $this->invoice($tenant, $customer, 500, 'INV-DUP-2');
        $allocator = app(PaymentAllocator::class);
        $allocator->allocate($tenant->id, $first->id, 100, 'bkash', 'TXN-DUP');

        $this->expectException(ValidationException::class);
        $allocator->allocate($tenant->id, $second->id, 100, 'bkash', 'TXN-DUP');
    }

    public function test_payment_cannot_be_allocated_to_another_tenant_invoice(): void
    {
        [$tenant] = $this->billingActor('owner');
        [$otherTenant, $otherUser, $otherCustomer] = $this->billingActor('other');
        $invoice = $this->invoice($otherTenant, $otherCustomer, 500);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        app(PaymentAllocator::class)->allocate($tenant->id, $invoice->id, 100, 'cash');
    }

    public function test_scheduler_marks_only_past_due_pending_invoices_overdue(): void
    {
        [$tenant, $user, $customer] = $this->billingActor('overdue');
        $pastDue = $this->invoice($tenant, $customer, 500, 'INV-PAST');
        $pastDue->update(['due_date' => today()->subDay()]);
        $future = $this->invoice($tenant, $customer, 500, 'INV-FUTURE');

        $this->artisan('billing:mark-overdue')->assertSuccessful();

        $this->assertSame('overdue', $pastDue->fresh()->status);
        $this->assertSame('pending', $future->fresh()->status);
    }

    public function test_finance_user_can_allocate_payment_through_livewire_form(): void
    {
        [$tenant, $user, $customer] = $this->billingActor('payment-form');
        $invoice = $this->invoice($tenant, $customer, 750);

        Livewire::actingAs($user)
            ->test(Payments::class)
            ->set('invoice_id', $invoice->id)
            ->set('amount', 250)
            ->set('payment_method', 'bkash')
            ->set('transaction_id', 'BKASH-LIVEWIRE-1')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', [
            'tenant_id' => $tenant->id,
            'invoice_id' => $invoice->id,
            'customer_id' => $customer->id,
            'amount' => 250,
            'transaction_id' => 'BKASH-LIVEWIRE-1',
        ]);
    }

    public function test_finance_user_can_open_billing_and_payment_create_forms(): void
    {
        [$tenant, $user, $customer] = $this->billingActor('forms');
        $this->invoice($tenant, $customer, 500, 'INV-FORM');

        Livewire::actingAs($user)
            ->test(Billing::class)
            ->call('create')
            ->assertOk()
            ->assertSee('Generate invoice')
            ->assertDontSee('Array');

        Livewire::actingAs($user)
            ->test(Payments::class)
            ->call('create')
            ->assertOk()
            ->assertSee('Record payment')
            ->assertDontSee('Array');
    }

    public function test_invoice_print_page_renders_clean_branded_view(): void
    {
        [$tenant, $user, $customer] = $this->billingActor('print');
        $tenant->update(['settings' => ['collection' => [
            'mode' => 'own',
            'methods' => ['bkash' => ['enabled' => true, 'number' => '01700000000']],
        ]]]);
        $invoice = $this->invoice($tenant, $customer, 500, 'INV-PRINT');

        $this->actingAs($user)
            ->get(route('billing.invoice-print', $invoice))
            ->assertOk()
            ->assertSee('INV-PRINT-'.$tenant->id)
            ->assertSee('Print / Save as PDF')
            ->assertSee(ucfirst('print'))
            ->assertSee('Payment instructions')
            ->assertSee('01700000000');
    }

    public function test_invoice_cannot_be_printed_across_tenants(): void
    {
        [$tenant, $user] = $this->billingActor('owner-print');
        [$otherTenant, $otherUser, $otherCustomer] = $this->billingActor('foreign');
        $invoice = $this->invoice($otherTenant, $otherCustomer, 500, 'INV-FOREIGN');

        $this->actingAs($user)
            ->get(route('billing.invoice-print', $invoice))
            ->assertNotFound();
    }

    private function billingActor(string $slug): array
    {
        $tenant = Tenant::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'status' => 'active',
            'currency' => 'BDT',
            'timezone' => 'UTC',
        ]);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_FINANCE]);
        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => 'Billing Customer',
            'email' => $slug.'@test.com',
            'status' => 'active',
        ]);

        return [$tenant, $user, $customer];
    }

    private function invoice(Tenant $tenant, Customer $customer, float $total, string $number = 'INV-TEST'): Invoice
    {
        return Invoice::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'invoice_number' => $number.'-'.$tenant->id,
            'status' => 'pending',
            'subtotal' => $total,
            'tax_amount' => 0,
            'total' => $total,
            'due_date' => now()->addWeek(),
        ]);
    }
}