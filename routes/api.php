<?php

use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\RideEstimateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| NEO Taxi API Routes — v1
|--------------------------------------------------------------------------
| All routes are protected by the 'gateway.auth' middleware which validates
| the X-NEO-API-Key header sent by the ChatGPT AI agent gateway.
|
| Rate limiting: 60 requests/minute per API key (throttle:api).
*/

Route::prefix('v1')->middleware(['gateway.auth', 'throttle:api'])->group(function () {

    // Ride estimation — generates a time-limited quote
    Route::post('rides/estimate', RideEstimateController::class)
        ->name('api.v1.rides.estimate');

    // Booking management
    Route::prefix('rides/bookings')->name('api.v1.rides.bookings.')->group(function () {
        Route::post('/', [BookingController::class, 'store'])
            ->name('store');

        Route::get('{booking}/status', [BookingController::class, 'status'])
            ->name('status');

        Route::post('{booking}/cancel', [BookingController::class, 'cancel'])
            ->name('cancel');
    });
});
