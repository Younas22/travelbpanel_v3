<?php
// database/migrations/xxxx_create_blog_categories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#667eea'); // Hex color code
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('posts_count')->default(0);
            $table->integer('views_count')->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('parent_id');
            $table->index('is_active');
            $table->index('sort_order');
            
            // Foreign key
            $table->foreign('parent_id')->references('id')->on('blog_categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_categories');
    }
};