<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\TenantSubscriptionEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Subscriptions extends Component
{
    use WithPagination;

    public string $statusFilter = '';
    public ?int $historyForId = null;

    public ?int $changePlanForId = null;
    public ?int $newPlanId = null;
    public string $newBillingCycle = 'monthly';

    public ?int $extendTrialForId = null;
    public int $extendTrialDays = 7;

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function renew(int $id): void
    {
        $this->assertSuperAdmin();

        DB::transaction(function () use ($id) {
            $subscription = TenantSubscription::query()->lockForUpdate()->findOrFail($id);
            abort_if($subscription->status === 'cancelled', 422, 'A cancelled subscription cannot be renewed.');

            $fromStatus = $subscription->status;
            $anchor = $subscription->current_period_ends_at?->isFuture() ? $subscription->current_period_ends_at : Carbon::today();
            $periodEnd = $subscription->billing_cycle === 'yearly'
                ? $anchor->copy()->addYear()
                : $anchor->copy()->addMonth();
            $plan = $subscription->plan;

            $subscription->update([
                'status' => 'active',
                'current_period_ends_at' => $periodEnd,
                'grace_ends_at' => $periodEnd->copy()->addDays($plan->grace_days),
            ]);

            if ($subscription->tenant->status === 'suspended') {
                $subscription->tenant->update(['status' => 'active']);
            }

            $this->recordEvent($subscription, 'subscription.renewed', $fromStatus, 'active');
            AuditLog::record('tenant.subscription.renewed', $subscription, tenantId: $subscription->tenant_id);
        });

        session()->flash('message', 'Subscription renewed.');
    }

    public function pause(int $id): void
    {
        $this->assertSuperAdmin();

        $subscription = TenantSubscription::findOrFail($id);
        abort_if(in_array($subscription->status, ['cancelled', 'paused'], true), 422, 'This subscription cannot be paused.');

        $fromStatus = $subscription->status;
        $subscription->update(['status' => 'paused']);
        $this->recordEvent($subscription, 'subscription.paused', $fromStatus, 'paused');
        AuditLog::record('tenant.subscription.paused', $subscription, tenantId: $subscription->tenant_id);
        session()->flash('message', 'Subscription paused.');
    }

    public function resume(int $id): void
    {
        $this->assertSuperAdmin();

        DB::transaction(function () use ($id) {
            $subscription = TenantSubscription::query()->lockForUpdate()->findOrFail($id);
            abort_unless($subscription->status === 'paused', 422, 'Only a paused subscription can be resumed.');

            $plan = $subscription->plan;
            $periodEnd = $subscription->billing_cycle === 'yearly'
                ? Carbon::today()->addYear()
                : Carbon::today()->addMonth();

            $subscription->update([
                'status' => 'active',
                'current_period_ends_at' => $periodEnd,
                'grace_ends_at' => $periodEnd->copy()->addDays($plan->grace_days),
            ]);

            $this->recordEvent($subscription, 'subscription.resumed', 'paused', 'active');
            AuditLog::record('tenant.subscription.resumed', $subscription, tenantId: $subscription->tenant_id);
        });

        session()->flash('message', 'Subscription resumed.');
    }

    public function cancel(int $id): void
    {
        $this->assertSuperAdmin();

        $subscription = TenantSubscription::findOrFail($id);
        abort_if($subscription->status === 'cancelled', 422, 'This subscription is already cancelled.');

        $fromStatus = $subscription->status;
        $subscription->update(['status' => 'cancelled', 'cancelled_at' => now(), 'auto_renew' => false]);
        $this->recordEvent($subscription, 'subscription.cancelled', $fromStatus, 'cancelled');
        AuditLog::record('tenant.subscription.cancelled', $subscription, tenantId: $subscription->tenant_id);
        session()->flash('message', 'Subscription cancelled.');
    }

    public function openChangePlan(int $id): void
    {
        $subscription = TenantSubscription::findOrFail($id);
        $this->changePlanForId = $id;
        $this->newPlanId = $subscription->saas_plan_id;
        $this->newBillingCycle = $subscription->billing_cycle;
    }

    public function changePlan(): void
    {
        $this->assertSuperAdmin();

        $data = $this->validate([
            'newPlanId' => ['required', Rule::exists('saas_plans', 'id')->where(fn ($query) => $query->where('is_active', true)->whereNull('archived_at'))],
            'newBillingCycle' => ['required', Rule::in(['monthly', 'yearly'])],
        ]);

        DB::transaction(function () use ($data) {
            $subscription = TenantSubscription::query()->lockForUpdate()->findOrFail($this->changePlanForId);
            abort_if($subscription->status === 'cancelled', 422, 'A cancelled subscription cannot change plan.');

            $newPlan = SaasPlan::findOrFail($data['newPlanId']);
            $oldPlan = $subscription->plan;
            $newPrice = $data['newBillingCycle'] === 'yearly' ? $newPlan->yearly_price : $newPlan->monthly_price;

            $totalDays = max(1, $subscription->starts_at->diffInDays($subscription->current_period_ends_at));
            $remainingDays = max(0, Carbon::today()->diffInDays($subscription->current_period_ends_at, false));
            $proratedCredit = $remainingDays > 0 ? round(($subscription->price / $totalDays) * $remainingDays, 2) : 0;

            $subscription->update([
                'saas_plan_id' => $newPlan->id,
                'billing_cycle' => $data['newBillingCycle'],
                'price' => $newPrice,
            ]);

            TenantSubscriptionEvent::create([
                'tenant_subscription_id' => $subscription->id,
                'user_id' => auth()->id(),
                'event' => 'subscription.plan_changed',
                'from_status' => $subscription->status,
                'to_status' => $subscription->status,
                'metadata' => [
                    'from_plan_id' => $oldPlan->id,
                    'to_plan_id' => $newPlan->id,
                    'from_price' => $subscription->getOriginal('price'),
                    'to_price' => $newPrice,
                    'prorated_credit' => $proratedCredit,
                ],
                'created_at' => now(),
            ]);

            AuditLog::record('tenant.subscription.plan_changed', $subscription, [
                'from_plan_id' => $oldPlan->id,
                'to_plan_id' => $newPlan->id,
            ], tenantId: $subscription->tenant_id);
        });

        $this->changePlanForId = null;
        session()->flash('message', 'Subscription plan changed.');
    }

    public function openExtendTrial(int $id): void
    {
        $this->extendTrialForId = $id;
        $this->extendTrialDays = 7;
    }

    public function extendTrial(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate(['extendTrialDays' => ['required', 'integer', 'min:1', 'max:365']]);

        $subscription = TenantSubscription::findOrFail($this->extendTrialForId);
        abort_unless($subscription->status === 'trialing', 422, 'Only a trialing subscription can be extended.');

        $newTrialEnd = ($subscription->trial_ends_at ?? Carbon::today())->copy()->addDays($data['extendTrialDays']);
        $subscription->update([
            'trial_ends_at' => $newTrialEnd,
            'current_period_ends_at' => $newTrialEnd,
        ]);

        $this->recordEvent($subscription, 'subscription.trial_extended', 'trialing', 'trialing', ['days' => $data['extendTrialDays']]);
        AuditLog::record('tenant.subscription.trial_extended', $subscription, ['days' => $data['extendTrialDays']], tenantId: $subscription->tenant_id);

        $this->extendTrialForId = null;
        session()->flash('message', 'Trial extended.');
    }

    public function viewHistory(int $id): void
    {
        $this->historyForId = $id;
    }

    public function closeModals(): void
    {
        $this->historyForId = null;
        $this->changePlanForId = null;
        $this->extendTrialForId = null;
    }

    public function render()
    {
        $this->assertSuperAdmin();

        return view('livewire.subscriptions', [
            'subscriptions' => TenantSubscription::query()
                ->with(['tenant', 'plan'])
                ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
                ->latest('id')
                ->paginate(15),
            'plans' => SaasPlan::query()->where('is_active', true)->whereNull('archived_at')->orderBy('monthly_price')->get(),
            'historyEvents' => $this->historyForId
                ? TenantSubscriptionEvent::where('tenant_subscription_id', $this->historyForId)->with('user')->latest('id')->get()
                : collect(),
        ]);
    }

    private function recordEvent(TenantSubscription $subscription, string $event, ?string $from, string $to, ?array $metadata = null): void
    {
        TenantSubscriptionEvent::create([
            'tenant_subscription_id' => $subscription->id,
            'user_id' => auth()->id(),
            'event' => $event,
            'from_status' => $from,
            'to_status' => $to,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
