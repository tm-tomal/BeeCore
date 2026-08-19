<?php

namespace Tests\Feature;

use App\Livewire\SaasPayments;
use App\Models\SaasInvoice;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SaasPaymentsConsoleTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(array $overrides = []): Tenant
    {
        return Tenant::create(array_merge([
            'name' => 'Payments Console ISP', 'slug' => 'payments-console-isp-'.uniqid(), 'status' => 'active',
            'currency' => 'BDT', 'timezone' => 'Asia/Dhaka',
        ], $overrides));
    }

    private function invoice(Tenant $tenant, array $overrides = []): SaasInvoice
    {
        $plan = SaasPlan::create(['name' => 'Starter', 'slug' => 'starter-'.uniqid(), 'monthly_price' => 1000, 'yearly_price' => 10000, 'trial_days' => 0, 'grace_days' => 5, 'is_active' => true]);
        $subscription = TenantSubscription::create([
            'tenant_id' => $tenant->id, 'saas_plan_id' => $plan->id, 'status' => 'past_due',
            'billing_cycle' => 'monthly', 'price' => 1000,
            'starts_at' => today()->subMonth(), 'current_period_ends_at' => today(),
            'grace_ends_at' => today()->addDays(5), 'auto_renew' => true,
        ]);

        return SaasInvoice::create(array_merge([
            'tenant_id' => $tenant->id, 'tenant_subscription_id' => $subscription->id,
            'invoice_number' => 'SAAS-TEST-'.uniqid(), 'status' => 'pending',
            'period_start' => today()->subMonth(), 'period_end' => today(), 'amount' => 1000,
            'due_date' => today(),
        ], $overrides));
    }

    public function test_recording_a_completed_payment_settles_the_invoice_and_reactivates_a_suspended_tenant(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant(['status' => 'suspended']);
        $invoice = $this->invoice($tenant);

        Livewire::actingAs($admin)->test(SaasPayments::class)
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
        $invoice = $this->invoice($tenant);

        Livewire::actingAs($admin)->test(SaasPayments::class)
            ->call('openRecordPayment', $invoice->id)
            ->set('recordAmount', 1000)
            ->set('recordAsPending', true)
            ->call('recordPayment')
            ->assertHasNoErrors();

        $payment = \App\Models\SaasPayment::where('saas_invoice_id', $invoice->id)->firstOrFail();
        $this->assertSame('pending', $payment->status);
        $this->assertDatabaseHas('saas_invoices', ['id' => $invoice->id, 'status' => 'pending']);

        Livewire::actingAs($admin)->test(SaasPayments::class)
            ->call('verifyPayment', $payment->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('saas_payments', ['id' => $payment->id, 'status' => 'completed']);
        $this->assertDatabaseHas('saas_invoices', ['id' => $invoice->id, 'status' => 'paid']);
    }

    public function test_a_pending_payment_can_be_marked_failed(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $invoice = $this->invoice($tenant);

        Livewire::actingAs($admin)->test(SaasPayments::class)
            ->call('openRecordPayment', $invoice->id)
            ->set('recordAmount', 1000)
            ->set('recordAsPending', true)
            ->call('recordPayment');

        $payment = \App\Models\SaasPayment::where('saas_invoice_id', $invoice->id)->firstOrFail();

        Livewire::actingAs($admin)->test(SaasPayments::class)
            ->call('markFailed', $payment->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('saas_payments', ['id' => $payment->id, 'status' => 'failed']);
        $this->assertDatabaseHas('saas_invoices', ['id' => $invoice->id, 'status' => 'pending']);
    }
}
