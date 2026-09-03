<?php

namespace Tests\Feature;

use App\Livewire\Notifications;
use App\Models\NotificationEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationsConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_toggle_a_notification_channel(): void
    {
        $admin = User::factory()->create();
        $event = NotificationEvent::where('key', 'payment_reminder')->firstOrFail();

        Livewire::actingAs($admin)->test(Notifications::class)
            ->call('toggleChannel', $event->id, 'sms')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('notification_events', ['id' => $event->id, 'sms_enabled' => true]);
    }

    public function test_super_admin_can_disable_an_event_and_test_sends_are_marked_failed(): void
    {
        $admin = User::factory()->create();
        $event = NotificationEvent::where('key', 'welcome_message')->firstOrFail();

        Livewire::actingAs($admin)->test(Notifications::class)
            ->call('toggleActive', $event->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('notification_events', ['id' => $event->id, 'is_active' => false]);

        Livewire::actingAs($admin)->test(Notifications::class)
            ->call('sendTest', $event->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('notification_logs', ['event_key' => 'welcome_message', 'status' => 'failed']);
    }

    public function test_send_test_logs_across_all_enabled_channels(): void
    {
        $admin = User::factory()->create();
        $event = NotificationEvent::where('key', 'subscription_expiry')->firstOrFail();
        $event->update(['sms_enabled' => true, 'push_enabled' => true]);

        Livewire::actingAs($admin)->test(Notifications::class)
            ->call('sendTest', $event->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('notification_logs', ['event_key' => 'subscription_expiry', 'channel' => 'email', 'status' => 'sent']);
        $this->assertDatabaseHas('notification_logs', ['event_key' => 'subscription_expiry', 'channel' => 'sms', 'status' => 'sent']);
        $this->assertDatabaseHas('notification_logs', ['event_key' => 'subscription_expiry', 'channel' => 'push', 'status' => 'sent']);
    }
}
