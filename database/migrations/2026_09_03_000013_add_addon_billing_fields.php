<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tenant_addons', 'period_start')) {
            Schema::table('tenant_addons', function (Blueprint $table) {
                $table->date('period_start')->nullable()->after('billing_cycle');
                $table->date('period_end')->nullable()->after('period_start');
                $table->boolean('auto_renew')->default(true)->after('period_end');
            });
        }

        if (! Schema::hasColumn('saas_invoices', 'tenant_addon_id')) {
            Schema::table('saas_invoices', function (Blueprint $table) {
                $table->foreignId('tenant_addon_id')->nullable()->after('tenant_subscription_id')->constrained('tenant_addons')->nullOnDelete();
            });
        }

        // Multiple add-on charges can land on the same subscription period day.
        try {
            Schema::table('saas_invoices', function (Blueprint $table) {
                $table->dropUnique(['tenant_subscription_id', 'period_start']);
            });
        } catch (Throwable $e) {
            // Already dropped.
        }
    }

    public function down(): void
    {
        try {
            Schema::table('saas_invoices', function (Blueprint $table) {
                $table->unique(['tenant_subscription_id', 'period_start']);
            });
        } catch (Throwable $e) {
            // Already present.
        }

        if (Schema::hasColumn('saas_invoices', 'tenant_addon_id')) {
            Schema::table('saas_invoices', function (Blueprint $table) {
                $table->dropConstrainedForeignId('tenant_addon_id');
            });
        }

        if (Schema::hasColumn('tenant_addons', 'period_start')) {
            Schema::table('tenant_addons', function (Blueprint $table) {
                $table->dropColumn(['period_start', 'period_end', 'auto_renew']);
            });
        }
    }
};
