<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Zone
 *
 * Representa una zona del local (terraza, comedor interior, barra).
 * Visible como región coloreada en el plano del restaurante.
 *
 * @package App\Models
 * @property int    $id
 * @property int    $user_id     Propietario (restaurante)
 * @property string $name        Nombre de la zona
 * @property string $color       Color hex para visualización en el plano
 * @property int    $position_x  Coordenada X en el plano
 * @property int    $position_y  Coordenada Y en el plano
 * @property int    $width       Ancho en px
 * @property int    $height      Alto en px
 * @property int        $rotation    Rotación en grados
 * @property array|null $vertices    Vértices del polígono personalizado; null = rectángulo
 * @author AyrtonAlania
 * @author SebastianBCF
 */
class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'color',
        'position_x',
        'position_y',
        'width',
        'height',
        'rotation',
        'floor',
        'vertices',
    ];

    protected $casts = [
        'vertices' => 'array',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mesas asignadas a esta zona.
     * Al eliminar la zona, las mesas pasan a zone_id = null (nullOnDelete en migración).
     *
     * @return HasMany
     */
    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }
}
