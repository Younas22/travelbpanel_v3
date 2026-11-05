<?php
// app/Http/Controllers/Admin/PageController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    /** /page/{slug} addresses that are built-in pages, not admin pages. */
    protected const RESERVED_SLUGS = ['contact', 'sitemap'];

    /**
     * Pages are managed in the Page & Menu Manager; old links keep working.
     */
    public function index(Request $request)
    {
        return redirect()->route('admin.page-manager.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('admin.page-manager.index', ['new' => 1]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $page = $this->savePage($request, $request->validate($this->pageRules()));

        return redirect()->route('admin.page-manager.index', ['page' => $page->id])
                        ->with('success', 'Page created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Page $page)
    {
        return redirect()->route('admin.page-manager.index', ['page' => $page->id]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Page $page)
    {
        return redirect()->route('admin.page-manager.index', ['page' => $page->id]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Page $page)
    {
        $this->savePage($request, $request->validate($this->pageRules()), $page);

        return redirect()->route('admin.page-manager.index', ['page' => $page->id])
                        ->with('success', 'Page updated successfully!');
    }

    /** Validation rules of the page form. */
    protected function pageRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::notIn(self::RESERVED_SLUGS)],
            'status' => 'required|in:draft,published,private',
            'translations' => 'nullable|array',
            'translations.*.title' => 'nullable|string|max:255',
            'translations.*.content' => 'nullable|string',
            'translations.*.meta_title' => 'nullable|string|max:255',
            'translations.*.meta_description' => 'nullable|string|max:500',
            'translations.*.meta_keywords' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_homepage' => 'boolean',
            'header_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'remove_header_image' => 'boolean',
        ];
    }

    /**
     * Create ($page null) or update a page from validated form data: unique
     * slug, single homepage, header image, per-language translations.
     */
    protected function savePage(Request $request, array $validated, ?Page $page = null): Page
    {
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?: $validated['name'], $page);

        if (!empty($validated['is_homepage'])) {
            Page::where('is_homepage', true)->when($page, fn ($q) => $q->whereKeyNot($page->id))
                ->update(['is_homepage' => false]);
            $validated['status'] = 'published'; // Homepage must be published
        }

        if (!$page && empty($validated['sort_order'])) {
            $validated['sort_order'] = (Page::max('sort_order') ?? 0) + 1;
        }

        $validated = $this->withHeaderImage($request, $validated, $page);

        if ($page) {
            $page->update($this->withTranslations($validated, $page));
            return $page;
        }

        return Page::create($this->withTranslations($validated));
    }

    /**
     * URL-safe slug (same rule as the form's live preview), made unique by
     * adding -1, -2 ... when another page or a built-in page already uses it.
     */
    protected function uniqueSlug(string $text, ?Page $page = null): string
    {
        $base = Str::slug($text) ?: 'page';
        $slug = $base;

        for ($i = 1; in_array($slug, self::RESERVED_SLUGS)
            || Page::where('slug', $slug)->when($page, fn ($q) => $q->whereKeyNot($page->id))->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }

    /**
     * Uploaded header image → public/assets/images/pages (same place the site's
     * other images live); "remove" clears it so the homepage cover shows.
     * The previous file is deleted when replaced or removed.
     */
    protected function withHeaderImage(Request $request, array $validated, ?Page $page = null): array
    {
        $old = $page?->header_image;
        unset($validated['header_image'], $validated['remove_header_image']);

        if ($request->hasFile('header_image')) {
            $file = $request->file('header_image');
            $name = Str::slug($validated['slug'] ?? $validated['name']) . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/images/pages'), $name);
            $validated['header_image'] = 'pages/' . $name;
        } elseif ($request->boolean('remove_header_image')) {
            $validated['header_image'] = null;
        }

        if ($page && array_key_exists('header_image', $validated)) {
            $this->deleteHeaderImageFile($old, $page);
        }

        return $validated;
    }

    /** Delete an uploaded header image unless another page still uses it (duplicates share it). */
    protected function deleteHeaderImageFile(?string $path, ?Page $except = null): void
    {
        if (!$path || !str_starts_with($path, 'pages/')) {
            return;
        }
        $inUse = Page::where('header_image', $path)->when($except, fn ($q) => $q->whereKeyNot($except->id))->exists();
        if (!$inUse) {
            @unlink(public_path('assets/images/' . $path));
        }
    }

    /**
     * Active languages for the form tabs, default language first.
     */
    protected function activeLanguages()
    {
        return Language::getActive()->sortByDesc('is_default')->values();
    }

    /**
     * Keep the posted fields of active languages (empty ones dropped). Content
     * of languages that are switched off right now stays as it was, and en/nl
     * are mirrored into their old columns for anything still reading them.
     */
    protected function withTranslations(array $validated, ?Page $page = null): array
    {
        $active = Language::getActiveCodes();
        $translations = Arr::except($page?->translations ?? [], $active);

        foreach (Arr::only($validated['translations'] ?? [], $active) as $code => $fields) {
            $fields = array_filter(Arr::only($fields, Page::TRANSLATABLE), 'filled');
            if ($fields) {
                $translations[$code] = $fields;
            }
        }

        $validated['translations'] = $translations ?: null;

        foreach (['en', 'nl'] as $code) {
            foreach (['content', 'meta_title', 'meta_description', 'meta_keywords'] as $field) {
                $validated["{$code}_{$field}"] = $translations[$code][$field] ?? null;
            }
        }

        return $validated;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Page $page)
    {
        // Prevent deletion of homepage
        if ($page->is_homepage) {
            return redirect()->route('admin.page-manager.index', ['page' => $page->id])
                           ->with('error', 'Cannot delete the homepage. Please set another page as homepage first.');
        }

        $image = $page->header_image;
        $page->delete();
        $this->deleteHeaderImageFile($image);

        return redirect()->route('admin.page-manager.index')
                        ->with('success', 'Page deleted successfully!');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:publish,unpublish,private,delete',
            'page_ids' => 'required|array',
            'page_ids.*' => 'exists:pages,id'
        ]);

        $pages = Page::whereIn('id', $request->page_ids);

        switch ($request->action) {
            case 'publish':
                $pages->update(['status' => 'published', 'published_at' => now()]);
                $message = 'Pages published successfully!';
                break;
                
            case 'unpublish':
                $pages->update(['status' => 'draft', 'published_at' => null]);
                $message = 'Pages unpublished successfully!';
                break;
                
            case 'private':
                $pages->update(['status' => 'private', 'published_at' => null]);
                $message = 'Pages made private successfully!';
                break;
                
            case 'delete':
                // Prevent deletion of homepage
                // clone: a where() on $pages itself would also limit the delete below to homepages
                $homepageIds = (clone $pages)->where('is_homepage', true)->pluck('id');
                if ($homepageIds->isNotEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete homepage. Please remove homepage status first.'
                    ]);
                }
                
                $images = (clone $pages)->whereNotNull('header_image')->pluck('header_image');
                $pages->delete();
                $images->unique()->each(fn ($image) => $this->deleteHeaderImageFile($image));
                $message = 'Pages deleted successfully!';
                break;
                
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid action.'
                ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return redirect()->route('admin.pages.index')->with('success', $message);
    }

    /**
     * Update sort order
     */
    public function updateSortOrder(Request $request)
    {
        $request->validate([
            'page_ids' => 'required|array',
            'page_ids.*' => 'exists:pages,id'
        ]);

        foreach ($request->page_ids as $index => $pageId) {
            Page::where('id', $pageId)->update(['sort_order' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sort order updated successfully!'
        ]);
    }

    /**
     * Upload image for CKEditor
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'upload' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        try {
            $image = $request->file('upload');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('pages/images', $filename, 'public');
            
            return response()->json([
                'url' => asset('storage/' . $path)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'message' => 'Upload failed: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Duplicate page
     */
    public function duplicate(Page $page)
    {
        $newPage = $page->replicate();
        $newPage->name = $page->name . ' (Copy)';
        $newPage->slug = $page->slug . '-copy';
        $newPage->status = 'draft';
        $newPage->is_homepage = false;
        $newPage->published_at = null;
        $newPage->sort_order = (Page::max('sort_order') ?? 0) + 1;

        // Ensure unique slug
        $originalSlug = $newPage->slug;
        $counter = 1;
        while (Page::where('slug', $newPage->slug)->exists()) {
            $newPage->slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $newPage->save();

        return redirect()->route('admin.pages.edit', $newPage)
                        ->with('success', 'Page duplicated successfully!');
    }
}