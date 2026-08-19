<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('host');
            $table->string('status')->default('offline')->index(); // online, offline, degraded
            $table->unsignedInteger('storage_capacity_gb')->default(0);
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('tenant_media_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->unsignedInteger('storage_allocated_gb')->default(0);
            $table->unsignedInteger('storage_used_gb')->default(0);
            $table->unsignedInteger('streaming_used_gb')->default(0);
            $table->unsignedInteger('bandwidth_used_gb')->default(0);
            $table->text('content_policy')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_media_settings');
        Schema::dropIfExists('media_servers');
    }
};
