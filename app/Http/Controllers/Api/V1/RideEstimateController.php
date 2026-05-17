<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\EstimateRideAction;
use App\Enums\VehicleType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\EstimateRideRequest;
use Illuminate\Http\JsonResponse;

/**
 * POST /api/v1/rides/estimate
 *
 * Generates a time-limited price quote for the AI agent.
 */
class RideEstimateController extends Controller
{
    public function __invoke(
        EstimateRideRequest $request,
        EstimateRideAction $action,
    ): JsonResponse {
        $data = $request->validated();

        $quote = $action->execute(
            pickup: $data['pickup'],
            destination: $data['destination'],
            pickupTime: $data['pickup_time'],
            passengers: $data['passengers'] ?? 1,
            vehicleType: VehicleType::from($data['vehicle_type'] ?? VehicleType::Standard->value),
        );

        return response()->json([
            'quote_id' => $quote->id,
            'serviceable' => $quote->serviceable,
            'estimated_price' => [
                'amount' => (float) $quote->estimated_price,
                'currency' => $quote->currency,
            ],
            'estimated_distance_km' => (float) $quote->estimated_distance_km,
            'estimated_duration_minutes' => $quote->estimated_duration_minutes,
            'surge_multiplier' => (float) $quote->surge_multiplier,
            'payment_methods_available' => $quote->payment_methods,
            'requires_preauthorization' => false,
            'expires_at' => $quote->expires_at->toIso8601String(),
        ], 201);
    }
}
