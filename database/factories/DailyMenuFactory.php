<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailyMenu>
 */
class DailyMenuFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'title'         => fake()->words(3, true) . ' del día',
            'description'   => fake()->sentence(),
            'price'         => fake()->randomFloat(2, 8, 18),
            'max_per_day'   => null,
            'is_active'     => true,
            'day_of_week'   => fake()->randomElement([
                'monday', 'tuesday', 'wednesday', 'thursday',
                'friday', 'saturday', 'sunday',
            ]),
            'specific_date' => null,
        ];
    }
}
