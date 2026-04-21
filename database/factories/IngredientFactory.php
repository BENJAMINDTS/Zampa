<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ingredient>
 *
 * @author SebastianBCF
 */
class IngredientFactory extends Factory
{
    /**
     * Mapa de nombre de ingrediente a tipo de alérgeno UE (Reglamento 1169/2011).
     */
    private const ALLERGEN_MAP = [
        'Queso'     => 'lacteos',
        'Bacon'     => 'gluten',
        'Huevo'     => 'huevos',
        'Pan'       => 'gluten',
        'Salsa BBQ' => 'mostaza',
        'Tomate'    => 'sulfitos',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name       = fake()->randomElement(['Tomate', 'Queso', 'Bacon', 'Lechuga', 'Salsa BBQ', 'Huevo', 'Pan', 'Pollo']);
        $isAllergen = isset(self::ALLERGEN_MAP[$name]) ? fake()->boolean(20) : false;

        return [
            'name'          => $name,
            'is_allergen'   => $isAllergen,
            'allergen_type' => $isAllergen ? self::ALLERGEN_MAP[$name] : null,
            'user_id'       => User::factory(),
        ];
    }
}
