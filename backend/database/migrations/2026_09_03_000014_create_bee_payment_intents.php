<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bee_payment_intents', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->string('merchant_invoice_number', 100)->unique();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('kind')->index(); // invoice, saas_plan, saas_addon
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('created')->index(); // created, processing, success, failed
            $table->json('meta')->nullable(); // invoice_id / saas_invoice_id / tenant_addon_id
            $table->string('bkash_payment_id')->nullable();
            $table->string('bkash_trx_id')->nullable();
            $table->timestamp('callback_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bee_payment_intents');
    }
};
