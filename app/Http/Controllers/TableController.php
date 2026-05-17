<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
 * @author SebastianBCF
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
        $tables    = Table::where('user_id', Auth::id())->servicePoints()->orderBy('name')->get();
        $maxTables = Auth::user()->plan?->max_tables ?? 10;

        return view('tables.index', compact('tables', 'maxTables'));
    }

    /**
     * Muestra el mapa visual interactivo con drag & drop para gestionar mesas y zonas.
     *
     * @return View
     */
    public function map(): View
    {
        $ownerId     = Auth::user()->ownerUserId();
        $tables      = Table::where('user_id', $ownerId)
                           ->servicePoints()
                           ->whereNotIn('shape', ['bar', 'stool'])
                           ->orderBy('name')
                           ->get();
        $elements    = Table::where('user_id', $ownerId)
                           ->where(function ($q) {
                               $q->where('is_service_point', false)
                                 ->orWhereIn('shape', ['bar', 'stool']);
                           })
                           ->orderBy('name')
                           ->get();
        $zones       = Zone::where('user_id', $ownerId)->orderBy('name')->get();
        $owner         = Auth::user()->isAdmin() ? Auth::user() : Auth::user()->admin;
        $maxTables     = $owner?->plan?->max_tables ?? 10;
        $floorWidth    = $owner?->floor_width    ?? 1200;
        $floorHeight   = $owner?->floor_height   ?? 800;
        $floorCount    = $owner?->floor_count    ?? 1;
        $floorsEnabled = $owner?->floors_enabled ?? false;
        $readonly      = ! Auth::user()->isAdmin();

        return view('tables.map', compact(
            'tables', 'elements', 'zones', 'maxTables',
            'floorWidth', 'floorHeight', 'floorCount', 'floorsEnabled',
            'readonly'
        ));
    }

    /**
     * Devuelve el estado actual (ocupada/libre y solicitud de cuenta) de todas las mesas.
     * Consumido por el polling del mapa en tiempo real.
     *
     * @return JsonResponse
     */
    public function mapStatuses(): JsonResponse
    {
        abort_if(! Auth::user()->canAccessBar(), 403, 'Acceso denegado.');

        $ownerId  = Auth::user()->ownerUserId();
        $statuses = Table::where('user_id', $ownerId)
            ->servicePoints()
            ->with(['activeOrder:id,table_id,bill_requested,requested_payment_method'])
            ->get(['id', 'status'])
            ->map(fn (Table $t) => [
                'id'                       => $t->id,
                'status'                   => $t->status,
                'bill_requested'           => (bool) ($t->activeOrder?->bill_requested ?? false),
                'requested_payment_method' => $t->activeOrder?->requested_payment_method,
            ]);

        return response()->json($statuses);
    }

    /**
     * Guarda las dimensiones personalizadas del canvas del plano.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function updateCanvas(Request $request): JsonResponse
    {
        $data = $request->validate([
            'floor_width'  => 'required|integer|min:800|max:3000',
            'floor_height' => 'required|integer|min:600|max:2000',
        ]);

        Auth::user()->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Dimensiones del plano guardadas.',
        ]);
    }

    /**
     * Actualiza la configuración del sistema de plantas del mapa.
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function updateFloorSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'floors_enabled' => 'sometimes|boolean',
            'floor_count'    => 'sometimes|integer|min:1|max:5',
        ]);

        if (isset($data['floors_enabled']) && ! $data['floors_enabled']) {
            $hasStructures = Table::where('user_id', Auth::id())->where('floor', '>', 1)->exists()
                          || Zone::where('user_id', Auth::id())->where('floor', '>', 1)->exists();

            if ($hasStructures) {
                return response()->json([
                    'success' => false,
                    'message' => 'Elimina todas las estructuras de las plantas superiores antes de desactivar el sistema.',
                ], 422);
            }
        }

        Auth::user()->update($data);

        $user = Auth::user()->fresh();

        return response()->json([
            'success' => true,
            'message' => 'Configuración de plantas guardada.',
            'data'    => [
                'floors_enabled' => $user->floors_enabled,
                'floor_count'    => $user->floor_count,
            ],
        ]);
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
            'name'             => 'nullable|string|max:50',
            'shape'            => 'required|in:square,round,rectangle,bar,stool',
            'position_x'       => 'required|integer|min:0',
            'position_y'       => 'required|integer|min:0',
            'width'            => 'required|integer|min:40|max:800',
            'height'           => 'required|integer|min:40|max:400',
            'is_service_point' => 'sometimes|boolean',
            'zone_id'          => ['sometimes', 'nullable', Rule::exists('zones', 'id')->where('user_id', Auth::id())],
            'floor'            => 'sometimes|integer|min:1|max:5',
        ]);

        $isServicePoint = in_array($data['shape'], ['bar', 'stool'])
            ? false
            : ($data['is_service_point'] ?? true);

        if (in_array($data['shape'], ['bar', 'stool'])) {
            $data['name'] = $data['shape'] === 'bar' ? 'Barra' : 'Taburete';
        } elseif (empty($data['name'])) {
            $user = Auth::user();
            $user->increment('next_table_number');
            $data['name'] = (string) $user->next_table_number;
        }

        if ($isServicePoint) {
            $count     = Table::where('user_id', Auth::id())->servicePoints()->count();
            $maxTables = Auth::user()->plan?->max_tables ?? 10;

            if ($count >= $maxTables) {
                return response()->json([
                    'success' => false,
                    'message' => "Has alcanzado el límite de {$maxTables} mesas de tu plan.",
                ], 422);
            }
        }

        $table = Table::create([
            ...$data,
            'user_id'          => Auth::id(),
            'unique_hash'      => Str::uuid()->toString(),
            'status'           => 'free',
            'is_service_point' => $isServicePoint,
            'floor'            => $data['floor'] ?? 1,
        ]);

        $message = match ($table->shape) {
            'bar'   => 'Barra colocada en el plano.',
            'stool' => 'Taburete colocado en el plano.',
            default => "Mesa \"{$table->name}\" creada.",
        };

        return response()->json([
            'success' => true,
            'data'    => $table,
            'message' => $message,
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
            'position_x' => 'required|integer|min:-3000',
            'position_y' => 'required|integer|min:-3000',
            'width'      => 'required|integer|min:40|max:800',
            'height'     => 'required|integer|min:40|max:800',
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
     * Asigna o desvincula la zona de una mesa.
     *
     * @param  Request  $request
     * @param  Table    $table
     * @return JsonResponse
     */
    public function updateZone(Request $request, Table $table): JsonResponse
    {
        abort_if($table->user_id !== Auth::id(), 403, 'Acceso denegado.');

        $data = $request->validate([
            'zone_id' => ['nullable', Rule::exists('zones', 'id')->where('user_id', Auth::id())],
        ]);

        $table->update($data);

        $message = $data['zone_id']
            ? "Zona asignada a \"{$table->name}\"."
            : "Zona eliminada de \"{$table->name}\".";

        return response()->json([
            'success' => true,
            'data'    => $table,
            'message' => $message,
        ]);
    }

    /**
     * Mueve una mesa o elemento a una planta diferente.
     *
     * @param  Request $request
     * @param  Table   $table
     * @return JsonResponse
     */
    public function updateFloor(Request $request, Table $table): JsonResponse
    {
        abort_if($table->user_id !== Auth::id(), 403, 'Acceso denegado.');

        $data = $request->validate([
            'floor' => 'required|integer|min:1|max:5',
        ]);

        $floorCount = Auth::user()->floor_count ?? 1;

        if ($data['floor'] > $floorCount) {
            return response()->json([
                'success' => false,
                'message' => 'Planta inexistente.',
            ], 422);
        }

        $table->update(['floor' => $data['floor']]);

        return response()->json([
            'success' => true,
            'data'    => $table,
            'message' => "Mesa movida a la Planta {$data['floor']}.",
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
            'shape' => 'required|in:square,round,rectangle,bar,stool',
        ]);

        $table->update($data);

        $label = match ($data['shape']) {
            'square'    => 'cuadrada',
            'round'     => 'redonda',
            'rectangle' => 'rectangular',
            'bar'       => 'barra',
            'stool'     => 'taburete',
        };

        return response()->json([
            'success' => true,
            'data'    => $table,
            'message' => "Forma de \"{$table->name}\" cambiada a {$label}.",
        ]);
    }

    /**
     * Elimina todas las mesas y elementos de una planta específica
     * y reduce floor_count en 1. Solo se puede eliminar la última planta.
     *
     * @param  int $floor
     * @return JsonResponse
     */
    public function destroyFloor(int $floor): JsonResponse
    {
        $user       = Auth::user();
        $floorCount = $user->floor_count ?? 1;

        if ($floor <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'La planta 1 no puede eliminarse.',
            ], 422);
        }

        if ($floor !== $floorCount) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede eliminar la última planta.',
            ], 422);
        }

        Table::where('user_id', Auth::id())->where('floor', $floor)->delete();
        Zone::where('user_id', Auth::id())->where('floor', $floor)->delete();

        $user->update(['floor_count' => $floorCount - 1]);

        return response()->json([
            'success' => true,
            'message' => "Planta {$floor} eliminada.",
            'data'    => ['floor_count' => $floorCount - 1],
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

        $name  = $table->name;
        $shape = $table->shape;
        $table->delete();

        $message = match ($shape) {
            'bar'   => 'Barra eliminada del plano.',
            'stool' => 'Taburete eliminado del plano.',
            default => "Mesa \"{$name}\" eliminada.",
        };

        return response()->json([
            'success' => true,
            'message' => $message,
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
