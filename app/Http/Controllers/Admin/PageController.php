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
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Page::query();

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('slug', 'like', '%' . $request->search . '%')
                  ->orWhere('translations', 'like', '%' . $request->search . '%')
                  ->orWhere('en_content', 'like', '%' . $request->search . '%')
                  ->orWhere('nl_content', 'like', '%' . $request->search . '%');
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Order by
        $orderBy = $request->get('order_by', 'sort_order');
        $orderDirection = $request->get('order_direction', 'asc');
        
        if ($orderBy === 'sort_order') {
            $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
        } else {
            $query->orderBy($orderBy, $orderDirection);
        }

        $pages = $query->paginate(10)->withQueryString();

        // Statistics
        $stats = [
            'total' => Page::count(),
            'published' => Page::where('status', 'published')->count(),
            'draft' => Page::where('status', 'draft')->count(),
            'private' => Page::where('status', 'private')->count(),
        ];

        return view('admin.pages.index', compact('pages', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $languages = $this->activeLanguages();
        return view('admin.pages.create', compact('languages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'status' => 'required|in:draft,published,private',
            'translations' => 'nullable|array',
            'translations.*.title' => 'nullable|string|max:255',
            'translations.*.content' => 'nullable|string',
            'translations.*.meta_title' => 'nullable|string|max:255',
            'translations.*.meta_description' => 'nullable|string|max:500',
            'translations.*.meta_keywords' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_homepage' => 'boolean',
            'show_in_menu' => 'boolean',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Ensure slug is unique
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Page::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Handle homepage setting
        if (!empty($validated['is_homepage'])) {
            Page::where('is_homepage', true)->update(['is_homepage' => false]);
            $validated['status'] = 'published'; // Homepage must be published
        }

        // Set sort order if not provided
        if (empty($validated['sort_order'])) {
            $validated['sort_order'] = (Page::max('sort_order') ?? 0) + 1;
        }

        $page = Page::create($this->withTranslations($validated));

        return redirect()->route('admin.pages.index')
                        ->with('success', 'Page created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Page $page)
    {
        return view('admin.pages.show', compact('page'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Page $page)
    {
        $languages = $this->activeLanguages();
        return view('admin.pages.edit', compact('page', 'languages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('pages')->ignore($page->id)
            ],
            'status' => 'required|in:draft,published,private',
            'translations' => 'nullable|array',
            'translations.*.title' => 'nullable|string|max:255',
            'translations.*.content' => 'nullable|string',
            'translations.*.meta_title' => 'nullable|string|max:255',
            'translations.*.meta_description' => 'nullable|string|max:500',
            'translations.*.meta_keywords' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_homepage' => 'boolean',
            'show_in_menu' => 'boolean',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Handle homepage setting
        if (!empty($validated['is_homepage'])) {
            Page::where('id', '!=', $page->id)
                ->where('is_homepage', true)
                ->update(['is_homepage' => false]);
            $validated['status'] = 'published'; // Homepage must be published
        }

        $page->update($this->withTranslations($validated, $page));

        return redirect()->route('admin.pages.index')
                        ->with('success', 'Page updated successfully!');
    }

    /**
     * Active languages for the form tabs, default language first.
     */
    private function activeLanguages()
    {
        return Language::getActive()->sortByDesc('is_default')->values();
    }

    /**
     * Keep the posted fields of active languages (empty ones dropped). Content
     * of languages that are switched off right now stays as it was, and en/nl
     * are mirrored into their old columns for anything still reading them.
     */
    private function withTranslations(array $validated, ?Page $page = null): array
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
            return redirect()->route('admin.pages.index')
                           ->with('error', 'Cannot delete the homepage. Please set another page as homepage first.');
        }

        $page->delete();

        return redirect()->route('admin.pages.index')
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
                $homepageIds = $pages->where('is_homepage', true)->pluck('id');
                if ($homepageIds->isNotEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete homepage. Please remove homepage status first.'
                    ]);
                }
                
                $pages->delete();
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