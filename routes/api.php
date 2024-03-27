<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TourBookingController;
use App\Http\Controllers\TourCityController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\TourCountryController;
use App\Http\Controllers\TourHotelController;
use App\Http\Controllers\TourPaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/auth/user', function (Request $request) {
    return $request->user();
});

Route::get('/tour-countries', [TourCountryController::class, 'getTourCountries']);
Route::get('/tour-countries/{countrySlug}', [TourCountryController::class, 'getTourCountry']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/tour-bookings', [TourBookingController::class, 'makeTourBooking']);

    Route::put('/settings/profile', [SettingsController::class, 'changeProfileSettings']);
    Route::put('/settings/security/email', [SettingsController::class, 'changeEmail']);
    Route::put('/settings/security/password', [SettingsController::class, 'changePassword']);

    Route::get('/users/{id}/tour-bookings', [TourBookingController::class, 'getByUser']);

    Route::put('/tour-bookings/{booking}/pay-remaining', [TourBookingController::class, 'payRemainingAmount']);

    Route::middleware('admin')->group(function () {
        Route::post('/tour-countries', [TourCountryController::class, 'addTourCountry']);
        Route::put('/tour-countries/{country}', [TourCountryController::class, 'updateTourCountry']);
        Route::delete('/tour-countries/{country}', [TourCountryController::class, 'deleteTourCountry']);

        Route::get('/tour-cities', [TourCityController::class, 'getTourCities']);
        Route::post('/tour-cities', [TourCityController::class, 'addTourCity']);
        Route::put('/tour-cities/{city}', [TourCityController::class, 'updateTourCity']);
        Route::delete('/tour-cities/{city}', [TourCityController::class, 'deleteTourCity']);

        Route::get('/tour-hotels', [TourHotelController::class, 'getTourHotels']);
        Route::post('/tour-hotels', [TourHotelController::class, 'addTourHotel']);
        Route::put('/tour-hotels/{hotel}', [TourHotelController::class, 'updateTourHotel']);
        Route::delete('/tour-hotels/{hotel}', [TourHotelController::class, 'deleteTourHotel']);

        Route::get('/tours', [TourController::class, 'getTours']);
        Route::post('/tours', [TourController::class, 'addTour']);
        Route::put('/tours/{tour}', [TourController::class, 'updateTour']);
        Route::delete('/tours/{tour}', [TourController::class, 'deleteTour']);

        Route::get('/tour-bookings', [TourBookingController::class, 'getTourBookings']);
        Route::put('/tour-bookings/{booking}', [TourBookingController::class, 'verifyTourBooking']);
        Route::delete('/tour-bookings/{booking}', [TourBookingController::class, 'deleteTourBooking']);

        Route::get('/tour-payments', [TourPaymentController::class, 'getTourPayments']);
        Route::delete('/tour-payments/{payment}', [TourPaymentController::class, 'deleteTourPayment']);

        Route::get('/analytics', [AnalyticsController::class, 'getAnalytics']);
    });
});
