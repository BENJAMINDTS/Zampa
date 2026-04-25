<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Http\JsonResponse;

/**
 * Gestiona la solicitud de cuenta desde la carta digital pública.
 *
 * @author SebastianBCF
 */
class BillRequestController extends Controller
{
    /**
     * El cliente solicita la cuenta desde la carta pública.
     * Localiza el pedido activo de la mesa y activa el flag bill_requested.
     *
     * @param  string  $hash  El unique_hash de la mesa
     * @return JsonResponse
     */
    public function store(string $hash): JsonResponse
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

        $order->update(['bill_requested' => true]);

        return response()->json(['success' => true]);
    }
}
