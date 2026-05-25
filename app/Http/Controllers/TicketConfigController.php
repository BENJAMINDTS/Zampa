<?php

namespace App\Http\Controllers;

use App\Models\TicketConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Gestión de la configuración del ticket PDF por restaurante.
 * Relación 1:1 con User — no requiere abort_if de multitenancy:
 * siempre se resuelve por Auth::id(), nunca por ID de ruta ajeno.
 *
 * @author SebastianBCF
 */
class TicketConfigController extends Controller
{
    /**
     * Muestra el formulario de configuración del ticket PDF.
     *
     * @return View
     */
    public function edit(): View
    {
        $ticketConfig = TicketConfig::firstOrCreate(
            ['user_id' => Auth::id()]
        );

        return view('ticket-config.edit', [
            'ticketConfig' => $ticketConfig,
            'templates'    => TicketConfig::TEMPLATES,
        ]);
    }

    /**
     * Guarda la configuración del ticket PDF del gerente.
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'logo'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'tax_id'      => ['nullable', 'string', 'max:20'],
            'footer_text' => ['nullable', 'string', 'max:500'],
            'template'    => ['required', 'in:' . implode(',', TicketConfig::TEMPLATES)],
        ]);

        $ticketConfig = TicketConfig::firstOrCreate(
            ['user_id' => Auth::id()]
        );

        if ($request->hasFile('logo')) {
            if ($ticketConfig->hasLogo()) {
                Storage::disk('public')->delete($ticketConfig->logo);
            }
            $validated['logo'] = $request->file('logo')->store('tickets', 'public');
        }

        $ticketConfig->update($validated);

        return redirect()->route('ticket-config.edit')
            ->with('success', 'Configuración del ticket guardada correctamente.');
    }

    /**
     * Previsualización del ticket con los datos actuales del restaurante.
     * Muestra el estado guardado; el gerente debe guardar para ver cambios.
     *
     * @return View
     */
    public function preview(): View
    {
        $ticketConfig = TicketConfig::firstOrCreate(
            ['user_id' => Auth::id()]
        );

        return view('ticket-config.preview', [
            'ticketConfig' => $ticketConfig,
            'user'         => Auth::user(),
        ]);
    }
}
