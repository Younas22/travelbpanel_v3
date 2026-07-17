<?php
// database/migrations/xxxx_create_travel_partners_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_partners', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('api_type')->nullable(); // Flight Search API, Hotel Booking API, etc.
            $table->enum('partner_tier', ['standard', 'premium', 'enterprise'])->default('standard');
            $table->enum('status', ['active', 'pending', 'suspended', 'inactive'])->default('pending');
            $table->decimal('commission_rate', 5, 2)->default(0); // 12.50 means 12.50%
            $table->decimal('monthly_revenue', 12, 2)->default(0);
            $table->date('integration_date')->nullable();
            $table->date('contract_end_date')->nullable();
            
            // API Credentials (6 parameters as shown in modal)
            $table->text('api_credential_1')->nullable(); // API Key/ID
            $table->text('api_credential_2')->nullable(); // Secret Key/Password
            $table->text('api_credential_3')->nullable(); // Access Token
            $table->text('api_credential_4')->nullable(); // Endpoint URL
            $table->text('api_credential_5')->nullable(); // Webhook Secret
            $table->text('api_credential_6')->nullable(); // Additional Parameter
            
            // Configuration Options
            $table->boolean('development_mode')->default(false);
            $table->boolean('currency_support')->default(false);
            $table->boolean('payment_integration')->default(false);
            $table->boolean('custom_pnr_format')->default(false);
            
            // Performance Metrics
            $table->decimal('api_uptime', 5, 2)->default(0); // API uptime percentage
            $table->integer('total_bookings')->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('revenue_growth', 5, 2)->default(0); // Monthly growth percentage
            
            // Admin Notes
            $table->text('admin_notes')->nullable();
            $table->text('contract_details')->nullable();
            
            // Additional Info
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_person')->nullable();
            $table->json('supported_currencies')->nullable(); // Array of currency codes
            $table->json('supported_countries')->nullable(); // Array of country codes
            
            // Tracking
            $table->timestamp('last_api_call')->nullable();
            $table->timestamp('last_revenue_update')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('company_name');
            $table->index('status');
            $table->index('partner_tier');
            $table->index('integration_date');
            $table->index('contract_end_date');
            $table->index(['status', 'partner_tier']);
            
            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_partners');
    }
};