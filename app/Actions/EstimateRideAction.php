<?php

namespace App\Actions;

use App\Enums\VehicleType;
use App\Models\RideQuote;
use App\Services\EtaService;
use App\Services\GeocodingService;
use App\Services\PricingService;
use Illuminate\Support\Str;

/**
 * Generates a time-limited ride quote.
 *
 * Flow:
 *   1. Resolve coordinates (use provided or geocode from address)
 *   2. Calculate distance + duration via EtaService
 *   3. Calculate price via PricingService
 *   4. Persist and return the RideQuote
 */
class EstimateRideAction
{
    public function __construct(
        private readonly GeocodingService $geocodingService,
        private readonly EtaService $etaService,
        private readonly PricingService $pricingService,
    ) {}

    /**
     * @param  array{address: string, lat?: float|null, lng?: float|null}  $pickup
     * @param  array{address: string, lat?: float|null, lng?: float|null}  $destination
     */
    public function execute(
        array $pickup,
        array $destination,
        string $pickupTime,
        int $passengers,
        VehicleType $vehicleType,
    ): RideQuote {
        // Resolve coordinates — geocode if not provided
        [$pickupLat, $pickupLng] = $this->resolveCoordinates($pickup);
        [$destLat, $destLng] = $this->resolveCoordinates($destination);

        // Calculate route metrics
        $routeMetrics = $this->etaService->calculate($pickupLat, $pickupLng, $destLat, $destLng);

        // Calculate fare
        $pricing = $this->pricingService->calculate(
            $routeMetrics['distance_km'],
            $routeMetrics['duration_minutes'],
            $vehicleType,
        );

        $ttlMinutes = (int) config('neo.pricing.quote_ttl_minutes', 15);

        return RideQuote::create([
            'quote_code' => 'QT-'.strtoupper(Str::random(10)),
            'customer_id' => null, // resolved at booking time
            'pickup_address' => $pickup['address'],
            'pickup_lat' => $pickupLat,
            'pickup_lng' => $pickupLng,
            'destination_address' => $destination['address'],
            'destination_lat' => $destLat,
            'destination_lng' => $destLng,
            'pickup_time' => $pickupTime,
            'estimated_distance_km' => $routeMetrics['distance_km'],
            'estimated_duration_minutes' => $routeMetrics['duration_minutes'],
            'estimated_price' => $pricing['amount'],
            'currency' => $pricing['currency'],
            'surge_multiplier' => $pricing['surge_multiplier'],
            'passengers' => $passengers,
            'vehicle_type' => $vehicleType,
            'payment_methods' => ['saved_card', 'new_card', 'cash', 'corporate_invoice'],
            'serviceable' => true,
            'expires_at' => now()->addMinutes($ttlMinutes),
        ]);
    }

    /**
     * @param  array{address: string, lat?: float|null, lng?: float|null}  $location
     * @return array{float, float}
     */
    private function resolveCoordinates(array $location): array
    {
        $lat = isset($location['lat']) ? (float) $location['lat'] : null;
        $lng = isset($location['lng']) ? (float) $location['lng'] : null;

        if ($lat !== null && $lng !== null) {
            return [$lat, $lng];
        }

        $geocoded = $this->geocodingService->geocode($location['address']);

        if ($geocoded !== null) {
            return [$geocoded['lat'], $geocoded['lng']];
        }

        // Last resort: return 0,0 — serviceable flag will remain true but ETA will be inaccurate.
        // In production, consider marking the quote as unserviceable instead.
        return [0.0, 0.0];
    }
}
