<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Generamos nombres de platos aleatorios
            'name' => $this->faker->randomElement(['Hamburguesa Clásica', 'Pizza Margarita', 'Ensalada César', 'Tacos al Pastor', 'Sushi Roll', 'Pasta Carbonara']),

            'description' => $this->faker->sentence(10), // Descripción corta

            'price' => $this->faker->randomFloat(2, 5, 25), // Precio entre 5.00 y 25.00

            'is_active' => true,

            // IMPORTANTE: Ahora la imagen es una ruta de texto (string).
            // Ponemos una ruta falsa por defecto para que no de error.
            'image' => 'products/default_food.jpg',
        ];
    }
}
