<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class OrderItemModification
 *
 * Registra cambios específicos en un plato.
 * Ej: Acción 'remove' Ingrediente 'Cebolla'.
 *
 * @property string $action             'add' (Añadir) o 'remove' (Quitar)
 * @property float $amount_charged    Precio cobrado por el extra (0 si es gratis)
 */
class OrderItemModification extends Model
{
    use HasFactory;

    protected $fillable = ['order_item_id', 'ingredient_id', 'action', 'amount_charged'];

    /**
     * La línea del pedido que se modifica.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * El ingrediente que se añade o se quita.
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
