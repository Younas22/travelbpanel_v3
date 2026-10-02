<?php

namespace App\Http\Controllers;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\Page;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Tour;
use App\Models\TourInclusion;
use App\Models\Umrah;
use App\Models\UmrahInclusion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagesController extends Controller
{
    public function home() {
        // Fetch featured tours if tours module is active
        $tours = collect();
        $toursModule = getActiveModule('tours');
        if ($toursModule) {
            $tours = Tour::with(['images', 'packageType'])
                ->where('status', '1')
                ->where('featured', '1')
                ->orderBy('featured', 'desc')
                ->limit(4)
                ->get();

            // Get all inclusions for mapping
            $allInclusions = TourInclusion::pluck('name', 'id')->toArray();

            // Transform tours to include inclusion names and location
            $tours->transform(function ($tour) use ($allInclusions) {
                // Map inclusion IDs to names
                if ($tour->inclusions) {
                    $tour->highlights = collect($tour->inclusions)->map(function ($id) use ($allInclusions) {
                        return $allInclusions[$id] ?? null;
                    })->filter()->values()->toArray();
                } else {
                    $tour->highlights = [];
                }

                // Get location name
                if ($tour->loaction) {
                    $locationData = DB::table('locations')->where('id', $tour->loaction)->first();
                    $tour->location_name = $locationData ? $locationData->city : '';
                } else {
                    $tour->location_name = '';
                }

                return $tour;
            });
        }

        // Fetch featured umrah packages if umrah module is active
        $umrahPackages = collect();
        $umrahModule = getActiveModule('umrah');
        if ($umrahModule) {
            $umrahPackages = Umrah::with(['images', 'packageType'])
                ->where('status', '1')
                ->where('featured', '1')
                ->orderBy('featured', 'desc')
                ->limit(4)
                ->get();

            // Get all inclusions for mapping
            $allInclusions = UmrahInclusion::pluck('name', 'id')->toArray();

            // Transform packages to include inclusion names and locations
            $umrahPackages->transform(function ($package) use ($allInclusions) {
                // Map inclusion IDs to names
                if ($package->inclusions) {
                    $package->highlights = collect($package->inclusions)->map(function ($id) use ($allInclusions) {
                        return $allInclusions[$id] ?? null;
                    })->filter()->values()->toArray();
                } else {
                    $package->highlights = [];
                }

                // Get location names from flights_airports
                if ($package->leaving_from) {
                    $fromAirport = DB::table('flights_airports')->where('id', $package->leaving_from)->first();
                    $package->from_location = $fromAirport ? $fromAirport->city : '';
                } else {
                    $package->from_location = '';
                }

                if ($package->going_to) {
                    $toAirport = DB::table('flights_airports')->where('id', $package->going_to)->first();
                    $package->to_location = $toAirport ? $toAirport->city : '';
                } else {
                    $package->to_location = '';
                }

                return $package;
            });
        }

        // Fetch featured hotels if hotel module is active
        $featuredHotels = collect();
        $hotelModule = getActiveModule('hotel');
        if ($hotelModule) {
            $featuredHotels = Hotel::with(['images', 'location', 'amenities'])
                ->where('status', 1)
                ->where('featured', '1')
                ->limit(4)
                ->get();
        }

        $hotel_search = session('hotel_search');

        // Latest published posts for the homepage "Travel Guides" section
        $latestBlogPosts = BlogPost::with('category')
            ->where('status', 'published')
            ->latest('published_at')
            ->limit(4)
            ->get();

        return view('home', compact('tours', 'umrahPackages', 'featuredHotels', 'hotel_search', 'latestBlogPosts'));
    }


    public function staticPage($slug)
{
    $staticPages = [
        'contact' => [
            'view' => 'pages.contact',
            'seo' => [
                'title' => 'Contact Us',
                'description' => 'Get in touch with our support or sales team.',
                'keywords' => 'contact, support, help',
            ],
        ],
        'sitemap' => [
            'view' => 'pages.sitemap',
            'seo' => [
                'title' => 'Sitemap',
                'description' => 'Overview of all website links.',
                'keywords' => 'sitemap, navigation',
            ],
        ],
    ];

    if (array_key_exists($slug, $staticPages)) {
        $pageData = $staticPages[$slug];
        return view($pageData['view'], ['seo' => $pageData['seo']]);
    }

    if (in_array($slug, ['privacy-policy', 'terms-conditions','about'])) {
        $page = Page::where('slug', $slug)
                    ->where('status', 'published')
                    ->firstOrFail();
        
        $seo = [
            'title' => $page->meta_title ?: $page->name,
            'description' => $page->meta_description ?: '',
            'keywords' => $page->meta_keywords ?: '',
        ];


        return view('pages.global', compact('page', 'seo'));
    }

    abort(404);
}




    public function index() {
        $blogs = BlogPost::with('category')
                        ->where('status', 'published')
                        ->latest()
                        ->paginate(10);
        
        // Get all active categories that have blog posts
        $categories = BlogCategory::has('posts')
                            ->whereHas('posts', function($query) {
                                $query->where('status', 'published');
                            })
                            ->get();
        
        return view('pages.blog', compact('blogs', 'categories'));
    }


    public function detail($slug) {
    $blog = BlogPost::with(['category', 'author', 'comments'])
                   ->where('slug', $slug)
                   ->where('status', 'published')
                   ->firstOrFail();

    // Increment view count
    $blog->increment('views_count');
    
    // Get related posts (from same category)
    $relatedPosts = BlogPost::where('category_id', $blog->category_id)
                           ->where('id', '!=', $blog->id)
                           ->where('status', 'published')
                           ->latest()
                           ->take(3)
                           ->get();
    
    // Get previous and next posts
    $previousPost = BlogPost::where('id', '<', $blog->id)
                          ->where('status', 'published')
                          ->orderBy('id', 'desc')
                          ->first();
    
    $nextPost = BlogPost::where('id', '>', $blog->id)
                      ->where('status', 'published')
                      ->orderBy('id', 'asc')
                      ->first();
    
    return view('pages.blogdetails', compact('blog', 'relatedPosts', 'previousPost', 'nextPost'));
}



}
