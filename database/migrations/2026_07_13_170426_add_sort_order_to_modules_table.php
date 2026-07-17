<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('status');
        });

        // Seed initial sort_order from existing row order
        $i = 1;
        DB::table('modules')->orderBy('id')->each(function ($row) use (&$i) {
            DB::table('modules')->where('id', $row->id)->update(['sort_order' => $i++]);
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
