<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

/**
 * Class IngredientController
 *
 * Controlador para la gestión del inventario de ingredientes (ej: Pan, Carne, Salsas).
 * Permite listar el stock actual y registrar nuevos ingredientes.
 *
 * @package App\Http\Controllers
 * @author BenjaminDTS
 * @author AyrtonAlania
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
        $ownerId     = Auth::user()->ownerUserId();
        $ingredients = Ingredient::where('user_id', $ownerId)->paginate(15);

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
        $request->validate([
            'name'            => 'required|string|max:255',
            'allergen_types'  => ['nullable', 'array'],
            'allergen_types.*' => ['string', Rule::in(array_keys(Ingredient::ALLERGEN_TYPES))],
        ]);

        $allergenTypes = array_values(array_filter($request->input('allergen_types', [])));
        $ownerId       = Auth::user()->ownerUserId();

        Ingredient::create([
            'user_id'        => $ownerId,
            'name'           => $request->input('name'),
            'allergen_types' => $allergenTypes ?: null,
            'is_allergen'    => count($allergenTypes) > 0,
        ]);

        Cache::forget("menu:{$ownerId}");

        return redirect()->route('ingredients.index')->with('success', 'Ingrediente creado correctamente.');
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
        abort_if($ingredient->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

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
        abort_if($ingredient->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

        $request->validate([
            'name'             => 'required|string|max:255',
            'allergen_types'   => ['nullable', 'array'],
            'allergen_types.*' => ['string', Rule::in(array_keys(Ingredient::ALLERGEN_TYPES))],
        ]);

        $allergenTypes = array_values(array_filter($request->input('allergen_types', [])));

        $ingredient->update([
            'name'           => $request->input('name'),
            'allergen_types' => $allergenTypes ?: null,
            'is_allergen'    => count($allergenTypes) > 0,
        ]);
        Cache::forget("menu:{$ingredient->user_id}");

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
        abort_if($ingredient->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

        $userId = $ingredient->user_id;
        $ingredient->delete();
        Cache::forget("menu:{$userId}");

        return redirect()->route('ingredients.index')->with('success', 'Ingrediente eliminado correctamente.');
    }
}
