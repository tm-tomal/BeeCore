<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const EVENTS = [
        ['key' => 'welcome_message', 'name' => 'Welcome message'],
        ['key' => 'invoice_generated', 'name' => 'Invoice generated'],
        ['key' => 'payment_reminder', 'name' => 'Payment reminder'],
        ['key' => 'due_date_reminder', 'name' => 'Due date reminder'],
        ['key' => 'suspension_warning', 'name' => 'Suspension warning'],
        ['key' => 'suspension_confirmation', 'name' => 'Suspension confirmation'],
        ['key' => 'payment_confirmation', 'name' => 'Payment confirmation'],
        ['key' => 'reactivation_confirmation', 'name' => 'Reactivation confirmation'],
        ['key' => 'trial_expiry', 'name' => 'Trial expiry'],
        ['key' => 'subscription_expiry', 'name' => 'Subscription expiry'],
        ['key' => 'system_maintenance', 'name' => 'System maintenance'],
    ];

    public function up(): void
    {
        Schema::create('notification_events', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_enabled')->default(false);
            $table->boolean('push_enabled')->default(false);
            $table->timestamps();
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_key');
            $table->string('channel')->index(); // email, sms, push
            $table->string('recipient')->nullable();
            $table->string('status')->default('sent')->index(); // sent, failed
            $table->timestamp('created_at')->useCurrent();
        });

        $now = now();
        DB::table('notification_events')->insert(array_map(fn ($event) => [
            'key' => $event['key'],
            'name' => $event['name'],
            'is_active' => true,
            'email_enabled' => true,
            'sms_enabled' => false,
            'push_enabled' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ], self::EVENTS));
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_events');
    }
};
