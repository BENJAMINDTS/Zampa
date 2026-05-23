<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderPayment>
 */
class OrderPaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'method'   => fake()->randomElement(['cash', 'card']),
            'amount'   => fake()->randomFloat(2, 5, 100),
            'tip'      => 0,
        ];
    }
}
