<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id'   => Order::factory(),
            'product_id' => Product::factory(),
            'quantity'   => fake()->numberBetween(1, 5),
            'price'      => fake()->randomFloat(2, 3, 30),
            'status'     => 'queued',
        ];
    }
}
