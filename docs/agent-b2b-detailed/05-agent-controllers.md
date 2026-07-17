# Agent B2B — Part 5: Agent Controllers (Actual Code)

---

## Controller 1: Agent\AuthController

**File:** `app/Http/Controllers/Agent/AuthController.php`

```php
<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (auth()->check() && auth()->user()->isAgent()) {
            return redirect()->route('agent.dashboard');
        }
        return view('agent.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $key = 'agent-login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['email' => 'Too many attempts. Try again in ' . RateLimiter::availableIn($key) . ' seconds.']);
        }

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password, 'user_type' => 'agent'], $request->remember)) {
            RateLimiter::hit($key, 60);
            return back()->withErrors(['email' => 'Invalid credentials or not an agent account.'])->withInput();
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->route('agent.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('agent.login');
    }
}
```

---

## Controller 2: Agent\DashboardController

**File:** `app/Http/Controllers/Agent/DashboardController.php`

```php
<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\HotelBooking;
use App\Models\FlightBooking;
use App\Models\TourBooking;
use App\Models\UmrahBooking;
use App\Models\AgentWalletTransaction;

class DashboardController extends Controller
{
    public function index()
    {
        $agent  = auth()->user();
        $wallet = $agent->wallet;
        $agentId = $agent->id;

        // Booking counts
        $stats = [
            'hotels'        => HotelBooking::where('agent_id', $agentId)->count(),
            'flights'       => FlightBooking::where('agent_id', $agentId)->count(),
            'tours'         => TourBooking::where('agent_id', $agentId)->count(),
            'umrah'         => UmrahBooking::where('agent_id', $agentId)->count(),
            'this_month'    => HotelBooking::where('agent_id', $agentId)->whereMonth('created_at', now()->month)->count()
                             + FlightBooking::where('agent_id', $agentId)->whereMonth('created_at', now()->month)->count()
                             + TourBooking::where('agent_id', $agentId)->whereMonth('created_at', now()->month)->count()
                             + UmrahBooking::where('agent_id', $agentId)->whereMonth('created_at', now()->month)->count(),
        ];

        $stats['total'] = $stats['hotels'] + $stats['flights'] + $stats['tours'] + $stats['umrah'];

        // Recent bookings (hotels as example, merge all)
        $recentHotels  = HotelBooking::where('agent_id', $agentId)->latest()->take(3)->get()->map(fn($b) => array_merge($b->toArray(), ['type' => 'hotel']));
        $recentFlights = FlightBooking::where('agent_id', $agentId)->latest()->take(3)->get()->map(fn($b) => array_merge($b->toArray(), ['type' => 'flight']));
        $recentTours   = TourBooking::where('agent_id', $agentId)->latest()->take(3)->get()->map(fn($b) => array_merge($b->toArray(), ['type' => 'tour']));

        $recentBookings = collect(array_merge($recentHotels->all(), $recentFlights->all(), $recentTours->all()))
            ->sortByDesc('created_at')
            ->take(5);

        // Recent wallet transactions
        $recentTransactions = AgentWalletTransaction::byAgent($agentId)->latest()->take(5)->get();

        return view('agent.dashboard', compact('agent', 'wallet', 'stats', 'recentBookings', 'recentTransactions'));
    }
}
```

---

## Controller 3: Agent\WalletController

**File:** `app/Http/Controllers/Agent/WalletController.php`

```php
<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentTopupRequest;
use App\Models\AgentWalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WalletController extends Controller
{
    public function index()
    {
        $agent  = auth()->user();
        $wallet = $agent->wallet;
        $recentTransactions = AgentWalletTransaction::byAgent($agent->id)->latest()->take(10)->get();

        return view('agent.wallet.index', compact('agent', 'wallet', 'recentTransactions'));
    }

    public function transactions(Request $request)
    {
        $agent = auth()->user();
        $query = AgentWalletTransaction::byAgent($agent->id);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->byDateRange($request->from, $request->to . ' 23:59:59');
        }

        $transactions = $query->latest()->paginate(20);
        $wallet = $agent->wallet;

        return view('agent.wallet.transactions', compact('agent', 'wallet', 'transactions'));
    }

    public function topupForm()
    {
        $agent = auth()->user();
        $pendingRequest = AgentTopupRequest::byAgent($agent->id)->pending()->latest()->first();

        return view('agent.wallet.topup', compact('agent', 'pendingRequest'));
    }

    public function topupSubmit(Request $request)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:100',
            'payment_method' => 'required|string|max:100',
            'note'           => 'nullable|string|max:500',
            'payment_proof'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $agent = auth()->user();

        // Check if already pending
        if (AgentTopupRequest::byAgent($agent->id)->pending()->exists()) {
            return back()->with('error', 'You already have a pending top-up request. Please wait for it to be reviewed.');
        }

        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('topup-proofs', 'public');
        }

        AgentTopupRequest::create([
            'agent_id'       => $agent->id,
            'amount'         => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_proof'  => $proofPath,
            'note'           => $request->note,
            'status'         => 'pending',
        ]);

        return redirect()->route('agent.wallet.index')
                         ->with('success', 'Top-up request submitted. Admin will review shortly.');
    }
}
```

---

## Controller 4: Agent\BookingController

**File:** `app/Http/Controllers/Agent/BookingController.php`

```php
<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\HotelBooking;
use App\Models\FlightBooking;
use App\Models\TourBooking;
use App\Models\UmrahBooking;
use App\Models\VisaRequest;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $agent   = auth()->user();
        $agentId = $agent->id;
        $type    = $request->input('type', 'all');

        $hotels  = $type === 'all' || $type === 'hotel'  ? HotelBooking::where('agent_id', $agentId)->latest()->get()->map(fn($b) => $this->formatBooking($b, 'hotel'))   : collect();
        $flights = $type === 'all' || $type === 'flight' ? FlightBooking::where('agent_id', $agentId)->latest()->get()->map(fn($b) => $this->formatBooking($b, 'flight')) : collect();
        $tours   = $type === 'all' || $type === 'tour'   ? TourBooking::where('agent_id', $agentId)->latest()->get()->map(fn($b) => $this->formatBooking($b, 'tour'))     : collect();
        $umrah   = $type === 'all' || $type === 'umrah'  ? UmrahBooking::where('agent_id', $agentId)->latest()->get()->map(fn($b) => $this->formatBooking($b, 'umrah'))   : collect();

        $bookings = $hotels->merge($flights)->merge($tours)->merge($umrah)
                           ->sortByDesc('created_at')
                           ->values();

        return view('agent.bookings.index', compact('bookings', 'type'));
    }

    public function show(string $type, int $id)
    {
        $agentId = auth()->id();

        $booking = match($type) {
            'hotel'  => HotelBooking::where('agent_id', $agentId)->findOrFail($id),
            'flight' => FlightBooking::where('agent_id', $agentId)->findOrFail($id),
            'tour'   => TourBooking::where('agent_id', $agentId)->findOrFail($id),
            'umrah'  => UmrahBooking::where('agent_id', $agentId)->findOrFail($id),
            'visa'   => VisaRequest::where('agent_id', $agentId)->findOrFail($id),
            default  => abort(404),
        };

        return view('agent.bookings.show', compact('booking', 'type'));
    }

    private function formatBooking($booking, string $type): array
    {
        return array_merge($booking->toArray(), ['booking_type' => $type]);
    }
}
```

---

## Controller 5: Agent\HotelController

**File:** `app/Http/Controllers/Agent/HotelController.php`

```php
<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Models\AgentWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HotelController extends Controller
{
    public function index()
    {
        return view('agent.hotels.index');
    }

    public function search(Request $request)
    {
        $agent = auth()->user();
        $hotels = collect();

        // Manual hotels (if permitted)
        if ($agent->hasPermission('hotels.manual')) {
            $query = Hotel::with(['images', 'location', 'roomTypes'])->where('status', 1);

            if ($request->filled('location')) {
                $query->whereHas('location', fn($q) => $q->where('city', 'like', '%' . $request->location . '%'));
            }

            $hotels = $query->get()->map(fn($h) => array_merge($h->toArray(), ['source' => 'manual']));
        }

        // API hotels handled via frontend AJAX (same as user panel)
        // Pass permission flag to view so JS can decide to call API or not
        $canUseApi = $agent->hasPermission('hotels.api');

        return view('agent.hotels.search', compact('hotels', 'canUseApi', 'request'));
    }

    public function details(int $id)
    {
        $hotel = Hotel::with(['images', 'roomTypes.amenities', 'amenities', 'policies', 'location'])->findOrFail($id);
        $agent = auth()->user();
        $wallet = $agent->wallet;

        return view('agent.hotels.details', compact('hotel', 'agent', 'wallet'));
    }

    public function bookingForm(Request $request, int $id)
    {
        $hotel  = Hotel::with(['roomTypes', 'location'])->findOrFail($id);
        $agent  = auth()->user();
        $wallet = $agent->wallet;

        return view('agent.hotels.booking', compact('hotel', 'agent', 'wallet', 'request'));
    }

    public function confirmBooking(Request $request)
    {
        $request->validate([
            'hotel_id'       => 'required|exists:hotels,id',
            'room_type_id'   => 'required',
            'check_in'       => 'required|date|after_or_equal:today',
            'check_out'      => 'required|date|after:check_in',
            'rooms'          => 'required|integer|min:1',
            'adults'         => 'required|integer|min:1',
            'children'       => 'nullable|integer|min:0',
            'guest_name'     => 'required|string|max:255',
            'guest_email'    => 'required|email',
            'guest_phone'    => 'required|string|max:30',
            'total_amount'   => 'required|numeric|min:1',
        ]);

        $agent  = auth()->user();
        $wallet = $agent->wallet;

        if (!$wallet || !$wallet->hasSufficientBalance($request->total_amount)) {
            return back()->with('error', 'Insufficient wallet balance. Available: PKR ' . number_format($wallet?->balance ?? 0, 2) . ', Required: PKR ' . number_format($request->total_amount, 2));
        }

        $hotel = Hotel::findOrFail($request->hotel_id);
        $ref   = 'HTL-' . strtoupper(Str::random(8));

        DB::transaction(function () use ($request, $agent, $wallet, $hotel, $ref) {
            // Save booking
            $booking = HotelBooking::create([
                'agent_id'     => $agent->id,
                'booked_via'   => 'agent',
                'booking_code' => $ref,
                'hotel_name'   => $hotel->name,
                'hotel_id'     => $hotel->id,
                'check_in'     => $request->check_in,
                'check_out'    => $request->check_out,
                'rooms'        => $request->rooms,
                'adults'       => $request->adults,
                'children'     => $request->children ?? 0,
                'guest_name'   => $request->guest_name,
                'guest_email'  => $request->guest_email,
                'guest_phone'  => $request->guest_phone,
                'total_fare'   => $request->total_amount,
                'payment_state'=> 'paid', // paid via wallet
                'status'       => 'confirmed',
                'supplier'     => 'manual',
            ]);

            // Deduct from wallet
            $wallet->debit(
                amount:      $request->total_amount,
                note:        'Hotel Booking: ' . $hotel->name,
                performedBy: $agent->id,
                bookingType: 'hotel',
                bookingId:   $booking->id,
                reference:   $ref,
            );
        });

        return redirect()->route('agent.hotels.invoice', $ref)
                         ->with('success', 'Booking confirmed! Reference: ' . $ref);
    }

    public function invoice(string $ref)
    {
        $booking = HotelBooking::where('booking_code', $ref)
                               ->where('agent_id', auth()->id())
                               ->firstOrFail();

        return view('agent.hotels.invoice', compact('booking'));
    }
}
```

---

## Controller 6: Agent\ProfileController

**File:** `app/Http/Controllers/Agent/ProfileController.php`

```php
<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $agent = auth()->user();
        return view('agent.profile.index', compact('agent'));
    }

    public function update(Request $request)
    {
        $agent = auth()->user();

        $request->validate([
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'phone'           => 'nullable|string|max:30',
            'company_name'    => 'nullable|string|max:255',
            'company_phone'   => 'nullable|string|max:30',
            'company_address' => 'nullable|string',
        ]);

        $agent->update($request->only([
            'first_name', 'last_name', 'phone',
            'company_name', 'company_phone', 'company_address',
        ]));

        return back()->with('success', 'Profile updated.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        $agent = auth()->user();

        if (!Hash::check($request->current_password, $agent->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $agent->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed successfully.');
    }

    public function uploadLogo(Request $request)
    {
        $request->validate(['logo' => 'required|image|max:2048']);

        $agent = auth()->user();
        $path  = $request->file('logo')->store('agent-logos', 'public');
        $agent->update(['company_logo' => $path]);

        return back()->with('success', 'Company logo updated.');
    }
}
```
