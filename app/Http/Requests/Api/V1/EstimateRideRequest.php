<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\VehicleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the ride estimate request from the ChatGPT AI agent.
 * Coordinates are optional — if omitted, the GeocodingService resolves them.
 */
class EstimateRideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pickup' => ['required', 'array'],
            'pickup.address' => ['required', 'string', 'max:500'],
            'pickup.lat' => ['nullable', 'numeric', 'between:-90,90'],
            'pickup.lng' => ['nullable', 'numeric', 'between:-180,180'],

            'destination' => ['required', 'array'],
            'destination.address' => ['required', 'string', 'max:500'],
            'destination.lat' => ['nullable', 'numeric', 'between:-90,90'],
            'destination.lng' => ['nullable', 'numeric', 'between:-180,180'],

            'pickup_time' => ['required', 'date', 'after:now'],
            'passengers' => ['nullable', 'integer', 'min:1', 'max:8'],
            'vehicle_type' => ['nullable', Rule::enum(VehicleType::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'pickup_time.after' => 'Pickup time must be in the future.',
        ];
    }
}
