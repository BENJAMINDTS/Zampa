<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Ingredient;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Controlador para la gestión del catálogo de productos.
 * Permite listar y crear nuevos platos en la carta digital, gestionando imágenes y relaciones.
 *
 * @author SebastianBCF
 * @author BenjaminDTS
 */
class ProductController extends Controller
{
  /**
   * Recupera los productos del restaurante actual y carga la vista del listado.
   *
   * @return View Vista con la tabla de productos.
   */
  /**
   * @param Request $request
   * @return View
   */
  public function index(Request $request): View
  {
    $ownerId      = Auth::user()->ownerUserId();
    $categories   = Category::where('user_id', $ownerId)->orderBy('name')->get();
    $allergenTypes = Ingredient::ALLERGEN_TYPES;
    $reorderMode  = $request->boolean('reorder');

    $query = Product::where('user_id', $ownerId)
      ->with(['allergens', 'variants', 'categories'])
      ->orderBy('sort_order')
      ->orderBy('id');

    if ($reorderMode) {
      $products = $query->get();
    } else {
      $products = $query
        ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
        ->when($request->filled('category'), fn ($q) => $q->whereHas('categories', fn ($q2) => $q2->where('categories.id', $request->category)))
        ->when($request->filled('allergen'), fn ($q) => $q->whereHas('ingredients', fn ($q2) => $q2->whereJsonContains('allergen_types', $request->allergen)))
        ->paginate(15)
        ->withQueryString();
    }

    return view('products.index', compact('products', 'categories', 'allergenTypes', 'reorderMode'));
  }

  /**
   * Muestra el formulario para crear un nuevo producto.
   * Carga las categorías del usuario autenticado para el menú desplegable.
   *
   * @return View Vista del formulario de creación.
   */
  public function create(): View
  {
    $ownerId    = Auth::user()->ownerUserId();
    $categories = Category::where('user_id', $ownerId)->get();
    return view('products.create', compact('categories'));
  }

  /**
   * Valida, procesa la imagen y almacena un nuevo producto en la base de datos.
   *
   * @param Request $request Objeto con los datos capturados del formulario web.
   * @return RedirectResponse Redirección a la vista principal con mensaje de éxito.
   */
  public function store(Request $request): RedirectResponse
  {
    $hasVariants = ! empty($request->input('variants'));

    /** @var array $validatedData Datos validados del formulario */
    $validatedData = $request->validate([
      'name'               => 'required|string|max:255',
      'description'        => 'nullable|string',
      'price'              => $hasVariants ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
      'category_ids'       => ['required', 'array', 'min:1'],
      'category_ids.*'     => [Rule::exists('categories', 'id')->where('user_id', Auth::user()->ownerUserId())],
      'image'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
      'variants'           => 'nullable|array',
      'variants.*.name'    => 'required_with:variants|string|max:100',
      'variants.*.price'   => 'required_with:variants|numeric|min:0',
      'variants.*.sort_order' => 'nullable|integer|min:0',
    ]);

    $validatedData['user_id'] = Auth::user()->ownerUserId();

    $validatedData['sort_order'] = Product::where('user_id', $validatedData['user_id'])->max('sort_order') + 1;

    if ($hasVariants) {
      $validatedData['price'] = null;
    }

    if ($request->hasFile('image')) {
      /** @var string $path Ruta donde se almacena temporalmente la imagen */
      $path = $request->file('image')->store('products', 'public');
      $validatedData['image'] = $path;
    }

    $product = DB::transaction(function () use ($validatedData, $hasVariants) {
      $product = Product::create(collect($validatedData)->except(['variants', 'category_ids'])->toArray());
      $product->categories()->sync($validatedData['category_ids']);

      if ($hasVariants) {
        foreach ($validatedData['variants'] as $idx => $variantData) {
          ProductVariant::create([
            'product_id' => $product->id,
            'name'       => $variantData['name'],
            'price'      => $variantData['price'],
            'sort_order' => $variantData['sort_order'] ?? $idx,
          ]);
        }
      }

      return $product;
    });

    Cache::forget("menu:{$validatedData['user_id']}");
    Cache::forget("chat-menu:{$validatedData['user_id']}");

    if ($request->boolean('configure_ingredients')) {
      return redirect()
        ->route('products.ingredients.edit', $product)
        ->with('success', '¡Plato creado! Ahora configura sus ingredientes.');
    }

    return redirect()
      ->route('products.index')
      ->with('success', '¡Plato creado con éxito en la carta digital!');
  }
  /**

     * Muestra el formulario para editar un producto existente.

     *

     * @param Product $product El modelo del producto a editar.

     * @return View

     */

    public function edit(Product $product): View

    {

        $ownerId = Auth::user()->ownerUserId();
        abort_if($product->user_id !== $ownerId, 403, 'No tienes permiso para editar este plato.');

        $product->load(['allergens', 'variants', 'categories']);

        $categories = Category::where('user_id', $ownerId)->get();

        return view('products.edit', compact('product', 'categories'));

    }
 
    /**

     * Actualiza los datos del producto y gestiona el reemplazo de la imagen en el disco.

     *

     * @param Request $request

     * @param Product $product

     * @return RedirectResponse

     */

    public function update(Request $request, Product $product): RedirectResponse

    {

        abort_if($product->user_id !== Auth::user()->ownerUserId(), 403);

        $hasVariants = ! empty($request->input('variants'));

        $validatedData = $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'price'              => $hasVariants ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'category_ids'       => ['required', 'array', 'min:1'],
            'category_ids.*'     => [Rule::exists('categories', 'id')->where('user_id', Auth::user()->ownerUserId())],
            'image'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'variants'           => 'nullable|array',
            'variants.*.name'    => 'required_with:variants|string|max:100',
            'variants.*.price'   => 'required_with:variants|numeric|min:0',
            'variants.*.sort_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
            $validatedData['image'] = $path;
        }

        DB::transaction(function () use ($request, $product, $validatedData, $hasVariants) {
            $productData          = collect($validatedData)->except(['variants', 'category_ids'])->toArray();
            $productData['price'] = $hasVariants ? null : $validatedData['price'];

            $product->update($productData);
            $product->categories()->sync($validatedData['category_ids']);

            $product->variants()->delete();

            if ($hasVariants) {
                foreach ($validatedData['variants'] as $idx => $variantData) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'name'       => $variantData['name'],
                        'price'      => $variantData['price'],
                        'sort_order' => $variantData['sort_order'] ?? $idx,
                    ]);
                }
            }
        });

        Cache::forget("menu:{$product->user_id}");
        Cache::forget("chat-menu:{$product->user_id}");

        return redirect()->route('products.index')->with('success', '¡Plato actualizado correctamente!');

    }
 
    /**

     * Elimina un producto de la carta digital (Soft Delete).

     *

     * @param Product $product

     * @return RedirectResponse

     */

    public function destroy(Product $product): RedirectResponse

    {

        abort_if($product->user_id !== Auth::user()->ownerUserId(), 403);

        $userId = $product->user_id;
        $product->delete();
        Cache::forget("menu:{$userId}");
        Cache::forget("chat-menu:{$userId}");

        return redirect()->route('products.index')->with('success', 'Plato retirado de la carta.');

    }

    /**
     * Muestra el configurador de ingredientes para un producto.
     *
     * @param Product $product
     * @return View
     */
    public function editIngredients(Product $product): View
    {
        $ownerId = Auth::user()->ownerUserId();
        abort_if($product->user_id !== $ownerId, 403, 'Acceso denegado.');

        $product->load('ingredients');

        $ingredients = Ingredient::where('user_id', $ownerId)
                                 ->orderBy('name')
                                 ->get();

        return view('products.ingredients', compact('product', 'ingredients'));
    }

    /**
     * Persiste el orden manual de productos arrastrado por el usuario.
     * Recibe un array de IDs en el nuevo orden; asigna sort_order = offset + posición.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorder(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'required|integer',
            'offset' => 'required|integer|min:0',
        ]);

        $ownerId = Auth::user()->ownerUserId();

        DB::transaction(function () use ($validated, $ownerId) {
            foreach ($validated['ids'] as $index => $id) {
                Product::where('id', $id)
                    ->where('user_id', $ownerId)
                    ->update(['sort_order' => $validated['offset'] + $index]);
            }
        });

        Cache::forget("menu:{$ownerId}");
        Cache::forget("chat-menu:{$ownerId}");

        return response()->json(['success' => true]);
    }

    /**
     * Sincroniza los ingredientes de un producto con sus datos de configuración de receta.
     *
     * @param Request $request
     * @param Product $product
     * @return RedirectResponse
     */
    public function syncIngredients(Request $request, Product $product): RedirectResponse
    {
        abort_if($product->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

        $validated = $request->validate([
            'ingredients'                     => 'nullable|array',
            'ingredients.*.included'          => 'nullable|boolean',
            'ingredients.*.quantity_base'     => 'numeric|min:0',
            'ingredients.*.is_removable'      => 'nullable|boolean',
            'ingredients.*.is_extra'          => 'nullable|boolean',
            'ingredients.*.extra_price'       => 'numeric|min:0',
        ]);

        $formatted = $product->formatIngredientsForSync(
            $validated['ingredients'] ?? []
        );

        $product->ingredients()->sync($formatted);
        Cache::forget("menu:{$product->user_id}");
        Cache::forget("chat-menu:{$product->user_id}");

        return redirect()
            ->route('products.edit', $product)
            ->with('success', 'Ingredientes del plato actualizados correctamente.');
    }

}

