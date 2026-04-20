<?php

namespace Database\Factories;

use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'table_id'        => Table::factory(),
            'status'          => 'pending',
            'total'           => $this->faker->randomFloat(2, 5, 200),
            'tip'             => 0,
            'payment_method'  => null,
            'payment_status'  => 'pending',
            'note'            => null,
        ];
    }
}
