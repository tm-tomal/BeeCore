<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Language;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Tenants extends Component
{
    use WithPagination;

    public $viewMode = 'index'; // Change to viewMode instead of showModal
    public $isEditing = false;
    public $tenantId;

    public $name = '';
    public $slug = '';
    public $status = 'active';
    public $currency = 'BDT';
    public $timezone = 'Asia/Dhaka';
    public $language = 'en';

    protected function rules() {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tenants,slug' . ($this->isEditing ? ',' . $this->tenantId : ''),
            'status' => 'required|in:active,suspended',
            'currency' => 'required|string|max:10',
            'timezone' => 'required|string|max:50',
            'language' => 'required|string|max:10|exists:languages,code',
        ];
    }

    public function updatedName($value)
    {
        if (!$this->isEditing) {
            $this->slug = Str::slug($value);
        }
    }

    public function create()
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $this->reset(['name', 'slug', 'status', 'currency', 'timezone', 'language', 'tenantId']);
        $this->isEditing = false;
        $this->viewMode = 'create';
    }

    public function edit($id)
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $tenant = Tenant::findOrFail($id);
        $this->tenantId = $tenant->id;
        $this->name = $tenant->name;
        $this->slug = $tenant->slug;
        $this->status = $tenant->status;
        $this->currency = $tenant->currency;
        $this->timezone = $tenant->timezone;
        $this->language = $tenant->language;

        $this->isEditing = true;
        $this->viewMode = 'create';
    }

    public function cancel()
    {
        $this->viewMode = 'index';
    }

    public function save()
    {
        $this->assertSuperAdmin();
        $this->validate();

        if ($this->isEditing) {
            Tenant::findOrFail($this->tenantId)->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'status' => $this->status,
                'currency' => $this->currency,
                'timezone' => $this->timezone,
                'language' => $this->language,
            ]);
        } else {
            Tenant::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'status' => $this->status,
                'currency' => $this->currency,
                'timezone' => $this->timezone,
                'language' => $this->language,
            ]);
        }

        $this->viewMode = 'index';
        session()->flash('message', $this->isEditing ? 'Tenant updated successfully.' : 'Tenant created successfully.');
    }

    public function delete($id)
    {
        $this->assertSuperAdmin();
        $tenant = Tenant::query()->whereNull('archived_at')->findOrFail($id);
        $tenant->update(['status' => 'inactive', 'archived_at' => now()]);
        AuditLog::record('tenant.archived', $tenant, tenantId: $tenant->id);
        session()->flash('message', 'Tenant archived successfully.');
    }

    public function impersonate($id)
    {
        $this->assertSuperAdmin();
        $tenant = Tenant::query()->where('status', 'active')->findOrFail($id);
        session()->put('impersonated_tenant_id', $tenant->id);
        session()->put('impersonated_tenant_name', $tenant->name);
        session()->migrate(true);
        AuditLog::record('tenant.impersonation.started', $tenant, tenantId: $tenant->id);

        return redirect()->route('dashboard');
    }

    public function render()
    {
        $this->assertSuperAdmin();

        return view('livewire.tenants', [
            'tenants' => Tenant::query()->whereNull('archived_at')->latest()->paginate(10),
            'languages' => Language::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
