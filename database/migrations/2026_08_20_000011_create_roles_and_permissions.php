<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PERMISSIONS = [
        ['key' => 'billing.view', 'name' => 'View billing', 'category' => 'financial', 'scope' => 'tenant'],
        ['key' => 'billing.manage', 'name' => 'Manage billing', 'category' => 'financial', 'scope' => 'tenant'],
        ['key' => 'saas_billing.manage', 'name' => 'Manage SaaS billing', 'category' => 'financial', 'scope' => 'platform'],
        ['key' => 'payments.manage', 'name' => 'Manage payments', 'category' => 'financial', 'scope' => 'tenant'],
        ['key' => 'network.view', 'name' => 'View network', 'category' => 'network', 'scope' => 'tenant'],
        ['key' => 'network.manage', 'name' => 'Manage network', 'category' => 'network', 'scope' => 'tenant'],
        ['key' => 'network_integrations.manage', 'name' => 'Manage network integrations', 'category' => 'network', 'scope' => 'tenant'],
        ['key' => 'security.manage_users', 'name' => 'Manage platform users', 'category' => 'security', 'scope' => 'platform'],
        ['key' => 'security.manage_roles', 'name' => 'Manage roles & permissions', 'category' => 'security', 'scope' => 'platform'],
        ['key' => 'security.view_audit', 'name' => 'View audit log', 'category' => 'audit', 'scope' => 'platform'],
        ['key' => 'audit.export', 'name' => 'Export audit log', 'category' => 'audit', 'scope' => 'platform'],
        ['key' => 'tenants.manage', 'name' => 'Manage tenants', 'category' => 'tenant', 'scope' => 'platform'],
        ['key' => 'customers.manage', 'name' => 'Manage customers', 'category' => 'tenant', 'scope' => 'tenant'],
        ['key' => 'resellers.manage', 'name' => 'Manage resellers', 'category' => 'tenant', 'scope' => 'tenant'],
        ['key' => 'reports.view', 'name' => 'View reports', 'category' => 'other', 'scope' => 'tenant'],
    ];

    private const ROLES = [
        'super_admin' => ['name' => 'Super admin', 'permissions' => '*'],
        'tenant_admin' => ['name' => 'Tenant admin', 'permissions' => ['billing.view', 'billing.manage', 'payments.manage', 'network.view', 'network.manage', 'customers.manage', 'resellers.manage', 'reports.view']],
        'finance' => ['name' => 'Finance', 'permissions' => ['billing.view', 'billing.manage', 'payments.manage', 'reports.view']],
        'support' => ['name' => 'Support', 'permissions' => ['customers.manage', 'reports.view']],
        'network_engineer' => ['name' => 'Network engineer', 'permissions' => ['network.view', 'network.manage', 'network_integrations.manage', 'reports.view']],
        'reseller' => ['name' => 'Reseller', 'permissions' => ['customers.manage']],
    ];

    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('category')->default('other')->index(); // financial, network, security, audit, tenant, other
            $table->string('scope')->default('tenant')->index(); // platform, tenant
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->boolean('is_system')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->unique(['role_id', 'permission_id']);
        });

        $now = now();
        DB::table('permissions')->insert(array_map(fn ($p) => [
            'key' => $p['key'], 'name' => $p['name'], 'category' => $p['category'], 'scope' => $p['scope'],
            'created_at' => $now, 'updated_at' => $now,
        ], self::PERMISSIONS));

        $permissionIds = DB::table('permissions')->pluck('id', 'key');

        foreach (self::ROLES as $key => $role) {
            $roleId = DB::table('roles')->insertGetId([
                'key' => $key, 'name' => $role['name'], 'is_system' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);

            $keys = $role['permissions'] === '*' ? array_keys($permissionIds->all()) : $role['permissions'];
            foreach ($keys as $permissionKey) {
                DB::table('role_permissions')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionIds[$permissionKey],
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
