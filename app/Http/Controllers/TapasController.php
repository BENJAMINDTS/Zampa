<?php

namespace App\Http\Controllers;

use App\Models\TapaConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class TapasController
 *
 * Permite al gerente configurar el sistema de tapas del restaurante:
 * activar/desactivar, modo gratuito/de pago, precio y variantes máximas.
 *
 * @author BenjaminDTS
 */
class TapasController extends Controller
{
    /**
     * Muestra el formulario de configuración de tapas.
     * Crea la configuración con valores por defecto si aún no existe.
     *
     * @return View
     */
    public function edit(): View
    {
        $tapaConfig = TapaConfig::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'tapas_enabled'     => false,
                'tapas_free'        => true,
                'max_tapa_variants' => 3,
                'tapa_price'        => null,
            ]
        );

        return view('tapas.edit', compact('tapaConfig'));
    }

    /**
     * Guarda la configuración de tapas del restaurante.
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tapas_enabled'     => ['sometimes', 'boolean'],
            'tapas_free'        => ['sometimes', 'boolean'],
            'max_tapa_variants' => ['required', 'integer', 'min:1', 'max:20'],
            'tapa_price'        => ['required_if:tapas_free,0', 'nullable', 'numeric', 'min:0', 'max:999.99'],
        ]);

        $tapas_enabled = $request->boolean('tapas_enabled');
        $tapas_free    = $request->boolean('tapas_free');

        TapaConfig::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'tapas_enabled'     => $tapas_enabled,
                'tapas_free'        => $tapas_free,
                'max_tapa_variants' => $validated['max_tapa_variants'],
                'tapa_price'        => $tapas_free ? null : ($validated['tapa_price'] ?? null),
            ]
        );

        return redirect()->route('tapas.edit')
                         ->with('success', 'Configuración de tapas guardada correctamente.');
    }
}
