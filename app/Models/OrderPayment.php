<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro contable de un pago (o parte de un pago) sobre un pedido.
 * Se crea únicamente en pedidos de cobro partido (payment_method='split').
 * Para pagos simples (cash/card), el registro vive en orders.total.
 *
 * @property int    $order_id
 * @property string $method   'cash' | 'card'
 * @property float  $amount
 * @property float  $tip
 *
 * @author BenjaminDTS
 */
class OrderPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'method',
        'amount',
        'tip',
    ];

    protected $casts = [
        'amount' => 'float',
        'tip'    => 'float',
    ];

    /**
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
