<?php

namespace Tests\Feature;

use App\Livewire\Customers;
use App\Models\Customer;
use App\Models\CustomerSubscription;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\CreatesPlanSubscriptions;
use Tests\TestCase;

class RecurringBillingTest extends TestCase
{
    use RefreshDatabase, CreatesPlanSubscriptions;

    public function test_customer_can_be_assigned_a_tenant_package_subscription(): void
    {
        [$tenant, $user] = $this->tenantActor('assignment');
        $this->attachActivePlan($tenant, customerLimit: 100);
        $package = $this->package($tenant, 'Fiber 20', 1200);

        Livewire::actingAs($user)
            ->test(Customers::class)
            ->set('name', 'Recurring Customer')
            ->set('email', 'recurring@test.com')
            ->set('status', 'active')
            ->set('package_id', $package->id)
            ->set('billing_cycle', 'monthly')
            ->set('next_billing_date', '2026-08-01')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customer_subscriptions', [
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'package_name' => 'Fiber 20',
            'price' => 1200,
            'status' => 'active',
        ]);
        $this->assertSame(
            '2026-08-01',
            CustomerSubscription::firstOrFail()->next_billing_date->toDateString(),
        );
    }

    public function test_customer_cannot_be_assigned_another_tenant_package(): void
    {
        [$tenant, $user] = $this->tenantActor('owner');
        [$otherTenant] = $this->tenantActor('other-owner');
        $package = $this->package($otherTenant, 'Foreign Package', 500);

        Livewire::actingAs($user)
            ->test(Customers::class)
            ->set('name', 'Unsafe Customer')
            ->set('email', 'unsafe@test.com')
            ->set('status', 'active')
            ->set('package_id', $package->id)
            ->set('billing_cycle', 'monthly')
            ->set('next_billing_date', '2026-08-01')
            ->call('save')
            ->assertHasErrors(['package_id']);

        $this->assertDatabaseMissing('customers', ['tenant_id' => $tenant->id, 'email' => 'unsafe@test.com']);
    }

    public function test_recurring_command_generates_one_invoice_and_advances_cycle(): void
    {
        [$tenant] = $this->tenantActor('generator');
        $package = $this->package($tenant, 'Quarterly Fiber', 3000);
        $customer = $this->customer($tenant, 'generator@test.com');
        $subscription = $this->subscription($tenant, $customer, $package, 'quarterly', '2026-08-01');

        $this->travelTo('2026-08-17');
        $this->artisan('billing:generate-recurring')->assertSuccessful();
        $this->artisan('billing:generate-recurring')->assertSuccessful();

        $this->assertDatabaseCount('invoices', 1);
        $invoice = $subscription->invoices()->with('items')->firstOrFail();
        $this->assertSame('3000.00', (string) $invoice->subtotal);
        $this->assertSame('pending', $invoice->status);
        $this->assertSame('2026-08-01', $invoice->billing_period_start->toDateString());
        $this->assertSame('Quarterly Fiber - Quarterly service', $invoice->items->first()->description);
        $this->assertSame('2026-11-01', $subscription->fresh()->next_billing_date->toDateString());
    }

    public function test_paused_subscription_does_not_generate_invoice(): void
    {
        [$tenant] = $this->tenantActor('paused');
        $package = $this->package($tenant, 'Paused Fiber', 900);
        $customer = $this->customer($tenant, 'paused@test.com');
        $subscription = $this->subscription($tenant, $customer, $package, 'monthly', '2026-08-01');
        $subscription->update(['status' => 'paused']);

        $this->travelTo('2026-08-17');
        $this->artisan('billing:generate-recurring')->assertSuccessful();

        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_scheduler_catches_up_all_missed_monthly_periods(): void
    {
        [$tenant] = $this->tenantActor('catch-up');
        $package = $this->package($tenant, 'Monthly Fiber', 1000);
        $customer = $this->customer($tenant, 'catch-up@test.com');
        $subscription = $this->subscription($tenant, $customer, $package, 'monthly', '2026-06-01');

        $this->travelTo('2026-08-17');
        $this->artisan('billing:generate-recurring')->assertSuccessful();

        $this->assertDatabaseCount('invoices', 3);
        $this->assertSame('2026-09-01', $subscription->fresh()->next_billing_date->toDateString());
    }

    public function test_tenant_admin_can_trigger_recurring_billing_from_console(): void
    {
        [$tenant, $user] = $this->tenantActor('console');
        [$otherTenant, $otherUser] = $this->tenantActor('other-console');

        $package = $this->package($tenant, 'Console Fiber', 1000);
        $customer = $this->customer($tenant, 'console@test.com');
        $subscription = $this->subscription($tenant, $customer, $package, 'monthly', '2026-06-01');

        // A due subscription in another workspace must stay untouched.
        $otherPackage = $this->package($otherTenant, 'Other Fiber', 900);
        $otherCustomer = $this->customer($otherTenant, 'other@test.com');
        $this->subscription($otherTenant, $otherCustomer, $otherPackage, 'monthly', '2026-06-01');

        $this->travelTo('2026-08-17');

        Livewire::actingAs($user)
            ->test(\App\Livewire\Billing::class)
            ->call('generateRecurring')
            ->assertHasNoErrors();

        // Three missed monthly periods (Jun, Jul, Aug) generated for this tenant only.
        $this->assertSame(3, Invoice::query()->where('tenant_id', $tenant->id)->count());
        $this->assertSame(0, Invoice::query()->where('tenant_id', $otherTenant->id)->count());
        $this->assertSame('2026-09-01', $subscription->fresh()->next_billing_date->toDateString());

        // Running it again is idempotent: customers already billed are skipped.
        Livewire::actingAs($user)
            ->test(\App\Livewire\Billing::class)
            ->call('generateRecurring')
            ->assertHasNoErrors();

        $this->assertSame(3, Invoice::query()->where('tenant_id', $tenant->id)->count());
    }

    public function test_tenant_grace_days_apply_to_recurring_invoice_due_date(): void
    {
        [$tenant, $user] = $this->tenantActor('grace');
        $tenant->update(['settings' => ['billing' => ['grace_days' => 3]]]);

        $package = $this->package($tenant, 'Grace Fiber', 1000);
        $customer = $this->customer($tenant, 'grace@test.com');
        $subscription = $this->subscription($tenant, $customer, $package, 'monthly', '2026-08-01');

        $this->travelTo('2026-08-17');

        Livewire::actingAs($user)
            ->test(\App\Livewire\Billing::class)
            ->call('generateRecurring')
            ->assertHasNoErrors();

        $invoice = $subscription->invoices()->firstOrFail();
        $this->assertSame('2026-08-04', $invoice->due_date->toDateString());
    }

    private function tenantActor(string $slug): array
    {
        $tenant = Tenant::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'status' => 'active',
            'currency' => 'BDT',
            'timezone' => 'UTC',
        ]);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        return [$tenant, $user];
    }

    private function package(Tenant $tenant, string $name, float $price): Package
    {
        return Package::create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'price' => $price,
            'type' => 'shared',
            'is_active' => true,
        ]);
    }

    private function customer(Tenant $tenant, string $email): Customer
    {
        return Customer::create([
            'tenant_id' => $tenant->id,
            'name' => 'Subscription Customer',
            'email' => $email,
            'status' => 'active',
        ]);
    }

    private function subscription(Tenant $tenant, Customer $customer, Package $package, string $cycle, string $nextDate): CustomerSubscription
    {
        return CustomerSubscription::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'package_name' => $package->name,
            'price' => $package->price,
            'billing_cycle' => $cycle,
            'status' => 'active',
            'next_billing_date' => $nextDate,
            'started_at' => '2026-08-01',
        ]);
    }
}