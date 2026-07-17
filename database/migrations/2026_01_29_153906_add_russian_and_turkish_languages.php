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
        DB::table('languages')->insert([
            [
                'code' => 'ru',
                'name' => 'Russian',
                'native_name' => 'Русский',
                'status' => 1,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'tr',
                'name' => 'Turkish',
                'native_name' => 'Türkçe',
                'status' => 1,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('languages')->whereIn('code', ['ru', 'tr'])->delete();
    }
};
