<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Gestión de negocios registrados en la plataforma Zampa (panel superadmin).
 *
 * Permite crear cuentas de administrador, asignar planes, activar/desactivar
 * negocios y eliminarlos. Solo accesible para el rol superadmin.
 *
 * @author BenjaminDTS
 * @author SebastianBCF
 */
class SuperAdminBusinessController extends Controller
{
    /**
     * Lista todos los negocios (usuarios con rol admin) con sus stats.
     *
     * @return View
     */
    public function index(): View
    {
        $businesses = User::where('role', 'admin')
            ->with('plan')
            ->withCount(['tables', 'orders'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('superadmin.businesses.index', compact('businesses'));
    }

    /**
     * Muestra el formulario para crear un nuevo negocio.
     *
     * @return View
     */
    public function create(): View
    {
        $plans = Plan::orderBy('price')->get();

        return view('superadmin.businesses.create', compact('plans'));
    }

    /**
     * Almacena un nuevo negocio (usuario con rol admin) en la base de datos.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'business_name'         => ['required', 'string', 'max:255'],
            'address'               => ['required', 'string', 'max:500'],
            'lat'                   => ['nullable', 'numeric', 'between:-90,90'],
            'lng'                   => ['nullable', 'numeric', 'between:-180,180'],
            'plan_id'               => ['required', 'exists:plans,id'],
        ]);

        User::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
            'role'          => 'admin',
            'active'        => true,
            'plan_id'       => $validated['plan_id'],
            'business_name' => $validated['business_name'],
            'address'       => $validated['address'],
            'lat'           => $validated['lat'] ?? null,
            'lng'           => $validated['lng'] ?? null,
        ]);

        return redirect()->route('superadmin.businesses.index')
            ->with('success', 'Negocio creado correctamente.');
    }

    /**
     * Muestra el formulario de edición de un negocio existente.
     *
     * @param  User  $business
     * @return View
     */
    public function edit(User $business): View
    {
        abort_if($business->role !== 'admin', 403, 'Acceso denegado.');

        $plans = Plan::orderBy('price')->get();

        return view('superadmin.businesses.edit', compact('business', 'plans'));
    }

    /**
     * Actualiza los datos de un negocio existente.
     *
     * No permite cambiar la contraseña desde esta vista.
     *
     * @param  Request  $request
     * @param  User     $business
     * @return RedirectResponse
     */
    public function update(Request $request, User $business): RedirectResponse
    {
        abort_if($business->role !== 'admin', 403, 'Acceso denegado.');

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($business->id)],
            'plan_id'       => ['required', 'exists:plans,id'],
            'address'       => ['nullable', 'string', 'max:500'],
            'lat'           => ['nullable', 'numeric', 'between:-90,90'],
            'lng'           => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $business->update($validated);

        return redirect()->route('superadmin.businesses.index')
            ->with('success', "Negocio «{$business->business_name}» actualizado correctamente.");
    }

    /**
     * Activa o desactiva un negocio alternando el campo active.
     *
     * @param  User  $business
     * @return RedirectResponse
     */
    public function toggle(User $business): RedirectResponse
    {
        abort_if($business->role !== 'admin', 403, 'Acceso denegado.');

        $business->update(['active' => ! $business->active]);

        $estado  = $business->active ? 'activado' : 'desactivado';
        $mensaje = "El negocio «{$business->business_name}» ha sido {$estado}.";

        return redirect()->route('superadmin.businesses.index')
            ->with('success', $mensaje);
    }

    /**
     * Elimina lógicamente un negocio (soft delete) y desactiva su staff.
     *
     * El admin queda marcado con deleted_at. Su staff se desactiva (active = false)
     * para que el middleware EnsureBusinessIsActive les bloquee el acceso.
     * Los datos históricos (mesas, pedidos, menú) se conservan en la BD.
     * La operación es atómica: si algo falla se hace rollback completo.
     *
     * @param  User  $business
     * @return RedirectResponse
     */
    public function destroy(User $business): RedirectResponse
    {
        abort_if($business->role !== 'admin', 403, 'Acceso denegado.');

        $nombre = $business->business_name ?? $business->name;

        DB::transaction(function () use ($business): void {
            User::where('admin_id', $business->id)->update(['active' => false]);
            $business->delete();
        });

        return redirect()->route('superadmin.businesses.index')
            ->with('success', "Negocio «{$nombre}» eliminado correctamente.");
    }
}
