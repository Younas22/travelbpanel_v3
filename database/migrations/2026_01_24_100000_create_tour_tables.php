<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_package_type', function (Blueprint $table) {
            $table->id();
            $table->string('packege_type');
            $table->enum('status', ['1', '0'])->default('1');
            $table->timestamps();
        });

        Schema::create('tour_inclusions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('tour_exclusions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('packege_type');
            $table->string('currceny');
            $table->string('price');
            $table->string('duration')->nullable();
            $table->string('loaction')->nullable();
            $table->integer('leaving_from')->nullable();
            $table->integer('going_to')->nullable();
            $table->date('checkin_date')->nullable();
            $table->date('checkout_date')->nullable();
            $table->integer('days')->nullable();
            $table->integer('nights')->nullable();
            $table->string('class')->nullable();
            $table->text('desc')->nullable();
            $table->json('inclusions')->nullable();
            $table->json('exclusions')->nullable();
            $table->text('policy')->nullable();
            $table->enum('featured', ['1', '0'])->default('0');
            $table->enum('status', ['1', '0'])->default('1');
            $table->string('stars')->nullable();
            $table->string('rating')->nullable();
            $table->string('adults')->nullable();
            $table->string('childs')->nullable();
            $table->string('infants')->nullable();
            $table->timestamps();
        });

        Schema::create('tour_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tour_id');
            $table->string('image');
            $table->timestamps();
            $table->foreign('tour_id')->references('id')->on('tours')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_images');
        Schema::dropIfExists('tours');
        Schema::dropIfExists('tour_exclusions');
        Schema::dropIfExists('tour_inclusions');
        Schema::dropIfExists('tour_package_type');
    }
};
