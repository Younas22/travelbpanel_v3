<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-language page content for every active language:
     * {"en": {"title": "", "content": "", "meta_title": "", ...}, "ar": {...}}.
     * Existing en_/nl_ column data is copied in.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('pages', 'translations')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->json('translations')->nullable()->after('slug');
            });
        }

        $fields = ['content', 'meta_title', 'meta_description', 'meta_keywords'];

        foreach (DB::table('pages')->get() as $page) {
            $translations = json_decode($page->translations ?? '', true) ?: [];

            foreach (['en', 'nl'] as $code) {
                foreach ($fields as $field) {
                    $value = $page->{"{$code}_{$field}"} ?? null;
                    if (filled($value) && empty($translations[$code][$field])) {
                        $translations[$code][$field] = $value;
                    }
                }
            }

            DB::table('pages')->where('id', $page->id)->update([
                'translations' => $translations ? json_encode($translations, JSON_UNESCAPED_UNICODE) : null,
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pages', 'translations')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->dropColumn('translations');
            });
        }
    }
};
