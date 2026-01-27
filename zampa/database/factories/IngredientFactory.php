<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ingredient>
 */
class IngredientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Incluye un flag booleano para simular alérgenos (20% de probabilidad).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Tomate', 'Queso', 'Bacon', 'Lechuga', 'Salsa BBQ', 'Huevo', 'Pan', 'Pollo']),
            'is_allergen' => $this->faker->boolean(20),
        ];
    }
}
