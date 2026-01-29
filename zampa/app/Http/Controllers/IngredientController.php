<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class IngredientController
 *
 * Controlador para la gestión del inventario de ingredientes (ej: Pan, Carne, Salsas).
 * Permite listar el stock actual y registrar nuevos ingredientes.
 *
 * @package App\Http\Controllers
 */
class IngredientController extends Controller
{
    /**
     * Muestra el listado de ingredientes disponibles para el usuario actual.
     *
     * @return \Illuminate\View\View Vista con la rejilla de ingredientes.
     */
    public function index()
    {
        $user = Auth::user();
        $ingredients = $user->ingredients; // Usamos la relación del modelo User

        return view('ingredients.index', compact('ingredients'));
    }

    /**
     * Muestra el formulario para dar de alta un nuevo ingrediente.
     *
     * @return \Illuminate\View\View Vista con el formulario de creación.
     */
    public function create()
    {
        return view('ingredients.create');
    }

    /**
     * Guarda un nuevo ingrediente en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request Datos del formulario.
     * @return \Illuminate\Http\RedirectResponse Redirección al índice tras guardar.
     */
    public function store(Request $request)
    {
        // 1. Validamos (Nombre obligatorio)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // 2. Creamos (Asociado al usuario)
        $request->user()->ingredients()->create($validated);

        // 3. Redirigimos
        return redirect()->route('ingredients.index');
    }

    // --- Métodos pendientes ---
    public function show(Ingredient $ingredient) {}
    public function edit(Ingredient $ingredient) {}
    public function update(Request $request, Ingredient $ingredient) {}
    public function destroy(Ingredient $ingredient) {}
}
