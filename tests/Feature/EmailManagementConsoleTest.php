<?php

namespace Tests\Feature;

use App\Livewire\EmailManagement;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class EmailManagementConsoleTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Email ISP', 'slug' => 'email-isp-'.uniqid(), 'status' => 'active',
            'currency' => 'BDT', 'timezone' => 'Asia/Dhaka',
        ]);
    }

    public function test_super_admin_can_create_a_provider_with_encrypted_credentials(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(EmailManagement::class)
            ->call('createProvider')
            ->set('name', 'Postmark')
            ->set('slug', 'postmark')
            ->set('type', 'api')
            ->set('provider', 'postmark')
            ->set('fromAddress', 'no-reply@beecore.test')
            ->set('fromName', 'BeeCore')
            ->set('credentialsText', "api_token=secret123")
            ->call('saveProvider')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('email_providers', ['slug' => 'postmark', 'from_address' => 'no-reply@beecore.test']);
        $provider = EmailProvider::where('slug', 'postmark')->firstOrFail();
        $this->assertSame('secret123', $provider->credentials['api_token']);

        $raw = DB::table('email_providers')->where('id', $provider->id)->value('credentials');
        $this->assertStringNotContainsString('secret123', $raw);
    }

    public function test_test_email_logs_delivered_only_when_provider_active(): void
    {
        $admin = User::factory()->create();
        $provider = EmailProvider::create(['name' => 'SMTP', 'slug' => 'smtp', 'type' => 'smtp', 'provider' => 'smtp', 'is_active' => false]);

        Livewire::actingAs($admin)->test(EmailManagement::class)->call('sendTestEmail', $provider->id);
        $this->assertDatabaseHas('email_logs', ['email_provider_id' => $provider->id, 'status' => 'failed']);

        $provider->update(['is_active' => true]);
        Livewire::actingAs($admin)->test(EmailManagement::class)->call('sendTestEmail', $provider->id);
        $this->assertDatabaseHas('email_logs', ['email_provider_id' => $provider->id, 'status' => 'delivered']);
    }

    public function test_super_admin_can_set_a_tenant_email_quota(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();

        Livewire::actingAs($admin)->test(EmailManagement::class)
            ->set('tab', 'quotas')
            ->set('quotaTenantId', $tenant->id)
            ->set('quotaAmount', 2000)
            ->call('setQuota')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tenant_email_quotas', ['tenant_id' => $tenant->id, 'monthly_quota' => 2000]);
    }

    public function test_super_admin_can_manage_email_templates(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(EmailManagement::class)
            ->call('createTemplate')
            ->set('templateKey', 'welcome_email')
            ->set('templateName', 'Welcome email')
            ->set('templateSubject', 'Welcome to BeeCore')
            ->set('templateBody', 'Hi {name}, welcome aboard!')
            ->call('saveTemplate')
            ->assertHasNoErrors();

        $template = EmailTemplate::where('key', 'welcome_email')->firstOrFail();
        $this->assertTrue($template->is_active);

        Livewire::actingAs($admin)->test(EmailManagement::class)->call('toggleTemplateActive', $template->id);
        $this->assertDatabaseHas('email_templates', ['id' => $template->id, 'is_active' => false]);

        Livewire::actingAs($admin)->test(EmailManagement::class)->call('deleteTemplate', $template->id);
        $this->assertDatabaseMissing('email_templates', ['id' => $template->id]);
    }
}
