<?php
// database/migrations/xxxx_create_blog_comments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('blog_post_id');
            $table->unsignedBigInteger('user_id')->nullable(); // Nullable for guest comments
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->text('content');
            $table->enum('status', ['pending', 'approved', 'rejected', 'spam'])->default('pending');
            $table->unsignedBigInteger('parent_id')->nullable(); // For replies
            $table->integer('likes_count')->default(0);
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('blog_post_id');
            $table->index('user_id');
            $table->index('parent_id');
            $table->index('status');
            $table->index(['blog_post_id', 'status']);
            
            // Foreign keys
            $table->foreign('blog_post_id')->references('id')->on('blog_posts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('blog_comments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_comments');
    }
};