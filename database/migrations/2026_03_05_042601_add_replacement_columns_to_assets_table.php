<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->unsignedSmallInteger('expected_lifespan_years')->nullable()->after('install_date');
            $table->boolean('replacement_alerts_enabled')->default(true)->after('expected_lifespan_years');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['expected_lifespan_years', 'replacement_alerts_enabled']);
        });
    }
};
