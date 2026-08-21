<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('type')->default('general')->index(); // general, maintenance, feature, payment, system
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete(); // null = global
            $table->string('status')->default('draft')->index(); // draft, scheduled, published
            $table->boolean('dashboard_channel')->default(true);
            $table->boolean('email_channel')->default(false);
            $table->boolean('sms_channel')->default(false);
            $table->boolean('push_channel')->default(false);
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
