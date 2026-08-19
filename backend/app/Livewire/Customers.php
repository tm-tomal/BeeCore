<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\CustomerSubscription;
use App\Models\Package;
use App\Models\User;
use App\Support\AuthorizesRoles;
use App\Support\CurrentTenant;
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

    public $name = '';
    public $email = '';
    public $phone = '';
    public $status = 'active';
    public $package_name = '';
    public $package_id = '';
    public $billing_cycle = 'monthly';
    public $next_billing_date = '';

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
        ];
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['name', 'email', 'phone', 'status', 'package_name', 'package_id', 'billing_cycle', 'next_billing_date', 'customerId']);
        $this->billing_cycle = 'monthly';
        $this->next_billing_date = today()->toDateString();
        $this->isEditing = false;
        $this->viewMode = 'create';
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
        
        $this->isEditing = true;
        $this->viewMode = 'create';
    }

    public function save()
    {
        $this->validate();

        $tenantId = app(CurrentTenant::class)->id();

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
            ])->save();
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
        return view('livewire.customers', [
            'customers' => $this->customers()->with('activeSubscription')->latest()->paginate(10),
            'packages' => Package::query()->where('tenant_id', app(CurrentTenant::class)->id())->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    private function customers()
    {
        return Customer::query()->where('tenant_id', app(CurrentTenant::class)->id());
    }
}
