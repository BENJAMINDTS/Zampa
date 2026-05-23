<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Product
 *
 * Representa un plato o bebida de la carta.
 *
 * @author SebastianBCF
 * @author BenjaminDTS
 *
 * @property string  $image       Ruta relativa de la imagen almacenada en storage/app/public/products
 * @property float|null $price   Precio base del producto (null si tiene variantes)
 * @property boolean $is_active  Si está disponible para pedir
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

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
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

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
     * Accede a la tabla pivote 'ingredient_product'.
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'ingredient_product')
            ->withPivot(['quantity_base', 'is_removable', 'is_extra', 'extra_price'])
            ->withTimestamps();
    }

    /**
     * Devuelve solo los ingredientes del plato que están marcados como alérgeno.
     * Se actualiza automáticamente al modificar los ingredientes del plato.
     *
     * @return BelongsToMany
     */
    public function allergens(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'ingredient_product')
            ->where('is_allergen', true)
            ->withTimestamps();
    }

    /**
     * Variantes del producto (ej: Ración entera, Media ración).
     *
     * @return HasMany
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Indica si el producto tiene al menos una variante definida.
     *
     * @return bool
     */
    public function hasVariants(): bool
    {
        return $this->variants()->exists();
    }

    /**
     * Devuelve el precio efectivo para mostrar en la carta.
     * Si tiene variantes, devuelve el precio de la más barata ("desde X€").
     * Si no tiene variantes, devuelve el precio directo del producto.
     *
     * @return float
     */
    public function getEffectivePrice(): float
    {
        if ($this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            return (float) $this->variants->min('price');
        }

        if ($this->variants()->exists()) {
            return (float) $this->variants()->min('price');
        }

        return (float) $this->price;
    }

    /**
     * Transforma el input del formulario al formato que espera Eloquent sync().
     *
     * @param array $ingredientsInput Array del formulario indexado por ingredient_id
     * @return array Array formateado para sync() con datos de la tabla pivote
     */
    public function formatIngredientsForSync(array $ingredientsInput): array
    {
        $formatted = [];
        foreach ($ingredientsInput as $id => $data) {
            if (empty($data['included'])) {
                continue;
            }
            $formatted[$id] = [
                'quantity_base' => $data['quantity_base'] ?? 1,
                'is_removable'  => isset($data['is_removable']) ? 1 : 0,
                'is_extra'      => isset($data['is_extra']) ? 1 : 0,
                'extra_price'   => $data['extra_price'] ?? 0.00,
            ];
        }
        return $formatted;
    }

}
