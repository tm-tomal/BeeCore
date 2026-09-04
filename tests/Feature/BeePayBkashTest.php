<?php

namespace Tests\Feature;

use App\Models\BeePaymentIntent;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\SaasInvoice;
use App\Models\SaasPayment;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\TenantAddon;
use App\Models\TenantSubscription;
use App\Models\TenantSubscriptionEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * End-to-end coverage for the real bKash Tokenized Checkout flow through the
 * hosted BeeCore gateway (customer invoices + BeeCore SaaS invoices).
 */
class BeePayBkashTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function gateway(bool $withCredentials = true): PaymentGateway
    {
        $credentials = $withCredentials ? [
            'app_key' => 'app-key-test',
            'app_secret' => 'app-secret-test',
            'username' => 'api-user',
            'password' => 'api-password',
        ] : [];

        return PaymentGateway::create([
            'name' => 'bKash', 'slug' => 'bkash-'.uniqid(), 'provider' => 'bkash',
            'mode' => 'live', 'is_active' => true, 'credentials' => $credentials,
        ]);
    }

    private function tenant(string $slug): Tenant
    {
        return Tenant::create(['name' => 'BeeCo', 'slug' => $slug, 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC', 'operation_mode' => 'manual']);
    }

    private function fakeBkash(): void
    {
        Http::fake([
            '*token/grant' => Http::response([
                'id_token' => 'bkash-token-123', 'refresh_token' => 'refresh', 'expires_in' => 3600,
            ], 200),
            '*create' => Http::response([
                'paymentID' => 'PAYID-001', 'bkashURL' => 'https://sandbox.bka.sh/pay?token=xyz',
                'statusCode' => '0000', 'statusMessage' => 'Successful',
            ], 200),
            '*execute' => Http::response([
                'paymentID' => 'PAYID-001', 'trxID' => 'TRX-BKASH-001', 'transactionStatus' => 'Completed',
                'amount' => '1000.00', 'statusCode' => '0000', 'statusMessage' => 'Successful',
            ], 200),
            '*payment/status' => Http::response([
                'paymentID' => 'PAYID-001', 'trxID' => 'TRX-BKASH-001', 'transactionStatus' => 'Completed',
                'statusCode' => '0000', 'statusMessage' => 'Successful',
            ], 200),
        ]);
    }

    public function test_customer_invoice_is_collected_through_the_real_bkash_flow(): void
    {
        $this->fakeBkash();
        $gateway = $this->gateway();
        $tenant = $this->tenant('bkash-invoice-isp');
        $customer = Customer::create(['tenant_id' => $tenant->id, 'name' => 'Amin', 'phone' => '01711111111']);

        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-BKASH-1',
            'status' => 'pending',
            'subtotal' => 1000,
            'tax_amount' => 0,
            'total' => 1000,
            'due_date' => today()->addDays(7),
        ]);

        // 1. Customer opens the payment link → hosted BeeCore page.
        $this->get(route('bee-pay.invoice', ['invoice' => $invoice->id]))
            ->assertRedirect();

        $intent = BeePaymentIntent::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertSame('invoice', $intent->kind);
        $this->assertSame('created', $intent->status);

        $this->get(route('bee-pay.intent', ['intent' => $intent->token]))
            ->assertOk()
            ->assertSee('Pay with bKash')
            ->assertSee($intent->merchant_invoice_number);

        // 2. Pay with bKash → gateway is contacted with real credentials and headers.
        $this->post(route('bee-pay.bkash', ['intent' => $intent->token]))
            ->assertRedirect('https://sandbox.bka.sh/pay?token=xyz');

        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/create')
            && $request['merchantInvoiceNumber'] === $intent->merchant_invoice_number
            && $request['amount'] === '1000.00'
            && $request['callbackURL'] === route('bee-pay.callback'));

        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/token/grant')
            && $request->hasHeader('username', 'api-user')
            && $request->hasHeader('password', 'api-password'));

        $this->assertSame('processing', $intent->fresh()->status);
        $this->assertSame('PAYID-001', $intent->fresh()->bkash_payment_id);

        // 3. bKash redirects back after the customer approves → execute + settle.
        $this->get(route('bee-pay.callback', ['paymentID' => 'PAYID-001', 'status' => 'success']))
            ->assertOk()
            ->assertSee('Payment successful')
            ->assertSee('TRX-BKASH-001');

        $this->assertSame('success', $intent->fresh()->status);
        $this->assertSame('TRX-BKASH-001', $intent->fresh()->bkash_trx_id);
        $this->assertSame('paid', $invoice->fresh()->status);

        $payment = Payment::where('invoice_id', $invoice->id)->firstOrFail();
        $this->assertSame('successful', $payment->status);
        $this->assertSame('bkash', $payment->payment_method);
        $this->assertSame('TRX-BKASH-001', $payment->transaction_id);
        $this->assertSame('1000.00', $payment->amount);

        // The printable invoice embeds the same online payment link.
        $invoice->load(['customer', 'items', 'payments' => fn ($q) => $q->where('status', 'successful')]);
        $html = view('billing.invoice-print', ['invoice' => $invoice, 'branding' => null])->render();
        $this->assertStringContainsString('Pay '.$invoice->invoice_number.' online', $html);
        $this->assertStringContainsString(route('bee-pay.invoice', ['invoice' => $invoice->id]), $html);
    }

    public function test_cancelled_bkash_attempt_never_settles_and_stays_retryable(): void
    {
        $this->fakeBkash();
        $this->gateway();
        $tenant = $this->tenant('bkash-cancel-isp');
        $customer = Customer::create(['tenant_id' => $tenant->id, 'name' => 'Amin', 'phone' => '01722222222']);

        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-CANCEL-1',
            'status' => 'pending',
            'subtotal' => 500, 'tax_amount' => 0, 'total' => 500,
            'due_date' => today()->addDays(7),
        ]);

        $intent = BeePaymentIntent::createFor(BeePaymentIntent::KIND_INVOICE, $tenant->id, 500, ['invoice_id' => $invoice->id]);
        $intent->update(['status' => 'processing', 'bkash_payment_id' => 'PAYID-CANCEL']);

        // bKash reports a cancelled payment — nothing was charged.
        $this->get(route('bee-pay.callback', ['paymentID' => 'PAYID-CANCEL', 'status' => 'cancel']))
            ->assertOk()
            ->assertSee('Payment not completed')
            ->assertSee('Try again');

        $intent->refresh();
        $this->assertSame('created', $intent->status);
        $this->assertNull($intent->bkash_payment_id);
        $this->assertSame('pending', $invoice->fresh()->status);
        $this->assertSame(0, Payment::count());

        // The same link still works for a fresh attempt.
        $this->post(route('bee-pay.bkash', ['intent' => $intent->token]))
            ->assertRedirect('https://sandbox.bka.sh/pay?token=xyz');
        $this->assertSame('PAYID-001', $intent->fresh()->bkash_payment_id);
    }

    public function test_bkash_callback_activates_a_pending_saas_subscription(): void
    {
        $this->fakeBkash();
        $this->gateway();
        $tenant = $this->tenant('bkash-saas-plan-isp');

        $plan = SaasPlan::create([
            'name' => 'Starter', 'slug' => 'bkash-plan', 'monthly_price' => 2500, 'yearly_price' => 25000,
            'customer_limit' => 300, 'overflow_rate' => 3, 'grace_days' => 7, 'is_active' => true, 'operation_mode' => 'manual',
        ]);

        $subscription = TenantSubscription::create([
            'tenant_id' => $tenant->id, 'saas_plan_id' => $plan->id, 'status' => 'pending_approval',
            'billing_cycle' => 'monthly', 'price' => 2500, 'starts_at' => today(),
            'current_period_ends_at' => today()->addMonth(), 'auto_renew' => true,
        ]);

        $invoice = SaasInvoice::create([
            'tenant_id' => $tenant->id, 'tenant_subscription_id' => $subscription->id,
            'invoice_number' => 'SAAS-BKASH-1', 'status' => 'pending',
            'period_start' => today()->toDateString(), 'period_end' => today()->addMonth()->toDateString(),
            'amount' => 2500, 'due_date' => today()->addDays(7)->toDateString(),
        ]);

        $payment = SaasPayment::create([
            'tenant_id' => $tenant->id, 'saas_invoice_id' => $invoice->id,
            'recorded_by' => null, 'amount' => 2500, 'method' => 'bkash',
            'reference' => 'BeeCore checkout — bKash', 'status' => 'pending', 'paid_at' => now(),
        ]);

        $intent = BeePaymentIntent::createFor(BeePaymentIntent::KIND_SAAS_PLAN, $tenant->id, 2500, ['saas_invoice_id' => $invoice->id]);
        $intent->update(['status' => 'processing', 'bkash_payment_id' => 'PAYID-001']);

        $this->get(route('bee-pay.callback', ['paymentID' => 'PAYID-001', 'status' => 'success']))
            ->assertOk()
            ->assertSee('Payment successful');

        $this->assertSame('active', $subscription->fresh()->status);
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame('completed', $payment->fresh()->status);
        $this->assertDatabaseHas('tenant_subscription_events', [
            'tenant_subscription_id' => $subscription->id,
            'event' => 'subscription.approved',
        ]);
    }

    public function test_bkash_callback_activates_a_purchased_add_on(): void
    {
        $this->fakeBkash();
        $this->gateway();
        $tenant = $this->tenant('bkash-addon-isp');

        $plan = SaasPlan::create([
            'name' => 'Starter', 'slug' => 'bkash-addon-plan', 'monthly_price' => 2000, 'yearly_price' => 20000,
            'customer_limit' => 300, 'overflow_rate' => 3, 'grace_days' => 7, 'is_active' => true, 'operation_mode' => 'manual',
        ]);

        $subscription = TenantSubscription::create([
            'tenant_id' => $tenant->id, 'saas_plan_id' => $plan->id, 'status' => 'active',
            'billing_cycle' => 'monthly', 'price' => 2000, 'starts_at' => today()->subMonth(),
            'current_period_ends_at' => today()->addMonth(), 'auto_renew' => true,
        ]);

        $addonProduct = \App\Models\Addon::create([
            'name' => 'SMS Pack', 'slug' => 'bkash-sms-pack', 'category' => 'sms', 'description' => 'Extra SMS',
            'price' => 500, 'billing_cycle' => 'monthly', 'is_active' => true,
        ]);

        $addon = TenantAddon::create([
            'tenant_id' => $tenant->id, 'addon_id' => $addonProduct->id, 'status' => 'pending_approval',
            'price' => 500, 'billing_cycle' => 'monthly', 'assigned_by' => null,
            'starts_at' => now(), 'period_start' => today(), 'period_end' => today()->addMonth(), 'auto_renew' => true,
        ]);

        $invoice = SaasInvoice::create([
            'tenant_id' => $tenant->id, 'tenant_subscription_id' => $subscription->id, 'tenant_addon_id' => $addon->id,
            'invoice_number' => 'ADDON-BKASH-1', 'status' => 'pending',
            'period_start' => today()->toDateString(), 'period_end' => today()->addMonth()->toDateString(),
            'amount' => 500, 'due_date' => today()->toDateString(),
        ]);

        SaasPayment::create([
            'tenant_id' => $tenant->id, 'saas_invoice_id' => $invoice->id,
            'recorded_by' => null, 'amount' => 500, 'method' => 'bkash',
            'reference' => 'Add-on checkout — bKash', 'status' => 'pending', 'paid_at' => now(),
        ]);

        $intent = BeePaymentIntent::createFor(BeePaymentIntent::KIND_SAAS_ADDON, $tenant->id, 500, [
            'saas_invoice_id' => $invoice->id, 'tenant_addon_id' => $addon->id,
        ]);
        $intent->update(['status' => 'processing', 'bkash_payment_id' => 'PAYID-001']);

        $this->get(route('bee-pay.callback', ['paymentID' => 'PAYID-001', 'status' => 'success']))
            ->assertOk()
            ->assertSee('Payment successful');

        $this->assertSame('active', $addon->fresh()->status);
        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_bkash_callback_materialises_a_deferred_saas_subscription(): void
    {
        $this->fakeBkash();
        $this->gateway();
        $tenant = $this->tenant('bkash-deferred-plan-isp');

        $plan = SaasPlan::create([
            'name' => 'Starter', 'slug' => 'bkash-deferred-plan', 'monthly_price' => 2500, 'yearly_price' => 25000,
            'customer_limit' => 300, 'overflow_rate' => 3, 'grace_days' => 7, 'is_active' => true, 'operation_mode' => 'manual',
        ]);

        // An online checkout writes nothing until bKash confirms the payment.
        $this->assertDatabaseCount('tenant_subscriptions', 0);
        $this->assertDatabaseCount('saas_invoices', 0);
        $this->assertDatabaseCount('saas_payments', 0);

        $intent = BeePaymentIntent::createFor(BeePaymentIntent::KIND_SAAS_PLAN, $tenant->id, 2500, [
            'deferred' => true, 'saas_plan_id' => $plan->id, 'billing_cycle' => 'monthly',
        ]);
        $intent->update(['status' => 'processing', 'bkash_payment_id' => 'PAYID-001']);

        $this->get(route('bee-pay.callback', ['paymentID' => 'PAYID-001', 'status' => 'success']))
            ->assertOk()
            ->assertSee('Payment successful');

        // Success materialises the subscription, a paid invoice and the payment.
        $subscription = TenantSubscription::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertSame('active', $subscription->status);
        $this->assertSame($plan->id, $subscription->saas_plan_id);
        $this->assertSame('2500.00', $subscription->price);

        $invoice = SaasInvoice::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame('2500.00', $invoice->amount);

        $payment = SaasPayment::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertSame('completed', $payment->status);
        $this->assertSame('bkash', $payment->method);
        $this->assertSame('bKash TRX-BKASH-001', $payment->reference);

        $this->assertDatabaseHas('tenant_subscription_events', [
            'tenant_subscription_id' => $subscription->id,
            'event' => 'subscription.created',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'tenant.subscription.created']);
    }

    public function test_bkash_callback_materialises_a_deferred_add_on_and_credits_sms(): void
    {
        $this->fakeBkash();
        $this->gateway();
        $tenant = $this->tenant('bkash-deferred-addon-isp');

        $plan = SaasPlan::create([
            'name' => 'Starter', 'slug' => 'bkash-deferred-addon-plan', 'monthly_price' => 2000, 'yearly_price' => 20000,
            'customer_limit' => 300, 'overflow_rate' => 3, 'grace_days' => 7, 'is_active' => true, 'operation_mode' => 'manual',
        ]);

        TenantSubscription::create([
            'tenant_id' => $tenant->id, 'saas_plan_id' => $plan->id, 'status' => 'active',
            'billing_cycle' => 'monthly', 'price' => 2000, 'starts_at' => today()->subMonth(),
            'current_period_ends_at' => today()->addMonth(), 'auto_renew' => true,
        ]);

        $addonProduct = \App\Models\Addon::create([
            'name' => 'SMS Pack', 'slug' => 'bkash-deferred-sms-pack', 'category' => 'sms', 'description' => 'Extra SMS',
            'price' => 500, 'billing_cycle' => 'monthly', 'usage_limit' => 1000, 'is_active' => true,
        ]);

        $this->assertDatabaseCount('tenant_addons', 0);
        $this->assertDatabaseCount('saas_invoices', 0);
        $this->assertDatabaseCount('saas_payments', 0);

        $intent = BeePaymentIntent::createFor(BeePaymentIntent::KIND_SAAS_ADDON, $tenant->id, 500, [
            'deferred' => true, 'addon_id' => $addonProduct->id, 'billing_cycle' => 'monthly',
        ]);
        $intent->update(['status' => 'processing', 'bkash_payment_id' => 'PAYID-001']);

        $this->get(route('bee-pay.callback', ['paymentID' => 'PAYID-001', 'status' => 'success']))
            ->assertOk()
            ->assertSee('Payment successful');

        $row = TenantAddon::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertSame('active', $row->status);
        $this->assertSame('500.00', $row->price);

        // An SMS add-on credits the tenant wallet as soon as it is paid.
        $this->assertDatabaseHas('tenant_sms_balances', ['tenant_id' => $tenant->id, 'balance' => 1000]);

        $invoice = SaasInvoice::where('tenant_addon_id', $row->id)->firstOrFail();
        $this->assertSame('paid', $invoice->status);
        $this->assertDatabaseHas('saas_payments', [
            'tenant_id' => $tenant->id, 'saas_invoice_id' => $invoice->id, 'status' => 'completed', 'method' => 'bkash',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'addon.purchased']);
    }

    public function test_cancelled_deferred_saas_attempt_leaves_no_records(): void
    {
        $this->gateway();
        $tenant = $this->tenant('bkash-deferred-cancel-isp');

        $plan = SaasPlan::create([
            'name' => 'Starter', 'slug' => 'bkash-deferred-cancel-plan', 'monthly_price' => 2500, 'yearly_price' => 25000,
            'customer_limit' => 300, 'is_active' => true, 'operation_mode' => 'manual',
        ]);

        $intent = BeePaymentIntent::createFor(BeePaymentIntent::KIND_SAAS_PLAN, $tenant->id, 2500, [
            'deferred' => true, 'saas_plan_id' => $plan->id, 'billing_cycle' => 'monthly',
        ]);
        $intent->update(['status' => 'processing', 'bkash_payment_id' => 'PAYID-CANCEL']);

        // bKash reports the customer cancelled — no money was taken.
        $this->get(route('bee-pay.callback', ['paymentID' => 'PAYID-CANCEL', 'status' => 'cancel']))
            ->assertOk()
            ->assertSee('Payment not completed');

        $this->assertDatabaseCount('tenant_subscriptions', 0);
        $this->assertDatabaseCount('saas_invoices', 0);
        $this->assertDatabaseCount('saas_payments', 0);
        $this->assertDatabaseCount('tenant_subscription_events', 0);

        // The same link stays retryable — nothing order-related was created.
        $intent->refresh();
        $this->assertSame('created', $intent->status);
        $this->assertNull($intent->bkash_payment_id);
    }

    public function test_admin_connection_test_hits_the_real_bkash_token_endpoint(): void
    {
        Http::fake([
            '*token/grant' => Http::response(['id_token' => 'live-token', 'refresh_token' => 'r', 'expires_in' => 3600], 200),
        ]);

        $admin = \App\Models\User::factory()->create();
        $gateway = $this->gateway(withCredentials: true);
        $this->assertTrue($gateway->mode === 'live');

        Livewire::actingAs($admin)
            ->test(\App\Livewire\PaymentGateways::class)
            ->call('testConnection', $gateway->id)
            ->assertHasNoErrors();

        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/token/grant'));
        $this->assertDatabaseHas('payment_gateway_logs', ['payment_gateway_id' => $gateway->id, 'event' => 'connection_test', 'status' => 'success']);
    }
}
