<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menu items made from admin → Pages ("Show in menu") point at their page:
     * the label comes from the page's title per language and the item goes
     * away with the page. Existing items for /page/{slug} are linked too.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('menu_items', 'page_id')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->foreignId('page_id')->nullable()->after('parent_id')
                      ->constrained('pages')->cascadeOnDelete();
            });
        }

        foreach (DB::table('pages')->get(['id', 'slug']) as $page) {
            DB::table('menu_items')
                ->whereNull('page_id')
                ->whereIn('url', ['/page/' . $page->slug, 'page/' . $page->slug])
                ->update(['page_id' => $page->id]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('menu_items', 'page_id')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('page_id');
            });
        }
    }
};
