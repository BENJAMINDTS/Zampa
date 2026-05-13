<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Zone>
 */
class ZoneFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'name'       => fake()->randomElement(['Terraza', 'Comedor interior', 'Barra', 'VIP', 'Jardín']),
            'color'      => '#' . fake()->hexColor(),
            'position_x' => fake()->numberBetween(0, 400),
            'position_y' => fake()->numberBetween(0, 300),
            'width'      => fake()->numberBetween(150, 400),
            'height'     => fake()->numberBetween(100, 300),
            'rotation'   => 0,
        ];
    }
}
