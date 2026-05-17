<?php

namespace Database\Factories;

use App\Enums\MessageRole;
use App\Models\AiConversation;
use App\Models\AiMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiMessage>
 */
class AiMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => AiConversation::factory(),
            'role' => MessageRole::User,
            'content' => fake()->paragraph(),
            'tool_name' => null,
            'tool_payload' => null,
            'token_usage' => null,
            'latency_ms' => null,
        ];
    }

    public function assistant(): static
    {
        return $this->state(fn () => [
            'role' => MessageRole::Assistant,
            'token_usage' => fake()->numberBetween(50, 800),
            'latency_ms' => fake()->numberBetween(300, 3000),
        ]);
    }

    public function toolCall(string $toolName = 'get_ride_quote'): static
    {
        return $this->state(fn () => [
            'role' => MessageRole::Tool,
            'tool_name' => $toolName,
            'tool_payload' => [
                'arguments' => ['pickup' => 'Paris CDG', 'destination' => 'Paris Centre'],
                'result' => ['quote_code' => 'QT-EXAMPLE', 'estimated_price' => 35.50],
            ],
            'token_usage' => fake()->numberBetween(20, 200),
            'latency_ms' => fake()->numberBetween(100, 1500),
        ]);
    }
}
