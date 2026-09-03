<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Language;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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

    public $search = '';

    public $name = '';
    public $slug = '';
    public $status = 'active';
    public $operationMode = 'manual';
    public $currency = 'BDT';
    public $timezone = 'Asia/Dhaka';
    public $language = 'en';

    // Company information
    public $companyLegalName = '';
    public $businessType = '';

    // Contact information
    public $contactPhone = '';
    public $contactAddress = '';

    // Domain / subdomain setup
    public $subdomain = '';
    public $customDomain = '';

    // ISP owner account (required on create, editable later)
    public $ownerName = '';
    public $ownerEmail = '';
    public $ownerPhone = '';
    public $password = '';
    public $passwordConfirmation = '';

    protected function rules() {
        $ignore = $this->isEditing ? ',' . $this->tenantId : '';

        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:tenants,slug' . $ignore,
            'status' => 'nullable|in:active,suspended',
            'operationMode' => 'nullable|in:automatic,manual',
            'currency' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:10|exists:languages,code',
            'companyLegalName' => 'nullable|string|max:255',
            'businessType' => 'nullable|string|max:255',
            'contactPhone' => 'nullable|string|max:30',
            'contactAddress' => 'nullable|string|max:500',
            'subdomain' => 'nullable|string|max:255|alpha_dash|unique:tenants,subdomain' . $ignore,
            'customDomain' => 'nullable|string|max:255|unique:tenants,custom_domain' . $ignore,
            'ownerName' => 'required|string|max:255',
            'ownerEmail' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->existingOwnerUserId()),
            ],
            'ownerPhone' => 'required|string|max:30',
            'password' => 'nullable|string|min:8',
            'passwordConfirmation' => 'nullable|same:password',
        ];
    }

    private function existingOwnerUserId(): ?int
    {
        if (!$this->isEditing) {
            return null;
        }

        $user = User::query()
            ->where('tenant_id', $this->tenantId)
            ->where('email', $this->ownerEmail)
            ->value('id');

        return $user ?: null;
    }

    public function updatedName($value)
    {
        if (!$this->isEditing) {
            $this->slug = Str::slug($value);
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function toggleStatus($id)
    {
        $this->assertSuperAdmin();

        $tenant = Tenant::query()->whereNull('archived_at')->findOrFail($id);
        $previous = $tenant->status;
        $next = $previous === 'active' ? 'suspended' : 'active';
        $tenant->update(['status' => $next]);

        AuditLog::record('tenant.status_toggled', $tenant, ['status' => $next, 'previous' => $previous], tenantId: $tenant->id);
    }

    public function create()
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $this->reset([
            'name', 'slug', 'status', 'operationMode', 'currency', 'timezone', 'language', 'tenantId',
            'companyLegalName', 'businessType', 'contactPhone', 'contactAddress',
            'subdomain', 'customDomain',
            'ownerName', 'ownerEmail', 'ownerPhone', 'password', 'passwordConfirmation',
        ]);
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
        $this->operationMode = $tenant->operation_mode ?? Tenant::MODE_AUTOMATIC;
        $this->currency = $tenant->currency;
        $this->timezone = $tenant->timezone;
        $this->language = $tenant->language;

        $this->companyLegalName = $tenant->company_legal_name ?? '';
        $this->businessType = $tenant->business_type ?? '';
        $this->contactPhone = $tenant->contact_phone ?? '';
        $this->contactAddress = $tenant->contact_address ?? '';
        $this->subdomain = $tenant->subdomain ?? '';
        $this->customDomain = $tenant->custom_domain ?? '';

        // Prefill owner profile from the tenant record (fall back to the existing owner user if present)
        $ownerUser = User::query()->where('tenant_id', $tenant->id)->where('email', $tenant->owner_email)->first()
            ?? User::query()->where('tenant_id', $tenant->id)->where('role', User::ROLE_TENANT_ADMIN)->first();

        $this->ownerName = $ownerUser?->name ?? $tenant->owner_name ?? '';
        $this->ownerEmail = $ownerUser?->email ?? $tenant->owner_email ?? '';
        $this->ownerPhone = $tenant->owner_phone ?? '';
        $this->password = '';
        $this->passwordConfirmation = '';

        $this->isEditing = true;
        $this->viewMode = 'create';
    }

    public function generatePassword(): void
    {
        $this->assertSuperAdmin();

        $password = Str::password(12);
        $this->password = $password;
        $this->passwordConfirmation = $password;
    }

    public function cancel()
    {
        $this->viewMode = 'index';
    }

    public function save()
    {
        $this->assertSuperAdmin();
        $this->validate();

        $generatedPassword = null;

        DB::transaction(function () use (&$generatedPassword) {
            $tenant = $this->isEditing ? Tenant::findOrFail($this->tenantId) : new Tenant;

            $tenant->fill([
                'name' => $this->name,
                'slug' => $this->slug !== '' ? $this->slug : Str::slug($this->name),
                'status' => $this->status !== '' ? $this->status : 'active',
                'operation_mode' => $this->operationMode !== '' ? $this->operationMode : Tenant::MODE_MANUAL,
                'currency' => $this->currency !== '' ? $this->currency : 'BDT',
                'timezone' => $this->timezone !== '' ? $this->timezone : 'Asia/Dhaka',
                'language' => $this->language !== '' ? $this->language : 'en',
                'company_legal_name' => $this->companyLegalName,
                'business_type' => $this->businessType,
                'contact_phone' => $this->contactPhone,
                'contact_address' => $this->contactAddress,
                'subdomain' => $this->subdomain !== '' ? $this->subdomain : null,
                'custom_domain' => $this->customDomain !== '' ? $this->customDomain : null,
                'owner_name' => $this->ownerName,
                'owner_email' => $this->ownerEmail,
                'owner_phone' => $this->ownerPhone,
            ]);
            $tenant->save();

            // Find the ISP owner account belonging to this tenant.
            $ownerUser = User::query()
                ->where('tenant_id', $tenant->id)
                ->where('email', $this->ownerEmail)
                ->first()
                ?? User::query()
                    ->where('tenant_id', $tenant->id)
                    ->where('role', User::ROLE_TENANT_ADMIN)
                    ->first();

            if ($ownerUser) {
                $ownerUser->name = $this->ownerName;
                $ownerUser->email = $this->ownerEmail;

                // Only touch the password when the admin typed a new one.
                if ($this->password !== '') {
                    $ownerUser->password = $this->password;
                }

                $ownerUser->save();
            } else {
                $finalPassword = $this->password !== '' ? $this->password : Str::password(12);
                if ($this->password === '') {
                    $generatedPassword = $finalPassword;
                }

                $ownerUser = User::create([
                    'tenant_id' => $tenant->id,
                    'name' => $this->ownerName,
                    'email' => $this->ownerEmail,
                    'password' => $finalPassword,
                    'role' => User::ROLE_TENANT_ADMIN,
                    'status' => 'active',
                ]);

                AuditLog::record('tenant.owner_user_created', $tenant, ['user_id' => $ownerUser->id], tenantId: $tenant->id);
            }
        });

        $this->viewMode = 'index';
        $message = $this->isEditing ? 'Tenant updated successfully.' : 'Tenant created successfully.';

        if ($generatedPassword) {
            $message .= " Owner login: {$this->ownerEmail} / {$generatedPassword}";
        }

        session()->flash('message', $message);
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
            'tenants' => Tenant::query()
                ->whereNull('archived_at')
                ->when($this->search !== '', fn ($query) => $query
                    ->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('slug', 'like', '%'.$this->search.'%'))
                ->latest()
                ->paginate(10),
            'languages' => Language::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
