<?php

namespace Database\Factories;

use App\Enums\PaymentMode;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\RideBooking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => RideBooking::factory(),
            'customer_id' => User::factory()->customer(),
            'provider' => fake()->randomElement(PaymentProvider::cases()),
            'payment_mode' => fake()->randomElement(PaymentMode::cases()),
            'payment_intent_id' => 'pi_'.Str::random(24),
            'amount' => fake()->randomFloat(2, 8.00, 80.00),
            'currency' => 'EUR',
            'status' => PaymentStatus::Pending,
            'paid_at' => null,
            'metadata' => null,
        ];
    }

    public function captured(): static
    {
        return $this->state(fn () => [
            'status' => PaymentStatus::Captured,
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => PaymentStatus::Failed,
            'metadata' => ['error_code' => 'card_declined', 'decline_code' => 'insufficient_funds'],
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn () => [
            'status' => PaymentStatus::Refunded,
            'paid_at' => fake()->dateTimeThisMonth(),
        ]);
    }

    public function stripe(): static
    {
        return $this->state(fn () => ['provider' => PaymentProvider::Stripe]);
    }
}
