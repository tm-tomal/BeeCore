<?php

namespace Tests\Feature;

use App\Livewire\SaasBilling;
use App\Models\SaasInvoice;
use App\Models\SaasInvoiceItem;
use App\Models\SaasPayment;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SaasBillingConsoleTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(array $overrides = []): Tenant
    {
        return Tenant::create(array_merge([
            'name' => 'Billing Console ISP', 'slug' => 'billing-console-isp-'.uniqid(), 'status' => 'active',
            'currency' => 'BDT', 'timezone' => 'Asia/Dhaka',
        ], $overrides));
    }

    private function invoiceWithCharge(Tenant $tenant, string $status = 'pending', array $extra = []): SaasInvoice
    {
        $plan = SaasPlan::create(['name' => 'Starter', 'slug' => 'starter-'.uniqid(), 'monthly_price' => 1000, 'yearly_price' => 10000, 'trial_days' => 0, 'grace_days' => 5, 'is_active' => true]);
        $subscription = TenantSubscription::create(array_merge([
            'tenant_id' => $tenant->id, 'saas_plan_id' => $plan->id, 'status' => 'active',
            'billing_cycle' => 'monthly', 'price' => 1000,
            'starts_at' => today()->subMonth(), 'current_period_ends_at' => today(),
            'grace_ends_at' => today()->addDays(5), 'auto_renew' => true,
        ], $extra['subscription'] ?? []));
        $invoice = SaasInvoice::create([
            'tenant_id' => $tenant->id, 'tenant_subscription_id' => $subscription->id,
            'invoice_number' => 'SAAS-TEST-'.uniqid(), 'status' => $status,
            'period_start' => today()->subMonth(), 'period_end' => today(), 'amount' => 1000,
            'due_date' => today(),
        ]);
        SaasInvoiceItem::create([
            'saas_invoice_id' => $invoice->id, 'type' => 'charge', 'description' => 'Starter subscription (monthly)',
            'amount' => 1000, 'created_at' => now(),
        ]);

        return $invoice;
    }

    public function test_super_admin_can_add_a_discount_and_invoice_total_is_recalculated(): void
    {
        $admin = User::factory()->create();
        $invoice = $this->invoiceWithCharge($this->tenant());

        Livewire::actingAs($admin)->test(SaasBilling::class)
            ->call('openAdjustment', $invoice->id)
            ->set('adjustmentType', 'discount')
            ->set('adjustmentDescription', 'Loyalty discount')
            ->set('adjustmentAmount', 200)
            ->call('addAdjustment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('saas_invoices', ['id' => $invoice->id, 'amount' => 800]);
        $this->assertDatabaseHas('saas_invoice_items', ['saas_invoice_id' => $invoice->id, 'type' => 'discount', 'amount' => -200]);
    }

    public function test_super_admin_can_cancel_a_pending_invoice(): void
    {
        $admin = User::factory()->create();
        $invoice = $this->invoiceWithCharge($this->tenant());

        Livewire::actingAs($admin)->test(SaasBilling::class)
            ->call('cancelInvoice', $invoice->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('saas_invoices', ['id' => $invoice->id, 'status' => 'cancelled']);
    }

    public function test_super_admin_can_send_a_payment_reminder(): void
    {
        $admin = User::factory()->create();
        $invoice = $this->invoiceWithCharge($this->tenant(), 'overdue');

        Livewire::actingAs($admin)->test(SaasBilling::class)
            ->call('sendReminder', $invoice->id)
            ->assertHasNoErrors();

        $invoice->refresh();
        $this->assertNotNull($invoice->reminder_sent_at);
    }

    public function test_super_admin_can_record_a_refund_against_a_payment(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $invoice = $this->invoiceWithCharge($tenant, 'paid');
        $payment = SaasPayment::create([
            'tenant_id' => $tenant->id, 'saas_invoice_id' => $invoice->id, 'recorded_by' => $admin->id,
            'amount' => 1000, 'method' => 'manual', 'paid_at' => now(),
        ]);

        Livewire::actingAs($admin)->test(SaasBilling::class)
            ->call('openRefund', $payment->id)
            ->set('refundAmount', 1000)
            ->set('refundReason', 'Service outage')
            ->call('recordRefund')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('saas_refunds', ['saas_payment_id' => $payment->id, 'amount' => 1000]);
        $this->assertDatabaseHas('saas_invoices', ['id' => $invoice->id, 'status' => 'refunded']);
    }

    public function test_refund_cannot_exceed_payment_amount(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $invoice = $this->invoiceWithCharge($tenant, 'paid');
        $payment = SaasPayment::create([
            'tenant_id' => $tenant->id, 'saas_invoice_id' => $invoice->id, 'recorded_by' => $admin->id,
            'amount' => 1000, 'method' => 'manual', 'paid_at' => now(),
        ]);

        Livewire::actingAs($admin)->test(SaasBilling::class)
            ->call('openRefund', $payment->id)
            ->set('refundAmount', 1500)
            ->call('recordRefund')
            ->assertHasErrors(['refundAmount']);

        $this->assertDatabaseMissing('saas_refunds', ['saas_payment_id' => $payment->id]);
    }

    public function test_super_admin_can_delete_a_payment_and_the_invoice_reopens(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $invoice = $this->invoiceWithCharge($tenant, 'paid');
        $payment = SaasPayment::create([
            'tenant_id' => $tenant->id, 'saas_invoice_id' => $invoice->id, 'recorded_by' => $admin->id,
            'amount' => 1000, 'method' => 'bkash', 'status' => 'completed', 'paid_at' => now(),
        ]);

        Livewire::actingAs($admin)->test(SaasBilling::class)
            ->call('viewInvoice', $invoice->id)
            ->assertSee('Payments & refunds')
            ->call('deletePayment', $payment->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('saas_payments', ['id' => $payment->id]);
        $invoice->refresh();
        $this->assertNotSame('paid', $invoice->status);
        $this->assertNull($invoice->paid_at);
    }

    public function test_super_admin_cannot_delete_a_payment_that_has_refunds(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $invoice = $this->invoiceWithCharge($tenant, 'paid');
        $payment = SaasPayment::create([
            'tenant_id' => $tenant->id, 'saas_invoice_id' => $invoice->id, 'recorded_by' => $admin->id,
            'amount' => 1000, 'method' => 'manual', 'status' => 'completed', 'paid_at' => now(),
        ]);
        \App\Models\SaasRefund::create([
            'tenant_id' => $tenant->id, 'saas_payment_id' => $payment->id, 'amount' => 1000,
            'reason' => 'Outage', 'refunded_by' => $admin->id, 'refunded_at' => now(),
        ]);

        // The action is rejected server-side (abort 422) — the payment stays put.
        Livewire::actingAs($admin)->test(SaasBilling::class)
            ->call('deletePayment', $payment->id);

        $this->assertDatabaseHas('saas_payments', ['id' => $payment->id]);
    }

    public function test_super_admin_can_delete_an_invoice_permanently(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $invoice = $this->invoiceWithCharge($tenant, 'pending');
        SaasPayment::create([
            'tenant_id' => $tenant->id, 'saas_invoice_id' => $invoice->id, 'recorded_by' => $admin->id,
            'amount' => 1000, 'method' => 'bkash', 'status' => 'completed', 'paid_at' => now(),
        ]);

        Livewire::actingAs($admin)->test(SaasBilling::class)
            ->call('deleteInvoice', $invoice->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('saas_invoices', ['id' => $invoice->id]);
        $this->assertDatabaseMissing('saas_payments', ['saas_invoice_id' => $invoice->id]);
    }

    public function test_recording_a_completed_payment_settles_the_invoice_and_reactivates_a_suspended_tenant(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant(['status' => 'suspended']);
        $invoice = $this->invoiceWithCharge($tenant, 'pending', ['subscription' => ['status' => 'past_due']]);

        Livewire::actingAs($admin)->test(SaasBilling::class)
            ->call('openRecordPayment', $invoice->id)
            ->set('recordAmount', 1000)
            ->set('recordMethod', 'bank_transfer')
            ->set('recordReference', 'TXN-1')
            ->call('recordPayment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('saas_payments', ['saas_invoice_id' => $invoice->id, 'status' => 'completed', 'amount' => 1000]);
        $this->assertDatabaseHas('saas_invoices', ['id' => $invoice->id, 'status' => 'paid']);
        $this->assertDatabaseHas('tenants', ['id' => $tenant->id, 'status' => 'active']);
    }

    public function test_pending_payment_requires_verification_before_settling_the_invoice(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $invoice = $this->invoiceWithCharge($tenant, 'pending');

        Livewire::actingAs($admin)->test(SaasBilling::class)
            ->call('openRecordPayment', $invoice->id)
            ->set('recordAmount', 1000)
            ->set('recordAsPending', true)
            ->call('recordPayment')
            ->assertHasNoErrors();

        $payment = SaasPayment::where('saas_invoice_id', $invoice->id)->firstOrFail();
        $this->assertSame('pending', $payment->status);
        $this->assertDatabaseHas('saas_invoices', ['id' => $invoice->id, 'status' => 'pending']);

        Livewire::actingAs($admin)->test(SaasBilling::class)
            ->call('verifyPayment', $payment->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('saas_payments', ['id' => $payment->id, 'status' => 'completed']);
        $this->assertDatabaseHas('saas_invoices', ['id' => $invoice->id, 'status' => 'paid']);
    }

    public function test_a_pending_payment_can_be_marked_failed(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $invoice = $this->invoiceWithCharge($tenant, 'pending');

        Livewire::actingAs($admin)->test(SaasBilling::class)
            ->call('openRecordPayment', $invoice->id)
            ->set('recordAmount', 1000)
            ->set('recordAsPending', true)
            ->call('recordPayment');

        $payment = SaasPayment::where('saas_invoice_id', $invoice->id)->firstOrFail();

        Livewire::actingAs($admin)->test(SaasBilling::class)
            ->call('markFailed', $payment->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('saas_payments', ['id' => $payment->id, 'status' => 'failed']);
        $this->assertDatabaseHas('saas_invoices', ['id' => $invoice->id, 'status' => 'pending']);
    }
}
