<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\TenantSubscriptionEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class IspOnboarding extends Component
{
    // Company information
    public string $name = '';
    public string $slug = '';
    public string $companyLegalName = '';
    public string $businessType = '';

    // Owner information
    public string $ownerName = '';
    public string $ownerEmail = '';
    public string $ownerPhone = '';

    // Contact information
    public string $contactPhone = '';
    public string $contactAddress = '';

    // Locale / billing configuration
    public string $currency = 'BDT';
    public string $timezone = 'Asia/Dhaka';

    // Domain / subdomain setup
    public string $subdomain = '';
    public string $customDomain = '';

    // Initial package setup
    public ?int $planId = null;
    public string $billingCycle = 'monthly';

    // Initial admin account
    public string $adminName = '';
    public string $adminEmail = '';
    public string $adminPassword = '';

    public ?Tenant $onboardedTenant = null;

    public function updatedName($value): void
    {
        $this->slug = Str::slug($value);
        $this->subdomain = Str::slug($value);
    }

    #[Computed]
    public function checklist(): array
    {
        return [
            'Company information' => filled($this->name) && filled($this->companyLegalName) && filled($this->businessType),
            'Owner information' => filled($this->ownerName) && filled($this->ownerEmail),
            'Contact information' => filled($this->contactPhone) && filled($this->contactAddress),
            'Locale & billing configuration' => filled($this->currency) && filled($this->timezone),
            'Domain / subdomain setup' => filled($this->subdomain),
            'Initial package setup' => filled($this->planId),
            'Initial admin account' => filled($this->adminName) && filled($this->adminEmail) && filled($this->adminPassword),
        ];
    }

    public function register(): void
    {
        $this->assertSuperAdmin();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:tenants,slug'],
            'companyLegalName' => ['required', 'string', 'max:255'],
            'businessType' => ['required', 'string', 'max:255'],
            'ownerName' => ['required', 'string', 'max:255'],
            'ownerEmail' => ['required', 'email', 'max:255'],
            'ownerPhone' => ['required', 'string', 'max:30'],
            'contactPhone' => ['required', 'string', 'max:30'],
            'contactAddress' => ['required', 'string', 'max:500'],
            'currency' => ['required', 'string', 'max:10'],
            'timezone' => ['required', 'string', 'max:50'],
            'subdomain' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:tenants,subdomain'],
            'customDomain' => ['nullable', 'string', 'max:255', 'unique:tenants,custom_domain'],
            'planId' => ['required', Rule::exists('saas_plans', 'id')->where(fn ($query) => $query->where('is_active', true)->whereNull('archived_at'))],
            'billingCycle' => ['required', Rule::in(['monthly', 'yearly'])],
            'adminName' => ['required', 'string', 'max:255'],
            'adminEmail' => ['required', 'email', 'max:255', 'unique:users,email'],
            'adminPassword' => ['required', 'string', 'min:8'],
        ]);

        $tenant = DB::transaction(function () use ($validated) {
            $plan = SaasPlan::findOrFail($validated['planId']);

            $tenant = Tenant::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'status' => 'trial',
                'currency' => $validated['currency'],
                'timezone' => $validated['timezone'],
                'company_legal_name' => $validated['companyLegalName'],
                'business_type' => $validated['businessType'],
                'owner_name' => $validated['ownerName'],
                'owner_email' => $validated['ownerEmail'],
                'owner_phone' => $validated['ownerPhone'],
                'contact_phone' => $validated['contactPhone'],
                'contact_address' => $validated['contactAddress'],
                'subdomain' => $validated['subdomain'],
                'custom_domain' => $validated['customDomain'] ?: null,
                'onboarding_status' => 'completed',
                'onboarding_completed_at' => now(),
            ]);

            $admin = User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['adminName'],
                'email' => $validated['adminEmail'],
                'password' => $validated['adminPassword'],
                'role' => User::ROLE_TENANT_ADMIN,
                'status' => 'active',
            ]);

            $startsAt = today();
            $trialEnds = $plan->trial_days > 0 ? $startsAt->copy()->addDays($plan->trial_days) : null;
            $periodEnd = $validated['billingCycle'] === 'yearly'
                ? $startsAt->copy()->addYear()->subDay()
                : $startsAt->copy()->addMonth()->subDay();

            $subscription = TenantSubscription::create([
                'tenant_id' => $tenant->id,
                'saas_plan_id' => $plan->id,
                'status' => $trialEnds ? 'trialing' : 'active',
                'billing_cycle' => $validated['billingCycle'],
                'price' => $validated['billingCycle'] === 'yearly' ? $plan->yearly_price : $plan->monthly_price,
                'starts_at' => $startsAt,
                'trial_ends_at' => $trialEnds,
                'current_period_ends_at' => $periodEnd,
                'grace_ends_at' => $periodEnd->copy()->addDays($plan->grace_days),
                'auto_renew' => true,
            ]);

            TenantSubscriptionEvent::create([
                'tenant_subscription_id' => $subscription->id,
                'user_id' => auth()->id(),
                'event' => 'subscription.created',
                'from_status' => null,
                'to_status' => $subscription->status,
                'metadata' => ['plan_id' => $plan->id, 'billing_cycle' => $subscription->billing_cycle, 'price' => $subscription->price, 'source' => 'isp_onboarding'],
                'created_at' => now(),
            ]);

            AuditLog::record('tenant.onboarded', $tenant, [
                'plan_id' => $plan->id,
                'admin_user_id' => $admin->id,
            ], tenantId: $tenant->id);

            return $tenant;
        });

        $this->onboardedTenant = $tenant;
        $this->reset([
            'name', 'slug', 'companyLegalName', 'businessType',
            'ownerName', 'ownerEmail', 'ownerPhone',
            'contactPhone', 'contactAddress',
            'subdomain', 'customDomain',
            'planId', 'adminName', 'adminEmail', 'adminPassword',
        ]);
        $this->currency = 'BDT';
        $this->timezone = 'Asia/Dhaka';
        $this->billingCycle = 'monthly';

        session()->flash('message', 'ISP onboarded successfully.');
    }

    public function render()
    {
        $this->assertSuperAdmin();

        return view('livewire.isp-onboarding', [
            'plans' => SaasPlan::query()->where('is_active', true)->whereNull('archived_at')->orderBy('monthly_price')->get(),
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
