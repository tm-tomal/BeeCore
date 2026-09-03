<?php

namespace Tests\Feature;

use App\Livewire\WhiteLabel;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class WhiteLabelConsoleTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Branding ISP', 'slug' => 'branding-isp-'.uniqid(), 'status' => 'active',
            'currency' => 'BDT', 'timezone' => 'Asia/Dhaka',
        ]);
    }

    public function test_super_admin_can_save_branding_with_logo_upload(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $tenant = $this->tenant();

        Livewire::actingAs($admin)->test(WhiteLabel::class)
            ->set('selectedTenantId', $tenant->id)
            ->set('isEnabled', true)
            ->set('brandName', 'Green Fiber')
            ->set('brandColor', '#00ff00')
            ->set('appName', 'Green Fiber App')
            ->set('logo', UploadedFile::fake()->image('logo.png'))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tenant_brandings', [
            'tenant_id' => $tenant->id, 'is_enabled' => true, 'brand_name' => 'Green Fiber',
        ]);

        $branding = \App\Models\TenantBranding::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertNotNull($branding->logo_path);
        Storage::disk('public')->assertExists($branding->logo_path);
    }

    public function test_selecting_a_tenant_loads_its_existing_branding(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        \App\Models\TenantBranding::create([
            'tenant_id' => $tenant->id, 'is_enabled' => true, 'brand_name' => 'Existing Brand',
        ]);

        Livewire::actingAs($admin)->test(WhiteLabel::class)
            ->set('selectedTenantId', $tenant->id)
            ->assertSet('brandName', 'Existing Brand')
            ->assertSet('isEnabled', true);
    }
}
