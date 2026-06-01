<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 *
 * @author BenjaminDTS
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Crea un plan con precio y límites aleatorios.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $monthly = $this->faker->randomFloat(2, 9, 99);

        return [
            'name'          => $this->faker->randomElement(['Básico', 'Pro', 'Business']),
            'price'         => $monthly,
            'price_monthly' => $monthly,
            'price_yearly'  => round($monthly * 10, 2),
            'max_tables'    => $this->faker->numberBetween(5, 50),
            'max_staff'     => $this->faker->numberBetween(3, 30),
            'max_floors'    => $this->faker->numberBetween(1, 5),
        ];
    }

    /**
     * Estado: plan Básico (29,99 €/mes · 20 mesas · 10 staff · 1 planta).
     *
     * @return static
     */
    public function basic(): static
    {
        return $this->state([
            'name'          => 'Básico',
            'price'         => 29.99,
            'price_monthly' => 29.99,
            'price_yearly'  => 299.90,
            'max_tables'    => 20,
            'max_staff'     => 10,
            'max_floors'    => 1,
        ]);
    }

    /**
     * Estado: plan Profesional (74,99 €/mes · 50 mesas · 25 staff · 3 plantas).
     *
     * @return static
     */
    public function professional(): static
    {
        return $this->state([
            'name'          => 'Profesional',
            'price'         => 74.99,
            'price_monthly' => 74.99,
            'price_yearly'  => 749.90,
            'max_tables'    => 50,
            'max_staff'     => 25,
            'max_floors'    => 3,
        ]);
    }

    /**
     * Estado: plan Premium (119,99 €/mes · ilimitado en todo).
     *
     * @return static
     */
    public function premium(): static
    {
        return $this->state([
            'name'          => 'Premium',
            'price'         => 119.99,
            'price_monthly' => 119.99,
            'price_yearly'  => 1199.90,
            'max_tables'    => null,
            'max_staff'     => null,
            'max_floors'    => null,
        ]);
    }
}
