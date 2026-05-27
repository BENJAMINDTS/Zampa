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
 * @author AyrtonAlania
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

        $templateLabels = ['classic' => 'Clásica', 'modern' => 'Moderna', 'minimal' => 'Minimalista'];
        $changed = [];

        if ($request->hasFile('logo')) {
            if ($ticketConfig->hasLogo()) {
                Storage::disk('public')->delete($ticketConfig->logo);
            }
            $validated['logo'] = $request->file('logo')->store('tickets', 'public');
            $changed[] = 'logo actualizado';
        } else {
            unset($validated['logo']);
        }

        if (trim($request->input('tax_id', '')) !== trim($ticketConfig->tax_id ?? '')) {
            $changed[] = 'datos fiscales guardados';
        }

        if (trim($request->input('footer_text', '')) !== trim($ticketConfig->footer_text ?? '')) {
            $changed[] = 'pie del ticket guardado';
        }

        if ($request->input('template') !== $ticketConfig->template) {
            $label = $templateLabels[$request->input('template')] ?? $request->input('template');
            $changed[] = "plantilla cambiada a «{$label}»";
        }

        $ticketConfig->update($validated);

        $message = count($changed) > 0
            ? ucfirst(implode(' · ', $changed)) . '.'
            : 'Sin cambios detectados.';

        return redirect()->route('ticket-config.edit')->with('success', $message);
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
