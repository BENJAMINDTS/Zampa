{{-- @author SebastianBCF --}}
{{-- Plantilla de ticket PDF — estilo clásico. Renderizada por DomPDF.
     Sin @extends. Sin Flexbox/Grid. Sin fuentes externas. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Ticket #{{ $order_id }}</title>
    <style>
        @page {
            size: A6;
            margin: 8mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 9pt;
            color: #111;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .center { text-align: center; }
        .right  { text-align: right; }
        .bold   { font-weight: bold; }

        .header { text-align: center; margin-bottom: 4mm; }
        .business-name { font-size: 14pt; font-weight: bold; margin-bottom: 1mm; }
        .tax-id { font-size: 8pt; color: #555; }

        .logo { max-height: 18mm; max-width: 55mm; }

        hr.dashed { border: none; border-top: 1px dashed #555; margin: 3mm 0; }
        hr.solid  { border: none; border-top: 1.5px solid #111; margin: 3mm 0; }
        hr.double {
            border: none;
            border-top: 2px solid #111;
            margin: 0;
        }
        hr.double-space { margin: 3mm 0; }

        .meta-table { width: 100%; font-size: 8.5pt; }

        /* Items */
        .items-table { width: 100%; border-collapse: collapse; margin-top: 2mm; }
        .items-table th {
            font-size: 8pt;
            font-weight: bold;
            border-bottom: 1px solid #111;
            padding: 1mm 1mm;
        }
        .items-table td {
            font-size: 8.5pt;
            padding: 1.5mm 1mm;
            border-bottom: 1px dotted #ccc;
            vertical-align: top;
        }
        .items-table .col-name  { width: 45%; text-align: left; }
        .items-table .col-qty   { width: 10%; text-align: center; }
        .items-table .col-price { width: 22%; text-align: right; }
        .items-table .col-sub   { width: 23%; text-align: right; }

        /* Totals */
        .totals-table { width: 100%; border-collapse: collapse; }
        .totals-table td { padding: 1mm 1mm; font-size: 9pt; }
        .totals-table .label { text-align: left; }
        .totals-table .amount { text-align: right; }
        .total-row td {
            font-size: 13pt;
            font-weight: bold;
            padding-top: 2mm;
        }

        .payment-info { font-size: 8.5pt; margin-top: 2mm; }

        .footer-text {
            text-align: center;
            font-size: 8pt;
            color: #555;
            margin-top: 2mm;
            font-style: italic;
        }
    </style>
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
