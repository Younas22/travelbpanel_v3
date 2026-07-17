<?php

use App\Http\Controllers\API\AmadeusEnterpriseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\FlightsController;
use App\Http\Controllers\API\Hotels\HotelbedsController;
use App\Http\Controllers\API\Hotels\AgodaController;
use App\Http\Controllers\API\Hotels\WebbedsController;
use App\Http\Controllers\API\Flights\SabreController;
use App\Http\Controllers\API\Flights\AmadeusSelfController;
use App\Http\Controllers\API\Flights\DuffelController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(FlightsController::class)->group(function(){
    Route::get('flights_airports', 'flights_airports');
    // Umrah route with return date
    Route::get('umrah/{origin}/{destination}/{departureDate}/{returnDate}/{adult}/{child}/{infant}', 'umrahSearch');
    // Umrah route without return date
    Route::get('umrah/{origin}/{destination}/{departureDate}/{adult}/{child}/{infant}', 'umrahSearch');
});

Route::get('/hotel_destinations', [FlightsController::class, 'hotelDestinations']);
Route::get('/umrah-locations', [FlightsController::class, 'umrahLocations']);
Route::get('/countries', [FlightsController::class, 'countries']);
Route::get('/tour-package-types', [FlightsController::class, 'tourPackageTypes']);

/*
|--------------------------------------------------------------------------
| AmadeusEnterprise API ROUTES
|--------------------------------------------------------------------------
| These routes handle integration with the Amadeus Enterprise API.
|
*/

Route::controller(AmadeusEnterpriseController::class)->group(function(){
    Route::post('amadeus_enterprise/flight_search', 'flight_search');
});
Route::controller(AmadeusEnterpriseController::class)->group(function(){
    Route::post('amadeus_enterprise/booking', 'booking');
});
//Route::controller(AmadeusEnterpriseController::class)->group(function(){
//    Route::post('amadeus_enterprise/get_fare_calendar', 'get_fare_calendar');
//});

/*
|--------------------------------------------------------------------------
| Amadeus Self API ROUTES
|--------------------------------------------------------------------------
| These routes handle integration with the Amadeus Enterprise API.
|
*/

Route::controller(AmadeusSelfController::class)->group(function(){
    Route::post('amadeus_self/flight_search', 'flight_search');
});


/*
|--------------------------------------------------------------------------
| Sabre API ROUTES
|--------------------------------------------------------------------------
|
| These routes handle integration with the Sabre API.
| Each route corresponds to a specific endpoint used to
| search and retrieve hotel data from Sabre.
|
*/
Route::controller(SabreController::class)->group(function(){
    Route::post('sabre/flight_search', 'flight_search');
});

/*
|--------------------------------------------------------------------------
| HOTELBEDS API ROUTES
|--------------------------------------------------------------------------
|
| These routes handle integration with the Hotelbeds API.
| Each route corresponds to a specific endpoint used to
| search and retrieve hotel data from Hotelbeds.
|
*/
Route::controller(HotelbedsController::class)->group(function(){
    Route::post('hotelbeds/hotel_search', 'hotel_search');
});

Route::controller(HotelbedsController::class)->group(function(){
    Route::post('hotelbeds/hotel_details', 'hotel_details');
});

Route::controller(HotelbedsController::class)->group(function(){
    Route::post('hotelbeds/hotel_booking', 'hotel_booking');
});

/*
|--------------------------------------------------------------------------
| Agoda API ROUTES
|--------------------------------------------------------------------------
|
| These routes handle integration with the Agoda API.
| Each route corresponds to a specific endpoint used to
| search and retrieve hotel data from Agoda.
|
*/
Route::controller(AgodaController::class)->group(function(){
    Route::post('agoda/hotel_search', 'hotel_search');
});


/*
|--------------------------------------------------------------------------
| WEBBEDS API ROUTES
|--------------------------------------------------------------------------
|
| These routes handle integration with the WebBeds API.
| Each route corresponds to a specific endpoint used to
| search and retrieve hotel data from WebBeds.
|
*/
Route::controller(WebbedsController::class)->group(function(){
    Route::post('webbeds/hotel_search', 'hotel_search');
});

Route::controller(WebbedsController::class)->group(function(){
    Route::post('webbeds/hotel_details', 'hotel_details');
});

Route::controller(WebbedsController::class)->group(function(){
    Route::post('webbeds/hotel_booking', 'hotel_booking');
});

Route::controller(WebbedsController::class)->group(function(){
    Route::post('webbeds/cancel_booking', 'hotel_cancel_booking');
});



/*
|--------------------------------------------------------------------------
| Duffel API ROUTES
|--------------------------------------------------------------------------
|
| These routes handle integration with the Duffel API.
| Each route corresponds to a specific endpoint used to
| search and retrieve flights data from Duffel.
|
*/
Route::controller(DuffelController::class)->group(function(){
    Route::post('duffel/flight_search', 'flight_search');
});

Route::controller(DuffelController::class)->group(function(){
    Route::post('duffel/booking', 'booking');
});

