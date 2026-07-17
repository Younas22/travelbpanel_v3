<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if columns already exist, if not add them
        if (!Schema::hasColumn('pages', 'en_content')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->longText('en_content')->nullable()->after('slug');
                $table->longText('nl_content')->nullable()->after('en_content');
                $table->string('en_meta_title')->nullable()->after('nl_content');
                $table->string('nl_meta_title')->nullable()->after('en_meta_title');
                $table->text('en_meta_description')->nullable()->after('nl_meta_title');
                $table->text('nl_meta_description')->nullable()->after('en_meta_description');
                $table->text('en_meta_keywords')->nullable()->after('nl_meta_description');
                $table->text('nl_meta_keywords')->nullable()->after('en_meta_keywords');
            });
        }

        // Migrate existing data based on language (only if old columns exist)
        if (Schema::hasColumn('pages', 'lang') && Schema::hasColumn('pages', 'content')) {
            DB::statement("UPDATE pages SET
                en_content = CASE WHEN lang = 'en' THEN content END,
                nl_content = CASE WHEN lang = 'nl' THEN content END,
                en_meta_title = CASE WHEN lang = 'en' THEN meta_title END,
                nl_meta_title = CASE WHEN lang = 'nl' THEN meta_title END,
                en_meta_description = CASE WHEN lang = 'en' THEN meta_description END,
                nl_meta_description = CASE WHEN lang = 'nl' THEN meta_description END,
                en_meta_keywords = CASE WHEN lang = 'en' THEN meta_keywords END,
                nl_meta_keywords = CASE WHEN lang = 'nl' THEN meta_keywords END
            ");

            // Merge duplicate slugs (group by slug and merge language data)
            $pages = DB::table('pages')
                ->select('slug', DB::raw('MIN(id) as keep_id'))
                ->groupBy('slug')
                ->get();

            foreach ($pages as $page) {
                $records = DB::table('pages')->where('slug', $page->slug)->get();

                $enData = $records->firstWhere('lang', 'en');
                $nlData = $records->firstWhere('lang', 'nl');

                // Merge all data into the first record
                DB::table('pages')->where('id', $page->keep_id)->update([
                    'en_content' => $enData->en_content ?? $enData->content ?? null,
                    'nl_content' => $nlData->nl_content ?? $nlData->content ?? null,
                    'en_meta_title' => $enData->en_meta_title ?? $enData->meta_title ?? null,
                    'nl_meta_title' => $nlData->nl_meta_title ?? $nlData->meta_title ?? null,
                    'en_meta_description' => $enData->en_meta_description ?? $enData->meta_description ?? null,
                    'nl_meta_description' => $nlData->nl_meta_description ?? $nlData->meta_description ?? null,
                    'en_meta_keywords' => $enData->en_meta_keywords ?? $enData->meta_keywords ?? null,
                    'nl_meta_keywords' => $nlData->nl_meta_keywords ?? $nlData->meta_keywords ?? null,
                ]);

                // Delete duplicate records
                DB::table('pages')
                    ->where('slug', $page->slug)
                    ->where('id', '!=', $page->keep_id)
                    ->delete();
            }
        }

        // First drop the lang index if it exists
        if (Schema::hasColumn('pages', 'lang')) {
            try {
                Schema::table('pages', function (Blueprint $table) {
                    $table->dropIndex(['lang']);
                });
            } catch (\Exception $e) {
                // Index may not exist, continue
            }
        }

        // Now drop old columns if they exist
        $columnsToDrop = [];
        foreach (['content', 'meta_title', 'meta_description', 'meta_keywords', 'lang'] as $column) {
            if (Schema::hasColumn('pages', $column)) {
                $columnsToDrop[] = $column;
            }
        }

        if (!empty($columnsToDrop)) {
            Schema::table('pages', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }

        // Make slug unique (since lang is removed, slug should be unique now)
        Schema::table('pages', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the old columns
        Schema::table('pages', function (Blueprint $table) {
            $table->string('lang', 5)->default('en')->after('slug');
            $table->longText('content')->nullable()->after('lang');
            $table->string('meta_title')->nullable()->after('content');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->text('meta_keywords')->nullable()->after('meta_description');

            $table->dropUnique(['slug']);
            $table->index('lang');
        });

        // Restore data from language-specific columns (this will create duplicate records)
        $pages = DB::table('pages')->get();

        foreach ($pages as $page) {
            // Keep the English version in the current record
            DB::table('pages')->where('id', $page->id)->update([
                'content' => $page->en_content,
                'meta_title' => $page->en_meta_title,
                'meta_description' => $page->en_meta_description,
                'meta_keywords' => $page->en_meta_keywords,
                'lang' => 'en',
            ]);

            // Create a new record for Dutch if data exists
            if ($page->nl_content || $page->nl_meta_title) {
                DB::table('pages')->insert([
                    'name' => $page->name,
                    'slug' => $page->slug,
                    'lang' => 'nl',
                    'status' => $page->status,
                    'content' => $page->nl_content,
                    'meta_title' => $page->nl_meta_title,
                    'meta_description' => $page->nl_meta_description,
                    'meta_keywords' => $page->nl_meta_keywords,
                    'sort_order' => $page->sort_order,
                    'is_homepage' => $page->is_homepage,
                    'show_in_menu' => $page->show_in_menu,
                    'published_at' => $page->published_at,
                    'created_at' => $page->created_at,
                    'updated_at' => now(),
                ]);
            }
        }

        // Drop the language-specific columns
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn([
                'en_content', 'nl_content',
                'en_meta_title', 'nl_meta_title',
                'en_meta_description', 'nl_meta_description',
                'en_meta_keywords', 'nl_meta_keywords'
            ]);
        });
    }
};
