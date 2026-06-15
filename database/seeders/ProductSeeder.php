<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Siembra los productos reales del bar demo con sus categorías e ingredientes.
 *
 * @author BenjaminDTS
 */
class ProductSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run(): void
    {
        $user = User::where('email', 'admin@zampa.app')->firstOrFail();

        // Ingredientes extra no incluidos en DatabaseSeeder
        $extraIngredients = [
            ['name' => 'Leche de Soja',         'is_allergen' => true,  'allergen_types' => ['soja']],
            ['name' => 'Leche sin Lactosa',      'is_allergen' => true,  'allergen_types' => ['lacteos']],
            ['name' => 'Leche desnatada',        'is_allergen' => true,  'allergen_types' => ['lacteos']],
            ['name' => 'Hamburguesa de ternera', 'is_allergen' => false, 'allergen_types' => null],
            ['name' => 'Pan de hamburguesa',     'is_allergen' => true,  'allergen_types' => ['gluten']],
            ['name' => 'Secreto ibérico',        'is_allergen' => false, 'allergen_types' => null],
        ];

        foreach ($extraIngredients as $ing) {
            Ingredient::firstOrCreate(
                ['name' => $ing['name'], 'user_id' => $user->id],
                ['is_allergen' => $ing['is_allergen'], 'allergen_types' => $ing['allergen_types']]
            );
        }

        $products = [
            // ── Desayunos ────────────────────────────────────────────────────
            ['name' => 'Café solo',                    'price' => null,  'sort_order' => 0,  'categories' => ['Desayunos'], 'ingredients' => []],
            ['name' => 'Café con leche',               'price' => null,  'sort_order' => 1,  'categories' => ['Desayunos'], 'ingredients' => [
                ['name' => 'Leche', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Café manchado',                'price' => null,  'sort_order' => 2,  'categories' => ['Desayunos'], 'ingredients' => [
                ['name' => 'Leche', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Café cortado',                 'price' => null,  'sort_order' => 3,  'categories' => ['Desayunos'], 'ingredients' => [
                ['name' => 'Leche', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Café americano',               'price' => null,  'sort_order' => 4,  'categories' => ['Desayunos'], 'ingredients' => []],
            ['name' => 'Café expresso',                'price' => null,  'sort_order' => 5,  'categories' => ['Desayunos'], 'ingredients' => []],
            ['name' => 'Cafés con leche de soja',      'price' => null,  'sort_order' => 6,  'categories' => ['Desayunos'], 'ingredients' => [
                ['name' => 'Leche de Soja', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Cafés con leche sin lactosa',  'price' => null,  'sort_order' => 7,  'categories' => ['Desayunos'], 'ingredients' => [
                ['name' => 'Leche sin Lactosa', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Cafés con leche desnatada',    'price' => null,  'sort_order' => 8,  'categories' => ['Desayunos'], 'ingredients' => [
                ['name' => 'Leche desnatada', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Carajillo',                    'price' => null,  'sort_order' => 9,  'categories' => ['Desayunos'], 'ingredients' => []],
            ['name' => 'Carajillo Bayleis',            'price' => null,  'sort_order' => 10, 'categories' => ['Desayunos'], 'ingredients' => [
                ['name' => 'Leche', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Infusiónes',                   'price' => null,  'sort_order' => 11, 'categories' => ['Desayunos'], 'ingredients' => []],
            ['name' => 'Infusiones en leche',          'price' => null,  'sort_order' => 12, 'categories' => ['Desayunos'], 'ingredients' => [
                ['name' => 'Leche', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Infusiones en leche de soja',  'price' => null,  'sort_order' => 13, 'categories' => ['Desayunos'], 'ingredients' => [
                ['name' => 'Leche de Soja', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Infusiones en leche sin lactosa', 'price' => null, 'sort_order' => 14, 'categories' => ['Desayunos'], 'ingredients' => [
                ['name' => 'Leche sin Lactosa', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Infusiones en leche desnatada', 'price' => null, 'sort_order' => 15, 'categories' => ['Desayunos'], 'ingredients' => [
                ['name' => 'Leche desnatada', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Infusiones especiales',        'price' => null,  'sort_order' => 16, 'categories' => ['Desayunos'], 'ingredients' => []],
            ['name' => 'Batido de chocolate',          'price' => '2.20', 'sort_order' => 17, 'categories' => ['Desayunos', 'Refrescos'], 'ingredients' => [
                ['name' => 'Leche', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Colacao',                      'price' => '1.40', 'sort_order' => 18, 'categories' => ['Desayunos'], 'ingredients' => [
                ['name' => 'Leche', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'ColaCao con leche de soja',    'price' => '1.40', 'sort_order' => 19, 'categories' => ['Desayunos'], 'ingredients' => [
                ['name' => 'Leche de Soja', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'ColaCao con leche sin lactosa', 'price' => '1.40', 'sort_order' => 20, 'categories' => ['Desayunos'], 'ingredients' => []],
            ['name' => 'ColaCao con leche desnatada',  'price' => '1.40', 'sort_order' => 21, 'categories' => ['Desayunos'], 'ingredients' => []],
            ['name' => 'Zumo de Naranja natural',      'price' => null,  'sort_order' => 22, 'categories' => ['Desayunos', 'Refrescos'], 'ingredients' => []],
            ['name' => 'Zumos',                        'price' => null,  'sort_order' => 23, 'categories' => ['Desayunos', 'Refrescos'], 'ingredients' => []],

            // ── Tostadas ─────────────────────────────────────────────────────
            ['name' => 'Tostada de aceite',            'price' => null,  'sort_order' => 24, 'categories' => ['Tostadas'], 'ingredients' => [
                ['name' => 'Pan', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Tostada de tomate',            'price' => null,  'sort_order' => 25, 'categories' => ['Tostadas'], 'ingredients' => [
                ['name' => 'Pan',    'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Tostada de mantequilla',       'price' => null,  'sort_order' => 26, 'categories' => ['Tostadas'], 'ingredients' => [
                ['name' => 'Mantequilla', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan',         'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Tostada de mermelada',         'price' => null,  'sort_order' => 27, 'categories' => ['Tostadas'], 'ingredients' => [
                ['name' => 'Pan', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Tostada mixta',                'price' => null,  'sort_order' => 28, 'categories' => ['Tostadas'], 'ingredients' => [
                ['name' => 'Mantequilla', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan',         'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Tostada de paté',              'price' => null,  'sort_order' => 29, 'categories' => ['Tostadas'], 'ingredients' => [
                ['name' => 'Pan',          'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Paté de cerdo','is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Tostada de sobrasada',         'price' => null,  'sort_order' => 30, 'categories' => ['Tostadas'], 'ingredients' => [
                ['name' => 'Pan',       'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Sobrasada', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Tostada de jamón',             'price' => null,  'sort_order' => 31, 'categories' => ['Tostadas'], 'ingredients' => [
                ['name' => 'Pan',    'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate', 'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Tostada de queso',             'price' => null,  'sort_order' => 32, 'categories' => ['Tostadas'], 'ingredients' => [
                ['name' => 'Pan',    'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Queso',  'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate', 'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Tostada de jamón y queso',     'price' => null,  'sort_order' => 33, 'categories' => ['Tostadas'], 'ingredients' => [
                ['name' => 'Pan',    'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate', 'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Queso',  'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Tostada jamon york',           'price' => null,  'sort_order' => 34, 'categories' => ['Tostadas'], 'ingredients' => [
                ['name' => 'Jamón york', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan',        'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate',     'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Tostada de jamón york y queso','price' => null,  'sort_order' => 35, 'categories' => ['Tostadas'], 'ingredients' => [
                ['name' => 'Jamón york', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan',        'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Queso',      'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate',     'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Tostada de atún',              'price' => null,  'sort_order' => 36, 'categories' => ['Tostadas'], 'ingredients' => [
                ['name' => 'Atún',   'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan',    'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate', 'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Tostada de atún y queso',      'price' => null,  'sort_order' => 37, 'categories' => ['Tostadas'], 'ingredients' => [
                ['name' => 'Atún',   'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan',    'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Queso',  'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate', 'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],

            // ── Ensaladas ────────────────────────────────────────────────────
            ['name' => 'Ensalada mixta',               'price' => '8.00', 'sort_order' => 38, 'categories' => ['Ensaladas'], 'ingredients' => [
                ['name' => 'Aceitunas', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Atún',      'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Cebolla',   'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Lechuga',   'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Maiz',      'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate',    'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Ensalada de pimientos con ventresca', 'price' => '9.00', 'sort_order' => 39, 'categories' => ['Ensaladas'], 'ingredients' => [
                ['name' => 'Pimientos asados', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Ventresca',        'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Tomate con anchoas',           'price' => '10.00', 'sort_order' => 40, 'categories' => ['Ensaladas'], 'ingredients' => [
                ['name' => 'Anchoas', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate',  'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],

            // ── Bocadillos ───────────────────────────────────────────────────
            ['name' => 'Bocadillo de jamón',           'price' => null,  'sort_order' => 41, 'categories' => ['Bocadillos'], 'ingredients' => [
                ['name' => 'Jamón',  'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan',    'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate', 'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Bocadillo de queso',           'price' => null,  'sort_order' => 42, 'categories' => ['Bocadillos'], 'ingredients' => [
                ['name' => 'Pan',    'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Queso',  'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate', 'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Bocadillo de jamón y queso',   'price' => null,  'sort_order' => 43, 'categories' => ['Bocadillos'], 'ingredients' => [
                ['name' => 'Jamón',  'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan',    'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Queso',  'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate', 'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Bocadillo de lomo',            'price' => null,  'sort_order' => 44, 'categories' => ['Bocadillos'], 'ingredients' => [
                ['name' => 'Ali-oli',      'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Lomo de cerdo','is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan',          'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate',       'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Bocadillo de lomo completo',   'price' => null,  'sort_order' => 45, 'categories' => ['Bocadillos'], 'ingredients' => [
                ['name' => 'Ali-oli',      'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Huevo',        'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Lechuga',      'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Lomo de cerdo','is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan',          'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Queso',        'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate',       'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Bocadillo de beacon',          'price' => null,  'sort_order' => 46, 'categories' => ['Bocadillos'], 'ingredients' => [
                ['name' => 'Ali-oli', 'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Bacon',   'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan',     'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate',  'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Bocadillo de beacon completo', 'price' => null,  'sort_order' => 47, 'categories' => ['Bocadillos'], 'ingredients' => [
                ['name' => 'Ali-oli', 'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Bacon',   'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Huevo',   'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Lechuga', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan',     'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Queso',   'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate',  'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Bocadillo de tortilla francesa','price' => null, 'sort_order' => 48, 'categories' => ['Bocadillos'], 'ingredients' => [
                ['name' => 'Huevo', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan',   'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Bocadillo de atún',            'price' => null,  'sort_order' => 49, 'categories' => ['Bocadillos'], 'ingredients' => [
                ['name' => 'Atún',   'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan',    'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate', 'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Sandwich mixto',               'price' => '3.00', 'sort_order' => 50, 'categories' => ['Bocadillos'], 'ingredients' => [
                ['name' => 'Jamón york', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan',        'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Queso',      'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Sandwich completo',            'price' => '4.50', 'sort_order' => 51, 'categories' => ['Bocadillos'], 'ingredients' => [
                ['name' => 'Huevo',      'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Jamón york', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Lechuga',    'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Mantequilla','is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan',        'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Queso',      'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate',     'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Hamburguesa',                  'price' => '4.00', 'sort_order' => 52, 'categories' => ['Bocadillos'], 'ingredients' => [
                ['name' => 'Hamburguesa de ternera', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Lechuga',                'is_removable' => 0, 'is_extra' => 1, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan de hamburguesa',     'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate',                 'is_removable' => 0, 'is_extra' => 1, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Huevo',                  'is_removable' => 0, 'is_extra' => 1, 'extra_price' => '0.50', 'quantity_base' => '1.00'],
                ['name' => 'Queso',                  'is_removable' => 0, 'is_extra' => 1, 'extra_price' => '0.50', 'quantity_base' => '1.00'],
                ['name' => 'Bacon',                  'is_removable' => 0, 'is_extra' => 1, 'extra_price' => '0.50', 'quantity_base' => '1.00'],
                ['name' => 'Cebolla',                'is_removable' => 0, 'is_extra' => 1, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Jamón york',             'is_removable' => 0, 'is_extra' => 1, 'extra_price' => '0.50', 'quantity_base' => '1.00'],
            ]],

            // ── Roscas ───────────────────────────────────────────────────────
            ['name' => 'Rosca de Lomo',                'price' => '11.00', 'sort_order' => 53, 'categories' => ['Roscas'], 'ingredients' => [
                ['name' => 'Ali-oli',      'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Huevo',        'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Lomo de cerdo','is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan',          'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Queso',        'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate',       'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Rosca de atún y pimientos asados', 'price' => '11.00', 'sort_order' => 54, 'categories' => ['Roscas'], 'ingredients' => [
                ['name' => 'Atún',          'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan',           'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pimientos asados','is_removable'=> 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate',        'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Rosca de bacon',               'price' => '11.00', 'sort_order' => 55, 'categories' => ['Roscas'], 'ingredients' => [
                ['name' => 'Ali-oli', 'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Bacon',   'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Huevo',   'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan',     'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Queso',   'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate',  'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Rosca de jamón',               'price' => '11.00', 'sort_order' => 56, 'categories' => ['Roscas'], 'ingredients' => [
                ['name' => 'Jamón',  'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan',    'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Queso',  'is_removable' => 0, 'is_extra' => 1, 'extra_price' => '1.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate', 'is_removable' => 1, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],

            // ── Pizzas ───────────────────────────────────────────────────────
            ['name' => 'Pizza margarita',              'price' => '7.00',  'sort_order' => 57, 'categories' => ['Pizzas'], 'ingredients' => [
                ['name' => 'Masa de pizza',     'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Queso mozzarella',  'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate',            'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Pizza prosciutto',             'price' => '9.00',  'sort_order' => 58, 'categories' => ['Pizzas'], 'ingredients' => [
                ['name' => 'Champiñones',       'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Jamón york',        'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Masa de pizza',     'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Queso mozzarella',  'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate',            'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Pizza 4 quesos',               'price' => '9.00',  'sort_order' => 59, 'categories' => ['Pizzas'], 'ingredients' => [
                ['name' => 'Masa de pizza',     'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Queso gouda',       'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Queso mozzarella',  'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Queso parmesano',   'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Queso roquefort',   'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate',            'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Pizza tonno',                  'price' => '11.00', 'sort_order' => 60, 'categories' => ['Pizzas'], 'ingredients' => [
                ['name' => 'Atún',           'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Cebolla',        'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Masa de pizza',  'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Queso mozzarella','is_removable'=> 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate',         'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],

            // ── Raciones ─────────────────────────────────────────────────────
            ['name' => 'Bacalao frtito',               'price' => null,   'sort_order' => 61, 'categories' => ['Raciones'], 'ingredients' => [
                ['name' => 'Bacalao', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Harina',  'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Rejos fritos',                 'price' => null,   'sort_order' => 62, 'categories' => ['Raciones'], 'ingredients' => [
                ['name' => 'Harina', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Rejos',  'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Calamares fritos',             'price' => null,   'sort_order' => 63, 'categories' => ['Raciones'], 'ingredients' => [
                ['name' => 'Calamares', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Harina',    'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Lomo a la plancha',            'price' => null,   'sort_order' => 64, 'categories' => ['Raciones'], 'ingredients' => [
                ['name' => 'Ajo',          'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Lomo de cerdo','is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Carne en salsa',               'price' => null,   'sort_order' => 65, 'categories' => ['Raciones'], 'ingredients' => [
                ['name' => 'Ajo',             'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Carne de ternera','is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Cebolla',         'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate',          'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Vino',            'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Patatas bravas',               'price' => null,   'sort_order' => 66, 'categories' => ['Raciones'], 'ingredients' => [
                ['name' => 'Ajo',       'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Patata',    'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pimentón',  'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate',    'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Albóndigas en salsa',          'price' => null,   'sort_order' => 67, 'categories' => ['Raciones'], 'ingredients' => [
                ['name' => 'Carne de ternera','is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Cebolla',         'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Huevo',           'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan rallado',     'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Tomate',          'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Berenjenas a la miel de caña', 'price' => null,   'sort_order' => 68, 'categories' => ['Raciones'], 'ingredients' => [
                ['name' => 'Berenjena',   'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Harina',      'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Miel de caña','is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Patatas fritas',               'price' => null,   'sort_order' => 69, 'categories' => ['Raciones'], 'ingredients' => [
                ['name' => 'Patata', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Croquetas caseras',            'price' => null,   'sort_order' => 70, 'categories' => ['Raciones'], 'ingredients' => [
                ['name' => 'Harina',      'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Huevo',       'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Jamón',       'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Leche',       'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Mantequilla', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
                ['name' => 'Pan rallado', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Chorizo',                      'price' => null,   'sort_order' => 71, 'categories' => ['Raciones'], 'ingredients' => [
                ['name' => 'Chorizo', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Morcilla',                     'price' => null,   'sort_order' => 72, 'categories' => ['Raciones'], 'ingredients' => [
                ['name' => 'Morcilla', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
            ['name' => 'Secreto ibérico',              'price' => '18.00','sort_order' => 73, 'categories' => ['Raciones'], 'ingredients' => [
                ['name' => 'Secreto ibérico', 'is_removable' => 0, 'is_extra' => 0, 'extra_price' => '0.00', 'quantity_base' => '1.00'],
            ]],
        ];

        foreach ($products as $data) {
            $product = Product::firstOrCreate(
                ['name' => $data['name'], 'user_id' => $user->id],
                [
                    'description'  => $data['description'] ?? null,
                    'price'        => $data['price'],
                    'is_active'    => true,
                    'is_available' => true,
                    'sort_order'   => $data['sort_order'],
                ]
            );

            $categoryIds = Category::where('user_id', $user->id)
                ->whereIn('name', $data['categories'])
                ->pluck('id')
                ->toArray();
            $product->categories()->sync($categoryIds);

            $ingredientSync = [];
            foreach ($data['ingredients'] as $ing) {
                $ingredient = Ingredient::where('name', $ing['name'])
                    ->where('user_id', $user->id)
                    ->first();
                if ($ingredient) {
                    $ingredientSync[$ingredient->id] = [
                        'is_removable'  => $ing['is_removable'],
                        'is_extra'      => $ing['is_extra'],
                        'extra_price'   => $ing['extra_price'],
                        'quantity_base' => $ing['quantity_base'],
                    ];
                }
            }
            $product->ingredients()->sync($ingredientSync);
        }
    }
}
