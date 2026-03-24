<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Order
 *
 * Representa un pedido (la cuenta de una mesa).
 * Puede contener múltiples items (platos).
 *
 * @property string $status         'pending', 'cooking', 'ready', 'served', 'closed'
 * @property float $total         Total a pagar
 * @property string $payment_status 'pending' o 'paid'
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_id',
        'status',
        'total',
        'tip',
        'payment_method',
        'payment_status',
        'note'
    ];

    /**
     * La mesa a la que pertenece el pedido.
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Los platos individuales dentro de este pedido.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
