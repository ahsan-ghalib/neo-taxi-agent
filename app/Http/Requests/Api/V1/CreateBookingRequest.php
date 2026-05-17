<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\PaymentMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the create booking request from the ChatGPT AI agent.
 * The customer is resolved by name + phone (created if not found).
 */
class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quote_id' => ['required', 'string', 'exists:ride_quotes,id'],
            'customer' => ['required', 'array'],
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.phone' => ['required', 'string', 'max:30'],
            'payment_mode' => ['required', Rule::enum(PaymentMode::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
