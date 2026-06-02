{{-- @author SebastianBCF --}}
{{-- Plantilla de ticket PDF — estilo minimalista. Renderizada por DomPDF.
     Sin @extends. Sin Flexbox/Grid. Sin fuentes externas.
     Pensada para impresoras de ticket térmico. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Ticket #{{ $order_id }}</title>
    @include('tickets.partials.style-minimal')
</head>
<body>

    {{-- ─── CABECERA ─── --}}
    @if($logo_url)
        <div class="center"><img src="{{ $logo_url }}" alt="Logo" class="logo"></div>
    @endif
    <div class="business-name">{{ $business_name }}</div>
    @if($tax_id)
        <div class="tax-id">NIF/CIF: {{ $tax_id }}</div>
    @endif

    <hr>

    {{-- ─── META ─── --}}
    <table class="meta-table">
        <tr>
            <td>Mesa: {{ $table_name }}</td>
            <td class="right">#{{ $order_id }}</td>
        </tr>
        <tr>
            <td colspan="2">{{ $date }}</td>
        </tr>
    </table>

    <hr>

    {{-- ─── LÍNEAS ─── --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="col-name">Producto</th>
                <th class="col-qty" style="text-align:center">Ud.</th>
                <th class="col-price right">P.U.</th>
                <th class="col-sub right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lines as $line)
                <tr>
                    <td class="col-name">{{ $line['name'] }}</td>
                    <td class="col-qty" style="text-align:center">{{ $line['quantity'] }}</td>
                    <td class="col-price" style="text-align:right">{{ number_format($line['unit_price'], 2, ',', '.') }}</td>
                    <td class="col-sub" style="text-align:right">{{ number_format($line['subtotal'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr>

    {{-- ─── TOTALES ─── --}}
    <table class="totals-table">
        <tr>
            <td class="label">Subtotal</td>
            <td class="amount">{{ number_format($subtotal, 2, ',', '.') }} EUR</td>
        </tr>
        @if($tip > 0)
            <tr>
                <td class="label">Propina</td>
                <td class="amount">{{ number_format($tip, 2, ',', '.') }} EUR</td>
            </tr>
        @endif
        <tr class="total-row">
            <td class="label">TOTAL</td>
            <td class="amount">{{ number_format($total, 2, ',', '.') }} EUR</td>
        </tr>
    </table>
    <div style="text-align:center;font-size:7pt;color:#888;margin-top:3mm">Precios con IVA incluido</div>

    @if($payment_method)
        <div class="payment-info">
            Pago:
            @switch($payment_method)
                @case('cash')  EFECTIVO @break
                @case('card')  TARJETA @break
                @case('split') COBRO PARTIDO @break
                @default       {{ strtoupper($payment_method) }}
            @endswitch
        </div>
    @endif

    <hr>

    {{-- ─── PIE ─── --}}
    @if($footer_text)
        <div class="footer-text">{{ $footer_text }}</div>
    @else
        <div class="footer-text">*** FIN DEL TICKET ***</div>
    @endif

</body>
</html>
