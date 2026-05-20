<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class SplitPaymentConfigController
 *
 * Permite al gerente activar o desactivar el cobro partido en su restaurante
 * y configurar el número máximo de partes en que se puede dividir la cuenta por mesa.
 *
 * @author BenjaminDTS
 */
class SplitPaymentConfigController extends Controller
{
    /**
     * Muestra el formulario de configuración de cobro partido.
     *
     * @return View
     */
    public function edit(): View
    {
        $user = Auth::user();

        return view('split-payment.edit', compact('user'));
    }

    /**
     * Guarda la configuración de cobro partido del restaurante.
     *
     * Cuando split_payment_enabled es false, split_payment_max_parts
     * se normaliza a null para evitar estado fantasma.
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'split_payment_enabled'   => ['sometimes', 'boolean'],
            'split_payment_max_parts' => ['nullable', 'integer', 'min:2', 'max:20'],
        ]);

        $enabled = (bool) ($validated['split_payment_enabled'] ?? false);

        Auth::user()->update([
            'split_payment_enabled'   => $enabled,
            'split_payment_max_parts' => $enabled
                ? ($request->input('split_payment_max_parts') ?: null)
                : null,
        ]);

        return redirect()->route('split-payment.edit')
                         ->with('success', 'Configuración de cobro partido guardada correctamente.');
    }
}
