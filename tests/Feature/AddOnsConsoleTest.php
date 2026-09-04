<?php

namespace Tests\Feature;

use App\Livewire\AddOns;
use App\Models\Addon;
use App\Models\Tenant;
use App\Models\TenantAddon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AddOnsConsoleTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Add-on ISP', 'slug' => 'addon-isp-'.uniqid(), 'status' => 'active',
            'currency' => 'BDT', 'timezone' => 'Asia/Dhaka',
        ]);
    }

    public function test_super_admin_can_create_an_addon(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(AddOns::class)
            ->call('create')
            ->set('name', 'SMS Booster 10k')
            ->set('slug', 'sms-booster-10k')
            ->set('category', 'sms')
            ->set('price', 1500)
            ->set('billingCycle', 'monthly')
            ->set('usageLimit', 10000)
            ->set('usageUnit', 'SMS')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('addons', ['slug' => 'sms-booster-10k', 'category' => 'sms', 'usage_limit' => 10000]);
    }

    public function test_super_admin_can_assign_an_addon_to_a_tenant_and_log_usage(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $addon = Addon::create(['name' => 'Storage 50GB', 'slug' => 'storage-50gb', 'category' => 'storage', 'price' => 500, 'billing_cycle' => 'monthly', 'usage_limit' => 50000, 'usage_unit' => 'MB', 'is_active' => true]);

        Livewire::actingAs($admin)->test(AddOns::class)
            ->set('tab', 'assignments')
            ->set('assignTenantId', $tenant->id)
            ->set('assignAddonId', $addon->id)
            ->set('assignBillingCycle', 'monthly')
            ->call('assignAddon')
            ->assertHasNoErrors();

        $assignment = TenantAddon::where('tenant_id', $tenant->id)->where('addon_id', $addon->id)->firstOrFail();
        $this->assertSame('active', $assignment->status);
        $this->assertEquals(500, $assignment->price);

        Livewire::actingAs($admin)->test(AddOns::class)
            ->call('openUsage', $assignment->id)
            ->set('usageAmount', 250)
            ->call('recordUsage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tenant_addons', ['id' => $assignment->id, 'usage_used' => 250]);
    }

    public function test_super_admin_can_cancel_an_assignment(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $addon = Addon::create(['name' => 'Premium Support', 'slug' => 'premium-support', 'category' => 'premium_support', 'price' => 2000, 'billing_cycle' => 'monthly', 'is_active' => true]);
        $assignment = TenantAddon::create(['tenant_id' => $tenant->id, 'addon_id' => $addon->id, 'status' => 'active', 'price' => 2000, 'billing_cycle' => 'monthly', 'starts_at' => now()]);

        Livewire::actingAs($admin)->test(AddOns::class)
            ->call('cancelAssignment', $assignment->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tenant_addons', ['id' => $assignment->id, 'status' => 'cancelled']);
    }

    public function test_sms_addon_cannot_be_created_without_credit_allowance(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(AddOns::class)
            ->call('create')
            ->set('name', 'SMS without credits')
            ->set('slug', 'sms-without-credits')
            ->set('category', 'sms')
            ->set('price', 500)
            ->set('billingCycle', 'monthly')
            ->call('save')
            ->assertHasErrors('usageLimit');

        $this->assertDatabaseMissing('addons', ['slug' => 'sms-without-credits']);
    }

    public function test_assigning_an_sms_addon_credits_the_tenant_wallet(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $addon = Addon::create([
            'name' => 'SMS Booster 5k', 'slug' => 'sms-booster-5k-'.uniqid(), 'category' => 'sms',
            'price' => 500, 'billing_cycle' => 'monthly', 'usage_limit' => 5000, 'usage_unit' => 'SMS', 'is_active' => true,
        ]);

        Livewire::actingAs($admin)->test(AddOns::class)
            ->set('tab', 'assignments')
            ->set('assignTenantId', $tenant->id)
            ->set('assignAddonId', $addon->id)
            ->set('assignBillingCycle', 'monthly')
            ->call('assignAddon')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tenant_addons', ['tenant_id' => $tenant->id, 'addon_id' => $addon->id, 'status' => 'active']);
        $this->assertDatabaseHas('tenant_sms_balances', ['tenant_id' => $tenant->id, 'balance' => 5000]);
    }

    public function test_approving_a_pending_sms_request_credits_the_wallet(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $addon = Addon::create([
            'name' => 'SMS Booster 2k', 'slug' => 'sms-booster-2k-'.uniqid(), 'category' => 'sms',
            'price' => 250, 'billing_cycle' => 'monthly', 'usage_limit' => 2000, 'usage_unit' => 'SMS', 'is_active' => true,
        ]);
        $assignment = TenantAddon::create([
            'tenant_id' => $tenant->id, 'addon_id' => $addon->id, 'status' => 'requested',
            'price' => 250, 'billing_cycle' => 'monthly', 'starts_at' => now(),
        ]);

        Livewire::actingAs($admin)->test(AddOns::class)
            ->call('approveRequest', $assignment->id)
            ->assertHasNoErrors();

        $this->assertSame('active', $assignment->fresh()->status);
        $this->assertDatabaseHas('tenant_sms_balances', ['tenant_id' => $tenant->id, 'balance' => 2000]);
    }
}
