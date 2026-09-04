<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\SmsProvider;
use App\Models\Tenant;
use App\Models\TenantAddon;
use App\Models\TenantSmsBalance;
use App\Services\SmsGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsGatewayTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::create([
            'name' => 'SMS ISP', 'slug' => 'sms-gw-'.uniqid(), 'status' => 'active',
            'currency' => 'BDT', 'timezone' => 'Asia/Dhaka',
        ]);
    }

    private function activeProvider(): SmsProvider
    {
        return SmsProvider::create([
            'name' => 'smsq.global', 'slug' => 'smsq-'.uniqid(), 'provider' => 'smsq',
            'sender_id' => '8809617602056', 'price_per_sms' => 0.35, 'is_active' => true,
            'credentials' => ['api_key' => 'key', 'client_id' => 'client'],
        ]);
    }

    public function test_tenant_sms_send_debits_wallet_and_logs_success(): void
    {
        $tenant = $this->tenant();
        $this->activeProvider();
        TenantSmsBalance::create(['tenant_id' => $tenant->id, 'balance' => 10]);

        Http::fake([
            'https://api.smsq.global/api/v2/SendSMS' => Http::response(['ErrorCode' => 0, 'ErrorDescription' => 'Success', 'Data' => []]),
        ]);

        $result = SmsGateway::sendForTenant($tenant, '01712345678', 'Hello from BeeCore');

        $this->assertTrue($result['ok']);
        $this->assertSame(9, TenantSmsBalance::where('tenant_id', $tenant->id)->first()->balance);
        $this->assertDatabaseHas('sms_logs', [
            'tenant_id' => $tenant->id,
            'recipient' => '8801712345678',
            'status' => 'sent',
        ]);
    }

    public function test_tenant_sms_send_is_blocked_without_credits(): void
    {
        $tenant = $this->tenant();
        $this->activeProvider();

        $result = SmsGateway::sendForTenant($tenant, '01712345678', 'Hello');

        $this->assertFalse($result['ok']);
        $this->assertSame('insufficient', $result['reason']);
        $this->assertDatabaseMissing('sms_logs', ['tenant_id' => $tenant->id]);
    }

    public function test_sms_addon_activation_credits_the_tenant_wallet(): void
    {
        $tenant = $this->tenant();
        $addon = Addon::create([
            'name' => 'SMS Booster 5k', 'slug' => 'sms-booster-5k', 'category' => 'sms',
            'price' => 500, 'billing_cycle' => 'monthly', 'usage_limit' => 5000, 'usage_unit' => 'SMS', 'is_active' => true,
        ]);
        $assignment = TenantAddon::create([
            'tenant_id' => $tenant->id, 'addon_id' => $addon->id, 'status' => 'active',
            'price' => 500, 'billing_cycle' => 'monthly', 'starts_at' => now(),
        ]);

        SmsGateway::creditSmsAddon($assignment);

        $this->assertDatabaseHas('tenant_sms_balances', ['tenant_id' => $tenant->id, 'balance' => 5000]);
    }

    public function test_non_sms_addon_does_not_touch_the_wallet(): void
    {
        $tenant = $this->tenant();
        $addon = Addon::create([
            'name' => 'Extra Storage', 'slug' => 'extra-storage', 'category' => 'storage',
            'price' => 300, 'billing_cycle' => 'monthly', 'usage_limit' => 50000, 'usage_unit' => 'MB', 'is_active' => true,
        ]);
        $assignment = TenantAddon::create([
            'tenant_id' => $tenant->id, 'addon_id' => $addon->id, 'status' => 'active',
            'price' => 300, 'billing_cycle' => 'monthly', 'starts_at' => now(),
        ]);

        SmsGateway::creditSmsAddon($assignment);

        $this->assertDatabaseMissing('tenant_sms_balances', ['tenant_id' => $tenant->id]);
    }
}
