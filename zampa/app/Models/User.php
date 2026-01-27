<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class User
 *
 * Representa al usuario del sistema (Dueño del restaurante, Camarero o Admin).
 * En este modelo SaaS, el Usuario es el "centro" de los datos del restaurante.
 *
 * @package App\Models
 * @property int $id
 * @property int|null $plan_id  ID del plan de suscripción (si aplica)
 * @property string $name       Nombre completo del usuario
 * @property string $email      Correo electrónico (login)
 * @property string $role       Rol: 'admin', 'waiter', 'kitchen'
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'plan_id',
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * Los atributos que deben ocultarse en las respuestas JSON (seguridad).
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversiones automáticas de tipos de datos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /* -------------------------------------------------------------------------- */
    /* RELACIONES                                  */
    /* -------------------------------------------------------------------------- */

    /**
     * Obtiene el Plan de suscripción del usuario.
     *
     * @return BelongsTo
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Obtiene las Mesas registradas por este restaurante.
     *
     * @return HasMany
     */
    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }

    /**
     * Obtiene las Categorías del menú (Ej: Bebidas, Postres).
     *
     * @return HasMany
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Obtiene todos los Productos de la carta.
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Obtiene el listado maestro de ingredientes disponibles.
     *
     * @return HasMany
     */
    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }
}
