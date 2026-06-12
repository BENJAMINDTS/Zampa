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
            ->with(['user.tapaConfig.kitchenSchedules', 'user.tapaConfig.businessSchedules', 'zone'])
            ->firstOrFail();
        $config = $table->user->tapaConfig;

        $businessOpen           = ! $config || $config->isBusinessOpen();
        $orderingAllowed        = ! $config || $config->isOrderingAllowed();
        $businessNextOpening    = ($config && ! $businessOpen) ? $config->getBusinessNextOpeningTime() : null;
        $minutesUntilClose      = ($config && $businessOpen && ! $orderingAllowed) ? $config->minutesUntilBusinessClose() : null;
        $businessCloseAt        = ($config && $businessOpen) ? $config->businessCloseAtDisplay() : null;

        $kitchenOpen              = ! $config || $config->isKitchenOpen();
        $nextOpeningTime          = ($config && ! $kitchenOpen) ? $config->nextOpeningTime() : null;
        $minutesUntilKitchenClose = ($config && $kitchenOpen) ? $config->minutesUntilKitchenClose() : null;
        $kitchenCloseAt           = ($config && $kitchenOpen) ? $config->kitchenCloseAtDisplay() : null;

        $allCategories = Cache::remember("menu:{$table->user_id}", 300, function () use ($table) {
            return Category::where('user_id', $table->user_id)
                ->with([
                    'products' => function ($query) {
                        $query->where('is_active', true)
                              ->where('is_available', true)
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
                              ->orderBy('sort_order')
                              ->orderBy('name');
                    },
                ])
                ->orderBy('name')
                ->get();
        });

        $categories = $allCategories
            ->filter(fn ($cat) => $cat->products->isNotEmpty())
            ->when($config && $config->tapas_enabled, fn ($col) => $col->reject(fn ($cat) => $cat->name === 'Tapas'))
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

        $tapaConfig          = null;
        $barItemsCount       = 0;
        $tapaVariantsUsed    = 0;
        $tapasQuantityUsed   = 0;
        $tapaProducts        = collect();
        $shouldSuggest       = false;

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

                $tapasQuantityUsed = OrderItem::whereHas('order', fn ($q) =>
                    $q->where('table_id', $table->id)
                      ->where('status', '!=', 'closed')
                )->whereHas('product.categories', fn ($q) =>
                    $q->where('categories.id', $tapaCategory->id)
                )->sum('quantity');

                $tapaProducts = $tapaCategory->products()
                                             ->where('is_active', true)
                                             ->where('is_available', true)
                                             ->orderBy('name')
                                             ->get(['id', 'name', 'price', 'description']);
            }

            $shouldSuggest = $config->shouldSuggestTapa((int) $barItemsCount, $tapaVariantsUsed);
        }

        $activeOrders = Order::activeForTable($table->id)->get();
        $activeOrder  = $activeOrders->sortByDesc('created_at')->first();

        $hasActiveOrder   = $activeOrders->isNotEmpty();
        $activeOrderTotal = $activeOrders->sum(
            fn ($o) => max(0, $o->total - $o->getPaidAmountViaSplit())
        );
        $billRequested    = $activeOrders->contains('bill_requested', true);

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

        $dmSectionLabels = [
            'first_course'  => 'Primer plato',
            'second_course' => 'Segundo plato',
            'dessert'       => 'Postre',
            'coffee'        => 'Café',
            'drink'         => 'Bebida',
            'bread'         => 'Pan',
        ];

        $allOrdersForAlpine = Order::where('table_id', $table->id)
            ->whereNotIn('status', ['closed'])
            ->where('payment_status', 'pending')
            ->orderBy('created_at')
            ->with([
                'items.product:id,name',
                'dailyMenuOrder.dailyMenu:id,title',
                'dailyMenuOrder.selections.section:id,type',
                'dailyMenuOrder.selections.product:id,name',
            ])
            ->get()
            ->values()
            ->map(fn (Order $order, int $index) => [
                'id'          => $order->id,
                'number'      => $index + 1,
                'itemCount'   => $order->dailyMenuOrder
                    ? $order->dailyMenuOrder->selections->count()
                    : (int) $order->items->sum('quantity'),
                'total'       => (float) $order->total,
                'sentAt'      => $order->created_at->format('H:i'),
                'isDailyMenu' => $order->dailyMenuOrder !== null,
                'dmTitle'     => $order->dailyMenuOrder?->dailyMenu?->title ?? 'Menú del Día',
                'picks'       => $order->dailyMenuOrder
                    ? $order->dailyMenuOrder->selections->map(fn ($sel) => [
                        'lab' => $dmSectionLabels[$sel->section?->type ?? ''] ?? ($sel->section?->type ?? 'Plato'),
                        'val' => $sel->product?->name ?? '—',
                    ])->values()->toArray()
                    : [],
                'items'       => $order->dailyMenuOrder
                    ? []
                    : $order->items->map(fn (OrderItem $item) => [
                        'name'     => $item->product?->name ?? 'Producto',
                        'quantity' => (int) $item->quantity,
                        'price'    => (float) $item->price,
                    ])->values()->toArray(),
            ])
            ->toArray();

        $theme = $table->user->menu_style ?: 'modern';

        return view('menu.show', compact(
            'theme', 'table', 'categories', 'allergens',
            'tapaConfig', 'barItemsCount', 'kitchenOpen', 'nextOpeningTime',
            'tapaVariantsUsed', 'tapasQuantityUsed', 'tapaProducts', 'shouldSuggest',
            'hasActiveOrder', 'activeOrderTotal', 'billRequested', 'stripePublicKey',
            'splitPaymentEnabled', 'splitPaymentMaxParts', 'activeOrderItemsForAlpine', 'allOrdersForAlpine',
            'businessOpen', 'orderingAllowed', 'businessNextOpening', 'minutesUntilClose',
            'minutesUntilKitchenClose', 'kitchenCloseAt', 'businessCloseAt'
        ));
    }
}
