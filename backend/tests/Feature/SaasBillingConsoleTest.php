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

    private function tenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Billing Console ISP', 'slug' => 'billing-console-isp-'.uniqid(), 'status' => 'active',
            'currency' => 'BDT', 'timezone' => 'Asia/Dhaka',
        ]);
    }

    private function invoiceWithCharge(Tenant $tenant, string $status = 'pending'): SaasInvoice
    {
        $plan = SaasPlan::create(['name' => 'Starter', 'slug' => 'starter-'.uniqid(), 'monthly_price' => 1000, 'yearly_price' => 10000, 'trial_days' => 0, 'grace_days' => 5, 'is_active' => true]);
        $subscription = TenantSubscription::create([
            'tenant_id' => $tenant->id, 'saas_plan_id' => $plan->id, 'status' => 'active',
            'billing_cycle' => 'monthly', 'price' => 1000,
            'starts_at' => today()->subMonth(), 'current_period_ends_at' => today(),
            'grace_ends_at' => today()->addDays(5), 'auto_renew' => true,
        ]);
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
}
