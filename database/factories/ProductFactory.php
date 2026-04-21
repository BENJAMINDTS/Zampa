<?php

namespace Database\Factories;

use App\Models\Category;
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
}
