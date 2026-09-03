<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\EmailLog;
use App\Models\SaasInvoice;
use App\Models\SaasPayment;
use App\Models\SmsLog;
use App\Models\Tenant;
use App\Models\TenantAddon;
use App\Models\TenantMediaSetting;
use App\Models\TenantSubscription;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ReportsAnalytics extends Component
{
    public function exportCsv()
    {
        $this->assertSuperAdmin();
        $report = $this->buildReport();

        $rows = [
            ['Metric', 'Value'],
            ['Active ISPs', $report['activeTenants']],
            ['Suspended ISPs', $report['suspendedTenants']],
            ['MRR', $report['mrr']],
            ['ARR', $report['arr']],
            ['Collected this month', $report['collectedThisMonth']],
            ['Add-on revenue (active)', $report['addonRevenue']],
            ['SMS revenue (30d)', $report['smsRevenue']],
            ['Trial conversion rate', $report['trialConversionRate'].'%'],
            ['Churn rate (30d)', $report['churnRate'].'%'],
            ['Payment success rate', $report['paymentSuccessRate'].'%'],
            ['Payment failure rate', $report['paymentFailureRate'].'%'],
            ['Email sent (30d)', $report['emailSent30d']],
            ['API requests (30d)', $report['apiRequests30d']],
            ['Storage used (GB)', $report['storageUsedGb']],
        ];

        $csv = implode("\n", array_map(fn ($row) => implode(',', $row), $rows));

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'beecore-reports-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function render()
    {
        $this->assertSuperAdmin();

        return view('livewire.reports-analytics', $this->buildReport());
    }

    private function buildReport(): array
    {
        $activeSubs = TenantSubscription::query()->where('status', 'active');
        $mrr = (float) (clone $activeSubs)->where('billing_cycle', 'monthly')->sum('price')
            + ((float) (clone $activeSubs)->where('billing_cycle', 'yearly')->sum('price') / 12);

        $since30 = now()->subDays(30);

        $ispGrowth = Tenant::query()
            ->where('created_at', '>=', now()->subMonths(6))
            ->pluck('created_at')
            ->groupBy(fn ($date) => $date->format('Y-m'))
            ->map->count();

        $customerGrowth = Customer::query()
            ->where('created_at', '>=', now()->subMonths(6))
            ->pluck('created_at')
            ->groupBy(fn ($date) => $date->format('Y-m'))
            ->map->count();

        $subscriptionsByStatus = TenantSubscription::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $planDistribution = TenantSubscription::query()
            ->join('saas_plans', 'saas_plans.id', '=', 'tenant_subscriptions.saas_plan_id')
            ->selectRaw('saas_plans.name as plan_name, count(*) as total')
            ->groupBy('saas_plans.name')->pluck('total', 'plan_name');

        $customerDistribution = Customer::query()
            ->join('tenants', 'tenants.id', '=', 'customers.tenant_id')
            ->selectRaw('tenants.name as tenant_name, count(*) as total')
            ->groupBy('tenants.name')->orderByDesc('total')->limit(10)->pluck('total', 'tenant_name');

        $everTrialed = TenantSubscription::query()->whereNotNull('trial_ends_at')->count();
        $converted = TenantSubscription::query()->whereNotNull('trial_ends_at')->where('status', '!=', 'trialing')->count();
        $trialConversionRate = $everTrialed > 0 ? round($converted / $everTrialed * 100) : 0;

        $cancelledThisMonth = TenantSubscription::query()->whereBetween('cancelled_at', [now()->startOfMonth(), now()->endOfMonth()])->count();
        $activeOrCancelled = TenantSubscription::query()->whereIn('status', ['active', 'cancelled'])->count();
        $churnRate = $activeOrCancelled > 0 ? round($cancelledThisMonth / $activeOrCancelled * 100) : 0;

        $paymentsTotal = SaasPayment::query()->count();
        $paymentsSuccess = SaasPayment::query()->where('status', 'completed')->count();
        $paymentsFailed = SaasPayment::query()->where('status', 'failed')->count();

        return [
            'activeTenants' => Tenant::query()->where('status', 'active')->count(),
            'suspendedTenants' => Tenant::query()->where('status', 'suspended')->count(),
            'mrr' => $mrr,
            'arr' => $mrr * 12,
            'collectedThisMonth' => (float) SaasInvoice::query()->where('status', 'paid')
                ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount'),
            'addonRevenue' => (float) TenantAddon::query()->where('status', 'active')->sum('price'),
            'smsRevenue' => (float) SmsLog::query()->whereIn('status', ['sent', 'delivered'])->where('created_at', '>=', $since30)->sum('cost'),
            'emailSent30d' => EmailLog::query()->whereIn('status', ['sent', 'delivered'])->where('created_at', '>=', $since30)->count(),
            'apiRequests30d' => DB::table('api_client_logs')->where('created_at', '>=', $since30)->count(),
            'storageUsedGb' => (int) TenantMediaSetting::query()->sum('storage_used_gb'),
            'ispGrowth' => $ispGrowth,
            'customerGrowth' => $customerGrowth,
            'subscriptionsByStatus' => $subscriptionsByStatus,
            'planDistribution' => $planDistribution,
            'customerDistribution' => $customerDistribution,
            'trialConversionRate' => $trialConversionRate,
            'churnRate' => $churnRate,
            'paymentSuccessRate' => $paymentsTotal > 0 ? round($paymentsSuccess / $paymentsTotal * 100) : 0,
            'paymentFailureRate' => $paymentsTotal > 0 ? round($paymentsFailed / $paymentsTotal * 100) : 0,
        ];
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
