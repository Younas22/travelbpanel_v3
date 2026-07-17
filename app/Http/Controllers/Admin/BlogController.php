<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(Request $request)
    {
 // Get blog statistics
        $stats = [
            'total_posts' => BlogPost::count(),
            'total_views' => BlogPost::sum('views_count'),
            'total_comments' => BlogPost::sum('comments_count'),
            'draft_posts' => BlogPost::draft()->count(),
            'published_posts' => BlogPost::published()->count(),
            'scheduled_posts' => BlogPost::scheduled()->count(),
            'posts_this_month' => BlogPost::whereMonth('created_at', now()->month)->count(),
            'views_this_month' => BlogPost::whereMonth('created_at', now()->month)->sum('views_count'),
            'pending_comments' => BlogPost::whereHas('comments', function($q) {
                $q->where('status', 'pending');
            })->count(),
            'featured_posts' => BlogPost::featured()->count(),
        ];

        // Calculate percentage changes (compared to last month)
        $lastMonth = now()->subMonth();
        $lastMonthStats = [
            'posts_last_month' => BlogPost::whereMonth('created_at', $lastMonth->month)->count(),
            'views_last_month' => BlogPost::whereMonth('created_at', $lastMonth->month)->sum('views_count'),
        ];

        // Calculate percentage changes
        $stats['posts_growth'] = $lastMonthStats['posts_last_month'] > 0 
            ? (($stats['posts_this_month'] - $lastMonthStats['posts_last_month']) / $lastMonthStats['posts_last_month']) * 100
            : 0;

        $stats['views_growth'] = $lastMonthStats['views_last_month'] > 0 
            ? (($stats['views_this_month'] - $lastMonthStats['views_last_month']) / $lastMonthStats['views_last_month']) * 100
            : 0;

        // Query builder for posts
        $query = BlogPost::with(['author', 'category', 'tags']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $request->search . '%')
                  ->orWhereHas('author', function($subQ) use ($request) {
                      $subQ->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->filled('author')) {
            $query->whereHas('author', function($q) use ($request) {
                $q->where('id', $request->author);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        switch ($sortBy) {
            case 'title':
                $query->orderBy('title', $sortOrder);
                break;
            case 'views':
                $query->orderBy('views_count', $sortOrder);
                break;
            case 'comments':
                $query->orderBy('comments_count', $sortOrder);
                break;
            case 'published_at':
                $query->orderBy('published_at', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }

        // Get posts with pagination
        $posts = $query->paginate(10)->appends($request->query());

        // Get categories for filter dropdown
        $categories = BlogCategory::orderBy('name')->get();

    // Get authors for filter dropdown
        $authors = User::whereHas('blogPosts')
                    ->orderBy('first_name')
                    ->get(['id', 'first_name']);

        // Recent activity
        $recent_posts = BlogPost::with('author')
                               ->latest()
                               ->take(5)
                               ->get();

        // Popular posts
        $popular_posts = BlogPost::with('author')
                                ->published()
                                ->orderBy('views_count', 'desc')
                                ->take(5)
                                ->get();

        // SEO insights
        $seo_insights = [
            'excellent_seo' => BlogPost::where('seo_score', '>=', 80)->count(),
            'good_seo' => BlogPost::whereBetween('seo_score', [60, 79])->count(),
            'needs_work' => BlogPost::where('seo_score', '<', 60)->count(),
            'avg_seo_score' => round(BlogPost::avg('seo_score'), 1),
        ];

        return view('admin.content.blog.index', compact(
            'stats',
            'posts',
            'categories',
            'authors',
            'recent_posts',
            'popular_posts',
            'seo_insights'
        ));
    }
    
    public function create()
    {
        $categories = BlogCategory::all();
        return view('admin.content.blog.create', compact('categories'));
    }
     
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|image|max:2048',
            'category_id' => 'required|exists:blog_categories,id',
            'status' => 'required|in:draft,published,scheduled',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
            'is_featured' => 'sometimes|boolean',
            'allow_comments' => 'boolean',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $validated['author_id'] = auth()->id();
        $validated['is_featured'] = $request->has('is_featured');
        $validated['allow_comments'] = $request->has('allow_comments');
        $validated['slug'] = Str::slug($request->title);
        
        if ($request->hasFile('featured_image')) {
            $image = $request->file('featured_image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('assets/images/blogs'), $filename);
            $validated['featured_image'] = 'assets/images/blogs/' . $filename;
        }

        $post = BlogPost::create($validated);

        // Handle tags
        if ($request->tags) {
            $tags = array_map('trim', explode(',', $request->tags));
            $post->syncTags($tags);
        }

        // Calculate SEO score
        $post->calculateSeoScore();

        return redirect()->route('admin.content.blog.index')
                        ->with('success', 'Blog post created successfully!');
    }
    
    public function edit(BlogPost $post)
{
    $categories = BlogCategory::all();
    $tags = $post->tags->pluck('name')->implode(', ');
    
    return view('admin.content.blog.edit', compact('post', 'categories', 'tags'));
}

public function update(Request $request, BlogPost $post)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'slug' => 'required|string|unique:blog_posts,slug,' . $post->id,
        'content' => 'required|string',
        'excerpt' => 'nullable|string',
        'featured_image' => 'nullable|image|max:2048',
        'category_id' => 'required|exists:blog_categories,id',
        'status' => 'required|in:draft,published,scheduled',
        'seo_title' => 'nullable|string|max:255',
        'meta_description' => 'nullable|string|max:255',
        'tags' => 'nullable|string',
        'is_featured' => 'boolean',
        'allow_comments' => 'boolean',
        'scheduled_at' => 'nullable|date|after:now',
    ]);

    // Handle featured image update
    if ($request->hasFile('featured_image')) {
        // Delete old image if exists
        if ($post->featured_image && file_exists(public_path($post->featured_image))) {
            unlink(public_path($post->featured_image));
        }

        $image = $request->file('featured_image');
        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('assets/images/blogs'), $filename);
        $validated['featured_image'] = 'assets/images/blogs/' . $filename;
    }

    // Handle image removal
    if ($request->has('remove_featured_image')) {
        if ($post->featured_image && file_exists(public_path($post->featured_image))) {
            unlink(public_path($post->featured_image));
        }
        $validated['featured_image'] = null;
    }

    // Handle tags
    if ($request->tags) {
        $tags = array_map('trim', explode(',', $request->tags));
        $post->syncTags($tags);
    }

    // Handle scheduled posts
    if ($validated['status'] !== 'scheduled') {
        $validated['scheduled_at'] = null;
    }

    $post->update($validated);

    // Recalculate SEO score
    $post->calculateSeoScore();

    return redirect()->route('admin.content.blog.index')
                    ->with([
                        'success' => 'Blog post updated successfully!',
                        'updated_post_id' => $post->id
                    ]);
}
    
    public function destroy(BlogPost $post)
    {
        $post->delete();
        
        return redirect()->route('admin.content.blog.index')
                        ->with('success', 'Blog post deleted successfully!');
    }



        // In BlogController.php
    public function publish(BlogPost $post)
    {
        $post->update([
            'status' => 'published',
            'published_at' => now(),
            'scheduled_at' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Post published successfully!'
        ]);
    }

    public function cancelSchedule(BlogPost $post)
    {
        $post->update([
            'status' => 'draft',
            'scheduled_at' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Schedule canceled successfully!'
        ]);
    }


        public function duplicate(BlogPost $post)
    {
        try {
            $newPost = $post->replicate();
            $newPost->title = 'Copy of ' . $post->title;
            $newPost->slug = Str::slug($newPost->title);
            $newPost->status = 'draft';
            $newPost->published_at = null;
            $newPost->scheduled_at = null;
            $newPost->views_count = 0;
            $newPost->save();

            // Duplicate tags if any
            if ($post->tags->count() > 0) {
                $newPost->tags()->attach($post->tags->pluck('id'));
            }

            return response()->json([
                'success' => true,
                'message' => 'Post duplicated successfully!',
                'redirect' => route('admin.content.blog.edit', $newPost)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate post: ' . $e->getMessage()
            ], 500);
        }
    }




public function uploadImage(Request $request)
{
    $request->validate([
        'upload' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
    ]);

    try {
        $image = $request->file('upload');
        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('assets/images/blogs'), $filename);
        $path = 'assets/images/blogs/' . $filename;

        return response()->json([
            'url' => asset($path)
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => [
                'message' => 'Upload failed: ' . $e->getMessage()
            ]
        ], 500);
    }
}

    public function show()  {
        
    }


/**
     * Get categories for management modal
     */
    public function getCategories()
    {
        try {
            $categories = BlogCategory::with('parent')
                            ->withCount('posts')
                            ->orderBy('created_at', 'desc')
                            ->get();

            return response()->json($categories);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Store a new category
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name',
            'slug' => 'nullable|string|max:255|unique:blog_categories,slug',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7',
            'parent_id' => 'nullable|exists:blog_categories,id',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Set default color if not provided
        if (empty($validated['color'])) {
            $validated['color'] = '#667eea';
        }

        // Set sort order
        $maxSortOrder = BlogCategory::where('parent_id', $validated['parent_id'] ?? null)
                                   ->max('sort_order') ?? 0;
        $validated['sort_order'] = $maxSortOrder + 1;
        $validated['is_active'] = true;

        $category = BlogCategory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully!',
            'category' => $category->load('parent')
        ]);
    }

    /**
     * Update a category
     */
    public function updateCategory(Request $request, BlogCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name,' . $category->id,
            'slug' => 'nullable|string|max:255|unique:blog_categories,slug,' . $category->id,
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7',
            'parent_id' => 'nullable|exists:blog_categories,id',
            'is_active' => 'boolean',
        ]);

        // Prevent setting parent as itself or its child
        if (isset($validated['parent_id']) && $validated['parent_id'] == $category->id) {
            return response()->json([
                'success' => false,
                'message' => 'Category cannot be its own parent.'
            ], 422);
        }

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully!',
            'category' => $category->load('parent')
        ]);
    }

    /**
     * Delete a category
     */
    public function deleteCategory(BlogCategory $category)
    {
        // Check if category has posts
        $postsCount = $category->posts()->count();
        
        if ($postsCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete category. It has {$postsCount} posts associated with it."
            ], 422);
        }

        // Check if category has children
        $childrenCount = $category->children()->count();
        
        if ($childrenCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete category. It has {$childrenCount} sub-categories."
            ], 422);
        }

        $categoryName = $category->name;
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => "Category '{$categoryName}' deleted successfully!"
        ]);
    }

    /**
     * Get category for editing
     */
    public function getCategory(BlogCategory $category)
    {
        return response()->json($category->load('parent'));
    }

    /**
     * Toggle category status
     */
    public function toggleCategoryStatus(BlogCategory $category)
    {
        $category->update(['is_active' => !$category->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Category status updated successfully!',
            'is_active' => $category->is_active
        ]);
    }

    /**
     * Update category sort order
     */
    public function updateCategoryOrder(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:blog_categories,id',
            'categories.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->categories as $categoryData) {
            BlogCategory::where('id', $categoryData['id'])
                       ->update(['sort_order' => $categoryData['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category order updated successfully!'
        ]);
    }

}