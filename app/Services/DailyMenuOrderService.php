<?php

namespace App\Services;

use App\Models\DailyMenuOrder;
use App\Models\DailyMenuTimingRule;
use Illuminate\Support\Collection;

/**
 * Gestiona el despacho de rondas del menú del día a cocina/barra.
 *
 * @author Ayrtonalania
 */
class DailyMenuOrderService
{
    /**
     * Despacha los jobs de cocina/barra para cada ronda del menú del día,
     * respetando los tiempos de preparación y los ajustes del cliente.
     *
     * @param  DailyMenuOrder  $dailyMenuOrder
     * @param  Collection      $timingRules
     * @param  array           $timingOverrides
     * @return void
     */
    public static function dispatchRounds(
        DailyMenuOrder $dailyMenuOrder,
        Collection $timingRules,
        array $timingOverrides
    ): void {
        // TODO: implementado en Bloque 14.4
    }
}
