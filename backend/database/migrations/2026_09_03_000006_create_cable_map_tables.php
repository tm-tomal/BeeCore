<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cable_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('source')->nullable();
            $table->string('destination')->nullable();
            $table->unsignedSmallInteger('fiber_cores')->nullable();
            $table->decimal('length_km', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'name']);
        });

        Schema::create('cable_splitters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cable_route_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('location')->nullable();
            $table->unsignedSmallInteger('port_count')->default(8);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'cable_route_id']);
        });

        Schema::create('splitter_ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cable_splitter_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('port_number');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['cable_splitter_id', 'port_number'], 'splitter_ports_number_unique');
            $table->index(['cable_splitter_id', 'customer_id']);
        });

        Schema::create('cable_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cable_route_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('cable_splitter_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('issue_type', 40)->default('fiber_cut'); // fiber_cut, maintenance, other
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('open'); // open, resolved
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cable_issues');
        Schema::dropIfExists('splitter_ports');
        Schema::dropIfExists('cable_splitters');
        Schema::dropIfExists('cable_routes');
    }
};
