<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class OrderItemPayment
 *
 * Registra cada pago parcial realizado sobre un pedido en el modo de cobro partido.
 * En modo 'items' se vincula a un order_item concreto; en modo 'equitative' order_item_id es null.
 *
 * @property int         $order_id
 * @property int|null    $order_item_id
 * @property float       $amount
 * @property string      $stripe_payment_intent_id
 * @property string      $status              'pending', 'paid', 'failed'
 * @property string      $mode                'items', 'equitative'
 * @property int|null    $parts_total
 * @property int|null    $part_number
 *
 * @author BenjaminDTS
 */
class OrderItemPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'order_item_id',
        'amount',
        'stripe_payment_intent_id',
        'status',
        'mode',
        'parts_total',
        'part_number',
    ];

    protected $casts = [
        'amount'      => 'float',
        'parts_total' => 'integer',
        'part_number' => 'integer',
    ];

    /**
     * El pedido al que pertenece este pago parcial.
     *
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * El ítem concreto que cubre este pago (null en modo equitativo).
     *
     * @return BelongsTo
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
