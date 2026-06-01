<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Plan;
use App\Models\Table;
use App\Models\Category;
use App\Models\Product;
use App\Models\Ingredient;

/**
 * @author BenjaminDTS
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Crea un escenario completo de prueba:
     * 1. Tres superadmins del equipo Zampa (BenjaminDTS, SebastianBCF, Ayrton)
     * 2. Tres planes: Básico · Profesional · Premium
     * 3. Usuario Admin de demo (admin@zampa.app / password) con plan Premium
     * 4. 10 Mesas, 50 Ingredientes reales con alérgenos UE 1169/2011, 5 Categorías con Productos conectados
     *
     * @return void
     */
    public function run(): void
    {
        // 1. Superadmins del equipo Zampa
        User::firstOrCreate(
            ['email' => 'benjamin@zampa.app'],
            [
                'name'     => 'BenjaminDTS',
                'password' => Hash::make('password'),
                'role'     => 'superadmin',
                'admin_id' => null,
            ]
        );

        User::firstOrCreate(
            ['email' => 'sebastian@zampa.app'],
            [
                'name'     => 'SebastianBCF',
                'password' => Hash::make('password'),
                'role'     => 'superadmin',
                'admin_id' => null,
            ]
        );

        User::firstOrCreate(
            ['email' => 'ayrton@zampa.app'],
            [
                'name'     => 'Ayrton',
                'password' => Hash::make('password'),
                'role'     => 'superadmin',
                'admin_id' => null,
            ]
        );

        // 2. Crear los tres planes de Zampa
        Plan::updateOrCreate(
            ['name' => 'Básico'],
            [
                'price'         => 29.99,
                'price_monthly' => 29.99,
                'price_yearly'  => 299.90,
                'max_tables'    => 20,
                'max_staff'     => 10,
                'max_floors'    => 1,
            ]
        );

        Plan::updateOrCreate(
            ['name' => 'Profesional'],
            [
                'price'         => 74.99,
                'price_monthly' => 74.99,
                'price_yearly'  => 749.90,
                'max_tables'    => 50,
                'max_staff'     => 25,
                'max_floors'    => 3,
            ]
        );

        $plan = Plan::updateOrCreate(
            ['name' => 'Premium'],
            [
                'price'         => 119.99,
                'price_monthly' => 119.99,
                'price_yearly'  => 1199.90,
                'max_tables'    => null,
                'max_staff'     => null,
                'max_floors'    => null,
            ]
        );

        // 3. Admin de demo con datos de negocio
        $user = User::firstOrCreate(
            ['email' => 'admin@zampa.app'],
            [
                'name'          => 'Admin Demo',
                'password'      => Hash::make('password'),
                'role'          => 'admin',
                'plan_id'       => $plan->id,
                'admin_id'      => null,
                'business_name' => 'Bar Zampa Demo',
                'address'       => 'Calle Mayor 1, Madrid',
                'lat'           => 40.4168,
                'lng'           => -3.7038,
                'is_waiter'              => true,
                'is_kitchen'             => true,
                'split_payment_enabled'  => false,
            ]
        );

        // 4. Staff del gerente demo: camarero y cocinero
        User::firstOrCreate(
            ['email' => 'camarero@zampa.app'],
            [
                'name'     => 'Camarero Demo',
                'password' => Hash::make('password'),
                'role'     => 'waiter',
                'admin_id' => $user->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'cocinero@zampa.app'],
            [
                'name'     => 'Cocinero Demo',
                'password' => Hash::make('password'),
                'role'     => 'kitchen',
                'admin_id' => $user->id,
            ]
        );

        // 5. Crear 10 Mesas para este usuario
        Table::factory(10)->create(['user_id' => $user->id]);

        // 6. Ingredientes reales del menú con alérgenos UE 1169/2011
        $ingredientData = [
            ['name' => 'Aceite',            'allergen_types' => []],
            ['name' => 'Aceitunas',         'allergen_types' => []],
            ['name' => 'Ajo',               'allergen_types' => []],
            ['name' => 'Ali-oli',           'allergen_types' => ['huevos']],
            ['name' => 'Anchoas',           'allergen_types' => ['pescado']],
            ['name' => 'Atún',              'allergen_types' => ['pescado']],
            ['name' => 'Bacalao',           'allergen_types' => ['pescado']],
            ['name' => 'Bacon',             'allergen_types' => ['soja']],
            ['name' => 'Berenjena',         'allergen_types' => []],
            ['name' => 'Calamares',         'allergen_types' => ['pescado']],
            ['name' => 'Carne de ternera',  'allergen_types' => []],
            ['name' => 'Cebolla',           'allergen_types' => []],
            ['name' => 'Champiñones',       'allergen_types' => []],
            ['name' => 'Chorizo',           'allergen_types' => ['soja']],
            ['name' => 'Cola Cao en polvo', 'allergen_types' => ['gluten', 'frutos-cascara']],
            ['name' => 'Harina',            'allergen_types' => ['gluten']],
            ['name' => 'Huevo',             'allergen_types' => ['huevos']],
            ['name' => 'Jamón',             'allergen_types' => []],
            ['name' => 'Jamón york',        'allergen_types' => ['soja']],
            ['name' => 'Leche',             'allergen_types' => ['lacteos']],
            ['name' => 'Lechuga',           'allergen_types' => []],
            ['name' => 'Lomo de cerdo',     'allergen_types' => []],
            ['name' => 'Lomo embuchado',    'allergen_types' => ['soja']],
            ['name' => 'Maiz',              'allergen_types' => []],
            ['name' => 'Mantequilla',       'allergen_types' => ['lacteos']],
            ['name' => 'Masa de pizza',     'allergen_types' => ['gluten', 'soja', 'apio', 'sesamo', 'sulfitos']],
            ['name' => 'Mayonesa',          'allergen_types' => ['huevos']],
            ['name' => 'Miel de caña',      'allergen_types' => []],
            ['name' => 'Morcilla',          'allergen_types' => ['soja']],
            ['name' => 'Naranja',           'allergen_types' => []],
            ['name' => 'Pan',               'allergen_types' => ['gluten']],
            ['name' => 'Pan rallado',       'allergen_types' => ['gluten']],
            ['name' => 'Patata',            'allergen_types' => []],
            ['name' => 'Paté de cerdo',     'allergen_types' => ['lacteos', 'soja']],
            ['name' => 'Pimentón',          'allergen_types' => []],
            ['name' => 'Pimientos asados',  'allergen_types' => []],
            ['name' => 'Pollo',             'allergen_types' => []],
            ['name' => 'Queso',             'allergen_types' => ['lacteos']],
            ['name' => 'Queso gouda',       'allergen_types' => ['lacteos']],
            ['name' => 'Queso mozzarella',  'allergen_types' => ['lacteos']],
            ['name' => 'Queso parmesano',   'allergen_types' => ['lacteos']],
            ['name' => 'Queso roquefort',   'allergen_types' => ['lacteos']],
            ['name' => 'Rejos',             'allergen_types' => ['pescado']],
            ['name' => 'Sal',               'allergen_types' => []],
            ['name' => 'Salchichón',        'allergen_types' => ['soja']],
            ['name' => 'Sésamo',            'allergen_types' => ['sesamo']],
            ['name' => 'Sobrasada',         'allergen_types' => []],
            ['name' => 'Tomate',            'allergen_types' => []],
            ['name' => 'Ventresca',         'allergen_types' => ['pescado']],
            ['name' => 'Vino',              'allergen_types' => ['sulfitos']],
        ];

        $ingredients = collect();
        foreach ($ingredientData as $data) {
            $hasAllergens = !empty($data['allergen_types']);
            $ingredients->push(Ingredient::firstOrCreate(
                ['name' => $data['name'], 'user_id' => $user->id],
                [
                    'is_allergen'    => $hasAllergens,
                    'allergen_types' => $hasAllergens ? $data['allergen_types'] : null,
                ]
            ));
        }

        // 7. Crear 5 Categorías y llenarlas de productos
        Category::factory(5)->create(['user_id' => $user->id])->each(function ($category) use ($user, $ingredients) {
            $products = Product::factory(4)->create([
                'user_id'     => $user->id,
                'category_id' => $category->id,
            ]);

            foreach ($products as $product) {
                $product->ingredients()->attach(
                    $ingredients->random(3),
                    ['quantity_base' => 1]
                );
            }
        });
    }
}
