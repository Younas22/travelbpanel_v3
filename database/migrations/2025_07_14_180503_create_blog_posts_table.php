<?php
// database/migrations/xxxx_create_blog_posts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->text('excerpt')->nullable();
            $table->string('featured_image')->nullable();
            $table->unsignedBigInteger('author_id');
            $table->unsignedBigInteger('category_id');
            $table->enum('status', ['draft', 'published', 'scheduled', 'reviewing', 'archived'])->default('draft');
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->string('seo_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->integer('reading_time')->default(0); // in minutes
            $table->integer('seo_score')->default(0); // 0-100
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('allow_comments')->default(true);
            $table->json('social_shares')->nullable(); // Track social media shares
            $table->timestamps();
            
            // Indexes
            $table->index('author_id');
            $table->index('category_id');
            $table->index('status');
            $table->index('published_at');
            $table->index('is_featured');
            $table->index(['status', 'published_at']);
            
            // Foreign keys
            $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('blog_categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};