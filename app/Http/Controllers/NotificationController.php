<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Gestiona las notificaciones al camarero cuando una comanda está lista.
 *
 * Expone dos endpoints consumidos por el polling de Alpine.js en el panel
 * de barra: uno para listar las comandas listas y otro para confirmar
 * (dismiss) la notificación una vez el camarero las ha recogido.
 *
 * @author BenjaminDTS
 */
class NotificationController extends Controller
{
    /**
     * Devuelve las órdenes con notification_ready=true del restaurante autenticado.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function ready(Request $request): JsonResponse
    {
        $orders = Order::with([
                'table:id,name',
                'items' => fn($q) => $q
                    ->where('destination', 'kitchen')
                    ->select(['id', 'order_id', 'destination', 'quantity', 'product_id'])
                    ->with([
                        'product:id,name',
                        'product.categories:id,name',
                    ]),
            ])
            ->select(['id', 'table_id', 'notification_ready'])
            ->where('notification_ready', true)
            ->whereHas('table', fn($q) => $q->where('user_id', Auth::user()->ownerUserId()))
            ->get()
            ->map(fn(Order $order) => [
                'id'    => $order->id,
                'table' => $order->table->name,
                'items' => $order->items->map(fn($item) => [
                    'name'     => $item->product->name,
                    'quantity' => $item->quantity,
                    'is_tapa'  => $item->product->categories->contains('name', 'Tapas'),
                ])->values(),
            ])
            ->values();

        return response()->json(['orders' => $orders]);
    }

    /**
     * Descarta la notificación de una comanda concreta.
     *
     * @param  Order  $order
     * @return JsonResponse
     */
    public function dismiss(Order $order): JsonResponse
    {
        abort_if($order->table->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

        $order->update(['notification_ready' => false]);

        return response()->json(['success' => true]);
    }

    /**
     * Devuelve los pedidos con bill_requested=true del restaurante autenticado.
     *
     * @return JsonResponse
     */
    public function billRequests(): JsonResponse
    {
        $orders = Order::with('table')
            ->where('bill_requested', true)
            ->whereHas('table', fn ($q) => $q->where('user_id', Auth::user()->ownerUserId()))
            ->get()
            ->map(fn (Order $order) => [
                'id'                => $order->id,
                'table'             => $order->table->name,
                'payment_method'    => $order->requested_payment_method,
                'subtotal'          => round((float) $order->total, 2),
                'tip'               => round((float) $order->tip, 2),
                'mixed_cash_amount' => $order->requested_payment_method === 'mixed'
                    ? round((float) $order->mixed_cash_amount, 2)
                    : null,
                'amount_to_collect' => $order->requested_payment_method === 'mixed'
                    ? round((float) $order->mixed_cash_amount, 2)
                    : round((float) $order->total + (float) $order->tip, 2),
            ])
            ->values();

        return response()->json(['orders' => $orders]);
    }

    /**
     * Descarta la solicitud de cuenta de un pedido concreto.
     *
     * @param  Order  $order
     * @return JsonResponse
     */
    public function dismissBillRequest(Order $order): JsonResponse
    {
        abort_if($order->table->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

        $order->update(['bill_requested' => false]);

        return response()->json(['success' => true]);
    }

    /**
     * Devuelve los pedidos con waiter_called=true del restaurante autenticado.
     *
     * @return JsonResponse
     */
    public function waiterCalls(): JsonResponse
    {
        $orders = Order::with('table')
            ->where('waiter_called', true)
            ->whereHas('table', fn ($q) => $q->where('user_id', Auth::user()->ownerUserId()))
            ->get()
            ->map(fn (Order $order) => [
                'id'        => $order->id,
                'table'     => $order->table->name,
                'called_at' => $order->waiter_called_at?->format('H:i'),
            ])
            ->values();

        return response()->json(['orders' => $orders]);
    }

    /**
     * Descarta la llamada al camarero de un pedido concreto.
     *
     * @param  Order  $order
     * @return JsonResponse
     */
    public function dismissWaiterCall(Order $order): JsonResponse
    {
        abort_if($order->table->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

        $order->update(['waiter_called' => false]);

        return response()->json(['success' => true]);
    }
}
