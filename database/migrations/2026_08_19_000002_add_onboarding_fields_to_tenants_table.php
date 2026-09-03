<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('company_legal_name')->nullable()->after('name');
            $table->string('business_type')->nullable()->after('company_legal_name');
            $table->string('owner_name')->nullable()->after('business_type');
            $table->string('owner_email')->nullable()->after('owner_name');
            $table->string('owner_phone')->nullable()->after('owner_email');
            $table->string('contact_phone')->nullable()->after('owner_phone');
            $table->text('contact_address')->nullable()->after('contact_phone');
            $table->string('subdomain')->nullable()->unique()->after('contact_address');
            $table->string('custom_domain')->nullable()->unique()->after('subdomain');
            $table->string('onboarding_status')->default('pending')->after('custom_domain')->index();
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_status');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'company_legal_name',
                'business_type',
                'owner_name',
                'owner_email',
                'owner_phone',
                'contact_phone',
                'contact_address',
                'subdomain',
                'custom_domain',
                'onboarding_status',
                'onboarding_completed_at',
            ]);
        });
    }
};
