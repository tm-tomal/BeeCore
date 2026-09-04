<?php

namespace App\Livewire;

use App\Models\Addon;
use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\TenantAddon;
use App\Models\TenantSmsBalance;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AddOns extends Component
{
    public string $tab = 'catalog';

    // Catalog form
    public string $viewMode = 'index';
    public ?int $addonId = null;
    public string $name = '';
    public string $slug = '';
    public string $category = 'sms';
    public string $description = '';
    public float $price = 0;
    public string $billingCycle = 'monthly';
    public ?int $usageLimit = null;
    public string $usageUnit = '';

    // Assignment form
    public ?int $assignTenantId = null;
    public ?int $assignAddonId = null;
    public string $assignBillingCycle = 'monthly';

    public ?int $usageForAssignmentId = null;
    public int $usageAmount = 1;

    public const CATEGORIES = [
        'sms' => 'SMS package', 'email' => 'Email package', 'storage' => 'Storage package',
        'media' => 'Media package', 'white_label' => 'White label', 'custom_domain' => 'Custom domain',
        'branded_app' => 'Branded mobile app', 'premium_support' => 'Premium support',
        'network_integration' => 'Advanced network integration', 'infrastructure' => 'Dedicated infrastructure',
        'custom_dev' => 'Custom development',
    ];

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('addons', 'slug')->ignore($this->addonId)],
            'category' => ['required', Rule::in(array_keys(self::CATEGORIES))],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'billingCycle' => ['required', Rule::in(['one_time', 'monthly', 'yearly'])],
            // SMS packages sell wallet credits — an SMS add-on without a
            // usage limit would activate without adding any SMS to the wallet.
            'usageLimit' => array_merge(
                $this->category === 'sms' ? ['required'] : ['nullable'],
                ['integer', 'min:1'],
            ),
            'usageUnit' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function updatedName($value): void
    {
        if (!$this->addonId) {
            $this->slug = Str::slug($value);
        }
    }

    public function create(): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $this->reset(['addonId', 'name', 'slug', 'description', 'price', 'usageLimit', 'usageUnit']);
        $this->category = 'sms';
        $this->billingCycle = 'monthly';
        $this->viewMode = 'create';
    }

    public function edit(int $id): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $addon = Addon::findOrFail($id);
        $this->addonId = $addon->id;
        $this->name = $addon->name;
        $this->slug = $addon->slug;
        $this->category = $addon->category;
        $this->description = $addon->description ?? '';
        $this->price = (float) $addon->price;
        $this->billingCycle = $addon->billing_cycle;
        $this->usageLimit = $addon->usage_limit;
        $this->usageUnit = $addon->usage_unit ?? '';
        $this->viewMode = 'create';
    }

    public function cancelForm(): void
    {
        $this->viewMode = 'index';
    }

    public function save(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate();

        $attributes = [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'category' => $data['category'],
            'description' => $data['description'] ?: null,
            'price' => $data['price'],
            'billing_cycle' => $data['billingCycle'],
            'usage_limit' => $data['usageLimit'] ?: null,
            'usage_unit' => $data['usageUnit'] ?: null,
        ];

        $addon = $this->addonId ? Addon::findOrFail($this->addonId) : new Addon(['is_active' => true]);
        $addon->fill($attributes)->save();

        AuditLog::record($this->addonId ? 'addon.updated' : 'addon.created', $addon, ['category' => $addon->category]);

        $this->viewMode = 'index';
        session()->flash('message', $this->addonId ? 'Add-on updated.' : 'Add-on created.');
    }

    public function toggleActive(int $id): void
    {
        $this->assertSuperAdmin();
        $addon = Addon::findOrFail($id);
        $addon->update(['is_active' => !$addon->is_active]);
        AuditLog::record($addon->is_active ? 'addon.activated' : 'addon.deactivated', $addon);
        session()->flash('message', 'Add-on '.($addon->is_active ? 'activated' : 'deactivated').'.');
    }

    public function archive(int $id): void
    {
        $this->assertSuperAdmin();
        $addon = Addon::whereNull('archived_at')->findOrFail($id);
        $addon->update(['is_active' => false, 'archived_at' => now()]);
        AuditLog::record('addon.archived', $addon);
        session()->flash('message', 'Add-on archived.');
    }

    public function assignAddon(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate([
            'assignTenantId' => ['required', 'exists:tenants,id'],
            'assignAddonId' => ['required', Rule::exists('addons', 'id')->where(fn ($q) => $q->where('is_active', true)->whereNull('archived_at'))],
            'assignBillingCycle' => ['required', Rule::in(['one_time', 'monthly', 'yearly'])],
        ]);

        $addon = Addon::findOrFail($data['assignAddonId']);

        $assignment = TenantAddon::create([
            'tenant_id' => $data['assignTenantId'],
            'addon_id' => $addon->id,
            'status' => 'active',
            'price' => $addon->price,
            'billing_cycle' => $data['assignBillingCycle'],
            'assigned_by' => auth()->id(),
            'starts_at' => now(),
        ]);

        \App\Services\SmsGateway::creditSmsAddon($assignment);

        AuditLog::record('addon.assigned', $assignment, ['addon_id' => $addon->id], tenantId: $data['assignTenantId']);

        $this->reset(['assignTenantId', 'assignAddonId']);
        $this->assignBillingCycle = 'monthly';
        session()->flash('message', 'Add-on assigned to tenant.');
    }

    public function cancelAssignment(int $id): void
    {
        $this->assertSuperAdmin();
        $assignment = TenantAddon::where('status', 'active')->findOrFail($id);
        $assignment->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        AuditLog::record('addon.assignment_cancelled', $assignment, tenantId: $assignment->tenant_id);
        session()->flash('message', 'Add-on assignment cancelled.');
    }

    public function approveRequest(int $id): void
    {
        $this->assertSuperAdmin();
        $assignment = TenantAddon::whereIn('status', ['requested', 'pending_approval'])->with('addon')->findOrFail($id);

        abort_unless($assignment->addon?->is_active && ! $assignment->addon->archived_at, 422, 'This add-on is no longer available.');

        $assignment->update(['status' => 'active', 'starts_at' => now(), 'assigned_by' => auth()->id()]);

        \App\Services\SmsGateway::creditSmsAddon($assignment);

        AuditLog::record('addon.request_approved', $assignment, ['addon_id' => $assignment->addon_id], tenantId: $assignment->tenant_id);
        session()->flash('message', 'Add-on request approved and activated.');
    }

    public function declineRequest(int $id): void
    {
        $this->assertSuperAdmin();
        $assignment = TenantAddon::whereIn('status', ['requested', 'pending_approval'])->findOrFail($id);
        $assignment->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        AuditLog::record('addon.request_declined', $assignment, ['addon_id' => $assignment->addon_id], tenantId: $assignment->tenant_id);
        session()->flash('message', 'Add-on request declined.');
    }

    public function openUsage(int $assignmentId): void
    {
        $this->usageForAssignmentId = $assignmentId;
        $this->usageAmount = 1;
    }

    public function recordUsage(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate(['usageAmount' => ['required', 'integer', 'min:1']]);

        $assignment = TenantAddon::findOrFail($this->usageForAssignmentId);
        $assignment->increment('usage_used', $data['usageAmount']);

        AuditLog::record('addon.usage_recorded', $assignment, ['amount' => $data['usageAmount']], tenantId: $assignment->tenant_id);

        $this->usageForAssignmentId = null;
        session()->flash('message', 'Usage recorded.');
    }

    public function closeModals(): void
    {
        $this->usageForAssignmentId = null;
    }

    public function render()
    {
        $this->assertSuperAdmin();

        $addons = Addon::query()->whereNull('archived_at')->withCount(['tenantAddons as active_assignments' => fn ($q) => $q->where('status', 'active')])->orderBy('name')->get();

        // Live SMS wallet position per tenant so SMS assignments show the real
        // remaining/used credits instead of the (never bumped) usage counter.
        $smsRows = TenantAddon::query()
            ->where('status', 'active')
            ->whereHas('addon', fn ($q) => $q->where('category', 'sms'))
            ->with('addon')
            ->get();

        $smsWalletByTenant = $smsRows
            ->groupBy('tenant_id')
            ->map(function ($rows, $tenantId) {
                $included = (int) $rows->sum(fn ($row) => (int) ($row->addon->usage_limit ?? 0));
                $remaining = (int) (TenantSmsBalance::query()->where('tenant_id', $tenantId)->value('balance') ?? 0);

                return [
                    'included' => $included,
                    'remaining' => $remaining,
                    'used' => max(0, $included - $remaining),
                ];
            })
            ->all();

        return view('livewire.add-ons', [
            'addons' => $addons,
            'assignments' => TenantAddon::query()->with(['tenant', 'addon'])->latest()->limit(50)->get(),
            'activeAddons' => Addon::query()->where('is_active', true)->whereNull('archived_at')->orderBy('name')->get(),
            'tenants' => Tenant::query()->whereNull('archived_at')->orderBy('name')->get(),
            'revenueByAddon' => TenantAddon::query()->where('status', 'active')->selectRaw('addon_id, sum(price) as total, count(*) as count')->groupBy('addon_id')->get()->keyBy('addon_id'),
            'smsWalletByTenant' => $smsWalletByTenant,
            'stats' => [
                'catalog' => $addons->count(),
                'active_catalog' => $addons->where('is_active', true)->count(),
                'active_assignments' => (int) TenantAddon::query()->where('status', 'active')->count(),
                'pending_approvals' => (int) TenantAddon::query()->whereIn('status', ['requested', 'pending_approval'])->count(),
                'revenue_monthly' => (float) TenantAddon::query()
                    ->where('status', 'active')
                    ->whereIn('billing_cycle', ['monthly', 'yearly'])
                    ->get()
                    ->sum(fn ($a) => $a->billing_cycle === 'yearly' ? (float) $a->price / 12 : (float) $a->price),
            ],
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
