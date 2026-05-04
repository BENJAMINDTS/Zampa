<?php

namespace Database\Factories;

use App\Models\TapaConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KitchenSchedule>
 *
 * @author BenjaminDTS
 */
class KitchenScheduleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tapa_config_id' => TapaConfig::factory(),
            'opens_at'       => '10:00:00',
            'closes_at'      => '23:00:00',
        ];
    }
}
