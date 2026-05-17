<?php

namespace Database\Factories;

use App\Enums\NotificationChannel;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    /** @var array<string, string> */
    private static array $types = [
        'booking_confirmed' => 'Your booking has been confirmed',
        'driver_assigned' => 'A driver has been assigned to your ride',
        'driver_arriving' => 'Your driver is arriving',
        'trip_started' => 'Your trip has started',
        'trip_completed' => 'Your trip is complete',
        'payment_captured' => 'Payment received',
    ];

    public function definition(): array
    {
        $type = fake()->randomElement(array_keys(self::$types));

        return [
            'user_id' => User::factory(),
            'type' => $type,
            'channel' => fake()->randomElement(NotificationChannel::cases()),
            'title' => 'NEO Taxi — '.ucfirst(str_replace('_', ' ', $type)),
            'body' => self::$types[$type],
            'payload' => ['booking_id' => fake()->uuid()],
            'sent_at' => fake()->optional(0.9)->dateTimeThisMonth(),
            'read_at' => fake()->optional(0.5)->dateTimeThisMonth(),
        ];
    }

    public function unread(): static
    {
        return $this->state(fn () => ['read_at' => null]);
    }

    public function push(): static
    {
        return $this->state(fn () => ['channel' => NotificationChannel::Push]);
    }

    public function unsent(): static
    {
        return $this->state(fn () => ['sent_at' => null, 'read_at' => null]);
    }
}
