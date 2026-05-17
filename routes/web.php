<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Welcome to the ' . config('app.name') . ' API',
        'status' => 'success',
        'version' => '1.0.0',
        'timestamp' => now()->toDateTimeString(),
        'environment' => config('app.env'),
        'debug' => config('app.debug'),
        'timezone' => config('app.timezone'),
        'locale' => config('app.locale'),
        'currency' => config('app.currency'),
    ])->setStatusCode(200);
});
