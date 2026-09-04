<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\BackupRecord;
use App\Models\Customer;
use App\Models\DataExport;
use App\Models\Invoice;
use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Support\PlanQuota;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class DataManagement extends Component
{
    use WithFileUploads;

    public string $tab = 'backups';

    public int $retentionDays = 365;

    // Export form
    public ?int $exportTenantId = null;
    public string $exportType = 'full';

    // Import form
    public ?int $importTenantId = null;
    public $importFile;
    public ?int $lastImportedCount = null;

    public function mount(): void
    {
        $this->assertSuperAdmin();
        $this->retentionDays = (int) SystemSetting::get('data_retention_days', 365);
    }

    public function saveRetention(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate(['retentionDays' => ['required', 'integer', 'min:30', 'max:3650']]);

        SystemSetting::set('data_retention_days', (string) $data['retentionDays'], auth()->id());
        AuditLog::record('data_management.retention_updated', null, ['days' => $data['retentionDays']]);
        session()->flash('message', 'Data retention policy updated.');
    }

    public function runBackupNow(): void
    {
        $this->assertSuperAdmin();

        $manifest = [
            'generated_at' => now()->toISOString(),
            'tables' => [
                'tenants' => Tenant::count(),
                'users' => DB::table('users')->count(),
                'customers' => Customer::count(),
                'invoices' => Invoice::count(),
            ],
        ];

        $path = 'backups/backup-'.now()->format('Ymd-His').'.json';
        Storage::disk('local')->put($path, json_encode($manifest, JSON_PRETTY_PRINT));

        $record = BackupRecord::create([
            'type' => 'manual',
            'status' => 'completed',
            'disk' => 'local',
            'path' => $path,
            'size_bytes' => Storage::disk('local')->size($path),
            'triggered_by' => auth()->id(),
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        AuditLog::record('data_management.backup_created', $record, ['path' => $path]);
        session()->flash('message', 'Backup completed and stored.');
    }

    public function runExport(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate([
            'exportTenantId' => ['nullable', 'exists:tenants,id'],
            'exportType' => ['required', Rule::in(['customers', 'invoices', 'full'])],
        ]);

        $tenantId = $data['exportTenantId'] ?: null;
        $payload = match ($data['exportType']) {
            'customers' => Customer::query()->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->get()->toArray(),
            'invoices' => Invoice::query()->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->get()->toArray(),
            default => [
                'customers' => Customer::query()->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->get()->toArray(),
                'invoices' => Invoice::query()->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->get()->toArray(),
            ],
        };

        $slug = $tenantId ? 'tenant-'.$tenantId : 'platform';
        $path = "exports/{$slug}-{$data['exportType']}-".now()->format('Ymd-His').'.json';
        Storage::disk('local')->put($path, json_encode($payload, JSON_PRETTY_PRINT));

        $export = DataExport::create([
            'tenant_id' => $tenantId,
            'type' => $data['exportType'],
            'status' => 'completed',
            'disk' => 'local',
            'path' => $path,
            'requested_by' => auth()->id(),
            'completed_at' => now(),
        ]);

        AuditLog::record('data_management.export_created', $export, ['type' => $export->type], tenantId: $tenantId);
        session()->flash('message', 'Data export generated.');
    }

    public function downloadExport(int $id)
    {
        $this->assertSuperAdmin();
        $export = DataExport::findOrFail($id);

        return Storage::disk($export->disk)->download($export->path);
    }

    public function downloadBackup(int $id)
    {
        $this->assertSuperAdmin();
        $backup = BackupRecord::findOrFail($id);

        return Storage::disk($backup->disk)->download($backup->path);
    }

    public function importCustomers(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate([
            'importTenantId' => ['required', 'exists:tenants,id'],
            'importFile' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $rows = array_map('str_getcsv', file($this->importFile->getRealPath()));
        $header = array_map('trim', array_shift($rows));
        $candidates = collect($rows)->filter(fn ($row) => count($row) >= count($header))->count();
        $imported = 0;

        // A tenant can never be bulk-imported beyond what its plan allows.
        $tenant = Tenant::find($data['importTenantId']);
        if ($tenant && $candidates > 0) {
            $gate = PlanQuota::check($tenant, PlanQuota::CUSTOMERS, $candidates);
            if (! $gate['allowed']) {
                session()->flash('plan_error', $gate + [
                    'actionUrl' => route('tenant-details', $tenant),
                    'actionLabel' => __('Open tenant subscription'),
                ]);
                $this->reset(['importFile']);

                return;
            }
        }

        foreach ($rows as $row) {
            if (count($row) < count($header)) {
                continue;
            }
            $record = array_combine($header, $row);
            if (empty($record['name']) || empty($record['email'])) {
                continue;
            }

            Customer::create([
                'tenant_id' => $data['importTenantId'],
                'name' => $record['name'],
                'email' => $record['email'],
                'phone' => $record['phone'] ?? null,
                'package_name' => $record['package_name'] ?? null,
                'status' => 'active',
            ]);
            $imported++;
        }

        AuditLog::record('data_management.customers_imported', null, ['count' => $imported], tenantId: $data['importTenantId']);
        $this->lastImportedCount = $imported;
        $this->reset(['importFile']);
        session()->flash('message', "Imported {$imported} customer(s).");
    }

    public function render()
    {
        $this->assertSuperAdmin();

        return view('livewire.data-management', [
            'backups' => BackupRecord::query()->with('triggeredBy')->latest('started_at')->limit(20)->get(),
            'exports' => DataExport::query()->with(['tenant', 'requestedBy'])->latest()->limit(20)->get(),
            'tenants' => Tenant::query()->whereNull('archived_at')->orderBy('name')->get(),
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
