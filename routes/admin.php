<?php
// routes/admin.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\TravelPartnerController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\VisaController;
use App\Http\Controllers\Admin\CurrenciesController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\UmrahController;
use App\Http\Controllers\Admin\UmrahPackageTypeController;
use App\Http\Controllers\Admin\UmrahInclusionController;
use App\Http\Controllers\Admin\UmrahExclusionController;
use App\Http\Controllers\Admin\TourController;
use App\Http\Controllers\Admin\TourPackageTypeController;
use App\Http\Controllers\Admin\TourInclusionController;
use App\Http\Controllers\Admin\TourExclusionController;
use App\Http\Controllers\Admin\ThemeSettingController;


Route::get('/test-csrf', function() {
    return view('test-csrf');
});

Route::post('/test-csrf', function() {
    return 'CSRF worked!';
})->name('test.csrf');




// Protected Admin Routes
Route::middleware(['auth', 'admin'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.index');

    // Profile Management
    Route::prefix('profile')->name('admin.profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::patch('/update', [ProfileController::class, 'update'])->name('update');
        Route::patch('/update-picture', [ProfileController::class, 'updateProfilePicture'])->name('update-picture');
        Route::delete('/remove-picture', [ProfileController::class, 'removeProfilePicture'])->name('remove-picture');
        Route::patch('/update-password', [ProfileController::class, 'updatePassword'])->name('update-password');
        Route::patch('/update-notifications', [ProfileController::class, 'updateNotifications'])->name('update-notifications');
    });

    // Bookings Management
    Route::prefix('bookings')->name('admin.bookings.')->group(function () {
        Route::get('/all', [BookingController::class, 'allBookings'])->name('all');
        Route::get('/cancelled-refunds', [BookingController::class, 'cancelledRefunds'])->name('cancelled-refunds');
        Route::get('/pending-confirmations', [BookingController::class, 'pendingConfirmations'])->name('pending-confirmations');
        Route::get('/{type}/{id}/edit', [BookingController::class, 'edit'])->name('edit');
        Route::put('/{type}/{id}', [BookingController::class, 'update'])->name('update');
        Route::delete('/{type}/{id}', [BookingController::class, 'destroy'])->name('destroy');
        Route::delete('/bulk-delete', [BookingController::class, 'bulkDestroy'])->name('bulk-delete');
    });

    // Visa Requests Routes
    Route::prefix('visa-requests')->name('admin.visa-requests.')->group(function () {
        Route::get('/', [VisaController::class, 'visaindex'])->name('visaindex');
        Route::get('/{visaRequest}', [VisaController::class, 'show'])->name('show');
        Route::get('/{visaRequest}/download/{documentType}', [VisaController::class, 'downloadDocument'])->name('download');
        Route::get('/export/csv', [VisaController::class, 'export'])->name('export');
    });

        // Admin Contact Messages Routes
    Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
        Route::get('/contact-messages', [ContactController::class, 'adminIndex'])->name('contact-messages.index');
        Route::get('/contact-messages/{id}', [ContactController::class, 'adminShow'])->name('contact-messages.show');
        Route::patch('/contact-messages/{id}/status', [ContactController::class, 'updateStatus'])->name('contact-messages.update-status');
        Route::delete('/contact-messages/{id}', [ContactController::class, 'destroy'])->name('contact-messages.destroy');
    });

    // Customers Management
    Route::prefix('customers')->name('admin.customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('/{user}', [CustomerController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::patch('/{user}', [CustomerController::class, 'update'])->name('update');
        Route::delete('/{user}', [CustomerController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-email', [CustomerController::class, 'bulkEmail'])->name('bulk-email');
        Route::get('/export/{format}', [CustomerController::class, 'export'])->name('export');
    });


    // Travel Partners Management
Route::prefix('travel-partners')->name('admin.travel-partners.')->group(function () {
    Route::get('/', [TravelPartnerController::class, 'index'])->name('index');
    Route::get('/create', [TravelPartnerController::class, 'create'])->name('create');
    Route::post('/', [TravelPartnerController::class, 'store'])->name('store');
    Route::get('/{partner}', [TravelPartnerController::class, 'show'])->name('show');
    Route::get('/{partner}/edit', [TravelPartnerController::class, 'edit'])->name('edit');
    Route::patch('/{partner}', [TravelPartnerController::class, 'update'])->name('update');
    Route::delete('/destroy/{partner}', [TravelPartnerController::class, 'destroy'])->name('destroy');
    Route::patch('/{partner}/activate', [TravelPartnerController::class, 'activate'])->name('activate');
    Route::patch('/{partner}/suspend', [TravelPartnerController::class, 'suspend'])->name('suspend');
    Route::patch('/{partner}/toggle-status', [TravelPartnerController::class, 'toggleStatus'])->name('toggle-status');
    Route::get('/module/{module}', [TravelPartnerController::class, 'getByModule'])->name('module');
});

// Modules Management
Route::prefix('modules')->name('admin.modules.')->group(function () {
    Route::get('/', [ModuleController::class, 'index'])->name('index');
    Route::get('/create', [ModuleController::class, 'create'])->name('create');
    Route::post('/', [ModuleController::class, 'store'])->name('store');
    Route::get('/{module}', [ModuleController::class, 'show'])->name('show');
    Route::get('/{module}/edit', [ModuleController::class, 'edit'])->name('edit');
    Route::patch('/{module}', [ModuleController::class, 'update'])->name('update');
    Route::delete('/{module}', [ModuleController::class, 'destroy'])->name('destroy');
    Route::patch('/{module}/update-status', [ModuleController::class, 'toggleStatus'])->name('update-status');
    Route::get('/{module}/stats', [ModuleController::class, 'getStats'])->name('stats');
    Route::get('/{module}/partners', [ModuleController::class, 'getPartners'])->name('partners');
    Route::post('/{module}/bulk-update-status', [ModuleController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
    Route::post('/reorder', [ModuleController::class, 'updateSortOrder'])->name('reorder');
});



    // Content Management
    Route::prefix('content')->name('admin.content.')->group(function () {
        // Blog Management
        Route::prefix('blog')->name('blog.')->group(function () {
            Route::get('/', [BlogController::class, 'index'])->name('index');
            Route::get('/create', [BlogController::class, 'create'])->name('create');
            Route::post('/', [BlogController::class, 'store'])->name('store');
            Route::get('/{post}', [BlogController::class, 'show'])->name('show');
            Route::get('/{post}/edit', [BlogController::class, 'edit'])->name('edit');
            Route::patch('/{post}', [BlogController::class, 'update'])->name('update');
            Route::delete('/{post}', [BlogController::class, 'destroy'])->name('destroy');
            Route::post('/upload-image', [BlogController::class, 'uploadImage'])->name('upload-image');
            // Additional routes for actions
            Route::post('/{post}/publish', [BlogController::class, 'publish'])->name('publish');
            Route::post('/{post}/cancel-schedule', [BlogController::class, 'cancelSchedule'])->name('cancel-schedule');
            Route::post('/{post}/duplicate', [BlogController::class, 'duplicate'])->name('duplicate');




        });



// Category Management
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [BlogController::class, 'getCategories'])->name('index');
            Route::post('/', [BlogController::class, 'storeCategory'])->name('store');
            Route::get('/{category}', [BlogController::class, 'getCategory'])->name('show');
            Route::put('/{category}', [BlogController::class, 'updateCategory'])->name('update');
            Route::delete('/{category}', [BlogController::class, 'deleteCategory'])->name('destroy');
            Route::post('/{category}/toggle-status', [BlogController::class, 'toggleCategoryStatus'])->name('toggle-status');
            Route::post('/update-order', [BlogController::class, 'updateCategoryOrder'])->name('update-order');
        });

        Route::prefix('newsletter')->name('newsletter.')->group(function () {
            Route::get('/', [NewsletterController::class, 'index'])->name('index');
            Route::get('/subscribers', [NewsletterController::class, 'subscribers'])->name('subscribers');
            Route::get('/subscribers/{id}', [NewsletterController::class, 'showSubscriber']);
            Route::post('/subscribers', [NewsletterController::class, 'storeSubscriber'])->name('store-subscriber');
            Route::patch('/subscribers/{id}', [NewsletterController::class, 'updateSubscriber']);
            Route::delete('/subscribers/{id}', [NewsletterController::class, 'destroySubscriber']);
            Route::post('/subscribers/bulk/{action}', [NewsletterController::class, 'bulkAction']);
            Route::post('/bulk-import', [NewsletterController::class, 'bulkImport'])->name('bulk-import');
            Route::patch('/subscribers/{id}/{action}', [NewsletterController::class, 'changeStatus']);
            Route::post('/send', [NewsletterController::class, 'send'])->name('send');
        });


    });


                   // Pages Management Routes
        Route::prefix('pages')->name('admin.pages.')->group(function () {
            Route::get('/', [PageController::class, 'index'])->name('index');
            Route::get('/create', [PageController::class, 'create'])->name('create');
            Route::post('/', [PageController::class, 'store'])->name('store');
            Route::get('/{page}/edit', [PageController::class, 'edit'])->name('edit');
            Route::patch('/{page}', [PageController::class, 'update'])->name('update');
            Route::delete('/{page}', [PageController::class, 'destroy'])->name('destroy');
            Route::get('/{page}', [PageController::class, 'show'])->name('show');

            // Additional functionality
            Route::post('/bulk-action', [PageController::class, 'bulkAction'])->name('bulk-action');
            Route::post('/upload-image', [PageController::class, 'uploadImage'])->name('upload-image');
            Route::post('/{page}/duplicate', [PageController::class, 'duplicate'])->name('duplicate');
            Route::post('/update-sort-order', [PageController::class, 'updateSortOrder'])->name('update-sort-order');
        });

    // Currencies Management Routes
    Route::prefix('currencies')->name('admin.currencies.')->group(function () {
        Route::get('/', [CurrenciesController::class, 'index'])->name('index');
        Route::get('/create', [CurrenciesController::class, 'create'])->name('create');
        Route::post('/', [CurrenciesController::class, 'store'])->name('store');
        Route::post('/toggle_default', [CurrenciesController::class, 'toggle_default'])->name('toggle_default');
        Route::post('/toggle_status', [CurrenciesController::class, 'toggle_status'])->name('toggle_status');
        Route::post('/', [CurrenciesController::class, 'store'])->name('store');
        Route::get('/{currency}/edit', [CurrenciesController::class, 'edit'])->name('edit');
        Route::patch('/{currency}', [CurrenciesController::class, 'update'])->name('update');
        Route::delete('/{currency}', [CurrenciesController::class, 'destroy'])->name('destroy');
        Route::get('/{page}', [CurrenciesController::class, 'show'])->name('show');
    });

                   // Pages Management Routes
        Route::prefix('menus')->name('admin.menus.')->group(function () {
            // Menu Management
            Route::get('/', [MenuController::class, 'index'])->name('index');
            Route::get('/create', [MenuController::class, 'create'])->name('create');
            Route::post('/', [MenuController::class, 'store'])->name('store');
            Route::get('/{menu}/edit', [MenuController::class, 'edit'])->name('edit');
            Route::put('/{menu}', [MenuController::class, 'update'])->name('update');
            Route::delete('/{menu}', [MenuController::class, 'destroy'])->name('destroy');

            // Additional Menu Routes
            Route::post('/update-sort-order', [MenuController::class, 'updateSortOrder'])->name('update-sort-order');
            Route::post('/bulk-action', [MenuController::class, 'bulkAction'])->name('bulk-action');
            Route::post('/{menu}/toggle-status', [MenuController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/get-parent-items', [MenuController::class, 'getParentItems'])->name('get-parent-items');
            Route::post('/{menu}/duplicate', [MenuController::class, 'duplicate'])->name('duplicate');
        });



    // Settings
    Route::prefix('settings')->name('admin.settings.')->group(function () {
        Route::get('/website', [SettingController::class, 'website'])->name('website');
        Route::post('/website', [SettingController::class, 'updateWebsite'])->name('website.update');
        Route::get('/email', [SettingController::class, 'email'])->name('email');
        Route::post('/email', [SettingController::class, 'updateEmail'])->name('email.update');
        Route::get('/payment', [SettingController::class, 'payment'])->name('payment');
        Route::get('/payment/{gateway}/edit', [SettingController::class, 'editPayment'])->name('payment.edit');
        Route::post('/payment', [SettingController::class, 'updatePayment'])->name('payment.update');



        // Email settings routes mein add karo
        Route::post('/settings/email/test-connection', [SettingController::class, 'testEmailConnection'])->name('email.test-connection');
        Route::post('/settings/email/send-test', [SettingController::class, 'sendTestEmail'])->name('email.send-test');

        // Theme / appearance settings
        Route::get('/theme', [ThemeSettingController::class, 'edit'])->name('theme');
        Route::post('/theme', [ThemeSettingController::class, 'update'])->name('theme.update');
        Route::post('/theme/reset', [ThemeSettingController::class, 'reset'])->name('theme.reset');
    });


        // Translation Management Routes (File-based)
        Route::prefix('content/translations')->name('admin.content.translations.')->group(function () {
            Route::get('/', [TranslationController::class, 'index'])->name('index');
            Route::get('/create', [TranslationController::class, 'create'])->name('create');
            Route::post('/', [TranslationController::class, 'store'])->name('store');
            Route::get('/{group}/edit', [TranslationController::class, 'edit'])->name('edit');
            Route::patch('/{group}', [TranslationController::class, 'update'])->name('update');
            Route::delete('/{group}', [TranslationController::class, 'destroy'])->name('destroy');

            // Additional actions
            Route::post('/{group}/add-key', [TranslationController::class, 'addKey'])->name('add-key');
            Route::delete('/{group}/delete-key/{key}', [TranslationController::class, 'deleteKey'])->name('delete-key');
            Route::get('/{lang}/{group}/export', [TranslationController::class, 'export'])->name('export');
            Route::post('/import', [TranslationController::class, 'import'])->name('import');
        });

        // Language Management Routes (Database)
        Route::prefix('settings/languages')->name('admin.settings.languages.')->group(function () {
            Route::get('/', [LanguageController::class, 'index'])->name('index');
            Route::get('/create', [LanguageController::class, 'create'])->name('create');
            Route::post('/', [LanguageController::class, 'store'])->name('store');
            Route::get('/{language}/edit', [LanguageController::class, 'edit'])->name('edit');
            Route::patch('/{language}', [LanguageController::class, 'update'])->name('update');
            Route::patch('/{language}/toggle-status', [LanguageController::class, 'toggleStatus'])->name('toggle-status');
            Route::patch('/{language}/toggle-default', [LanguageController::class, 'toggleDefault'])->name('toggle-default');
            Route::delete('/{language}', [LanguageController::class, 'destroy'])->name('destroy');
        });

    // Umrah Management
    Route::prefix('umrah')->name('admin.umrah.')->group(function () {
        // Umrah Packages
        Route::prefix('packages')->name('packages.')->group(function () {
            Route::get('/', [UmrahController::class, 'index'])->name('index');
            Route::get('/create', [UmrahController::class, 'create'])->name('create');
            Route::post('/', [UmrahController::class, 'store'])->name('store');
            Route::get('/{umrah}/edit', [UmrahController::class, 'edit'])->name('edit');
            Route::patch('/{umrah}', [UmrahController::class, 'update'])->name('update');
            Route::delete('/{umrah}', [UmrahController::class, 'destroy'])->name('destroy');
            Route::patch('/{umrah}/toggle-status', [UmrahController::class, 'toggleStatus'])->name('toggle-status');
            Route::patch('/{umrah}/toggle-featured', [UmrahController::class, 'toggleFeatured'])->name('toggle-featured');
            Route::delete('/image/{image}', [UmrahController::class, 'deleteImage'])->name('delete-image');
            Route::post('/{umrah}/approve', [UmrahController::class, 'approve'])->name('approve');
            Route::post('/{umrah}/reject',  [UmrahController::class, 'reject'])->name('reject');
        });

        // Package Types
        Route::prefix('package-types')->name('package-types.')->group(function () {
            Route::get('/', [UmrahPackageTypeController::class, 'index'])->name('index');
            Route::post('/', [UmrahPackageTypeController::class, 'store'])->name('store');
            Route::patch('/{packageType}', [UmrahPackageTypeController::class, 'update'])->name('update');
            Route::delete('/{packageType}', [UmrahPackageTypeController::class, 'destroy'])->name('destroy');
            Route::patch('/{packageType}/toggle-status', [UmrahPackageTypeController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Inclusions
        Route::prefix('inclusions')->name('inclusions.')->group(function () {
            Route::get('/', [UmrahInclusionController::class, 'index'])->name('index');
            Route::post('/', [UmrahInclusionController::class, 'store'])->name('store');
            Route::patch('/{inclusion}', [UmrahInclusionController::class, 'update'])->name('update');
            Route::delete('/{inclusion}', [UmrahInclusionController::class, 'destroy'])->name('destroy');
        });

        // Exclusions
        Route::prefix('exclusions')->name('exclusions.')->group(function () {
            Route::get('/', [UmrahExclusionController::class, 'index'])->name('index');
            Route::post('/', [UmrahExclusionController::class, 'store'])->name('store');
            Route::patch('/{exclusion}', [UmrahExclusionController::class, 'update'])->name('update');
            Route::delete('/{exclusion}', [UmrahExclusionController::class, 'destroy'])->name('destroy');
        });
    });

    // Tours Management
    Route::prefix('tours')->name('admin.tours.')->group(function () {
        // Tour Packages
        Route::prefix('packages')->name('packages.')->group(function () {
            Route::get('/', [TourController::class, 'index'])->name('index');
            Route::get('/create', [TourController::class, 'create'])->name('create');
            Route::post('/', [TourController::class, 'store'])->name('store');
            Route::get('/{tour}/edit', [TourController::class, 'edit'])->name('edit');
            Route::patch('/{tour}', [TourController::class, 'update'])->name('update');
            Route::delete('/{tour}', [TourController::class, 'destroy'])->name('destroy');
            Route::patch('/{tour}/toggle-status', [TourController::class, 'toggleStatus'])->name('toggle-status');
            Route::patch('/{tour}/toggle-featured', [TourController::class, 'toggleFeatured'])->name('toggle-featured');
            Route::delete('/image/{image}', [TourController::class, 'deleteImage'])->name('delete-image');
            Route::post('/{tour}/approve', [TourController::class, 'approve'])->name('approve');
            Route::post('/{tour}/reject',  [TourController::class, 'reject'])->name('reject');
        });

        // Package Types
        Route::prefix('package-types')->name('package-types.')->group(function () {
            Route::get('/', [TourPackageTypeController::class, 'index'])->name('index');
            Route::post('/', [TourPackageTypeController::class, 'store'])->name('store');
            Route::patch('/{packageType}', [TourPackageTypeController::class, 'update'])->name('update');
            Route::delete('/{packageType}', [TourPackageTypeController::class, 'destroy'])->name('destroy');
            Route::patch('/{packageType}/toggle-status', [TourPackageTypeController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Inclusions
        Route::prefix('inclusions')->name('inclusions.')->group(function () {
            Route::get('/', [TourInclusionController::class, 'index'])->name('index');
            Route::post('/', [TourInclusionController::class, 'store'])->name('store');
            Route::patch('/{inclusion}', [TourInclusionController::class, 'update'])->name('update');
            Route::delete('/{inclusion}', [TourInclusionController::class, 'destroy'])->name('destroy');
        });

        // Exclusions
        Route::prefix('exclusions')->name('exclusions.')->group(function () {
            Route::get('/', [TourExclusionController::class, 'index'])->name('index');
            Route::post('/', [TourExclusionController::class, 'store'])->name('store');
            Route::patch('/{exclusion}', [TourExclusionController::class, 'update'])->name('update');
            Route::delete('/{exclusion}', [TourExclusionController::class, 'destroy'])->name('destroy');
        });
    });

    // Hotel Management
    Route::prefix('hotels')->name('admin.hotels.')->group(function () {
        // Hotels
        Route::get('/', [\App\Http\Controllers\Admin\HotelController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\HotelController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\HotelController::class, 'store'])->name('store');
        Route::get('/{hotel}/edit', [\App\Http\Controllers\Admin\HotelController::class, 'edit'])->name('edit');
        Route::patch('/{hotel}', [\App\Http\Controllers\Admin\HotelController::class, 'update'])->name('update');
        Route::delete('/{hotel}', [\App\Http\Controllers\Admin\HotelController::class, 'destroy'])->name('destroy');
        Route::patch('/{hotel}/toggle-status', [\App\Http\Controllers\Admin\HotelController::class, 'toggleStatus'])->name('toggle-status');
        Route::patch('/{hotel}/toggle-featured', [\App\Http\Controllers\Admin\HotelController::class, 'toggleFeatured'])->name('toggle-featured');
        Route::delete('/image/{image}', [\App\Http\Controllers\Admin\HotelController::class, 'deleteImage'])->name('delete-image');
        Route::post('/images/reorder', [\App\Http\Controllers\Admin\HotelController::class, 'reorderImages'])->name('reorder-images');
        Route::post('/{hotel}/approve', [\App\Http\Controllers\Admin\HotelController::class, 'approve'])->name('approve');
        Route::post('/{hotel}/reject',  [\App\Http\Controllers\Admin\HotelController::class, 'reject'])->name('reject');

        // Room Types
        Route::prefix('room-types')->name('room-types.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\RoomTypeController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\RoomTypeController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\RoomTypeController::class, 'store'])->name('store');
            Route::get('/{roomType}/edit', [\App\Http\Controllers\Admin\RoomTypeController::class, 'edit'])->name('edit');
            Route::patch('/{roomType}', [\App\Http\Controllers\Admin\RoomTypeController::class, 'update'])->name('update');
            Route::delete('/{roomType}', [\App\Http\Controllers\Admin\RoomTypeController::class, 'destroy'])->name('destroy');
            Route::patch('/{roomType}/toggle-status', [\App\Http\Controllers\Admin\RoomTypeController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/image/{image}', [\App\Http\Controllers\Admin\RoomTypeController::class, 'deleteImage'])->name('delete-image');
            Route::post('/images/reorder', [\App\Http\Controllers\Admin\RoomTypeController::class, 'reorderImages'])->name('reorder-images');
        });

        // Rooms
        Route::prefix('rooms')->name('rooms.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\RoomController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\RoomController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\RoomController::class, 'store'])->name('store');
            Route::get('/{room}/edit', [\App\Http\Controllers\Admin\RoomController::class, 'edit'])->name('edit');
            Route::patch('/{room}', [\App\Http\Controllers\Admin\RoomController::class, 'update'])->name('update');
            Route::delete('/{room}', [\App\Http\Controllers\Admin\RoomController::class, 'destroy'])->name('destroy');
            Route::patch('/{room}/update-status', [\App\Http\Controllers\Admin\RoomController::class, 'updateStatus'])->name('update-status');
        });

        // Amenities
        Route::prefix('amenities')->name('amenities.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AmenityController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\AmenityController::class, 'store'])->name('store');
            Route::patch('/{amenity}', [\App\Http\Controllers\Admin\AmenityController::class, 'update'])->name('update');
            Route::delete('/{amenity}', [\App\Http\Controllers\Admin\AmenityController::class, 'destroy'])->name('destroy');
        });
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

    // ─── Agent Management ─────────────────────────────────────────────────────
    Route::prefix('agents')->name('admin.agents.')->group(function () {
        Route::get('/',                           [\App\Http\Controllers\Admin\AgentController::class, 'index'])->name('index');
        Route::get('/create',                     [\App\Http\Controllers\Admin\AgentController::class, 'create'])->name('create');
        Route::post('/',                          [\App\Http\Controllers\Admin\AgentController::class, 'store'])->name('store');
        Route::get('/{agent}',                    [\App\Http\Controllers\Admin\AgentController::class, 'show'])->name('show');
        Route::get('/{agent}/edit',               [\App\Http\Controllers\Admin\AgentController::class, 'edit'])->name('edit');
        Route::put('/{agent}',                    [\App\Http\Controllers\Admin\AgentController::class, 'update'])->name('update');
        Route::delete('/{agent}',                 [\App\Http\Controllers\Admin\AgentController::class, 'destroy'])->name('destroy');

        Route::post('/{agent}/approve',           [\App\Http\Controllers\Admin\AgentController::class, 'approve'])->name('approve');
        Route::post('/{agent}/reject',            [\App\Http\Controllers\Admin\AgentController::class, 'reject'])->name('reject');
        Route::post('/{agent}/suspend',           [\App\Http\Controllers\Admin\AgentController::class, 'suspend'])->name('suspend');
        Route::post('/{agent}/activate',          [\App\Http\Controllers\Admin\AgentController::class, 'activate'])->name('activate');

        Route::get('/{agent}/permissions',        [\App\Http\Controllers\Admin\AgentController::class, 'permissions'])->name('permissions');
        Route::post('/{agent}/permissions',       [\App\Http\Controllers\Admin\AgentController::class, 'savePermissions'])->name('permissions.save');

        Route::get('/{agent}/wallet',             [\App\Http\Controllers\Admin\AgentWalletController::class, 'index'])->name('wallet');
        Route::post('/{agent}/wallet/credit',     [\App\Http\Controllers\Admin\AgentWalletController::class, 'credit'])->name('wallet.credit');
        Route::post('/{agent}/wallet/debit',      [\App\Http\Controllers\Admin\AgentWalletController::class, 'debit'])->name('wallet.debit');
        Route::get('/{agent}/wallet/transactions',[\App\Http\Controllers\Admin\AgentWalletController::class, 'transactions'])->name('wallet.transactions');
    });

    // ─── Top-Up Requests ──────────────────────────────────────────────────────
    Route::prefix('topup-requests')->name('admin.topup.')->group(function () {
        Route::get('/',                        [\App\Http\Controllers\Admin\TopupRequestController::class, 'index'])->name('index');
        Route::get('/{topupRequest}',          [\App\Http\Controllers\Admin\TopupRequestController::class, 'show'])->name('show');
        Route::post('/{topupRequest}/approve', [\App\Http\Controllers\Admin\TopupRequestController::class, 'approve'])->name('approve');
        Route::post('/{topupRequest}/reject',  [\App\Http\Controllers\Admin\TopupRequestController::class, 'reject'])->name('reject');
    });
});
