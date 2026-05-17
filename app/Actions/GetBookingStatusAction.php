<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Models\RideBooking;
use App\Services\EtaService;

/**
 * Returns the current status of a booking with driver details and ETA.
 *
 * ETA is calculated from the driver's last known GPS position to the pickup point.
 */
class GetBookingStatusAction
{
    public function __construct(
        private readonly EtaService $etaService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(RideBooking $booking): array
    {
        $response = [
            'booking_id' => $booking->booking_code,
            'status' => $booking->status->value,
        ];

        // Include driver details when one is assigned
        if ($booking->driver_id !== null) {
            $booking->loadMissing(['driver.user', 'vehicle']);

            $driver = $booking->driver;
            $vehicle = $booking->vehicle;
            $driverUser = $driver?->user;

            $response['driver'] = [
                'name' => $driverUser ? "Mr./Ms. {$driverUser->last_name}" : null,
                'phone_masked' => $driverUser ? $this->maskPhone($driverUser->phone) : null,
                'vehicle' => $vehicle ? "{$vehicle->make} {$vehicle->model}" : null,
                'plate' => $vehicle?->plate_number,
            ];

            // ETA from driver's current position to pickup
            if (
                $driver?->current_lat !== null
                && $driver->current_lng !== null
                && $booking->status->isActive()
            ) {
                $eta = $this->etaService->calculate(
                    (float) $driver->current_lat,
                    (float) $driver->current_lng,
                    (float) $booking->pickup_lat,
                    (float) $booking->pickup_lng,
                );

                $response['eta_minutes'] = $eta['duration_minutes'];

                // Latest driver location ping
                $latestLocation = $driver->locations()
                    ->latestFirst()
                    ->first(['lat', 'lng', 'recorded_at']);

                if ($latestLocation !== null) {
                    $response['driver_location'] = [
                        'lat' => (float) $latestLocation->lat,
                        'lng' => (float) $latestLocation->lng,
                        'updated_at' => $latestLocation->recorded_at?->toIso8601String(),
                    ];
                }
            }
        }

        // Include trip timestamps for completed/cancelled bookings
        if ($booking->status === BookingStatus::Completed) {
            $response['completed_at'] = $booking->completed_at?->toIso8601String();
            $response['final_price'] = [
                'amount' => (float) $booking->final_price,
                'currency' => $booking->currency,
            ];
        }

        if ($booking->status === BookingStatus::Cancelled) {
            $response['cancelled_at'] = $booking->cancelled_at?->toIso8601String();
            $response['cancellation_reason'] = $booking->cancellation_reason;
        }

        return $response;
    }

    /**
     * Masks a phone number: +49 170 1234567 → +49 *** *** 4567
     */
    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        $last4 = substr($digits, -4);
        $prefix = substr($phone, 0, 3); // e.g. +49

        return "{$prefix} *** *** {$last4}";
    }
}
