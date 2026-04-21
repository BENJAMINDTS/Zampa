<?php

namespace Database\Factories;

use App\Models\User;
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
            'name'        => fake()->randomElement(['Entrantes', 'Hamburguesas', 'Bebidas', 'Postres', 'Ensaladas']),
            'destination' => fake()->randomElement(['kitchen', 'bar']),
            'user_id'     => User::factory(),
        ];
    }
}
