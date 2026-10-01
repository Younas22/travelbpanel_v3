<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Agent\AuthController;
use App\Http\Controllers\Agent\DashboardController;
use App\Http\Controllers\Agent\ProfileController;
use App\Http\Controllers\Agent\WalletController;
use App\Http\Controllers\Agent\BookingController;
use App\Http\Controllers\Admin\HotelController;
use App\Http\Controllers\Admin\RoomTypeController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\AmenityController;
use App\Http\Controllers\Admin\TourController;
use App\Http\Controllers\Admin\TourPackageTypeController;
use App\Http\Controllers\Admin\TourInclusionController;
use App\Http\Controllers\Admin\TourExclusionController;
use App\Http\Controllers\Admin\UmrahController;
use App\Http\Controllers\Admin\UmrahPackageTypeController;
use App\Http\Controllers\Admin\UmrahInclusionController;
use App\Http\Controllers\Admin\UmrahExclusionController;
use App\Http\Controllers\Agent\ThemeSettingController;

// ─── Agent Auth Routes (no middleware) ───────────────────────────────────────
Route::prefix('agent')->name('agent.')->group(function () {

    Route::post('/login',   [AuthController::class, 'login'])->name('login.submit');
    Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register'])->name('register.submit');

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
            Route::get('/',          [ProfileController::class, 'index'])->name('index');
            Route::post('/update',   [ProfileController::class, 'update'])->name('update');
            Route::post('/password', [ProfileController::class, 'changePassword'])->name('password');
            Route::post('/logo',     [ProfileController::class, 'uploadLogo'])->name('logo');
            Route::post('/photo',    [ProfileController::class, 'uploadPhoto'])->name('photo');
        });

        // Support tickets
        Route::prefix('support')->name('support.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Agent\SupportController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Agent\SupportController::class, 'store'])->name('store');
            Route::get('/{ticket}', [\App\Http\Controllers\Agent\SupportController::class, 'show'])->name('show');
            Route::post('/{ticket}/reply', [\App\Http\Controllers\Agent\SupportController::class, 'reply'])->name('reply');
        });

        // Theme / appearance settings (personal — each agent has their own)
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/theme', [ThemeSettingController::class, 'edit'])->name('theme');
            Route::post('/theme', [ThemeSettingController::class, 'update'])->name('theme.update');
            Route::post('/theme/reset', [ThemeSettingController::class, 'reset'])->name('theme.reset');
        });

        // ─── Wallet ───────────────────────────────────────────────────────────
        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::middleware('agent.permission:wallet.view')->group(function () {
                Route::get('/',             [WalletController::class, 'index'])->name('index');
                Route::get('/transactions', [WalletController::class, 'transactions'])->name('transactions');
            });
            Route::middleware('agent.permission:wallet.request')->group(function () {
                Route::get('/topup',  [WalletController::class, 'topupForm'])->name('topup');
                Route::post('/topup', [WalletController::class, 'topupSubmit'])->name('topup.submit');
            });
        });

        // ─── Bookings (view own bookings) ─────────────────────────────────────
        Route::middleware('agent.permission:bookings.view')
            ->prefix('bookings')->name('bookings.')->group(function () {
            Route::get('/', [BookingController::class, 'index'])->name('index');
            Route::get('/{type}/{id}', [BookingController::class, 'show'])->name('show')
                ->where('type', 'hotel|flight|tour|umrah|visa');
        });

        // ─── Add/Manage Hotels (uses Admin\HotelController) ───────────────────
        Route::middleware('agent.permission:hotels.add')
            ->prefix('hotels')->name('hotels.')->group(function () {
            Route::get('/',                [HotelController::class, 'index'])->name('index');
            Route::get('/create',          [HotelController::class, 'create'])->name('create');
            Route::post('/',               [HotelController::class, 'store'])->name('store');
            Route::get('/{hotel}/edit',    [HotelController::class, 'edit'])->name('edit');
            Route::put('/{hotel}',         [HotelController::class, 'update'])->name('update');
            Route::delete('/{hotel}',      [HotelController::class, 'destroy'])->name('destroy');
            Route::delete('/image/{image}',[HotelController::class, 'deleteImage'])->name('delete-image');
            Route::post('/images/reorder', [HotelController::class, 'reorderImages'])->name('reorder-images');

            // Room Types
            Route::prefix('room-types')->name('room-types.')->group(function () {
                Route::get('/create',              [RoomTypeController::class, 'create'])->name('create');
                Route::post('/',                   [RoomTypeController::class, 'store'])->name('store');
                Route::get('/{roomType}/edit',     [RoomTypeController::class, 'edit'])->name('edit');
                Route::patch('/{roomType}',        [RoomTypeController::class, 'update'])->name('update');
                Route::delete('/{roomType}',       [RoomTypeController::class, 'destroy'])->name('destroy');
                Route::delete('/image/{image}',    [RoomTypeController::class, 'deleteImage'])->name('delete-image');
                Route::post('/images/reorder',     [RoomTypeController::class, 'reorderImages'])->name('reorder-images');
            });

            // Rooms
            Route::prefix('rooms')->name('rooms.')->group(function () {
                Route::get('/create',        [RoomController::class, 'create'])->name('create');
                Route::post('/',             [RoomController::class, 'store'])->name('store');
                Route::get('/{room}/edit',   [RoomController::class, 'edit'])->name('edit');
                Route::patch('/{room}',      [RoomController::class, 'update'])->name('update');
                Route::delete('/{room}',     [RoomController::class, 'destroy'])->name('destroy');
            });

            // Amenities
            Route::prefix('amenities')->name('amenities.')->group(function () {
                Route::get('/',                [AmenityController::class, 'index'])->name('index');
                Route::post('/',               [AmenityController::class, 'store'])->name('store');
                Route::patch('/{amenity}',     [AmenityController::class, 'update'])->name('update');
                Route::delete('/{amenity}',    [AmenityController::class, 'destroy'])->name('destroy');
            });
        });

        // ─── Add/Manage Tour Packages (uses Admin\TourController) ─────────────
        Route::middleware('agent.permission:tours.add')
            ->prefix('tours')->name('tours.')->group(function () {
            Route::get('/',                        [TourController::class, 'index'])->name('index');
            Route::get('/create',                  [TourController::class, 'create'])->name('create');
            Route::post('/',                       [TourController::class, 'store'])->name('store');
            Route::get('/{tour}/edit',             [TourController::class, 'edit'])->name('edit');
            Route::put('/{tour}',                  [TourController::class, 'update'])->name('update');
            Route::delete('/{tour}',               [TourController::class, 'destroy'])->name('destroy');
            Route::delete('/images/{image}',       [TourController::class, 'deleteImage'])->name('image.destroy');

            // Package Types
            Route::prefix('package-types')->name('package-types.')->group(function () {
                Route::get('/',                              [TourPackageTypeController::class, 'index'])->name('index');
                Route::post('/',                             [TourPackageTypeController::class, 'store'])->name('store');
                Route::patch('/{packageType}',               [TourPackageTypeController::class, 'update'])->name('update');
                Route::delete('/{packageType}',              [TourPackageTypeController::class, 'destroy'])->name('destroy');
                Route::patch('/{packageType}/toggle-status', [TourPackageTypeController::class, 'toggleStatus'])->name('toggle-status');
            });

            // Inclusions
            Route::prefix('inclusions')->name('inclusions.')->group(function () {
                Route::get('/',                    [TourInclusionController::class, 'index'])->name('index');
                Route::post('/',                   [TourInclusionController::class, 'store'])->name('store');
                Route::patch('/{inclusion}',       [TourInclusionController::class, 'update'])->name('update');
                Route::delete('/{inclusion}',      [TourInclusionController::class, 'destroy'])->name('destroy');
            });

            // Exclusions
            Route::prefix('exclusions')->name('exclusions.')->group(function () {
                Route::get('/',                    [TourExclusionController::class, 'index'])->name('index');
                Route::post('/',                   [TourExclusionController::class, 'store'])->name('store');
                Route::patch('/{exclusion}',       [TourExclusionController::class, 'update'])->name('update');
                Route::delete('/{exclusion}',      [TourExclusionController::class, 'destroy'])->name('destroy');
            });
        });

        // ─── Add/Manage Umrah Packages (uses Admin\UmrahController) ───────────
        Route::middleware('agent.permission:umrah.add')
            ->prefix('umrah')->name('umrah.')->group(function () {
            Route::get('/',                        [UmrahController::class, 'index'])->name('index');
            Route::get('/create',                  [UmrahController::class, 'create'])->name('create');
            Route::post('/',                       [UmrahController::class, 'store'])->name('store');
            Route::get('/{umrah}/edit',            [UmrahController::class, 'edit'])->name('edit');
            Route::put('/{umrah}',                 [UmrahController::class, 'update'])->name('update');
            Route::delete('/{umrah}',              [UmrahController::class, 'destroy'])->name('destroy');
            Route::delete('/images/{image}',       [UmrahController::class, 'deleteImage'])->name('image.destroy');

            // Package Types
            Route::prefix('package-types')->name('package-types.')->group(function () {
                Route::get('/',                              [UmrahPackageTypeController::class, 'index'])->name('index');
                Route::post('/',                             [UmrahPackageTypeController::class, 'store'])->name('store');
                Route::patch('/{packageType}',               [UmrahPackageTypeController::class, 'update'])->name('update');
                Route::delete('/{packageType}',              [UmrahPackageTypeController::class, 'destroy'])->name('destroy');
                Route::patch('/{packageType}/toggle-status', [UmrahPackageTypeController::class, 'toggleStatus'])->name('toggle-status');
            });

            // Inclusions
            Route::prefix('inclusions')->name('inclusions.')->group(function () {
                Route::get('/',                    [UmrahInclusionController::class, 'index'])->name('index');
                Route::post('/',                   [UmrahInclusionController::class, 'store'])->name('store');
                Route::patch('/{inclusion}',       [UmrahInclusionController::class, 'update'])->name('update');
                Route::delete('/{inclusion}',      [UmrahInclusionController::class, 'destroy'])->name('destroy');
            });

            // Exclusions
            Route::prefix('exclusions')->name('exclusions.')->group(function () {
                Route::get('/',                    [UmrahExclusionController::class, 'index'])->name('index');
                Route::post('/',                   [UmrahExclusionController::class, 'store'])->name('store');
                Route::patch('/{exclusion}',       [UmrahExclusionController::class, 'update'])->name('update');
                Route::delete('/{exclusion}',      [UmrahExclusionController::class, 'destroy'])->name('destroy');
            });
        });
    });
});
