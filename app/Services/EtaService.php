<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Calculates ETA and driving distance between two GPS coordinates.
 *
 * Strategy:
 *   1. Google Maps Distance Matrix API (accurate, real traffic)
 *   2. Haversine formula fallback (when API key missing or request fails)
 */
class EtaService
{
    /**
     * @return array{distance_km: float, duration_minutes: int}
     */
    public function calculate(
        float $originLat,
        float $originLng,
        float $destLat,
        float $destLng,
    ): array {
        $apiKey = config('neo.google_maps_api_key');

        if (! empty($apiKey)) {
            $result = $this->calculateViaGoogleMaps($originLat, $originLng, $destLat, $destLng, $apiKey);

            if ($result !== null) {
                return $result;
            }
        }

        return $this->calculateViaHaversine($originLat, $originLng, $destLat, $destLng);
    }

    /**
     * @return array{distance_km: float, duration_minutes: int}|null
     */
    private function calculateViaGoogleMaps(
        float $originLat,
        float $originLng,
        float $destLat,
        float $destLng,
        string $apiKey,
    ): ?array {
        try {
            $response = Http::timeout(config('neo.google_maps.timeout', 5))
                ->connectTimeout(config('neo.google_maps.connect_timeout', 3))
                ->retry(2, 500)
                ->get(config('neo.google_maps.distance_matrix_url'), [
                    'origins' => "{$originLat},{$originLng}",
                    'destinations' => "{$destLat},{$destLng}",
                    'mode' => 'driving',
                    'traffic_model' => 'best_guess',
                    'departure_time' => 'now',
                    'key' => $apiKey,
                ]);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            $element = $data['rows'][0]['elements'][0] ?? null;

            if (($element['status'] ?? '') !== 'OK') {
                return null;
            }

            $distanceMeters = $element['distance']['value'];
            // Use duration_in_traffic when available (requires departure_time)
            $durationSeconds = $element['duration_in_traffic']['value']
                ?? $element['duration']['value'];

            return [
                'distance_km' => round($distanceMeters / 1000, 2),
                'duration_minutes' => (int) ceil($durationSeconds / 60),
            ];
        } catch (\Throwable $e) {
            Log::warning('EtaService: Google Maps failed, falling back to Haversine.', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Haversine great-circle distance + average speed assumption.
     *
     * @return array{distance_km: float, duration_minutes: int}
     */
    private function calculateViaHaversine(
        float $originLat,
        float $originLng,
        float $destLat,
        float $destLng,
    ): array {
        $earthRadiusKm = 6371.0;

        $dLat = deg2rad($destLat - $originLat);
        $dLng = deg2rad($destLng - $originLng);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($originLat)) * cos(deg2rad($destLat)) * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distanceKm = round($earthRadiusKm * $c, 2);

        // Apply a 1.35 road factor to convert straight-line to road distance
        $roadDistanceKm = $distanceKm * 1.35;
        $avgSpeedKmh = config('neo.dispatch.average_speed_kmh', 30);
        $durationMinutes = (int) ceil(($roadDistanceKm / $avgSpeedKmh) * 60);

        return [
            'distance_km' => round($roadDistanceKm, 2),
            'duration_minutes' => $durationMinutes,
        ];
    }
}
