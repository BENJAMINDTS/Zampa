<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Controlador para la gestión de mesas y sus códigos QR.
 *
 * Permite al gerente ver todas sus mesas con el QR generado
 * para cada una, descargar el QR en formato PNG o SVG, y
 * regenerar el unique_hash invalidando el QR anterior.
 *
 * @author AyrtonAlania
 */
class TableController extends Controller
{
    /**
     * Muestra el listado de mesas del restaurante autenticado
     * junto con el QR generado para cada una.
     *
     * @return View
     */
    public function index(): View
    {
        $tables = Table::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return view('tables.index', compact('tables'));
    }

    /**
     * Descarga el código QR de una mesa en formato SVG.
     *
     * @param  Table  $table
     * @return Response
     */
    public function downloadQr(Table $table): Response
    {
        abort_if($table->user_id !== Auth::id(), 403, 'Acceso denegado.');

        $url      = route('menu.show', $table->unique_hash);
        $svg      = QrCode::format('svg')
            ->size(300)
            ->margin(1)
            ->generate($url);

        $filename = 'qr-' . Str::slug($table->name) . '.svg';

        return response($svg, 200, [
            'Content-Type'        => 'image/svg+xml',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Regenera el unique_hash de una mesa, invalidando el QR anterior.
     *
     * @param  Table  $table
     * @return RedirectResponse
     */
    public function regenerateHash(Table $table): RedirectResponse
    {
        abort_if($table->user_id !== Auth::id(), 403, 'Acceso denegado.');

        $table->update(['unique_hash' => Str::uuid()->toString()]);

        return redirect()
            ->route('tables.index')
            ->with('success', "El QR de la mesa \"{$table->name}\" ha sido regenerado. El enlace anterior ya no es válido.");
    }
}
