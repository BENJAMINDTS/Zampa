<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use Illuminate\View\View;

/**
 * Mapa interactivo de negocios registrados (Bloque 13.5).
 *
 * @author BenjaminDTS
 * @author SebastianBCF
 */
class SuperAdminMapController extends Controller
{
    /** Color hex para negocios sin plan contratado o plan no catalogado. */
    private const COLOR_NO_PLAN = '#6b7280';

    /** Colores hex por nombre exacto de plan. */
    private const PLAN_COLORS = [
        'Plan Básico'   => '#3b82f6',
        'Plan Premium'  => '#8b5cf6',
        'Plan Business' => '#f59e0b',
    ];

    /**
     * Muestra el mapa interactivo de negocios registrados.
     * Solo incluye admins con coordenadas válidas.
     * Filtra estrictamente por role = admin para evitar
     * mostrar staff huérfano (admin_id = null pero no admin).
     *
     * @return View
     */
    public function index(): View
    {
        $businesses = User::where('role', 'admin')
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->with('plan:id,name')
            ->get()
            ->map(fn($u) => [
                'name'       => $u->business_name ?? $u->name,
                'lat'        => (float) $u->lat,
                'lng'        => (float) $u->lng,
                'plan'       => $u->plan?->name ?? 'Sin plan',
                'plan_color' => $this->planColor($u->plan?->name),
                'active'     => (bool) $u->active,
                'address'    => $u->address ?? '',
            ]);

        $withoutCoords = User::where('role', 'admin')
            ->where(fn($q) =>
                $q->whereNull('lat')->orWhereNull('lng')
            )
            ->count();

        $planLegend = Plan::select('id', 'name')->get()
            ->filter(fn($p) => array_key_exists($p->name, self::PLAN_COLORS))
            ->map(fn($p) => [
                'label' => $p->name,
                'color' => $this->planColor($p->name),
            ])
            ->values();

        return view('superadmin.map', compact('businesses', 'withoutCoords', 'planLegend'))
            ->with('colorNoPlan', self::COLOR_NO_PLAN);
    }

    /**
     * Devuelve el color hex asociado al nombre del plan.
     * Usa gris neutro para planes no catalogados o negocios sin plan.
     *
     * @param string|null $planName
     * @return string
     */
    private function planColor(?string $planName): string
    {
        return self::PLAN_COLORS[$planName] ?? self::COLOR_NO_PLAN;
    }
}
