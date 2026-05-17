<?php

namespace Database\Factories;

use App\Enums\VehicleType;
use App\Models\RideQuote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RideQuote>
 */
class RideQuoteFactory extends Factory
{
    public function definition(): array
    {
        $distanceKm = fake()->randomFloat(2, 1.5, 40.0);
        $durationMinutes = (int) ($distanceKm * fake()->randomFloat(1, 2.5, 4.5));
        $pricePerKm = fake()->randomFloat(2, 1.5, 3.5);
        $surgeMultiplier = fake()->randomElement([1.00, 1.00, 1.00, 1.25, 1.50, 2.00]);

        return [
            'quote_code' => 'QT-'.strtoupper(Str::random(8)),
            'customer_id' => User::factory()->customer(),
            'pickup_address' => fake()->streetAddress().', '.fake()->city(),
            'pickup_lat' => fake()->latitude(48.7, 49.0),
            'pickup_lng' => fake()->longitude(2.2, 2.5),
            'destination_address' => fake()->streetAddress().', '.fake()->city(),
            'destination_lat' => fake()->latitude(48.7, 49.0),
            'destination_lng' => fake()->longitude(2.2, 2.5),
            'pickup_time' => fake()->dateTimeBetween('now', '+2 hours'),
            'estimated_distance_km' => $distanceKm,
            'estimated_duration_minutes' => $durationMinutes,
            'estimated_price' => round($distanceKm * $pricePerKm * $surgeMultiplier, 2),
            'currency' => 'EUR',
            'surge_multiplier' => $surgeMultiplier,
            'passengers' => fake()->numberBetween(1, 4),
            'vehicle_type' => fake()->randomElement(VehicleType::cases()),
            'payment_methods' => ['saved_card', 'new_card', 'cash'],
            'serviceable' => true,
            'expires_at' => now()->addMinutes(15),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subMinutes(30)]);
    }

    public function withSurge(float $multiplier = 1.5): static
    {
        return $this->state(fn (array $attrs) => [
            'surge_multiplier' => $multiplier,
            'estimated_price' => round($attrs['estimated_price'] * $multiplier, 2),
        ]);
    }

    public function unserviceable(): static
    {
        return $this->state(fn () => ['serviceable' => false]);
    }
}
