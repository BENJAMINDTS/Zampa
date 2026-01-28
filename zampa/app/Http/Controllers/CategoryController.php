<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class CategoryController
 *
 * Controlador encargado de gestionar las categorías del menú del restaurante.
 * Permite listar, crear, editar y eliminar categorías.
 *
 * @package App\Http\Controllers
 */
class CategoryController extends Controller
{
    /**
     * Muestra la lista de categorías pertenecientes al usuario autenticado.
     *
     * @return \Illuminate\View\View Retorna la vista 'categories.index' con los datos.
     */
    public function index()
    {
        // Obtener el usuario actual
        $user = Auth::user();

        // Obtener sus categorías mediante la relación definida en el modelo User
        $categories = $user->categories;

        return view('categories.index', compact('categories'));
    }

    /**
     * Muestra el formulario para crear una nueva categoría.
     *
     * @return \Illuminate\View\View Retorna la vista 'categories.create'.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Almacena una nueva categoría en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request Objeto con los datos del formulario.
     * @return \Illuminate\Http\RedirectResponse Redirige al índice de categorías.
     */
    public function store(Request $request)
    {
        // 1. Validar los datos de entrada
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'destination' => 'required|in:kitchen,bar', // Solo acepta 'kitchen' o 'bar'
        ]);

        // 2. Crear la categoría asociada al usuario
        $request->user()->categories()->create($validated);

        // 3. Redirigir al listado
        return redirect()->route('categories.index');
    }

    // --- Métodos pendientes (se implementarán más adelante) ---

    public function show(Category $category) {}
    public function edit(Category $category) {}
    public function update(Request $request, Category $category) {}
    public function destroy(Category $category) {}
}
