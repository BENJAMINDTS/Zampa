<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemModification;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @author SebastianBCF
 * @author BenjaminDTS
 */
class OrderController extends Controller
{
    /**
     * Persiste un nuevo pedido recibido desde la carta digital pública.
     *
     * Para ítems de la categoría "Tapas", el precio del snapshot se resuelve
     * usando TapaConfig::getPriceForProduct() según la modalidad activa,
     * garantizando que lo cobrado coincide con lo mostrado al cliente.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'table_hash'                             => 'required|string',
            'items'                                  => 'required|array|min:1',
            'items.*.product_id'                     => 'required|integer|exists:products,id',
            'items.*.variant_id'                     => 'nullable|integer|exists:product_variants,id',
            'items.*.quantity'                       => 'required|integer|min:1|max:99',
            'items.*.modifications'                  => 'nullable|array',
            'items.*.modifications.*.ingredient_id'  => 'required|integer|exists:ingredients,id',
            'items.*.modifications.*.action'         => 'required|in:add,remove',
            'items.*.modifications.*.amount_charged' => 'nullable|numeric|min:0',
        ]);

        $table      = Table::where('unique_hash', $validated['table_hash'])->firstOrFail();
        $tapaConfig = $table->user->tapaConfig?->load(['kitchenSchedules', 'businessSchedules']);

        if ($tapaConfig && ! $tapaConfig->isBusinessOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'El negocio está cerrado. No se pueden realizar pedidos en este momento.',
            ], 422);
        }

        if ($tapaConfig && ! $tapaConfig->isOrderingAllowed()) {
            return response()->json([
                'success' => false,
                'message' => 'El tiempo de pedidos ha finalizado. Solo puedes solicitar la cuenta.',
            ], 422);
        }

        if ($tapaConfig && ! $tapaConfig->isKitchenOpen()) {
            $hasKitchenItems = Product::with('category')
                ->whereIn('id', collect($validated['items'])->pluck('product_id'))
                ->whereHas('category', fn ($q) => $q->where('destination', 'kitchen'))
                ->exists();

            if ($hasKitchenItems) {
                return response()->json([
                    'success' => false,
                    'message' => 'La cocina está cerrada. Solo puedes pedir productos de barra.',
                ], 422);
            }
        }

        // Pre-validate variant ownership and product/variant consistency before opening transaction.
        $resolvedItems = [];
        foreach ($validated['items'] as $itemData) {
            $product   = Product::with('category')->findOrFail($itemData['product_id']);
            $variantId = $itemData['variant_id'] ?? null;
            $variant   = null;

            if ($variantId !== null) {
                $variant = ProductVariant::findOrFail($variantId);
                if ($variant->product_id !== $product->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La variante no pertenece al producto indicado.',
                    ], 422);
                }
            }

            $productHasVariants = $product->variants()->exists();

            if ($productHasVariants && $variantId === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este producto requiere seleccionar una variante.',
                ], 422);
            }

            if (! $productHasVariants && $variantId !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este producto no tiene variantes.',
                ], 422);
            }

            if ($variant !== null) {
                $basePrice = (float) $variant->price;
            } elseif ($tapaConfig && $tapaConfig->tapas_enabled && $tapaConfig->isTapaProduct($product)) {
                $basePrice = $tapaConfig->getPriceForProduct($product);
            } else {
                $basePrice = (float) $product->price;
            }

            $resolvedItems[] = [
                'product'      => $product,
                'variant'      => $variant,
                'variantId'    => $variantId,
                'variantName'  => $variant?->name,
                'basePrice'    => $basePrice,
                'quantity'     => $itemData['quantity'],
                'modifications' => $itemData['modifications'] ?? [],
            ];
        }

        $order = DB::transaction(function () use ($resolvedItems, $table) {
            $order = Order::create([
                'table_id'       => $table->id,
                'status'         => 'pending',
                'payment_status' => 'pending',
                'total'          => 0,
            ]);

            $total = 0;

            foreach ($resolvedItems as $item) {
                $extraCharge = collect($item['modifications'])
                    ->where('action', 'add')
                    ->sum('amount_charged');

                $unitPrice = $item['basePrice'] + (float) $extraCharge;
                $total    += $unitPrice * $item['quantity'];

                $orderItem = OrderItem::create([
                    'order_id'    => $order->id,
                    'product_id'  => $item['product']->id,
                    'variant_id'  => $item['variantId'],
                    'variant_name' => $item['variantName'],
                    'quantity'    => $item['quantity'],
                    'price'       => $item['basePrice'],
                    'status'      => 'queued',
                    'destination' => $item['product']->category->destination,
                ]);

                foreach ($item['modifications'] as $mod) {
                    OrderItemModification::create([
                        'order_item_id'  => $orderItem->id,
                        'ingredient_id'  => $mod['ingredient_id'],
                        'action'         => $mod['action'],
                        'amount_charged' => $mod['amount_charged'] ?? 0,
                    ]);
                }
            }

            $order->update(['total' => round($total, 2)]);

            return $order;
        });

        return response()->json([
            'success'  => true,
            'order_id' => $order->id,
            'total'    => $order->total,
        ], 201);
    }
}
