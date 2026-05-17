<?php

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    /** @var string[] */
    private static array $categories = [
        'payment',
        'driver_behaviour',
        'lost_item',
        'route_issue',
        'app_bug',
        'overcharge',
        'cancellation',
    ];

    public function definition(): array
    {
        return [
            'booking_id' => null,
            'customer_id' => User::factory()->customer(),
            'category' => fake()->randomElement(self::$categories),
            'priority' => fake()->randomElement(TicketPriority::cases()),
            'status' => TicketStatus::Open,
            'description' => fake()->paragraph(3),
            'resolved_at' => null,
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn () => [
            'status' => TicketStatus::Resolved,
            'resolved_at' => fake()->dateTimeThisMonth(),
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn () => ['priority' => TicketPriority::Urgent]);
    }

    public function inProgress(): static
    {
        return $this->state(fn () => ['status' => TicketStatus::InProgress]);
    }
}
