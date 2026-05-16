<?php

namespace Database\Factories;

use App\Models\DailyMenu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailyMenuSection>
 */
class DailyMenuSectionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'daily_menu_id' => DailyMenu::factory(),
            'type'          => fake()->randomElement([
                'first_course', 'second_course', 'dessert',
                'coffee', 'drink', 'bread',
            ]),
            'is_required'   => true,
            'is_free'       => false,
            'max_quantity'  => 1,
            'display_order' => fake()->numberBetween(0, 5),
        ];
    }
}
