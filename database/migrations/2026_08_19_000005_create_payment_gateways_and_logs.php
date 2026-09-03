<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('provider'); // stripe, bkash, nagad, sslcommerz, manual, etc.
            $table->string('mode')->default('sandbox'); // sandbox, live
            $table->boolean('is_active')->default(false);
            $table->text('credentials')->nullable(); // encrypted JSON key/value pairs
            $table->text('webhook_secret')->nullable(); // encrypted
            $table->string('webhook_url')->nullable();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('payment_gateway_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_gateway_id')->constrained()->cascadeOnDelete();
            $table->string('event');
            $table->string('status')->index(); // success, failed
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_logs');
        Schema::dropIfExists('payment_gateways');
    }
};
