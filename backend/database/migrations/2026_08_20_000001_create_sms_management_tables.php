<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('provider'); // twilio, banglalink, ssl_wireless, manual, etc.
            $table->string('sender_id')->nullable();
            $table->decimal('price_per_sms', 10, 4)->default(0);
            $table->boolean('is_active')->default(false);
            $table->text('credentials')->nullable(); // encrypted JSON key/value pairs
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('tenant_sms_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('balance')->default(0); // SMS credits
            $table->timestamps();
        });

        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sms_provider_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient');
            $table->text('message');
            $table->string('status')->default('queued')->index(); // queued, sent, delivered, failed
            $table->decimal('cost', 10, 4)->default(0);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_templates');
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('tenant_sms_balances');
        Schema::dropIfExists('sms_providers');
    }
};
