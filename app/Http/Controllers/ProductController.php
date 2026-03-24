<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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
  public function index(): View
  {
    $products = Product::where('user_id', Auth::id())->paginate(15);
    return view('products.index', compact('products'));
  }

  /**
   * Muestra el formulario para crear un nuevo producto.
   * Carga las categorías del usuario autenticado para el menú desplegable.
   *
   * @return View Vista del formulario de creación.
   */
  public function create(): View
  {
    $categories = Category::where('user_id', Auth::id())->get();
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
    /** @var array $validatedData Datos validados del formulario */
    $validatedData = $request->validate([
      'name'        => 'required|string|max:255',
      'description' => 'nullable|string',
      'price'       => 'required|numeric|min:0',
      'category_id' => 'required|exists:categories,id',
      'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
    ]);

    $validatedData['user_id'] = Auth::id();

    if ($request->hasFile('image')) {
      /** @var string $path Ruta donde se almacena temporalmente la imagen */
      $path = $request->file('image')->store('products', 'public');
      $validatedData['image'] = $path;
    }

    Product::create($validatedData);

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

        abort_if($product->user_id !== Auth::id(), 403, 'No tienes permiso para editar este plato.');
 
        $categories = Category::where('user_id', Auth::id())->get();

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

        abort_if($product->user_id !== Auth::id(), 403);
 
        $validatedData = $request->validate([

            'name'        => 'required|string|max:255',

            'description' => 'nullable|string',

            'price'       => 'required|numeric|min:0',

            'category_id' => 'required|exists:categories,id',

            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',

        ]);
 
        if ($request->hasFile('image')) {

            if ($product->image) {

                Storage::disk('public')->delete($product->image);

            }

            $path = $request->file('image')->store('products', 'public');

            $validatedData['image'] = $path;

        }
 
        $product->update($validatedData);
 
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

        abort_if($product->user_id !== Auth::id(), 403);

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Plato retirado de la carta.');

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
        abort_if($product->user_id !== Auth::id(), 403, 'Acceso denegado.');

        $validated = $request->validate([
            'ingredients'                     => 'nullable|array',
            'ingredients.*.quantity_base'     => 'numeric|min:0',
            'ingredients.*.is_removable'      => 'boolean',
            'ingredients.*.is_extra'          => 'boolean',
            'ingredients.*.extra_price'       => 'numeric|min:0',
        ]);

        $formatted = $product->formatIngredientsForSync(
            $validated['ingredients'] ?? []
        );

        $product->ingredients()->sync($formatted);

        return redirect()
            ->route('products.edit', $product)
            ->with('success', 'Ingredientes del plato actualizados correctamente.');
    }

}

