<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class DailyMenuOrderSelection
 *
 * Registra la elección concreta de un producto dentro de una sección
 * del menú del día por parte del cliente.
 *
 * @property int $daily_menu_order_id   ID del pedido de menú del día
 * @property int $daily_menu_section_id ID de la sección del menú
 * @property int $product_id            ID del producto elegido
 * @property int $quantity              Cantidad seleccionada
 *
 * @author Ayrtonalania
 */
class DailyMenuOrderSelection extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_menu_order_id',
        'daily_menu_section_id',
        'product_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * El pedido de menú al que pertenece esta selección.
     *
     * @return BelongsTo
     */
    public function dailyMenuOrder(): BelongsTo
    {
        return $this->belongsTo(DailyMenuOrder::class);
    }

    /**
     * La sección del menú del día a la que corresponde esta elección.
     *
     * @return BelongsTo
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(DailyMenuSection::class, 'daily_menu_section_id');
    }

    /**
     * El producto seleccionado por el cliente.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
