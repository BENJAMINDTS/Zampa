<?php

namespace App\Services;

use App\Jobs\SendDailyMenuRound;
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
        // Sin timing rules → enviar toda la orden inmediatamente como ronda 0
        if ($timingRules->isEmpty()) {
            SendDailyMenuRound::dispatchSync($dailyMenuOrder, 0);
            return;
        }

        foreach ($timingRules as $rule) {
            $override = collect($timingOverrides)->firstWhere('round', $rule->round_number);

            $clientDelay = $override['delay_minutes'] ?? $rule->default_delay_minutes;

            // dispatch_at = now + client_delay - estimated_prep  (mínimo 0 segundos)
            $delaySeconds = max(0, ($clientDelay - $rule->estimated_prep_minutes) * 60);

            if ($delaySeconds === 0) {
                // Ronda inmediata: síncrona para que llegue al panel sin necesitar queue worker
                SendDailyMenuRound::dispatchSync($dailyMenuOrder, $rule->round_number);
            } else {
                SendDailyMenuRound::dispatch($dailyMenuOrder, $rule->round_number)
                    ->delay(now()->addSeconds($delaySeconds));
            }
        }
    }
}
