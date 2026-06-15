<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Table
 *
 * Representa una mesa física en el restaurante.
 * Incluye datos de posición para el plano visual (Editor de Mapa).
 * Los elementos especiales (barra, taburetes) tienen is_service_point = false
 * y no generan QR ni aparecen en la carta pública.
 *
 * @package App\Models
 * @property int      $id
 * @property int      $user_id          Dueño de la mesa
 * @property int|null $zone_id          Zona a la que pertenece (nullable)
 * @property string   $name             Nombre o número (Ej: "Mesa 1", "Terraza 2")
 * @property string   $unique_hash      Código único para el QR
 * @property string   $status           Estado: 'free', 'occupied'
 * @property int      $position_x       Coordenada X en el plano
 * @property int      $position_y       Coordenada Y en el plano
 * @property int      $width            Ancho de la mesa (px)
 * @property int      $height           Alto de la mesa (px)
 * @property int        $floor            Planta en la que se encuentra la mesa (1–5)
 * @property bool       $is_service_point Si genera QR y acepta pedidos
 * @property array|null $vertices         Vértices poligonales (solo barra); null = forma predefinida
 * @property string     $orderStatus      Estado derivado del pedido activo (virtual, no en BD)
 * @author AyrtonAlania
 * @author SebastianBCF
 */
class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'zone_id',
        'name',
        'unique_hash',
        'status',
        'position_x',
        'position_y',
        'width',
        'height',
        'shape',
        'rotation',
        'floor',
        'is_service_point',
        'vertices',
        'waiter_called',
        'waiter_called_at',
    ];

    protected $casts = [
        'floor'            => 'integer',
        'is_service_point' => 'boolean',
        'vertices'         => 'array',
        'waiter_called'    => 'boolean',
        'waiter_called_at' => 'datetime',
    ];

    /**
     * El restaurante al que pertenece la mesa.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * La zona del plano a la que pertenece esta mesa.
     *
     * @return BelongsTo
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Historial de todos los pedidos hechos en esta mesa.
     *
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Obtiene EXCLUSIVAMENTE el pedido actual (el que no está cerrado).
     * Útil para saber qué se está consumiendo ahora mismo.
     *
     * @return HasOne
     */
    public function activeOrder(): HasOne
    {
        return $this->hasOne(Order::class)
            ->where('status', '!=', 'closed')
            ->latestOfMany();
    }

    /**
     * Estado derivado del pedido activo para el mapa visual.
     * Requiere que la relación activeOrder esté eager-loaded para evitar N+1.
     *
     * @return string  'free' | 'occupied' | 'ready' | 'payment_pending'
     */
    public function getOrderStatusAttribute(): string
    {
        $order = $this->activeOrder;

        if (! $order) {
            return 'free';
        }
        if ($order->bill_requested) {
            return 'payment_pending';
        }
        if ($order->notification_ready) {
            return 'ready';
        }

        return 'occupied';
    }

    /**
     * Scope: solo mesas con carta pública y capacidad de recibir pedidos.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeServicePoints(Builder $query): Builder
    {
        return $query->where('is_service_point', true);
    }
}
