<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the old "one invoice per subscription per period" unique constraint.
 *
 * Multiple BeeCore charges (plan + several add-ons / renewals) can now land on
 * the same subscription in the same period day, so the constraint must go.
 * Migration 2026_09_03_000013 already ran on some databases before this drop
 * was added to it — this migration makes sure every environment ends up clean.
 *
 * On MySQL the unique index doubles as the backing index for the
 * saas_invoices.tenant_subscription_id foreign key, so we release that FK,
 * drop the unique index and re-create the FK on a dedicated column index.
 */
return new class extends Migration
{
    private const UNIQUE = 'saas_invoices_tenant_subscription_id_period_start_unique';

    private const FK = 'saas_invoices_tenant_subscription_id_foreign';

    private const PLAIN = 'saas_invoices_tenant_subscription_id_index';

    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (! Schema::hasIndex('saas_invoices', self::UNIQUE)) {
            return; // Already clean (fresh installs / sqlite).
        }

        // The re-created FK needs its own index once the composite is gone.
        if (! Schema::hasIndex('saas_invoices', self::PLAIN)) {
            Schema::table('saas_invoices', function (Blueprint $table) {
                $table->index('tenant_subscription_id', self::PLAIN);
            });
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE saas_invoices DROP FOREIGN KEY '.self::FK);
        } else {
            Schema::table('saas_invoices', function (Blueprint $table) {
                $table->dropForeign(self::FK);
            });
        }

        Schema::table('saas_invoices', function (Blueprint $table) {
            $table->dropUnique(self::UNIQUE);
        });

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            Schema::table('saas_invoices', function (Blueprint $table) {
                $table->foreign('tenant_subscription_id')->references('id')->on('tenant_subscriptions')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        try {
            Schema::table('saas_invoices', function (Blueprint $table) {
                $table->unique(['tenant_subscription_id', 'period_start'], self::UNIQUE);
            });
        } catch (Throwable $e) {
            // Duplicate rows may exist — leave as is.
        }
    }
};
