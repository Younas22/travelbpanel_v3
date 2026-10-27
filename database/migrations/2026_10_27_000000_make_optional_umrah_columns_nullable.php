<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * These fields are optional on the Umrah package form (validated as
 * "nullable" in UmrahController) but were NOT NULL in the table, so saving a
 * package with any of them left blank failed with "Column ... cannot be null".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('umrah', function (Blueprint $table) {
            $table->text('policy')->nullable()->change();
            $table->string('stars', 225)->nullable()->change();
            $table->string('rating', 225)->nullable()->change();
            $table->string('adults', 225)->nullable()->change();
            $table->string('childs', 225)->nullable()->change();
            $table->string('infants', 225)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('umrah', function (Blueprint $table) {
            $table->text('policy')->nullable(false)->change();
            $table->string('stars', 225)->nullable(false)->change();
            $table->string('rating', 225)->nullable(false)->change();
            $table->string('adults', 225)->nullable(false)->change();
            $table->string('childs', 225)->nullable(false)->change();
            $table->string('infants', 225)->nullable(false)->change();
        });
    }
};
