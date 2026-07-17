# Agent B2B — Part 3: Middleware & Routes (Actual Code)

---

## Middleware 1: AgentMiddleware

**File:** `app/Http/Middleware/AgentMiddleware.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AgentMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Not logged in
        if (!auth()->check()) {
            return redirect()->route('agent.login');
        }

        $user = auth()->user();

        // Not an agent
        if (!$user->isAgent()) {
            abort(403, 'Access denied. Agent account required.');
        }

        // Pending approval
        if ($user->approval_status === 'pending') {
            return redirect()->route('agent.pending');
        }

        // Suspended
        if ($user->approval_status === 'suspended') {
            return redirect()->route('agent.suspended');
        }

        return $next($request);
    }
}
```

---

## Middleware 2: AgentPermissionMiddleware

**File:** `app/Http/Middleware/AgentPermissionMiddleware.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AgentPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permissionKey): Response
    {
        $user = auth()->user();

        if (!$user || !$user->hasPermission($permissionKey)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Permission denied.'], 403);
            }
            return response()->view('agent.403', ['permission' => $permissionKey], 403);
        }

        return $next($request);
    }
}
```

---

## Register Middleware

**File:** `bootstrap/app.php` — Replace existing withMiddleware block:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
        \App\Http\Middleware\SetLocale::class,
    ]);

    $middleware->alias([
        'admin'            => \App\Http\Middleware\AdminMiddleware::class,
        'agent'            => \App\Http\Middleware\AgentMiddleware::class,
        'agent.permission' => \App\Http\Middleware\AgentPermissionMiddleware::class,
    ]);
})
```

---

## Routes File: routes/agent.php

**File:** `routes/agent.php` (new file)

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Agent\DashboardController;
use App\Http\Controllers\Agent\ProfileController;
use App\Http\Controllers\Agent\WalletController;
use App\Http\Controllers\Agent\BookingController;
use App\Http\Controllers\Agent\HotelController;
use App\Http\Controllers\Agent\FlightController;
use App\Http\Controllers\Agent\TourController;
use App\Http\Controllers\Agent\UmrahController;
use App\Http\Controllers\Agent\VisaController;
use App\Http\Controllers\Agent\AuthController;
use App\Http\Controllers\Agent\CustomerController;

// ─── Agent Auth Routes (no middleware) ───────────────────────────────────────
Route::prefix('agent')->name('agent.')->group(function () {

    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Status pages (auth but not active check)
    Route::middleware('auth')->group(function () {
        Route::get('/pending',   fn() => view('agent.pending'))->name('pending');
        Route::get('/suspended', fn() => view('agent.suspended'))->name('suspended');
    });

    // ─── Protected Agent Routes ───────────────────────────────────────────────
    Route::middleware('agent')->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Profile
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/',         [ProfileController::class, 'index'])->name('index');
            Route::post('/update',  [ProfileController::class, 'update'])->name('update');
            Route::post('/password',[ProfileController::class, 'changePassword'])->name('password');
            Route::post('/logo',    [ProfileController::class, 'uploadLogo'])->name('logo');
        });

        // Wallet
        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::middleware('agent.permission:wallet.view')->group(function () {
                Route::get('/',              [WalletController::class, 'index'])->name('index');
                Route::get('/transactions',  [WalletController::class, 'transactions'])->name('transactions');
            });
            Route::middleware('agent.permission:wallet.request')->group(function () {
                Route::get('/topup',         [WalletController::class, 'topupForm'])->name('topup');
                Route::post('/topup',        [WalletController::class, 'topupSubmit'])->name('topup.submit');
            });
        });

        // ─── My Customers (Agent ke registered clients) ───────────────────────────
        // Yeh woh users hain jinhe agent ne create kiya (parent_agent_id = agent.id)
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/',              [CustomerController::class, 'index'])->name('index');
            Route::get('/create',        [CustomerController::class, 'create'])->name('create');
            Route::post('/',             [CustomerController::class, 'store'])->name('store');
            Route::get('/{customer}',    [CustomerController::class, 'show'])->name('show');
            Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
            Route::put('/{customer}',    [CustomerController::class, 'update'])->name('update');
        });

        // All Bookings
        Route::prefix('bookings')->name('bookings.')->group(function () {
            Route::get('/',        [BookingController::class, 'index'])->name('index');
            Route::get('/{type}/{id}', [BookingController::class, 'show'])->name('show')
                ->where('type', 'hotel|flight|tour|umrah|visa');
        });

        // ─── Hotels Module ────────────────────────────────────────────────────
        Route::middleware('agent.permission:module.hotels')
            ->prefix('hotels')->name('hotels.')->group(function () {

            Route::get('/',                     [HotelController::class, 'index'])->name('index');
            Route::get('/search',               [HotelController::class, 'search'])->name('search');
            Route::get('/details/{id}/{name?}', [HotelController::class, 'details'])->name('details');
            Route::get('/booking/{id}',         [HotelController::class, 'bookingForm'])->name('booking');
            Route::post('/booking/confirm',     [HotelController::class, 'confirmBooking'])->name('booking.confirm');
            Route::get('/invoice/{ref}',        [HotelController::class, 'invoice'])->name('invoice');
        });

        // ─── Flights Module ───────────────────────────────────────────────────
        Route::middleware('agent.permission:module.flights')
            ->prefix('flights')->name('flights.')->group(function () {

            Route::get('/',                     [FlightController::class, 'index'])->name('index');
            Route::get('/search',               [FlightController::class, 'search'])->name('search');
            Route::get('/results',              [FlightController::class, 'results'])->name('results');
            Route::post('/booking',             [FlightController::class, 'booking'])->name('booking');
            Route::post('/booking/confirm',     [FlightController::class, 'confirmBooking'])->name('booking.confirm');
            Route::get('/invoice/{ref}',        [FlightController::class, 'invoice'])->name('invoice');
        });

        // ─── Tours Module ─────────────────────────────────────────────────────
        Route::middleware('agent.permission:module.tours')
            ->prefix('tours')->name('tours.')->group(function () {

            Route::get('/',                     [TourController::class, 'index'])->name('index');
            Route::get('/search',               [TourController::class, 'search'])->name('search');
            Route::get('/details/{slug}',       [TourController::class, 'details'])->name('details');
            Route::get('/booking/{id}',         [TourController::class, 'bookingForm'])->name('booking');
            Route::post('/booking/confirm',     [TourController::class, 'confirmBooking'])->name('booking.confirm');
            Route::get('/invoice/{ref}',        [TourController::class, 'invoice'])->name('invoice');
        });

        // ─── Umrah Module ─────────────────────────────────────────────────────
        Route::middleware('agent.permission:module.umrah')
            ->prefix('umrah')->name('umrah.')->group(function () {

            Route::get('/',                     [UmrahController::class, 'index'])->name('index');
            Route::get('/search',               [UmrahController::class, 'search'])->name('search');
            Route::get('/details/{slug}',       [UmrahController::class, 'details'])->name('details');
            Route::get('/booking/{id}',         [UmrahController::class, 'bookingForm'])->name('booking');
            Route::post('/booking/confirm',     [UmrahController::class, 'confirmBooking'])->name('booking.confirm');
            Route::get('/invoice/{ref}',        [UmrahController::class, 'invoice'])->name('invoice');
        });

        // ─── Visa Module ──────────────────────────────────────────────────────
        Route::middleware('agent.permission:module.visa')
            ->prefix('visa')->name('visa.')->group(function () {

            Route::get('/',           [VisaController::class, 'index'])->name('index');
            Route::get('/apply',      [VisaController::class, 'form'])->name('apply');
            Route::post('/apply',     [VisaController::class, 'submit'])->name('submit');
            Route::get('/status/{id}',[VisaController::class, 'status'])->name('status');
        });
    });
});
```

---

## Register routes/agent.php in bootstrap/app.php

**File:** `bootstrap/app.php` — withRouting section update:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
    then: function () {
        Route::middleware('web')
            ->prefix('admin')
            ->group(base_path('routes/admin.php'));

        Route::middleware('web')
            ->group(base_path('routes/agent.php'));
    }
)
```

---

## Admin Routes to Add (in routes/admin.php)

Add these inside the existing `Route::middleware(['auth', 'admin'])->group(...)` block:

```php
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\AgentWalletController;
use App\Http\Controllers\Admin\TopupRequestController;

// ─── Agent Management ─────────────────────────────────────────────────────────
Route::prefix('agents')->name('admin.agents.')->group(function () {
    Route::get('/',                          [AgentController::class, 'index'])->name('index');
    Route::get('/create',                    [AgentController::class, 'create'])->name('create');
    Route::post('/',                         [AgentController::class, 'store'])->name('store');
    Route::get('/{agent}',                   [AgentController::class, 'show'])->name('show');
    Route::get('/{agent}/edit',              [AgentController::class, 'edit'])->name('edit');
    Route::put('/{agent}',                   [AgentController::class, 'update'])->name('update');
    Route::delete('/{agent}',                [AgentController::class, 'destroy'])->name('destroy');

    Route::post('/{agent}/approve',          [AgentController::class, 'approve'])->name('approve');
    Route::post('/{agent}/suspend',          [AgentController::class, 'suspend'])->name('suspend');
    Route::post('/{agent}/activate',         [AgentController::class, 'activate'])->name('activate');

    Route::get('/{agent}/permissions',       [AgentController::class, 'permissions'])->name('permissions');
    Route::post('/{agent}/permissions',      [AgentController::class, 'savePermissions'])->name('permissions.save');

    Route::get('/{agent}/wallet',            [AgentWalletController::class, 'index'])->name('wallet');
    Route::post('/{agent}/wallet/credit',    [AgentWalletController::class, 'credit'])->name('wallet.credit');
    Route::post('/{agent}/wallet/debit',     [AgentWalletController::class, 'debit'])->name('wallet.debit');
    Route::get('/{agent}/wallet/transactions',[AgentWalletController::class, 'transactions'])->name('wallet.transactions');
});

// ─── Top-Up Requests ──────────────────────────────────────────────────────────
Route::prefix('topup-requests')->name('admin.topup.')->group(function () {
    Route::get('/',                    [TopupRequestController::class, 'index'])->name('index');
    Route::get('/{topupRequest}',      [TopupRequestController::class, 'show'])->name('show');
    Route::post('/{topupRequest}/approve', [TopupRequestController::class, 'approve'])->name('approve');
    Route::post('/{topupRequest}/reject',  [TopupRequestController::class, 'reject'])->name('reject');
});
```
