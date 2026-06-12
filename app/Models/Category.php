<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Category
 *
 * Categoría del menú (Ej: Entrantes, Hamburguesas, Bebidas).
 * Define a qué impresora debe enviarse (Bar o Cocina).
 *
 * @property string $destination  'kitchen' o 'bar'
 * @author BenjaminDTS
 */
class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'name', 'destination', 'sort_order'];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Los productos que pertenecen a esta categoría (M:N).
     *
     * @return BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product');
    }
}
