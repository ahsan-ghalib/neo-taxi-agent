<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Resolves street addresses to GPS coordinates via Google Maps Geocoding API.
 * Results are cached for 24 hours to reduce API costs.
 * Returns null when the API key is not configured or the request fails.
 */
class GeocodingService
{
    /**
     * @return array{lat: float, lng: float}|null
     */
    public function geocode(string $address): ?array
    {
        $apiKey = config('neo.google_maps_api_key');

        if (empty($apiKey)) {
            Log::warning('GeocodingService: GOOGLE_MAPS_API_KEY not configured.', [
                'address' => $address,
            ]);

            return null;
        }

        $cacheKey = 'geocode:'.md5(mb_strtolower(trim($address)));

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($address, $apiKey) {
            try {
                $response = Http::timeout(config('neo.google_maps.timeout', 5))
                    ->connectTimeout(config('neo.google_maps.connect_timeout', 3))
                    ->retry(2, 500)
                    ->get(config('neo.google_maps.geocoding_url'), [
                        'address' => $address,
                        'key' => $apiKey,
                    ]);

                if (! $response->successful()) {
                    Log::error('GeocodingService: HTTP error.', [
                        'status' => $response->status(),
                        'address' => $address,
                    ]);

                    return null;
                }

                $data = $response->json();

                if (($data['status'] ?? '') !== 'OK' || empty($data['results'])) {
                    Log::warning('GeocodingService: No results.', [
                        'status' => $data['status'] ?? 'unknown',
                        'address' => $address,
                    ]);

                    return null;
                }

                $location = $data['results'][0]['geometry']['location'];

                return [
                    'lat' => (float) $location['lat'],
                    'lng' => (float) $location['lng'],
                ];
            } catch (\Throwable $e) {
                Log::error('GeocodingService: Exception.', [
                    'message' => $e->getMessage(),
                    'address' => $address,
                ]);

                return null;
            }
        });
    }
}
