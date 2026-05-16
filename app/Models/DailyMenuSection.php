<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class DailyMenuSection
 *
 * Representa una sección del menú del día (primer plato, segundo plato, etc.).
 * Contiene los productos disponibles para que el cliente elija en esa sección.
 *
 * @property int    $daily_menu_id  ID del menú al que pertenece
 * @property string $type           Tipo: first_course|second_course|dessert|coffee|drink|bread
 * @property bool   $is_required    Si la sección es obligatoria para confirmar el pedido
 * @property bool   $is_free        Si la sección está incluida en el precio total del menú
 * @property int    $max_quantity   Cantidad máxima que el cliente puede seleccionar
 * @property int    $display_order  Orden de presentación en el stepper
 *
 * @author Ayrtonalania
 */
class DailyMenuSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_menu_id',
        'type',
        'is_required',
        'is_free',
        'max_quantity',
        'display_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_free'     => 'boolean',
    ];

    /**
     * El menú del día al que pertenece esta sección.
     *
     * @return BelongsTo
     */
    public function dailyMenu(): BelongsTo
    {
        return $this->belongsTo(DailyMenu::class);
    }

    /**
     * Los productos disponibles para elegir en esta sección.
     *
     * @return BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'daily_menu_section_products',
            'daily_menu_section_id',
            'product_id'
        )->withTimestamps();
    }
}
