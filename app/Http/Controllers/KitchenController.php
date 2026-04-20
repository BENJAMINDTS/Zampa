<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Panel de cocina en tiempo real.
 *
 * Muestra las comandas pendientes (status=queued) agrupadas por pedido,
 * permite marcar ítems como listos y cierra el pedido automáticamente
 * cuando todos sus ítems han sido preparados.
 *
 * @author AyrtonAlania
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
        $orders = $this->getActiveOrders();

        return view('kitchen.index', compact('orders'));
    }

    /**
     * Endpoint JSON consumido por el polling de Alpine.js cada 5 segundos.
     *
     * @return JsonResponse
     */
    public function pendingOrders(): JsonResponse
    {
        $orders = $this->getActiveOrders()
            ->map(fn(Order $order) => [
                'id'         => $order->id,
                'table_name' => $order->table->name,
                'created_at' => $order->created_at->format('H:i'),
                'status'     => $order->status,
                'items'      => $order->items
                    ->where('status', 'queued')
                    ->map(fn(OrderItem $item) => [
                        'id'            => $item->id,
                        'product_name'  => $item->product->name,
                        'quantity'      => $item->quantity,
                        'modifications' => $item->modifications
                            ->map(fn($mod) => [
                                'action'     => $mod->action,
                                'ingredient' => $mod->ingredient->name,
                            ])->values(),
                    ])->values(),
            ])
            ->filter(fn($order) => count($order['items']) > 0)
            ->values();

        $readyCount = Order::whereHas('table', fn($q) => $q->where('user_id', Auth::id()))
            ->where('status', 'ready')
            ->count();

        return response()->json([
            'orders'      => $orders,
            'ready_count' => $readyCount,
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
        $order = $item->order()->with('table')->first();

        abort_if($order->table->user_id !== Auth::id(), 403, 'Acceso denegado.');

        $item->update(['status' => 'ready']);

        $allReady = $order->items()->where('status', 'queued')->doesntExist();

        if ($allReady) {
            $order->update(['status' => 'served']);
        }

        return response()->json([
            'item_id'      => $item->id,
            'order_id'     => $order->id,
            'order_status' => $order->fresh()->status,
            'all_ready'    => $allReady,
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
                ->with(['product', 'modifications.ingredient']),
        ])
        ->whereHas('table', fn($q) => $q->where('user_id', Auth::id()))
        ->whereIn('status', ['pending', 'cooking'])
        ->orderBy('created_at')
        ->get();
    }
}
