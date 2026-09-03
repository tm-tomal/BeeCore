<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\CustomerAppEvent;
use App\Models\CustomerAppSetting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CustomerApp extends Component
{
    public string $currentVersion = '1.0.0';
    public string $minimumSupportedVersion = '1.0.0';
    public bool $forceUpdateEnabled = false;
    public bool $maintenanceModeEnabled = false;
    public string $maintenanceMessage = '';
    public bool $pushNotificationsEnabled = true;

    public function mount(): void
    {
        $this->assertSuperAdmin();
        $settings = CustomerAppSetting::current();
        $this->currentVersion = $settings->current_version;
        $this->minimumSupportedVersion = $settings->minimum_supported_version;
        $this->forceUpdateEnabled = $settings->force_update_enabled;
        $this->maintenanceModeEnabled = $settings->maintenance_mode_enabled;
        $this->maintenanceMessage = $settings->maintenance_message ?? '';
        $this->pushNotificationsEnabled = $settings->push_notifications_enabled;
    }

    public function save(): void
    {
        $this->assertSuperAdmin();

        $data = $this->validate([
            'currentVersion' => ['required', 'string', 'max:20'],
            'minimumSupportedVersion' => ['required', 'string', 'max:20'],
            'maintenanceMessage' => ['nullable', 'string', 'max:500'],
        ]);

        $settings = CustomerAppSetting::current();
        $settings->update([
            'current_version' => $data['currentVersion'],
            'minimum_supported_version' => $data['minimumSupportedVersion'],
            'force_update_enabled' => $this->forceUpdateEnabled,
            'maintenance_mode_enabled' => $this->maintenanceModeEnabled,
            'maintenance_message' => $data['maintenanceMessage'] ?: null,
            'push_notifications_enabled' => $this->pushNotificationsEnabled,
        ]);

        AuditLog::record('customer_app.settings_updated', $settings, [
            'current_version' => $settings->current_version,
            'force_update_enabled' => $settings->force_update_enabled,
            'maintenance_mode_enabled' => $settings->maintenance_mode_enabled,
        ]);

        session()->flash('message', 'Customer app settings saved.');
    }

    public function logTestEvent(string $type): void
    {
        $this->assertSuperAdmin();
        abort_unless(in_array($type, ['session_start', 'crash', 'active_user'], true), 422);

        CustomerAppEvent::create([
            'type' => $type,
            'metadata' => ['source' => 'manual_test', 'triggered_by' => auth()->user()?->name],
            'created_at' => now(),
        ]);

        session()->flash('message', 'Test '.str_replace('_', ' ', $type).' event logged.');
    }

    public function render()
    {
        $this->assertSuperAdmin();

        $since = now()->subDays(30);

        return view('livewire.customer-app', [
            'stats' => [
                'sessions' => CustomerAppEvent::where('type', 'session_start')->where('created_at', '>=', $since)->count(),
                'crashes' => CustomerAppEvent::where('type', 'crash')->where('created_at', '>=', $since)->count(),
                'active_users' => CustomerAppEvent::where('type', 'active_user')->where('created_at', '>=', $since)->distinct('tenant_id')->count('tenant_id'),
            ],
            'recentEvents' => CustomerAppEvent::query()->with('tenant')->latest('id')->limit(20)->get(),
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
