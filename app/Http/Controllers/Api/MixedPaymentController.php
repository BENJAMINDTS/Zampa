<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Table;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestiona el cobro mixto (parte en tarjeta + parte en efectivo) desde la carta pública.
 * El cliente paga la parte de tarjeta via Stripe; el camarero recoge el efectivo.
 *
 * @author SebastianBCF
 */
class MixedPaymentController extends Controller
{
    public function __construct(private readonly StripeService $stripe) {}

    /**
     * Crea un PaymentIntent de Stripe para la parte en tarjeta del cobro mixto.
     *
     * @param  Request  $request
     * @param  string   $hash
     * @return JsonResponse
     */
    public function intent(Request $request, string $hash): JsonResponse
    {
        $validated = $request->validate([
            'cash_amount' => 'required|numeric|min:0.01',
            'tip'         => 'nullable|numeric|min:0|max:500',
        ]);

        $table = Table::with('user')->where('unique_hash', $hash)->firstOrFail();
        $order = $this->activeOrder($table->id);

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'No hay un pedido activo en esta mesa.'], 404);
        }

        $cashAmount = round((float) $validated['cash_amount'], 2);
        $tip        = (float) ($validated['tip'] ?? 0);
        $cardAmount = round($order->total - $cashAmount, 2);

        if ($cashAmount <= 0 || $cashAmount >= $order->total) {
            return response()->json(['success' => false, 'message' => 'El importe en efectivo debe ser mayor que cero y menor que el total.'], 422);
        }

        if ($cardAmount < 0.50) {
            return response()->json(['success' => false, 'message' => 'El importe mínimo a pagar con tarjeta es 0,50 €.'], 422);
        }

        $grandTotal = $cardAmount + $tip;

        $stripeAccountId = ($table->user->stripe_onboarding_completed && $table->user->stripe_account_id)
            ? $table->user->stripe_account_id
            : null;

        $result = $this->stripe->createPaymentIntent(
            amount:          (int) round($grandTotal * 100),
            currency:        'eur',
            metadata:        [
                'order_id'    => $order->id,
                'table_name'  => $table->name,
                'cash_amount' => $cashAmount,
                'tip'         => $tip,
            ],
            stripeAccountId: $stripeAccountId,
        );

        return response()->json([
            'success'       => true,
            'client_secret' => $result['client_secret'],
            'card_amount'   => $cardAmount,
            'cash_amount'   => $cashAmount,
            'tip'           => $tip,
            'grand_total'   => $grandTotal,
        ]);
    }

    /**
     * Confirma el pago con tarjeta y notifica al camarero para que cobre el efectivo.
     * El pedido no se cierra hasta que el camarero confirme la parte en efectivo.
     *
     * @param  Request  $request
     * @param  string   $hash
     * @return JsonResponse
     */
    public function confirm(Request $request, string $hash): JsonResponse
    {
        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
            'cash_amount'       => 'required|numeric|min:0.01',
            'tip'               => 'nullable|numeric|min:0|max:500',
        ]);

        $table = Table::where('unique_hash', $hash)->firstOrFail();
        $order = $this->activeOrder($table->id);

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'No hay un pedido activo en esta mesa.'], 404);
        }

        $intent = $this->stripe->confirmPayment($validated['payment_intent_id']);

        if ($intent['status'] !== 'succeeded') {
            return response()->json(['success' => false, 'message' => 'El pago con tarjeta no se ha completado.'], 422);
        }

        $order->update([
            'mixed_cash_amount'        => round((float) $validated['cash_amount'], 2),
            'bill_requested'           => true,
            'requested_payment_method' => 'mixed',
            'tip'                      => (float) ($validated['tip'] ?? 0),
        ]);

        return response()->json([
            'success'     => true,
            'order_id'    => $order->id,
            'cash_amount' => $order->mixed_cash_amount,
        ]);
    }

    /**
     * @param  int  $tableId
     * @return Order|null
     */
    private function activeOrder(int $tableId): ?Order
    {
        return Order::where('table_id', $tableId)
            ->whereIn('status', ['pending', 'cooking', 'ready'])
            ->where('payment_status', 'pending')
            ->latest()
            ->first();
    }
}
