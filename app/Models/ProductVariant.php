<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProductVariant
 *
 * Variante de un producto (ej: Ración entera, Media ración, Tapa).
 * Cada variante tiene su propio precio. Un producto con variantes
 * no tiene precio directo; el precio lo aporta la variante elegida.
 *
 * @author BenjaminDTS
 */
class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'name', 'price', 'sort_order'];

    protected $casts = [
        'price'      => 'decimal:2',
        'sort_order' => 'integer',
    ];

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
