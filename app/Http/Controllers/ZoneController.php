<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class ZoneController
 *
 * Gestión de zonas del plano del restaurante (terraza, comedor, barra).
 * Solo accesible para el gerente (admin). Todas las operaciones son AJAX/JSON.
 *
 * @author AyrtonAlania
 * @author BenjaminDTS
 * @author SebastianBCF
 */
class ZoneController extends Controller
{
    /**
     * Crea una nueva zona en el plano del restaurante.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:50',
            'color'         => ['required', 'string', 'regex:/^#[0-9a-fA-F]{3,6}$/'],
            'position_x'    => 'required|integer|min:0',
            'position_y'    => 'required|integer|min:0',
            'width'         => 'required|integer|min:80|max:2000',
            'height'        => 'required|integer|min:60|max:1500',
            'floor'         => 'sometimes|integer|min:1|max:5',
            'vertices'      => 'sometimes|nullable|array|min:3',
            'vertices.*.x'  => 'required_with:vertices|numeric',
            'vertices.*.y'  => 'required_with:vertices|numeric',
        ]);

        $zone = Zone::create([
            ...$data,
            'user_id'  => Auth::id(),
            'rotation' => 0,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $zone,
            'message' => "Zona \"{$zone->name}\" creada.",
        ], 201);
    }

    /**
     * Actualiza posición, dimensiones, nombre y color de una zona.
     *
     * @param  Request  $request
     * @param  Zone     $zone
     * @return JsonResponse
     */
    public function update(Request $request, Zone $zone): JsonResponse
    {
        abort_if($zone->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

        $data = $request->validate([
            'name'          => 'sometimes|string|max:50',
            'color'         => ['sometimes', 'string', 'regex:/^#[0-9a-fA-F]{3,6}$/'],
            'position_x'    => 'sometimes|integer|min:0',
            'position_y'    => 'sometimes|integer|min:0',
            'width'         => 'sometimes|integer|min:80|max:2000',
            'height'        => 'sometimes|integer|min:60|max:1500',
            'rotation'      => 'sometimes|integer|min:0|max:359',
            'floor'         => 'sometimes|integer|min:1|max:5',
            'vertices'      => 'sometimes|nullable|array|min:3',
            'vertices.*.x'  => 'required_with:vertices|numeric',
            'vertices.*.y'  => 'required_with:vertices|numeric',
        ]);

        $zone->update($data);

        return response()->json([
            'success' => true,
            'data'    => $zone,
            'message' => "Zona \"{$zone->name}\" actualizada.",
        ]);
    }

    /**
     * Elimina una zona. Las mesas asignadas pasan a zone_id = null (nullOnDelete).
     *
     * @param  Zone  $zone
     * @return JsonResponse
     */
    public function destroy(Zone $zone): JsonResponse
    {
        abort_if($zone->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

        $name = $zone->name;
        $zone->delete();

        return response()->json([
            'success' => true,
            'message' => "Zona \"{$name}\" eliminada.",
        ]);
    }
}
