<?php

namespace Database\Factories;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\VehicleType;
use App\Models\Driver;
use App\Models\RideBooking;
use App\Models\RideQuote;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RideBooking>
 */
class RideBookingFactory extends Factory
{
    public function definition(): array
    {
        $vehicleType = fake()->randomElement(VehicleType::cases());
        $estimatedPrice = fake()->randomFloat(2, 8.00, 80.00);

        return [
            'booking_code' => 'BK-'.strtoupper(Str::random(8)),
            'quote_id' => RideQuote::factory(),
            'customer_id' => User::factory()->customer(),
            'driver_id' => null,
            'vehicle_id' => null,
            'booking_source' => fake()->randomElement(BookingSource::cases()),
            'status' => BookingStatus::Pending,
            'pickup_address' => fake()->streetAddress().', '.fake()->city(),
            'pickup_lat' => fake()->latitude(48.7, 49.0),
            'pickup_lng' => fake()->longitude(2.2, 2.5),
            'destination_address' => fake()->streetAddress().', '.fake()->city(),
            'destination_lat' => fake()->latitude(48.7, 49.0),
            'destination_lng' => fake()->longitude(2.2, 2.5),
            'scheduled_at' => fake()->dateTimeBetween('now', '+2 hours'),
            'started_at' => null,
            'completed_at' => null,
            'cancelled_at' => null,
            'cancellation_reason' => null,
            'passengers' => fake()->numberBetween(1, 4),
            'vehicle_type' => $vehicleType,
            'estimated_price' => $estimatedPrice,
            'final_price' => null,
            'currency' => 'EUR',
            'notes' => fake()->optional(0.2)->sentence(),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => BookingStatus::Confirmed]);
    }

    public function withDriver(): static
    {
        return $this->state(function () {
            $driver = Driver::factory()->online()->create();
            $vehicle = Vehicle::factory()->for($driver)->create();

            return [
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'status' => BookingStatus::DriverAssigned,
            ];
        });
    }

    public function completed(): static
    {
        return $this->state(function (array $attrs) {
            $started = fake()->dateTimeBetween('-2 hours', '-1 hour');
            $completed = fake()->dateTimeBetween($started, 'now');
            $finalPrice = round($attrs['estimated_price'] * fake()->randomFloat(2, 0.9, 1.1), 2);

            return [
                'status' => BookingStatus::Completed,
                'started_at' => $started,
                'completed_at' => $completed,
                'final_price' => $finalPrice,
            ];
        });
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
            'cancellation_reason' => fake()->randomElement([
                'Customer cancelled',
                'Driver unavailable',
                'No drivers in area',
                'Payment failed',
            ]),
        ]);
    }

    public function fromChatGpt(): static
    {
        return $this->state(fn () => ['booking_source' => BookingSource::ChatGpt]);
    }
}
