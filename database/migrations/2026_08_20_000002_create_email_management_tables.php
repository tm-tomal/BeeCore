<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('smtp'); // smtp, api
            $table->string('provider'); // smtp, postmark, resend, ses, mailgun, manual
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->boolean('is_active')->default(false);
            $table->text('credentials')->nullable(); // encrypted JSON key/value pairs
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('tenant_email_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('monthly_quota')->default(0);
            $table->unsignedInteger('used_this_month')->default(0);
            $table->timestamps();
        });

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('email_provider_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient');
            $table->string('subject');
            $table->string('category')->default('transactional')->index(); // transactional, bulk
            $table->string('status')->default('queued')->index(); // queued, sent, delivered, failed
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('subject');
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('tenant_email_quotas');
        Schema::dropIfExists('email_providers');
    }
};
