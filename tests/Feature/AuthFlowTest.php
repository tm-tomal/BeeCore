<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_register_a_new_isp_workspace(): void
    {
        $response = $this->post('/register', [
            'name' => 'SpeedNet Broadband',
            'operationMode' => 'automatic',
            'businessAddress' => 'Dhaka, Bangladesh',
            'ownerName' => 'Rahim Uddin',
            'ownerEmail' => 'owner@speednet.test',
            'ownerPhone' => '01700000000',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        $tenant = Tenant::where('slug', 'speednet-broadband')->firstOrFail();
        $this->assertSame('automatic', $tenant->operation_mode);
        $this->assertSame('owner@speednet.test', $tenant->owner_email);

        $this->assertDatabaseHas('users', [
            'tenant_id' => $tenant->id,
            'email' => 'owner@speednet.test',
            'role' => User::ROLE_TENANT_ADMIN,
        ]);
    }

    public function test_registration_requires_matching_password_and_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.test']);

        $this->post('/register', [
            'name' => 'Test ISP',
            'ownerName' => 'Jane Doe',
            'ownerEmail' => 'taken@example.test',
            'ownerPhone' => '01700000000',
            'password' => 'secret123',
            'password_confirmation' => 'different123',
        ])->assertSessionHasErrors(['ownerEmail', 'password']);
    }

    public function test_guest_can_request_a_password_reset_link(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'resetme@example.test']);

        $this->post('/forgot-password', ['email' => 'resetme@example.test'])
            ->assertSessionHasNoErrors();

        $this->post('/forgot-password', ['email' => 'nobody@example.test'])
            ->assertSessionHasErrors('email');
    }

    public function test_guest_can_open_registration_and_password_pages(): void
    {
        $this->get('/register')->assertOk()->assertSee('Create your ISP workspace');
        $this->get('/forgot-password')->assertOk()->assertSee('Reset your password');
    }
}
