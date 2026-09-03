<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cable_routes', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('length_km');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        Schema::table('cable_splitters', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('location');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('cable_routes', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('cable_splitters', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
