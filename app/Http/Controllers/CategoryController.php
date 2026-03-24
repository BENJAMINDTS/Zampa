<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Class CategoryController
 *
 * Controlador encargado de gestionar las categorías del menú del restaurante.
 * Permite listar, crear, editar y eliminar categorías, y asignar su destino de preparación (cocina o barra).
 *
 * @package App\Http\Controllers
 * @author BenjaminDTS, SebastianBCF
 */
class CategoryController extends Controller
{
    /**
     * Muestra la lista de categorías pertenecientes al usuario autenticado.
     *
     * @return View Retorna la vista 'categories.index' con los datos.
     */
    public function index(): View
    {
        // Obtener el usuario actual
        $user = Auth::user();

        // Obtener sus categorías mediante la relación definida en el modelo User
        $categories = $user->categories()->paginate(15);

        return view('categories.index', compact('categories'));
    }

    /**
     * Muestra el formulario para crear una nueva categoría.
     *
     * @return View Retorna la vista 'categories.create'.
     */
    public function create(): View
    {
        return view('categories.create');
    }

    /**
     * Almacena una nueva categoría en la base de datos.
     *
     * @param  Request  $request Objeto con los datos del formulario.
     * @return RedirectResponse Redirige al índice de categorías.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validar los datos de entrada
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'destination' => 'required|in:kitchen,bar', // Solo acepta 'kitchen' o 'bar'
        ]);

        // 2. Crear la categoría asociada al usuario
        $request->user()->categories()->create($validated);

        // 3. Redirigir al listado con mensaje de éxito
        return redirect()->route('categories.index')->with('success', 'Categoría creada correctamente.');
    }

    /**
     * Muestra los detalles de una categoría específica.
     * * @param Category $category
     */
    public function show(Category $category)
    {
        // Método pendiente de implementación si se requiere vista de detalle
    }

    /**
     * Muestra el formulario para editar una categoría existente.
     *
     * @param Category $category El modelo de la categoría a editar.
     * @return View
     */
    public function edit(Category $category): View
    {
        // Protección Multitenancy: solo el dueño puede editar su categoría
        abort_if($category->user_id !== Auth::id(), 403, 'No tienes permiso para editar esta categoría.');

        return view('categories.edit', compact('category'));
    }

    /**
     * Actualiza el nombre y el destino de la categoría en la base de datos.
     *
     * @param Request $request
     * @param Category $category
     * @return RedirectResponse
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        abort_if($category->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'destination' => 'required|in:kitchen,bar',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', '¡Categoría actualizada correctamente!');
    }

    /**
     * Elimina una categoría de la base de datos.
     *
     * @param Category $category
     * @return RedirectResponse
     */
    public function destroy(Category $category): RedirectResponse
    {
        abort_if($category->user_id !== Auth::id(), 403);

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Categoría eliminada.');
    }
}
