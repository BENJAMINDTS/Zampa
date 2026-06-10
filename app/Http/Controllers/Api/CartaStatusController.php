<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\JsonResponse;

/**
 * Class CartaStatusController
 *
 * Devuelve el estado actual de horarios (cocina y negocio) para la carta pública.
 * Consumido por Alpine.store('schedule') cada 30 s para mantener la UI reactiva
 * sin recargar la página.
 *
 * @author Ayrton
 */
class CartaStatusController extends Controller
{
    /**
     * @param  string  $hash  unique_hash de la mesa
     * @return JsonResponse
     */
    public function show(string $hash): JsonResponse
    {
        $table = Table::where('unique_hash', $hash)
            ->where('is_service_point', true)
            ->with(['user.tapaConfig.kitchenSchedules', 'user.tapaConfig.businessSchedules'])
            ->firstOrFail();

        $config = $table->user->tapaConfig;

        $kitchenOpen  = ! $config || $config->isKitchenOpen();
        $businessOpen = ! $config || $config->isBusinessOpen();

        return response()->json([
            'success'                     => true,
            'kitchen_open'                => $kitchenOpen,
            'kitchen_next_opening'        => ($config && ! $kitchenOpen) ? $config->nextOpeningTime()          : null,
            'minutes_until_kitchen_close' => ($config && $kitchenOpen)  ? $config->minutesUntilKitchenClose() : null,
            'kitchen_close_at'            => ($config && $kitchenOpen)  ? $config->kitchenCloseAtDisplay()    : null,
            'business_open'               => $businessOpen,
            'business_next_opening'       => ($config && ! $businessOpen) ? $config->getBusinessNextOpeningTime() : null,
            'ordering_allowed'            => ! $config || $config->isOrderingAllowed(),
        ]);
    }
}
