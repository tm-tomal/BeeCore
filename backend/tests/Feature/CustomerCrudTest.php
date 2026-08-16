<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_customers(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/customers')
            ->assertSee('Manage Customers');
    }

    public function test_can_create_customer(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Customers::class)
            ->set('name', 'John Doe')
            ->set('email', 'john@test.com')
            ->set('phone', '12345678')
            ->set('status', 'active')
            ->set('package_name', '10Mbps')
            ->call('save')
            ->assertHasNoErrors();
            
        $this->assertDatabaseHas('customers', [
            'email' => 'john@test.com'
        ]);
    }
}
