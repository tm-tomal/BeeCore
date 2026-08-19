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
            ->call('save')
            ->assertHasNoErrors();
            
        $this->assertDatabaseHas('tenants', [
            'slug' => 'speednet'
        ]);
    }
}
