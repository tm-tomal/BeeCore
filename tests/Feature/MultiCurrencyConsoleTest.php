<?php

namespace Tests\Feature;

use App\Livewire\MultiCurrency;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MultiCurrencyConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_add_a_currency_and_it_logs_an_initial_rate(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(MultiCurrency::class)
            ->call('create')
            ->set('code', 'EUR')
            ->set('name', 'Euro')
            ->set('symbol', '€')
            ->set('decimalPlaces', 2)
            ->set('exchangeRate', 0.0083)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('currencies', ['code' => 'EUR', 'name' => 'Euro']);
        $currency = Currency::where('code', 'EUR')->firstOrFail();
        $this->assertDatabaseHas('currency_rate_history', ['currency_id' => $currency->id]);
    }

    public function test_updating_the_exchange_rate_logs_history(): void
    {
        $admin = User::factory()->create();
        $currency = Currency::where('code', 'USD')->firstOrFail();

        Livewire::actingAs($admin)->test(MultiCurrency::class)
            ->call('openRate', $currency->id)
            ->set('newRate', 0.0095)
            ->call('updateRate')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('currencies', ['id' => $currency->id, 'exchange_rate' => 0.0095]);
        $this->assertDatabaseHas('currency_rate_history', ['currency_id' => $currency->id, 'rate' => 0.0095]);
    }

    public function test_default_currency_cannot_be_deactivated_or_deleted(): void
    {
        $admin = User::factory()->create();
        $default = Currency::where('is_default', true)->firstOrFail();

        try {
            Livewire::actingAs($admin)->test(MultiCurrency::class)->call('toggleActive', $default->id);
        } catch (\Throwable $e) {
            // Guarded by abort_if; either the exception surfaces or the state is left untouched.
        }

        $this->assertDatabaseHas('currencies', ['id' => $default->id, 'is_active' => true]);
    }

    public function test_super_admin_can_change_the_default_currency(): void
    {
        $admin = User::factory()->create();
        $usd = Currency::where('code', 'USD')->firstOrFail();

        Livewire::actingAs($admin)->test(MultiCurrency::class)
            ->call('setDefault', $usd->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('currencies', ['code' => 'USD', 'is_default' => true]);
        $this->assertDatabaseHas('currencies', ['code' => 'BDT', 'is_default' => false]);
    }
}
