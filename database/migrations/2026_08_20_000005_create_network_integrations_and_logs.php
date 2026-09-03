<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('network_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->index(); // mikrotik, radius, olt, custom_api
            $table->string('host')->nullable();
            $table->string('version')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('health_status')->default('unknown')->index(); // online, offline, degraded, unknown
            $table->timestamp('last_checked_at')->nullable();
            $table->text('credentials')->nullable(); // encrypted JSON key/value pairs
            $table->timestamps();
        });

        Schema::create('network_integration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_integration_id')->constrained()->cascadeOnDelete();
            $table->string('direction')->index(); // request, response, failure
            $table->text('message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('network_integration_logs');
        Schema::dropIfExists('network_integrations');
    }
};
