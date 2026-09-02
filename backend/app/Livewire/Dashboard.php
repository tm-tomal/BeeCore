<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Announcement;
use App\Models\Customer;
use App\Models\CustomerSubscription;
use App\Models\Invoice;
use App\Models\Network;
use App\Models\Payment;
use App\Models\SaasInvoice;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Support\CurrentTenant;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public string $range = '12m';

    public function updatedRange(string $value): void
    {
        $this->range = in_array($value, ['6m', '12m'], true) ? $value : '12m';
    }

    public function render()
    {
        $tenant = app(CurrentTenant::class)->resolve();
        $tenantId = $tenant?->id;
        $months = $this->range === '6m' ? 6 : 12;

        $scope = fn ($query) => $tenantId ? $query->where('tenant_id', $tenantId) : $query;

        $customers = $scope(Customer::query());
        $customerCount = (clone $customers)->count();
        $activeCustomers = (clone $customers)->where('status', 'active')->count();
        $suspendedCustomers = (clone $customers)->where('status', 'suspended')->count();

        $payments = $scope(Payment::query())->where('status', 'successful');
        $invoices = $scope(Invoice::query());

        $activeSaasSubscriptions = TenantSubscription::query()->where('status', 'active');
        $saasMrr = (float) (clone $activeSaasSubscriptions)->where('billing_cycle', 'monthly')->sum('price')
            + ((float) (clone $activeSaasSubscriptions)->where('billing_cycle', 'yearly')->sum('price') / 12);
        $saasInvoices = SaasInvoice::query();
        $saasCollectedThisMonth = (float) (clone $saasInvoices)->where('status', 'paid')
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount');
        $saasCollectedLastMonth = (float) (clone $saasInvoices)->where('status', 'paid')
            ->whereBetween('paid_at', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])->sum('amount');
        $saasOutstanding = (float) (clone $saasInvoices)->whereIn('status', ['pending', 'overdue'])->sum('amount');
        $saasOverdueCount = (clone $saasInvoices)->where('status', 'overdue')->count();

        $collectedDelta = $this->percentageDelta($saasCollectedThisMonth, $saasCollectedLastMonth);

        // ---- Chart data (last N months) ----
        $monthLabels = [];
        $monthRanges = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $start = now()->startOfMonth()->subMonths($i);
            $monthLabels[] = $start->format('M y');
            $monthRanges[] = [$start, (clone $start)->endOfMonth()];
        }

        $charts = [];
        $insights = [];

        if ($tenantId === null) {
            $saasCollections = [];
            foreach ($monthRanges as [$rangeStart, $rangeEnd]) {
                $saasCollections[] = (float) (clone $saasInvoices)->where('status', 'paid')->whereBetween('paid_at', [$rangeStart, $rangeEnd])->sum('amount');
            }
            $tenantStatus = Tenant::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
            $planSplit = TenantSubscription::query()
                ->whereIn('status', ['trialing', 'active'])
                ->join('saas_plans', 'saas_plans.id', '=', 'tenant_subscriptions.saas_plan_id')
                ->selectRaw('saas_plans.name as plan_name, COUNT(*) as total')
                ->groupBy('saas_plans.name')
                ->orderByDesc('total')
                ->pluck('total', 'plan_name');
            $invoiceStatus = (clone $saasInvoices)->selectRaw('status, SUM(amount) as total')->groupBy('status')->pluck('total', 'status');

            $newTenantsThisMonth = Tenant::query()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();

            $charts = [
                'saas_collections' => ['labels' => $monthLabels, 'values' => $saasCollections],
                'tenant_status' => [
                    'labels' => $tenantStatus->keys()->map(fn ($v) => ucfirst($v))->all(),
                    'values' => $tenantStatus->values()->all(),
                ],
                'plan_split' => [
                    'labels' => $planSplit->keys()->all(),
                    'values' => $planSplit->values()->all(),
                ],
                'invoice_status' => [
                    'labels' => $invoiceStatus->keys()->map(fn ($v) => ucfirst($v))->all(),
                    'values' => $invoiceStatus->values()->all(),
                ],
            ];
            $insights = [
                'outstanding' => $saasOutstanding,
                'overdue' => $saasOverdueCount,
                'new_tenants' => $newTenantsThisMonth,
                'expiring' => TenantSubscription::query()
                    ->whereIn('status', ['trialing', 'active'])
                    ->whereBetween('current_period_ends_at', [today(), today()->addDays(30)])
                    ->count(),
            ];
        } else {
            $revenue = [];
            $signups = [];
            $billed = [];
            foreach ($monthRanges as [$rangeStart, $rangeEnd]) {
                $revenue[] = (float) (clone $payments)->whereBetween('payment_date', [$rangeStart, $rangeEnd])->sum('amount');
                $signups[] = (clone $customers)->whereBetween('created_at', [$rangeStart, $rangeEnd])->count();
                $billed[] = (float) (clone $invoices)->whereBetween('created_at', [$rangeStart, $rangeEnd])->sum('total');
            }

            $customerStatus = (clone $customers)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
            $methodSplit = (clone $payments)->selectRaw('payment_method, SUM(amount) as total')->groupBy('payment_method')->pluck('total', 'payment_method');
            $packageSplit = CustomerSubscription::query()
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->selectRaw('package_name, COUNT(*) as total')
                ->groupBy('package_name')
                ->orderByDesc('total')
                ->limit(6)
                ->pluck('total', 'package_name');

            $revenueThisMonth = (float) (clone $payments)->whereBetween('payment_date', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount');
            $revenueLastMonth = (float) (clone $payments)->whereBetween('payment_date', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])->sum('amount');
            $newThisMonth = (clone $customers)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();
            $newLastMonth = (clone $customers)->whereBetween('created_at', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])->count();
            $pendingInvoices = (clone $invoices)->whereIn('status', ['pending', 'overdue']);
            $outstanding = (float) (clone $invoices)->whereIn('status', ['pending', 'overdue'])->sum('total');
            $overdueCount = (clone $invoices)->where('status', 'overdue')->count();
            $networks = $scope(Network::query());
            $onlineDevices = (clone $networks)->where('status', 'online')->count();
            $totalDevices = (clone $networks)->count();

            $revenueDelta = $this->percentageDelta($revenueThisMonth, $revenueLastMonth);
            $signupDelta = $this->percentageDelta($newThisMonth, $newLastMonth);

            $charts = [
                'revenue' => ['labels' => $monthLabels, 'values' => $revenue],
                'billed' => ['labels' => $monthLabels, 'values' => $billed],
                'signups' => ['labels' => $monthLabels, 'values' => $signups],
                'customer_status' => [
                    'labels' => $customerStatus->keys()->map(fn ($v) => ucfirst($v))->all(),
                    'values' => $customerStatus->values()->all(),
                ],
                'methods' => [
                    'labels' => $methodSplit->keys()->map(fn ($v) => ucfirst(str_replace('_', ' ', $v)))->all(),
                    'values' => $methodSplit->values()->all(),
                ],
                'packages' => [
                    'labels' => $packageSplit->keys()->all(),
                    'values' => $packageSplit->values()->all(),
                ],
            ];
            $insights = [
                'new_customers' => $newThisMonth,
                'new_customers_pct' => $signupDelta,
                'active_pct' => $customerCount > 0 ? round($activeCustomers / $customerCount * 100) : 0,
                'suspended' => $suspendedCustomers,
                'overdue' => $overdueCount,
                'outstanding' => $outstanding,
                'online_pct' => $totalDevices > 0 ? round($onlineDevices / $totalDevices * 100) : 0,
                'online_total' => $onlineDevices,
                'total_devices' => $totalDevices,
                'pending' => $pendingInvoices->count(),
            ];
        }

        return view('livewire.dashboard', [
            'workspaceName' => $tenant?->name ?? 'SaaS portfolio',
            'isSaasView' => $tenantId === null,
            'rangeLabel' => $months.' months',
            'charts' => $charts,
            'insights' => $insights,
            'metrics' => [
                'tenants' => Tenant::query()->where('status', 'active')->count(),
                'suspended_tenants' => Tenant::query()->where('status', 'suspended')->count(),
                'trial_tenants' => TenantSubscription::query()->where('status', 'trialing')->count(),
                'new_tenants' => Tenant::query()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
                'platform_users' => User::query()->count(),
                'saas_mrr' => $saasMrr,
                'saas_arr' => $saasMrr * 12,
                'saas_collected_this_month' => $saasCollectedThisMonth,
                'saas_collected_last_month' => $saasCollectedLastMonth,
                'saas_collected_delta' => $collectedDelta,
                'saas_outstanding' => $saasOutstanding,
                'saas_overdue_count' => $saasOverdueCount,
                'subscriptions_expiring' => TenantSubscription::query()
                    ->whereIn('status', ['trialing', 'active'])
                    ->whereBetween('current_period_ends_at', [today(), today()->addDays(30)])
                    ->count(),
                'audit_events_today' => AuditLog::query()->where('created_at', '>=', now()->startOfDay())->count(),
                'customers' => $customerCount,
                'active_customers' => $activeCustomers,
                'suspended_customers' => $suspendedCustomers,
                'new_customers' => $insights['new_customers'] ?? 0,
                'new_customers_pct' => $insights['new_customers_pct'] ?? 0,
                'monthly_revenue' => (float) (clone $payments)->whereBetween('payment_date', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount'),
                'revenue_last_month' => isset($revenueLastMonth) ? $revenueLastMonth : 0,
                'revenue_delta' => isset($revenueDelta) ? $revenueDelta : 0,
                'active_services' => $customerCount > 0 ? round($activeCustomers / $customerCount * 100) : 0,
                'pending_billing' => (clone $invoices)->whereIn('status', ['pending', 'overdue'])->count(),
                'overdue_count' => $overdueCount ?? (clone $invoices)->where('status', 'overdue')->count(),
                'outstanding' => (float) (clone $invoices)->whereIn('status', ['pending', 'overdue'])->sum('total'),
                'online_devices' => isset($onlineDevices) ? $onlineDevices : $scope(Network::query())->where('status', 'online')->count(),
                'total_devices' => isset($totalDevices) ? $totalDevices : $scope(Network::query())->count(),
            ],
            'recentPayments' => (clone $payments)->with(['customer', 'invoice'])->latest('payment_date')->limit(5)->get(),
            'overdueInvoices' => (clone $invoices)->with('customer')->where('status', 'overdue')->oldest('due_date')->limit(5)->get(),
            'recentAuditActivity' => $tenantId === null
                ? AuditLog::query()->with(['user', 'tenant'])->latest('created_at')->limit(7)->get()
                : collect(),
            'activeAnnouncements' => Announcement::query()->activeFor($tenantId)->latest('published_at')->limit(3)->get(),
        ]);
    }

    private function percentageDelta(float $current, float $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
