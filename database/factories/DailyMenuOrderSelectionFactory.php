<?php

namespace Database\Factories;

use App\Models\DailyMenuOrder;
use App\Models\DailyMenuSection;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailyMenuOrderSelection>
 */
class DailyMenuOrderSelectionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'daily_menu_order_id'   => DailyMenuOrder::factory(),
            'daily_menu_section_id' => DailyMenuSection::factory(),
            'product_id'            => Product::factory(),
            'quantity'              => 1,
        ];
    }
}
