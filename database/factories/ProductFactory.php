<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
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
        ];
    }

    /**
     * Adjunta una categoría por defecto si el producto no tiene ninguna tras la creación.
     * Garantiza que todo producto de test tenga al menos una categoría sin requerir
     * que los tests la pasen explícitamente.
     *
     * @return static
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            if ($product->categories()->doesntExist()) {
                $cat = Category::factory()->create(['user_id' => $product->user_id]);
                $product->categories()->attach($cat->id);
            }
        });
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
