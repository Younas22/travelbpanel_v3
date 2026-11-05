<?php
// app/Http/Controllers/Admin/PageManagerController.php

namespace App\Http\Controllers\Admin;

use App\Models\Language;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Page & Menu Manager: menu structure, page content and page translations in
 * one screen. Pages are saved with PageController's own rules/routine
 * (inherited), menu items with MenuController::menuRules() and
 * MenuItem::linkAndNameAttributes(), so there is one set of business logic.
 */
class PageManagerController extends PageController
{
    public function index(Request $request)
    {
        $categories = MenuItem::getCategoryOptions();
        $items = MenuItem::with('page')->orderBy('sort_order')->get();
        $topItems = $items->whereNull('parent_id')->groupBy('category');
        $childItems = $items->whereNotNull('parent_id')->groupBy('parent_id');
        $orphanPages = Page::doesntHave('menuItems')->orderBy('name')->get();
        $allPages = Page::orderBy('name')->get(['id', 'name', 'slug', 'status']);
        $languages = $this->activeLanguages();

        $menu = $request->filled('menu') ? $items->firstWhere('id', (int) $request->menu) : null;
        $page = $menu?->page
            ?? ($request->filled('page') ? Page::find($request->page) : null);
        $page?->load('menuItems');
        $creating = $request->boolean('new');

        // translation status per language: complete (title + content), partial, missing
        $translationStatus = [];
        foreach ($languages as $language) {
            $title = $page?->translation('title', $language->code, false);
            $content = trim(strip_tags((string) $page?->translation('content', $language->code, false)));
            $translationStatus[$language->code] = $title && $content ? 'complete' : (($title || $content) ? 'partial' : 'missing');
        }

        return view('admin.page-manager.index', compact(
            'categories', 'topItems', 'childItems', 'orphanPages', 'allPages', 'languages',
            'menu', 'page', 'creating', 'translationStatus'
        ));
    }

    /**
     * New menu item. "New page" (the default) creates its blank page in the
     * same transaction and opens it in the editor; an existing page, a custom
     * URL or a dropdown create no page.
     */
    public function storeMenu(Request $request)
    {
        $rules = MenuController::menuRules();
        $rules['link_type'] = 'required|in:new_page,page,url,dropdown';
        $rules['slug'] = ['nullable', 'string', 'max:255', Rule::notIn(self::RESERVED_SLUGS)];
        $validated = $request->validate($rules);
        $this->checkParent($validated);

        $menu = DB::transaction(function () use ($validated) {
            if ($validated['link_type'] === 'new_page') {
                $default = Language::getDefault()?->code ?? 'en';
                $page = Page::create([
                    'name' => $validated['name'],
                    'slug' => $this->uniqueSlug($validated['slug'] ?: $validated['name']),
                    'status' => 'draft',
                    'sort_order' => (Page::max('sort_order') ?? 0) + 1,
                    'translations' => [$default => ['title' => $validated['name']]],
                ]);
                $validated['link_type'] = 'page';
                $validated['page_id'] = $page->id;
            }
            unset($validated['slug']);

            $validated['sort_order'] = (MenuItem::where('category', $validated['category'])
                ->where('parent_id', $validated['parent_id'] ?? null)->max('sort_order') ?? 0) + 1;

            return MenuItem::create(MenuItem::linkAndNameAttributes($validated));
        });

        $message = $menu->page_id && $request->input('link_type') === 'new_page'
            ? 'Menu item and its blank page created. Add the content below, then publish.'
            : 'Menu item created.';

        return redirect()->route('admin.page-manager.index', ['menu' => $menu->id])->with('success', $message);
    }

    /** Menu settings of an item; its page is never created or deleted here. */
    public function updateMenu(Request $request, MenuItem $menu)
    {
        $validated = $request->validate(MenuController::menuRules());
        $this->checkParent($validated, $menu);

        $menu->update(MenuItem::linkAndNameAttributes($validated, $menu));

        return redirect()->route('admin.page-manager.index', ['menu' => $menu->id])->with('success', 'Menu settings saved.');
    }

    /** Show / hide an item on the website. */
    public function toggleMenu(MenuItem $menu)
    {
        $menu->update(['is_active' => !$menu->is_active]);

        return redirect()->route('admin.page-manager.index', ['menu' => $menu->id])
            ->with('success', $menu->is_active ? 'Menu item is visible on the website.' : 'Menu item is hidden on the website.');
    }

    /**
     * Removes the menu item (and its sub-items, as the menu table cascades).
     * Linked pages stay: they move to "Pages not in a menu".
     */
    public function destroyMenu(MenuItem $menu)
    {
        $pageId = $menu->page_id;
        $menu->delete();

        return redirect()->route('admin.page-manager.index', array_filter(['page' => $pageId]))
            ->with('success', 'Menu item deleted. Its page was kept.');
    }

    /**
     * Page content and translations. "Save this language only" (save_lang)
     * stores that language's fields and leaves every other language as saved.
     */
    public function updatePage(Request $request, Page $page)
    {
        $rules = $this->pageRules();
        $rules['save_lang'] = ['nullable', Rule::in(Language::getActiveCodes())];
        $rules['menu'] = 'nullable|integer';
        $validated = $request->validate($rules);

        if ($lang = $validated['save_lang'] ?? null) {
            $saved = Arr::only($page->translations ?? [], Language::getActiveCodes());
            $saved[$lang] = $validated['translations'][$lang] ?? [];
            $validated['translations'] = $saved;
        }
        $menuId = $validated['menu'] ?? null;
        unset($validated['save_lang'], $validated['menu']);

        $this->savePage($request, $validated, $page);

        $language = $lang ? Language::where('code', $lang)->value('name') : null;

        return redirect()->route('admin.page-manager.index', $menuId ? ['menu' => $menuId] : ['page' => $page->id])
            ->with('success', $language ? "{$language} saved." : 'Page saved.');
    }

    /**
     * Dropdowns are one level deep (that is what the website menus show):
     * the parent must be a top-level item of the same location, not the item itself.
     */
    private function checkParent(array $validated, ?MenuItem $menu = null): void
    {
        if (empty($validated['parent_id'])) {
            return;
        }
        $parent = MenuItem::find($validated['parent_id']);
        $invalid = !$parent || $parent->parent_id || $parent->category !== $validated['category']
            || ($menu && ($parent->id === $menu->id || $menu->children()->exists()));

        if ($invalid) {
            throw ValidationException::withMessages([
                'parent_id' => 'Choose a top-level item of the same menu location (an item with sub-items cannot be moved under another).',
            ]);
        }
    }
}
