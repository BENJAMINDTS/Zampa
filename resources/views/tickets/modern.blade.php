{{-- @author SebastianBCF --}}
{{-- Plantilla de ticket PDF — estilo moderno. Renderizada por DomPDF.
     Sin @extends. Sin Flexbox/Grid. Sin fuentes externas. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Ticket #{{ $order_id }}</title>
    @include('tickets.partials.style-modern')
</head>
<body>

    {{-- ─── CABECERA OSCURA ─── --}}
    <div class="header-block">
        @if($logo_url)
            <div><img src="{{ $logo_url }}" alt="Logo" class="logo"></div>
        @endif
        <div class="business-name">{{ $business_name }}</div>
        @if($tax_id)
            <div class="tax-id">NIF/CIF: {{ $tax_id }}</div>
        @endif
    </div>

    {{-- ─── CUERPO ─── --}}
    <div class="body-pad">

        {{-- Meta --}}
        <table class="meta-table">
            <tr>
                <td><strong>Mesa:</strong> {{ $table_name }}</td>
                <td class="right"><strong>#</strong>{{ $order_id }}</td>
            </tr>
            <tr>
                <td colspan="2">{{ $date }}</td>
            </tr>
        </table>

        <hr class="sep">

        {{-- Líneas --}}
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
                    <tr class="{{ $loop->iteration % 2 === 0 ? 'row-even' : '' }}">
                        <td class="col-name">{{ $line['name'] }}</td>
                        <td class="col-qty">{{ $line['quantity'] }}</td>
                        <td class="col-price">{{ number_format($line['unit_price'], 2, ',', '.') }}&nbsp;€</td>
                        <td class="col-sub">{{ number_format($line['subtotal'], 2, ',', '.') }}&nbsp;€</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <hr class="sep">

        {{-- Subtotal / propina --}}
        <table class="totals-table">
            <tr>
                <td class="label">Subtotal</td>
                <td class="amount">{{ number_format($subtotal, 2, ',', '.') }}&nbsp;€</td>
            </tr>
            @if($tip > 0)
                <tr>
                    <td class="label">Propina</td>
                    <td class="amount">{{ number_format($tip, 2, ',', '.') }}&nbsp;€</td>
                </tr>
            @endif
        </table>

        {{-- Total destacado --}}
        <div class="total-block">
            <table class="total-block-table">
                <tr>
                    <td class="label">TOTAL</td>
                    <td class="amount">{{ number_format($total, 2, ',', '.') }}&nbsp;€</td>
                </tr>
            </table>
        </div>
        <div style="text-align:center;font-size:8pt;color:#888;margin-top:4mm">Precios con IVA incluido</div>

        @if($payment_method)
            <div class="payment-info">
                Pago:
                @switch($payment_method)
                    @case('cash')  Efectivo @break
                    @case('card')  Tarjeta @break
                    @case('split') Cobro partido @break
                    @default       {{ ucfirst($payment_method) }}
                @endswitch
            </div>
        @endif

    </div>

    {{-- ─── PIE ─── --}}
    @if($footer_text)
        <div class="footer-block">{{ $footer_text }}</div>
    @endif

</body>
</html>
