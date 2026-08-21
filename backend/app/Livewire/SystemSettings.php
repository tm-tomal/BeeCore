<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Language;
use App\Models\Currency;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class SystemSettings extends Component
{
    use WithFileUploads;

    public string $platformName = '';
    public string $defaultLanguage = 'en';
    public string $defaultCurrency = 'BDT';
    public string $defaultTimezone = 'Asia/Dhaka';
    public string $dateFormat = 'd M Y';
    public string $timeFormat = 'H:i';
    public string $invoicePrefix = 'INV';
    public int $invoiceDueDays = 7;
    public int $fileUploadMaxMb = 10;
    public string $allowedFileTypes = 'jpg,jpeg,png,pdf';
    public int $apiRateLimitPerMinute = 60;
    public int $sessionLifetimeMinutes = 120;
    public int $passwordMinLength = 8;
    public string $storageDisk = 'public';

    public $logo;
    public $favicon;

    public function mount(): void
    {
        $this->assertSuperAdmin();

        $this->platformName = SystemSetting::get('platform_name', 'BeeCore');
        $this->defaultLanguage = SystemSetting::get('default_language', 'en');
        $this->defaultCurrency = SystemSetting::get('default_currency', 'BDT');
        $this->defaultTimezone = SystemSetting::get('default_timezone', 'Asia/Dhaka');
        $this->dateFormat = SystemSetting::get('date_format', 'd M Y');
        $this->timeFormat = SystemSetting::get('time_format', 'H:i');
        $this->invoicePrefix = SystemSetting::get('invoice_prefix', 'INV');
        $this->invoiceDueDays = (int) SystemSetting::get('invoice_due_days', 7);
        $this->fileUploadMaxMb = (int) SystemSetting::get('file_upload_max_mb', 10);
        $this->allowedFileTypes = SystemSetting::get('allowed_file_types', 'jpg,jpeg,png,pdf');
        $this->apiRateLimitPerMinute = (int) SystemSetting::get('api_rate_limit_per_minute', 60);
        $this->sessionLifetimeMinutes = (int) SystemSetting::get('session_lifetime_minutes', 120);
        $this->passwordMinLength = (int) SystemSetting::get('password_min_length', 8);
        $this->storageDisk = SystemSetting::get('storage_disk', 'public');
    }

    public function save(): void
    {
        $this->assertSuperAdmin();

        $data = $this->validate([
            'platformName' => ['required', 'string', 'max:255'],
            'defaultLanguage' => ['required', 'string', 'max:10', 'exists:languages,code'],
            'defaultCurrency' => ['required', 'string', 'max:10', 'exists:currencies,code'],
            'defaultTimezone' => ['required', 'string', 'max:50'],
            'dateFormat' => ['required', 'string', 'max:20'],
            'timeFormat' => ['required', 'string', 'max:20'],
            'invoicePrefix' => ['required', 'string', 'max:10'],
            'invoiceDueDays' => ['required', 'integer', 'min:0', 'max:365'],
            'fileUploadMaxMb' => ['required', 'integer', 'min:1', 'max:1024'],
            'allowedFileTypes' => ['required', 'string', 'max:255'],
            'apiRateLimitPerMinute' => ['required', 'integer', 'min:1', 'max:10000'],
            'sessionLifetimeMinutes' => ['required', 'integer', 'min:5', 'max:10080'],
            'passwordMinLength' => ['required', 'integer', 'min:6', 'max:64'],
            'storageDisk' => ['required', 'string', 'max:50'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:512'],
        ]);

        $map = [
            'platform_name' => $data['platformName'],
            'default_language' => $data['defaultLanguage'],
            'default_currency' => $data['defaultCurrency'],
            'default_timezone' => $data['defaultTimezone'],
            'date_format' => $data['dateFormat'],
            'time_format' => $data['timeFormat'],
            'invoice_prefix' => $data['invoicePrefix'],
            'invoice_due_days' => (string) $data['invoiceDueDays'],
            'file_upload_max_mb' => (string) $data['fileUploadMaxMb'],
            'allowed_file_types' => $data['allowedFileTypes'],
            'api_rate_limit_per_minute' => (string) $data['apiRateLimitPerMinute'],
            'session_lifetime_minutes' => (string) $data['sessionLifetimeMinutes'],
            'password_min_length' => (string) $data['passwordMinLength'],
            'storage_disk' => $data['storageDisk'],
        ];

        if ($this->logo) {
            $map['platform_logo_path'] = $this->logo->store('platform', 'public');
        }
        if ($this->favicon) {
            $map['platform_favicon_path'] = $this->favicon->store('platform', 'public');
        }

        foreach ($map as $key => $value) {
            SystemSetting::set($key, $value, auth()->id());
        }

        AuditLog::record('system_settings.updated', null, ['keys' => array_keys($map)]);

        $this->reset(['logo', 'favicon']);
        session()->flash('message', 'System settings saved.');
    }

    public function render()
    {
        $this->assertSuperAdmin();

        return view('livewire.system-settings', [
            'languages' => Language::where('is_active', true)->orderBy('name')->get(),
            'currencies' => Currency::where('is_active', true)->orderBy('name')->get(),
            'currentLogoPath' => SystemSetting::get('platform_logo_path'),
            'currentFaviconPath' => SystemSetting::get('platform_favicon_path'),
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
