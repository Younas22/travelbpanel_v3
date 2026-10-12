<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The agent create/edit form no longer asks for CNIC / Business Reg No. —
 * replaced with Tax Number. Renaming (not adding a new column) keeps
 * whatever any agent already had on file instead of silently discarding it.
 * Raw SQL rather than Schema::rename, since this app doesn't have
 * doctrine/dbal installed.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE users CHANGE cnic_or_reg_number tax_number VARCHAR(100) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users CHANGE tax_number cnic_or_reg_number VARCHAR(100) NULL');
    }
};
