<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Gestiona los flujos de pago desde el panel del camarero.
 *
 * @author SebastianBCF
 */
class PaymentController extends Controller
{
    /**
     * El camarero confirma el pago en efectivo de un pedido.
     * Cierra el pedido y registra el método de pago como cash.
     * La propina en efectivo no se registra digitalmente.
     *
     * @param  Order  $order
     * @return JsonResponse
     */
    public function cashPayment(Order $order): JsonResponse
    {
        abort_if($order->table->user_id !== Auth::id(), 403, 'Acceso denegado.');
        abort_if($order->status === 'closed', 422, 'Este pedido ya está cerrado.');

        $order->update([
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status'         => 'closed',
            'bill_requested' => false,
        ]);

        return response()->json(['success' => true]);
    }
}
