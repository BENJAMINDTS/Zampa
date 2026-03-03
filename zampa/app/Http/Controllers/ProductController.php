<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Controlador para la gestión del catálogo de productos.
 * Permite listar y crear nuevos platos en la carta digital.
 *
 * @author SebastianBCF
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
    $products = Product::where('user_id', auth::id())->get();
    return view('products.index', compact('products'));
  }

  /**
   * Muestra el formulario para crear un nuevo producto.
   *
   * @return View Vista del formulario de creación.
   */
  public function create(): View
  {
    // Nota: Aquí se deberían cargar las categorías para pasarlas a la vista
    return view('products.create');
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

    $validatedData['user_id'] = auth::id();

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
}
