{{-- @author SebastianBCF --}}
{{-- Plantilla de ticket PDF — estilo clásico. Renderizada por DomPDF.
     Sin @extends. Sin Flexbox/Grid. Sin fuentes externas. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Ticket #{{ $order_id }}</title>
    @include('tickets.partials.style-classic')
</head>
<body>

    {{-- ─── CABECERA ─── --}}
    <div class="header">
        @if($logo_url)
            <div><img src="{{ $logo_url }}" alt="Logo" class="logo"></div>
        @endif
        <div class="business-name">{{ $business_name }}</div>
        @if($tax_id)
            <div class="tax-id">NIF/CIF: {{ $tax_id }}</div>
        @endif
    </div>

    <hr class="dashed">

    {{-- ─── META ─── --}}
    <table class="meta-table">
        <tr>
            <td><strong>Mesa:</strong> {{ $table_name }}</td>
            <td class="right"><strong>Pedido #:</strong> {{ $order_id }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Fecha:</strong> {{ $date }}</td>
        </tr>
    </table>

    <hr class="solid">

    {{-- ─── LÍNEAS ─── --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="col-name">Producto</th>
                <th class="col-qty">Cant.</th>
                <th class="col-price">P.U.</th>
                <th class="col-sub">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lines as $line)
                <tr>
                    <td class="col-name">{{ $line['name'] }}</td>
                    <td class="col-qty">{{ $line['quantity'] }}</td>
                    <td class="col-price">{{ number_format($line['unit_price'], 2, ',', '.') }}&nbsp;€</td>
                    <td class="col-sub">{{ number_format($line['subtotal'], 2, ',', '.') }}&nbsp;€</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr class="solid" style="border-top-width: 2px; margin-top: 1mm;">
    <hr class="solid" style="border-top-width: 1px; margin-top: 1mm; margin-bottom: 2mm;">

    {{-- ─── TOTALES ─── --}}
    <table class="totals-table">
        <tr>
            <td class="label">Subtotal:</td>
            <td class="amount">{{ number_format($subtotal, 2, ',', '.') }}&nbsp;€</td>
        </tr>
        @if($tip > 0)
            <tr>
                <td class="label">Propina:</td>
                <td class="amount">{{ number_format($tip, 2, ',', '.') }}&nbsp;€</td>
            </tr>
        @endif
        <tr class="total-row">
            <td class="label">TOTAL:</td>
            <td class="amount">{{ number_format($total, 2, ',', '.') }}&nbsp;€</td>
        </tr>
    </table>
    <div style="text-align:center;font-size:7pt;color:#888;margin-top:3mm">Precios con IVA incluido</div>

    @if($payment_method)
        <div class="payment-info">
            <strong>Pago:</strong>
            @switch($payment_method)
                @case('cash')  Efectivo @break
                @case('card')  Tarjeta @break
                @case('split') Cobro partido @break
                @default       {{ ucfirst($payment_method) }}
            @endswitch
        </div>
    @endif

    <hr class="dashed">

    {{-- ─── PIE ─── --}}
    @if($footer_text)
        <div class="footer-text">{{ $footer_text }}</div>
    @endif

</body>
</html>
