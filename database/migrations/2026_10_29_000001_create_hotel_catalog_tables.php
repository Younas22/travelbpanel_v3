<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Admin-managed hotel lists (Hotels menu):
 *   stay_type      — Stay Types (was the settings-based "Hotel types"; hotels.type keeps the name)
 *   room_type      — Room Types master list (names the per-hotel room types pick from)
 *   room_amenity   — Room Amenities (attached to per-hotel room types)
 *   meal_plan      — Meal Plans
 * Stay Amenities stay in all_amenities (hotels/room types already link to it) and gain a status.
 * catalog_translations holds the per-language name/description for all of them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_catalog_items', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 30)->index();
            $table->string('name');                 // default-language name
            $table->string('icon', 100)->nullable();
            $table->boolean('status')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('catalog_translations', function (Blueprint $table) {
            $table->id();
            $table->string('translatable_type');
            $table->unsignedBigInteger('translatable_id');
            $table->string('locale', 10);
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['translatable_type', 'translatable_id', 'locale'], 'catalog_translations_unique');
        });

        Schema::table('all_amenities', function (Blueprint $table) {
            $table->boolean('status')->default(true)->after('icon');
        });

        Schema::create('room_type_room_amenity', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_type_id');
            $table->unsignedBigInteger('room_amenity_id');
            $table->unique(['room_type_id', 'room_amenity_id']);
            $table->foreign('room_type_id')->references('id')->on('room_types')->cascadeOnDelete();
            $table->foreign('room_amenity_id')->references('id')->on('hotel_catalog_items')->cascadeOnDelete();
        });

        // Seed starter data
        $default = DB::table('languages')->where('is_default', 1)->value('code') ?? 'en';
        $savedTypes = json_decode((string) DB::table('settings')->where('key', 'hotel_types')->where('group', 'hotels')->value('value'), true);
        $seed = [
            'stay_type'    => collect(is_array($savedTypes) && $savedTypes ? $savedTypes : ['hotel', 'guest house', 'resort'])->map(fn ($t) => [ucwords($t), null])->all(),
            'room_type'    => [['Standard Room', null], ['Deluxe Room', null], ['Superior Room', null], ['Executive Room', null], ['Family Room', null], ['Suite', null]],
            'room_amenity' => [['Air Conditioning', 'bi bi-snow'], ['Flat-screen TV', 'bi bi-tv'], ['Free Wi-Fi', 'bi bi-wifi'], ['Mini Bar', 'bi bi-cup-straw'], ['In-room Safe', 'bi bi-safe'], ['Hair Dryer', 'bi bi-wind'], ['Tea / Coffee Maker', 'bi bi-cup-hot'], ['Private Bathroom', 'bi bi-droplet']],
            'meal_plan'    => [['Room Only', null], ['Bed & Breakfast', null], ['Half Board', null], ['Full Board', null], ['All Inclusive', null]],
        ];
        foreach ($seed as $kind => $rows) {
            foreach ($rows as $i => [$name, $icon]) {
                $id = DB::table('hotel_catalog_items')->insertGetId(['kind' => $kind, 'name' => $name, 'icon' => $icon, 'status' => 1, 'sort_order' => $i + 1, 'created_at' => now(), 'updated_at' => now()]);
                DB::table('catalog_translations')->insert(['translatable_type' => 'App\\Models\\HotelCatalogItem', 'translatable_id' => $id, 'locale' => $default, 'name' => $name, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
        // existing stay amenities: default-language translation = current name
        foreach (DB::table('all_amenities')->get(['id', 'name']) as $a) {
            DB::table('catalog_translations')->insert(['translatable_type' => 'App\\Models\\AllAmenity', 'translatable_id' => $a->id, 'locale' => $default, 'name' => $a->name, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('room_type_room_amenity');
        Schema::table('all_amenities', fn (Blueprint $table) => $table->dropColumn('status'));
        Schema::dropIfExists('catalog_translations');
        Schema::dropIfExists('hotel_catalog_items');
    }
};
