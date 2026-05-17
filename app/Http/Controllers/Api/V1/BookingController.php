<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CancelBookingAction;
use App\Actions\CreateBookingAction;
use App\Actions\GetBookingStatusAction;
use App\Enums\PaymentMode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CancelBookingRequest;
use App\Http\Requests\Api\V1\CreateBookingRequest;
use App\Models\RideBooking;
use App\Models\RideQuote;
use Illuminate\Http\JsonResponse;

/**
 * Handles booking lifecycle: create, status, cancel.
 *
 * POST   /api/v1/rides/bookings
 * GET    /api/v1/rides/bookings/{booking}/status
 * POST   /api/v1/rides/bookings/{booking}/cancel
 *
 * Route model binding resolves {booking} by the UUID primary key.
 */
class BookingController extends Controller
{
    /**
     * POST /api/v1/rides/bookings
     * Creates a confirmed booking from a valid quote.
     */
    public function store(
        CreateBookingRequest $request,
        CreateBookingAction $action,
    ): JsonResponse {
        $data = $request->validated();

        $quote = RideQuote::findOrFail($data['quote_id']);

        $booking = $action->execute(
            quote: $quote,
            customerName: $data['customer']['name'],
            customerPhone: $data['customer']['phone'],
            paymentMode: PaymentMode::from($data['payment_mode']),
            notes: $data['notes'] ?? null,
        );

        return response()->json([
            'booking_id' => $booking->booking_code,
            'status' => $booking->status->value,
            'pickup_time' => $booking->scheduled_at->toIso8601String(),
            'driver_assignment' => 'pending',
            'customer_message' => 'Your NEO ride has been confirmed. A driver will be assigned shortly.',
        ], 201);
    }

    /**
     * GET /api/v1/rides/bookings/{booking}/status
     * Returns current booking status, driver info, and ETA.
     */
    public function status(
        RideBooking $booking,
        GetBookingStatusAction $action,
    ): JsonResponse {
        return response()->json($action->execute($booking));
    }

    /**
     * POST /api/v1/rides/bookings/{booking}/cancel
     * Cancels a booking (only allowed before driver assignment).
     */
    public function cancel(
        RideBooking $booking,
        CancelBookingRequest $request,
        CancelBookingAction $action,
    ): JsonResponse {
        $data = $request->validated();

        $result = $action->execute($booking, $data['reason'] ?? null);

        return response()->json($result);
    }
}
