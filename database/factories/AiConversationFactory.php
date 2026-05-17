<?php

namespace Database\Factories;

use App\Enums\ConversationStatus;
use App\Models\AiConversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AiConversation>
 */
class AiConversationFactory extends Factory
{
    public function definition(): array
    {
        $startedAt = fake()->dateTimeThisMonth();

        return [
            'customer_id' => User::factory()->customer(),
            'session_id' => 'thread_'.Str::random(24),
            'provider' => 'openai',
            'model' => fake()->randomElement(['gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo']),
            'status' => ConversationStatus::Active,
            'started_at' => $startedAt,
            'ended_at' => null,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => ConversationStatus::Closed,
            'ended_at' => fake()->dateTimeBetween($attrs['started_at'], 'now'),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => ConversationStatus::Expired,
            'ended_at' => fake()->dateTimeThisMonth(),
        ]);
    }

    public function anonymous(): static
    {
        return $this->state(fn () => ['customer_id' => null]);
    }
}
