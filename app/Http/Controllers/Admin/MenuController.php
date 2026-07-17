<?php
// app/Http/Controllers/Admin/MenuController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $category = $request->get('category', 'header');
        
        $menuItems = MenuItem::where('category', $category)
                            ->with(['children' => function($q) {
                                $q->orderBy('sort_order');
                            }])
                            ->parentItems()
                            ->ordered()
                            ->get();

        $categories = MenuItem::getCategoryOptions();
        
        $stats = [
            'total' => MenuItem::count(),
            'active' => MenuItem::active()->count(),
            'header' => MenuItem::header()->count(),
            'footer_total' => MenuItem::whereIn('category', ['footer_quick_links', 'footer_services', 'footer_support'])->count(),
        ];

        return view('admin.menus.index', compact('menuItems', 'categories', 'category', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $category = $request->get('category', 'header');
        $categories = MenuItem::getCategoryOptions();
        
        // Get potential parent items for the selected category
        $parentItems = MenuItem::where('category', $category)
                              ->parentItems()
                              ->active()
                              ->ordered()
                              ->get();

        return view('admin.menus.create', compact('categories', 'category', 'parentItems'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|string|max:500',
            'category' => 'required|in:header,footer_quick_links,footer_services,footer_support',
            'parent_id' => 'nullable|exists:menu_items,id',
            'icon' => 'nullable|string|max:100',
            'target' => 'required|in:_self,_blank',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        // Set sort order
        $maxSortOrder = MenuItem::where('category', $validated['category'])
                               ->where('parent_id', $validated['parent_id'])
                               ->max('sort_order') ?? 0;
        
        $validated['sort_order'] = $maxSortOrder + 1;

        MenuItem::create($validated);

        return redirect()->route('admin.menus.index', ['category' => $validated['category']])
                        ->with('success', 'Menu item created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MenuItem $menu)
    {
        $categories = MenuItem::getCategoryOptions();
        
        // Get potential parent items for the selected category (excluding self and descendants)
        $parentItems = MenuItem::where('category', $menu->category)
                              ->parentItems()
                              ->where('id', '!=', $menu->id)
                              ->active()
                              ->ordered()
                              ->get();

        return view('admin.menus.edit', compact('menu', 'categories', 'parentItems'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MenuItem $menu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|string|max:500',
            'category' => 'required|in:header,footer_quick_links,footer_services,footer_support',
            'parent_id' => 'nullable|exists:menu_items,id',
            'icon' => 'nullable|string|max:100',
            'target' => 'required|in:_self,_blank',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $menu->update($validated);

        return redirect()->route('admin.menus.index', ['category' => $validated['category']])
                        ->with('success', 'Menu item updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MenuItem $menu)
    {
        $category = $menu->category;
        
        // Delete menu item and its children
        $menu->delete();

        return redirect()->route('admin.menus.index', ['category' => $category])
                        ->with('success', 'Menu item deleted successfully!');
    }

    /**
     * Update sort order via drag and drop
     */
    public function updateSortOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.sort_order' => 'required|integer|min:0',
            'items.*.parent_id' => 'nullable|exists:menu_items,id',
        ]);

        foreach ($request->items as $item) {
            MenuItem::where('id', $item['id'])->update([
                'sort_order' => $item['sort_order'],
                'parent_id'  => $item['parent_id'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Menu order updated successfully!',
        ]);
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'menu_ids' => 'required|array',
            'menu_ids.*' => 'exists:menu_items,id'
        ]);

        $menuItems = MenuItem::whereIn('id', $request->menu_ids);

        switch ($request->action) {
            case 'activate':
                $menuItems->update(['is_active' => true]);
                $message = 'Menu items activated successfully!';
                break;
                
            case 'deactivate':
                $menuItems->update(['is_active' => false]);
                $message = 'Menu items deactivated successfully!';
                break;
                
            case 'delete':
                $menuItems->delete();
                $message = 'Menu items deleted successfully!';
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

        return redirect()->route('admin.menus.index')->with('success', $message);
    }

    /**
     * Toggle menu item status
     */
    public function toggleStatus(MenuItem $menu)
    {
        $menu->update(['is_active' => !$menu->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Menu status updated successfully!',
            'is_active' => $menu->is_active
        ]);
    }

    /**
     * Get parent items for AJAX
     */
    public function getParentItems(Request $request)
    {
        $category = $request->get('category');
        $excludeId = $request->get('exclude_id');
        
        $parentItems = MenuItem::where('category', $category)
                              ->parentItems()
                              ->when($excludeId, function($q) use ($excludeId) {
                                  $q->where('id', '!=', $excludeId);
                              })
                              ->active()
                              ->ordered()
                              ->get(['id', 'name']);

        return response()->json($parentItems);
    }

    /**
     * Duplicate menu item
     */
    public function duplicate(MenuItem $menu)
    {
        $newMenu = $menu->replicate();
        $newMenu->name = $menu->name . ' (Copy)';
        $newMenu->sort_order = (MenuItem::where('category', $menu->category)
                                       ->where('parent_id', $menu->parent_id)
                                       ->max('sort_order') ?? 0) + 1;
        $newMenu->save();

        return redirect()->route('admin.menus.index', ['category' => $menu->category])
                        ->with('success', 'Menu item duplicated successfully!');
    }
}