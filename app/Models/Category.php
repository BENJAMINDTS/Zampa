<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Category
 *
 * Categoría del menú (Ej: Entrantes, Hamburguesas, Bebidas).
 * Define a qué impresora debe enviarse (Bar o Cocina).
 *
 * @property string $destination  'kitchen' o 'bar'
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'destination'];

    /**
     * Los productos que pertenecen a esta categoría.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
