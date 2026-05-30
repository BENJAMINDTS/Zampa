<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Order;
use App\Models\Table;
use App\Models\Zone;
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
 * @property bool   $is_waiter     Admin actuando también como camarero
 * @property bool   $is_kitchen    Admin actuando también como cocinero
 * @property string|null $business_name  Nombre del negocio (solo admins/gerentes)
 * @property string|null $address        Dirección del negocio
 * @property float|null  $lat            Latitud del negocio
 * @property float|null  $lng            Longitud del negocio
 * @property int         $floor_width    Ancho del canvas del plano (px)
 * @property int         $floor_height   Alto del canvas del plano (px)
 * @property int         $floor_count    Número de plantas activas del local (1–5)
 * @property bool        $floors_enabled Si el sistema de plantas está activo
 * @property bool        $split_payment_enabled   Si el cobro partido está activo para este restaurante
 * @property int|null    $split_payment_max_parts Máximo de partes en que se puede dividir la cuenta (null = sin límite)
 * @property string|null $stripe_account_id           ID de cuenta Stripe Connect Express del restaurante (acct_xxx)
 * @property bool        $stripe_onboarding_completed Si el restaurante completó el onboarding de Stripe Connect
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
        'is_waiter',
        'is_kitchen',
        'admin_id',
        'business_name',
        'address',
        'lat',
        'lng',
        'floor_width',
        'floor_height',
        'floor_count',
        'floors_enabled',
        'floor_canvas_sizes',
        'split_payment_enabled',
        'split_payment_max_parts',
        'stripe_account_id',
        'stripe_onboarding_completed',
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
            'password'          => 'hashed',
            'active'            => 'boolean',
            'is_waiter'         => 'boolean',
            'is_kitchen'        => 'boolean',
            'floor_count'             => 'integer',
            'floors_enabled'          => 'boolean',
            'floor_canvas_sizes'      => 'array',
            'split_payment_enabled'        => 'boolean',
            'split_payment_max_parts'      => 'integer',
            'stripe_onboarding_completed'  => 'boolean',
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
     * Obtiene las Zonas definidas en el plano del restaurante.
     *
     * @return HasMany
     */
    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
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
     * Obtiene la configuración del ticket PDF del restaurante.
     *
     * @return HasOne
     */
    public function ticketConfig(): HasOne
    {
        return $this->hasOne(TicketConfig::class);
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
     * Indica si el usuario puede actuar como camarero.
     * Cubre el rol nativo 'waiter' y el admin con la opción activada.
     *
     * @return bool
     */
    public function isWaiter(): bool
    {
        return $this->role === 'waiter'
            || ($this->role === 'admin' && (bool) $this->is_waiter);
    }

    /**
     * Indica si el usuario puede actuar como cocinero.
     * Cubre el rol nativo 'kitchen' y el admin con la opción activada.
     *
     * @return bool
     */
    public function isKitchen(): bool
    {
        return $this->role === 'kitchen'
            || ($this->role === 'admin' && (bool) $this->is_kitchen);
    }

    /**
     * Única fuente de verdad para el acceso al panel de Barra.
     *
     * @return bool
     */
    public function canAccessBar(): bool
    {
        return $this->isWaiter();
    }

    /**
     * Única fuente de verdad para el acceso al panel de Cocina.
     *
     * @return bool
     */
    public function canAccessKitchen(): bool
    {
        return $this->isKitchen();
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

    /**
     * Indica si el cobro partido está habilitado para este restaurante.
     *
     * @return bool
     */
    public function isSplitPaymentEnabled(): bool
    {
        return (bool) $this->split_payment_enabled;
    }

    /**
     * Devuelve el nombre de ruta de la pantalla principal según el rol del usuario.
     * Usado para la redirección post-login y el logo del navbar.
     *
     * @return string
     */
    public function homeRoute(): string
    {
        return match ($this->role) {
            'superadmin' => 'superadmin.dashboard',
            'waiter'     => 'bar.index',
            'kitchen'    => 'kitchen.index',
            default      => 'dashboard',
        };
    }
}
