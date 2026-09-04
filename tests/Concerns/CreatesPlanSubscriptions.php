<?php

namespace Tests\Concerns;

use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;

/**
 * Attach an entitled BeeCore plan subscription to a test tenant so plan-quota
 * enforcement tests the intended paths instead of being blocked by it.
 */
trait CreatesPlanSubscriptions
{
    protected function attachActivePlan(Tenant $tenant, ?int $customerLimit = null, ?int $staffLimit = null, ?int $resellerLimit = null): SaasPlan
    {
        $plan = SaasPlan::create([
            'name' => 'Test Plan '.uniqid(),
            'slug' => 'test-plan-'.uniqid(),
            'monthly_price' => 1000,
            'yearly_discount_percent' => 25,
            'customer_limit' => $customerLimit,
            'staff_limit' => $staffLimit,
            'reseller_limit' => $resellerLimit,
            'is_active' => true,
            'operation_mode' => 'both',
        ]);

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'saas_plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'price' => 1000,
            'starts_at' => today(),
            'auto_renew' => true,
        ]);

        return $plan;
    }
}
