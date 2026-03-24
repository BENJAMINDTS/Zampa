<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Product
 *
 * Representa un plato o bebida de la carta.
 *
 * @property string $image       Imagen en formato binario (BLOB)
 * @property float $price      Precio base del producto
 * @property boolean $is_active  Si está disponible para pedir
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'description',
        'price',
        'is_active',
        'image'
    ];

    /**
     * Categoría a la que pertenece (Ej: Hamburguesas).
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relación Muchos a Muchos con Ingredientes.
     * Permite saber qué ingredientes lleva por defecto y cuánto cuesta añadirlos como extra.
     * * Accede a la tabla pivote 'ingredient_product'.
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'ingredient_product')
            ->withPivot(['quantity_base', 'is_removable', 'is_extra', 'extra_price'])
            ->withTimestamps();
    }
}
