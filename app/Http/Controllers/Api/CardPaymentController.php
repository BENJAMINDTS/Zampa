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
     * Crea un PaymentIntent de Stripe para el pedido activo de la mesa.
     * Devuelve el client_secret que necesita Stripe.js en el cliente.
     *
     * @param  string  $hash  El unique_hash de la mesa
     * @return JsonResponse
     */
    public function intent(string $hash): JsonResponse
    {
        $table = Table::where('unique_hash', $hash)->firstOrFail();

        $order = Order::where('table_id', $table->id)
            ->whereIn('status', ['pending', 'cooking', 'ready'])
            ->latest()
            ->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'No hay un pedido activo en esta mesa.',
            ], 404);
        }

        $result = $this->stripe->createPaymentIntent(
            amount:   (int) round($order->total * 100),
            currency: 'eur',
            metadata: ['order_id' => $order->id, 'table_name' => $table->name],
        );

        return response()->json([
            'success'       => true,
            'client_secret' => $result['client_secret'],
            'total'         => $order->total,
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
        ]);

        $table = Table::where('unique_hash', $hash)->firstOrFail();

        $order = Order::where('table_id', $table->id)
            ->whereIn('status', ['pending', 'cooking', 'ready'])
            ->latest()
            ->first();

        if (! $order) {
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

        $order->update([
            'payment_method' => 'card',
            'payment_status' => 'paid',
            'status'         => 'closed',
            'bill_requested' => false,
        ]);

        return response()->json(['success' => true]);
    }
}
