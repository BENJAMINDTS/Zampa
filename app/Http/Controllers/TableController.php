<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Controlador para la gestión de mesas y sus códigos QR.
 *
 * Permite al gerente ver todas sus mesas con el QR generado
 * para cada una, descargar el QR en formato PNG o SVG, y
 * regenerar el unique_hash invalidando el QR anterior.
 * Incluye el mapa visual drag & drop para crear, mover y eliminar mesas.
 *
 * @author AyrtonAlania
 */
class TableController extends Controller
{
    /**
     * Muestra el listado de mesas del restaurante autenticado
     * junto con el QR generado para cada una.
     *
     * @return View
     */
    public function index(): View
    {
        $tables    = Table::where('user_id', Auth::id())->orderBy('name')->get();
        $maxTables = Auth::user()->plan?->max_tables ?? 10;

        return view('tables.index', compact('tables', 'maxTables'));
    }

    /**
     * Muestra el mapa visual interactivo con drag & drop para gestionar mesas.
     *
     * @return View
     */
    public function map(): View
    {
        $tables    = Table::where('user_id', Auth::id())->orderBy('name')->get();
        $maxTables = Auth::user()->plan?->max_tables ?? 10;

        return view('tables.map', compact('tables', 'maxTables'));
    }

    /**
     * Crea una nueva mesa desde el mapa visual.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => 'required|string|max:50',
            'shape'      => 'required|in:square,round,rectangle',
            'position_x' => 'required|integer|min:0',
            'position_y' => 'required|integer|min:0',
            'width'      => 'required|integer|min:60|max:400',
            'height'     => 'required|integer|min:60|max:400',
        ]);

        $count     = Table::where('user_id', Auth::id())->count();
        $maxTables = Auth::user()->plan?->max_tables ?? 10;

        if ($count >= $maxTables) {
            return response()->json([
                'success' => false,
                'message' => "Has alcanzado el límite de {$maxTables} mesas de tu plan.",
            ], 422);
        }

        $table = Table::create([
            ...$data,
            'user_id'     => Auth::id(),
            'unique_hash' => Str::uuid()->toString(),
            'status'      => 'free',
        ]);

        return response()->json([
            'success' => true,
            'data'    => $table,
            'message' => "Mesa \"{$table->name}\" creada.",
        ], 201);
    }

    /**
     * Persiste la posición, dimensiones y rotación de una mesa tras moverla en el mapa.
     *
     * @param  Request  $request
     * @param  Table    $table
     * @return JsonResponse
     */
    public function updatePosition(Request $request, Table $table): JsonResponse
    {
        abort_if($table->user_id !== Auth::id(), 403, 'Acceso denegado.');

        $data = $request->validate([
            'position_x' => 'required|integer|min:0',
            'position_y' => 'required|integer|min:0',
            'width'      => 'required|integer|min:60|max:400',
            'height'     => 'required|integer|min:60|max:400',
            'rotation'   => 'sometimes|integer|min:0|max:359',
        ]);

        $table->update($data);

        return response()->json([
            'success' => true,
            'data'    => $table,
            'message' => 'Posición guardada.',
        ]);
    }

    /**
     * Actualiza el nombre de una mesa existente.
     *
     * @param  Request  $request
     * @param  Table    $table
     * @return JsonResponse
     */
    public function updateName(Request $request, Table $table): JsonResponse
    {
        abort_if($table->user_id !== Auth::id(), 403, 'Acceso denegado.');

        $data = $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $table->update($data);

        return response()->json([
            'success' => true,
            'data'    => $table,
            'message' => "Mesa renombrada a \"{$table->name}\".",
        ]);
    }

    /**
     * Actualiza la forma visual de una mesa existente en el mapa.
     *
     * @param  Request  $request
     * @param  Table    $table
     * @return JsonResponse
     */
    public function updateShape(Request $request, Table $table): JsonResponse
    {
        abort_if($table->user_id !== Auth::id(), 403, 'Acceso denegado.');

        $data = $request->validate([
            'shape' => 'required|in:square,round,rectangle',
        ]);

        $table->update($data);

        $label = match ($data['shape']) {
            'square'    => 'cuadrada',
            'round'     => 'redonda',
            'rectangle' => 'rectangular',
        };

        return response()->json([
            'success' => true,
            'data'    => $table,
            'message' => "Forma de \"{$table->name}\" cambiada a {$label}.",
        ]);
    }

    /**
     * Elimina una mesa del mapa y de la base de datos.
     *
     * @param  Table  $table
     * @return JsonResponse
     */
    public function destroy(Table $table): JsonResponse
    {
        abort_if($table->user_id !== Auth::id(), 403, 'Acceso denegado.');

        $name = $table->name;
        $table->delete();

        return response()->json([
            'success' => true,
            'message' => "Mesa \"{$name}\" eliminada.",
        ]);
    }

    /**
     * Devuelve el QR de una mesa como SVG inline para mostrarlo en el mapa.
     *
     * @param  Table  $table
     * @return Response
     */
    public function showQr(Table $table): Response
    {
        abort_if($table->user_id !== Auth::id(), 403, 'Acceso denegado.');

        $url = route('menu.show', $table->unique_hash);
        $svg = QrCode::format('svg')
            ->size(200)
            ->margin(1)
            ->generate($url);

        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }

    /**
     * Descarga el código QR de una mesa en formato SVG.
     *
     * @param  Table  $table
     * @return Response
     */
    public function downloadQr(Table $table): Response
    {
        abort_if($table->user_id !== Auth::id(), 403, 'Acceso denegado.');

        $url      = route('menu.show', $table->unique_hash);
        $svg      = QrCode::format('svg')
            ->size(300)
            ->margin(1)
            ->generate($url);

        $filename = 'qr-' . Str::slug($table->name) . '.svg';

        return response($svg, 200, [
            'Content-Type'        => 'image/svg+xml',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Regenera el unique_hash de una mesa, invalidando el QR anterior.
     *
     * @param  Table  $table
     * @return RedirectResponse
     */
    public function regenerateHash(Table $table): RedirectResponse
    {
        abort_if($table->user_id !== Auth::id(), 403, 'Acceso denegado.');

        $table->update(['unique_hash' => Str::uuid()->toString()]);

        return redirect()
            ->route('tables.index')
            ->with('success', "El QR de la mesa \"{$table->name}\" ha sido regenerado. El enlace anterior ya no es válido.");
    }
}
