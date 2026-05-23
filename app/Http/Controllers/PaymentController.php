<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Gestiona los flujos de pago desde el panel del camarero.
 *
 * @author SebastianBCF
 * @author BenjaminDTS
 */
class PaymentController extends Controller
{
    /**
     * El camarero confirma el pago en efectivo de un pedido.
     * Si hay partes ya pagadas con tarjeta (cobro partido), registra todos los
     * pagos en order_payments y cierra el pedido como 'split'.
     * En caso contrario, cierra como pago puro en efectivo.
     * La propina en efectivo nunca se registra digitalmente.
     *
     * @param  Order  $order
     * @return JsonResponse
     */
    public function cashPayment(Order $order): JsonResponse
    {
        abort_if($order->table->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');
        abort_if($order->status === 'closed', 422, 'Este pedido ya está cerrado.');

        $paidCardParts = $order->splitPayments()->where('status', 'paid')->get();

        if ($paidCardParts->isNotEmpty()) {
            // Leer propina en efectivo del bill-request antes de sobreescribir orders.tip
            $cashTip = max(0, round((float) $order->tip, 2));

            DB::transaction(function () use ($order, $paidCardParts, $cashTip) {
                $cardPayments = $paidCardParts
                    ->groupBy('stripe_payment_intent_id')
                    ->map(fn ($parts) => [
                        'amount' => round((float) $parts->sum('amount'), 2),
                        'tip'    => round((float) $parts->sum('tip'), 2),
                    ]);

                foreach ($cardPayments as $payment) {
                    OrderPayment::create([
                        'order_id' => $order->id,
                        'method'   => 'card',
                        'amount'   => $payment['amount'],
                        'tip'      => $payment['tip'],
                    ]);
                }

                $paidCardAmount = round((float) $paidCardParts->sum('amount'), 2);
                $remaining      = max(0, round((float) $order->total - $paidCardAmount, 2));
                $cardTips       = round((float) $paidCardParts->sum('tip'), 2);

                OrderPayment::create([
                    'order_id' => $order->id,
                    'method'   => 'cash',
                    'amount'   => $remaining,
                    'tip'      => $cashTip,
                ]);

                $order->update([
                    'payment_method' => 'split',
                    'payment_status' => 'paid',
                    'status'         => 'closed',
                    'bill_requested' => false,
                    'tip'            => $cardTips + $cashTip,
                ]);
            });
        } else {
            $order->update([
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'status'         => 'closed',
                'bill_requested' => false,
            ]);
        }

        return response()->json(['success' => true]);
    }
}
