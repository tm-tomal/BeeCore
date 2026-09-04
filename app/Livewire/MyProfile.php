<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Language;
use App\Models\LoginAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class MyProfile extends Component
{
    public string $name = '';
    public string $email = '';
    public string $language = 'en';
    public string $timezone = 'Asia/Dhaka';

    public bool $notifyEmail = true;
    public bool $notifySms = false;
    public bool $notifyPush = false;

    public string $currentPassword = '';
    public string $newPassword = '';
    public string $newPasswordConfirmation = '';

    public ?string $issuedTwoFactorSecret = null;

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->language = $user->language ?? 'en';
        $this->timezone = $user->timezone ?? 'Asia/Dhaka';

        $prefs = $user->notification_preferences ?? [];
        $this->notifyEmail = $prefs['email'] ?? true;
        $this->notifySms = $prefs['sms'] ?? false;
        $this->notifyPush = $prefs['push'] ?? false;
    }

    public function saveProfile(): void
    {
        $user = auth()->user();

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'language' => ['required', 'string', 'max:10', 'exists:languages,code'],
            'timezone' => ['required', 'string', 'max:50'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'language' => $data['language'],
            'timezone' => $data['timezone'],
        ]);

        AuditLog::record('profile.updated', $user);
        session()->flash('message', 'Profile updated.');
    }

    public function saveNotificationPreferences(): void
    {
        $user = auth()->user();
        $user->update([
            'notification_preferences' => [
                'email' => $this->notifyEmail,
                'sms' => $this->notifySms,
                'push' => $this->notifyPush,
            ],
        ]);

        AuditLog::record('profile.notification_preferences_updated', $user);
        session()->flash('message', 'Notification preferences saved.');
    }

    public function changePassword(): void
    {
        $user = auth()->user();

        $this->validate([
            'currentPassword' => ['required', 'string'],
            'newPassword' => ['required', 'string', 'min:8'],
            'newPasswordConfirmation' => ['required', 'string', 'same:newPassword'],
        ]);

        if (!Hash::check($this->currentPassword, $user->password)) {
            $this->addError('currentPassword', 'The current password is incorrect.');

            return;
        }

        $user->update(['password' => $this->newPassword]);
        AuditLog::record('profile.password_changed', $user);

        $this->reset(['currentPassword', 'newPassword', 'newPasswordConfirmation']);
        session()->flash('message', 'Password changed.');
    }

    public function enableTwoFactor(): void
    {
        $user = auth()->user();
        $secret = Str::random(32);

        $user->update(['two_factor_enabled' => true, 'two_factor_secret' => $secret]);
        AuditLog::record('profile.two_factor_enabled', $user);

        $this->issuedTwoFactorSecret = $secret;
        session()->flash('message', 'Two-factor authentication enabled. Save your secret now.');
    }

    public function disableTwoFactor(): void
    {
        $user = auth()->user();
        $user->update(['two_factor_enabled' => false, 'two_factor_secret' => null]);
        AuditLog::record('profile.two_factor_disabled', $user);

        $this->issuedTwoFactorSecret = null;
        session()->flash('message', 'Two-factor authentication disabled.');
    }

    public function terminateOtherSessions(): void
    {
        $user = auth()->user();
        $currentId = session()->getId();

        DB::table('sessions')->where('user_id', $user->id)->where('id', '!=', $currentId)->delete();
        AuditLog::record('profile.other_sessions_terminated', $user);
        session()->flash('message', 'All other sessions were terminated.');
    }

    public function render()
    {
        $user = auth()->user();
        $currentId = session()->getId();

        $workspaceTenantId = session('impersonated_tenant_id') ?? $user->tenant_id;
        $workspace = $workspaceTenantId ? \App\Models\Tenant::query()->find($workspaceTenantId) : null;

        return view('livewire.my-profile', [
            'languages' => Language::where('is_active', true)->orderBy('name')->get(),
            'sessions' => DB::table('sessions')->where('user_id', $user->id)->orderByDesc('last_activity')->get(),
            'currentSessionId' => $currentId,
            'loginHistory' => LoginAttempt::where('email', $user->email)->latest('id')->limit(10)->get(),
            'workspace' => $workspace,
            'roleLabel' => \App\Models\User::roleLabel($user->role),
        ]);
    }
}
