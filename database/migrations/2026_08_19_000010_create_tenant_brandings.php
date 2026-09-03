<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_brandings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->string('brand_name')->nullable();
            $table->string('brand_color', 20)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('app_name')->nullable();
            $table->string('app_icon_path')->nullable();
            $table->string('splash_screen_path')->nullable();
            $table->boolean('login_branding_enabled')->default(true);
            $table->boolean('dashboard_branding_enabled')->default(true);
            $table->boolean('email_branding_enabled')->default(true);
            $table->boolean('sms_branding_enabled')->default(true);
            $table->boolean('customer_app_branding_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_brandings');
    }
};
