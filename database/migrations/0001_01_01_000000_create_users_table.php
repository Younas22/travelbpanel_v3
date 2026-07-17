<?php
// database/migrations/2025_01_01_000000_create_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // User Type & Role
            $table->enum('user_type', ['user', 'admin', 'agent'])->default('user');
            
            // Personal Information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->nullable();
            $table->string('profile_image')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer-not-to-say'])->nullable();
            
            // Account Settings
            $table->enum('customer_tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
            $table->enum('status', ['active', 'inactive', 'suspended', 'vip'])->default('active');
            $table->string('password');
            
            // Address Information
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->nullable();
            
            // Booking & Financial Stats (Only for users)
            $table->integer('total_bookings')->default(0);
            $table->decimal('total_spent', 10, 2)->default(0.00);
            $table->decimal('average_spending', 8, 2)->default(0.00);
            $table->timestamp('last_activity')->nullable();
            
            // Agent Specific Fields
            $table->string('employee_id')->nullable(); // For admin/agent
            $table->date('hire_date')->nullable(); // For admin/agent
            $table->string('department')->nullable(); // For admin/agent
            $table->decimal('commission_rate', 5, 2)->nullable(); // For agent
            $table->integer('total_sales')->default(0); // For agent
            $table->decimal('total_commission', 10, 2)->default(0.00); // For agent
            
            // Notification Preferences
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            $table->boolean('marketing_emails')->default(false);
            
            // Internal Notes
            $table->text('internal_notes')->nullable();
            
            // Laravel Defaults
            $table->rememberToken();
            $table->timestamps();
            
            // Indexes
            $table->index(['user_type', 'status']);
            $table->index(['status', 'customer_tier']);
            $table->index('last_activity');
            $table->index('total_spent');
            $table->index('employee_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};