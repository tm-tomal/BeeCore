<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_analytics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('total_tenants');
            $table->unsignedInteger('active_tenants');
            $table->unsignedInteger('trial_tenants');
            $table->unsignedInteger('suspended_tenants');
            $table->unsignedInteger('total_customers');
            $table->unsignedInteger('total_resellers');
            $table->decimal('mrr', 12, 2);
            $table->decimal('arr', 12, 2);
            $table->decimal('arpu', 12, 2);
            $table->decimal('churn_rate', 5, 2);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('recorded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_analytics_snapshots');
    }
};
