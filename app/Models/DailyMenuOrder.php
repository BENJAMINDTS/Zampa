<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class DailyMenuOrder
 *
 * Vincula un Order normal con un DailyMenu, registrando las selecciones
 * del cliente, el estado de progreso de las rondas y los ajustes de timing.
 *
 * @property int        $order_id                ID del pedido padre (Order)
 * @property int        $daily_menu_id           ID del menú del día seleccionado
 * @property int        $rounds_total            Total de rondas configuradas
 * @property int        $current_round           Ronda en curso
 * @property array|null $client_timing_overrides Ajustes de timing elegidos por el cliente
 * @property string     $status                  pending|in_progress|completed|cancelled
 *
 * @author Ayrtonalania
 */
class DailyMenuOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'daily_menu_id',
        'rounds_total',
        'current_round',
        'client_timing_overrides',
        'status',
    ];

    protected $casts = [
        'client_timing_overrides' => 'array',
        'current_round'           => 'integer',
    ];

    /**
     * El pedido padre al que pertenece este pedido de menú.
     *
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * El menú del día seleccionado por el cliente.
     *
     * @return BelongsTo
     */
    public function dailyMenu(): BelongsTo
    {
        return $this->belongsTo(DailyMenu::class);
    }

    /**
     * Las selecciones de productos por sección del menú.
     *
     * @return HasMany
     */
    public function selections(): HasMany
    {
        return $this->hasMany(DailyMenuOrderSelection::class);
    }

    /**
     * Cancela el pedido de menú del día actualizando su estado en base de datos.
     *
     * @return void
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }
}
