<?php

namespace App\Support;

use App\Models\TenantRolePermission;
use App\Models\User;

/**
 * Workspace role permissions.
 *
 * The ISP Owner (tenant_admin) and platform super admins always have full
 * access. Every staff role (finance / support / network_engineer) starts with
 * a sensible default set of modules (mirroring the historic role gates) that
 * the ISP Owner can fine-tune per module. Rows are only written when the owner
 * changes a value; anything without a row falls back to the defaults.
 */
class TenantPermissions
{
    public const STAFF_ROLES = [User::ROLE_FINANCE, User::ROLE_SUPPORT, User::ROLE_NETWORK_ENGINEER];

    public const MODULES = ['customers', 'billing', 'network', 'reports', 'support', 'issues'];

    /**
     * Human-facing module catalogue shown in the ISP Owner UI.
     */
    public static function catalog(): array
    {
        return [
            'customers' => ['label' => __('Customers'), 'description' => __('View and manage the customer directory and profiles.')],
            'billing' => ['label' => __('Billing & payments'), 'description' => __('Create invoices, record payments and manage billing.')],
            'network' => ['label' => __('Network & cable map'), 'description' => __('Operate devices, cable map and connectivity.')],
            'reports' => ['label' => __('Reports'), 'description' => __('Open reports, snapshots and printable exports.')],
            'support' => ['label' => __('Support'), 'description' => __('Handle customer support tickets.')],
            'issues' => ['label' => __('Network issues'), 'description' => __('Log and resolve network problems reported on the map.')],
        ];
    }

    /**
     * Default module access per role, matching the original built-in gates.
     */
    public static function defaults(): array
    {
        return [
            User::ROLE_FINANCE => [
                'customers' => false,
                'billing' => true,
                'network' => false,
                'reports' => true,
                'support' => false,
                'issues' => false,
            ],
            User::ROLE_SUPPORT => [
                'customers' => true,
                'billing' => false,
                'network' => false,
                'reports' => true,
                'support' => true,
                'issues' => true,
            ],
            User::ROLE_NETWORK_ENGINEER => [
                'customers' => true,
                'billing' => false,
                'network' => true,
                'reports' => true,
                'support' => false,
                'issues' => true,
            ],
        ];
    }

    public static function isEnabled(int $tenantId, string $role, string $module): bool
    {
        if (! in_array($module, self::MODULES, true) || ! in_array($role, self::STAFF_ROLES, true)) {
            return false;
        }

        $row = TenantRolePermission::query()
            ->where('tenant_id', $tenantId)
            ->where('role', $role)
            ->where('module', $module)
            ->first();

        if ($row) {
            return (bool) $row->allowed;
        }

        return (bool) (self::defaults()[$role][$module] ?? false);
    }

    public static function setEnabled(int $tenantId, string $role, string $module, bool $enabled): void
    {
        if (! in_array($module, self::MODULES, true) || ! in_array($role, self::STAFF_ROLES, true)) {
            return;
        }

        TenantRolePermission::updateOrCreate(
            ['tenant_id' => $tenantId, 'role' => $role, 'module' => $module],
            ['allowed' => $enabled],
        );
    }

    /**
     * Enabled modules for one role, keyed by module => bool.
     */
    public static function roleModules(int $tenantId, string $role): array
    {
        if (! in_array($role, self::STAFF_ROLES, true)) {
            return [];
        }

        return collect(self::MODULES)->mapWithKeys(fn (string $module) => [
            $module => self::isEnabled($tenantId, $role, $module),
        ])->all();
    }

    /**
     * Blocks a staff member when their role does not have the module enabled.
     * Owners and platform super admins always pass.
     */
    public static function assert(string $module): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 401);

        if ($user->role === User::ROLE_SUPER_ADMIN || $user->role === User::ROLE_TENANT_ADMIN) {
            return;
        }

        abort_unless(in_array($user->role, self::STAFF_ROLES, true), 403);

        $tenantId = $user->tenant_id;

        abort_unless($tenantId && self::isEnabled((int) $tenantId, $user->role, $module), 403);
    }

    /**
     * Resolve an actor's workspace tenant id (staff belong to their own tenant).
     */
    public static function tenantIdFor(User $user): ?int
    {
        return $user->role === User::ROLE_SUPER_ADMIN
            ? (int) session('impersonated_tenant_id')
            : $user->tenant_id;
    }
}
