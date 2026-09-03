<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 12, 2)->default(0);
            $table->decimal('yearly_price', 12, 2)->default(0);
            $table->unsignedInteger('customer_limit')->nullable();
            $table->unsignedInteger('staff_limit')->nullable();
            $table->unsignedInteger('reseller_limit')->nullable();
            $table->unsignedBigInteger('storage_limit_mb')->nullable();
            $table->unsignedInteger('api_limit')->nullable();
            $table->unsignedInteger('sms_limit')->nullable();
            $table->unsignedInteger('email_limit')->nullable();
            $table->json('features')->nullable();
            $table->unsignedSmallInteger('trial_days')->default(0);
            $table->unsignedSmallInteger('grace_days')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('tenant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('saas_plan_id')->constrained()->restrictOnDelete();
            $table->string('status')->index();
            $table->string('billing_cycle')->default('monthly');
            $table->decimal('price', 12, 2);
            $table->date('starts_at');
            $table->date('trial_ends_at')->nullable();
            $table->date('current_period_ends_at')->nullable()->index();
            $table->date('grace_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('tenant_subscription_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event');
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_subscription_events');
        Schema::dropIfExists('tenant_subscriptions');
        Schema::dropIfExists('saas_plans');
    }
};