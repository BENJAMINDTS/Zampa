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
 * @author BenjaminDTS
 */
class IngredientController extends Controller
{
    /**
     * Muestra el listado de ingredientes disponibles para el usuario actual.
     *
     * @return \Illuminate\View\View Vista con la rejilla de ingredientes.
     */
    public function index(): \Illuminate\View\View
    {
        $user = Auth::user();
        $ingredients = $user->ingredients()->paginate(15); // Usamos la relación del modelo User

        return view('ingredients.index', compact('ingredients'));
    }

    /**
     * Muestra el formulario para dar de alta un nuevo ingrediente.
     *
     * @return \Illuminate\View\View Vista con el formulario de creación.
     */
    public function create(): \Illuminate\View\View
    {
        return view('ingredients.create');
    }

    /**
     * Guarda un nuevo ingrediente en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request Datos del formulario.
     * @return \Illuminate\Http\RedirectResponse Redirección al índice tras guardar.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
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

    /**
     * Muestra los detalles de un ingrediente específico.
     *
     * @param  Ingredient  $ingredient El modelo del ingrediente a mostrar.
     * @return void
     */
    public function show(Ingredient $ingredient): void {}

    /**
     * Muestra el formulario para editar un ingrediente existente.
     *
     * @param  Ingredient  $ingredient El modelo del ingrediente a editar.
     * @return \Illuminate\View\View Vista con el formulario de edición.
     */
    public function edit(Ingredient $ingredient): \Illuminate\View\View
    {
        abort_if($ingredient->user_id !== Auth::id(), 403, 'Acceso denegado.');

        return view('ingredients.edit', compact('ingredient'));
    }

    /**
     * Actualiza los datos de un ingrediente en la base de datos.
     *
     * @param  Request    $request    Datos del formulario de edición.
     * @param  Ingredient $ingredient El modelo del ingrediente a actualizar.
     * @return \Illuminate\Http\RedirectResponse Redirección al índice tras actualizar.
     */
    public function update(Request $request, Ingredient $ingredient): \Illuminate\Http\RedirectResponse
    {
        abort_if($ingredient->user_id !== Auth::id(), 403, 'Acceso denegado.');

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'is_allergen' => 'boolean',
        ]);

        $ingredient->update($validated);

        return redirect()->route('ingredients.index')->with('success', 'Ingrediente actualizado correctamente.');
    }

    /**
     * Elimina un ingrediente de la base de datos.
     *
     * @param  Ingredient  $ingredient El modelo del ingrediente a eliminar.
     * @return \Illuminate\Http\RedirectResponse Redirección al índice tras eliminar.
     */
    public function destroy(Ingredient $ingredient): \Illuminate\Http\RedirectResponse
    {
        abort_if($ingredient->user_id !== Auth::id(), 403, 'Acceso denegado.');

        $ingredient->delete();

        return redirect()->route('ingredients.index')->with('success', 'Ingrediente eliminado correctamente.');
    }
}
