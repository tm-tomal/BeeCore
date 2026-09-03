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
    public $viewMode = 'index';
    public $isEditing = false;
    public $search = '';
    public $modeFilter = '';
    public ?int $planId = null;
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public $monthlyPrice = 0;
    public $yearlyPrice = 0;
    public int $yearlyDiscountPercent = 25;
    public $customerLimit = null;
    public $overflowRate = 0;
    public $staffLimit = null;
    public $resellerLimit = null;
    public int $trialDays = 0;
    public int $graceDays = 0;
    public string $operationMode = 'both';
    public bool $isActive = true;

    public function create(): void
    {
        $this->authorizeAdmin();
        $this->resetPlanForm();
        $this->isEditing = false;
        $this->viewMode = 'create';
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
        $this->yearlyPrice = (float) $plan->yearly_price;
        $this->yearlyDiscountPercent = $plan->yearlyDiscountPercent();
        $this->customerLimit = $plan->customer_limit;
        $this->overflowRate = (float) ($plan->overflow_rate ?? 0);
        $this->staffLimit = $plan->staff_limit;
        $this->resellerLimit = $plan->reseller_limit;
        $this->trialDays = $plan->trial_days;
        $this->graceDays = $plan->grace_days;
        $this->operationMode = $plan->operation_mode ?? 'both';
        $this->isActive = $plan->is_active;
        $this->isEditing = true;
        $this->viewMode = 'create';
    }

    public function cancel(): void
    {
        $this->viewMode = 'index';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedName(string $name): void
    {
        if (!$this->planId) {
            $this->slug = Str::slug($name);
        }
    }

    public function updatedMonthlyPrice($value): void
    {
        $this->yearlyPrice = round((float) $value * 12 * (1 - (int) $this->yearlyDiscountPercent / 100), 2);
    }

    public function updatedYearlyDiscountPercent($value): void
    {
        $this->yearlyPrice = round((float) $this->monthlyPrice * 12 * (1 - (int) $value / 100), 2);
    }

    public function save(): void
    {
        $this->authorizeAdmin();
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('saas_plans', 'slug')->ignore($this->planId)],
            'description' => ['nullable', 'string', 'max:2000'],
            'monthlyPrice' => ['required', 'numeric', 'min:0'],
            'yearlyDiscountPercent' => ['required', 'integer', 'min:0', 'max:100'],
            'customerLimit' => ['nullable', 'integer', 'min:1'],
            'overflowRate' => ['required', 'numeric', 'min:0'],
            'staffLimit' => ['nullable', 'integer', 'min:1'],
            'resellerLimit' => ['nullable', 'integer', 'min:0'],
            'trialDays' => ['required', 'integer', 'min:0', 'max:365'],
            'graceDays' => ['required', 'integer', 'min:0', 'max:90'],
            'operationMode' => ['required', Rule::in(['automatic', 'manual', 'both'])],
            'isActive' => ['boolean'],
        ]);

        $yearlyDiscount = (int) $data['yearlyDiscountPercent'];
        $yearlyPrice = round((float) $data['monthlyPrice'] * 12 * (1 - $yearlyDiscount / 100), 2);

        $plan = $this->planId ? SaasPlan::findOrFail($this->planId) : new SaasPlan();
        $plan->fill([
            'name' => $data['name'], 'slug' => $data['slug'], 'description' => $data['description'],
            'monthly_price' => $data['monthlyPrice'], 'yearly_price' => $yearlyPrice,
            'yearly_discount_percent' => $yearlyDiscount,
            'customer_limit' => $data['customerLimit'], 'overflow_rate' => $data['overflowRate'],
            'staff_limit' => $data['staffLimit'], 'reseller_limit' => $data['resellerLimit'], 'trial_days' => $data['trialDays'],
            'grace_days' => $data['graceDays'], 'operation_mode' => $data['operationMode'],
            'is_active' => $data['isActive'],
        ])->save();
        AuditLog::record($this->planId ? 'saas.plan.updated' : 'saas.plan.created', $plan);
        $this->viewMode = 'index';
        $this->isEditing = false;
        session()->flash('message', 'SaaS plan saved.');
    }

    public function archive(int $id): void
    {
        $this->authorizeAdmin();
        $plan = SaasPlan::query()->whereNull('archived_at')->findOrFail($id);
        abort_if($plan->subscriptions()->whereIn('status', ['trialing', 'active', 'paused'])->exists(), 409, 'This plan has current subscriptions.');
        $plan->update(['is_active' => false, 'archived_at' => now()]);
        AuditLog::record('saas.plan.archived', $plan);
        session()->flash('message', 'SaaS plan archived.');
    }

    public function toggleActive(int $id): void
    {
        $this->authorizeAdmin();
        $plan = SaasPlan::query()->whereNull('archived_at')->findOrFail($id);
        $plan->update(['is_active' => ! $plan->is_active]);
        AuditLog::record('saas.plan.toggled', $plan, ['is_active' => (bool) $plan->is_active]);
    }

    public function render()
    {
        $this->authorizeAdmin();

        return view('livewire.saas-plans', [
            'plans' => SaasPlan::query()
                ->whereNull('archived_at')
                ->withCount('subscriptions')
                ->when($this->search !== '', fn ($query) => $query
                    ->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('slug', 'like', '%'.$this->search.'%'))
                ->when($this->modeFilter !== '', fn ($query) => $query->where('operation_mode', $this->modeFilter))
                ->latest()
                ->get(),
        ]);
    }

    private function resetPlanForm(): void
    {
        $this->reset(['planId', 'name', 'slug', 'description', 'monthlyPrice', 'yearlyPrice', 'customerLimit', 'staffLimit', 'resellerLimit']);
        $this->yearlyDiscountPercent = SaasPlan::DEFAULT_YEARLY_DISCOUNT_PERCENT;
        $this->trialDays = 0;
        $this->graceDays = 0;
        $this->overflowRate = 0;
        $this->operationMode = 'both';
        $this->isActive = true;
        $this->resetValidation();
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}