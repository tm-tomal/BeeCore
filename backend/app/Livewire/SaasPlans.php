<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\SaasPlan;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class SaasPlans extends Component
{
    public bool $showModal = false;
    public ?int $planId = null;
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public $monthlyPrice = 0;
    public $yearlyPrice = 0;
    public $customerLimit = null;
    public $staffLimit = null;
    public $resellerLimit = null;
    public int $trialDays = 0;
    public int $graceDays = 0;
    public bool $isActive = true;

    public function create(): void
    {
        $this->authorizeAdmin();
        $this->resetPlanForm();
        $this->showModal = true;
    }

    public function updatedName(string $name): void
    {
        if (!$this->planId) {
            $this->slug = Str::slug($name);
        }
    }

    public function edit(int $id): void
    {
        $this->authorizeAdmin();
        $plan = SaasPlan::query()->whereNull('archived_at')->findOrFail($id);
        $this->planId = $plan->id;
        $this->name = $plan->name;
        $this->slug = $plan->slug;
        $this->description = $plan->description ?? '';
        $this->monthlyPrice = $plan->monthly_price;
        $this->yearlyPrice = $plan->yearly_price;
        $this->customerLimit = $plan->customer_limit;
        $this->staffLimit = $plan->staff_limit;
        $this->resellerLimit = $plan->reseller_limit;
        $this->trialDays = $plan->trial_days;
        $this->graceDays = $plan->grace_days;
        $this->isActive = $plan->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->authorizeAdmin();
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('saas_plans', 'slug')->ignore($this->planId)],
            'description' => ['nullable', 'string', 'max:2000'],
            'monthlyPrice' => ['required', 'numeric', 'min:0'],
            'yearlyPrice' => ['required', 'numeric', 'min:0'],
            'customerLimit' => ['nullable', 'integer', 'min:1'],
            'staffLimit' => ['nullable', 'integer', 'min:1'],
            'resellerLimit' => ['nullable', 'integer', 'min:0'],
            'trialDays' => ['required', 'integer', 'min:0', 'max:365'],
            'graceDays' => ['required', 'integer', 'min:0', 'max:90'],
            'isActive' => ['boolean'],
        ]);

        $plan = $this->planId ? SaasPlan::findOrFail($this->planId) : new SaasPlan();
        $plan->fill([
            'name' => $data['name'], 'slug' => $data['slug'], 'description' => $data['description'],
            'monthly_price' => $data['monthlyPrice'], 'yearly_price' => $data['yearlyPrice'],
            'customer_limit' => $data['customerLimit'], 'staff_limit' => $data['staffLimit'],
            'reseller_limit' => $data['resellerLimit'], 'trial_days' => $data['trialDays'],
            'grace_days' => $data['graceDays'], 'is_active' => $data['isActive'],
        ])->save();
        AuditLog::record($this->planId ? 'saas.plan.updated' : 'saas.plan.created', $plan);
        $this->showModal = false;
        session()->flash('message', 'SaaS plan saved.');
    }

    public function archive(int $id): void
    {
        $this->authorizeAdmin();
        $plan = SaasPlan::query()->whereNull('archived_at')->findOrFail($id);
        abort_if($plan->subscriptions()->whereIn('status', ['trialing', 'active', 'paused'])->exists(), 409, 'This plan has current subscriptions.');
        $plan->update(['is_active' => false, 'archived_at' => now()]);
        AuditLog::record('saas.plan.archived', $plan);
    }

    public function render()
    {
        $this->authorizeAdmin();
        return view('livewire.saas-plans', ['plans' => SaasPlan::query()->whereNull('archived_at')->withCount('subscriptions')->latest()->get()]);
    }

    private function resetPlanForm(): void
    {
        $this->reset(['planId', 'name', 'slug', 'description', 'monthlyPrice', 'yearlyPrice', 'customerLimit', 'staffLimit', 'resellerLimit']);
        $this->trialDays = 0;
        $this->graceDays = 0;
        $this->isActive = true;
        $this->resetValidation();
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}