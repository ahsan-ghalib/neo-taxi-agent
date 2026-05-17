<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\BookingStatusLog;
use App\Models\RideBooking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingStatusLog>
 */
class BookingStatusLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => RideBooking::factory(),
            'old_status' => null,
            'new_status' => BookingStatus::Pending,
            'changed_by' => null,
            'metadata' => null,
        ];
    }

    public function transition(BookingStatus $from, BookingStatus $to): static
    {
        return $this->state(fn () => [
            'old_status' => $from,
            'new_status' => $to,
        ]);
    }
}
