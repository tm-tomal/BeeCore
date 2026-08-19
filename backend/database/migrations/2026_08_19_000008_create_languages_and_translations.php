<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // en, bn
            $table->string('name');
            $table->string('native_name')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('locale', 10);
            $table->text('value');
            $table->timestamps();
            $table->unique(['key', 'locale']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->string('language', 10)->default('en')->after('timezone');
        });

        $now = now();
        DB::table('languages')->insert([
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'is_default' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'bn', 'name' => 'Bangla', 'native_name' => 'বাংলা', 'is_default' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('language');
        });

        Schema::dropIfExists('translations');
        Schema::dropIfExists('languages');
    }
};
