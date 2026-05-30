<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Ingredient
 *
 * Materia prima o ingrediente modificable.
 * Ej: "Queso Cheddar", "Huevo", "Salsa Picante".
 *
 * @property string    $name           Nombre del ingrediente
 * @property boolean   $is_allergen    Si se considera alérgeno (true si allergen_types no está vacío)
 * @property array     $allergen_types Slugs de alérgenos UE 1169/2011 que contiene este ingrediente
 *
 * @author AyrtonAlania
 * @author BenjaminDTS
 */
class Ingredient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'name', 'is_allergen', 'allergen_types'];

    protected $casts = [
        'is_allergen'   => 'boolean',
        'allergen_types' => 'array',
    ];

    /**
     * Slugs válidos para allergen_type.
     * Coinciden con los nombres de los SVGs en public/images/allergens/
     * Basados en el Reglamento UE 1169/2011 de información alimentaria.
     */
    public const ALLERGEN_TYPES = [
        'gluten'          => 'Cereales con gluten',
        'crustaceos'      => 'Crustáceos',
        'huevos'          => 'Huevos',
        'pescado'         => 'Pescado',
        'cacahuetes'      => 'Cacahuetes',
        'soja'            => 'Soja',
        'lacteos'         => 'Lácteos',
        'frutos-cascara'  => 'Frutos de cáscara',
        'apio'            => 'Apio',
        'mostaza'         => 'Mostaza',
        'sesamo'          => 'Granos de sésamo',
        'sulfitos'        => 'Dióxido de azufre y sulfitos',
        'altramuces'      => 'Altramuces',
        'moluscos'        => 'Moluscos',
    ];

    public const ALLERGEN_EMOJIS = [
        'gluten'          => '🌾',
        'crustaceos'      => '🦐',
        'huevos'          => '🥚',
        'pescado'         => '🐟',
        'cacahuetes'      => '🥜',
        'soja'            => '🫘',
        'lacteos'         => '🥛',
        'frutos-cascara'  => '🌰',
        'apio'            => '🌿',
        'mostaza'         => '🟡',
        'sesamo'          => '🌱',
        'sulfitos'        => '🍷',
        'altramuces'      => '🌼',
        'moluscos'        => '🐚',
    ];

    /**
     * Emojis por nombre de ingrediente para mostrar junto al nombre en los badges.
     * Fallback: '⚠️' si el nombre no está en el mapa.
     */
    public const INGREDIENT_EMOJIS = [
        'bacon'        => '🥓',
        'queso'        => '🧀',
        'tomate'       => '🍅',
        'huevo'        => '🥚',
        'lechuga'      => '🥬',
        'pollo'        => '🍗',
        'pan'          => '🍞',
        'salsa bbq'    => '🍖',
        'salsa'        => '🫙',
        'cebolla'      => '🧅',
        'ajo'          => '🧄',
        'zanahoria'    => '🥕',
        'patata'       => '🥔',
        'patatas'      => '🥔',
        'seta'         => '🍄',
        'setas'        => '🍄',
        'pimiento'     => '🫑',
        'maiz'         => '🌽',
        'maíz'         => '🌽',
        'aguacate'     => '🥑',
        'pepino'       => '🥒',
        'limon'        => '🍋',
        'limón'        => '🍋',
        'naranja'      => '🍊',
        'fresa'        => '🍓',
        'leche'        => '🥛',
        'mantequilla'  => '🧈',
        'nata'         => '🍦',
        'jamon'        => '🥩',
        'jamón'        => '🥩',
        'carne'        => '🥩',
        'ternera'      => '🥩',
        'cerdo'        => '🐷',
        'atun'         => '🐟',
        'atún'         => '🐟',
        'salmon'       => '🐟',
        'salmón'       => '🐟',
        'gambas'       => '🦐',
        'langostinos'  => '🦐',
        'anchoa'       => '🐟',
        'anchoas'      => '🐟',
        'aceite'       => '🫒',
        'oliva'        => '🫒',
        'vinagre'      => '🍶',
        'azucar'       => '🍬',
        'azúcar'       => '🍬',
        'sal'          => '🧂',
        'pimienta'     => '🌶️',
        'mostaza'      => '🟡',
        'mayonesa'     => '🥣',
        'ketchup'      => '🍅',
        'arroz'        => '🍚',
        'pasta'        => '🍝',
        'harina'       => '🌾',
        'nuez'         => '🌰',
        'nueces'       => '🌰',
        'almendra'     => '🌰',
        'almendras'    => '🌰',
        'cacahuete'    => '🥜',
        'cacahuetes'   => '🥜',
        'soja'         => '🫘',
        'cerveza'      => '🍺',
        'vino'         => '🍷',
        'chocolate'    => '🍫',
        'miel'         => '🍯',
    ];

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

    /**
     * Devuelve la URL del SVG oficial del primer alérgeno asignado.
     * Retorna null si el ingrediente no tiene alérgenos.
     *
     * @return string|null
     */
    public function allergenIconPath(): ?string
    {
        $first = ($this->allergen_types ?? [])[0] ?? null;
        if (!$first) return null;
        return asset('images/allergens/' . $first . '.svg');
    }

    /**
     * Devuelve el nombre legible del primer tipo de alérgeno.
     *
     * @return string|null
     */
    public function allergenTypeName(): ?string
    {
        $first = ($this->allergen_types ?? [])[0] ?? null;
        return $first ? (self::ALLERGEN_TYPES[$first] ?? null) : null;
    }

    /**
     * Devuelve el emoji del primer tipo de alérgeno UE.
     * Retorna ⚠️ como fallback si no tiene alérgenos asignados.
     *
     * @return string
     */
    public function allergenEmoji(): string
    {
        $first = ($this->allergen_types ?? [])[0] ?? null;
        return $first ? (self::ALLERGEN_EMOJIS[$first] ?? '⚠️') : '⚠️';
    }

    /**
     * Devuelve el emoji del ingrediente basado en su nombre.
     * Retorna ⚠️ si el nombre no está en el mapa.
     *
     * @return string
     */
    public function ingredientEmoji(): string
    {
        $key = mb_strtolower(trim($this->name));
        return self::INGREDIENT_EMOJIS[$key] ?? '⚠️';
    }
}
