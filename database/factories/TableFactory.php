<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Table>
 */
class TableFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'name'        => 'Mesa ' . fake()->unique()->numberBetween(1, 50),
            'unique_hash' => Str::uuid()->toString(),
            'status'      => 'free',
            'position_x'  => 0,
            'position_y'  => 0,
            'width'       => 100,
            'height'      => 100,
            'shape'       => 'square',
            'rotation'    => 0,
        ];
    }
}
