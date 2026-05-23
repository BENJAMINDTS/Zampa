<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\TapaConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Class TapasController
 *
 * Gestiona la configuración del negocio: horarios de apertura (negocio y cocina),
 * período de cierre de pedidos, sistema de tapas y tapa extra.
 *
 * @author BenjaminDTS
 */
class TapasController extends Controller
{
    /**
     * Muestra el formulario de configuración del negocio.
     * Crea la configuración con valores por defecto si aún no existe.
     *
     * @return View
     */
    public function edit(): View
    {
        $tapaConfig = TapaConfig::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'tapas_enabled'           => false,
                'tapas_free'              => true,
                'price_mode'              => 'fixed',
                'max_tapa_variants'       => 3,
                'tapa_price'              => null,
                'extra_tapa_enabled'      => false,
                'extra_tapa_price'        => null,
                'ordering_cutoff_minutes' => 0,
            ]
        );

        $tapaConfig->load(['kitchenSchedules', 'businessSchedules']);

        return view('negocio.config', compact('tapaConfig'));
    }

    /**
     * Guarda la configuración del negocio.
     *
     * Valida y persiste:
     *  - Tramos de horario del negocio (type=business, máx. 4)
     *  - Tramos de horario de cocina (type=kitchen, máx. 4)
     *  - Minutos de cierre de pedidos antes del fin de tramo
     *  - Toda la configuración de tapas
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $tapasFree = $request->boolean('tapas_free');
        $priceMode = $request->input('price_mode', 'fixed');

        $request->validate([
            'tapas_enabled'                       => ['sometimes', 'boolean'],
            'tapas_free'                          => ['sometimes', 'boolean'],
            'price_mode'                          => ['nullable', 'in:fixed,per_product'],
            'max_tapa_variants'                   => ['required', 'integer', 'min:1', 'max:20'],
            'tapa_price'                          => [
                Rule::requiredIf(fn () => ! $tapasFree && $priceMode === 'fixed'),
                'nullable', 'numeric', 'min:0', 'max:999.99',
            ],
            'extra_tapa_enabled'                  => ['sometimes', 'boolean'],
            'extra_tapa_price'                    => ['required_if:extra_tapa_enabled,1', 'nullable', 'numeric', 'min:0', 'max:999.99'],
            'ordering_cutoff_minutes'             => ['nullable', 'integer', 'min:0', 'max:120'],
            'kitchen_schedules'                   => ['nullable', 'array', 'max:4'],
            'kitchen_schedules.*.opens_at'        => ['required', 'date_format:H:i'],
            'kitchen_schedules.*.closes_at'       => ['required', 'date_format:H:i'],
            'business_schedules'                  => ['nullable', 'array', 'max:4'],
            'business_schedules.*.opens_at'       => ['required', 'date_format:H:i'],
            'business_schedules.*.closes_at'      => ['required', 'date_format:H:i'],
            'split_payment_enabled'               => ['sometimes', 'boolean'],
            'split_payment_max_parts'             => ['nullable', 'integer', 'min:2', 'max:20'],
        ]);

        $userId       = Auth::id();
        $tapasEnabled = $request->boolean('tapas_enabled');
        $extraEnabled = $request->boolean('extra_tapa_enabled');

        $resolvedMode  = $tapasFree ? 'fixed' : $priceMode;
        $resolvedPrice = ($tapasFree || $resolvedMode === 'per_product')
            ? null
            : ($request->input('tapa_price') ?? null);

        if ($tapasEnabled) {
            Category::firstOrCreate(
                ['user_id' => $userId, 'name' => 'Tapas'],
                ['destination' => 'kitchen']
            );
        }

        $config = TapaConfig::updateOrCreate(
            ['user_id' => $userId],
            [
                'tapas_enabled'           => $tapasEnabled,
                'tapas_free'              => $tapasFree,
                'price_mode'              => $resolvedMode,
                'max_tapa_variants'       => $request->integer('max_tapa_variants'),
                'tapa_price'              => $resolvedPrice,
                'extra_tapa_enabled'      => $extraEnabled,
                'extra_tapa_price'        => $extraEnabled ? ($request->input('extra_tapa_price') ?? null) : null,
                'ordering_cutoff_minutes' => (int) ($request->input('ordering_cutoff_minutes') ?? 0),
            ]
        );

        // Reemplazar tramos de cocina
        $config->kitchenSchedules()->delete();
        foreach ($request->input('kitchen_schedules', []) as $slot) {
            $config->kitchenSchedules()->create([
                'type'      => 'kitchen',
                'opens_at'  => $slot['opens_at'],
                'closes_at' => $slot['closes_at'],
            ]);
        }

        // Reemplazar tramos del negocio
        $config->businessSchedules()->delete();
        foreach ($request->input('business_schedules', []) as $slot) {
            $config->businessSchedules()->create([
                'type'      => 'business',
                'opens_at'  => $slot['opens_at'],
                'closes_at' => $slot['closes_at'],
            ]);
        }

        // Guardar cobro partido en el usuario
        $splitEnabled = $request->boolean('split_payment_enabled');
        Auth::user()->update([
            'split_payment_enabled'   => $splitEnabled,
            'split_payment_max_parts' => $splitEnabled
                ? ($request->input('split_payment_max_parts') ?: null)
                : null,
        ]);

        return redirect()->route('negocio.config.edit')
                         ->with('success', 'Configuración del negocio guardada correctamente.');
    }
}
