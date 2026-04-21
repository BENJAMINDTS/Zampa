<?php

namespace Database\Factories;

use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'role'            => fake()->randomElement(['user', 'assistant']),
            'content'         => fake()->sentence(),
            'tokens_used'     => fake()->numberBetween(10, 100),
        ];
    }
}
