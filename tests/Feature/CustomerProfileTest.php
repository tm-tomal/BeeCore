<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\TenantSmsBalance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerProfileTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(string $slug): Tenant
    {
        return Tenant::create(['name' => 'ProfileCo', 'slug' => $slug, 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);
    }

    private function admin(Tenant $tenant): User
    {
        return User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);
    }

    public function test_profile_page_shows_full_customer_data_address_and_contact(): void
    {
        $tenant = $this->tenant('profile-view');
        $admin = $this->admin($tenant);

        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => 'Rahim Uddin',
            'email' => 'rahim@example.com',
            'phone' => '01712345678',
            'status' => 'active',
            'address' => [
                'house' => 'House 12',
                'street' => 'Road 8, Banani',
                'area' => 'Banani',
                'city' => 'Dhaka',
                'latitude' => '23.7937',
                'longitude' => '90.4066',
            ],
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\CustomerProfile::class, ['customer' => $customer->id])
            ->assertOk()
            ->assertSee('Rahim Uddin')
            ->assertSee('rahim@example.com')
            ->assertSee('01712345678')
            ->assertSee('House 12, Road 8, Banani, Banani, Dhaka')
            ->assertSee('Open in Google Maps')
            ->assertSee('Notifications')
            ->assertSee('Send a message')
            ->assertSee('Recent invoices');
    }

    public function test_notification_toggle_is_persisted_and_audited(): void
    {
        $tenant = $this->tenant('profile-toggle');
        $admin = $this->admin($tenant);

        $customer = Customer::create(['tenant_id' => $tenant->id, 'name' => 'Ayesha', 'email' => 'a@test.com', 'phone' => '01811111111', 'status' => 'active']);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\CustomerProfile::class, ['customer' => $customer->id])
            ->assertSet('composeChannel', 'sms')
            ->call('toggleSms')
            ->call('toggleEmail');

        $fresh = $customer->fresh();
        $this->assertFalse($fresh->notify_sms);
        $this->assertFalse($fresh->notify_email);
        $this->assertSame(2, AuditLog::query()->where('action', 'customer.notification_pref')->where('subject_id', $customer->id)->count());
    }

    public function test_sms_send_is_blocked_when_wallet_is_empty(): void
    {
        $tenant = $this->tenant('profile-sms');
        $admin = $this->admin($tenant);

        $customer = Customer::create(['tenant_id' => $tenant->id, 'name' => 'Karim', 'email' => 'k@test.com', 'phone' => '01777777777', 'status' => 'active']);

        TenantSmsBalance::create(['tenant_id' => $tenant->id, 'balance' => 0]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\CustomerProfile::class, ['customer' => $customer->id])
            ->set('composeChannel', 'sms')
            ->set('composeMessage', 'Your bill is due.')
            ->call('sendMessage');

        $this->assertSame(0, AuditLog::query()->where('action', 'customer.message.sms')->count());
        $this->assertSame(0, \App\Models\SmsLog::count());
    }

    public function test_sms_send_is_blocked_when_customer_turned_it_off(): void
    {
        $tenant = $this->tenant('profile-sms-off');
        $admin = $this->admin($tenant);

        $customer = Customer::create(['tenant_id' => $tenant->id, 'name' => 'Jamil', 'email' => 'j@test.com', 'phone' => '01911111111', 'status' => 'active', 'notify_sms' => false]);
        TenantSmsBalance::create(['tenant_id' => $tenant->id, 'balance' => 50]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\CustomerProfile::class, ['customer' => $customer->id])
            ->set('composeChannel', 'sms')
            ->set('composeMessage', 'Hi Jamil')
            ->call('sendMessage');

        $this->assertSame(0, AuditLog::query()->where('action', 'customer.message.sms')->count());
        $this->assertSame(0, \App\Models\SmsLog::count());
        $this->assertSame(50, (int) TenantSmsBalance::query()->where('tenant_id', $tenant->id)->value('balance'));
    }

    public function test_email_send_records_audit(): void
    {
        Mail::fake();

        $tenant = $this->tenant('profile-mail');
        $admin = $this->admin($tenant);

        $customer = Customer::create(['tenant_id' => $tenant->id, 'name' => 'Nadia', 'email' => 'nadia@test.com', 'phone' => null, 'status' => 'active']);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\CustomerProfile::class, ['customer' => $customer->id])
            ->set('composeChannel', 'email')
            ->set('composeSubject', 'Your invoice')
            ->set('composeMessage', 'Dear Nadia, please pay your bill.')
            ->call('sendMessage');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'customer.message.email',
            'subject_type' => Customer::class,
            'subject_id' => $customer->id,
        ]);
    }

    public function test_profile_route_renders_for_an_authorized_tenant_admin(): void
    {
        $tenant = $this->tenant('profile-route');
        $admin = $this->admin($tenant);

        $customer = Customer::create(['tenant_id' => $tenant->id, 'name' => 'Sami', 'email' => 'sami@test.com', 'phone' => '01700000001', 'status' => 'active']);

        $this->actingAs($admin)
            ->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee('Sami')
            ->assertSee('Notifications');
    }

    public function test_profile_route_is_tenanted(): void
    {
        $tenantA = $this->tenant('profile-route-a');
        $otherTenant = $this->tenant('profile-route-b');
        $adminA = $this->admin($tenantA);

        $customer = Customer::create(['tenant_id' => $otherTenant->id, 'name' => 'Other ISP Customer', 'email' => 'o@test.com', 'status' => 'active']);

        $this->actingAs($adminA)
            ->get(route('customers.show', $customer))
            ->assertNotFound();
    }
}
