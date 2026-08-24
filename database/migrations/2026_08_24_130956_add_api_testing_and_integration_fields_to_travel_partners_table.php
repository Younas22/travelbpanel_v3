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
            $table->string('db_host')->nullable()->after('api_credential_6');
            $table->string('db_port')->nullable()->after('db_host');
            $table->string('db_database')->nullable()->after('db_port');
            $table->string('db_username')->nullable()->after('db_database');
            $table->text('db_password')->nullable()->after('db_username');

            $table->string('content_import_type')->nullable()->after('db_password');
            $table->string('content_import_format')->nullable()->after('content_import_type');
            $table->string('content_import_file')->nullable()->after('content_import_format');
            $table->string('content_import_status')->nullable()->after('content_import_file');
            $table->timestamp('content_import_at')->nullable()->after('content_import_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_partners', function (Blueprint $table) {
            $table->dropColumn([
                'db_host', 'db_port', 'db_database', 'db_username', 'db_password',
                'content_import_type', 'content_import_format', 'content_import_file',
                'content_import_status', 'content_import_at',
            ]);
        });
    }
};
