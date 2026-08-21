<?php

namespace Tests\Feature;

use App\Livewire\SystemSettings;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SystemSettingsConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_update_system_settings(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(SystemSettings::class)
            ->set('platformName', 'GreenNet ISP Suite')
            ->set('invoicePrefix', 'GNI')
            ->set('invoiceDueDays', 14)
            ->set('sessionLifetimeMinutes', 240)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('GreenNet ISP Suite', SystemSetting::get('platform_name'));
        $this->assertSame('GNI', SystemSetting::get('invoice_prefix'));
        $this->assertSame('14', SystemSetting::get('invoice_due_days'));
        $this->assertSame('240', SystemSetting::get('session_lifetime_minutes'));
    }

    public function test_super_admin_can_upload_a_platform_logo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(SystemSettings::class)
            ->set('logo', UploadedFile::fake()->image('logo.png'))
            ->call('save')
            ->assertHasNoErrors();

        $path = SystemSetting::get('platform_logo_path');
        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);
    }
}
