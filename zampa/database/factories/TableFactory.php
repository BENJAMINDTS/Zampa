<?php

namespace Database\Factories;

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
            'name' => 'Mesa ' . $this->faker->unique()->numberBetween(1, 50),
            'unique_hash' => Str::uuid(),
            'status' => 'free',
            'position_x' => $this->faker->numberBetween(0, 800), // Ancho del canvas simulado
            'position_y' => $this->faker->numberBetween(0, 600), // Alto del canvas simulado
        ];
    }
}
