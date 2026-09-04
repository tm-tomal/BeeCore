<?php

namespace Tests\Feature;

use App\Livewire\DataManagement;
use App\Models\BackupRecord;
use App\Models\DataExport;
use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\CreatesPlanSubscriptions;
use Tests\TestCase;

class DataManagementConsoleTest extends TestCase
{
    use RefreshDatabase, CreatesPlanSubscriptions;

    private function tenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Data ISP', 'slug' => 'data-isp-'.uniqid(), 'status' => 'active',
            'currency' => 'BDT', 'timezone' => 'Asia/Dhaka',
        ]);
    }

    public function test_super_admin_can_run_a_backup_and_a_manifest_file_is_written(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(DataManagement::class)
            ->call('runBackupNow')
            ->assertHasNoErrors();

        $backup = BackupRecord::firstOrFail();
        $this->assertSame('completed', $backup->status);
        Storage::disk('local')->assertExists($backup->path);
    }

    public function test_super_admin_can_generate_a_tenant_data_export(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $tenant = $this->tenant();

        Livewire::actingAs($admin)->test(DataManagement::class)
            ->set('tab', 'exports')
            ->set('exportTenantId', $tenant->id)
            ->set('exportType', 'customers')
            ->call('runExport')
            ->assertHasNoErrors();

        $export = DataExport::firstOrFail();
        $this->assertSame($tenant->id, $export->tenant_id);
        $this->assertSame('completed', $export->status);
        Storage::disk('local')->assertExists($export->path);
    }

    public function test_super_admin_can_import_customers_from_csv(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();
        $this->attachActivePlan($tenant, customerLimit: 100);

        $csv = "name,email,phone,package_name\nJohn Doe,john@example.com,0170000000,Basic\nJane Roe,jane@example.com,0180000000,Pro\n";
        $file = UploadedFile::fake()->createWithContent('customers.csv', $csv);

        Livewire::actingAs($admin)->test(DataManagement::class)
            ->set('tab', 'import')
            ->set('importTenantId', $tenant->id)
            ->set('importFile', $file)
            ->call('importCustomers')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customers', ['tenant_id' => $tenant->id, 'email' => 'john@example.com']);
        $this->assertDatabaseHas('customers', ['tenant_id' => $tenant->id, 'email' => 'jane@example.com']);
    }

    public function test_super_admin_can_update_data_retention_policy(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(DataManagement::class)
            ->set('tab', 'retention')
            ->set('retentionDays', 730)
            ->call('saveRetention')
            ->assertHasNoErrors();

        $this->assertSame('730', SystemSetting::get('data_retention_days'));
    }
}
