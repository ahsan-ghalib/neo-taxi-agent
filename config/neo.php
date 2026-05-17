<?php

return [

    /*
    |--------------------------------------------------------------------------
    | NEO Gateway API Key
    |--------------------------------------------------------------------------
    | Used to authenticate requests from the ChatGPT AI agent to this backend.
    | Resolved from system_configs table first; falls back to this env value.
    */
    'gateway_api_key' => env('NEO_GATEWAY_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Google Maps API
    |--------------------------------------------------------------------------
    | Used for geocoding (address → lat/lng) and ETA/distance calculations.
    | When not configured, geocoding returns null and ETA falls back to Haversine.
    */
    'google_maps_api_key' => env('GOOGLE_MAPS_API_KEY', ''),

    'google_maps' => [
        'geocoding_url' => 'https://maps.googleapis.com/maps/api/geocode/json',
        'distance_matrix_url' => 'https://maps.googleapis.com/maps/api/distancematrix/json',
        'timeout' => 5,
        'connect_timeout' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pricing Defaults
    |--------------------------------------------------------------------------
    | Fallback pricing config when system_configs table has no overrides.
    */
    'pricing' => [
        'base_fare' => [
            'standard' => 3.50,
            'business' => 6.00,
            'van' => 8.00,
        ],
        'price_per_km' => [
            'standard' => 1.80,
            'business' => 2.80,
            'van' => 2.20,
        ],
        'price_per_minute' => [
            'standard' => 0.25,
            'business' => 0.40,
            'van' => 0.30,
        ],
        'minimum_fare' => [
            'standard' => 8.00,
            'business' => 12.00,
            'van' => 15.00,
        ],
        'currency' => 'EUR',
        'quote_ttl_minutes' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dispatch Settings
    |--------------------------------------------------------------------------
    */
    'dispatch' => [
        // Radius in km to search for available drivers
        'search_radius_km' => 10,
        // Average speed assumption for Haversine ETA fallback (km/h)
        'average_speed_kmh' => 30,
    ],

];
