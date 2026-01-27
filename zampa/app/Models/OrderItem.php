<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class OrderItem
 *
 * Una línea de detalle dentro de un pedido.
 * Ej: "2 Hamburguesas de la Casa".
 *
 * @property int $quantity      Cantidad solicitada
 * @property float $price     Precio unitario en el momento del pedido
 * @property string $status     'queued' (en cola), 'ready' (listo para servir)
 */
class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'product_id', 'quantity', 'price', 'status'];

    /**
     * El pedido general al que pertenece esta línea.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * El producto real que se pidió.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Las modificaciones específicas de este plato.
     * Ej: "Sin cebolla", "Extra de queso".
     */
    public function modifications(): HasMany
    {
        return $this->hasMany(OrderItemModification::class);
    }
}
