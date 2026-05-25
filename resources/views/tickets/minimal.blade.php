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
    <style>
        @page {
            size: A6;
            margin: 6mm 7mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 8pt;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .center { text-align: center; }
        .right  { text-align: right; }

        .business-name {
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 0.5mm;
        }
        .tax-id {
            font-size: 7.5pt;
            text-align: center;
            margin-bottom: 0.5mm;
        }
        .logo {
            max-height: 12mm;
            max-width: 40mm;
        }

        hr {
            border: none;
            border-top: 1px solid #000;
            margin: 2mm 0;
        }

        /* Meta */
        .meta-table { width: 100%; font-size: 7.5pt; }
        .meta-table td { padding: 0.3mm 0; }

        /* Items */
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th {
            font-size: 7pt;
            font-weight: bold;
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 0.5mm 0;
        }
        .items-table th.right { text-align: right; }
        .items-table td {
            font-size: 7.5pt;
            padding: 0.5mm 0;
            vertical-align: top;
        }
        .items-table .col-name  { width: 45%; }
        .items-table .col-qty   { width: 10%; text-align: center; }
        .items-table .col-price { width: 22%; text-align: right; }
        .items-table .col-sub   { width: 23%; text-align: right; }

        /* Totals */
        .totals-table { width: 100%; border-collapse: collapse; }
        .totals-table td { font-size: 8pt; padding: 0.5mm 0; }
        .totals-table .label  { text-align: left; }
        .totals-table .amount { text-align: right; }
        .totals-table .total-row td {
            font-size: 10pt;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 1mm;
        }

        .payment-info { font-size: 7.5pt; margin-top: 1.5mm; }

        .footer-text {
            text-align: center;
            font-size: 7pt;
            margin-top: 1.5mm;
        }
    </style>
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
