<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Asigna aleatoriamente si los pedidos de esta categoría
     * deben imprimirse en cocina o en barra.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Entrantes', 'Hamburguesas', 'Bebidas', 'Postres', 'Ensaladas']),
            'destination' => $this->faker->randomElement(['kitchen', 'bar']),
        ];
    }
}
