<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use Illuminate\View\View;

/**
 * Class MenuController
 *
 * Controlador público para la carta digital.
 * No requiere autenticación. Accesible mediante el unique_hash de la mesa.
 *
 * @author Ayrton
 * @author BenjaminDTS
 */
class MenuController extends Controller
{
    /**
     * Muestra la carta digital pública de un restaurante para una mesa concreta.
     * Filtra las categorías de cocina cuando la cocina está cerrada.
     * Calcula las variantes de tapa ya usadas y si se debe sugerir tapa al cliente.
     *
     * @param  string  $hash  El unique_hash asignado a la mesa
     * @return View
     */
    public function show(string $hash): View
    {
        $table  = Table::where('unique_hash', $hash)->firstOrFail();
        $config = $table->user->tapaConfig;

        $kitchenOpen = ! ($config && $config->tapas_enabled) || $config->isKitchenOpen();

        $categories = Category::where('user_id', $table->user_id)
            ->when(! $kitchenOpen, fn ($q) => $q->where('destination', 'bar'))
            ->with([
                'products' => function ($query) {
                    $query->where('is_active', true)
                          ->with([
                              'ingredients' => function ($q) {
                                  $q->withPivot(['is_removable', 'is_extra', 'extra_price'])
                                    ->select([
                                        'ingredients.id',
                                        'ingredients.name',
                                        'ingredients.is_allergen',
                                        'ingredients.allergen_type',
                                    ]);
                              },
                          ])
                          ->orderBy('name');
                },
            ])
            ->orderBy('name')
            ->get()
            ->filter(fn ($category) => $category->products->isNotEmpty());

        $allergens = $categories
            ->flatMap(fn ($c) => $c->products)
            ->flatMap(fn ($p) => $p->ingredients->where('is_allergen', true))
            ->unique(fn ($i) => $i->allergen_type ?? 'name:'.$i->name)
            ->sortBy('name')
            ->values();

        $tapaConfig        = null;
        $barItemsCount     = 0;
        $tapaVariantsUsed  = 0;
        $tapaProducts      = collect();
        $shouldSuggest     = false;

        if ($config && $config->tapas_enabled) {
            $tapaConfig    = $config;
            $barItemsCount = OrderItem::whereHas('order', fn ($q) =>
                $q->where('table_id', $table->id)
                  ->where('status', '!=', 'closed')
            )->where('destination', 'bar')->sum('quantity');

            $tapaCategory = Category::where('user_id', $table->user_id)
                                    ->where('name', 'Tapas')
                                    ->first();

            if ($tapaCategory) {
                $tapaVariantsUsed = OrderItem::whereHas('order', fn ($q) =>
                    $q->where('table_id', $table->id)
                      ->where('status', '!=', 'closed')
                )->whereHas('product', fn ($q) =>
                    $q->where('category_id', $tapaCategory->id)
                )->distinct('product_id')->count('product_id');

                $tapaProducts = $tapaCategory->products()
                                             ->where('is_active', true)
                                             ->orderBy('name')
                                             ->get(['id', 'name', 'price', 'description']);
            }

            $shouldSuggest = $config->shouldSuggestTapa((int) $barItemsCount, $tapaVariantsUsed);
        }

        $activeOrder = Order::where('table_id', $table->id)
            ->whereIn('status', ['pending', 'cooking', 'ready'])
            ->where('payment_status', 'pending')
            ->latest()
            ->first();

        $hasActiveOrder   = (bool) $activeOrder;
        $activeOrderTotal = $activeOrder?->total ?? 0;
        $billRequested    = (bool) ($activeOrder?->bill_requested);

        $stripePublicKey = config('services.stripe.key');

        return view('menu.show', compact(
            'table', 'categories', 'allergens',
            'tapaConfig', 'barItemsCount', 'kitchenOpen',
            'tapaVariantsUsed', 'tapaProducts', 'shouldSuggest',
            'hasActiveOrder', 'activeOrderTotal', 'billRequested', 'stripePublicKey'
        ));
    }
}
