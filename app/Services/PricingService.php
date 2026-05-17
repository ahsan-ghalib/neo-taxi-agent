<?php

namespace App\Services;

use App\Enums\VehicleType;
use App\Models\SystemConfig;

/**
 * Calculates ride fares based on distance, duration, vehicle type, and surge.
 * Config is resolved from system_configs table first, then neo.php defaults.
 */
class PricingService
{
    /**
     * @return array{amount: float, currency: string, surge_multiplier: float}
     */
    public function calculate(
        float $distanceKm,
        int $durationMinutes,
        VehicleType $vehicleType,
    ): array {
        $type = $vehicleType->value;

        $baseFare = (float) SystemConfig::get(
            "pricing.base_fare.{$type}",
            config("neo.pricing.base_fare.{$type}", 3.50)
        );

        $perKm = (float) SystemConfig::get(
            "pricing.price_per_km.{$type}",
            config("neo.pricing.price_per_km.{$type}", 1.80)
        );

        $perMinute = (float) SystemConfig::get(
            "pricing.price_per_minute.{$type}",
            config("neo.pricing.price_per_minute.{$type}", 0.25)
        );

        $minimumFare = (float) SystemConfig::get(
            "pricing.minimum_fare.{$type}",
            config("neo.pricing.minimum_fare.{$type}", 8.00)
        );

        $surgeEnabled = (bool) SystemConfig::get('pricing.surge_enabled', false);
        $surgeMultiplier = $surgeEnabled
            ? (float) SystemConfig::get('pricing.surge_multiplier', 1.00)
            : 1.00;

        $rawFare = $baseFare + ($distanceKm * $perKm) + ($durationMinutes * $perMinute);
        $fare = max($rawFare * $surgeMultiplier, $minimumFare);

        return [
            'amount' => round($fare, 2),
            'currency' => (string) SystemConfig::get('pricing.currency', config('neo.pricing.currency', 'EUR')),
            'surge_multiplier' => $surgeMultiplier,
        ];
    }
}
