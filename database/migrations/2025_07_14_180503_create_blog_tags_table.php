<?php
// database/migrations/xxxx_create_blog_tags_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('posts_count')->default(0);
            $table->string('color', 7)->default('#6c757d');
            $table->timestamps();
            
            // Indexes
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_tags');
    }
};