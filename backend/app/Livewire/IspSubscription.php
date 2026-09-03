<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\BeePaymentIntent;
use App\Models\PaymentGateway;
use App\Models\SaasInvoice;
use App\Models\SaasPayment;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\TenantSubscriptionEvent;
use App\Models\User;
use App\Services\SaasSubscriptionBilling;
use App\Support\AuthorizesRoles;
use App\Support\CurrentTenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class IspSubscription extends Component
{
    use AuthorizesRoles, WithPagination;

    public ?int $selectedPlanId = null;
    public string $billingCycle = 'monthly';

    public bool $checkoutActive = false;
    public string $checkoutGateway = '';

    public ?string $beePayUrl = null;

    public function boot(): void
    {
        $this->authorizeRoles(User::ROLE_SUPER_ADMIN, User::ROLE_TENANT_ADMIN);
    }

    public function openCheckout(int $planId): void
    {
        $tenant = Tenant::query()->findOrFail(app(CurrentTenant::class)->id());

        abort_unless($this->eligiblePlan($tenant, $planId) !== null, 422, 'This plan is not available for your workspace type.');

        $this->selectedPlanId = $planId;
        $this->billingCycle = $this->billingCycle ?: 'monthly';
        $this->checkoutGateway = '';
        $this->checkoutActive = true;
        $this->resetValidation();
    }

    public function selectGateway(string $key): void
    {
        $this->checkoutGateway = $key;
        $this->resetValidation('checkoutGateway');
    }

    public function cancelCheckout(): void
    {
        $this->checkoutActive = false;
        $this->selectedPlanId = null;
        $this->checkoutGateway = '';
        $this->resetValidation();
    }

    public function confirmCheckout()
    {
        $this->validate([
            'selectedPlanId' => ['required', 'integer'],
            'billingCycle' => ['required', Rule::in(['monthly', 'yearly'])],
            'checkoutGateway' => ['required', Rule::in(collect($this->paymentMethods())->pluck('key')->all())],
        ]);

        $tenant = Tenant::query()->findOrFail(app(CurrentTenant::class)->id());
        $plan = $this->eligiblePlan($tenant, (int) $this->selectedPlanId);

        if (!$plan) {
            $this->addError('selectedPlanId', 'This plan is not available for your workspace type.');

            return;
        }

        $method = collect($this->paymentMethods())->firstWhere('key', $this->checkoutGateway);
        $manual = (bool) ($method['manual'] ?? false);

        $message = $this->placeOrder($tenant, $plan, $this->billingCycle, $manual, $method);

        if ($message === '' && ! $this->beePayUrl) {
            return; // A validation error was registered — stay on the checkout page.
        }

        $this->checkoutActive = false;
        $this->selectedPlanId = null;
        $this->checkoutGateway = '';

        // bKash orders move to the hosted BeeCore payment page where the real
        // bKash Tokenized Checkout session is created. Nothing is activated
        // until bKash confirms the payment.
        if ($this->beePayUrl) {
            $url = $this->beePayUrl;
            $this->beePayUrl = null;

            $this->dispatch('bee-pay-open', $url);

            return redirect()->to($url);
        }

        session()->flash('message', $message);
    }

    /**
     * Direct BeeCore activation (used by the platform owner / internal flows and tests).
     */
    public function subscribe(): void
    {
        $this->validate([
            'selectedPlanId' => ['required', 'integer'],
            'billingCycle' => ['required', Rule::in(['monthly', 'yearly'])],
        ]);

        $tenant = Tenant::query()->findOrFail(app(CurrentTenant::class)->id());
        $plan = $this->eligiblePlan($tenant, (int) $this->selectedPlanId);

        if (!$plan) {
            $this->addError('selectedPlanId', 'This plan is not available for your workspace type.');

            return;
        }

        DB::transaction(function () use ($tenant, $plan) {
            $price = $this->billingCycle === 'yearly' ? (float) $plan->yearly_price : (float) $plan->monthly_price;
            $subscription = $this->currentSubscription($tenant, lock: true);

            if (!$subscription || $subscription->status === 'cancelled') {
                $starts = today();
                $periodEnd = $this->billingCycle === 'yearly'
                    ? $starts->copy()->addYear()->subDay()
                    : $starts->copy()->addMonth()->subDay();

                $subscription = TenantSubscription::create([
                    'tenant_id' => $tenant->id,
                    'saas_plan_id' => $plan->id,
                    'status' => 'active',
                    'billing_cycle' => $this->billingCycle,
                    'price' => $price,
                    'starts_at' => $starts,
                    'current_period_ends_at' => $periodEnd,
                    'grace_ends_at' => $periodEnd->copy()->addDays($plan->grace_days),
                    'auto_renew' => true,
                ]);

                $dueDate = $starts->copy()->addDays(max((int) $plan->grace_days, 7));
                (new SaasSubscriptionBilling())->createInvoiceForPeriod($subscription, $starts, $periodEnd, $dueDate);

                TenantSubscriptionEvent::create([
                    'tenant_subscription_id' => $subscription->id,
                    'user_id' => auth()->id(),
                    'event' => 'subscription.created',
                    'from_status' => null,
                    'to_status' => 'active',
                    'metadata' => ['plan_id' => $plan->id, 'billing_cycle' => $subscription->billing_cycle, 'price' => $subscription->price],
                    'created_at' => now(),
                ]);
                AuditLog::record('tenant.subscription.created', $subscription, tenantId: $tenant->id);

                session()->flash('message', 'Your '.$plan->name.' plan is active. An invoice for the first period has been created.');
            } else {
                if (!in_array($subscription->status, ['active', 'trialing'], true)) {
                    $this->addError('selectedPlanId', 'Your current subscription is '.$subscription->status.'. Contact the BeeCore Account team to change it.');

                    return;
                }
                if ($subscription->invoices()->whereIn('status', ['pending', 'overdue'])->exists()) {
                    $this->addError('selectedPlanId', 'You have an unpaid BeeCore invoice. Settle it first, then you can change plans.');

                    return;
                }

                $oldPlanId = $subscription->saas_plan_id;
                $fromStatus = $subscription->status;

                $subscription->update([
                    'saas_plan_id' => $plan->id,
                    'billing_cycle' => $this->billingCycle,
                    'price' => $price,
                    'auto_renew' => true,
                ]);

                TenantSubscriptionEvent::create([
                    'tenant_subscription_id' => $subscription->id,
                    'user_id' => auth()->id(),
                    'event' => 'subscription.plan_changed',
                    'from_status' => $fromStatus,
                    'to_status' => $subscription->status,
                    'metadata' => [
                        'from_plan_id' => $oldPlanId,
                        'to_plan_id' => $plan->id,
                        'from_price' => $subscription->getOriginal('price'),
                        'to_price' => $price,
                    ],
                    'created_at' => now(),
                ]);
                AuditLog::record('tenant.subscription.plan_changed', $subscription, tenantId: $tenant->id);

                session()->flash('message', 'Your plan has been changed to '.$plan->name.' — the new price applies from your next billing period.');
            }
        });
    }

    public function render()
    {
        $tenant = Tenant::query()->findOrFail(app(CurrentTenant::class)->id());
        $subscription = TenantSubscription::query()
            ->where('tenant_id', $tenant->id)
            ->with('plan')
            ->latest('id')
            ->first();

        $invoices = SaasInvoice::query()
            ->where('tenant_id', $tenant->id)
            ->with(['subscription.plan', 'payments'])
            ->latest('id')
            ->paginate(8);

        $modes = $tenant->isAutomatic() ? ['automatic', 'both'] : ['manual', 'both'];

        return view('livewire.isp-subscription', [
            'workspace' => $tenant,
            'subscription' => $subscription,
            'invoices' => $invoices,
            'plans' => SaasPlan::query()
                ->where('is_active', true)
                ->whereNull('archived_at')
                ->whereIn('operation_mode', $modes)
                ->orderBy('monthly_price')
                ->get(),
            'currentPlanId' => $subscription && $subscription->status !== 'cancelled' ? $subscription->saas_plan_id : null,
            'selectedPlan' => $this->selectedPlanId ? SaasPlan::find($this->selectedPlanId) : null,
            'paymentMethods' => $this->paymentMethods(),
            'checkout' => $this->checkoutActive,
        ]);
    }

    public function eligiblePlan(Tenant $tenant, int $planId): ?SaasPlan
    {
        $modes = $tenant->isAutomatic() ? ['automatic', 'both'] : ['manual', 'both'];

        return SaasPlan::query()
            ->where('is_active', true)
            ->whereNull('archived_at')
            ->whereIn('operation_mode', $modes)
            ->find($planId);
    }

    public function paymentMethods(): array
    {
        $gateways = PaymentGateway::query()
            ->where('is_active', true)
            ->whereNull('archived_at')
            ->whereIn('provider', ['bkash', 'bank'])
            ->orderBy('provider')
            ->get();

        $methods = [];

        foreach ($gateways as $gateway) {
            $manual = $gateway->provider === 'bank';
            $methods[] = [
                'key' => 'gateway:'.$gateway->id,
                'provider' => $gateway->provider,
                'name' => $gateway->name,
                'mode' => $gateway->mode,
                'manual' => $manual,
                'account' => $manual ? ($gateway->credentials ?? []) : [],
            ];
        }

        // Manual bank transfer always exists as a BeeCore fallback.
        if (!collect($methods)->contains(fn ($m) => $m['manual'])) {
            $methods[] = [
                'key' => 'manual_transfer',
                'provider' => 'bank',
                'name' => 'Bank transfer (BeeCore account)',
                'mode' => 'live',
                'manual' => true,
                'account' => [],
            ];
        }

        return $methods;
    }

    public function paymentMethodStyle(string $provider): array
    {
        return match ($provider) {
            'bkash' => ['letter' => 'bK', 'avatar' => 'from-pink-500 to-rose-500', 'label' => 'bKash'],
            'nagad' => ['letter' => 'Ng', 'avatar' => 'from-orange-500 to-amber-500', 'label' => 'Nagad'],
            'stripe' => ['letter' => 'St', 'avatar' => 'from-indigo-500 to-violet-500', 'label' => 'Stripe'],
            'bank' => ['letter' => 'Ba', 'avatar' => 'from-emerald-500 to-teal-500', 'label' => 'Bank'],
            default => ['letter' => strtoupper(substr($provider, 0, 2)), 'avatar' => 'from-gray-400 to-gray-500', 'label' => ucfirst($provider)],
        };
    }

    private function placeOrder(Tenant $tenant, SaasPlan $plan, string $cycle, bool $manual, array $method): string
    {
        $price = $cycle === 'yearly' ? (float) $plan->yearly_price : (float) $plan->monthly_price;

        // bKash is the only real-time gateway on the SaaS checkout: the order is
        // created as pending and is completed by the BeeCore payment callback.
        $onlineBkash = ! $manual && ($method['provider'] ?? null) === 'bkash';
        $needsApproval = $manual || $onlineBkash;

        $intent = null;

        DB::transaction(function () use ($tenant, $plan, $cycle, $manual, $onlineBkash, $needsApproval, $method, $price, &$intent) {
            $subscription = $this->currentSubscription($tenant, lock: true);

            if (!$subscription || $subscription->status === 'cancelled') {
                // Fresh subscribe.
                $starts = today();
                $periodEnd = $cycle === 'yearly'
                    ? $starts->copy()->addYear()->subDay()
                    : $starts->copy()->addMonth()->subDay();

                $subscription = TenantSubscription::create([
                    'tenant_id' => $tenant->id,
                    'saas_plan_id' => $plan->id,
                    'status' => $needsApproval ? 'pending_approval' : 'active',
                    'billing_cycle' => $cycle,
                    'price' => $price,
                    'starts_at' => $starts,
                    'current_period_ends_at' => $periodEnd,
                    'grace_ends_at' => $periodEnd->copy()->addDays($plan->grace_days),
                    'auto_renew' => true,
                ]);

                $dueDate = $starts->copy()->addDays(max((int) $plan->grace_days, 7));
                $invoice = (new SaasSubscriptionBilling())->createInvoiceForPeriod($subscription, $starts, $periodEnd, $dueDate);

                TenantSubscriptionEvent::create([
                    'tenant_subscription_id' => $subscription->id,
                    'user_id' => auth()->id(),
                    'event' => 'subscription.created',
                    'from_status' => null,
                    'to_status' => $subscription->status,
                    'metadata' => ['plan_id' => $plan->id, 'billing_cycle' => $cycle, 'price' => $price, 'payment' => $needsApproval ? 'pending' : 'online'],
                    'created_at' => now(),
                ]);
                AuditLog::record('tenant.subscription.created', $subscription, tenantId: $tenant->id);
            } else {
                // Switch plan on an active/trialing subscription.
                if (!in_array($subscription->status, ['active', 'trialing'], true)) {
                    $this->addError('selectedPlanId', 'Your current subscription is '.$subscription->status.'. Contact the BeeCore Account team to change it.');

                    return;
                }
                if ($subscription->invoices()->whereIn('status', ['pending', 'overdue'])->exists()) {
                    $this->addError('selectedPlanId', 'You have an unpaid BeeCore invoice. Settle it first, then you can change plans.');

                    return;
                }

                $anchor = $subscription->current_period_ends_at?->isFuture()
                    ? $subscription->current_period_ends_at->copy()->addDay()
                    : today();
                $periodEnd = $cycle === 'yearly'
                    ? $anchor->copy()->addYear()->subDay()
                    : $anchor->copy()->addMonth()->subDay();

                $oldPlanId = $subscription->saas_plan_id;
                $fromStatus = $subscription->status;

                $subscription->update([
                    'saas_plan_id' => $plan->id,
                    'billing_cycle' => $cycle,
                    'price' => $price,
                    'status' => $needsApproval ? 'pending_approval' : 'active',
                    'auto_renew' => true,
                ]);

                $dueDate = $anchor->copy()->addDays(max((int) $plan->grace_days, 7));
                $invoice = (new SaasSubscriptionBilling())->createInvoiceForPeriod($subscription, $anchor, $periodEnd, $dueDate);

                TenantSubscriptionEvent::create([
                    'tenant_subscription_id' => $subscription->id,
                    'user_id' => auth()->id(),
                    'event' => 'subscription.plan_changed',
                    'from_status' => $fromStatus,
                    'to_status' => $subscription->status,
                    'metadata' => [
                        'from_plan_id' => $oldPlanId,
                        'to_plan_id' => $plan->id,
                        'from_price' => $subscription->getOriginal('price'),
                        'to_price' => $price,
                    ],
                    'created_at' => now(),
                ]);
                AuditLog::record('tenant.subscription.plan_changed', $subscription, tenantId: $tenant->id);
            }

            $payment = SaasPayment::create([
                'tenant_id' => $tenant->id,
                'saas_invoice_id' => $invoice->id,
                'recorded_by' => auth()->id(),
                'amount' => $price,
                'method' => $method['provider'] ?? 'manual',
                'reference' => 'BeeCore checkout — '.($method['name'] ?? 'BeeCore'),
                'status' => $needsApproval ? 'pending' : 'completed',
                'verified_at' => $needsApproval ? null : now(),
                'verified_by' => $needsApproval ? null : auth()->id(),
                'paid_at' => now(),
            ]);

            AuditLog::record($needsApproval ? 'saas.payment.pending' : 'saas.payment.recorded', $payment, [
                'amount' => $price,
                'method' => $method['provider'] ?? 'manual',
                'status' => $payment->status,
            ], tenantId: $tenant->id);

            if ($onlineBkash) {
                $intent = BeePaymentIntent::findOpen(BeePaymentIntent::KIND_SAAS_PLAN, $tenant->id, ['saas_invoice_id' => $invoice->id])
                    ?? BeePaymentIntent::createFor(BeePaymentIntent::KIND_SAAS_PLAN, $tenant->id, $price, ['saas_invoice_id' => $invoice->id]);
            }
        });

        if ($this->getErrorBag()->isNotEmpty()) {
            return '';
        }

        if ($onlineBkash && $intent) {
            $this->beePayUrl = route('bee-pay.intent', ['intent' => $intent->token]);

            return '';
        }

        if ($needsApproval) {
            return 'Order submitted. The BeeCore Account team will verify your payment and activate your '.$plan->name.' plan — no action is needed from you.';
        }

        return 'Payment successful — your '.$plan->name.' plan is now active.';
    }

    private function currentSubscription(Tenant $tenant, bool $lock = false): ?TenantSubscription
    {
        $query = TenantSubscription::query()->where('tenant_id', $tenant->id)->latest('id');

        return ($lock ? $query->lockForUpdate() : $query)->first();
    }
}
