{{-- @author SebastianBCF --}}
{{-- Plantilla de ticket PDF — estilo moderno. Renderizada por DomPDF.
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
            margin: 0;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #1a1a2e;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .right  { text-align: right; }
        .center { text-align: center; }
        .bold   { font-weight: bold; }

        /* ── Cabecera oscura ── */
        .header-block {
            background-color: #1a1a2e;
            color: #ffffff;
            padding: 6mm 8mm 5mm;
            text-align: center;
        }
        .header-block .logo {
            max-height: 16mm;
            max-width: 50mm;
            margin-bottom: 2mm;
        }
        .header-block .business-name {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .header-block .tax-id {
            font-size: 8pt;
            color: #aab4be;
            margin-top: 1mm;
        }

        /* ── Cuerpo ── */
        .body-pad { padding: 5mm 8mm; }

        /* Meta */
        .meta-table { width: 100%; font-size: 8pt; color: #555; margin-bottom: 3mm; }
        .meta-table td { padding: 0.5mm 0; }

        /* Separador */
        hr.sep {
            border: none;
            border-top: 1px solid #e0e0e0;
            margin: 3mm 0;
        }

        /* Items */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 2mm; }
        .items-table th {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #888;
            letter-spacing: 0.04em;
            border-bottom: 1px solid #ddd;
            padding: 1mm 1.5mm;
        }
        .items-table td {
            font-size: 8.5pt;
            padding: 1.5mm 1.5mm;
            vertical-align: top;
        }
        .items-table .row-even { background-color: #f5f5f5; }
        .items-table .col-name  { width: 45%; text-align: left; }
        .items-table .col-qty   { width: 10%; text-align: center; }
        .items-table .col-price { width: 22%; text-align: right; }
        .items-table .col-sub   { width: 23%; text-align: right; }

        /* Totals */
        .totals-table { width: 100%; border-collapse: collapse; margin-top: 1mm; }
        .totals-table td { padding: 1mm 1.5mm; font-size: 9pt; }
        .totals-table .label  { text-align: left;  color: #555; }
        .totals-table .amount { text-align: right; }

        /* Total final — bloque destacado */
        .total-block {
            background-color: #1a1a2e;
            color: #ffffff;
            padding: 3mm 6mm;
            margin-top: 3mm;
        }
        .total-block-table { width: 100%; border-collapse: collapse; }
        .total-block-table td { font-size: 13pt; font-weight: bold; color: #ffffff; }
        .total-block-table .label  { text-align: left; }
        .total-block-table .amount { text-align: right; }

        .payment-info {
            font-size: 8pt;
            color: #555;
            margin-top: 3mm;
        }

        /* Pie */
        .footer-block {
            background-color: #f5f5f5;
            padding: 4mm 8mm;
            text-align: center;
            font-size: 7.5pt;
            color: #777;
            margin-top: 4mm;
        }
    </style>
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
