<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
 * @author BenjaminDTS
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
        $maxTables = Auth::user()->plan?->max_tables;

        return view('tables.index', compact('tables', 'maxTables'));
    }

    /**
     * Muestra el mapa visual interactivo con drag & drop para gestionar mesas y zonas.
     *
     * @return View
     */
    public function map(): View
    {
        $ownerId = Auth::user()->ownerUserId();
        $tables  = Table::where('user_id', $ownerId)
                       ->servicePoints()
                       ->whereNotIn('shape', ['bar', 'stool', 'chair', 'fireplace', 'pillar', 'column'])
                       ->with('activeOrder')
                       ->orderBy('name')
                       ->get()
                       ->each(function (Table $t): void {
                           $t->append('orderStatus');
                       });
        $elements    = Table::where('user_id', $ownerId)
                           ->where(function ($q) {
                               $q->where('is_service_point', false)
                                 ->orWhereIn('shape', ['bar', 'stool', 'chair', 'fireplace', 'pillar', 'column']);
                           })
                           ->orderBy('name')
                           ->get();
        $zones       = Zone::where('user_id', $ownerId)->orderBy('name')->get();
        $owner         = Auth::user()->isAdmin() ? Auth::user() : Auth::user()->admin;
        $maxTables     = $owner?->plan?->max_tables;
        $maxFloors     = $owner?->plan?->max_floors;
        $floorWidth    = $owner?->floor_width    ?? 1200;
        $floorHeight   = $owner?->floor_height   ?? 800;
        $floorCount    = $owner?->floor_count    ?? 1;
        $floorsEnabled = $owner?->floors_enabled ?? false;
        $readonly      = ! Auth::user()->isAdmin();

        $savedSizes       = $owner?->floor_canvas_sizes ?? [];
        $floorCanvasSizes = [];
        for ($f = 1; $f <= $floorCount; $f++) {
            $floorCanvasSizes[$f] = [
                'width'  => $savedSizes[$f]['width']  ?? $floorWidth,
                'height' => $savedSizes[$f]['height'] ?? $floorHeight,
            ];
        }

        return view('tables.map', compact(
            'tables', 'elements', 'zones', 'maxTables', 'maxFloors',
            'floorWidth', 'floorHeight', 'floorCount', 'floorsEnabled',
            'floorCanvasSizes', 'readonly'
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
        abort_if(! Auth::user()->isAdmin() && ! Auth::user()->canAccessBar(), 403, 'Acceso denegado.');

        $ownerId  = Auth::user()->ownerUserId();
        $statuses = Table::where('user_id', $ownerId)
            ->servicePoints()
            ->with('activeOrder')
            ->get(['tables.id', 'tables.status'])
            ->map(fn (Table $t) => [
                'id'          => $t->id,
                'orderStatus' => $t->orderStatus,
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
            'floor'        => 'sometimes|integer|min:1|max:5',
        ]);

        $user  = Auth::user();
        $floor = $data['floor'] ?? 1;

        $sizes          = $user->floor_canvas_sizes ?? [];
        $sizes[$floor]  = ['width' => $data['floor_width'], 'height' => $data['floor_height']];

        $updatePayload = ['floor_canvas_sizes' => $sizes];

        if ($floor === 1) {
            $updatePayload['floor_width']  = $data['floor_width'];
            $updatePayload['floor_height'] = $data['floor_height'];
        }

        $user->update($updatePayload);

        return response()->json([
            'success' => true,
            'message' => 'Dimensiones del plano guardadas.',
        ]);
    }

    /**
     * Actualiza la configuración de plantas del restaurante (número de plantas y toggle).
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function updateFloorSettings(Request $request): JsonResponse
    {
        $plan          = Auth::user()->plan;
        $floorCountMax = $plan?->max_floors;

        // max:null would be an invalid rule; use a hard cap of 99 only as a UI safety net for unlimited plans.
        $floorValidation = $floorCountMax !== null
            ? "sometimes|integer|min:1|max:{$floorCountMax}"
            : 'sometimes|integer|min:1|max:99';

        $data = $request->validate([
            'floor_count'    => $floorValidation,
            'floors_enabled' => 'sometimes|boolean',
        ]);

        if (isset($data['floor_count'])) {
            $newCount = (int) $data['floor_count'];

            // isLimitReached uses current >= limit. newCount is the desired final count,
            // so pass newCount - 1 to represent existing floors before adding the new one.
            if ($plan && $plan->isLimitReached('floors', $newCount - 1)) {
                return response()->json([
                    'success' => false,
                    'message' => "Tu plan {$plan->name} permite un máximo de {$plan->max_floors} planta(s). Actualiza al plan Premium para tener plantas ilimitadas.",
                ], 422);
            }
        }

        Auth::user()->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Configuración de plantas guardada.',
        ]);
    }

    /**
     * Elimina la última planta y todas las estructuras que contiene.
     *
     * @param  int  $floor
     * @return JsonResponse
     */
    public function destroyFloor(int $floor): JsonResponse
    {
        $user = Auth::user();

        if ($floor < 2 || $floor > ($user->floor_count ?? 1)) {
            return response()->json([
                'success' => false,
                'message' => 'Planta no válida.',
            ], 422);
        }

        $ownerId = $user->ownerUserId();

        Table::where('user_id', $ownerId)->where('floor', $floor)->delete();
        Zone::where('user_id', $ownerId)->where('floor', $floor)->delete();

        $newCount = $floor - 1;
        $sizes    = $user->floor_canvas_sizes ?? [];
        unset($sizes[$floor]);
        $user->update([
            'floor_count'        => $newCount,
            'floor_canvas_sizes' => $sizes,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Planta {$floor} eliminada.",
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
            'shape'            => 'required|in:square,round,rectangle,bar,stool,chair,fireplace,pillar,column',
            'position_x'       => 'required|integer|min:0',
            'position_y'       => 'required|integer|min:0',
            'width'            => 'required|integer|min:20|max:800',
            'height'           => 'required|integer|min:20|max:800',
            'rotation'         => 'sometimes|numeric|min:0|max:360',
            'is_service_point' => 'sometimes|boolean',
            'floor'            => 'sometimes|integer|min:1|max:5',
            'zone_id'          => ['sometimes', 'nullable', Rule::exists('zones', 'id')->where('user_id', Auth::id())],
        ]);

        $specialShapes = ['bar', 'stool', 'chair', 'fireplace', 'pillar', 'column'];
        $isServicePoint = in_array($data['shape'], $specialShapes)
            ? false
            : ($data['is_service_point'] ?? true);

        $specialNames = [
            'bar'       => 'Barra',
            'stool'     => 'Taburete',
            'chair'     => 'Silla',
            'fireplace' => 'Chimenea',
            'pillar'    => 'Pilar',
            'column'    => 'Columna',
        ];

        if (in_array($data['shape'], $specialShapes)) {
            $data['name'] = $data['name'] ?? $specialNames[$data['shape']];
        } elseif (empty($data['name'])) {
            $usedNumbers = Table::where('user_id', Auth::id())
                ->servicePoints()
                ->pluck('name')
                ->filter(fn ($n) => ctype_digit((string) $n) && (int) $n > 0)
                ->map(fn ($n) => (int) $n)
                ->sort()
                ->values();

            $next = 1;
            foreach ($usedNumbers as $num) {
                if ($num > $next) {
                    break;
                }
                if ($num === $next) {
                    $next++;
                }
            }

            $data['name'] = (string) $next;
        }

        if ($isServicePoint) {
            $count = Table::where('user_id', Auth::id())->servicePoints()->count();
            $plan  = Auth::user()->plan;

            $limitHit = $plan !== null
                ? $plan->isLimitReached('tables', $count)
                : $count >= 10;

            if ($limitHit) {
                $limit = $plan?->max_tables ?? 10;
                return response()->json([
                    'success' => false,
                    'message' => "Has alcanzado el límite de {$limit} mesas de tu plan {$plan?->name}.",
                ], 422);
            }
        }

        $table = Table::create([
            ...$data,
            'user_id'          => Auth::id(),
            'unique_hash'      => Str::uuid()->toString(),
            'status'           => 'free',
            'is_service_point' => $isServicePoint,
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
        abort_if($table->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

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
        abort_if($table->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

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
        abort_if($table->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

        $data = $request->validate([
            'zone_id' => ['nullable', Rule::exists('zones', 'id')->where('user_id', Auth::id())],
        ]);

        $table->update($data);

        if ($data['zone_id']) {
            $zoneName = Zone::find($data['zone_id'])?->name ?? 'desconocida';
            $message  = "Zona \"{$zoneName}\" asignada a \"{$table->name}\".";
        } else {
            $message = "Zona eliminada de \"{$table->name}\".";
        }

        return response()->json([
            'success' => true,
            'data'    => $table,
            'message' => $message,
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
        abort_if($table->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

        $data = $request->validate([
            'shape' => 'required|in:square,round,rectangle,bar,stool,chair,fireplace,pillar,column',
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
     * Persiste los vértices poligonales de un elemento de tipo barra.
     * Solo aplicable a elementos con shape='bar'; ignorado para mesas normales.
     *
     * @param  Request  $request
     * @param  Table    $table
     * @return JsonResponse
     */
    public function updateVertices(Request $request, Table $table): JsonResponse
    {
        abort_if($table->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

        $data = $request->validate([
            'vertices'      => 'nullable|array|min:3',
            'vertices.*.x'  => 'required_with:vertices|numeric',
            'vertices.*.y'  => 'required_with:vertices|numeric',
        ]);

        $table->update(['vertices' => $data['vertices'] ?? null]);

        return response()->json([
            'success' => true,
            'data'    => $table,
            'message' => 'Vértices del elemento guardados.',
        ]);
    }

    /**
     * Mueve una mesa o elemento a una planta diferente.
     *
     * @param  Request  $request
     * @param  Table    $table
     * @return JsonResponse
     */
    public function updateFloor(Request $request, Table $table): JsonResponse
    {
        abort_if($table->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

        $data = $request->validate([
            'floor' => 'required|integer|min:1|max:5',
        ]);

        $table->update($data);

        $label = match ($table->shape) {
            'bar'   => 'Barra',
            'stool' => 'Taburete',
            default => "Mesa \"{$table->name}\"",
        };

        return response()->json([
            'success' => true,
            'data'    => $table,
            'message' => "{$label} movida a Planta {$table->floor}.",
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
        abort_if($table->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

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
        abort_if($table->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

        $svg = Cache::rememberForever(
            "qr_svg_{$table->unique_hash}",
            fn () => (string) QrCode::format('svg')
                ->size(200)
                ->margin(1)
                ->generate(route('menu.show', $table->unique_hash))
        );

        return response($svg, 200, [
            'Content-Type'  => 'image/svg+xml',
            'Cache-Control' => 'private, max-age=31536000, immutable',
        ]);
    }

    /**
     * Descarga el código QR de una mesa en formato SVG.
     *
     * @param  Table  $table
     * @return Response
     */
    public function downloadQr(Table $table): Response
    {
        abort_if($table->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

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
        abort_if($table->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

        Cache::forget("qr_svg_{$table->unique_hash}");
        $table->update(['unique_hash' => Str::uuid()->toString()]);

        return redirect()
            ->route('tables.index')
            ->with('success', "El QR de la mesa \"{$table->name}\" ha sido regenerado. El enlace anterior ya no es válido.");
    }
}
