<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
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
                'name'    => $u->business_name ?? $u->name,
                'lat'     => (float) $u->lat,
                'lng'     => (float) $u->lng,
                'plan'    => $u->plan?->name ?? 'Sin plan',
                'active'  => (bool) $u->active,
                'address' => $u->address ?? '',
            ]);

        $withoutCoords = User::where('role', 'admin')
            ->where(fn($q) =>
                $q->whereNull('lat')->orWhereNull('lng')
            )
            ->count();

        return view('superadmin.map', compact('businesses', 'withoutCoords'));
    }
}
