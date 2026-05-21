<?php

namespace Database\Factories;

use App\Models\OrderItemPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItemPayment>
 */
class OrderItemPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id'                 => \App\Models\Order::factory(),
            'order_item_id'            => null,
            'amount'                   => $this->faker->randomFloat(2, 1, 50),
            'stripe_payment_intent_id' => 'pi_test_' . $this->faker->unique()->lexify('??????????????????'),
            'status'                   => 'pending',
            'mode'                     => $this->faker->randomElement(['items', 'equitative']),
            'parts_total'              => null,
            'part_number'              => null,
        ];
    }
}
