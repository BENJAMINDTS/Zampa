<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 0;

        return [
            'product_id' => Product::factory(),
            'name'       => fake()->randomElement(['Ración entera', 'Media ración', 'Tapa', 'Para compartir']),
            'price'      => fake()->randomFloat(2, 2, 25),
            'sort_order' => $counter++,
        ];
    }
}
