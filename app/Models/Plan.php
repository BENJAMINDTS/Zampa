<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Plan
 *
 * Define los niveles de suscripción para los restaurantes (SaaS).
 * Ej: "Plan Básico" (5 mesas), "Plan Gold" (Ilimitado).
 *
 * @property string $name       Nombre del plan
 * @property float $price     Coste mensual
 * @property int $max_tables    Límite de mesas permitidas
 *
 * @author BenjaminDTS
 */
class Plan extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'max_tables'];

    protected $casts = [
        'price'      => 'decimal:2',
        'max_tables' => 'integer',
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
}
