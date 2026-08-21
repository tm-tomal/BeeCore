<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\NotificationEvent;
use App\Models\NotificationLog;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Notifications extends Component
{
    use WithPagination;

    public string $tab = 'events';
    public string $statusFilter = '';
    public string $channelFilter = '';

    public function toggleActive(int $id): void
    {
        $this->assertSuperAdmin();
        $event = NotificationEvent::findOrFail($id);
        $event->update(['is_active' => !$event->is_active]);
        AuditLog::record('notification.event_toggled', $event, ['enabled' => $event->is_active]);
        session()->flash('message', $event->name.' is now '.($event->is_active ? 'enabled' : 'disabled').'.');
    }

    public function toggleChannel(int $id, string $channel): void
    {
        $this->assertSuperAdmin();
        abort_unless(in_array($channel, ['email', 'sms', 'push'], true), 422);

        $event = NotificationEvent::findOrFail($id);
        $column = $channel.'_enabled';
        $event->update([$column => !$event->{$column}]);
        AuditLog::record('notification.channel_toggled', $event, ['channel' => $channel, 'enabled' => $event->{$column}]);
        session()->flash('message', ucfirst($channel).' channel updated for '.$event->name.'.');
    }

    public function sendTest(int $id): void
    {
        $this->assertSuperAdmin();
        $event = NotificationEvent::findOrFail($id);

        $channels = array_filter([
            'email' => $event->email_enabled,
            'sms' => $event->sms_enabled,
            'push' => $event->push_enabled,
        ]);

        foreach (array_keys($channels) ?: ['email'] as $channel) {
            NotificationLog::create([
                'event_key' => $event->key,
                'channel' => $channel,
                'recipient' => 'test@beecore.test',
                'status' => $event->is_active ? 'sent' : 'failed',
                'created_at' => now(),
            ]);
        }

        AuditLog::record('notification.test_sent', $event, ['channels' => array_keys($channels)]);
        session()->flash('message', 'Test notification logged for '.$event->name.'.');
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedChannelFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->assertSuperAdmin();

        return view('livewire.notifications', [
            'events' => NotificationEvent::query()->orderBy('name')->get(),
            'logs' => NotificationLog::query()->with('tenant')
                ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
                ->when($this->channelFilter, fn ($q) => $q->where('channel', $this->channelFilter))
                ->latest('id')->paginate(15),
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
