<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Panel de barra en tiempo real.
 *
 * Muestra los ítems con destination=bar pendientes (status=queued)
 * agrupados por pedido, permite marcarlos como listos y cierra
 * automáticamente el pedido cuando todos sus ítems están preparados.
 *
 * @author SebastianBCF
 * @author BenjaminDTS
 */
class BarPanelController extends Controller
{
    /**
     * Devuelve los pedidos activos con ítems de barra pendientes.
     *
     * @return View
     */
    public function index(): View
    {
        abort_if(! Auth::user()->canAccessBar(), 403, 'Acceso denegado.');

        $orders   = $this->getActiveOrders();
        $ownerId  = Auth::user()->ownerUserId();

        $readyOrders = Order::with('table')
            ->where('notification_ready', true)
            ->whereHas('table', fn($q) => $q->where('user_id', $ownerId))
            ->get()
            ->map(fn(Order $order) => [
                'id'    => $order->id,
                'table' => $order->table->name,
            ])
            ->values();

        return view('bar.index', compact('orders', 'readyOrders'));
    }

    /**
     * Endpoint para el badge del nav: conteo de pedidos con ítems de barra pendientes.
     *
     * @return JsonResponse
     */
    public function badgeCount(): JsonResponse
    {
        abort_if(! Auth::user()->canAccessBar(), 403, 'Acceso denegado.');

        $ownerId = Auth::user()->ownerUserId();
        $count   = Order::whereHas('table', fn($q) => $q->where('user_id', $ownerId))
            ->whereHas('items', fn($q) => $q->where('destination', 'bar')->where('status', 'queued'))
            ->whereIn('status', ['pending', 'cooking'])
            ->count();

        return response()->json(['pending_count' => $count]);
    }

    /**
     * Endpoint JSON consumido por el polling de Alpine.js cada 5 segundos.
     *
     * @return JsonResponse
     */
    public function pendingItems(): JsonResponse
    {
        abort_if(! Auth::user()->canAccessBar(), 403, 'Acceso denegado.');

        $orders = $this->getActiveOrders()
            ->map(fn(Order $order) => [
                'id'         => $order->id,
                'table_name' => $order->table->name,
                'created_at' => $order->created_at->format('H:i'),
                'status'     => $order->status,
                'items'      => $order->items
                    ->where('status', 'queued')
                    ->map(fn(OrderItem $item) => [
                        'id'           => $item->id,
                        'product_name' => $item->product->name,
                        'quantity'     => $item->quantity,
                    ])->values(),
            ])
            ->filter(fn($order) => count($order['items']) > 0)
            ->values();

        $ownerId      = Auth::user()->ownerUserId();
        $pendingCount = Order::whereHas('table', fn($q) => $q->where('user_id', $ownerId))
            ->whereHas('items', fn($q) => $q->where('destination', 'bar')->where('status', 'queued'))
            ->whereIn('status', ['pending', 'cooking'])
            ->count();

        return response()->json([
            'orders'        => $orders,
            'pending_count' => $pendingCount,
        ]);
    }

    /**
     * Marca un ítem de barra como listo y cierra el pedido si todos
     * los ítems (cocina + barra) están preparados.
     *
     * @param  OrderItem  $item
     * @return JsonResponse
     */
    public function updateItem(OrderItem $item): JsonResponse
    {
        abort_if(! Auth::user()->canAccessBar(), 403, 'Acceso denegado.');

        $order = $item->order()->with('table')->first();

        abort_if($order->table->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');
        abort_if($item->destination !== 'bar', 403, 'Este ítem no pertenece a la barra.');

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
     * Consulta los pedidos activos con ítems de barra en cola
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
                ->where('destination', 'bar')
                ->with(['product']),
        ])
        ->whereHas('table', fn($q) => $q->where('user_id', Auth::user()->ownerUserId()))
        ->whereHas('items', fn($q) => $q->where('destination', 'bar')->where('status', 'queued'))
        ->whereIn('status', ['pending', 'cooking'])
        ->orderBy('created_at')
        ->get();
    }
}
