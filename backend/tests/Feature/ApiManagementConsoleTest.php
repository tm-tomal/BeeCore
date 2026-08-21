<?php

namespace Tests\Feature;

use App\Livewire\ApiManagement;
use App\Models\ApiClient;
use App\Models\User;
use App\Models\Webhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ApiManagementConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_an_api_client_and_token_is_hashed(): void
    {
        $admin = User::factory()->create();

        $component = Livewire::actingAs($admin)->test(ApiManagement::class)
            ->set('clientName', 'Reporting service')
            ->set('rateLimit', 120)
            ->call('createClient')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('api_clients', ['name' => 'Reporting service', 'rate_limit_per_minute' => 120]);

        $token = $component->get('issuedToken');
        $this->assertNotNull($token);

        $client = ApiClient::where('name', 'Reporting service')->firstOrFail();
        $this->assertSame(hash('sha256', $token), $client->token_hash);
    }

    public function test_super_admin_can_revoke_and_delete_an_api_client(): void
    {
        $admin = User::factory()->create();
        $client = ApiClient::create(['name' => 'Legacy client', 'token_hash' => hash('sha256', 'x'), 'rate_limit_per_minute' => 60, 'is_active' => true]);

        $component = Livewire::actingAs($admin)->test(ApiManagement::class)
            ->call('toggleClientActive', $client->id)
            ->assertHasNoErrors();
        $this->assertDatabaseHas('api_clients', ['id' => $client->id, 'is_active' => false]);

        $component->call('deleteClient', $client->id);
        $this->assertDatabaseMissing('api_clients', ['id' => $client->id]);
    }

    public function test_super_admin_can_log_a_test_request_and_a_failure(): void
    {
        $admin = User::factory()->create();
        $client = ApiClient::create(['name' => 'Test client', 'token_hash' => hash('sha256', 'y'), 'rate_limit_per_minute' => 60, 'is_active' => true]);

        Livewire::actingAs($admin)->test(ApiManagement::class)->call('simulateRequest', $client->id);
        $this->assertDatabaseHas('api_client_logs', ['api_client_id' => $client->id, 'is_failed' => false]);

        Livewire::actingAs($admin)->test(ApiManagement::class)->call('simulateRequest', $client->id, true);
        $this->assertDatabaseHas('api_client_logs', ['api_client_id' => $client->id, 'is_failed' => true]);
    }

    public function test_super_admin_can_create_a_webhook_with_encrypted_secret_and_trigger_a_test(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(ApiManagement::class)
            ->call('createWebhook')
            ->set('webhookEvent', 'tenant.subscription.renewed')
            ->set('webhookUrl', 'https://example.com/webhooks')
            ->set('webhookSecret', 'whsec_abc')
            ->call('saveWebhook')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('webhooks', ['event' => 'tenant.subscription.renewed', 'url' => 'https://example.com/webhooks']);
        $webhook = Webhook::where('event', 'tenant.subscription.renewed')->firstOrFail();
        $this->assertSame('whsec_abc', $webhook->secret);

        $raw = DB::table('webhooks')->where('id', $webhook->id)->value('secret');
        $this->assertStringNotContainsString('whsec_abc', $raw);

        Livewire::actingAs($admin)->test(ApiManagement::class)->call('triggerTestWebhook', $webhook->id);
        $this->assertDatabaseHas('webhook_logs', ['webhook_id' => $webhook->id, 'success' => true]);
    }
}
