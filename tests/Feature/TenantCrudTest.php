<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TenantCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_tenants(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/tenants')
            ->assertSee('Tenant portfolio');
    }

    public function test_can_create_tenant(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Tenants::class)
            ->set('name', 'SpeedNet ISP')
            ->set('slug', 'speednet')
            ->set('status', 'active')
            ->set('currency', 'BDT')
            ->set('timezone', 'Asia/Dhaka')
            ->set('ownerName', 'Rahim Uddin')
            ->set('ownerEmail', 'owner@speednet.test')
            ->set('ownerPhone', '01700000000')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tenants', [
            'slug' => 'speednet',
            'owner_email' => 'owner@speednet.test',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'owner@speednet.test',
            'role' => User::ROLE_TENANT_ADMIN,
        ]);
    }

    public function test_owner_email_and_password_confirmation_are_required(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Tenants::class)
            ->set('name', 'Incomplete ISP')
            ->set('ownerName', 'Rahim Uddin')
            ->call('save')
            ->assertHasErrors(['ownerEmail', 'ownerPhone']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Tenants::class)
            ->set('name', 'SpeedNet ISP')
            ->set('ownerName', 'Rahim Uddin')
            ->set('ownerEmail', 'owner@speednet.test')
            ->set('ownerPhone', '01700000000')
            ->set('password', 'secret123')
            ->set('passwordConfirmation', 'different123')
            ->call('save')
            ->assertHasErrors(['passwordConfirmation']);
    }
}
