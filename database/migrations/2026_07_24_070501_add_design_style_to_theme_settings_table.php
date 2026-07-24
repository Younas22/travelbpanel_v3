<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "classic" loads admin.css (the existing design); "modern" loads
     * admin-modern.css (the new premium design) — independent of the
     * color/font/radius/layout fields, which apply to either one.
     */
    public function up(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->string('design_style', 20)->default('classic')->after('theme_name');
        });
    }

    public function down(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->dropColumn('design_style');
        });
    }
};
