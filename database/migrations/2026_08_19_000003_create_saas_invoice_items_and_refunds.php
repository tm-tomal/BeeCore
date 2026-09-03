<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saas_invoice_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('charge')->index(); // charge, discount, adjustment, credit
            $table->string('description');
            $table->decimal('amount', 12, 2); // negative for discount/credit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('saas_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('saas_payment_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('reason')->nullable();
            $table->foreignId('refunded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('refunded_at')->useCurrent();
        });

        Schema::table('saas_invoices', function (Blueprint $table) {
            $table->timestamp('reminder_sent_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('saas_invoices', function (Blueprint $table) {
            $table->dropColumn('reminder_sent_at');
        });

        Schema::dropIfExists('saas_refunds');
        Schema::dropIfExists('saas_invoice_items');
    }
};
