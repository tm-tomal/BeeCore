<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\PlatformAnalyticsSnapshot;
use App\Models\Reseller;
use App\Models\SaasPayment;
use App\Models\Tenant;
use App\Models\TenantAddon;
use App\Models\TenantSubscription;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PlatformAnalytics extends Component
{
    public function recordSnapshotNow(): void
    {
        $this->assertSuperAdmin();
        $metrics = $this->computeMetrics();

        $snapshot = PlatformAnalyticsSnapshot::create([
            'total_tenants' => $metrics['totalTenants'],
            'active_tenants' => $metrics['activeTenants'],
            'trial_tenants' => $metrics['trialTenants'],
            'suspended_tenants' => $metrics['suspendedTenants'],
            'total_customers' => $metrics['totalCustomers'],
            'total_resellers' => $metrics['totalResellers'],
            'mrr' => $metrics['mrr'],
            'arr' => $metrics['arr'],
            'arpu' => $metrics['arpu'],
            'churn_rate' => $metrics['churnRate'],
            'recorded_by' => auth()->id(),
            'recorded_at' => now(),
        ]);

        AuditLog::record('platform_analytics.snapshot_recorded', $snapshot);
        session()->flash('message', 'Platform analytics snapshot recorded.');
    }

    public function render()
    {
        $this->assertSuperAdmin();

        $metrics = $this->computeMetrics();

        $addonGrowth = TenantAddon::query()
            ->where('created_at', '>=', now()->subMonths(6))
            ->pluck('created_at')
            ->groupBy(fn ($date) => $date->format('Y-m'))
            ->map->count();

        return view('livewire.platform-analytics', array_merge($metrics, [
            'addonGrowth' => $addonGrowth,
            'history' => PlatformAnalyticsSnapshot::query()->with('recordedBy')->latest('recorded_at')->limit(12)->get(),
        ]));
    }

    private function computeMetrics(): array
    {
        $activeTenants = Tenant::query()->where('status', 'active')->count();
        $totalCustomers = Customer::query()->count();

        $activeSubs = TenantSubscription::query()->where('status', 'active');
        $mrr = (float) (clone $activeSubs)->where('billing_cycle', 'monthly')->sum('price')
            + ((float) (clone $activeSubs)->where('billing_cycle', 'yearly')->sum('price') / 12);

        $cancelledThisMonth = TenantSubscription::query()->whereBetween('cancelled_at', [now()->startOfMonth(), now()->endOfMonth()])->count();
        $activeOrCancelled = TenantSubscription::query()->whereIn('status', ['active', 'cancelled'])->count();

        return [
            'totalTenants' => Tenant::query()->count(),
            'activeTenants' => $activeTenants,
            'trialTenants' => TenantSubscription::query()->where('status', 'trialing')->count(),
            'suspendedTenants' => Tenant::query()->where('status', 'suspended')->count(),
            'totalCustomers' => $totalCustomers,
            'totalResellers' => Reseller::query()->count(),
            'totalRevenue' => (float) SaasPayment::query()->where('status', 'completed')->sum('amount'),
            'mrr' => $mrr,
            'arr' => $mrr * 12,
            'arpu' => $activeTenants > 0 ? round($mrr / $activeTenants, 2) : 0,
            'churnRate' => $activeOrCancelled > 0 ? round($cancelledThisMonth / $activeOrCancelled * 100, 2) : 0,
        ];
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
