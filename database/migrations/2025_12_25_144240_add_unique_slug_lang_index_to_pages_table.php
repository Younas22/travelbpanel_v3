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
        Schema::table('pages', function (Blueprint $table) {
            // First, drop the old unique constraint on slug column
            $table->dropUnique('pages_slug_unique');

            // Then add unique composite index for slug + lang combination
            $table->unique(['slug', 'lang'], 'pages_slug_lang_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            // Drop the composite unique index
            $table->dropUnique('pages_slug_lang_unique');

            // Restore the old unique constraint on slug column
            $table->unique('slug', 'pages_slug_unique');
        });
    }
};
