<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buscamos el primer usuario (el admin) y la primera categoría
        // Si no existen, esto fallaría, pero el seed general los creará antes.
        $user = User::first();
        $category = Category::first();

        // Si por lo que sea no hay categorías, no creamos nada para evitar error
        if (!$category || !$user) {
            return;
        }

        // 2. Creamos productos de ejemplo y adjuntamos la categoría via pivot
        $hamburguesa = Product::create([
            'user_id'     => $user->id,
            'name'        => 'Hamburguesa Kevin Bacon',
            'description' => 'La best seller. Carne picada fresca, bacon crujiente y salsa secreta.',
            'price'       => 12.50,
            'image'       => 'products/burger-ejemplo.jpg',
            'is_active'   => true,
        ]);
        $hamburguesa->categories()->attach($category->id);

        $cola = Product::create([
            'user_id'     => $user->id,
            'name'        => 'Coca Cola Zero',
            'description' => 'Lata de 33cl bien fría.',
            'price'       => 2.50,
            'image'       => 'products/coca-cola.jpg',
            'is_active'   => true,
        ]);
        $cola->categories()->attach($category->id);

        $patatas = Product::create([
            'user_id'     => $user->id,
            'name'        => 'Patatas Fritas Caseras',
            'description' => 'Cortadas a mano y con doble fritura.',
            'price'       => 4.00,
            'image'       => 'products/fries.jpg',
            'is_active'   => true,
        ]);
        $patatas->categories()->attach($category->id);
    }
}
