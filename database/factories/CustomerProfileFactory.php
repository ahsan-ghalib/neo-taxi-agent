<?php

namespace Database\Factories;

use App\Models\CustomerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerProfile>
 */
class CustomerProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->customer(),
            'default_payment_method_id' => null,
            'loyalty_points' => fake()->numberBetween(0, 5000),
            'corporate_account_id' => null,
            'emergency_contact' => fake()->optional(0.6)->passthrough([
                'name' => fake()->name(),
                'phone' => fake()->e164PhoneNumber(),
                'relationship' => fake()->randomElement(['spouse', 'parent', 'sibling', 'friend']),
            ]),
            'preferences' => [
                'preferred_vehicle_type' => fake()->randomElement(['standard', 'business', 'van']),
                'language' => fake()->randomElement(['en', 'fr', 'de']),
                'notifications' => [
                    'push' => true,
                    'sms' => fake()->boolean(),
                    'email' => fake()->boolean(),
                ],
            ],
        ];
    }

    public function withLoyaltyPoints(int $points): static
    {
        return $this->state(fn () => ['loyalty_points' => $points]);
    }

    public function corporate(): static
    {
        return $this->state(fn () => [
            'corporate_account_id' => fake()->uuid(),
        ]);
    }
}
