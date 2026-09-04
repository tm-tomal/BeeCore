<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\CustomerSubscription;
use App\Models\Package;
use App\Models\User;
use App\Support\AuthorizesRoles;
use App\Support\CurrentTenant;
use App\Support\PlanQuota;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Customers extends Component
{
    use AuthorizesRoles, WithPagination;

    public function boot(): void
    {
        $this->authorizeRoles(User::ROLE_SUPER_ADMIN, User::ROLE_TENANT_ADMIN, User::ROLE_SUPPORT, User::ROLE_NETWORK_ENGINEER);
    }

    public $viewMode = 'index'; // Change from showModal to viewMode
    public $isEditing = false;
    public $customerId;

    public $search = '';
    public $statusFilter = '';

    public $name = '';
    public $email = '';
    public $phone = '';
    public $status = 'active';
    public $package_name = '';
    public $package_id = '';
    public $billing_cycle = 'monthly';
    public $next_billing_date = '';
    public $pppoe_username = '';
    public $pppoe_password = '';

    public $address_house = '';
    public $address_street = '';
    public $address_area = '';
    public $address_city = '';
    public $address_postcode = '';
    public $address_latitude = '';
    public $address_longitude = '';

    protected function rules(): array
    {
        $tenantId = app(CurrentTenant::class)->id();

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:25',
            'status' => 'required|in:active,inactive,pending,suspended',
            'package_name' => 'nullable|string|max:255',
            'package_id' => ['nullable', Rule::exists('packages', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)->where('is_active', true))],
            'billing_cycle' => 'required_with:package_id|in:monthly,quarterly,semiannual,yearly',
            'next_billing_date' => 'required_with:package_id|nullable|date',
            'pppoe_username' => 'nullable|string|max:190',
            'pppoe_password' => 'nullable|string|max:255',
        ];
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['name', 'email', 'phone', 'status', 'package_name', 'package_id', 'billing_cycle', 'next_billing_date', 'pppoe_username', 'pppoe_password', 'customerId', 'address_house', 'address_street', 'address_area', 'address_city', 'address_postcode', 'address_latitude', 'address_longitude']);
        $this->billing_cycle = 'monthly';
        $this->next_billing_date = today()->toDateString();
        $this->isEditing = false;
        $this->viewMode = 'create';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function cancel()
    {
        $this->viewMode = 'index';
    }

    public function edit($id)
    {
        $this->resetValidation();
        $customer = $this->customers()->with('activeSubscription')->findOrFail($id);
        $this->customerId = $customer->id;
        $this->name = $customer->name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->status = $customer->status;
        $this->package_name = $customer->package_name;
        $this->package_id = $customer->activeSubscription?->package_id ?? '';
        $this->billing_cycle = $customer->activeSubscription?->billing_cycle ?? 'monthly';
        $this->next_billing_date = $customer->activeSubscription?->next_billing_date?->toDateString() ?? today()->toDateString();
        $this->pppoe_username = $customer->activeSubscription?->pppoe_username ?? '';
        $this->pppoe_password = '';

        $this->address_house = (string) ($customer->address['house'] ?? '');
        $this->address_street = (string) ($customer->address['street'] ?? '');
        $this->address_area = (string) ($customer->address['area'] ?? '');
        $this->address_city = (string) ($customer->address['city'] ?? '');
        $this->address_postcode = (string) ($customer->address['postcode'] ?? '');
        $this->address_latitude = (string) ($customer->address['latitude'] ?? '');
        $this->address_longitude = (string) ($customer->address['longitude'] ?? '');

        $this->isEditing = true;
        $this->viewMode = 'create';
    }

    public function save()
    {
        $this->validate();

        $tenantId = app(CurrentTenant::class)->id();

        if (! $this->isEditing) {
            $tenant = app(CurrentTenant::class)->resolve();
            $gate = $tenant ? PlanQuota::check($tenant, PlanQuota::CUSTOMERS) : ['allowed' => true];

            if (! $gate['allowed']) {
                $this->viewMode = 'index';
                session()->flash('plan_error', $gate + [
                    'actionUrl' => route('isp-subscription'),
                    'actionLabel' => __('View plans & upgrade'),
                ]);

                return;
            }
        }

        DB::transaction(function () use ($tenantId) {
            $package = $this->package_id
                ? Package::query()->where('tenant_id', $tenantId)->where('is_active', true)->findOrFail($this->package_id)
                : null;
            $attributes = [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'status' => $this->status,
                'package_name' => $package?->name ?? $this->package_name,
                'address' => $this->buildAddressArray(),
            ];

            $customer = $this->isEditing
                ? tap($this->customers()->findOrFail($this->customerId))->update($attributes)
                : Customer::create(['tenant_id' => $tenantId] + $attributes);

            $existing = $customer->subscriptions()->whereIn('status', ['active', 'paused'])->latest()->first();

            if (!$package) {
                $existing?->update(['status' => 'cancelled', 'ended_at' => today()]);
                return;
            }

            $subscription = $existing ?? new CustomerSubscription([
                'tenant_id' => $tenantId,
                'customer_id' => $customer->id,
                'started_at' => today(),
            ]);
            $subscription->fill([
                'package_id' => $package->id,
                'package_name' => $package->name,
                'price' => $package->price,
                'billing_cycle' => $this->billing_cycle,
                'status' => $this->status === 'active' ? 'active' : 'paused',
                'next_billing_date' => $this->next_billing_date,
                'ended_at' => null,
            ]);
            if ($this->pppoe_username !== '') {
                $subscription->pppoe_username = $this->pppoe_username;
            }
            if ($this->pppoe_password !== '') {
                $subscription->pppoe_password = $this->pppoe_password;
            }
            $subscription->save();
        });

        $this->viewMode = 'index';
        session()->flash('message', $this->isEditing ? 'Customer updated successfully.' : 'Customer created successfully.');
    }

    public function delete($id)
    {
        $this->customers()->findOrFail($id)->delete();
        session()->flash('message', 'Customer deleted successfully.');
    }

    public function render()
    {
        $tenantId = app(CurrentTenant::class)->id();
        $tenant = app(CurrentTenant::class)->resolve();
        $automatic = $tenant?->isAutomatic() ?? true;

        $customers = Customer::query()
            ->where('tenant_id', $tenantId)
            ->with('activeSubscription')
            ->when($this->search !== '', fn ($query) => $query->where(function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('phone', 'like', '%'.$this->search.'%');
            }))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);

        $packages = Package::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.customers', [
            'customers' => $customers,
            'packages' => $packages,
            'isAutomatic' => $automatic,
            'packageOptions' => collect(['' => 'No recurring package'])->union(
                $packages->mapWithKeys(fn ($package) => [$package->id => $package->name.($package->bandwidth ? ' ('.$package->bandwidth.')' : '').' — ৳'.number_format($package->price, 2)])
            )->map(fn ($label, $value) => ['value' => (string) $value, 'label' => (string) $label])->values()->all(),
        ]);
    }

    private function customers()
    {
        return Customer::query()->where('tenant_id', app(CurrentTenant::class)->id());
    }

    /**
     * Assemble the JSON address payload saved on the customer row. Only filled
     * parts are stored; coordinates need a valid lat + lng pair.
     */
    private function buildAddressArray(): ?array
    {
        $address = [];

        foreach (['house', 'street', 'area', 'city', 'postcode'] as $key) {
            $value = trim((string) $this->{'address_'.$key});
            if ($value !== '') {
                $address[$key] = $value;
            }
        }

        $lat = trim((string) $this->address_latitude);
        $lng = trim((string) $this->address_longitude);

        if ($lat !== '' && $lng !== '' && is_numeric($lat) && is_numeric($lng)) {
            $address['latitude'] = (string) round((float) $lat, 6);
            $address['longitude'] = (string) round((float) $lng, 6);
        }

        return $address ?: null;
    }
}
