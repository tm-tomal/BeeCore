<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DEFAULTS = [
        'platform_name' => 'BeeCore',
        'platform_logo_path' => '',
        'platform_favicon_path' => '',
        'default_language' => 'en',
        'default_currency' => 'BDT',
        'default_timezone' => 'Asia/Dhaka',
        'date_format' => 'd M Y',
        'time_format' => 'H:i',
        'invoice_prefix' => 'INV',
        'invoice_due_days' => '7',
        'file_upload_max_mb' => '10',
        'allowed_file_types' => 'jpg,jpeg,png,pdf',
        'api_rate_limit_per_minute' => '60',
        'session_lifetime_minutes' => '120',
        'password_min_length' => '8',
        'storage_disk' => 'public',
    ];

    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        $now = now();
        DB::table('system_settings')->insert(array_map(fn ($key, $value) => [
            'key' => $key,
            'value' => $value,
            'created_at' => $now,
            'updated_at' => $now,
        ], array_keys(self::DEFAULTS), self::DEFAULTS));
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
