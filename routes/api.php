<?php

use App\Http\Controllers\TourCityController;
use App\Http\Controllers\TourCountryController;
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

Route::get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/tour-country', [TourCountryController::class, 'getTourCountries']);
Route::get('/tour-country/{countrySlug}', [TourCountryController::class, 'getTourCountry']);

Route::middleware('auth:sanctum')->group(
    function () {
        Route::post('/tour-country', [TourCountryController::class, 'addTourCountry']);
        Route::put('/tour-country/{country}', [TourCountryController::class, 'updateTourCountry']);
        Route::delete('/tour-country/{country}', [TourCountryController::class, 'deleteTourCountry']);

        Route::get('/tour-city', [TourCityController::class, 'getTourCities']);
        Route::post('/tour-city', [TourCityController::class, 'addTourCity']);
        Route::put('/tour-city/{city}', [TourCityController::class, 'updateTourCity']);
        Route::delete('/tour-city/{city}', [TourCityController::class, 'deleteTourCity']);
    }
);

