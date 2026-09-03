<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('networks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('ip_address')->nullable();
            $table->string('device_type')->default('mikrotik');
            $table->string('location')->nullable();
            $table->enum('status', ['online', 'offline', 'maintenance'])->default('online');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('networks'); }
};
