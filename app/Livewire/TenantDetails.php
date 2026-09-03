<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\SaasInvoice;
use App\Models\SaasPayment;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\TenantSubscriptionEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class TenantDetails extends Component
{
    public Tenant $tenant;
    public ?int $planId = null;
    public string $billingCycle = 'monthly';
    public string $subscriptionStatus = 'active';
    public string $startsAt = '';
    public ?string $trialEndsAt = null;
    public bool $autoRenew = true;

    public function mount(Tenant $tenant): void
    {
        $this->authorizeAdmin();
        abort_if($tenant->archived_at, 404);
        $this->tenant = $tenant;
        $this->startsAt = today()->toDateString();
        $current = $this->currentSubscription();
        if ($current) {
            $this->planId = $current->saas_plan_id;
            $this->billingCycle = $current->billing_cycle;
            $this->subscriptionStatus = $current->status;
            $this->startsAt = $current->starts_at->toDateString();
            $this->trialEndsAt = $current->trial_ends_at?->toDateString();
            $this->autoRenew = $current->auto_renew;
        }
    }

    public function saveSubscription(): void
    {
        $this->authorizeAdmin();
        $data = $this->validate([
            'planId' => ['required', Rule::exists('saas_plans', 'id')->where(fn ($query) => $query->where('is_active', true)->whereNull('archived_at'))],
            'billingCycle' => ['required', Rule::in(['monthly', 'yearly'])],
            'subscriptionStatus' => ['required', Rule::in(['trialing', 'active', 'paused', 'cancelled', 'past_due', 'suspended'])],
            'startsAt' => ['required', 'date'],
            'trialEndsAt' => ['nullable', 'date', 'after_or_equal:startsAt'],
            'autoRenew' => ['boolean'],
        ]);

        DB::transaction(function () use ($data) {
            $plan = SaasPlan::findOrFail($data['planId']);
            $subscription = $this->currentSubscription(lock: true);
            $fromStatus = $subscription?->status;
            $periodEnd = $data['billingCycle'] === 'yearly'
                ? Carbon::parse($data['startsAt'])->addYear()->subDay()
                : Carbon::parse($data['startsAt'])->addMonth()->subDay();
            $attributes = [
                'tenant_id' => $this->tenant->id,
                'saas_plan_id' => $plan->id,
                'status' => $data['subscriptionStatus'],
                'billing_cycle' => $data['billingCycle'],
                'price' => $data['billingCycle'] === 'yearly' ? $plan->yearly_price : $plan->monthly_price,
                'starts_at' => $data['startsAt'],
                'trial_ends_at' => $data['subscriptionStatus'] === 'trialing' ? $data['trialEndsAt'] : null,
                'current_period_ends_at' => $periodEnd,
                'grace_ends_at' => $periodEnd->copy()->addDays($plan->grace_days),
                'cancelled_at' => $data['subscriptionStatus'] === 'cancelled' ? now() : null,
                'auto_renew' => $data['autoRenew'],
            ];
            $subscription = $subscription ?? new TenantSubscription();
            $subscription->fill($attributes)->save();
            TenantSubscriptionEvent::create([
                'tenant_subscription_id' => $subscription->id,
                'user_id' => auth()->id(),
                'event' => $fromStatus ? 'subscription.updated' : 'subscription.created',
                'from_status' => $fromStatus,
                'to_status' => $subscription->status,
                'metadata' => ['plan_id' => $plan->id, 'billing_cycle' => $subscription->billing_cycle, 'price' => $subscription->price],
                'created_at' => now(),
            ]);
            AuditLog::record($fromStatus ? 'tenant.subscription.updated' : 'tenant.subscription.created', $subscription, tenantId: $this->tenant->id);
        });
        session()->flash('message', 'Tenant subscription saved.');
    }

    public function markInvoicePaid(int $invoiceId): void
    {
        $this->authorizeAdmin();

        DB::transaction(function () use ($invoiceId) {
            $invoice = SaasInvoice::query()
                ->where('tenant_id', $this->tenant->id)
                ->whereIn('status', ['pending', 'overdue'])
                ->lockForUpdate()
                ->findOrFail($invoiceId);

            $invoice->update(['status' => 'paid', 'paid_at' => now()]);

            SaasPayment::create([
                'tenant_id' => $this->tenant->id,
                'saas_invoice_id' => $invoice->id,
                'recorded_by' => auth()->id(),
                'amount' => $invoice->amount,
                'method' => 'manual',
                'paid_at' => now(),
            ]);

            $subscription = $invoice->subscription;
            $stillUnpaid = $subscription->invoices()->whereIn('status', ['pending', 'overdue'])->where('id', '!=', $invoice->id)->exists();

            if (!$stillUnpaid && in_array($subscription->status, ['pending_approval', 'past_due', 'suspended'], true)) {
                $wasPendingApproval = $subscription->status === 'pending_approval';
                $fromStatus = $subscription->status;
                $subscription->update(['status' => 'active']);
                if ($this->tenant->status === 'suspended') {
                    $this->tenant->update(['status' => 'active']);
                }
                TenantSubscriptionEvent::create([
                    'tenant_subscription_id' => $subscription->id,
                    'user_id' => auth()->id(),
                    'event' => $wasPendingApproval ? 'subscription.approved' : 'subscription.reactivated',
                    'from_status' => $fromStatus,
                    'to_status' => 'active',
                    'metadata' => null,
                    'created_at' => now(),
                ]);
            }

            AuditLog::record('tenant.invoice.paid', $invoice, tenantId: $this->tenant->id);
        });

        session()->flash('message', 'SaaS invoice marked paid.');
    }

    public function render()
    {
        $this->authorizeAdmin();
        $subscription = $this->currentSubscription();
        $counts = [
            'customers' => $this->tenant->customers()->count(),
            'staff' => $this->tenant->users()->where('status', 'active')->count(),
            'resellers' => $this->tenant->resellers()->count(),
        ];
        $eligibleModes = $this->tenant->isAutomatic() ? ['automatic', 'both'] : ['manual', 'both'];

        return view('livewire.tenant-details', [
            'plans' => SaasPlan::query()
                ->where('is_active', true)
                ->whereNull('archived_at')
                ->where(fn ($query) => $query
                    ->whereIn('operation_mode', $eligibleModes)
                    ->when($subscription, fn ($query) => $query->orWhere('id', $subscription->saas_plan_id)))
                ->orderBy('monthly_price')
                ->get(),
            'subscription' => $subscription?->load(['plan', 'events']),
            'invoices' => $subscription?->invoices()->latest('period_start')->limit(12)->get() ?? collect(),
            'counts' => $counts,
        ]);
    }

    private function currentSubscription(bool $lock = false): ?TenantSubscription
    {
        $query = $this->tenant->saasSubscriptions()->latest('id');
        return ($lock ? $query->lockForUpdate() : $query)->first();
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}