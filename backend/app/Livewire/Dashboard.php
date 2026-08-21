<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Announcement;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Network;
use App\Models\Payment;
use App\Models\SaasInvoice;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Support\CurrentTenant;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $tenant = app(CurrentTenant::class)->resolve();
        $tenantId = $tenant?->id;
        $scope = fn ($query) => $tenantId ? $query->where('tenant_id', $tenantId) : $query;
        $customers = $scope(Customer::query());
        $customerCount = (clone $customers)->count();
        $activeCustomers = (clone $customers)->where('status', 'active')->count();
        $payments = $scope(Payment::query())->where('status', 'successful');
        $invoices = $scope(Invoice::query());
        $activeSaasSubscriptions = TenantSubscription::query()->where('status', 'active');
        $saasMrr = (float) (clone $activeSaasSubscriptions)->where('billing_cycle', 'monthly')->sum('price')
            + ((float) (clone $activeSaasSubscriptions)->where('billing_cycle', 'yearly')->sum('price') / 12);
        $saasInvoices = SaasInvoice::query();
        $saasCollectedThisMonth = (float) (clone $saasInvoices)->where('status', 'paid')
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount');
        $saasOutstanding = (float) (clone $saasInvoices)->whereIn('status', ['pending', 'overdue'])->sum('amount');
        $saasOverdueCount = (clone $saasInvoices)->where('status', 'overdue')->count();

        return view('livewire.dashboard', [
            'workspaceName' => $tenant?->name ?? 'SaaS portfolio',
            'isSaasView' => $tenantId === null,
            'metrics' => [
                'tenants' => Tenant::query()->where('status', 'active')->count(),
                'suspended_tenants' => Tenant::query()->where('status', 'suspended')->count(),
                'trial_tenants' => TenantSubscription::query()->where('status', 'trialing')->count(),
                'platform_users' => User::query()->count(),
                'saas_mrr' => $saasMrr,
                'saas_arr' => $saasMrr * 12,
                'saas_collected_this_month' => $saasCollectedThisMonth,
                'saas_outstanding' => $saasOutstanding,
                'saas_overdue_count' => $saasOverdueCount,
                'subscriptions_expiring' => TenantSubscription::query()
                    ->whereIn('status', ['trialing', 'active'])
                    ->whereBetween('current_period_ends_at', [today(), today()->addDays(30)])
                    ->count(),
                'audit_events_today' => AuditLog::query()->where('created_at', '>=', now()->startOfDay())->count(),
                'customers' => $customerCount,
                'monthly_revenue' => (float) (clone $payments)->whereBetween('payment_date', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount'),
                'active_services' => $customerCount > 0 ? round($activeCustomers / $customerCount * 100) : 0,
                'pending_billing' => (clone $invoices)->whereIn('status', ['pending', 'overdue'])->count(),
                'outstanding' => (float) (clone $invoices)->whereIn('status', ['pending', 'overdue'])->sum('total'),
                'online_devices' => $scope(Network::query())->where('status', 'online')->count(),
            ],
            'recentPayments' => (clone $payments)->with(['customer', 'invoice'])->latest('payment_date')->limit(5)->get(),
            'overdueInvoices' => (clone $invoices)->with('customer')->where('status', 'overdue')->oldest('due_date')->limit(5)->get(),
            'recentAuditActivity' => $tenantId === null
                ? AuditLog::query()->with(['user', 'tenant'])->latest('created_at')->limit(7)->get()
                : collect(),
            'activeAnnouncements' => Announcement::query()->activeFor($tenantId)->latest('published_at')->limit(3)->get(),
        ]);
    }
}
