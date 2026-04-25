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
        $orders = Order::with('table')
            ->where('notification_ready', true)
            ->whereHas('table', fn($q) => $q->where('user_id', Auth::id()))
            ->get()
            ->map(fn(Order $order) => [
                'id'    => $order->id,
                'table' => $order->table->name,
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
        abort_if($order->table->user_id !== Auth::id(), 403, 'Acceso denegado.');

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
            ->whereHas('table', fn ($q) => $q->where('user_id', Auth::id()))
            ->get()
            ->map(fn (Order $order) => [
                'id'              => $order->id,
                'table'           => $order->table->name,
                'payment_method'  => $order->requested_payment_method,
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
        abort_if($order->table->user_id !== Auth::id(), 403, 'Acceso denegado.');

        $order->update(['bill_requested' => false]);

        return response()->json(['success' => true]);
    }
}
