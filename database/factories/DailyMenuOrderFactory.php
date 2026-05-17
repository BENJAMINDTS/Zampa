<?php

namespace Database\Factories;

use App\Models\DailyMenu;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailyMenuOrder>
 */
class DailyMenuOrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id'                => Order::factory(),
            'daily_menu_id'           => DailyMenu::factory(),
            'rounds_total'            => 2,
            'current_round'           => 0,
            'client_timing_overrides' => null,
            'status'                  => 'pending',
        ];
    }
}
