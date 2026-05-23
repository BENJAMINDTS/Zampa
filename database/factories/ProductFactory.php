<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'        => fake()->words(2, true),
            'description' => fake()->sentence(),
            'price'       => fake()->randomFloat(2, 3, 30),
            'is_active'   => true,
            'image'       => null,
            'user_id'     => User::factory(),
            'category_id' => Category::factory(),
        ];
    }

    /**
     * Producto con 2 variantes (Ración entera y Media ración).
     * El precio base queda en null porque el precio viene de la variante.
     *
     * @return static
     */
    public function withVariants(): static
    {
        return $this->state(['price' => null])
            ->afterCreating(function ($product) {
                ProductVariant::factory()->create([
                    'product_id' => $product->id,
                    'name'       => 'Ración entera',
                    'price'      => fake()->randomFloat(2, 10, 25),
                    'sort_order' => 0,
                ]);
                ProductVariant::factory()->create([
                    'product_id' => $product->id,
                    'name'       => 'Media ración',
                    'price'      => fake()->randomFloat(2, 5, 12),
                    'sort_order' => 1,
                ]);
            });
    }
}
