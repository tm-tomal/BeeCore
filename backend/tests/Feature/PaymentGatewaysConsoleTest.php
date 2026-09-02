<?php

namespace Tests\Feature;

use App\Livewire\PaymentGateways;
use App\Models\PaymentGateway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentGatewaysConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_add_a_gateway_with_encrypted_credentials(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(PaymentGateways::class)
            ->call('create')
            ->assertSee('Add payment gateway')
            ->set('name', 'bKash')
            ->set('slug', 'bkash')
            ->set('provider', 'bkash')
            ->set('mode', 'sandbox')
            ->set('credentialsText', "api_key=abc123\napi_secret=xyz789")
            ->set('webhookUrl', 'https://beecore.test/webhooks/bkash')
            ->set('webhookSecret', 'whsec_123')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Payment gateways')
            ->assertSee('bKash');

        $this->assertDatabaseHas('payment_gateways', ['slug' => 'bkash', 'provider' => 'bkash', 'is_active' => false]);

        $gateway = PaymentGateway::where('slug', 'bkash')->firstOrFail();
        $this->assertSame('abc123', $gateway->credentials['api_key']);
        $this->assertSame('xyz789', $gateway->credentials['api_secret']);

        $raw = DB::table('payment_gateways')->where('id', $gateway->id)->value('credentials');
        $this->assertStringNotContainsString('abc123', $raw);
    }

    public function test_presets_prefill_names_and_save_structured_credentials_encrypted(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(PaymentGateways::class)
            ->call('create')
            ->assertSet('provider', 'bkash')
            ->assertSet('name', 'bKash')
            ->assertSet('slug', 'bkash')
            ->call('selectProvider', 'stripe')
            ->assertSet('name', 'Stripe')
            ->assertSet('slug', 'stripe')
            ->set('credentialValues.publishable_key', 'pk_test_123')
            ->set('credentialValues.secret_key', 'sk_test_secret_456')
            ->set('webhookUrl', 'https://beecore.test/webhooks/stripe')
            ->set('webhookSecret', 'whsec_stripe')
            ->call('save')
            ->assertHasNoErrors();

        $gateway = PaymentGateway::where('slug', 'stripe')->firstOrFail();
        $this->assertSame('pk_test_123', $gateway->credentials['publishable_key']);
        $this->assertSame('sk_test_secret_456', $gateway->credentials['secret_key']);

        $raw = DB::table('payment_gateways')->where('id', $gateway->id)->value('credentials');
        $this->assertStringNotContainsString('sk_test_secret_456', $raw);
    }

    public function test_bank_account_preset_does_not_require_a_mode_or_webhook(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(PaymentGateways::class)
            ->call('create')
            ->call('selectProvider', 'bank')
            ->assertSet('name', 'Bank Account')
            ->assertSet('mode', 'live')
            ->set('credentialValues.bank_name', 'Dutch-Bangla Bank')
            ->set('credentialValues.account_name', 'BeeCore Ltd')
            ->set('credentialValues.account_number', '1234567890')
            ->set('credentialValues.branch_name', 'Gulshan')
            ->set('credentialValues.routing_number', '090263343')
            ->call('save')
            ->assertHasNoErrors();

        $gateway = PaymentGateway::where('slug', 'bank-account')->firstOrFail();
        $this->assertSame('Bank Account', $gateway->name);
        $this->assertSame('bank', $gateway->provider);
        $this->assertSame('live', $gateway->mode);
        $this->assertNull($gateway->webhook_url);
        $this->assertSame('Dutch-Bangla Bank', $gateway->credentials['bank_name']);
        $this->assertSame('1234567890', $gateway->credentials['account_number']);
    }

    public function test_super_admin_can_toggle_activation_and_archive_a_gateway(): void
    {
        $admin = User::factory()->create();
        $gateway = PaymentGateway::create(['name' => 'Manual', 'slug' => 'manual', 'provider' => 'manual', 'mode' => 'sandbox', 'is_active' => false]);

        $component = Livewire::actingAs($admin)->test(PaymentGateways::class)
            ->call('toggleActive', $gateway->id)
            ->assertHasNoErrors();
        $this->assertDatabaseHas('payment_gateways', ['id' => $gateway->id, 'is_active' => true]);

        $component->call('archive', $gateway->id)->assertHasNoErrors();
        $gateway->refresh();
        $this->assertFalse((bool) $gateway->is_active);
        $this->assertNotNull($gateway->archived_at);
    }

    public function test_test_connection_logs_success_only_when_gateway_is_active(): void
    {
        $admin = User::factory()->create();
        $gateway = PaymentGateway::create(['name' => 'Manual', 'slug' => 'manual-2', 'provider' => 'manual', 'mode' => 'sandbox', 'is_active' => false]);

        Livewire::actingAs($admin)->test(PaymentGateways::class)
            ->call('testConnection', $gateway->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payment_gateway_logs', ['payment_gateway_id' => $gateway->id, 'status' => 'failed']);

        $gateway->update(['is_active' => true]);

        Livewire::actingAs($admin)->test(PaymentGateways::class)
            ->call('testConnection', $gateway->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payment_gateway_logs', ['payment_gateway_id' => $gateway->id, 'status' => 'success']);
    }
}
