<?php

namespace Database\Factories;

use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 *
 * @author AyrtonAlania
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'role'            => $this->faker->randomElement(['user', 'assistant']),
            'content'         => $this->faker->sentence(),
            'tokens_used'     => $this->faker->numberBetween(10, 100),
        ];
    }
}
