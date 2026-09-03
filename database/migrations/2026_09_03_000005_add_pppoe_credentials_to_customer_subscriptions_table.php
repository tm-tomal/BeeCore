<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_subscriptions', function (Blueprint $table) {
            $table->string('pppoe_username', 190)->nullable()->after('package_name');
            $table->text('pppoe_password')->nullable()->after('pppoe_username');

            $table->index(['tenant_id', 'pppoe_username'], 'customer_subscriptions_pppoe_username_index');
        });
    }

    public function down(): void
    {
        Schema::table('customer_subscriptions', function (Blueprint $table) {
            $table->dropIndex('customer_subscriptions_pppoe_username_index');
            $table->dropColumn(['pppoe_password', 'pppoe_username']);
        });
    }
};
