<?php
namespace Tests\Feature;

use App\Models\Reseller;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\CreatesPlanSubscriptions;
use Tests\TestCase;

class RemainingPanelsTest extends TestCase {
    use RefreshDatabase, CreatesPlanSubscriptions;

    public function test_all_panels_load(): void {
        $tenant = Tenant::create(['name' => 'Demo', 'slug' => 'demo', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        $this->actingAs($user)->get('/payments')->assertSee('Payments');
        $this->actingAs($user)->get('/network')->assertSee('Network devices');
        $this->actingAs($user)->get('/resellers')->assertSee('Resellers');
        $this->actingAs($user)->get('/reports')->assertSee('Business reports');
        $this->actingAs($user)->get('/settings')->assertSee('ISP settings');
        $this->actingAs($user)->get('/gateway')->assertSee('Customer payment gateway');
        $this->actingAs($user)->get('/subscription')->assertSee('My BeeCore subscription');
    }

    public function test_tenant_admin_can_configure_own_payment_gateway(): void
    {
        $tenant = Tenant::create(['name' => 'Gw', 'slug' => 'gw', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\IspGateway::class)
            ->set('collectionMode', 'own')
            ->set('bkashEnabled', true)
            ->set('bkashNumber', '01700000000')
            ->set('bankEnabled', true)
            ->set('bankDetails', 'Bank A/C 1234')
            ->call('save')
            ->assertHasNoErrors();

        $config = $tenant->fresh()->settings['collection'];
        $this->assertSame('own', $config['mode']);
        $this->assertTrue($config['methods']['bkash']['enabled']);
        $this->assertSame('01700000000', $config['methods']['bkash']['number']);
        $this->assertSame('Bank A/C 1234', $config['methods']['bank']['details']);
    }

    public function test_tenant_sees_recommended_bee_gateway_with_platform_fee(): void
    {
        $tenant = Tenant::create(['name' => 'BeeGw', 'slug' => 'beegw', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);
        \App\Models\SystemSetting::set('bee_gateway_fee_percent', '2');

        Livewire::actingAs($user)
            ->test(\App\Livewire\IspGateway::class)
            ->assertSee('Recommended')
            ->assertSee('Platform fee 2%')
            ->assertDontSee('Bee processing fee');
    }

    public function test_bee_gateway_fee_is_platform_managed_not_tenant_editable(): void
    {
        $tenant = Tenant::create(['name' => 'BeeFee', 'slug' => 'beefee', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);
        \App\Models\SystemSetting::set('bee_gateway_fee_percent', '5');

        Livewire::actingAs($user)
            ->test(\App\Livewire\IspGateway::class)
            ->assertSee('Platform fee 5%')
            ->set('collectionMode', 'bee')
            ->call('save')
            ->assertHasNoErrors();

        $config = $tenant->fresh()->settings['collection'];
        $this->assertSame('bee', $config['mode']);
        $this->assertArrayNotHasKey('bee_fee_percent', $config);
    }

    public function test_tenant_buys_addon_and_payment_verification_activates_it(): void
    {
        $tenant = Tenant::create(['name' => 'AddonCo', 'slug' => 'addonco', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);
        $super = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        $plan = SaasPlan::create([
            'name' => 'Professional', 'slug' => 'professional-'.uniqid(), 'monthly_price' => 2500,
            'yearly_price' => 25000, 'trial_days' => 0, 'grace_days' => 7, 'is_active' => true,
        ]);
        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'saas_plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'price' => 2500,
            'starts_at' => today()->subMonth(),
            'current_period_ends_at' => today()->addMonth(),
            'grace_ends_at' => today()->addDays(30),
            'auto_renew' => true,
        ]);

        $addon = \App\Models\Addon::create([
            'name' => 'SMS Pack', 'slug' => 'sms-pack', 'category' => 'sms',
            'description' => 'Extra SMS credits', 'price' => 500, 'billing_cycle' => 'monthly', 'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\IspAddons::class)
            ->assertSee('Add-on marketplace')
            ->assertSee('Buy now')
            ->call('buy', $addon->id)
            ->assertSet('checkoutAddonId', $addon->id)
            ->set('checkoutGateway', 'manual_transfer')
            ->call('confirmBuy')
            ->assertHasNoErrors();

        $row = \App\Models\TenantAddon::where('tenant_id', $tenant->id)->where('addon_id', $addon->id)->firstOrFail();
        $this->assertSame('pending_approval', $row->status);
        $this->assertTrue((bool) $row->auto_renew);
        $this->assertNotNull($row->period_end);

        $invoice = \App\Models\SaasInvoice::where('tenant_addon_id', $row->id)->firstOrFail();
        $this->assertSame('pending', $invoice->status);
        $this->assertNotNull($invoice->tenant_subscription_id);

        $payment = \App\Models\SaasPayment::where('saas_invoice_id', $invoice->id)->where('status', 'pending')->firstOrFail();

        // BeeCore team verifies the transfer -> add-on activates and invoice is paid.
        Livewire::actingAs($super)
            ->test(\App\Livewire\SaasBilling::class)
            ->call('verifyPayment', $payment->id)
            ->assertHasNoErrors();

        $this->assertSame('active', $row->fresh()->status);
        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_tenant_admin_can_save_workspace_billing_settings(): void
    {
        $tenant = Tenant::create(['name' => 'SettingsCo', 'slug' => 'settingsco', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC', 'contact_address' => 'Dhaka']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\IspSettings::class)
            ->set('name', 'SettingsCo Ltd.')
            ->set('contactAddress', 'Gulshan, Dhaka')
            ->set('graceDays', 3)
            ->set('cutoffDay', 5)
            ->set('autoSuspend', true)
            ->set('autoSuspendDays', 10)
            ->set('invoiceTerms', 'Payment required before suspension.')
            ->call('save')
            ->assertHasNoErrors();

        $fresh = $tenant->fresh();
        $this->assertSame('SettingsCo Ltd.', $fresh->name);
        $this->assertSame(3, $fresh->billingSetting('grace_days', 7));
        $this->assertSame(5, $fresh->billingSetting('cutoff_day', 1));
        $this->assertTrue($fresh->billingSetting('auto_suspend_enabled', false));
        $this->assertSame(10, $fresh->billingSetting('auto_suspend_days', 7));
        $this->assertSame('Payment required before suspension.', $fresh->billingSetting('invoice_terms'));
    }

    public function test_admin_can_create_edit_toggle_and_delete_reseller(): void
    {
        $tenant = Tenant::create(['name' => 'Demo', 'slug' => 'demo', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);
        $this->attachActivePlan($tenant, resellerLimit: 100);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Resellers::class)
            ->call('create')
            ->assertOk()
            ->assertSee('Add reseller')
            ->assertDontSee('Array')
            ->set('name', 'Aziz Traders')
            ->set('email', 'aziz@example.com')
            ->set('phone', '01700000000')
            ->set('status', 'active')
            ->call('save')
            ->assertHasNoErrors();

        $reseller = Reseller::where('tenant_id', $tenant->id)->where('name', 'Aziz Traders')->firstOrFail();
        $this->assertSame('0.00', $reseller->balance);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Resellers::class)
            ->call('edit', $reseller->id)
            ->assertSet('name', 'Aziz Traders')
            ->set('name', 'Aziz Traders & Co.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('resellers', ['id' => $reseller->id, 'name' => 'Aziz Traders & Co.']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Resellers::class)
            ->call('toggleStatus', $reseller->id);

        $this->assertSame('suspended', $reseller->fresh()->status);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Resellers::class)
            ->call('delete', $reseller->id);

        $this->assertDatabaseMissing('resellers', ['id' => $reseller->id]);
    }

    public function test_tenant_admin_can_subscribe_to_an_eligible_plan_and_gets_a_first_invoice(): void
    {
        $tenant = Tenant::create(['name' => 'Manual ISP', 'slug' => 'manual-isp', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC', 'operation_mode' => 'manual']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        $manual = SaasPlan::create([
            'name' => 'Starter', 'slug' => 'starter-manual', 'monthly_price' => 1000, 'yearly_price' => 10000,
            'customer_limit' => 300, 'overflow_rate' => 3, 'is_active' => true, 'operation_mode' => 'manual',
        ]);
        $automatic = SaasPlan::create([
            'name' => 'Auto Pro', 'slug' => 'auto-pro', 'monthly_price' => 2000, 'yearly_price' => 20000,
            'customer_limit' => 1000, 'overflow_rate' => 2, 'is_active' => true, 'operation_mode' => 'automatic',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\IspSubscription::class)
            ->assertViewHas('plans', fn ($plans) => $plans->pluck('id')->contains($manual->id)
                && ! $plans->pluck('id')->contains($automatic->id))
            ->set('selectedPlanId', $manual->id)
            ->set('billingCycle', 'monthly')
            ->call('subscribe')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tenant_subscriptions', [
            'tenant_id' => $tenant->id, 'saas_plan_id' => $manual->id, 'status' => 'active', 'price' => 1000,
        ]);
        $subscription = TenantSubscription::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertDatabaseHas('saas_invoices', [
            'tenant_id' => $tenant->id, 'tenant_subscription_id' => $subscription->id, 'amount' => 1000, 'status' => 'pending',
        ]);
        $this->assertDatabaseHas('tenant_subscription_events', [
            'tenant_subscription_id' => $subscription->id, 'event' => 'subscription.created',
        ]);
    }

    public function test_tenant_admin_cannot_subscribe_to_a_plan_meant_for_another_operation_type(): void
    {
        $tenant = Tenant::create(['name' => 'Manual ISP', 'slug' => 'manual-isp-2', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC', 'operation_mode' => 'manual']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        $automatic = SaasPlan::create([
            'name' => 'Auto Only', 'slug' => 'auto-only', 'monthly_price' => 2000, 'yearly_price' => 20000,
            'customer_limit' => 1000, 'is_active' => true, 'operation_mode' => 'automatic',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\IspSubscription::class)
            ->set('selectedPlanId', $automatic->id)
            ->set('billingCycle', 'monthly')
            ->call('subscribe')
            ->assertHasErrors('selectedPlanId');

        $this->assertDatabaseCount('tenant_subscriptions', 0);
    }

    public function test_tenant_admin_can_switch_to_another_eligible_plan(): void
    {
        $tenant = Tenant::create(['name' => 'Growth ISP', 'slug' => 'growth-isp', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC', 'operation_mode' => 'manual']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        $planA = SaasPlan::create([
            'name' => 'Starter', 'slug' => 'switch-a', 'monthly_price' => 1000, 'yearly_price' => 10000,
            'customer_limit' => 300, 'overflow_rate' => 3, 'is_active' => true, 'operation_mode' => 'manual',
        ]);
        $planB = SaasPlan::create([
            'name' => 'Growth', 'slug' => 'switch-b', 'monthly_price' => 1500, 'yearly_price' => 15000,
            'customer_limit' => 750, 'overflow_rate' => 2.8, 'is_active' => true, 'operation_mode' => 'manual',
        ]);

        $subscription = TenantSubscription::create([
            'tenant_id' => $tenant->id, 'saas_plan_id' => $planA->id, 'status' => 'active',
            'billing_cycle' => 'monthly', 'price' => 1000,
            'starts_at' => today(), 'current_period_ends_at' => today()->addMonth(),
            'auto_renew' => true,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\IspSubscription::class)
            ->set('selectedPlanId', $planB->id)
            ->set('billingCycle', 'monthly')
            ->call('subscribe')
            ->assertHasNoErrors();

        $fresh = $subscription->fresh();
        $this->assertSame($planB->id, $fresh->saas_plan_id);
        $this->assertSame('1500.00', $fresh->price);
        $this->assertDatabaseHas('tenant_subscription_events', [
            'tenant_subscription_id' => $subscription->id, 'event' => 'subscription.plan_changed',
        ]);
        $this->assertDatabaseCount('saas_invoices', 0);
    }

    public function test_tenant_admin_cannot_switch_plan_while_an_invoice_is_unpaid(): void
    {
        $tenant = Tenant::create(['name' => 'Due ISP', 'slug' => 'due-isp', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC', 'operation_mode' => 'manual']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        $planA = SaasPlan::create([
            'name' => 'Starter', 'slug' => 'due-a', 'monthly_price' => 1000, 'yearly_price' => 10000,
            'customer_limit' => 300, 'is_active' => true, 'operation_mode' => 'manual',
        ]);
        $planB = SaasPlan::create([
            'name' => 'Growth', 'slug' => 'due-b', 'monthly_price' => 1500, 'yearly_price' => 15000,
            'customer_limit' => 750, 'is_active' => true, 'operation_mode' => 'manual',
        ]);

        $subscription = TenantSubscription::create([
            'tenant_id' => $tenant->id, 'saas_plan_id' => $planA->id, 'status' => 'active',
            'billing_cycle' => 'monthly', 'price' => 1000,
            'starts_at' => today(), 'current_period_ends_at' => today()->addMonth(),
            'auto_renew' => true,
        ]);
        \App\Models\SaasInvoice::create([
            'tenant_id' => $tenant->id, 'tenant_subscription_id' => $subscription->id,
            'invoice_number' => 'SAAS-DUE-0001', 'status' => 'pending',
            'period_start' => today(), 'period_end' => today()->addMonth(),
            'amount' => 1000, 'due_date' => today()->addDays(5),
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\IspSubscription::class)
            ->set('selectedPlanId', $planB->id)
            ->set('billingCycle', 'monthly')
            ->call('subscribe')
            ->assertHasErrors('selectedPlanId');

        $this->assertSame($planA->id, $subscription->fresh()->saas_plan_id);
    }

    public function test_online_bkash_checkout_creates_only_a_payment_intent_until_confirmed(): void
    {
        $tenant = Tenant::create(['name' => 'Online ISP', 'slug' => 'online-isp', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC', 'operation_mode' => 'manual']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        $plan = SaasPlan::create([
            'name' => 'Starter', 'slug' => 'online-checkout-plan', 'monthly_price' => 1000, 'yearly_price' => 10000,
            'customer_limit' => 300, 'overflow_rate' => 3, 'is_active' => true, 'operation_mode' => 'manual',
        ]);
        $gateway = \App\Models\PaymentGateway::create([
            'name' => 'bKash', 'slug' => 'online-bkash', 'provider' => 'bkash', 'mode' => 'live', 'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\IspSubscription::class)
            ->call('openCheckout', $plan->id)
            ->assertSet('checkoutActive', true)
            ->set('billingCycle', 'monthly')
            ->call('selectGateway', 'gateway:'.$gateway->id)
            ->call('confirmCheckout')
            ->assertHasNoErrors()
            ->assertRedirect();

        // No order rows are written before bKash confirms the payment — a failed
        // attempt therefore leaves nothing behind.
        $this->assertDatabaseCount('tenant_subscriptions', 0);
        $this->assertDatabaseCount('saas_invoices', 0);
        $this->assertDatabaseCount('saas_payments', 0);
        $this->assertDatabaseCount('tenant_subscription_events', 0);

        // The order details ride inside a hosted BeeCore payment intent.
        $intent = \App\Models\BeePaymentIntent::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertSame('saas_plan', $intent->kind);
        $this->assertSame('created', $intent->status);
        $this->assertSame(1000.0, (float) $intent->amount);
        $this->assertTrue($intent->meta['deferred'] ?? false);
        $this->assertSame($plan->id, $intent->meta['saas_plan_id']);
        $this->assertSame('monthly', $intent->meta['billing_cycle']);
    }

    public function test_online_bkash_addon_checkout_creates_only_a_payment_intent(): void
    {
        $tenant = Tenant::create(['name' => 'Addon Online ISP', 'slug' => 'addon-online-isp', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC', 'operation_mode' => 'manual']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        $plan = SaasPlan::create([
            'name' => 'Starter', 'slug' => 'addon-online-plan', 'monthly_price' => 1000, 'yearly_price' => 10000,
            'customer_limit' => 300, 'is_active' => true, 'operation_mode' => 'manual',
        ]);
        TenantSubscription::create([
            'tenant_id' => $tenant->id, 'saas_plan_id' => $plan->id, 'status' => 'active',
            'billing_cycle' => 'monthly', 'price' => 1000, 'starts_at' => today()->subMonth(),
            'current_period_ends_at' => today()->addMonth(), 'auto_renew' => true,
        ]);

        $addon = \App\Models\Addon::create([
            'name' => 'SMS Pack', 'slug' => 'addon-online-sms', 'category' => 'sms',
            'description' => 'Extra SMS credits', 'price' => 500, 'billing_cycle' => 'monthly',
            'usage_limit' => 1000, 'is_active' => true,
        ]);

        $gateway = \App\Models\PaymentGateway::create([
            'name' => 'bKash', 'slug' => 'online-bkash-addon', 'provider' => 'bkash', 'mode' => 'live', 'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\IspAddons::class)
            ->call('buy', $addon->id)
            ->assertSet('checkoutAddonId', $addon->id)
            ->set('checkoutGateway', 'gateway:'.$gateway->id)
            ->call('confirmBuy')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseCount('tenant_addons', 0);
        $this->assertDatabaseCount('saas_invoices', 0);
        $this->assertDatabaseCount('saas_payments', 0);

        $intent = \App\Models\BeePaymentIntent::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertSame('saas_addon', $intent->kind);
        $this->assertSame('created', $intent->status);
        $this->assertSame(500.0, (float) $intent->amount);
        $this->assertTrue($intent->meta['deferred'] ?? false);
        $this->assertSame($addon->id, $intent->meta['addon_id']);
    }

    public function test_manual_checkout_waits_for_beecore_approval(): void
    {
        $tenant = Tenant::create(['name' => 'Manual ISP', 'slug' => 'manual-checkout-isp', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC', 'operation_mode' => 'manual']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        $plan = SaasPlan::create([
            'name' => 'Starter', 'slug' => 'manual-checkout-plan', 'monthly_price' => 1000, 'yearly_price' => 10000,
            'customer_limit' => 300, 'is_active' => true, 'operation_mode' => 'manual',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\IspSubscription::class)
            ->call('openCheckout', $plan->id)
            ->set('billingCycle', 'monthly')
            ->call('selectGateway', 'manual_transfer')
            ->call('confirmCheckout')
            ->assertHasNoErrors();

        $subscription = TenantSubscription::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertSame('pending_approval', $subscription->status);
        $this->assertDatabaseHas('saas_invoices', ['tenant_subscription_id' => $subscription->id, 'status' => 'pending']);
        $this->assertDatabaseHas('saas_payments', ['tenant_id' => $tenant->id, 'status' => 'pending', 'method' => 'bank']);

        // BeeCore Account team verifies the pending payment => plan is activated.
        $admin = User::factory()->create();
        $payment = \App\Models\SaasPayment::where('tenant_id', $tenant->id)->firstOrFail();

        Livewire::actingAs($admin)
            ->test(\App\Livewire\SaasBilling::class)
            ->call('verifyPayment', $payment->id)
            ->assertHasNoErrors();

        $this->assertSame('active', $subscription->fresh()->status);
        $this->assertDatabaseHas('saas_invoices', ['tenant_subscription_id' => $subscription->id, 'status' => 'paid']);
        $this->assertDatabaseHas('tenant_subscription_events', ['tenant_subscription_id' => $subscription->id, 'event' => 'subscription.approved']);
    }

    public function test_tenant_admin_can_open_the_printable_pdf_report_view(): void
    {
        $tenant = Tenant::create(['name' => 'ReportCo', 'slug' => 'reportco', 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        $this->actingAs($user)
            ->get('/reports/print?from='.now()->startOfMonth()->toDateString().'&to='.now()->toDateString())
            ->assertOk()
            ->assertSee('ReportCo')
            ->assertSee('Summary')
            ->assertSee('Print / Save as PDF');
    }

    public function test_marketplace_shows_the_live_sms_wallet_to_the_owner(): void
    {
        $tenant = Tenant::create(['name' => 'WalletCo', 'slug' => 'walletco-'.uniqid(), 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);
        $owner = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        $addon = \App\Models\Addon::create([
            'name' => 'SMS Booster 1k', 'slug' => 'sms-booster-1k-'.uniqid(), 'category' => 'sms',
            'description' => 'Extra SMS credits', 'price' => 300, 'billing_cycle' => 'monthly',
            'usage_limit' => 1000, 'usage_unit' => 'SMS', 'is_active' => true,
        ]);
        \App\Models\TenantAddon::create([
            'tenant_id' => $tenant->id, 'addon_id' => $addon->id, 'status' => 'active',
            'price' => 300, 'billing_cycle' => 'monthly', 'starts_at' => now(), 'auto_renew' => true,
        ]);
        \App\Models\TenantSmsBalance::create(['tenant_id' => $tenant->id, 'balance' => 700]);
        \App\Models\SmsLog::create([
            'tenant_id' => $tenant->id, 'recipient' => '8801711111111', 'message' => 'Due notice',
            'status' => 'sent', 'cost' => 0.35, 'created_at' => now(),
        ]);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\IspAddons::class)
            ->call('setTab', 'my')
            ->assertSet('tab', 'my')
            ->assertSee('SMS wallet')
            ->assertSee('credits left')
            ->assertSee('700')
            ->assertSee('Sent so far')
            ->assertSee('active package');
    }

    public function test_subscription_invoices_live_in_their_own_tab(): void
    {
        $tenant = Tenant::create(['name' => 'SubTabCo', 'slug' => 'subtabco-'.uniqid(), 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC', 'operation_mode' => 'automatic']);
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        $plan = SaasPlan::create([
            'name' => 'Starter', 'slug' => 'subtab-plan-'.uniqid(), 'monthly_price' => 1000, 'yearly_price' => 10000,
            'customer_limit' => 300, 'grace_days' => 7, 'is_active' => true, 'operation_mode' => 'automatic',
        ]);
        $subscription = TenantSubscription::create([
            'tenant_id' => $tenant->id, 'saas_plan_id' => $plan->id, 'status' => 'active',
            'billing_cycle' => 'monthly', 'price' => 1000, 'starts_at' => today()->subMonth(),
            'current_period_ends_at' => today()->addMonth(), 'auto_renew' => true,
        ]);

        $invoice = app(\App\Services\SaasSubscriptionBilling::class)->createInvoiceForPeriod(
            $subscription, today()->subMonth(), today()->addMonth()->subDay(), today()
        );

        Livewire::actingAs($admin)
            ->test(\App\Livewire\IspSubscription::class)
            ->assertSet('tab', 'overview')
            ->call('setTab', 'invoices')
            ->assertSet('tab', 'invoices')
            ->assertSee($invoice->invoice_number)
            ->call('setTab', 'plans')
            ->assertSet('tab', 'plans')
            ->assertDontSee($invoice->invoice_number);
    }

    public function test_report_range_presets_update_the_period(): void
    {
        $tenant = Tenant::create(['name' => 'ReportCo', 'slug' => 'reportco-'.uniqid(), 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);
        $now = now();

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Reports::class)
            ->call('setRange', 'last_month')
            ->assertSet('from', $now->copy()->subMonthNoOverflow()->startOfMonth()->toDateString())
            ->assertSet('to', $now->copy()->subMonthNoOverflow()->endOfMonth()->toDateString())
            ->call('setRange', '30d')
            ->assertSet('from', $now->copy()->subDays(29)->startOfDay()->toDateString())
            ->assertSet('to', $now->copy()->endOfDay()->toDateString());
    }
}
