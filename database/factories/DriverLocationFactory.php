<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\DriverLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DriverLocation>
 */
class DriverLocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'driver_id' => Driver::factory(),
            'booking_id' => null,
            'lat' => fake()->latitude(48.7, 49.0),
            'lng' => fake()->longitude(2.2, 2.5),
            'heading' => fake()->optional(0.8)->randomFloat(2, 0, 359.99),
            'speed' => fake()->optional(0.8)->randomFloat(2, 0, 90),
            'status' => 'online',
            'recorded_at' => fake()->dateTimeThisHour(),
        ];
    }
}
