<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\TapaConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class TapasController
 *
 * Permite al gerente configurar el sistema de tapas del restaurante:
 * activar/desactivar, modo gratuito/de pago, precio, variantes máximas,
 * tapa extra de pago y tramos horarios de apertura de cocina.
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
                'tapas_enabled'      => false,
                'tapas_free'         => true,
                'max_tapa_variants'  => 3,
                'tapa_price'         => null,
                'extra_tapa_enabled' => false,
                'extra_tapa_price'   => null,
            ]
        );

        $tapaConfig->load('schedules');

        return view('tapas.edit', compact('tapaConfig'));
    }

    /**
     * Guarda la configuración de tapas del restaurante.
     * Al activar tapas crea automáticamente la categoría 'Tapas' con destination=kitchen.
     * Reemplaza los tramos horarios de cocina por los enviados en el formulario.
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'tapas_enabled'            => ['sometimes', 'boolean'],
            'tapas_free'               => ['sometimes', 'boolean'],
            'max_tapa_variants'        => ['required', 'integer', 'min:1', 'max:20'],
            'tapa_price'               => ['required_if:tapas_free,0', 'nullable', 'numeric', 'min:0', 'max:999.99'],
            'extra_tapa_enabled'       => ['sometimes', 'boolean'],
            'extra_tapa_price'         => ['required_if:extra_tapa_enabled,1', 'nullable', 'numeric', 'min:0', 'max:999.99'],
            'schedules'                => ['nullable', 'array', 'max:10'],
            'schedules.*.opens_at'     => ['required', 'date_format:H:i'],
            'schedules.*.closes_at'    => ['required', 'date_format:H:i'],
        ]);

        $userId       = Auth::id();
        $tapasEnabled = $request->boolean('tapas_enabled');
        $tapasFree    = $request->boolean('tapas_free');
        $extraEnabled = $request->boolean('extra_tapa_enabled');

        if ($tapasEnabled) {
            Category::firstOrCreate(
                ['user_id' => $userId, 'name' => 'Tapas'],
                ['destination' => 'kitchen']
            );
        }

        $config = TapaConfig::updateOrCreate(
            ['user_id' => $userId],
            [
                'tapas_enabled'      => $tapasEnabled,
                'tapas_free'         => $tapasFree,
                'max_tapa_variants'  => $request->integer('max_tapa_variants'),
                'tapa_price'         => $tapasFree ? null : ($request->input('tapa_price') ?? null),
                'extra_tapa_enabled' => $extraEnabled,
                'extra_tapa_price'   => $extraEnabled ? ($request->input('extra_tapa_price') ?? null) : null,
            ]
        );

        $config->schedules()->delete();

        foreach ($request->input('schedules', []) as $slot) {
            $config->schedules()->create([
                'opens_at'  => $slot['opens_at'],
                'closes_at' => $slot['closes_at'],
            ]);
        }

        return redirect()->route('tapas.edit')
                         ->with('success', 'Configuración de tapas guardada correctamente.');
    }
}
