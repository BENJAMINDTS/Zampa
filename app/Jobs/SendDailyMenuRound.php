<?php

namespace App\Jobs;

use App\Models\DailyMenuOrder;
use App\Models\OrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Despacha los ítems de una ronda del menú del día a cocina o barra.
 *
 * Se lanza una instancia de este Job por cada ronda configurada,
 * con un delay calculado en DailyMenuOrderService::dispatchRounds().
 *
 * @author Ayrtonalania
 */
class SendDailyMenuRound implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 30;
    public bool $deleteWhenMissingModels = true;

    /**
     * @param  DailyMenuOrder  $dailyMenuOrder  El pedido de menú del día
     * @param  int             $round           Número de ronda a despachar
     * @return void
     */
    public function __construct(
        public readonly DailyMenuOrder $dailyMenuOrder,
        public readonly int $round
    ) {}

    /**
     * Crea los order_items de la ronda y actualiza el estado del pedido.
     *
     * @return void
     */
    public function handle(): void
    {
        // PASO 1 — Recargar para detectar cancelaciones ocurridas mientras el job esperaba
        $order = $this->dailyMenuOrder->fresh();

        if ($order === null || $order->status === 'cancelled') {
            return;
        }

        // PASO 2 — Ronda 0: sin timing rules, enviar todas las selecciones inmediatamente
        if ($this->round === 0) {
            $selections = $order->selections()
                ->with(['section', 'product.categories'])
                ->get();

            DB::transaction(function () use ($order, $selections) {
                foreach ($selections as $selection) {
                    OrderItem::create([
                        'order_id'      => $order->order_id,
                        'product_id'    => $selection->product_id,
                        'quantity'      => $selection->quantity,
                        'price'         => $selection->section->is_free ? 0.00 : (float) $selection->product->price,
                        'status'        => 'queued',
                        'destination'   => $selection->product->categories->first()?->destination ?? 'kitchen',
                        'is_daily_menu' => true,
                    ]);
                }
            });

            $order->update(['current_round' => 0, 'status' => 'completed']);
            return;
        }

        // PASO 3 — Obtener la timing rule de esta ronda
        $timingRule = $order->dailyMenu
            ->timingRules()
            ->where('round_number', $this->round)
            ->first();

        if ($timingRule === null) {
            return;
        }

        // PASO 4 — Obtener las selecciones que pertenecen a esta ronda
        $selections = $order->selections()
            ->whereHas('section', fn ($q) => $q->whereIn('type', $timingRule->section_types))
            ->with(['section', 'product.categories'])
            ->get();

        // PASO 5 — Crear los order_items dentro de una transacción
        DB::transaction(function () use ($order, $selections) {
            foreach ($selections as $selection) {
                $price = $selection->section->is_free
                    ? 0.00
                    : (float) $selection->product->price;

                OrderItem::create([
                    'order_id'      => $order->order_id,
                    'product_id'    => $selection->product_id,
                    'quantity'      => $selection->quantity,
                    'price'         => $price,
                    'status'        => 'queued',
                    'destination'   => $selection->product->categories->first()?->destination ?? 'kitchen',
                    'is_daily_menu' => true,
                ]);
            }
        });

        // PASO 6 — Actualizar la ronda actual
        $order->update(['current_round' => $this->round]);

        // PASO 7 — Si es la última ronda, marcar como completado
        if ($this->round >= $order->rounds_total) {
            $order->update(['status' => 'completed']);
        }
    }

    /**
     * Registra el error cuando el job falla. No reintenta ($tries = 1).
     *
     * @param  Throwable  $e
     * @return void
     */
    public function failed(Throwable $e): void
    {
        Log::error('SendDailyMenuRound failed', [
            'daily_menu_order_id' => $this->dailyMenuOrder->id,
            'round'               => $this->round,
            'error'               => $e->getMessage(),
        ]);
    }
}
