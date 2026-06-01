<?php

namespace App\Models;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Plan
 *
 * Define los niveles de suscripción para los restaurantes (SaaS).
 * Tres niveles: Básico (20 mesas · 10 staff · 1 planta),
 * Profesional (50 · 25 · 3) y Premium (null = ilimitado en todo).
 *
 * @property string   $name           Nombre del plan
 * @property float    $price          Coste mensual (legado: usar price_monthly)
 * @property float    $price_monthly  Coste mensual
 * @property float    $price_yearly   Coste anual
 * @property int|null $max_tables     Límite de mesas (null = ilimitado)
 * @property int|null $max_staff      Límite de personal (null = ilimitado)
 * @property int|null $max_floors     Límite de plantas (null = ilimitado)
 *
 * @author BenjaminDTS
 */
class Plan extends Model
{
    use HasFactory;

    public const BASIC        = 'Básico';
    public const PROFESSIONAL = 'Profesional';
    public const PREMIUM      = 'Premium';

    protected $fillable = [
        'name',
        'price',
        'price_monthly',
        'price_yearly',
        'max_tables',
        'max_staff',
        'max_floors',
    ];

    protected $casts = [
        'price'         => 'decimal:2',
        'price_monthly' => 'decimal:2',
        'price_yearly'  => 'decimal:2',
        'max_tables'    => 'integer',
        'max_staff'     => 'integer',
        'max_floors'    => 'integer',
    ];

    /**
     * Usuarios (Restaurantes) que tienen contratado este plan.
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Indica si hay negocios asignados a este plan.
     *
     * @return bool
     */
    public function hasActiveBusinesses(): bool
    {
        return $this->users()->exists();
    }

    /**
     * @return bool
     */
    public function hasUnlimitedTables(): bool
    {
        return $this->max_tables === null;
    }

    /**
     * @return bool
     */
    public function hasUnlimitedStaff(): bool
    {
        return $this->max_staff === null;
    }

    /**
     * @return bool
     */
    public function hasUnlimitedFloors(): bool
    {
        return $this->max_floors === null;
    }

    /**
     * Devuelve el límite numérico para el recurso indicado, o null si es ilimitado.
     *
     * @param  string  $resource  'tables' | 'staff' | 'floors'
     * @return int|null
     *
     * @throws InvalidArgumentException si el recurso no es reconocido
     */
    public function limitFor(string $resource): ?int
    {
        return match ($resource) {
            'tables' => $this->max_tables,
            'staff'  => $this->max_staff,
            'floors' => $this->max_floors,
            default  => throw new InvalidArgumentException(
                "Resource '{$resource}' no reconocido. Usa: tables, staff, floors."
            ),
        };
    }

    /**
     * Comprueba si el plan ha alcanzado el límite para un recurso dado.
     * Devuelve siempre false cuando el límite es null (plan Premium, ilimitado).
     *
     * Convención: $current es el conteo existente ANTES de crear el nuevo recurso.
     * Se bloquea cuando $current >= $limit (ya se ha llenado el cupo).
     *
     * @param  string  $resource  'tables' | 'staff' | 'floors'
     * @param  int     $current   Cantidad actual del recurso
     * @return bool
     */
    public function isLimitReached(string $resource, int $current): bool
    {
        $limit = $this->limitFor($resource);

        if ($limit === null) {
            return false;
        }

        return $current >= $limit;
    }
}
