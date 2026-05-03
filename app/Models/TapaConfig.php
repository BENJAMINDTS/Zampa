<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Class TapaConfig
 *
 * Configuración del sistema de tapas de un restaurante (relación 1:1 con User).
 * Permite al gerente activar tapas, definir si son gratuitas o de pago,
 * el precio unitario, el número máximo de variantes distintas por pedido,
 * la tapa extra de pago y los tramos horarios de apertura de cocina.
 *
 * @package App\Models
 * @property int        $user_id
 * @property bool       $tapas_enabled       Si el sistema de tapas está activo
 * @property bool       $tapas_free          true = gratuitas, false = de pago
 * @property int        $max_tapa_variants   Máximo de variantes distintas por mesa
 * @property float|null $tapa_price          Precio por tapa (solo si tapas_free = false)
 * @property bool       $extra_tapa_enabled  Si se permite pedir una tapa extra de pago
 * @property float|null $extra_tapa_price    Precio de la tapa extra (obligatorio si extra_tapa_enabled)
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
        'extra_tapa_enabled',
        'extra_tapa_price',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'tapas_enabled'      => 'boolean',
        'tapas_free'         => 'boolean',
        'max_tapa_variants'  => 'integer',
        'tapa_price'         => 'decimal:2',
        'extra_tapa_enabled' => 'boolean',
        'extra_tapa_price'   => 'decimal:2',
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

    /**
     * Tramos horarios de apertura de cocina configurados por el gerente.
     *
     * @return HasMany
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(KitchenSchedule::class)->orderBy('opens_at');
    }

    /**
     * Determina si la cocina está abierta en este momento.
     * Sin tramos configurados se considera siempre abierta.
     * Soporta rangos que cruzan medianoche (ej: 22:00 – 02:00).
     *
     * @return bool
     */
    public function isKitchenOpen(): bool
    {
        $schedules = $this->schedules;

        if ($schedules->isEmpty()) {
            return true;
        }

        $now = Carbon::now()->format('H:i:s');

        foreach ($schedules as $schedule) {
            $opens  = $schedule->opens_at;
            $closes = $schedule->closes_at;

            if ($opens <= $closes) {
                if ($now >= $opens && $now <= $closes) {
                    return true;
                }
            } else {
                // Tramo que cruza medianoche (ej: 22:00 – 02:00)
                if ($now >= $opens || $now <= $closes) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Devuelve la hora de apertura del próximo tramo (HH:MM) para mostrarlo
     * en el aviso de cocina cerrada. Null si no hay tramos configurados.
     *
     * @return string|null
     */
    public function nextOpeningTime(): ?string
    {
        $schedules = $this->schedules;

        if ($schedules->isEmpty()) {
            return null;
        }

        $now  = Carbon::now()->format('H:i:s');
        $next = $schedules->first(fn ($s) => $s->opens_at > $now);

        // Si no hay tramo posterior hoy, el primero de mañana
        $schedule = $next ?? $schedules->first();

        return substr($schedule->opens_at, 0, 5);
    }

    /**
     * Determina si se debe mostrar el modal de sugerencia de tapa al cliente.
     *
     * @param  int  $barItemsCount     Bebidas de barra en órdenes activas de la mesa
     * @param  int  $tapaVariantsUsed  Variantes distintas de tapas ya pedidas en la mesa
     * @return bool
     */
    public function shouldSuggestTapa(int $barItemsCount, int $tapaVariantsUsed): bool
    {
        if (! $this->tapas_enabled) {
            return false;
        }

        if (! $this->isKitchenOpen()) {
            return false;
        }

        if ($barItemsCount <= 0) {
            return false;
        }

        return $tapaVariantsUsed < $this->max_tapa_variants;
    }
}
