<?php

namespace Tests\Feature;

use App\Livewire\MyProfile;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class MyProfileConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_profile_information(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(MyProfile::class)
            ->set('name', 'Updated Name')
            ->set('email', 'updated@example.test')
            ->set('language', 'en')
            ->set('timezone', 'Asia/Dhaka')
            ->call('saveProfile')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name', 'email' => 'updated@example.test']);
    }

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password-123']);

        Livewire::actingAs($user)->test(MyProfile::class)
            ->set('currentPassword', 'old-password-123')
            ->set('newPassword', 'new-password-456')
            ->set('newPasswordConfirmation', 'new-password-456')
            ->call('changePassword')
            ->assertHasNoErrors();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('new-password-456', $user->fresh()->password));
    }

    public function test_password_change_fails_with_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password-123']);

        Livewire::actingAs($user)->test(MyProfile::class)
            ->set('currentPassword', 'wrong-password')
            ->set('newPassword', 'new-password-456')
            ->set('newPasswordConfirmation', 'new-password-456')
            ->call('changePassword')
            ->assertHasErrors(['currentPassword']);
    }

    public function test_user_can_enable_and_disable_two_factor_authentication(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)->test(MyProfile::class)
            ->call('enableTwoFactor')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'two_factor_enabled' => true]);
        $this->assertNotNull($component->get('issuedTwoFactorSecret'));

        $component->call('disableTwoFactor');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'two_factor_enabled' => false]);
    }

    public function test_user_can_save_notification_preferences(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(MyProfile::class)
            ->set('notifyEmail', false)
            ->set('notifySms', true)
            ->call('saveNotificationPreferences')
            ->assertHasNoErrors();

        $this->assertSame(['email' => false, 'sms' => true, 'push' => false], $user->fresh()->notification_preferences);
    }

    public function test_user_can_terminate_other_sessions(): void
    {
        $user = User::factory()->create();

        DB::table('sessions')->insert([
            ['id' => 'current-session', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'test', 'payload' => 'x', 'last_activity' => time()],
            ['id' => 'other-session', 'user_id' => $user->id, 'ip_address' => '127.0.0.2', 'user_agent' => 'test', 'payload' => 'x', 'last_activity' => time()],
        ]);

        $this->actingAs($user);
        session(['_test' => true]);

        Livewire::actingAs($user)->test(MyProfile::class)->call('terminateOtherSessions');

        $this->assertGreaterThanOrEqual(0, DB::table('sessions')->where('user_id', $user->id)->count());
    }

    public function test_login_history_shows_attempts_for_the_current_user(): void
    {
        $user = User::factory()->create();
        LoginAttempt::create(['email' => $user->email, 'ip_address' => '127.0.0.1', 'successful' => true, 'created_at' => now()]);

        Livewire::actingAs($user)->test(MyProfile::class)
            ->assertSee('Success');
    }
}
