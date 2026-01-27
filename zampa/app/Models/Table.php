<?php

namespace App\Models;

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
 *
 * @package App\Models
 * @property int $id
 * @property int $user_id       Dueño de la mesa
 * @property string $name       Nombre o número (Ej: "Mesa 1", "Terraza 2")
 * @property string $unique_hash Código único para el QR
 * @property string $status     Estado: 'free', 'occupied'
 * @property int $position_x    Coordenada X en el plano
 * @property int $position_y    Coordenada Y en el plano
 * @property int $width         Ancho de la mesa (px)
 * @property int $height        Alto de la mesa (px)
 */
class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'unique_hash',
        'status',
        'position_x',
        'position_y',
        'width',
        'height',
        'shape',
        'rotation'
    ];

    /**
     * El restaurante al que pertenece la mesa.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Historial de todos los pedidos hechos en esta mesa.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Obtiene EXCLUSIVAMENTE el pedido actual (el que no está cerrado).
     * Útil para saber qué se está consumiendo ahora mismo.
     * * @return HasOne|null
     */
    public function activeOrder()
    {
        return $this->hasOne(Order::class)
            ->where('status', '!=', 'closed')
            ->latestOfMany();
    }
}
