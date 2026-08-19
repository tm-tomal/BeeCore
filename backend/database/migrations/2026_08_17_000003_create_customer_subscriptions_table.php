<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('package_name');
            $table->decimal('price', 12, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semiannual', 'yearly'])->default('monthly');
            $table->enum('status', ['active', 'paused', 'cancelled'])->default('active');
            $table->date('next_billing_date');
            $table->date('started_at');
            $table->date('ended_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status', 'next_billing_date']);
            $table->index(['customer_id', 'status']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('subscription_id')->nullable()->after('customer_id')->constrained('customer_subscriptions')->nullOnDelete();
            $table->date('billing_period_start')->nullable()->after('due_date');
            $table->unique(['subscription_id', 'billing_period_start'], 'invoices_subscription_period_unique');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('invoices_subscription_period_unique');
            $table->dropConstrainedForeignId('subscription_id');
            $table->dropColumn('billing_period_start');
        });

        Schema::dropIfExists('customer_subscriptions');
    }
};