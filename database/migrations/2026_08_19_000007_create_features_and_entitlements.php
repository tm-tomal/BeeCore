<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const CATALOG = [
        ['key' => 'billing', 'name' => 'Billing module'],
        ['key' => 'customer_management', 'name' => 'Customer management'],
        ['key' => 'reseller_management', 'name' => 'Reseller management'],
        ['key' => 'network_automation', 'name' => 'Network automation'],
        ['key' => 'mikrotik_integration', 'name' => 'MikroTik integration'],
        ['key' => 'radius_integration', 'name' => 'RADIUS integration'],
        ['key' => 'olt_integration', 'name' => 'OLT integration'],
        ['key' => 'payment_gateway', 'name' => 'Payment gateway'],
        ['key' => 'sms', 'name' => 'SMS'],
        ['key' => 'email', 'name' => 'Email'],
        ['key' => 'push_notification', 'name' => 'Push notification'],
        ['key' => 'customer_app', 'name' => 'Customer app'],
        ['key' => 'support_ticket', 'name' => 'Support / ticket'],
        ['key' => 'media_server', 'name' => 'Media / movie server'],
        ['key' => 'advanced_reports', 'name' => 'Advanced reports'],
        ['key' => 'api', 'name' => 'API'],
        ['key' => 'white_label', 'name' => 'White label'],
        ['key' => 'custom_domain', 'name' => 'Custom domain'],
        ['key' => 'multi_language', 'name' => 'Multi-language'],
        ['key' => 'multi_currency', 'name' => 'Multi-currency'],
        ['key' => 'ai_features', 'name' => 'AI features'],
        ['key' => 'whatsapp_integration', 'name' => 'WhatsApp integration'],
    ];

    public function up(): void
    {
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_globally_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saas_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->unique(['saas_plan_id', 'feature_id']);
        });

        Schema::create('tenant_feature_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->unique(['tenant_id', 'feature_id']);
        });

        $now = now();
        DB::table('features')->insert(array_map(fn ($feature) => [
            'key' => $feature['key'],
            'name' => $feature['name'],
            'is_globally_enabled' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], self::CATALOG));
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_feature_overrides');
        Schema::dropIfExists('plan_features');
        Schema::dropIfExists('features');
    }
};
