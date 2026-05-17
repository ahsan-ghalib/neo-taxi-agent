<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Models\BookingStatusLog;
use App\Models\RideBooking;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Cancels a booking.
 *
 * Business rules:
 *   - Cannot cancel if a driver has already been assigned (status >= driver_assigned)
 *   - Cannot cancel a booking that is already terminal (completed/cancelled/expired)
 *   - Cancellation fee is always 0 (no driver assigned by the time cancellation is allowed)
 */
class CancelBookingAction
{
    /** Statuses that block cancellation */
    private const array NON_CANCELLABLE_STATUSES = [
        BookingStatus::DriverAssigned,
        BookingStatus::DriverArriving,
        BookingStatus::DriverArrived,
        BookingStatus::TripStarted,
        BookingStatus::Completed,
        BookingStatus::Cancelled,
        BookingStatus::Expired,
    ];

    /**
     * @return array{booking_id: string, status: string, cancellation_fee: array{amount: float, currency: string}}
     */
    public function execute(RideBooking $booking, ?string $reason): array
    {
        $this->assertCancellable($booking);

        DB::transaction(function () use ($booking, $reason) {
            $oldStatus = $booking->status;

            $booking->update([
                'status' => BookingStatus::Cancelled,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason ?? 'customer_requested',
            ]);

            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'old_status' => $oldStatus,
                'new_status' => BookingStatus::Cancelled,
                'changed_by' => null, // AI agent initiated
                'metadata' => [
                    'reason' => $reason ?? 'customer_requested',
                    'source' => 'chatgpt_agent',
                ],
            ]);
        });

        return [
            'booking_id' => $booking->booking_code,
            'status' => BookingStatus::Cancelled->value,
            'cancellation_fee' => [
                'amount' => 0.00,
                'currency' => $booking->currency,
            ],
        ];
    }

    private function assertCancellable(RideBooking $booking): void
    {
        if (in_array($booking->status, self::NON_CANCELLABLE_STATUSES, true)) {
            $message = match (true) {
                $booking->status === BookingStatus::Completed => 'This ride has already been completed.',
                $booking->status === BookingStatus::Cancelled => 'This booking is already cancelled.',
                $booking->status === BookingStatus::Expired => 'This booking has expired.',
                default => 'Cancellation is not possible once a driver has been assigned. Please contact support.',
            };

            throw ValidationException::withMessages(['booking_id' => $message]);
        }
    }
}
