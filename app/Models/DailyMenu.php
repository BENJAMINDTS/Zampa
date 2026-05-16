<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class DailyMenu
 *
 * Representa el menú del día configurado por el gerente del restaurante.
 * Puede definirse para un día de la semana (rotación semanal) o para una
 * fecha específica (que tiene prioridad sobre el día de la semana).
 *
 * @property int              $user_id        ID del usuario propietario
 * @property string           $title          Título del menú
 * @property string|null      $description    Descripción del menú
 * @property float            $price          Precio total del menú
 * @property int|null         $max_per_day    Límite de menús disponibles por día
 * @property bool             $is_active      Si el menú está activo
 * @property string|null      $day_of_week    Día de la semana para rotación semanal
 * @property \Carbon\Carbon|null $specific_date Fecha específica (tiene prioridad)
 *
 * @author Ayrtonalania
 */
class DailyMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'max_per_day',
        'is_active',
        'day_of_week',
        'specific_date',
    ];

    protected $casts = [
        'price'         => 'decimal:2',
        'is_active'     => 'boolean',
        'specific_date' => 'date',
    ];

    /**
     * El usuario propietario del menú.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Las secciones del menú ordenadas por display_order.
     *
     * @return HasMany
     */
    public function sections(): HasMany
    {
        return $this->hasMany(DailyMenuSection::class)->orderBy('display_order');
    }

    /**
     * Las reglas de timing del menú ordenadas por round_number.
     *
     * @return HasMany
     */
    public function timingRules(): HasMany
    {
        return $this->hasMany(DailyMenuTimingRule::class)->orderBy('round_number');
    }

    /**
     * Los pedidos de menú del día asociados.
     *
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(DailyMenuOrder::class);
    }

    /**
     * Scope: filtra solo menús activos.
     *
     * @param  Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Devuelve el menú del día vigente para el usuario dado.
     * Prioridad: specific_date > day_of_week.
     *
     * @param  int       $userId
     * @return self|null
     */
    public static function forToday(int $userId): ?self
    {
        $byDate = static::where('user_id', $userId)
            ->where('specific_date', today())
            ->where('is_active', true)
            ->first();

        if ($byDate) {
            return $byDate;
        }

        return static::where('user_id', $userId)
            ->where('day_of_week', strtolower(now()->englishDayOfWeek))
            ->where('is_active', true)
            ->first();
    }
}
