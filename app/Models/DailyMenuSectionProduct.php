<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class DailyMenuSectionProduct
 *
 * Modelo explícito de la tabla pivote que relaciona secciones del menú
 * con sus productos disponibles.
 *
 * @property int $daily_menu_section_id  ID de la sección
 * @property int $product_id             ID del producto
 *
 * @author Ayrtonalania
 */
class DailyMenuSectionProduct extends Model
{
    protected $fillable = [
        'daily_menu_section_id',
        'product_id',
    ];

    /**
     * La sección del menú del día a la que pertenece.
     *
     * @return BelongsTo
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(DailyMenuSection::class, 'daily_menu_section_id');
    }

    /**
     * El producto asociado a esta entrada de la sección.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
