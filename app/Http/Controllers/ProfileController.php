<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * Class ProfileController
 *
 * Controlador para la gestión del perfil del usuario autenticado.
 * Permite visualizar, actualizar y eliminar la cuenta propia.
 *
 * @author BenjaminDTS
 */
class ProfileController extends Controller
{
    /**
     * Muestra el formulario de edición del perfil del usuario.
     *
     * @param  Request  $request Objeto de la petición HTTP.
     * @return View Vista del formulario de perfil.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Actualiza los datos del perfil del usuario autenticado.
     *
     * @param  ProfileUpdateRequest  $request Datos validados del formulario de perfil.
     * @return RedirectResponse Redirección al formulario con mensaje de estado.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Elimina la cuenta del usuario autenticado y cierra su sesión.
     *
     * @param  Request  $request Objeto de la petición HTTP con la confirmación de contraseña.
     * @return RedirectResponse Redirección a la página de inicio.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
