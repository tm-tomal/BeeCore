<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reporter_name');
            $table->string('reporter_phone')->nullable();
            $table->string('subject');
            $table->string('category')->default('connection')->index(); // connection, network, service, billing, other
            $table->string('priority')->default('medium')->index(); // low, medium, high, urgent
            $table->string('status')->default('new')->index(); // new, in_progress, resolved, closed
            $table->string('source')->default('staff')->index(); // staff, public
            $table->text('description')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
