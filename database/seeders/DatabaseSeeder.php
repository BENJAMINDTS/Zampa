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
     * 4. 10 Mesas, 20 Ingredientes, 5 Categorías con Productos conectados
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

        // 6. Crear 20 Ingredientes base
        $ingredients = Ingredient::factory(20)->create(['user_id' => $user->id]);

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
