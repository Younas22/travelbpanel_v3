<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menus are managed in one place (admin → Menus): each item keeps its own
     * name per language ({"en": "About", "ar": "من نحن"}). Copies the names the
     * items had through resources/lang/{lang}/header.json / footer.json, and
     * turns the old header "Pages" dropdown (pages ticked "Header", named in
     * setting menu.header_pages_label) into a normal parent menu item.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('menu_items', 'translations')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->json('translations')->nullable()->after('name');
            });
        }

        $langs = [];
        foreach (glob(resource_path('lang/*'), GLOB_ONLYDIR) as $dir) {
            foreach (['header', 'footer'] as $group) {
                $file = "{$dir}/{$group}.json";
                $langs[basename($dir)][$group] = is_file($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];
            }
        }

        foreach (DB::table('menu_items')->get() as $item) {
            $group = $item->category === 'header' ? 'header' : 'footer';
            $translations = json_decode($item->translations ?? '', true) ?: [];
            foreach ($langs as $code => $groups) {
                $value = $groups[$group][$item->name] ?? null;
                if (filled($value) && empty($translations[$code])) {
                    $translations[$code] = $value;
                }
            }
            DB::table('menu_items')->where('id', $item->id)->update([
                'translations' => $translations ? json_encode($translations, JSON_UNESCAPED_UNICODE) : null,
            ]);
        }

        // header page links without a parent used to be grouped under one dropdown
        $loose = DB::table('menu_items')->where('category', 'header')
            ->whereNotNull('page_id')->whereNull('parent_id')->pluck('id');
        $setting = DB::table('settings')->where('group', 'menu')->where('key', 'header_pages_label')->first();

        if ($loose->isNotEmpty()) {
            $labels = array_filter(json_decode($setting->value ?? '', true) ?: [], 'filled');
            $parentId = DB::table('menu_items')->insertGetId([
                'name' => $labels['en'] ?? 'Pages',
                'translations' => $labels ? json_encode($labels, JSON_UNESCAPED_UNICODE) : null,
                'url' => null,
                'category' => 'header',
                'sort_order' => (DB::table('menu_items')->where('category', 'header')->whereNull('parent_id')->max('sort_order') ?? 0) + 1,
                'is_active' => true,
                'target' => '_self',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('menu_items')->whereIn('id', $loose)->update(['parent_id' => $parentId]);
        }

        if ($setting) {
            DB::table('settings')->where('id', $setting->id)->delete();
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('menu_items', 'translations')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->dropColumn('translations');
            });
        }
    }
};
