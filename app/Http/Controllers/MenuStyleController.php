<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Gestiona la selección del estilo visual de la carta digital pública.
 *
 * @author Ayrtonalania
 */
class MenuStyleController extends Controller
{
    /**
     * Muestra el selector de estilo de la carta digital.
     *
     * @return View
     */
    public function edit(): View
    {
        return view('negocio.menu-style', [
            'currentStyle' => Auth::user()->menu_style ?? 'modern',
        ]);
    }

    /**
     * Persiste el estilo seleccionado por el gerente.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'menu_style' => ['required', 'in:modern,classic,minimal'],
        ], [
            'menu_style.required' => 'Debes seleccionar un estilo para la carta digital.',
            'menu_style.in'       => 'El estilo seleccionado no es válido.',
        ]);

        $user = Auth::user();
        $changed = $request->input('menu_style') !== ($user->menu_style ?? 'modern');

        $user->update(['menu_style' => $request->input('menu_style')]);

        $message = $changed ? 'Estilo de carta digital actualizado.' : 'Sin cambios detectados.';

        return redirect()->route('negocio.menu-style.edit')->with('success', $message);
    }
}
