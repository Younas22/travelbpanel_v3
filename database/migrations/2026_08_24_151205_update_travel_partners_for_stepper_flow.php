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
        Schema::table('travel_partners', function (Blueprint $table) {
            // Superseded by the travel_partner_imports table (multiple imports with view/delete)
            $table->dropColumn([
                'content_import_type', 'content_import_format', 'content_import_file',
                'content_import_status', 'content_import_at',
            ]);

            $table->string('last_api_test_status')->nullable()->after('db_password');
            $table->string('last_api_test_message')->nullable()->after('last_api_test_status');
            $table->timestamp('last_api_test_at')->nullable()->after('last_api_test_message');

            $table->string('last_db_test_status')->nullable()->after('last_api_test_at');
            $table->string('last_db_test_message')->nullable()->after('last_db_test_status');
            $table->timestamp('last_db_test_at')->nullable()->after('last_db_test_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_partners', function (Blueprint $table) {
            $table->dropColumn([
                'last_api_test_status', 'last_api_test_message', 'last_api_test_at',
                'last_db_test_status', 'last_db_test_message', 'last_db_test_at',
            ]);

            $table->string('content_import_type')->nullable();
            $table->string('content_import_format')->nullable();
            $table->string('content_import_file')->nullable();
            $table->string('content_import_status')->nullable();
            $table->timestamp('content_import_at')->nullable();
        });
    }
};
