<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use Illuminate\Support\Facades\Cache;
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
        $table = Table::where('unique_hash', $hash)
            ->where('is_service_point', true)
            ->with(['user.tapaConfig.kitchenSchedules', 'user.tapaConfig.businessSchedules'])
            ->firstOrFail();
        $config = $table->user->tapaConfig;

        $businessOpen           = ! $config || $config->isBusinessOpen();
        $orderingAllowed        = ! $config || $config->isOrderingAllowed();
        $businessNextOpening    = ($config && ! $businessOpen) ? $config->getBusinessNextOpeningTime() : null;
        $minutesUntilClose      = ($config && $businessOpen && ! $orderingAllowed) ? $config->minutesUntilBusinessClose() : null;

        $kitchenOpen     = ! $config || $config->isKitchenOpen();
        $nextOpeningTime = ($config && ! $kitchenOpen) ? $config->nextOpeningTime() : null;

        $allCategories = Cache::remember("menu:{$table->user_id}", 300, function () use ($table) {
            return Category::where('user_id', $table->user_id)
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
                                            'ingredients.allergen_types',
                                        ]);
                                  },
                                  'variants',
                              ])
                              ->orderBy('name');
                    },
                ])
                ->orderBy('name')
                ->get();
        });

        $categories = $allCategories
            ->filter(fn ($cat) => $kitchenOpen || $cat->destination === 'bar')
            ->filter(fn ($cat) => ! ($config && $config->tapas_enabled) || $cat->name !== 'Tapas')
            ->filter(fn ($cat) => $cat->products->isNotEmpty())
            ->values();

        $allergens = $categories
            ->flatMap(fn ($c) => $c->products)
            ->flatMap(fn ($p) => $p->ingredients->where('is_allergen', true))
            ->flatMap(fn ($i) => $i->allergen_types ?? [])
            ->unique()
            ->map(fn ($slug) => (object)[
                'slug'  => $slug,
                'name'  => Ingredient::ALLERGEN_TYPES[$slug] ?? $slug,
                'emoji' => Ingredient::ALLERGEN_EMOJIS[$slug] ?? '⚠️',
            ])
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
                )->whereHas('product.categories', fn ($q) =>
                    $q->where('categories.id', $tapaCategory->id)
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

        $hasActiveOrder      = (bool) $activeOrder;
        $originalOrderTotal  = $activeOrder?->total ?? 0;
        $paidViaSplit        = $activeOrder?->getPaidAmountViaSplit() ?? 0;
        $activeOrderTotal    = max(0, $originalOrderTotal - $paidViaSplit);
        $billRequested       = (bool) ($activeOrder?->bill_requested);

        $stripePublicKey = config('services.stripe.key');

        $splitPaymentEnabled  = $table->user->isSplitPaymentEnabled();
        $splitPaymentMaxParts = $table->user->split_payment_max_parts;

        $activeOrderItemsForAlpine = $activeOrder
            ? $activeOrder->items()->with(['product:id,name', 'splitPayments'])->get()
                ->map(fn (OrderItem $item) => [
                    'id'       => $item->id,
                    'name'     => $item->product?->name ?? 'Producto',
                    'quantity' => $item->quantity,
                    'price'    => (float) $item->price,
                    'total'    => round((float) $item->price * $item->quantity, 2),
                    'claimed'  => $item->isClaimed(),
                ])
                ->values()
                ->toArray()
            : [];

        $theme = $table->user->menu_style ?: 'modern';

        return view('menu.show', compact(
            'theme', 'table', 'categories', 'allergens',
            'tapaConfig', 'barItemsCount', 'kitchenOpen', 'nextOpeningTime',
            'tapaVariantsUsed', 'tapaProducts', 'shouldSuggest',
            'hasActiveOrder', 'activeOrderTotal', 'originalOrderTotal', 'billRequested', 'stripePublicKey',
            'splitPaymentEnabled', 'splitPaymentMaxParts', 'activeOrderItemsForAlpine',
            'businessOpen', 'orderingAllowed', 'businessNextOpening', 'minutesUntilClose'
        ));
    }
}
