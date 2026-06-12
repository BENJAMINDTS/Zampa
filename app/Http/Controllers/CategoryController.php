<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\TapaConfig;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $ownerId     = Auth::user()->ownerUserId();
        $reorderMode = $request->boolean('reorder');

        $query = Category::where('user_id', $ownerId)
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($reorderMode) {
            $categories = $query->get();
        } else {
            $categories = $query
                ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
                ->when($request->filled('destination'), fn ($q) => $q->where('destination', $request->destination))
                ->paginate(15)
                ->withQueryString();
        }

        return view('categories.index', compact('categories', 'reorderMode'));
    }

    /**
     * Persiste el orden manual de categorías arrastrado por el usuario.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'required|integer',
        ]);

        $ownerId = Auth::user()->ownerUserId();

        DB::transaction(function () use ($validated, $ownerId) {
            foreach ($validated['ids'] as $index => $id) {
                Category::where('id', $id)
                    ->where('user_id', $ownerId)
                    ->update(['sort_order' => $index]);
            }
        });

        Cache::forget("menu:{$ownerId}");

        return response()->json(['success' => true]);
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

        // 2. Crear la categoría bajo el propietario del restaurante
        $ownerId = Auth::user()->ownerUserId();
        Category::create(array_merge($validated, ['user_id' => $ownerId]));
        Cache::forget("menu:{$ownerId}");

        // 3. Redirigir al listado con mensaje de éxito
        return redirect()->route('categories.index')->with('success', 'Categoría creada correctamente.');
    }

    /**
     * Muestra los detalles de una categoría específica.
     *
     * @param  Category  $category El modelo de la categoría a mostrar.
     * @return void
     */
    public function show(Category $category): void
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
        abort_if($category->user_id !== Auth::user()->ownerUserId(), 403, 'No tienes permiso para editar esta categoría.');

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
        abort_if($category->user_id !== Auth::user()->ownerUserId(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'destination' => 'required|in:kitchen,bar',
        ]);

        if ($category->name === 'Tapas' && $validated['name'] !== 'Tapas') {
            $ownerId    = Auth::user()->ownerUserId();
            $tapaConfig = TapaConfig::where('user_id', $ownerId)->first();
            if ($tapaConfig?->tapas_enabled) {
                return back()->withErrors(['name' => 'No puedes renombrar la categoría "Tapas" mientras el sistema de tapas esté activo.']);
            }
        }

        $category->update($validated);
        Cache::forget("menu:{$category->user_id}");

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
        abort_if($category->user_id !== Auth::user()->ownerUserId(), 403);

        if ($category->name === 'Tapas') {
            $ownerId    = Auth::user()->ownerUserId();
            $tapaConfig = TapaConfig::where('user_id', $ownerId)->first();
            if ($tapaConfig?->tapas_enabled) {
                return back()->withErrors(['general' => 'No puedes eliminar la categoría "Tapas" mientras el sistema de tapas esté activo.']);
            }
        }

        $userId = $category->user_id;
        $category->delete();
        Cache::forget("menu:{$userId}");

        return redirect()->route('categories.index')->with('success', 'Categoría eliminada.');
    }
}
