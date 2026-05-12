<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class User
 *
 * Representa al usuario del sistema (Dueño del restaurante, Camarero o Admin).
 * En este modelo SaaS, el Usuario es el "centro" de los datos del restaurante.
 *
 * @package App\Models
 * @property int $id
 * @property int|null $plan_id        ID del plan de suscripción (si aplica)
 * @property int|null $admin_id       ID del gerente propietario (null si es admin)
 * @property bool     $active         Si el negocio está activo en la plataforma
 * @property string $name        Nombre completo del usuario
 * @property string $email       Correo electrónico (login)
 * @property string $role          Rol: 'admin', 'waiter', 'kitchen', 'superadmin'
 * @property string|null $business_name  Nombre del negocio (solo admins/gerentes)
 * @property string|null $address        Dirección del negocio
 * @property float|null  $lat            Latitud del negocio
 * @property float|null  $lng            Longitud del negocio
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @author BenjaminDTS
 * @author SebastianBCF
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'plan_id',
        'active',
        'name',
        'email',
        'password',
        'role',
        'admin_id',
        'business_name',
        'address',
        'lat',
        'lng',
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
            'active' => 'boolean',
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

    /**
     * Obtiene la configuración de tapas del restaurante.
     *
     * @return HasOne
     */
    public function tapaConfig(): HasOne
    {
        return $this->hasOne(TapaConfig::class);
    }

    /**
     * Obtiene el gerente (admin) al que pertenece este miembro de staff.
     *
     * @return BelongsTo
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Obtiene los miembros de staff (camareros y cocineros) gestionados por este admin.
     *
     * @return HasMany
     */
    public function staff(): HasMany
    {
        return $this->hasMany(User::class, 'admin_id');
    }

    /**
     * Obtiene todos los pedidos del negocio a través de sus mesas.
     *
     * @return HasManyThrough
     */
    public function orders(): HasManyThrough
    {
        return $this->hasManyThrough(Order::class, Table::class);
    }

    /* -------------------------------------------------------------------------- */
    /* HELPERS DE ROL                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Indica si el usuario es superadmin (nivel máximo del sistema SaaS).
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Indica si el usuario es un gerente (admin del restaurante).
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Indica si el usuario es personal del restaurante (waiter o kitchen).
     *
     * @return bool
     */
    public function isStaff(): bool
    {
        return in_array($this->role, ['waiter', 'kitchen'], true);
    }

    /**
     * Indica si el negocio/usuario está activo en la plataforma.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) $this->active;
    }

    /**
     * Devuelve el user_id del propietario real de los recursos del restaurante.
     * Si el usuario es admin: devuelve su propio id.
     * Si el usuario es staff: devuelve el id de su gerente (admin_id).
     *
     * @return int
     */
    public function ownerUserId(): int
    {
        return $this->admin_id ?? $this->id;
    }
}
