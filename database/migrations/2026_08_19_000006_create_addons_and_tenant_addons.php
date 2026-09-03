<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->index(); // sms, email, storage, media, white_label, custom_domain, branded_app, premium_support, network_integration, infrastructure, custom_dev
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('billing_cycle')->default('monthly'); // one_time, monthly, yearly
            $table->unsignedInteger('usage_limit')->nullable();
            $table->string('usage_unit')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('tenant_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('addon_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('active')->index(); // active, cancelled
            $table->decimal('price', 12, 2);
            $table->string('billing_cycle');
            $table->unsignedInteger('usage_used')->default(0);
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_addons');
        Schema::dropIfExists('addons');
    }
};
