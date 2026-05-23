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
 * @property int    $quantity      Cantidad solicitada
 * @property float  $price         Precio unitario en el momento del pedido
 * @property string $status        'queued' (en cola), 'ready' (listo para servir)
 * @property string $destination   'kitchen' o 'bar', copiado de la categoría del producto
 *
 * @author BenjaminDTS
 * @author AyrtonAlania
 */
class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'product_id', 'quantity', 'price', 'status', 'destination', 'is_daily_menu'];

    protected $casts = [
        'is_daily_menu' => 'boolean',
    ];

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

    /**
     * Pagos parciales que cubren este ítem.
     *
     * @return HasMany
     */
    public function splitPayments(): HasMany
    {
        return $this->hasMany(OrderItemPayment::class);
    }

    /**
     * Indica si este ítem ha sido reclamado (hay algún pago parcial pendiente o pagado).
     *
     * @return bool
     */
    public function isClaimed(): bool
    {
        return $this->splitPayments()->whereIn('status', ['pending', 'paid'])->exists();
    }

    /**
     * Indica si este ítem está completamente cubierto por pagos parciales pagados.
     *
     * @return bool
     */
    public function isPaidViaSplit(): bool
    {
        return $this->splitPayments()->where('status', 'paid')->exists();
    }
}
