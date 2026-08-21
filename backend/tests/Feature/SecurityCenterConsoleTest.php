<?php

namespace Tests\Feature;

use App\Livewire\SecurityCenter;
use App\Models\BlockedIp;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityCenterConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_login_is_recorded_and_blocked_ip_prevents_login(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.test', 'password' => 'correct-password']);

        $this->post('/login', ['email' => 'owner@example.test', 'password' => 'wrong-password']);
        $this->assertDatabaseHas('login_attempts', ['email' => 'owner@example.test', 'successful' => false]);

        BlockedIp::create(['ip_address' => '127.0.0.1', 'reason' => 'test']);

        $response = $this->post('/login', ['email' => 'owner@example.test', 'password' => 'correct-password']);
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_super_admin_can_block_and_unblock_an_ip(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(SecurityCenter::class)
            ->set('tab', 'ip')
            ->set('blockIpAddress', '203.0.113.10')
            ->set('blockReason', 'Repeated failed logins')
            ->call('blockIp')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('blocked_ips', ['ip_address' => '203.0.113.10']);

        $blocked = BlockedIp::where('ip_address', '203.0.113.10')->firstOrFail();
        Livewire::actingAs($admin)->test(SecurityCenter::class)->call('unblockIp', $blocked->id);
        $this->assertDatabaseMissing('blocked_ips', ['id' => $blocked->id]);
    }

    public function test_super_admin_can_force_logout_a_user(): void
    {
        $admin = User::factory()->create();
        $otherUser = User::factory()->create();

        DB::table('sessions')->insert([
            'id' => 'sess-123', 'user_id' => $otherUser->id, 'ip_address' => '127.0.0.1',
            'user_agent' => 'test', 'payload' => base64_encode('data'), 'last_activity' => time(),
        ]);

        Livewire::actingAs($admin)->test(SecurityCenter::class)
            ->call('forceLogoutUser', $otherUser->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('sessions', ['user_id' => $otherUser->id]);
    }

    public function test_suspicious_ip_is_flagged_after_five_failed_attempts(): void
    {
        $admin = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            LoginAttempt::create([
                'email' => 'attacker@example.test', 'ip_address' => '198.51.100.5',
                'successful' => false, 'created_at' => now(),
            ]);
        }

        Livewire::actingAs($admin)->test(SecurityCenter::class)
            ->assertSee('198.51.100.5')
            ->assertSee('5 failed login attempts');
    }
}
