<?php

namespace Database\Factories;

use App\Enums\PaymentProvider;
use App\Models\SavedPaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SavedPaymentMethod>
 */
class SavedPaymentMethodFactory extends Factory
{
    public function definition(): array
    {
        $expiryDate = fake()->dateTimeBetween('+1 month', '+4 years');

        return [
            'customer_id' => User::factory()->customer(),
            'provider' => fake()->randomElement(PaymentProvider::cases()),
            'provider_payment_method_id' => 'pm_'.Str::random(24),
            'card_brand' => fake()->randomElement(['visa', 'mastercard', 'amex']),
            'last4' => fake()->numerify('####'),
            'expiry_month' => (int) $expiryDate->format('n'),
            'expiry_year' => (int) $expiryDate->format('Y'),
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }

    public function visa(): static
    {
        return $this->state(fn () => ['card_brand' => 'visa']);
    }

    public function mastercard(): static
    {
        return $this->state(fn () => ['card_brand' => 'mastercard']);
    }
}
