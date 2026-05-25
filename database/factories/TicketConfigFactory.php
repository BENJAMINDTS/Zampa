<?php

namespace Database\Factories;

use App\Models\TicketConfig;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketConfig>
 *
 * @author SebastianBCF
 */
class TicketConfigFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'logo'        => null,
            'tax_id'      => $this->faker->numerify('B########'),
            'footer_text' => $this->faker->sentence(),
            'template'    => $this->faker->randomElement(TicketConfig::TEMPLATES),
        ];
    }
}
