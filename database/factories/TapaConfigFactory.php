<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TapaConfig>
 *
 * @author BenjaminDTS
 */
class TapaConfigFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'            => User::factory(),
            'tapas_enabled'      => false,
            'tapas_free'         => true,
            'price_mode'         => 'fixed',
            'max_tapa_variants'  => 3,
            'tapa_price'         => null,
            'extra_tapa_enabled' => false,
            'extra_tapa_price'   => null,
        ];
    }
}
