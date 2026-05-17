<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class DailyMenuTimingRule
 *
 * Define los tiempos de entrega entre rondas del menú del día.
 * Cada regla agrupa secciones en una ronda y establece el delay
 * por defecto y el tiempo estimado de preparación en cocina/barra.
 *
 * @property int   $daily_menu_id           ID del menú
 * @property int   $round_number            Número de ronda (1, 2, ...)
 * @property int   $default_delay_minutes   Espera por defecto antes de enviar esta ronda
 * @property int   $estimated_prep_minutes  Tiempo estimado de preparación en cocina/barra
 * @property array $section_types           Tipos de sección incluidos en esta ronda
 *
 * @author Ayrtonalania
 */
class DailyMenuTimingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_menu_id',
        'round_number',
        'default_delay_minutes',
        'estimated_prep_minutes',
        'section_types',
    ];

    protected $casts = [
        'section_types' => 'array',
    ];

    /**
     * El menú del día al que pertenece esta regla de timing.
     *
     * @return BelongsTo
     */
    public function dailyMenu(): BelongsTo
    {
        return $this->belongsTo(DailyMenu::class);
    }
}
