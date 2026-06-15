<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
     * Marca la mesa como waiter_called = true.
     * Funciona con o sin pedido activo.
     *
     * @param  string  $hash  El unique_hash de la mesa
     * @return JsonResponse
     */
    public function store(string $hash): JsonResponse
    {
        $table = Table::where('unique_hash', $hash)->firstOrFail();

        $table->update([
            'waiter_called'    => true,
            'waiter_called_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
