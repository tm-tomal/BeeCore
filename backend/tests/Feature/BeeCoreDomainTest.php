<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeeCoreDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_customer_and_invoice_records_can_be_created(): void
    {
        $tenant = Tenant::create([
            'name' => 'Acme ISP',
            'slug' => 'acme-isp',
            'status' => 'active',
            'currency' => 'BDT',
            'timezone' => 'Asia/Dhaka',
        ]);

        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => 'Rahim Ahmed',
            'email' => 'rahim@example.com',
            'phone' => '+8801700000000',
            'status' => 'active',
            'package_name' => '20 Mbps Fiber',
        ]);

        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-2026-001',
            'status' => 'pending',
            'subtotal' => 500.00,
            'tax_amount' => 50.00,
            'total' => 550.00,
            'due_date' => '2026-08-30',
        ]);

        $this->assertDatabaseHas('tenants', ['slug' => 'acme-isp']);
        $this->assertDatabaseHas('customers', ['tenant_id' => $tenant->id, 'email' => 'rahim@example.com']);
        $this->assertDatabaseHas('invoices', ['tenant_id' => $tenant->id, 'customer_id' => $customer->id, 'invoice_number' => 'INV-2026-001']);

        $this->assertEquals($tenant->id, $customer->tenant_id);
        $this->assertEquals($customer->id, $invoice->customer_id);
        $this->assertSame('550.00', (string) $invoice->total);
    }
}
