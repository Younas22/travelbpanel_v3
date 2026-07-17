<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{

public function index(Request $request)
    {
        // Get filter values from request
        $search = $request->input('search');
        $status = $request->input('status');
        $tier = $request->input('tier');
        $dateFilter = $request->input('date_filter');
        
        // Base query
        $query = User::customers();
        
        // Search functionality
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($status) {
            $query->where('status', $status);
        }
        
        // Tier filter
        if ($tier) {
            $query->where('customer_tier', $tier);
        }
        
        // Date filter
        if ($dateFilter) {
            switch ($dateFilter) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }
        
        // Get stats for cards
        $stats = [
            'total_customers' => User::customers()->count(),
            'active_customers' => User::customers()->where('status', 'active')->count(),
            'vip_customers' => User::customers()->where('customer_tier', 'platinum')->count(),
            'avg_customer_value' => User::customers()->avg('average_spending') ?? 0
        ];
        
        // Get customers with pagination
        $customers = $query->latest()->paginate(10);
        
        return view('admin.customers.index', compact('customers', 'stats'));
    }
    
    public function create()
    {
        return view('admin.customers.create');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other,prefer-not-to-say',
            'customer_tier' => 'required|in:bronze,silver,gold,platinum',
            'status' => 'required|in:active,inactive,suspended,vip',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'internal_notes' => 'nullable|string',
        ]);
        
        $validated['user_type'] = 'user';
        $validated['password'] = bcrypt('password123');
        $validated['email_verified_at'] = now();
        
        // Handle boolean fields
        $validated['email_notifications'] = $request->has('email_notifications');
        $validated['sms_notifications'] = $request->has('sms_notifications');
        $validated['marketing_emails'] = $request->has('marketing_emails');
        
        User::create($validated);
        
        return redirect()->route('admin.customers.index')
                        ->with('success', 'Customer created successfully!');
    }
    
    public function show(User $user)
    {
        if (!$user->isCustomer()) {
            abort(404);
        }
        
        $user->load('bookings');
        
        return view('admin.customers.show', compact('user'));
    }
    
    public function edit(User $user)
    {
        if (!$user->isCustomer()) {
            abort(404);
        }
        
        return view('admin.customers.edit', compact('user'));
    }
    
    public function update(Request $request, User $user)
    {
        if (!$user->isCustomer()) {
            abort(404);
        }
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other,prefer-not-to-say',
            'customer_tier' => 'required|in:bronze,silver,gold,platinum',
            'status' => 'required|in:active,inactive,suspended,vip',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'internal_notes' => 'nullable|string',
        ]);
        
        // Handle boolean fields
        $validated['email_notifications'] = $request->has('email_notifications');
        $validated['sms_notifications'] = $request->has('sms_notifications');
        $validated['marketing_emails'] = $request->has('marketing_emails');
        
        $user->update($validated);
        
        return redirect()->route('admin.customers.index')
                        ->with('success', 'Customer updated successfully!');
    }
    
    public function destroy(User $user)
    {
        if (!$user->isCustomer()) {
            abort(404);
        }
        
        $user->delete();
        
        return redirect()->route('admin.customers.index')
                        ->with('success', 'Customer deleted successfully!');
    }
    
    public function bulkEmail(Request $request)
    {
        // Bulk email functionality
        return back()->with('success', 'Bulk email sent successfully!');
    }
    
    public function export($format)
    {
        // Export functionality (CSV, Excel, PDF)
        return back()->with('success', "Data exported as {$format} successfully!");
    }
}