<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hero image of a page on the website, a path under public/assets/images
     * (e.g. "pages/about-1730800000.jpg"). Empty = the homepage cover image.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('pages', 'header_image')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->string('header_image')->nullable()->after('translations');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pages', 'header_image')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->dropColumn('header_image');
            });
        }
    }
};
