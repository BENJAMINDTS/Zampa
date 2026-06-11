<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Table;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestiona el flujo de pago con tarjeta desde la carta pública.
 * Los endpoints son públicos — el cliente los llama directamente desde su móvil.
 *
 * @author BenjaminDTS
 */
class CardPaymentController extends Controller
{
    public function __construct(private readonly StripeService $stripe) {}

    /**
     * Devuelve el total del pedido activo para calcular porcentajes de propina.
     * No crea ningún PaymentIntent en Stripe.
     *
     * @param  string  $hash
     * @return JsonResponse
     */
    public function total(string $hash): JsonResponse
    {
        $table = Table::where('unique_hash', $hash)->firstOrFail();

        $orders = Order::activeForTable($table->id)->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay un pedido activo en esta mesa.',
            ], 404);
        }

        $remaining = $orders->sum(fn ($o) => max(0, $o->total - $o->getPaidAmountViaSplit()));

        return response()->json(['success' => true, 'total' => $remaining]);
    }

    /**
     * Crea un PaymentIntent de Stripe para el pedido activo de la mesa.
     * Acepta una propina opcional que se suma al total del pedido.
     * Devuelve el client_secret que necesita Stripe.js en el cliente.
     *
     * @param  Request  $request
     * @param  string   $hash
     * @return JsonResponse
     */
    public function intent(Request $request, string $hash): JsonResponse
    {
        $validated = $request->validate([
            'tip' => 'nullable|numeric|min:0|max:500',
        ]);

        $tip = (float) ($validated['tip'] ?? 0);

        $table = Table::with('user')->where('unique_hash', $hash)->firstOrFail();

        $orders = Order::activeForTable($table->id)->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay un pedido activo en esta mesa.',
            ], 404);
        }

        $remaining  = $orders->sum(fn ($o) => max(0, $o->total - $o->getPaidAmountViaSplit()));
        $grandTotal = $remaining + $tip;

        $stripeAccountId = ($table->user->stripe_onboarding_completed && $table->user->stripe_account_id)
            ? $table->user->stripe_account_id
            : null;

        $result = $this->stripe->createPaymentIntent(
            amount:          (int) round($grandTotal * 100),
            currency:        'eur',
            metadata:        ['order_ids' => $orders->pluck('id')->implode(','), 'table_name' => $table->name, 'tip' => $tip],
            stripeAccountId: $stripeAccountId,
        );

        return response()->json([
            'success'       => true,
            'client_secret' => $result['client_secret'],
            'total'         => $remaining,
            'tip'           => $tip,
            'grand_total'   => $grandTotal,
        ]);
    }

    /**
     * Verifica el pago con Stripe y cierra el pedido si ha sido exitoso.
     * El cliente llama a este endpoint tras la confirmación de Stripe.js.
     *
     * @param  Request  $request
     * @param  string   $hash
     * @return JsonResponse
     */
    public function confirm(Request $request, string $hash): JsonResponse
    {
        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
            'tip'               => 'nullable|numeric|min:0|max:500',
        ]);

        $table = Table::where('unique_hash', $hash)->firstOrFail();

        $orders = Order::activeForTable($table->id)->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay un pedido activo en esta mesa.',
            ], 404);
        }

        $intent = $this->stripe->confirmPayment($validated['payment_intent_id']);

        if ($intent['status'] !== 'succeeded') {
            return response()->json([
                'success' => false,
                'message' => 'El pago no se ha completado.',
            ], 422);
        }

        // Cerrar todos los pedidos activos de la mesa con el mismo pago
        $tip = (float) ($validated['tip'] ?? 0);
        foreach ($orders as $index => $order) {
            $order->update([
                'payment_method' => 'card',
                'payment_status' => 'paid',
                'status'         => 'closed',
                'bill_requested' => false,
                // La propina se asigna solo al primer pedido para no duplicarla
                'tip'            => $index === 0 ? $tip : 0,
            ]);
        }

        return response()->json(['success' => true, 'order_id' => $orders->first()->id]);
    }
}
