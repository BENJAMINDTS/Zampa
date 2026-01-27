<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Crea un plan con precio y límites aleatorios.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Básico', 'Pro', 'Business']),
            'price' => $this->faker->randomFloat(2, 9, 99), // Precio float con 2 decimales
            'max_tables' => $this->faker->numberBetween(5, 50),
        ];
    }
}
