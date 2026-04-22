<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Table>
 */
class TableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Genera mesas con coordenadas X/Y para el plano visual
     * y un hash único (UUID) para simular el código QR.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'name'        => 'Mesa ' . $this->faker->unique()->numberBetween(1, 100),
            'unique_hash' => Str::uuid(),
            'status'      => 'free',
            'position_x'  => 0,
            'position_y'  => 0,
            'width'       => 100,
            'height'      => 100,
            'shape'       => 'square',
            'rotation'    => 0,
        ];
    }
}
