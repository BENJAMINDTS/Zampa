<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class KitchenSchedule
 *
 * Representa un tramo horario de apertura de cocina asociado a una TapaConfig.
 * Un restaurante puede tener varios tramos (ej: mediodía y noche).
 *
 * @package App\Models
 * @property string $opens_at   Hora de apertura (HH:MM:SS)
 * @property string $closes_at  Hora de cierre  (HH:MM:SS)
 *
 * @author BenjaminDTS
 */
class KitchenSchedule extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'tapa_config_id',
        'type',
        'opens_at',
        'closes_at',
    ];

    /**
     * @return BelongsTo
     */
    public function tapaConfig(): BelongsTo
    {
        return $this->belongsTo(TapaConfig::class);
    }
}
