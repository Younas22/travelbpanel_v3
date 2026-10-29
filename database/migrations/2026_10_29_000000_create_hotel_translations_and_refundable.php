<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-language hotel content (name, address, description, cancellation and
 * privacy policy) for every language in the `languages` table, plus a
 * "refundable booking" flag on the hotel itself. The hotels table keeps its
 * own name/address/description as the default-language values, so existing
 * pages that read them keep working.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotel_id');
            $table->string('locale', 10);
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->text('description')->nullable();
            $table->text('cancellation_policy')->nullable();
            $table->text('privacy_policy')->nullable();
            $table->timestamps();

            $table->unique(['hotel_id', 'locale']);
            $table->foreign('hotel_id')->references('id')->on('hotels')->cascadeOnDelete();
        });

        Schema::table('hotels', function (Blueprint $table) {
            $table->boolean('refundable')->default(false)->after('featured');
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('refundable');
        });
        Schema::dropIfExists('hotel_translations');
    }
};
