<?php

namespace App\Actions;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\PaymentMode;
use App\Models\BookingStatusLog;
use App\Models\RideBooking;
use App\Models\RideQuote;
use App\Models\User;
use App\Services\CustomerResolverService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Creates a confirmed booking from a valid quote.
 *
 * Flow:
 *   1. Validate quote is still valid (not expired, not already booked)
 *   2. Resolve or create the customer
 *   3. Create the booking + initial status log in a transaction
 */
class CreateBookingAction
{
    public function __construct(
        private readonly CustomerResolverService $customerResolver,
    ) {}

    public function execute(
        RideQuote $quote,
        string $customerName,
        string $customerPhone,
        PaymentMode $paymentMode,
        ?string $notes,
    ): RideBooking {
        $this->assertQuoteIsUsable($quote);

        return DB::transaction(function () use ($quote, $customerName, $customerPhone, $paymentMode, $notes) {
            $customer = $this->customerResolver->resolveOrCreate($customerName, $customerPhone);

            // Attach customer to the quote for audit trail
            $quote->update(['customer_id' => $customer->id]);

            $booking = RideBooking::create([
                'booking_code' => 'BK-'.strtoupper(Str::random(10)),
                'quote_id' => $quote->id,
                'customer_id' => $customer->id,
                'driver_id' => null,
                'vehicle_id' => null,
                'booking_source' => BookingSource::ChatGpt,
                'status' => BookingStatus::Confirmed,
                'pickup_address' => $quote->pickup_address,
                'pickup_lat' => $quote->pickup_lat,
                'pickup_lng' => $quote->pickup_lng,
                'destination_address' => $quote->destination_address,
                'destination_lat' => $quote->destination_lat,
                'destination_lng' => $quote->destination_lng,
                'scheduled_at' => $quote->pickup_time,
                'passengers' => $quote->passengers,
                'vehicle_type' => $quote->vehicle_type,
                'estimated_price' => $quote->estimated_price,
                'currency' => $quote->currency,
                'notes' => $notes,
            ]);

            // Immutable audit log entry
            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'old_status' => null,
                'new_status' => BookingStatus::Confirmed,
                'changed_by' => null, // system-initiated via AI agent
                'metadata' => [
                    'source' => 'chatgpt_agent',
                    'payment_mode' => $paymentMode->value,
                ],
            ]);

            return $booking;
        });
    }

    private function assertQuoteIsUsable(RideQuote $quote): void
    {
        if ($quote->isExpired()) {
            throw ValidationException::withMessages([
                'quote_id' => 'This quote has expired. Please request a new estimate.',
            ]);
        }

        if ($quote->hasBeenBooked()) {
            throw ValidationException::withMessages([
                'quote_id' => 'This quote has already been used for a booking.',
            ]);
        }

        if (! $quote->serviceable) {
            throw ValidationException::withMessages([
                'quote_id' => 'This route is not currently serviceable.',
            ]);
        }
    }
}
