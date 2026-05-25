<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\TicketConfig;
use App\Services\TicketPdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Gestiona la descarga y reimpresión del ticket PDF.
 *
 * download() es pública: el cliente la llama desde la carta tras el pago.
 * reprint()  requiere auth: solo el gerente propietario del restaurante.
 *
 * @author BenjaminDTS
 */
class TicketController extends Controller
{
    public function __construct(private readonly TicketPdfService $pdfService) {}

    /**
     * Descarga pública del ticket tras el pago.
     * Verifica que el pedido está pagado y que el hash de mesa es correcto.
     * Si el restaurante no tiene TicketConfig se usa plantilla classic por defecto.
     *
     * @param  Request $request
     * @param  Order   $order
     * @return Response
     */
    public function download(Request $request, Order $order): Response
    {
        abort_if($order->payment_status !== 'paid', 403, 'El pedido no está pagado.');
        abort_if(
            $order->table->unique_hash !== $request->input('hash'),
            403,
            'Hash de mesa incorrecto.'
        );

        $config = TicketConfig::firstOrCreate(
            ['user_id' => $order->table->user_id],
            ['template' => 'classic']
        );

        return $this->pdfService->generate($order, $config);
    }

    /**
     * Reimpresión del ticket desde el panel del gerente.
     * Restringida al gerente propietario del restaurante.
     *
     * @param  Order $order
     * @return Response
     */
    public function reprint(Order $order): Response
    {
        abort_if(
            $order->table->user_id !== Auth::user()->ownerUserId(),
            403,
            'Acceso denegado.'
        );

        $config = TicketConfig::firstOrCreate(
            ['user_id' => $order->table->user_id],
            ['template' => 'classic']
        );

        return $this->pdfService->generate($order, $config);
    }
}
