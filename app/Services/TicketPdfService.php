<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TicketConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

/**
 * Genera el PDF del ticket a partir de una Order y su TicketConfig.
 * Usa barryvdh/laravel-dompdf para el renderizado.
 *
 * @author BenjaminDTS
 * @author AyrtonAlania
 */
class TicketPdfService
{
    /**
     * Genera y devuelve la Response de descarga del PDF del ticket.
     * Aplica eager loading para evitar N+1 sobre los ítems y la mesa.
     *
     * @param  Order        $order
     * @param  TicketConfig $config
     * @return Response
     */
    public function generate(Order $order, TicketConfig $config): Response
    {
        $order->loadMissing([
            'items.product',
            'items.variant',
            'table',
        ]);
        $config->loadMissing('user');

        $view = 'tickets.' . $config->template;
        $data = $this->buildTicketData($order, $config);

        $pdf = Pdf::loadView($view, $data);

        return $pdf->download($this->getFilename($order));
    }

    /**
     * Devuelve el nombre del archivo PDF para la descarga.
     *
     * @param  Order  $order
     * @return string
     */
    public function getFilename(Order $order): string
    {
        return 'ticket-' . $order->id . '-' . now()->format('Y-m-d') . '.pdf';
    }

    /**
     * Construye el array de datos para las vistas de ticket.
     * Público para permitir pruebas unitarias directas.
     *
     * Calcula:
     *   - líneas: nombre, cantidad, precio unitario, subtotal de línea
     *   - subtotal: suma de líneas sin propina
     *   - tip: propina del pedido (puede ser 0)
     *   - total: subtotal + tip
     *
     * @param  Order        $order
     * @param  TicketConfig $config
     * @return array<string, mixed>
     */
    public function buildTicketData(Order $order, TicketConfig $config): array
    {
        $user = $config->user;

        $lines = $order->items->map(function (OrderItem $item): array {
            $name = $item->variant_name ?? ($item->product?->name ?? '—');
            return [
                'name'       => $name,
                'quantity'   => (int) $item->quantity,
                'unit_price' => (float) $item->price,
                'subtotal'   => round((float) $item->price * $item->quantity, 2),
            ];
        });

        $subtotal = round($lines->sum('subtotal'), 2);
        $tip      = round((float) ($order->tip ?? 0), 2);
        $total    = round($subtotal + $tip, 2);

        return [
            'business_name'  => $user?->business_name ?? $user?->name,
            'tax_id'         => $config->tax_id,
            'logo_url'       => $config->getLogoUrl(),
            'footer_text'    => $config->footer_text,
            'template'       => $config->template,
            'table_name'     => $order->table?->name ?? '—',
            'date'           => $order->created_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i'),
            'lines'          => $lines,
            'subtotal'       => $subtotal,
            'tip'            => $tip,
            'total'          => $total,
            'payment_method' => $order->payment_method,
            'order_id'       => $order->id,
        ];
    }
}
