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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // en, nl, ar, ur etc
            $table->string('name', 100); // English, Dutch, Arabic, Urdu
            $table->string('native_name', 100)->nullable(); // Native language name
            $table->boolean('status')->default(1); // 1 = Active, 0 = Inactive
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Insert default languages
        DB::table('languages')->insert([
            [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'status' => 1,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'nl',
                'name' => 'Dutch',
                'native_name' => 'Nederlands',
                'status' => 1,
                'sort_order' => 2,
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
        Schema::dropIfExists('languages');
    }
};
