<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Plan;
use App\Models\Table;
use App\Models\Category;
use App\Models\Product;
use App\Models\Ingredient;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Crea un escenario completo de prueba:
     * 1. Plan Premium
     * 2. Usuario Admin (admin@zampa.app / password)
     * 3. 10 Mesas, 20 Ingredientes, 5 Categorías
     * 4. Productos conectados con Ingredientes (Pivot)
     */
    public function run(): void
    {
        // 1. Crear un Plan Premium
        $plan = Plan::factory()->create([
            'name' => 'Plan Premium',
            'price' => 29.99,
            'max_tables' => 50
        ]);

        // 2. Crear TU usuario Admin
        // Le asignamos el plan creado arriba.
        $user = User::factory()->create([
            'name' => 'Admin Zampa',
            'email' => 'admin@zampa.app',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'plan_id' => $plan->id,
        ]);

        // 3. Crear 10 Mesas para este usuario
        Table::factory(10)->create(['user_id' => $user->id]);

        // 4. Crear 20 Ingredientes base
        $ingredients = Ingredient::factory(20)->create(['user_id' => $user->id]);

        // 5. Crear 5 Categorías y llenarlas de productos
        // Usamos 'each' para iterar sobre cada categoría creada
        Category::factory(5)->create(['user_id' => $user->id])->each(function ($category) use ($user, $ingredients) {

            // Por cada categoría, creamos 4 productos
            $products = Product::factory(4)->create([
                'user_id' => $user->id,
                'category_id' => $category->id
            ]);

            // A cada producto le asignamos 3 ingredientes aleatorios (Tabla Pivote)
            foreach ($products as $product) {
                $product->ingredients()->attach(
                    $ingredients->random(3),
                    ['quantity_base' => 1] // Dato extra en la tabla intermedia
                );
            }
            
        });
    }
}
