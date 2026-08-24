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
        Schema::create('travel_partner_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_partner_id')->constrained()->cascadeOnDelete();
            $table->string('import_type');
            $table->string('file_format');
            $table->string('original_filename');
            $table->string('stored_path');
            $table->string('status'); // imported | failed
            $table->unsignedInteger('records_count')->nullable();
            $table->json('preview_data')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_partner_imports');
    }
};
