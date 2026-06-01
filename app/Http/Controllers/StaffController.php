<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Controlador para la gestión del personal del restaurante.
 *
 * Permite al gerente (admin) listar, dar de alta y eliminar
 * miembros de su equipo (camareros y cocineros).
 * El acceso está restringido al middleware role:admin.
 *
 * @author BenjaminDTS
 */
class StaffController extends Controller
{
    /**
     * Muestra el listado de personal perteneciente al admin autenticado.
     *
     * @return View
     */
    public function index(): View
    {
        $staff = Auth::user()->staff()->paginate(15);

        return view('staff.index', compact('staff'));
    }

    /**
     * Muestra el formulario de alta de un nuevo miembro de personal.
     *
     * @return View
     */
    public function create(): View
    {
        return view('staff.create');
    }

    /**
     * Valida y crea un nuevo miembro de personal asociado al admin autenticado.
     * Comprueba el límite max_staff del plan antes de crear el usuario.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $owner        = Auth::user();
        $plan         = $owner->plan;
        $currentStaff = $owner->staff()->count();

        if ($plan && $plan->isLimitReached('staff', $currentStaff)) {
            return redirect()
                ->route('staff.create')
                ->with('error', "Has alcanzado el límite de {$plan->max_staff} miembros de personal de tu plan {$plan->name}. Actualiza tu plan para añadir más personal.")
                ->withInput();
        }

        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
            'role'                  => 'required|in:waiter,kitchen',
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
            'admin_id' => Auth::id(),
        ]);

        return redirect()
            ->route('staff.index')
            ->with('success', 'Miembro del personal dado de alta correctamente.');
    }

    /**
     * Elimina un miembro del personal del admin autenticado.
     *
     * @param  User  $staff
     * @return RedirectResponse
     */
    public function destroy(User $staff): RedirectResponse
    {
        abort_if($staff->admin_id !== Auth::id(), 403, 'No puedes eliminar personal de otro restaurante.');

        $staff->forceDelete();

        return redirect()
            ->route('staff.index')
            ->with('success', 'Miembro del personal eliminado correctamente.');
    }
}
