<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_subscription_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('status')->default('pending')->index();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('amount', 12, 2);
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->unique(['tenant_subscription_id', 'period_start']);
        });

        Schema::create('saas_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('saas_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('method')->default('manual');
            $table->string('reference')->nullable();
            $table->timestamp('paid_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_payments');
        Schema::dropIfExists('saas_invoices');
    }
};
