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
 * Configuración del negocio: tapas, horarios de apertura (negocio y cocina)
 * y período de cierre de pedidos. Relación 1:1 con User.
 *
 * @package App\Models
 * @property int        $user_id
 * @property bool       $tapas_enabled              Si el sistema de tapas está activo
 * @property bool       $tapas_free                 true = gratuitas, false = de pago
 * @property string     $price_mode                 'fixed' | 'per_product'
 * @property int        $max_tapa_variants          Máximo de variantes distintas por mesa
 * @property float|null $tapa_price                 Precio fijo global (solo si price_mode=fixed y tapas_free=false)
 * @property bool       $extra_tapa_enabled         Si se permite pedir una tapa extra de pago
 * @property float|null $extra_tapa_price           Precio de la tapa extra
 * @property int        $ordering_cutoff_minutes    Minutos antes del cierre en los que no se admiten nuevos pedidos
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
        'price_mode',
        'max_tapa_variants',
        'tapa_price',
        'extra_tapa_enabled',
        'extra_tapa_price',
        'ordering_cutoff_minutes',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'tapas_enabled'           => 'boolean',
        'tapas_free'              => 'boolean',
        'price_mode'              => 'string',
        'max_tapa_variants'       => 'integer',
        'tapa_price'              => 'decimal:2',
        'extra_tapa_enabled'      => 'boolean',
        'extra_tapa_price'        => 'decimal:2',
        'ordering_cutoff_minutes' => 'integer',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tramos horarios de la cocina.
     *
     * @return HasMany
     */
    public function kitchenSchedules(): HasMany
    {
        return $this->hasMany(KitchenSchedule::class)
                    ->where('type', 'kitchen')
                    ->orderBy('opens_at');
    }

    /**
     * Tramos horarios de apertura del negocio.
     *
     * @return HasMany
     */
    public function businessSchedules(): HasMany
    {
        return $this->hasMany(KitchenSchedule::class)
                    ->where('type', 'business')
                    ->orderBy('opens_at');
    }

    /**
     * Alias de kitchenSchedules() para compatibilidad con código y tests anteriores.
     *
     * @return HasMany
     */
    public function schedules(): HasMany
    {
        return $this->kitchenSchedules();
    }

    // ─── Helpers: cocina ─────────────────────────────────────────────────────

    /**
     * Determina si la cocina está abierta en este momento.
     * El horario del negocio tiene prioridad: si el negocio está cerrado, la cocina también.
     * Sin tramos de cocina configurados se considera abierta dentro del horario del negocio.
     * Soporta rangos que cruzan medianoche (ej: 22:00 – 02:00).
     *
     * @return bool
     */
    public function isKitchenOpen(): bool
    {
        // El negocio manda: si está cerrado, la cocina no puede estar abierta
        if (! $this->isBusinessOpen()) {
            return false;
        }

        $schedules = $this->kitchenSchedules;

        if ($schedules->isEmpty()) {
            return true;
        }

        return $this->isNowWithinSlots($schedules);
    }

    /**
     * Devuelve la hora de apertura del próximo tramo de cocina (HH:MM).
     * Null si no hay tramos configurados.
     *
     * @return string|null
     */
    public function nextOpeningTime(): ?string
    {
        return $this->nextSlotOpeningTime($this->kitchenSchedules);
    }

    // ─── Helpers: negocio ────────────────────────────────────────────────────

    /**
     * Determina si el negocio está abierto en este momento.
     * Sin tramos de negocio configurados se considera siempre abierto.
     *
     * @return bool
     */
    public function isBusinessOpen(): bool
    {
        $schedules = $this->businessSchedules;

        if ($schedules->isEmpty()) {
            return true;
        }

        return $this->isNowWithinSlots($schedules);
    }

    /**
     * Devuelve false si estamos en el período de cierre de pedidos
     * (dentro de los últimos ordering_cutoff_minutes del tramo activo).
     * Si ordering_cutoff_minutes = 0 los pedidos se admiten hasta el cierre.
     * Si el negocio está cerrado siempre devuelve false.
     *
     * @return bool
     */
    public function isOrderingAllowed(): bool
    {
        if (! $this->isBusinessOpen()) {
            return false;
        }

        $cutoff = (int) $this->ordering_cutoff_minutes;

        if ($cutoff <= 0) {
            return true;
        }

        $schedules = $this->businessSchedules;
        $now       = Carbon::now()->format('H:i:s');

        foreach ($schedules as $schedule) {
            $opens  = $schedule->opens_at;
            $closes = $schedule->closes_at;

            $inSlot = ($opens <= $closes)
                ? ($now >= $opens && $now <= $closes)
                : ($now >= $opens || $now <= $closes);

            if ($inSlot) {
                // Calculamos minutos hasta el cierre del tramo
                return $this->minutesToTime($closes) > $cutoff;
            }
        }

        return true;
    }

    /**
     * Minutos que quedan hasta el cierre del tramo de negocio activo.
     * Null si el negocio está cerrado o no hay tramos.
     *
     * @return int|null
     */
    public function minutesUntilBusinessClose(): ?int
    {
        if (! $this->isBusinessOpen()) {
            return null;
        }

        $schedules = $this->businessSchedules;

        if ($schedules->isEmpty()) {
            return null;
        }

        $now = Carbon::now()->format('H:i:s');

        foreach ($schedules as $schedule) {
            $opens  = $schedule->opens_at;
            $closes = $schedule->closes_at;

            $inSlot = ($opens <= $closes)
                ? ($now >= $opens && $now <= $closes)
                : ($now >= $opens || $now <= $closes);

            if ($inSlot) {
                return $this->minutesToTime($closes);
            }
        }

        return null;
    }

    /**
     * Devuelve la hora de apertura del próximo tramo de negocio (HH:MM).
     * Null si no hay tramos configurados.
     *
     * @return string|null
     */
    public function getBusinessNextOpeningTime(): ?string
    {
        return $this->nextSlotOpeningTime($this->businessSchedules);
    }

    /**
     * Minutos que quedan hasta el cierre del tramo de cocina activo.
     * Null si la cocina está cerrada o no hay tramos configurados.
     *
     * @return int|null
     */
    public function minutesUntilKitchenClose(): ?int
    {
        if (! $this->isKitchenOpen()) {
            return null;
        }

        $schedules = $this->kitchenSchedules;

        if ($schedules->isEmpty()) {
            return null;
        }

        $now = Carbon::now()->format('H:i:s');

        foreach ($schedules as $schedule) {
            $opens  = $schedule->opens_at;
            $closes = $schedule->closes_at;

            $inSlot = ($opens <= $closes)
                ? ($now >= $opens && $now <= $closes)
                : ($now >= $opens || $now <= $closes);

            if ($inSlot) {
                return $this->minutesToTime($closes);
            }
        }

        return null;
    }

    /**
     * Hora de cierre del tramo de cocina activo en formato "HH:MM".
     * Null si la cocina está cerrada o no hay tramos configurados.
     *
     * @return string|null
     */
    public function kitchenCloseAtDisplay(): ?string
    {
        if (! $this->isKitchenOpen()) {
            return null;
        }

        $schedules = $this->kitchenSchedules;

        if ($schedules->isEmpty()) {
            return null;
        }

        $now = Carbon::now()->format('H:i:s');

        foreach ($schedules as $schedule) {
            $opens  = $schedule->opens_at;
            $closes = $schedule->closes_at;

            $inSlot = ($opens <= $closes)
                ? ($now >= $opens && $now <= $closes)
                : ($now >= $opens || $now <= $closes);

            if ($inSlot) {
                return substr($closes, 0, 5);
            }
        }

        return null;
    }

    // ─── Lógica de tapas ─────────────────────────────────────────────────────

    /**
     * @param  int  $barItemsCount
     * @param  int  $tapaVariantsUsed
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

    /**
     * @param  Product  $product
     * @return float
     */
    public function getPriceForProduct(Product $product): float
    {
        if ($this->tapas_free) {
            return 0.0;
        }

        if ($this->price_mode === 'fixed') {
            return (float) ($this->tapa_price ?? 0);
        }

        return (float) $product->price;
    }

    /**
     * @param  Product  $product  Debe tener la relación 'categories' cargada
     * @return bool
     */
    public function isTapaProduct(Product $product): bool
    {
        $categories = $product->relationLoaded('categories')
            ? $product->categories
            : $product->categories()->get();

        return $categories->where('name', 'Tapas')
                          ->where('user_id', $this->user_id)
                          ->isNotEmpty();
    }

    // ─── Helpers privados ────────────────────────────────────────────────────

    /**
     * @param  \Illuminate\Support\Collection  $schedules
     * @return bool
     */
    private function isNowWithinSlots(\Illuminate\Support\Collection $schedules): bool
    {
        $now = Carbon::now()->format('H:i:s');

        foreach ($schedules as $schedule) {
            $opens  = $schedule->opens_at;
            $closes = $schedule->closes_at;

            if ($opens <= $closes) {
                if ($now >= $opens && $now <= $closes) {
                    return true;
                }
            } else {
                if ($now >= $opens || $now <= $closes) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  \Illuminate\Support\Collection  $schedules
     * @return string|null
     */
    private function nextSlotOpeningTime(\Illuminate\Support\Collection $schedules): ?string
    {
        if ($schedules->isEmpty()) {
            return null;
        }

        $now  = Carbon::now()->format('H:i:s');
        $next = $schedules->first(fn ($s) => $s->opens_at > $now);

        $schedule = $next ?? $schedules->first();

        return substr($schedule->opens_at, 0, 5);
    }

    /**
     * Minutos que quedan desde ahora hasta la hora dada (HH:MM:SS).
     * Si la hora ya pasó hoy, calcula como si fuera mañana.
     *
     * @param  string  $time  Formato HH:MM:SS
     * @return int
     */
    private function minutesToTime(string $time): int
    {
        $now    = Carbon::now();
        $target = Carbon::today()->setTimeFromTimeString($time);

        if ($target <= $now) {
            $target->addDay();
        }

        return (int) $now->diffInMinutes($target);
    }
}
