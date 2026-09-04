<?php

namespace App\Livewire;

use App\Models\Addon;
use App\Models\AuditLog;
use App\Models\BeePaymentIntent;
use App\Models\PaymentGateway;
use App\Models\SaasInvoice;
use App\Models\SaasInvoiceItem;
use App\Models\SaasPayment;
use App\Models\Tenant;
use App\Models\TenantAddon;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Support\AuthorizesRoles;
use App\Support\CurrentTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class IspAddons extends Component
{
    use AuthorizesRoles;

    public string $categoryFilter = '';

    public ?int $checkoutAddonId = null;

    public string $checkoutGateway = '';

    public ?string $beePayUrl = null;

    public function boot(): void
    {
        $this->authorizeRoles(User::ROLE_SUPER_ADMIN, User::ROLE_TENANT_ADMIN);
    }

    private function tenantId(): int
    {
        return app(CurrentTenant::class)->id();
    }

    public function updatedCategoryFilter(): void
    {
        //
    }

    /* ---------- Checkout ---------- */

    public function buy(int $addonId): void
    {
        $addon = Addon::query()->where('is_active', true)->whereNull('archived_at')->findOrFail($addonId);

        if ($this->hasOpenAddon($addon->id)) {
            session()->flash('error', __('This add-on is already active or pending.'));
            return;
        }

        $subscription = $this->activeSubscription();
        if (! $subscription) {
            session()->flash('error', __('Activate a BeeCore plan before buying add-ons — add-ons are billed with your plan.'));
            return;
        }

        $this->resetValidation();
        $this->checkoutAddonId = $addon->id;
        $this->checkoutGateway = '';
    }

    public function selectGateway(string $key): void
    {
        $this->checkoutGateway = $key;
        $this->resetValidation('checkoutGateway');
    }

    public function cancelCheckout(): void
    {
        $this->checkoutAddonId = null;
        $this->checkoutGateway = '';
        $this->resetValidation();
    }

    public function confirmBuy()
    {
        $this->validate([
            'checkoutAddonId' => ['required', 'integer'],
            'checkoutGateway' => ['required', Rule::in(collect($this->paymentMethods())->pluck('key')->all())],
        ]);

        $tenant = Tenant::query()->findOrFail($this->tenantId());
        $addon = Addon::query()->where('is_active', true)->whereNull('archived_at')->find((int) $this->checkoutAddonId);

        if (! $addon) {
            $this->addError('checkoutAddonId', 'This add-on is no longer available.');
            return;
        }

        $method = collect($this->paymentMethods())->firstWhere('key', $this->checkoutGateway);
        $manual = (bool) ($method['manual'] ?? false);

        $message = $this->placeOrder($tenant, $addon, $method, $manual);

        if ($message === '' && ! $this->beePayUrl) {
            return;
        }

        $this->checkoutAddonId = null;
        $this->checkoutGateway = '';

        if ($this->beePayUrl) {
            $url = $this->beePayUrl;
            $this->beePayUrl = null;

            $this->dispatch('bee-pay-open', $url);

            return redirect()->to($url);
        }

        session()->flash('message', $message);
    }

    private function placeOrder(Tenant $tenant, Addon $addon, array $method, bool $manual): string
    {
        if ($this->hasOpenAddon($addon->id)) {
            $this->addError('checkoutGateway', __('This add-on is already active or pending. Check “My add-ons” — if it is awaiting payment, open your BeeCore invoice list and click “Pay now”.'));
            return '';
        }

        $subscription = $this->activeSubscription();
        if (! $subscription) {
            $this->addError('checkoutGateway', __('Activate a BeeCore plan before buying add-ons — add-ons are billed with your plan.'));
            return '';
        }

        // bKash online: no order rows are written until bKash confirms the
        // payment — a failed/cancelled attempt leaves no add-on, invoice or
        // payment behind (see placeBkashAddonOrder).
        if (! $manual && ($method['provider'] ?? null) === 'bkash') {
            return $this->placeBkashAddonOrder($tenant, $addon);
        }

        // Bank transfer: a pending order is raised for the BeeCore team to verify.
        $recurring = in_array($addon->billing_cycle, ['monthly', 'yearly'], true);
        $start = today();
        $periodEnd = $addon->billing_cycle === 'yearly'
            ? $start->copy()->addYear()->subDay()
            : ($addon->billing_cycle === 'monthly' ? $start->copy()->addMonth()->subDay() : null);

        DB::transaction(function () use ($tenant, $addon, $method, $subscription, $start, $periodEnd, $recurring) {
            $row = TenantAddon::create([
                'tenant_id' => $tenant->id,
                'addon_id' => $addon->id,
                'status' => 'pending_approval',
                'price' => $addon->price,
                'billing_cycle' => $addon->billing_cycle,
                'assigned_by' => auth()->id(),
                'starts_at' => now(),
                'period_start' => $start,
                'period_end' => $periodEnd,
                'auto_renew' => $recurring,
            ]);

            $dueDate = $start->copy()->addDays(7);

            $invoice = SaasInvoice::create([
                'tenant_id' => $tenant->id,
                'tenant_subscription_id' => $subscription->id,
                'tenant_addon_id' => $row->id,
                'invoice_number' => SaasInvoice::draftNumber(),
                'status' => 'pending',
                'period_start' => $start->toDateString(),
                'period_end' => ($periodEnd ?? $start)->toDateString(),
                'amount' => $addon->price,
                'due_date' => $dueDate->toDateString(),
                'paid_at' => null,
            ]);
            $invoice->setSequentialNumber();

            SaasInvoiceItem::create([
                'saas_invoice_id' => $invoice->id,
                'type' => 'charge',
                'description' => $addon->name.' add-on ('.$addon->billing_cycle.')',
                'amount' => $addon->price,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            SaasPayment::create([
                'tenant_id' => $tenant->id,
                'saas_invoice_id' => $invoice->id,
                'recorded_by' => auth()->id(),
                'amount' => $addon->price,
                'method' => $method['provider'] ?? 'manual',
                'reference' => 'Add-on checkout — '.($method['name'] ?? 'BeeCore'),
                'status' => 'pending',
                'verified_at' => null,
                'verified_by' => null,
                'paid_at' => now(),
            ]);

            AuditLog::record('addon.purchase_pending', $row, ['addon_id' => $addon->id, 'amount' => $addon->price, 'cycle' => $addon->billing_cycle], tenantId: $tenant->id);
        });

        if ($this->getErrorBag()->isNotEmpty()) {
            return '';
        }

        return __('Order submitted. :addon becomes active once the BeeCore team verifies your payment.', ['addon' => $addon->name]);
    }

    /**
     * Online bKash add-on checkout: the order rides inside the BeeCore payment
     * intent and is materialised (add-on + paid invoice) only after bKash
     * confirms the payment — a failed/cancelled attempt leaves no records.
     */
    private function placeBkashAddonOrder(Tenant $tenant, Addon $addon): string
    {
        $intent = null;

        DB::transaction(function () use ($tenant, $addon, &$intent) {
            $intent = BeePaymentIntent::findOpen(BeePaymentIntent::KIND_SAAS_ADDON, $tenant->id, [
                'addon_id' => $addon->id,
                'billing_cycle' => $addon->billing_cycle,
            ])
                ?? BeePaymentIntent::createFor(BeePaymentIntent::KIND_SAAS_ADDON, $tenant->id, (float) $addon->price, [
                    'deferred' => true,
                    'addon_id' => $addon->id,
                    'billing_cycle' => $addon->billing_cycle,
                ]);
        });

        if ($this->getErrorBag()->isNotEmpty()) {
            return '';
        }

        $this->beePayUrl = route('bee-pay.intent', ['intent' => $intent->token]);

        return '';
    }

    private function hasOpenAddon(int $addonId): bool
    {
        return TenantAddon::query()
            ->where('tenant_id', $this->tenantId())
            ->where('addon_id', $addonId)
            ->whereIn('status', ['active', 'requested', 'pending_approval'])
            ->exists();
    }

    private function activeSubscription(): ?TenantSubscription
    {
        return TenantSubscription::query()
            ->where('tenant_id', $this->tenantId())
            ->whereIn('status', ['active', 'trialing'])
            ->latest('id')
            ->first();
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

        if (! collect($methods)->contains(fn ($m) => $m['manual'])) {
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

    public function render()
    {
        $tenantId = $this->tenantId();

        $catalog = Addon::query()
            ->where('is_active', true)
            ->whereNull('archived_at')
            ->when($this->categoryFilter !== '', fn ($q) => $q->where('category', $this->categoryFilter))
            ->orderBy('name')
            ->get();

        $mine = TenantAddon::query()
            ->where('tenant_id', $tenantId)
            ->with('addon')
            ->orderByDesc('id')
            ->get();

        // If an add-on order is stuck as pending_approval with an open invoice
        // (e.g. the bKash session was abandoned), let the owner continue it.
        $openInvoiceByAddon = SaasInvoice::query()
            ->whereIn('tenant_addon_id', $mine->pluck('id'))
            ->whereIn('status', ['pending', 'overdue'])
            ->latest('id')
            ->get()
            ->keyBy('tenant_addon_id');

        $stateByAddon = $mine->groupBy('addon_id')->map(fn ($rows) => $rows->first());

        $checkoutAddon = $this->checkoutAddonId
            ? Addon::query()->where('is_active', true)->whereNull('archived_at')->find($this->checkoutAddonId)
            : null;

        return view('livewire.isp-addons', [
            'workspace' => Tenant::query()->find($tenantId),
            'catalog' => $catalog,
            'mine' => $mine,
            'stateByAddon' => $stateByAddon,
            'openInvoiceByAddon' => $openInvoiceByAddon,
            'categories' => AddOns::CATEGORIES,
            'paymentMethods' => $this->paymentMethods(),
            'checkoutAddon' => $checkoutAddon,
            'summary' => [
                'active' => $mine->where('status', 'active')->count(),
                'pending' => $mine->whereIn('status', ['requested', 'pending_approval'])->count(),
                'monthlySpend' => $mine
                    ->where('status', 'active')
                    ->filter(fn ($row) => in_array($row->billing_cycle, ['monthly', 'yearly'], true))
                    ->sum(fn ($row) => $row->billing_cycle === 'monthly' ? (float) $row->price : (float) $row->price / 12),
            ],
        ]);
    }
}
