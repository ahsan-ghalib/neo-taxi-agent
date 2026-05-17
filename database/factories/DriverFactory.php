<?php

namespace Database\Factories;

use App\Enums\AvailabilityStatus;
use App\Enums\OnboardingStatus;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Driver>
 */
class DriverFactory extends Factory
{
    public function definition(): array
    {
        $isOnline = fake()->boolean(40);

        return [
            'user_id' => User::factory()->driver(),
            'license_number' => strtoupper(fake()->bothify('??-########')),
            'license_expiry' => fake()->dateTimeBetween('+6 months', '+3 years')->format('Y-m-d'),
            'onboarding_status' => OnboardingStatus::Approved,
            'availability_status' => $isOnline ? AvailabilityStatus::Online : AvailabilityStatus::Offline,
            'current_lat' => $isOnline ? fake()->latitude(48.8, 48.9) : null,
            'current_lng' => $isOnline ? fake()->longitude(2.3, 2.4) : null,
            'rating' => fake()->randomFloat(2, 3.5, 5.0),
            'total_rides' => fake()->numberBetween(0, 2000),
            'is_online' => $isOnline,
            'last_seen_at' => fake()->dateTimeThisMonth(),
        ];
    }

    public function online(): static
    {
        return $this->state(fn () => [
            'is_online' => true,
            'availability_status' => AvailabilityStatus::Online,
            'current_lat' => fake()->latitude(48.8, 48.9),
            'current_lng' => fake()->longitude(2.3, 2.4),
        ]);
    }

    public function offline(): static
    {
        return $this->state(fn () => [
            'is_online' => false,
            'availability_status' => AvailabilityStatus::Offline,
            'current_lat' => null,
            'current_lng' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'onboarding_status' => OnboardingStatus::Pending,
            'is_online' => false,
            'availability_status' => AvailabilityStatus::Offline,
        ]);
    }

    public function busy(): static
    {
        return $this->state(fn () => [
            'is_online' => true,
            'availability_status' => AvailabilityStatus::Busy,
        ]);
    }
}
