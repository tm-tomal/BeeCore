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

    public function test_super_admin_can_create_a_gateway_with_encrypted_credentials(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(PaymentGateways::class)
            ->call('create')
            ->set('name', 'bKash')
            ->set('slug', 'bkash')
            ->set('provider', 'bkash')
            ->set('mode', 'sandbox')
            ->set('credentialsText', "api_key=abc123\napi_secret=xyz789")
            ->set('webhookUrl', 'https://beecore.test/webhooks/bkash')
            ->set('webhookSecret', 'whsec_123')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payment_gateways', ['slug' => 'bkash', 'provider' => 'bkash', 'is_active' => false]);

        $gateway = PaymentGateway::where('slug', 'bkash')->firstOrFail();
        $this->assertSame('abc123', $gateway->credentials['api_key']);
        $this->assertSame('xyz789', $gateway->credentials['api_secret']);

        $raw = DB::table('payment_gateways')->where('id', $gateway->id)->value('credentials');
        $this->assertStringNotContainsString('abc123', $raw);
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
