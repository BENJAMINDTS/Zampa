<?php

namespace Database\Factories;

use App\Models\DailyMenu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailyMenuTimingRule>
 */
class DailyMenuTimingRuleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'daily_menu_id'          => DailyMenu::factory(),
            'round_number'           => 1,
            'default_delay_minutes'  => fake()->numberBetween(10, 30),
            'estimated_prep_minutes' => fake()->numberBetween(5, 15),
            'section_types'          => ['first_course'],
        ];
    }
}
