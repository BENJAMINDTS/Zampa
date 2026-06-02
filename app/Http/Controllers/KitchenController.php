<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Panel de cocina en tiempo real.
 *
 * Muestra las comandas pendientes (status=queued) agrupadas por pedido,
 * permite marcar ítems como listos y cierra el pedido automáticamente
 * cuando todos sus ítems han sido preparados.
 *
 * @author AyrtonAlania
 * @author BenjaminDTS
 * @author SebastianBCF
 */
class KitchenController extends Controller
{
    /**
     * Devuelve los pedidos activos con ítems pendientes para la vista principal.
     *
     * @return View
     */
    public function index(): View
    {
        abort_if(! Auth::user()->canAccessKitchen(), 403, 'Acceso denegado.');

        $orders = $this->getActiveOrders();

        return view('kitchen.index', compact('orders'));
    }

    /**
     * Vista de gestión de disponibilidad de productos de cocina.
     *
     * @return View
     */
    public function availability(): View
    {
        abort_if(! Auth::user()->canAccessKitchen(), 403, 'Acceso denegado.');

        $products = $this->getKitchenProducts();

        return view('kitchen.availability', compact('products'));
    }

    /**
     * Endpoint ligero para el badge del nav: devuelve el conteo de pedidos pendientes.
     *
     * @return JsonResponse
     */
    public function badgeCount(): JsonResponse
    {
        abort_if(! Auth::user()->canAccessKitchen(), 403, 'Acceso denegado.');

        $ownerId = Auth::user()->ownerUserId();
        $count   = Order::whereHas('table', fn($q) => $q->where('user_id', $ownerId))
            ->whereHas('items', fn($q) => $q->where('destination', 'kitchen')->where('status', 'queued'))
            ->whereIn('status', ['pending', 'cooking'])
            ->count();

        return response()->json(['pending_count' => $count]);
    }

    /**
     * Endpoint JSON consumido por el polling de Alpine.js cada 5 segundos.
     *
     * @return JsonResponse
     */
    public function pendingOrders(): JsonResponse
    {
        abort_if(! Auth::user()->canAccessKitchen(), 403, 'Acceso denegado.');

        $rawOrders    = $this->getActiveOrders();
        $pendingCount = $rawOrders->where('status', 'pending')->count();

        $orders = $rawOrders
            ->map(fn(Order $order) => [
                'id'         => $order->id,
                'table_name' => $order->table->name,
                'created_at' => $order->created_at->format('H:i'),
                'status'     => $order->status,
                'items'      => $order->items
                    ->where('status', 'queued')
                    ->map(fn(OrderItem $item) => [
                        'id'            => $item->id,
                        'product_name'  => $item->product->name . ($item->variant_name ? ' — ' . $item->variant_name : ''),
                        'quantity'      => $item->quantity,
                        'is_daily_menu' => (bool) $item->is_daily_menu,
                        'modifications' => $item->modifications
                            ->map(fn($mod) => [
                                'action'     => $mod->action,
                                'ingredient' => $mod->ingredient->name,
                            ])->values(),
                    ])->values(),
            ])
            ->filter(fn($order) => count($order['items']) > 0)
            ->values();

        return response()->json([
            'orders'        => $orders,
            'pending_count' => $pendingCount,
        ]);
    }

    /**
     * Marca un ítem como listo y cierra el pedido si todos los ítems están preparados.
     *
     * @param  OrderItem  $item
     * @return JsonResponse
     */
    public function markItemReady(OrderItem $item): JsonResponse
    {
        abort_if(! Auth::user()->canAccessKitchen(), 403, 'Acceso denegado.');

        $order = $item->order()->with('table')->first();

        abort_if($order->table->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');
        abort_if($item->destination !== 'kitchen', 403, 'Este ítem no pertenece a cocina.');

        $item->update(['status' => 'ready']);

        if ($order->status === 'pending') {
            $order->update(['status' => 'cooking']);
        }

        $allReady = $order->fresh()->items()
            ->whereNotIn('status', ['ready', 'served'])
            ->doesntExist();

        if ($allReady) {
            $order->update(['status' => 'ready', 'notification_ready' => true]);
        }

        return response()->json([
            'item_id'      => $item->id,
            'order_id'     => $order->id,
            'order_status' => $order->fresh()->status,
            'all_ready'    => $allReady,
        ]);
    }

    /**
     * Marca un pedido completo como servido, limpiando la notificación del badge.
     *
     * @param  Order  $order
     * @return JsonResponse
     */
    public function markOrderServed(Order $order): JsonResponse
    {
        abort_if(! Auth::user()->canAccessKitchen(), 403, 'Acceso denegado.');

        abort_if($order->table->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');
        abort_if($order->status !== 'ready', 422, 'El pedido no está listo para servir.');

        $order->update(['status' => 'served', 'notification_ready' => false]);

        return response()->json(['order_id' => $order->id, 'status' => 'served']);
    }

    /**
     * Activa o desactiva temporalmente un producto de cocina durante el servicio.
     * Solo permite gestionar productos cuya categoría tenga destination=kitchen.
     *
     * @param  Product  $product
     * @return JsonResponse
     */
    public function toggleAvailability(Product $product): JsonResponse
    {
        abort_if(! Auth::user()->canAccessKitchen(), 403, 'Acceso denegado.');

        $ownerId = Auth::user()->ownerUserId();
        abort_if($product->user_id !== $ownerId, 403, 'Acceso denegado.');

        $isKitchenProduct = $product->categories()
            ->where('destination', 'kitchen')
            ->exists();
        abort_if(! $isKitchenProduct, 403, 'Este producto no pertenece a cocina.');

        $product->update(['is_available' => ! $product->is_available]);

        Cache::forget("menu:{$ownerId}");

        return response()->json([
            'product_id'   => $product->id,
            'is_available' => $product->is_available,
        ]);
    }

    /**
     * Consulta los pedidos activos (cooking o pending) con ítems en cola
     * pertenecientes al restaurante autenticado.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getActiveOrders(): \Illuminate\Database\Eloquent\Collection
    {
        return Order::with([
            'table',
            'items' => fn($q) => $q
                ->where('status', 'queued')
                ->where('destination', 'kitchen')
                ->with(['product', 'modifications.ingredient']),
        ])
        ->whereHas('table', fn($q) => $q->where('user_id', Auth::user()->ownerUserId()))
        ->whereHas('items', fn($q) => $q->where('destination', 'kitchen')->where('status', 'queued'))
        ->whereIn('status', ['pending', 'cooking'])
        ->orderBy('created_at')
        ->get();
    }

    /**
     * Devuelve los productos activos de cocina del restaurante para gestionar disponibilidad.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getKitchenProducts(): \Illuminate\Database\Eloquent\Collection
    {
        $ownerId = Auth::user()->ownerUserId();

        return Product::where('user_id', $ownerId)
            ->where('is_active', true)
            ->whereHas('categories', fn ($q) => $q->where('destination', 'kitchen'))
            ->with('categories:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'is_available']);
    }
}
