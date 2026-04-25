<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class TapaConfig
 *
 * Configuración del sistema de tapas de un restaurante (relación 1:1 con User).
 * Permite al gerente activar tapas, definir si son gratuitas o de pago,
 * el precio unitario y el número máximo de variantes distintas por pedido.
 *
 * @package App\Models
 * @property int     $user_id
 * @property bool    $tapas_enabled       Si el sistema de tapas está activo
 * @property bool    $tapas_free          true = gratuitas, false = de pago
 * @property int     $max_tapa_variants   Máximo de variantes distintas por mesa
 * @property float|null $tapa_price       Precio por tapa (solo si tapas_free = false)
 *
 * @author BenjaminDTS
 */
class TapaConfig extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'tapas_enabled',
        'tapas_free',
        'max_tapa_variants',
        'tapa_price',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'tapas_enabled'     => 'boolean',
        'tapas_free'        => 'boolean',
        'max_tapa_variants' => 'integer',
        'tapa_price'        => 'decimal:2',
    ];

    /**
     * El restaurante (usuario) al que pertenece esta configuración.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
