<?php

use Illuminate\Support\Facades\Route;

// License pages — defined FIRST so LicenseCheck middleware skips them
Route::get('/license-invalid', fn () => view('errors.license-invalid'))
     ->name('license.invalid');

Route::get( '/license-setup',       [\App\Http\Controllers\LicenseSetupController::class, 'index'])->name('license.setup');
Route::post('/license-setup/test',  [\App\Http\Controllers\LicenseSetupController::class, 'test'])->name('license.setup.test');
Route::post('/license-setup/save',  [\App\Http\Controllers\LicenseSetupController::class, 'save'])->name('license.setup.save');

use App\Http\Controllers\FlightsController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\VisaController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\UmrahController;
use App\Http\Controllers\User\AuthController as UserAuthController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\BookingController as UserBookingController;
use App\Http\Controllers\User\SupportController as UserSupportController;
use App\Http\Controllers\User\ProfileController as UserProfileController;
use App\Http\Controllers\TicketLookupController;

// Support ticket tracking — public, no login required. A visitor (customer,
// agent, or anyone else) looks their own ticket up by ticket number + the
// email it was raised with, same as "track my order" on most sites.
Route::get('/track-ticket',                    [TicketLookupController::class, 'index'])->name('ticket.track');
Route::post('/track-ticket',                   [TicketLookupController::class, 'lookup'])->name('ticket.lookup');
Route::get('/track-ticket/{ticket:ticket_number}',        [TicketLookupController::class, 'show'])->name('ticket.show');
Route::post('/track-ticket/{ticket:ticket_number}/reply',  [TicketLookupController::class, 'reply'])->name('ticket.reply');

// User Auth Routes
Route::prefix('user')->name('user.')->group(function () {
    Route::get('/register',  [UserAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [UserAuthController::class, 'register'])->name('register.submit');
    Route::post('/logout',   [UserAuthController::class, 'logout'])->name('logout');

    // Protected user routes
    Route::middleware('user')->group(function () {
        Route::get('/dashboard',           [UserDashboardController::class, 'index'])->name('dashboard');
        Route::get('/bookings',            [UserBookingController::class, 'index'])->name('bookings.index');
        Route::get('/support',             [UserSupportController::class, 'index'])->name('support.index');
        Route::post('/support',            [UserSupportController::class, 'store'])->name('support.store');
        Route::get('/support/{ticket}',    [UserSupportController::class, 'show'])->name('support.show');
        Route::post('/support/{ticket}/reply', [UserSupportController::class, 'reply'])->name('support.reply');
        Route::get('/profile',             [UserProfileController::class, 'index'])->name('profile.index');
        Route::post('/profile/update',     [UserProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/password',   [UserProfileController::class, 'changePassword'])->name('profile.password');
        Route::post('/profile/avatar',     [UserProfileController::class, 'updateAvatar'])->name('profile.avatar');
    });
});

// Admin Login Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login'); // Laravel expects 'login' route
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login'); // Keep admin.login for backward compatibility
Route::post('/signin', [AuthController::class, 'login'])->name('admin.signin.post');

// Shared 2FA step for admin/agent/user logins — role-agnostic, see TwoFactorController.
Route::get('/signin/enroll', [\App\Http\Controllers\Auth\TwoFactorController::class, 'showEnroll'])->name('2fa.enroll');
Route::post('/signin/enroll', [\App\Http\Controllers\Auth\TwoFactorController::class, 'confirmEnroll'])->name('2fa.enroll.confirm');
Route::get('/signin/verify', [\App\Http\Controllers\Auth\TwoFactorController::class, 'showVerify'])->name('2fa.show');
Route::post('/signin/verify', [\App\Http\Controllers\Auth\TwoFactorController::class, 'verify'])->name('2fa.verify');
Route::post('/set-currency', [CurrencyController::class, 'setCurrency'])->name('set.currency');



//Route::get('flight_list', function () {
//    return view('flight_list');
//});
Route::get('my_booking', function () {
    return view('my_booking');
});
Route::get('booked_successfully', function () {
    return view('booked_successfully');
});
Route::get('contact_us', function () {
    return view('contact_us');
});
Route::get('about_us', function () {
    return view('about_us');
});
Route::get('privacy_policy', function () {
    return view('privacy_policy');
});
Route::get('terms_of_use', function () {
    return view('terms_of_use');
});



// HotelController
Route::controller(HotelController::class)->prefix('hotel')->group(function () {
    // Route::get('/', 'index')->name('hotel.home');
    Route::get('/search', 'search')->name('hotel.search');
    Route::get('/details/{hotel_id}/{hotel_name}/{checkin}/{checkout}/{adults}/{chlids}/{rooms}/{supplier_name}', 'details')->name('hotel.details');
    Route::post('/hotel_booking', 'hotel_booking')->name('hotel_booking');
    Route::post('booking',  'booking')->name('booking');
    Route::get('/invoice/{booking_ref}', 'invoice')->name('hotel.invoice');
    Route::get('/demomsg/{booking_ref}', 'hotelbooking')->name('hotel.hotelbooking');
    Route::get('/payment/success',  [HotelController::class, 'payment_success'])->name('payment_success');
});

Route::get('/payment/hotel/{gateway_name}/{booking_ref}',  [HotelController::class, 'payment'])->name('payment.hotel');


// Airport search route for Select2
Route::get('/airports/search', [FlightsController::class, 'searchAirports'])->name('airports.search');



Route::get('/rs', [FlightsController::class, 'remove_session'])->name('remove_session');
Route::get('/flights', [FlightsController::class, 'index'])->name('flights.index');
Route::get('/rs', [FlightsController::class, 'remove_session'])->name('remove_session');
Route::get('/flights/{origin}/{destination}/{trip}/{flight_type}/{departure_date}/{return_date?}/{adult}/{child?}/{infant?}', [FlightsController::class, 'search'])->name('search');
Route::post('/flight_booking', [FlightsController::class, 'flight_booking'])->name('flight_booking');
Route::post('/flights/booking', [FlightsController::class, 'booking'])->name('flights.booking');
Route::get('/flight/invoice/{booking_ref}',  [FlightsController::class, 'invoice'])->name('flight.invoice');
Route::post('/flight/send-invoice-email/{booking_ref}', [FlightsController::class, 'sendInvoiceEmail'])->name('flight.send-invoice-email');
Route::get('/payment/flight/{gateway_name}/{booking_ref}',  [FlightsController::class, 'payment'])->name('payment');
Route::get('flight/payment/success',  [FlightsController::class, 'payment_success'])->name('payment_success');

Route::get('/hotels', [HotelController::class, 'index'])->name('hotels.index');
Route::post('/hotels/store-child-ages', [HotelController::class, 'storeChildAges'])->name('hotels.store-child-ages');
Route::get('hotels/{country}/{checkin}/{checkout}/{adult}/{child}/{room}/{nationality}', [HotelController::class, 'search']);

Route::get('/umrah', [UmrahController::class, 'index'])->name('umrah.index');
Route::get('/umrah/{origin}/{destination}/{departure_date}/{return_date}/{adult}/{child}/{infant}/{makkah_nights}/{madina_nights}', [UmrahController::class, 'umrahSearch'])->name('umrah.search');
Route::get('/umrah/{slug}/{origin}/{destination}/{departure_date}/{return_date}/{adult}/{child}/{infant}/{makkah_nights}/{madina_nights}', [UmrahController::class, 'details'])->name('umrah.details');
Route::get('/umrah/booking/{slug}/{origin}/{destination}/{departure_date}/{return_date}/{adult}/{child}/{infant}/{makkah_nights}/{madina_nights}', [UmrahController::class, 'booking'])->name('umrah.booking');
Route::post('/umrah/booking', [UmrahController::class, 'storeBooking'])->name('umrah.booking.store');
Route::get('/umrah/invoice/{booking_ref}', [UmrahController::class, 'invoice'])->name('umrah.invoice');
Route::get('/payment/umrah/{gateway_name}/{booking_ref}',  [UmrahController::class, 'payment'])->name('payment.umrah');
Route::get('umrah/payment/success',  [UmrahController::class, 'payment_success'])->name('payment_success');

// Tour Routes
Route::get('/tours', [TourController::class, 'index'])->name('tours.index');
Route::get('/tours/{location}/{type}/{startDate}/{endDate}/{adult}/{child}', [TourController::class, 'list'])->name('tours.list');
Route::get('/tours/{slug}/{location}/{type}/{startDate}/{endDate}/{adult}/{child}', [TourController::class, 'details'])->name('tours.details');
Route::get('/tour/booking/{slug}/{location}/{type}/{startDate}/{endDate}/{adult}/{child}', [TourController::class, 'booking'])->name('tour.booking');
Route::post('/tour/booking', [TourController::class, 'storeBooking'])->name('tour.booking.store');
Route::get('/tour/invoice/{booking_ref}', [TourController::class, 'invoice'])->name('tour.invoice');
Route::get('/payment/tour/{gateway_name}/{booking_ref}',  [TourController::class, 'payment'])->name('payment.tour');
Route::get('tour/payment/success',  [TourController::class, 'payment_success'])->name('payment_success');



// visa
Route::get('/visa', [VisaController::class, 'create'])->name('visa.create');
Route::post('/visa-form', [VisaController::class, 'store'])->name('visa.store');
Route::get('/visa-success', [VisaController::class, 'success'])->name('visa.success');

// contact us
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::get('/contact/thank-you', [ContactController::class, 'thankYou'])->name('contact.thank-you');

// newsletter subscription
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

// all pages
Route::get('/', [PagesController::class, 'home'])->name('home');
Route::get('/home', [PagesController::class, 'home'])->name('home');
Route::get('/blog', [PagesController::class, 'index'])->name('blog.index');
Route::get('/page/{slug}', [PagesController::class, 'staticPage'])
    ->where('slug', 'about|contact|sitemap|privacy-policy|terms-conditions');
Route::get('/{slug}', [PagesController::class, 'detail'])->name('blog.detail');


