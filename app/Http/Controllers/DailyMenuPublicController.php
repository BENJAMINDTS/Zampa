<?php

namespace App\Http\Controllers;

use App\Models\DailyMenu;
use App\Models\DailyMenuOrder;
use App\Models\DailyMenuOrderSelection;
use App\Models\Order;
use App\Models\Table;
use App\Services\DailyMenuOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Endpoints públicos del menú del día.
 *
 * Accesibles sin autenticación desde la carta digital.
 * El multitenancy se garantiza a través de table_hash → table → user_id.
 *
 * @author Ayrtonalania
 */
class DailyMenuPublicController extends Controller
{
    /**
     * Devuelve el menú del día activo para hoy para la mesa indicada.
     * Devuelve 200 con data:null si no hay menú activo (el frontend no muestra el banner).
     *
     * @param  string  $hash
     * @return JsonResponse
     */
    public function show(string $hash): JsonResponse
    {
        $table = Table::where('unique_hash', $hash)->first();

        if (! $table) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Mesa no encontrada.',
            ], 404);
        }

        $menu = DailyMenu::forToday($table->user_id);

        if (! $menu) {
            return response()->json(['success' => true, 'data' => null], 200);
        }

        $menu->load([
            'sections'     => fn ($q) => $q->orderBy('display_order'),
            'sections.products',
            'timingRules'  => fn ($q) => $q->orderBy('round_number'),
        ]);

        $availableCount = null;
        if ($menu->max_per_day !== null) {
            $sold = DailyMenuOrder::where('daily_menu_id', $menu->id)
                ->whereDate('created_at', today())
                ->where('status', '!=', 'cancelled')
                ->count();
            $availableCount = max(0, $menu->max_per_day - $sold);
        }

        $sections = $menu->sections->map(function ($section) use ($menu) {
            $timingRule = $menu->timingRules->first(
                fn ($rule) => in_array($section->type, $rule->section_types ?? [])
            );

            return [
                'id'            => $section->id,
                'type'          => $section->type,
                'is_required'   => $section->is_required,
                'is_free'       => $section->is_free,
                'max_quantity'  => $section->max_quantity,
                'display_order' => $section->display_order,
                'products'      => $section->products->map(fn ($p) => [
                    'id'          => $p->id,
                    'name'        => $p->name,
                    'description' => $p->description,
                    'price'       => $p->price,
                    'image'       => $p->image,
                    'is_active'   => (bool) $p->is_active,
                ])->values(),
                'timing_rule'   => $timingRule ? [
                    'round_number'           => $timingRule->round_number,
                    'default_delay_minutes'  => $timingRule->default_delay_minutes,
                    'estimated_prep_minutes' => $timingRule->estimated_prep_minutes,
                ] : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'menu'     => [
                    'id'              => $menu->id,
                    'title'           => $menu->title,
                    'description'     => $menu->description,
                    'price'           => $menu->price,
                    'max_per_day'     => $menu->max_per_day,
                    'available_count' => $availableCount,
                ],
                'sections' => $sections,
            ],
        ]);
    }

    /**
     * Crea el pedido de menú del día con las selecciones y preferencias de timing del cliente.
     *
     * @param  Request  $request
     * @param  string   $hash
     * @return JsonResponse
     */
    public function order(Request $request, string $hash): JsonResponse
    {
        // PASO 1 — Resolver la mesa
        $table = Table::where('unique_hash', $hash)->first();

        if (! $table) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Mesa no encontrada.',
            ], 404);
        }

        // PASO 2 — Verificar exclusividad con pedidos à la carte
        $hasAlaCarteOrders = Order::where('table_id', $table->id)
            ->whereIn('status', ['pending', 'cooking'])
            ->whereDoesntHave('dailyMenuOrder')
            ->exists();

        if ($hasAlaCarteOrders) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Ya tienes productos à la carte en el carrito. Vacíalo para pedir el Menú del Día.',
            ], 409);
        }

        // PASO 3 — Resolver el menú de hoy
        $menu = DailyMenu::forToday($table->user_id);

        if (! $menu) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'No hay menú del día disponible.',
            ], 404);
        }

        // PASO 4 — Validación de input
        $request->validate([
            'selections'                       => 'required|array|min:1',
            'selections.*.section_id'          => 'required|integer',
            'selections.*.product_id'          => 'required|integer',
            'selections.*.quantity'            => 'required|integer|min:1|max:10',
            'timing_overrides'                 => 'nullable|array',
            'timing_overrides.*.round'         => 'required|integer|min:1',
            'timing_overrides.*.delay_minutes' => 'required|integer|min:0',
        ]);

        $menu->load([
            'sections.products',
            'timingRules' => fn ($q) => $q->orderBy('round_number'),
        ]);

        // PASO 5 — Validaciones de negocio
        $validSectionIds = $menu->sections->pluck('id')->toArray();

        // 5a — Todos los section_id pertenecen al menú
        foreach ($request->selections as $sel) {
            if (! in_array($sel['section_id'], $validSectionIds)) {
                return response()->json([
                    'success' => false,
                    'data'    => null,
                    'message' => 'La sección ' . $sel['section_id'] . ' no pertenece al menú del día.',
                ], 422);
            }
        }

        // 5b — Todos los product_id están disponibles en su sección
        $sectionProductMap = $menu->sections->keyBy('id')->map(
            fn ($section) => $section->products->pluck('id')->toArray()
        );

        foreach ($request->selections as $sel) {
            $allowedProducts = $sectionProductMap->get($sel['section_id'], []);
            if (! in_array($sel['product_id'], $allowedProducts)) {
                return response()->json([
                    'success' => false,
                    'data'    => null,
                    'message' => 'El producto ' . $sel['product_id'] . ' no está disponible en esa sección.',
                ], 422);
            }
        }

        // 5c — Todas las secciones obligatorias tienen selección
        $selectedSectionIds = collect($request->selections)->pluck('section_id')->toArray();

        foreach ($menu->sections->where('is_required', true) as $requiredSection) {
            if (! in_array($requiredSection->id, $selectedSectionIds)) {
                return response()->json([
                    'success' => false,
                    'data'    => null,
                    'message' => 'Debes elegir un plato para la sección ' . $requiredSection->type . '.',
                ], 422);
            }
        }

        // 5e — Timing overrides respetan el tiempo mínimo de preparación
        $timingOverrides = $request->timing_overrides ?? [];
        foreach ($timingOverrides as $override) {
            $rule = $menu->timingRules->firstWhere('round_number', $override['round']);
            if ($rule && $override['delay_minutes'] < $rule->estimated_prep_minutes) {
                return response()->json([
                    'success' => false,
                    'data'    => null,
                    'message' => 'El tiempo solicitado para la ronda ' . $override['round']
                        . ' es inferior al tiempo de preparación mínimo de '
                        . $rule->estimated_prep_minutes . ' minutos.',
                ], 422);
            }
        }

        // PASO 5d + 6 — Verificar disponibilidad y crear el pedido en una sola transacción
        $order          = null;
        $dailyMenuOrder = null;
        $soldOut        = false;

        DB::transaction(function () use ($request, $table, $menu, $timingOverrides, &$order, &$dailyMenuOrder, &$soldOut) {
            // 5d — Verificar disponibilidad con lock para evitar race conditions
            if ($menu->max_per_day !== null) {
                $count = DailyMenuOrder::where('daily_menu_id', $menu->id)
                    ->whereDate('created_at', today())
                    ->where('status', '!=', 'cancelled')
                    ->lockForUpdate()
                    ->count();

                if ($count >= $menu->max_per_day) {
                    $soldOut = true;
                    return;
                }
            }

            // PASO 6a — Crear Order
            $order = Order::create([
                'table_id' => $table->id,
                'status'   => 'pending',
                'total'    => $menu->price,
            ]);

            // PASO 6b — Crear DailyMenuOrder
            $dailyMenuOrder = DailyMenuOrder::create([
                'order_id'                => $order->id,
                'daily_menu_id'           => $menu->id,
                'rounds_total'            => $menu->timingRules->count(),
                'current_round'           => 0,
                'client_timing_overrides' => $timingOverrides,
                'status'                  => 'pending',
            ]);

            // PASO 6c — Crear DailyMenuOrderSelections
            foreach ($request->selections as $sel) {
                DailyMenuOrderSelection::create([
                    'daily_menu_order_id'   => $dailyMenuOrder->id,
                    'daily_menu_section_id' => $sel['section_id'],
                    'product_id'            => $sel['product_id'],
                    'quantity'              => $sel['quantity'],
                ]);
            }

            // PASO 6d — Despachar rondas
            DailyMenuOrderService::dispatchRounds($dailyMenuOrder, $menu->timingRules, $timingOverrides);
        });

        if ($soldOut) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'El Menú del Día está agotado por hoy.',
            ], 409);
        }

        // PASO 7 — Calcular estimated_times para la respuesta
        $estimatedTimes = $menu->timingRules->map(function ($rule) use ($timingOverrides) {
            $override    = collect($timingOverrides)->firstWhere('round', $rule->round_number);
            $clientDelay = $override['delay_minutes'] ?? $rule->default_delay_minutes;
            $arrivesAt   = now()->addMinutes($clientDelay)->format('H:i');

            return [
                'round'     => $rule->round_number,
                'sections'  => $rule->section_types,
                'arrives_at'=> $arrivesAt,
            ];
        })->values();

        // PASO 8 — Respuesta 201
        return response()->json([
            'success' => true,
            'data'    => [
                'order_id'        => $order->id,
                'estimated_times' => $estimatedTimes,
            ],
        ], 201);
    }
}
