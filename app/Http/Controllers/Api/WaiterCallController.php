<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Http\JsonResponse;

/**
 * Permite al cliente de una mesa llamar al camarero desde la carta pública.
 *
 * @author SebastianBCF
 */
class WaiterCallController extends Controller
{
    /**
     * Marca el pedido activo de la mesa como waiter_called = true.
     *
     * @param  string  $hash  El unique_hash de la mesa
     * @return JsonResponse
     */
    public function store(string $hash): JsonResponse
    {
        $table = Table::where('unique_hash', $hash)->firstOrFail();

        $order = Order::where('table_id', $table->id)
            ->whereIn('status', ['pending', 'cooking', 'ready', 'served'])
            ->where('payment_status', 'pending')
            ->latest()
            ->first();

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'No hay pedido activo.'], 404);
        }

        $order->update([
            'waiter_called'    => true,
            'waiter_called_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
