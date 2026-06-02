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
        // ── Carnes y aves ───────────────────────────────────────────────────────
        'bacon'          => '🥓',
        'carne'          => '🥩',
        'ternera'        => '🥩',
        'buey'           => '🥩',
        'lomo'           => '🥩',
        'cecina'         => '🥩',
        'filete'         => '🥩',
        'solomillo'      => '🥩',
        'entrecot'       => '🥩',
        'jamon'          => '🥩',
        'jamón'          => '🥩',
        'jamon serrano'  => '🥩',
        'jamón serrano'  => '🥩',
        'jamon iberico'  => '🥩',
        'jamón ibérico'  => '🥩',
        'mortadela'      => '🥩',
        'foie'           => '🥩',
        'foie gras'      => '🥩',
        'pollo'          => '🍗',
        'pechuga'        => '🍗',
        'muslo'          => '🍗',
        'alitas'         => '🍗',
        'pavo'           => '🦃',
        'pato'           => '🦆',
        'cerdo'          => '🐷',
        'costilla'       => '🍖',
        'costillas'      => '🍖',
        'salsa bbq'      => '🍖',
        'cordero'        => '🐑',
        'chorizo'        => '🌭',
        'salchicha'      => '🌭',
        'salchichas'     => '🌭',
        'morcilla'       => '🌭',
        'fuet'           => '🌭',
        'longaniza'      => '🌭',
        'frankfurt'      => '🌭',

        // ── Pescados ────────────────────────────────────────────────────────────
        'atun'           => '🐟',
        'atún'           => '🐟',
        'salmon'         => '🐟',
        'salmón'         => '🐟',
        'anchoa'         => '🐟',
        'anchoas'        => '🐟',
        'boquerón'       => '🐟',
        'boqueron'       => '🐟',
        'boquerones'     => '🐟',
        'merluza'        => '🐟',
        'bacalao'        => '🐟',
        'lubina'         => '🐟',
        'dorada'         => '🐟',
        'bonito'         => '🐟',
        'trucha'         => '🐟',
        'sardina'        => '🐟',
        'sardinas'       => '🐟',
        'rape'           => '🐟',
        'lenguado'       => '🐟',
        'jurel'          => '🐟',

        // ── Mariscos ────────────────────────────────────────────────────────────
        'gambas'         => '🦐',
        'gamba'          => '🦐',
        'langostinos'    => '🦐',
        'langostino'     => '🦐',
        'bogavante'      => '🦞',
        'langosta'       => '🦞',
        'cangrejo'       => '🦀',
        'calamar'        => '🦑',
        'calamares'      => '🦑',
        'sepia'          => '🦑',
        'chipirón'       => '🦑',
        'chipirón'       => '🦑',
        'chipirones'     => '🦑',
        'pulpo'          => '🐙',
        'mejillon'       => '🦪',
        'mejillón'       => '🦪',
        'mejillones'     => '🦪',
        'almeja'         => '🦪',
        'almejas'        => '🦪',
        'ostra'          => '🦪',
        'ostras'         => '🦪',
        'vieira'         => '🦪',
        'berberecho'     => '🦪',
        'berberechos'    => '🦪',
        'navaja'         => '🦪',

        // ── Verduras y hortalizas ────────────────────────────────────────────────
        'tomate'         => '🍅',
        'lechuga'        => '🥬',
        'espinaca'       => '🥬',
        'espinacas'      => '🥬',
        'rucula'         => '🥬',
        'rúcula'         => '🥬',
        'canonigos'      => '🥬',
        'canónigos'      => '🥬',
        'col'            => '🥬',
        'repollo'        => '🥬',
        'lombarda'       => '🥬',
        'pimiento'       => '🫑',
        'pimientos'      => '🫑',
        'calabacin'      => '🥒',
        'calabacín'      => '🥒',
        'pepino'         => '🥒',
        'zanahoria'      => '🥕',
        'cebolla'        => '🧅',
        'cebolleta'      => '🧅',
        'cebollino'      => '🌿',
        'ajo'            => '🧄',
        'ajete'          => '🧄',
        'patata'         => '🥔',
        'patatas'        => '🥔',
        'boniato'        => '🍠',
        'maiz'           => '🌽',
        'maíz'           => '🌽',
        'aguacate'       => '🥑',
        'berenjena'      => '🍆',
        'berenjenas'     => '🍆',
        'brocoli'        => '🥦',
        'brócoli'        => '🥦',
        'coliflor'       => '🥦',
        'alcachofa'      => '🥦',
        'alcachofas'     => '🥦',
        'esparrago'      => '🌿',
        'espárrago'      => '🌿',
        'esparragos'     => '🌿',
        'espárragos'     => '🌿',
        'guisante'       => '🌱',
        'guisantes'      => '🌱',
        'garbanzo'       => '🫘',
        'garbanzos'      => '🫘',
        'lenteja'        => '🫘',
        'lentejas'       => '🫘',
        'alubia'         => '🫘',
        'alubias'        => '🫘',
        'judias'         => '🫘',
        'judías'         => '🫘',
        'frijoles'       => '🫘',
        'soja'           => '🫘',
        'tofu'           => '🫘',
        'edamame'        => '🫘',
        'apio'           => '🌿',
        'remolacha'      => '🫑',
        'nabo'           => '🫑',
        'rabano'         => '🫑',
        'rábano'         => '🫑',

        // ── Setas y hongos ───────────────────────────────────────────────────────
        'seta'           => '🍄',
        'setas'          => '🍄',
        'champiñon'      => '🍄',
        'champiñones'    => '🍄',
        'portobello'     => '🍄',
        'boletus'        => '🍄',
        'rebozuelo'      => '🍄',
        'shiitake'       => '🍄',
        'trufa'          => '🍄',
        'trufa negra'    => '🍄',

        // ── Quesos ───────────────────────────────────────────────────────────────
        'queso'          => '🧀',
        'mozzarella'     => '🧀',
        'parmesano'      => '🧀',
        'manchego'       => '🧀',
        'cheddar'        => '🧀',
        'feta'           => '🧀',
        'brie'           => '🧀',
        'gouda'          => '🧀',
        'roquefort'      => '🧀',
        'gorgonzola'     => '🧀',
        'ricotta'        => '🧀',
        'gruyere'        => '🧀',
        'gruyère'        => '🧀',
        'emmental'       => '🧀',
        'camembert'      => '🧀',
        'queso crema'    => '🧀',
        'queso fresco'   => '🧀',
        'queso de cabra' => '🧀',

        // ── Lácteos ──────────────────────────────────────────────────────────────
        'leche'          => '🥛',
        'mantequilla'    => '🧈',
        'nata'           => '🍦',
        'nata agria'     => '🍦',
        'crema agria'    => '🍦',
        'yogur'          => '🥛',
        'yogurt'         => '🥛',
        'huevo'          => '🥚',
        'huevos'         => '🥚',
        'clara'          => '🥚',
        'yema'           => '🥚',

        // ── Pan, pastas y cereales ───────────────────────────────────────────────
        'pan'            => '🍞',
        'pan de molde'   => '🍞',
        'baguette'       => '🥖',
        'pan pita'       => '🫓',
        'tortilla'       => '🫓',
        'crackers'       => '🍘',
        'arroz'          => '🍚',
        'pasta'          => '🍝',
        'macarrones'     => '🍝',
        'espagueti'      => '🍝',
        'espaguetis'     => '🍝',
        'fideos'         => '🍜',
        'harina'         => '🌾',
        'quinoa'         => '🌾',
        'avena'          => '🌾',
        'couscous'       => '🍚',
        'cous cous'      => '🍚',

        // ── Frutas ───────────────────────────────────────────────────────────────
        'fresa'          => '🍓',
        'fresas'         => '🍓',
        'manzana'        => '🍎',
        'pera'           => '🍐',
        'naranja'        => '🍊',
        'mandarina'      => '🍊',
        'pomelo'         => '🍊',
        'limon'          => '🍋',
        'limón'          => '🍋',
        'platano'        => '🍌',
        'plátano'        => '🍌',
        'uva'            => '🍇',
        'uvas'           => '🍇',
        'mango'          => '🥭',
        'piña'           => '🍍',
        'coco'           => '🥥',
        'kiwi'           => '🥝',
        'melocoton'      => '🍑',
        'melocotón'      => '🍑',
        'cereza'         => '🍒',
        'cerezas'        => '🍒',
        'sandia'         => '🍉',
        'sandía'         => '🍉',
        'melon'          => '🍈',
        'melón'          => '🍈',
        'frambuesa'      => '🫐',
        'frambuesas'     => '🫐',
        'arandano'       => '🫐',
        'arándano'       => '🫐',
        'arandanos'      => '🫐',
        'arándanos'      => '🫐',
        'granada'        => '🫐',

        // ── Frutos secos ─────────────────────────────────────────────────────────
        'nuez'           => '🌰',
        'nueces'         => '🌰',
        'almendra'       => '🌰',
        'almendras'      => '🌰',
        'avellana'       => '🌰',
        'avellanas'      => '🌰',
        'pistacho'       => '🌰',
        'pistachos'      => '🌰',
        'cacahuete'      => '🥜',
        'cacahuetes'     => '🥜',
        'anacardo'       => '🌰',
        'anacardos'      => '🌰',
        'pipa'           => '🌻',
        'pipas'          => '🌻',

        // ── Aceites y grasas ─────────────────────────────────────────────────────
        'aceite'         => '🫒',
        'oliva'          => '🫒',
        'aceite de oliva'=> '🫒',
        'aceitunas'      => '🫒',
        'aceituna'       => '🫒',

        // ── Salsas y condimentos ─────────────────────────────────────────────────
        'salsa'          => '🫙',
        'ketchup'        => '🍅',
        'mayonesa'       => '🥣',
        'mostaza'        => '🟡',
        'alioli'         => '🧄',
        'pesto'          => '🌿',
        'guacamole'      => '🥑',
        'hummus'         => '🫘',
        'tzatziki'       => '🥒',
        'romesco'        => '🌶️',
        'vinagre'        => '🍶',

        // ── Especias y hierbas ───────────────────────────────────────────────────
        'sal'            => '🧂',
        'pimienta'       => '🌶️',
        'pimenton'       => '🌶️',
        'pimentón'       => '🌶️',
        'paprika'        => '🌶️',
        'páprika'        => '🌶️',
        'cayena'         => '🌶️',
        'chili'          => '🌶️',
        'tabasco'        => '🌶️',
        'curry'          => '🟡',
        'curcuma'        => '🟡',
        'cúrcuma'        => '🟡',
        'azafran'        => '🟡',
        'azafrán'        => '🟡',
        'perejil'        => '🌿',
        'cilantro'       => '🌿',
        'albahaca'       => '🌿',
        'oregano'        => '🌿',
        'orégano'        => '🌿',
        'romero'         => '🌿',
        'tomillo'        => '🌿',
        'laurel'         => '🍃',
        'menta'          => '🌿',
        'hierbabuena'    => '🌿',
        'jengibre'       => '🌿',
        'canela'         => '🌿',
        'comino'         => '🌿',
        'nuez moscada'   => '🌰',

        // ── Dulces y repostería ──────────────────────────────────────────────────
        'azucar'         => '🍬',
        'azúcar'         => '🍬',
        'miel'           => '🍯',
        'chocolate'      => '🍫',
        'cacao'          => '🍫',
        'vainilla'       => '🌿',
        'caramelo'       => '🍬',
        'mermelada'      => '🍓',

        // ── Bebidas ──────────────────────────────────────────────────────────────
        'cerveza'        => '🍺',
        'vino'           => '🍷',
        'cava'           => '🍾',
        'champan'        => '🍾',
        'champán'        => '🍾',
        'brandy'         => '🥃',
        'whisky'         => '🥃',
        'whiskey'        => '🥃',
        'ron'            => '🥃',
        'gin'            => '🥃',
        'vodka'          => '🥃',
        'tequila'        => '🥃',
        'agua'           => '💧',
        'hielo'          => '🧊',
        'cafe'           => '☕',
        'café'           => '☕',
        'te'             => '🍵',
        'té'             => '🍵',

        // ── Caldos y bases ───────────────────────────────────────────────────────
        'caldo'          => '🍲',
        'caldo de pollo' => '🍲',
        'caldo de carne' => '🍲',
        'caldo de pescado'=> '🍲',
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
