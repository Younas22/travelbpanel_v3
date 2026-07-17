<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{

    public function index(Request $request)
{
    $search = $request->input('search');
    $status = $request->input('status');
    $joinDate = $request->input('join_date');
    
    $query = NewsletterSubscriber::query();
    
    if ($search) {
        $query->where('email', 'like', "%{$search}%");
    }
    
    if ($status) {
        $query->where('status', $status);
    }
    
    if ($joinDate) {
        switch ($joinDate) {
            case 'today':
                $query->whereDate('joined_date', today());
                break;
            case 'week':
                $query->where('joined_date', '>=', now()->startOfWeek());
                break;
            case 'month':
                $query->whereMonth('joined_date', now()->month);
                break;
            case 'year':
                $query->whereYear('joined_date', now()->year);
                break;
        }
    }
    
    $stats = [
        'total_subscribers' => NewsletterSubscriber::count(),
        'active_subscribers' => NewsletterSubscriber::active()->count(),
        'unsubscribed' => NewsletterSubscriber::unsubscribed()->count(),
        'new_this_week' => NewsletterSubscriber::where('joined_date', '>=', now()->startOfWeek())->count(),
        'new_this_month' => NewsletterSubscriber::whereMonth('joined_date', now()->month)->count(),
        'growth_percentage' => 15 // Calculate actual growth
    ];
    
    $subscribers = $query->latest()->paginate(10);
    
    return view('admin.content.newsletter.index', compact('subscribers', 'stats'));
}
    public function subscribers(Request $request)
{
    $search = $request->input('search');
    $status = $request->input('status');
    $joinDate = $request->input('join_date');
    
    $query = NewsletterSubscriber::query();
    
    if ($search) {
        $query->where('email', 'like', "%{$search}%");
    }
    
    if ($status) {
        $query->where('status', $status);
    }
    
    if ($joinDate) {
        switch ($joinDate) {
            case 'today':
                $query->whereDate('joined_date', today());
                break;
            case 'week':
                $query->where('joined_date', '>=', now()->startOfWeek());
                break;
            case 'month':
                $query->whereMonth('joined_date', now()->month);
                break;
            case 'year':
                $query->whereYear('joined_date', now()->year);
                break;
        }
    }
    
    $stats = [
        'total_subscribers' => NewsletterSubscriber::count(),
        'active_subscribers' => NewsletterSubscriber::active()->count(),
        'unsubscribed' => NewsletterSubscriber::unsubscribed()->count(),
        'new_this_week' => NewsletterSubscriber::where('joined_date', '>=', now()->startOfWeek())->count(),
        'new_this_month' => NewsletterSubscriber::whereMonth('joined_date', now()->month)->count(),
        'growth_percentage' => 15 // Calculate actual growth
    ];
    
    $subscribers = $query->latest()->paginate(10);
    
    return view('admin.content.newsletter.index', compact('subscribers', 'stats'));
}

public function showSubscriber($id)
{
    $subscriber = NewsletterSubscriber::findOrFail($id);
    
    if (request()->ajax()) {
        return response()->json([
            'success' => true,
            'subscriber' => $subscriber
        ]);
    }
    
    return response()->json(['success' => false]);
}

public function storeSubscriber(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email|unique:newsletter_subscribers,email',
        'status' => 'required|in:active,inactive,unsubscribed',
        'joined_date' => 'required|date'
    ]);
    
    NewsletterSubscriber::create($validated);
    
    return redirect()->route('admin.content.newsletter.subscribers')
                    ->with('success', 'Subscriber added successfully!');
}

public function updateSubscriber(Request $request, $id)
{
    $subscriber = NewsletterSubscriber::findOrFail($id);
    
    $validated = $request->validate([
        'email' => 'required|email|unique:newsletter_subscribers,email,' . $id,
        'status' => 'required|in:active,inactive,unsubscribed',
        'joined_date' => 'required|date'
    ]);
    
    $subscriber->update($validated);
    
    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Subscriber updated successfully!'
        ]);
    }
    
    return redirect()->route('admin.content.newsletter.subscribers')
                    ->with('success', 'Subscriber updated successfully!');
}

public function destroySubscriber($id)
{
    $subscriber = NewsletterSubscriber::findOrFail($id);
    $subscriber->delete();
    
    return response()->json([
        'success' => true,
        'message' => 'Subscriber deleted successfully!'
    ]);
}

public function bulkAction(Request $request, $action)
{
    $subscriberIds = $request->input('subscriber_ids');
    
    if (empty($subscriberIds)) {
        return response()->json([
            'success' => false,
            'message' => 'No subscribers selected'
        ]);
    }
    
    $subscribers = NewsletterSubscriber::whereIn('id', $subscriberIds);
    
    switch ($action) {
        case 'activate':
            $subscribers->update(['status' => 'active']);
            $message = 'Subscribers activated successfully!';
            break;
        case 'deactivate':
            $subscribers->update(['status' => 'inactive']);
            $message = 'Subscribers deactivated successfully!';
            break;
        case 'unsubscribe':
            $subscribers->update(['status' => 'unsubscribed']);
            $message = 'Subscribers unsubscribed successfully!';
            break;
        case 'delete':
            $subscribers->delete();
            $message = 'Subscribers deleted successfully!';
            break;
        default:
            return response()->json([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
    
    return response()->json([
        'success' => true,
        'message' => $message
    ]);
}

public function bulkImport(Request $request)
{
    $request->validate([
        'csv_file' => 'required|file|mimes:csv,txt'
    ]);
    
    $file = $request->file('csv_file');
    $csvData = array_map('str_getcsv', file($file));
    $header = array_shift($csvData);
    
    $imported = 0;
    $skipped = 0;
    
    foreach ($csvData as $row) {
        $data = array_combine($header, $row);
        
        if ($request->has('skip_duplicates') && 
            NewsletterSubscriber::where('email', $data['email'])->exists()) {
            $skipped++;
            continue;
        }
        
        NewsletterSubscriber::create([
            'email' => $data['email'],
            'status' => $data['status'] ?? 'active',
            'joined_date' => $data['joined_date'] ?? now()
        ]);
        
        $imported++;
    }
    
    return redirect()->route('admin.content.newsletter.subscribers')
                    ->with('success', "Imported {$imported} subscribers. Skipped {$skipped} duplicates.");
}


public function changeStatus($id, $action)
{
    $subscriber = NewsletterSubscriber::findOrFail($id);

    switch ($action) {
        case 'activate':
            $subscriber->activate();
            $message = 'Subscriber activated successfully!';
            break;
        case 'subscribe':
            $subscriber->subscribe();
            $message = 'Subscriber resubscribed successfully!';
            break;
        case 'unsubscribe':
            $subscriber->unsubscribe();
            $message = 'Subscriber unsubscribed successfully!';
            break;
        default:
            return response()->json(['success' => false, 'message' => 'Invalid action']);
    }

    return response()->json(['success' => true, 'message' => $message]);
}

// Public subscription method for frontend
public function subscribe(Request $request)
{
    try {
        $validated = $request->validate([
            'email' => 'required|email'
        ]);

        // Check if email already exists
        $existingSubscriber = NewsletterSubscriber::where('email', $validated['email'])->first();

        if ($existingSubscriber) {
            return response()->json([
                'success' => false,
                'message' => 'This email is already subscribed to our newsletter!'
            ]);
        }

        // Create new subscriber
        NewsletterSubscriber::create([
            'email' => $validated['email'],
            'status' => 'active',
            'joined_date' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for subscribing! You will receive exclusive travel deals in your inbox.'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong. Please try again later.'
        ], 500);
    }
}

}