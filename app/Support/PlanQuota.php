<?php

namespace App\Support;

use App\Models\SaasPlan;
use App\Models\Tenant;

/**
 * Enforces BeeCore subscription-plan quotas for tenant workspace records.
 *
 * A tenant must hold an entitled subscription (active / trialing / pending
 * approval / past due) before it may add customers, staff or resellers, and
 * it may never exceed the numeric limit configured on the plan. A null plan
 * limit means the plan includes unlimited capacity.
 */
class PlanQuota
{
    public const CUSTOMERS = 'customers';
    public const STAFF = 'staff';
    public const RESELLERS = 'resellers';

    /** Subscription statuses that grant plan capacity. */
    public const ENTITLED_STATUSES = ['active', 'trialing', 'pending_approval', 'past_due'];

    /** @var array<string, string> */
    private const LABELS = [
        self::CUSTOMERS => 'customer',
        self::STAFF => 'team member',
        self::RESELLERS => 'reseller',
    ];

    /** @var array<string, string> */
    private const LIMIT_COLUMNS = [
        self::CUSTOMERS => 'customer_limit',
        self::STAFF => 'staff_limit',
        self::RESELLERS => 'reseller_limit',
    ];

    /** @var array<string, array<int, string>> */
    private const ROLE_COLUMNS = [
        self::CUSTOMERS => [],
        self::STAFF => ['tenant_admin', 'finance', 'support', 'network_engineer'],
        self::RESELLERS => [],
    ];

    /**
     * Check whether $extra more records of $kind may be created for the tenant.
     *
     * @return array{allowed: bool, reason: ?string, usage: int, limit: ?int, message: string, label: string, plan: ?SaasPlan}
     */
    public static function check(Tenant $tenant, string $kind, int $extra = 1): array
    {
        $subscription = $tenant->saasSubscriptions()
            ->whereIn('status', self::ENTITLED_STATUSES)
            ->latest('id')
            ->first();

        $plan = $subscription?->plan;
        $usage = self::usage($tenant, $kind);
        $limit = $plan ? self::limitFor($plan, $kind) : null;
        $label = self::label($kind);

        if (! $plan) {
            return [
                'allowed' => false,
                'reason' => 'no_plan',
                'usage' => $usage,
                'limit' => null,
                'plan' => null,
                'label' => $label,
                'message' => __('Your workspace does not have an active BeeCore plan yet. Choose a plan first — it determines how many :label records you can create.', ['label' => $label]),
            ];
        }

        if ($limit !== null && $usage + $extra > $limit) {
            return [
                'allowed' => false,
                'reason' => 'limit',
                'usage' => $usage,
                'limit' => $limit,
                'plan' => $plan,
                'label' => $label,
                'message' => __("Your current plan allows :limit :label records and you already use :usage. Upgrade your plan to add more.", [
                    'limit' => $limit,
                    'label' => $label,
                    'usage' => $usage,
                ]),
            ];
        }

        return [
            'allowed' => true,
            'reason' => null,
            'usage' => $usage,
            'limit' => $limit,
            'plan' => $plan,
            'label' => $label,
            'message' => '',
        ];
    }

    /** Current usage of a quota kind for the tenant. */
    public static function usage(Tenant $tenant, string $kind): int
    {
        return match ($kind) {
            self::CUSTOMERS => $tenant->customers()->count(),
            self::STAFF => $tenant->users()->where('status', 'active')->whereIn('role', self::ROLE_COLUMNS[self::STAFF])->count(),
            self::RESELLERS => $tenant->resellers()->count(),
            default => 0,
        };
    }

    /** Configured plan limit (null = unlimited). */
    public static function limitFor(SaasPlan $plan, string $kind): ?int
    {
        $column = self::LIMIT_COLUMNS[$kind] ?? null;

        if (! $column) {
            return null;
        }

        $value = $plan->{$column};

        return $value === null ? null : (int) $value;
    }

    public static function label(string $kind): string
    {
        return self::LABELS[$kind] ?? $kind;
    }
}
