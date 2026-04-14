<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ingredient>
 */
class IngredientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Incluye un flag booleano para simular alérgenos (20% de probabilidad).
     *
     * @return array<string, mixed>
     */
    /**
     * Mapa de nombre de ingrediente → tipo de alérgeno UE (Reglamento 1169/2011).
     * Solo los que realmente son alérgenos oficiales.
     */
    private const ALLERGEN_MAP = [
        'Queso'     => 'lacteos',
        'Bacon'     => 'gluten',
        'Huevo'     => 'huevos',
        'Pan'       => 'gluten',
        'Salsa BBQ' => 'mostaza',
        'Tomate'    => 'sulfitos',
    ];

    public function definition(): array
    {
        $name       = $this->faker->randomElement(['Tomate', 'Queso', 'Bacon', 'Lechuga', 'Salsa BBQ', 'Huevo', 'Pan', 'Pollo']);
        $isAllergen = isset(self::ALLERGEN_MAP[$name]) ? $this->faker->boolean(20) : false;

        return [
            'name'         => $name,
            'is_allergen'  => $isAllergen,
            'allergen_type' => $isAllergen ? self::ALLERGEN_MAP[$name] : null,
        ];
    }
}
