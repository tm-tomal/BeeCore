<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('current_version')->default('1.0.0');
            $table->string('minimum_supported_version')->default('1.0.0');
            $table->boolean('force_update_enabled')->default(false);
            $table->boolean('maintenance_mode_enabled')->default(false);
            $table->text('maintenance_message')->nullable();
            $table->boolean('push_notifications_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('customer_app_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->index(); // session_start, crash, active_user
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        DB::table('customer_app_settings')->insert([
            'current_version' => '1.0.0',
            'minimum_supported_version' => '1.0.0',
            'force_update_enabled' => false,
            'maintenance_mode_enabled' => false,
            'push_notifications_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_app_events');
        Schema::dropIfExists('customer_app_settings');
    }
};
