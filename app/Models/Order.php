<?php

namespace App\Models;

use App\Models\DailyMenuOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

/**
 * Class Order
 *
 * Representa un pedido (la cuenta de una mesa).
 * Puede contener múltiples items (platos).
 *
 * @property string $status         'pending', 'cooking', 'ready', 'served', 'closed'
 * @property float $total         Total a pagar
 * @property string $payment_status 'pending' o 'paid'
 *
 * @author BenjaminDTS
 * @author AyrtonAlania
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_id',
        'status',
        'total',
        'tip',
        'mixed_cash_amount',
        'payment_method',
        'payment_status',
        'note',
        'notification_ready',
        'bill_requested',
        'requested_payment_method',
    ];

    protected $casts = [
        'notification_ready' => 'boolean',
        'bill_requested'     => 'boolean',
        'total'              => 'float',
        'tip'                => 'float',
        'mixed_cash_amount'  => 'float',
    ];

    /**
     * Cierra las conversaciones activas de la mesa cuando el pedido se cierra.
     *
     * Este hook se activará automáticamente cuando el flujo de pago
     * marque el Order como 'closed' (Bloque de pago — pendiente de implementar).
     */
    protected static function booted(): void
    {
        static::updated(function (Order $order): void {
            if ($order->wasChanged('status') && $order->status === 'closed') {
                Conversation::where('table_id', $order->table_id)
                    ->where('status', 'active')
                    ->update(['status' => 'closed']);
            }
        });
    }

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

    /**
     * El pedido de menú del día vinculado a este pedido, si existe.
     *
     * @return HasOne
     */
    public function dailyMenuOrder(): HasOne
    {
        return $this->hasOne(DailyMenuOrder::class);
    }

    /**
     * Todos los pagos parciales realizados sobre este pedido.
     *
     * @return HasMany
     */
    public function splitPayments(): HasMany
    {
        return $this->hasMany(OrderItemPayment::class);
    }

    /**
     * Registros contables de cobro partido (solo para payment_method='split').
     *
     * @return HasMany
     */
    public function orderPayments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    /**
     * Suma de los pagos parciales con status 'paid'.
     *
     * @return float
     */
    public function getPaidAmountViaSplit(): float
    {
        return (float) $this->splitPayments()->where('status', 'paid')->sum('amount');
    }

    /**
     * Indica si el total del pedido está completamente cubierto por pagos parciales pagados.
     *
     * @return bool
     */
    public function isFullyPaidViaSplit(): bool
    {
        return $this->getPaidAmountViaSplit() >= (float) $this->total;
    }
}
