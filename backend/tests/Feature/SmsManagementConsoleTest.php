<?php

namespace Tests\Feature;

use App\Livewire\SmsManagement;
use App\Models\SmsProvider;
use App\Models\SmsTemplate;
use App\Models\Tenant;
use App\Models\TenantSmsBalance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class SmsManagementConsoleTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::create([
            'name' => 'SMS ISP', 'slug' => 'sms-isp-'.uniqid(), 'status' => 'active',
            'currency' => 'BDT', 'timezone' => 'Asia/Dhaka',
        ]);
    }

    public function test_super_admin_can_create_a_provider_with_encrypted_credentials(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(SmsManagement::class)
            ->call('createProvider')
            ->set('name', 'SSL Wireless')
            ->set('slug', 'ssl-wireless')
            ->set('provider', 'ssl_wireless')
            ->set('senderId', 'BEECORE')
            ->set('pricePerSms', 0.35)
            ->set('credentialsText', "api_key=abc\napi_secret=xyz")
            ->call('saveProvider')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sms_providers', ['slug' => 'ssl-wireless', 'sender_id' => 'BEECORE']);
        $provider = SmsProvider::where('slug', 'ssl-wireless')->firstOrFail();
        $this->assertSame('abc', $provider->credentials['api_key']);

        $raw = DB::table('sms_providers')->where('id', $provider->id)->value('credentials');
        $this->assertStringNotContainsString('abc', $raw);
    }

    public function test_test_sms_logs_delivered_only_when_provider_active(): void
    {
        $admin = User::factory()->create();
        $provider = SmsProvider::create(['name' => 'Manual', 'slug' => 'manual', 'provider' => 'manual', 'price_per_sms' => 0.5, 'is_active' => false]);

        Livewire::actingAs($admin)->test(SmsManagement::class)->call('sendTestSms', $provider->id);
        $this->assertDatabaseHas('sms_logs', ['sms_provider_id' => $provider->id, 'status' => 'failed']);

        $provider->update(['is_active' => true]);
        Livewire::actingAs($admin)->test(SmsManagement::class)->call('sendTestSms', $provider->id);
        $this->assertDatabaseHas('sms_logs', ['sms_provider_id' => $provider->id, 'status' => 'delivered']);
    }

    public function test_super_admin_can_add_sms_credit_to_a_tenant(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();

        Livewire::actingAs($admin)->test(SmsManagement::class)
            ->set('tab', 'balances')
            ->set('creditTenantId', $tenant->id)
            ->set('creditAmount', 500)
            ->call('addCredit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tenant_sms_balances', ['tenant_id' => $tenant->id, 'balance' => 500]);

        Livewire::actingAs($admin)->test(SmsManagement::class)
            ->set('creditTenantId', $tenant->id)
            ->set('creditAmount', 200)
            ->call('addCredit');

        $this->assertDatabaseHas('tenant_sms_balances', ['tenant_id' => $tenant->id, 'balance' => 700]);
    }

    public function test_super_admin_can_manage_sms_templates(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(SmsManagement::class)
            ->call('createTemplate')
            ->set('templateKey', 'invoice_due')
            ->set('templateName', 'Invoice due reminder')
            ->set('templateContent', 'Your invoice is due on {due_date}.')
            ->call('saveTemplate')
            ->assertHasNoErrors();

        $template = SmsTemplate::where('key', 'invoice_due')->firstOrFail();
        $this->assertTrue($template->is_active);

        Livewire::actingAs($admin)->test(SmsManagement::class)->call('toggleTemplateActive', $template->id);
        $this->assertDatabaseHas('sms_templates', ['id' => $template->id, 'is_active' => false]);

        Livewire::actingAs($admin)->test(SmsManagement::class)->call('deleteTemplate', $template->id);
        $this->assertDatabaseMissing('sms_templates', ['id' => $template->id]);
    }
}
