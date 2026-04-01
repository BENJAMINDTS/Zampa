<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Table;
use Illuminate\View\View;

/**
 * Class MenuController
 *
 * Controlador público para la carta digital.
 * No requiere autenticación. Accesible mediante el unique_hash de la mesa.
 *
 * @author Ayrton
 */
class MenuController extends Controller
{
    /**
     * Muestra la carta digital pública de un restaurante para una mesa concreta.
     * Carga las categorías activas con sus productos e ingredientes alérgenos.
     *
     * @param  string  $hash  El unique_hash asignado a la mesa
     * @return View
     */
    public function show(string $hash): View
    {
        $table = Table::where('unique_hash', $hash)->firstOrFail();

        $categories = Category::where('user_id', $table->user_id)
            ->with([
                'products' => function ($query) {
                    $query->where('is_active', true)
                          ->with([
                              'ingredients' => function ($q) {
                                  $q->where('is_allergen', true);
                              },
                          ])
                          ->orderBy('name');
                },
            ])
            ->orderBy('name')
            ->get()
            ->filter(fn ($category) => $category->products->isNotEmpty());

        // Se consultan TODOS los alérgenos del restaurante directamente,
        // no solo los vinculados a productos activos, para que el filtro
        // refleje la realidad completa de la carta.
        $allergens = Ingredient::where('user_id', $table->user_id)
            ->where('is_allergen', true)
            ->orderBy('name')
            ->get();

        return view('menu.show', compact('table', 'categories', 'allergens'));
    }
}
