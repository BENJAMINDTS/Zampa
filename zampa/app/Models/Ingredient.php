<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Ingredient
 *
 * Materia prima o ingrediente modificable.
 * Ej: "Queso Cheddar", "Huevo", "Salsa Picante".
 *
 * @property string $name           Nombre del ingrediente
 * @property boolean $is_allergen   Si se considera alérgeno importante
 */
class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'is_allergen'];

    /**
     * El restaurante dueño de este ingrediente.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Productos que llevan este ingrediente en su receta original.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'ingredient_product')
            ->withPivot(['quantity_base', 'is_removable', 'is_extra', 'extra_price']);
    }
}
