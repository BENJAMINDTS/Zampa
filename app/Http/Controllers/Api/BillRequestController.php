<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestiona la solicitud de cuenta desde la carta digital pública.
 *
 * @author SebastianBCF
 */
class BillRequestController extends Controller
{
    /**
     * El cliente solicita la cuenta desde la carta pública indicando
     * su método de pago preferido (cash o card).
     *
     * @param  Request  $request
     * @param  string   $hash  El unique_hash de la mesa
     * @return JsonResponse
     */
    public function store(Request $request, string $hash): JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,card',
            'tip'            => 'nullable|numeric|min:0|max:500',
        ]);

        $table = Table::where('unique_hash', $hash)->firstOrFail();

        $orders = Order::activeForTable($table->id)->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay un pedido activo en esta mesa.',
            ], 404);
        }

        // Marcar todos los pedidos activos: el cliente puede tener à la carte + menú del día
        $orders->each(fn ($o) => $o->update([
            'bill_requested'           => true,
            'requested_payment_method' => $validated['payment_method'],
        ]));

        // La propina se asigna al pedido más reciente para no duplicarla
        $orders->sortByDesc('created_at')->first()->update([
            'tip' => (float) ($validated['tip'] ?? 0),
        ]);

        return response()->json(['success' => true]);
    }
}
