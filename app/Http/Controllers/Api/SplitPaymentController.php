<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemPayment;
use App\Models\Table;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Gestiona el flujo de cobro partido desde la carta pública.
 * Soporta dos modos: por ítems (items) y reparto equitativo (equitative).
 * Cada comensal genera un PaymentIntent independiente en Stripe.
 *
 * @author BenjaminDTS
 * @author AyrtonAlania
 */
class SplitPaymentController extends Controller
{
    public function __construct(private readonly StripeService $stripe) {}

    /**
     * Devuelve los ítems del pedido activo indicando cuáles están ya reclamados.
     *
     * @param  string  $hash
     * @return JsonResponse
     */
    public function claimedItems(string $hash): JsonResponse
    {
        $table = Table::with('user')->where('unique_hash', $hash)->firstOrFail();

        if ($guard = $this->guardSplitEnabled($table)) {
            return $guard;
        }

        $order = $this->activeOrder($table->id);

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'No hay pedido activo.'], 404);
        }

        $items = $order->items()->with('splitPayments')->get()
            ->map(fn (OrderItem $item) => [
                'id'      => $item->id,
                'name'    => $item->product?->name ?? 'Producto',
                'price'   => (float) $item->price,
                'total'   => round((float) $item->price * $item->quantity, 2),
                'claimed' => $item->isClaimed(),
            ]);

        return response()->json(['success' => true, 'items' => $items]);
    }

    /**
     * Crea un PaymentIntent de Stripe para los ítems seleccionados por el comensal.
     * Verifica atómicamente que ninguno de los ítems esté ya reclamado.
     *
     * @param  Request  $request
     * @param  string   $hash
     * @return JsonResponse
     */
    public function payItems(Request $request, string $hash): JsonResponse
    {
        $validated = $request->validate([
            'item_ids'   => 'required|array|min:1',
            'item_ids.*' => 'required|integer',
        ]);

        $table = Table::with('user')->where('unique_hash', $hash)->firstOrFail();

        if ($guard = $this->guardSplitEnabled($table)) {
            return $guard;
        }

        $order = $this->activeOrder($table->id);

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'No hay pedido activo.'], 404);
        }

        return DB::transaction(function () use ($order, $validated, $table) {
            $items = $order->items()
                ->whereIn('id', $validated['item_ids'])
                ->lockForUpdate()
                ->get();

            if ($items->count() !== count($validated['item_ids'])) {
                return response()->json(['success' => false, 'message' => 'Uno o más ítems no pertenecen a este pedido.'], 422);
            }

            $alreadyClaimed = $items->filter(fn (OrderItem $i) => $i->isClaimed());
            if ($alreadyClaimed->isNotEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Uno o más ítems ya han sido reclamados por otro comensal.',
                    'claimed_ids' => $alreadyClaimed->pluck('id'),
                ], 409);
            }

            $amount = $items->sum(fn (OrderItem $i) => round($i->price * $i->quantity, 2));

            $result = $this->stripe->createSplitPaymentIntent(
                amount:      (int) round($amount * 100),
                mode:        'items',
                partNumber:  1,
                partsTotal:  1,
                metadata:    ['order_id' => $order->id, 'table_name' => $table->name],
            );

            foreach ($items as $item) {
                OrderItemPayment::create([
                    'order_id'                  => $order->id,
                    'order_item_id'             => $item->id,
                    'amount'                    => round($item->price * $item->quantity, 2),
                    'stripe_payment_intent_id'  => $result['id'],
                    'status'                    => 'pending',
                    'mode'                      => 'items',
                    'parts_total'               => null,
                    'part_number'               => null,
                ]);
            }

            return response()->json([
                'success'       => true,
                'client_secret' => $result['client_secret'],
                'amount'        => $amount,
            ]);
        });
    }

    /**
     * Crea un PaymentIntent de Stripe para la parte equitativa de un comensal.
     *
     * @param  Request  $request
     * @param  string   $hash
     * @return JsonResponse
     */
    public function payEquitative(Request $request, string $hash): JsonResponse
    {
        $validated = $request->validate([
            'people'      => 'required|integer|min:2|max:50',
            'part_number' => 'required|integer|min:1',
        ]);

        $table = Table::with('user')->where('unique_hash', $hash)->firstOrFail();

        if ($guard = $this->guardSplitEnabled($table)) {
            return $guard;
        }

        $order = $this->activeOrder($table->id);

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'No hay pedido activo.'], 404);
        }

        $people     = (int) $validated['people'];
        $partNumber = (int) $validated['part_number'];

        $maxParts = $table->user->split_payment_max_parts;
        if ($maxParts !== null && $people > $maxParts) {
            return response()->json([
                'success' => false,
                'message' => "No se puede dividir en más de {$maxParts} partes.",
            ], 422);
        }

        if ($partNumber > $people) {
            return response()->json(['success' => false, 'message' => 'El número de parte excede el total de personas.'], 422);
        }

        $part = round((float) $order->total / $people, 2);

        $result = $this->stripe->createSplitPaymentIntent(
            amount:     (int) round($part * 100),
            mode:       'equitative',
            partNumber: $partNumber,
            partsTotal: $people,
            metadata:   ['order_id' => $order->id, 'table_name' => $table->name],
        );

        OrderItemPayment::create([
            'order_id'                 => $order->id,
            'order_item_id'            => null,
            'amount'                   => $part,
            'stripe_payment_intent_id' => $result['id'],
            'status'                   => 'pending',
            'mode'                     => 'equitative',
            'parts_total'              => $people,
            'part_number'              => $partNumber,
        ]);

        return response()->json([
            'success'       => true,
            'client_secret' => $result['client_secret'],
            'amount'        => $part,
            'part_number'   => $partNumber,
            'parts_total'   => $people,
        ]);
    }

    /**
     * Verifica el pago con Stripe y marca el OrderItemPayment como pagado.
     * Si el pedido queda completamente cubierto, lo cierra automáticamente.
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

        $table = Table::with('user')->where('unique_hash', $hash)->firstOrFail();

        if ($guard = $this->guardSplitEnabled($table)) {
            return $guard;
        }

        $order = $this->activeOrder($table->id);

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'No hay pedido activo.'], 404);
        }

        $splitPayments = OrderItemPayment::where('stripe_payment_intent_id', $validated['payment_intent_id'])
            ->where('order_id', $order->id)
            ->get();

        if ($splitPayments->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Pago parcial no encontrado.'], 404);
        }

        $intent = $this->stripe->confirmPayment($validated['payment_intent_id']);

        if ($intent['status'] !== 'succeeded') {
            OrderItemPayment::where('stripe_payment_intent_id', $validated['payment_intent_id'])
                ->where('order_id', $order->id)
                ->update(['status' => 'failed']);
            return response()->json(['success' => false, 'message' => 'El pago no se ha completado.'], 422);
        }

        OrderItemPayment::where('stripe_payment_intent_id', $validated['payment_intent_id'])
            ->where('order_id', $order->id)
            ->update(['status' => 'paid']);

        $fullyPaid = $this->checkOrderFullyPaid($order);

        return response()->json([
            'success'    => true,
            'fully_paid' => $fullyPaid,
        ]);
    }

    /**
     * Devuelve 403 si el cobro partido no está activado para este restaurante.
     *
     * @param  Table  $table
     * @return JsonResponse|null
     */
    private function guardSplitEnabled(Table $table): ?JsonResponse
    {
        if (! $table->user->isSplitPaymentEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'El cobro partido no está activado para este restaurante.',
            ], 403);
        }

        return null;
    }

    /**
     * Cierra el pedido si el importe pagado via split cubre el total.
     *
     * @param  Order  $order
     * @return bool   true si el pedido fue cerrado
     */
    private function checkOrderFullyPaid(Order $order): bool
    {
        $order->refresh();

        if (! $order->isFullyPaidViaSplit()) {
            return false;
        }

        $order->update([
            'payment_method' => 'card',
            'payment_status' => 'paid',
            'status'         => 'closed',
            'bill_requested' => false,
        ]);

        return true;
    }

    /**
     * Busca el pedido activo de una mesa.
     *
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
