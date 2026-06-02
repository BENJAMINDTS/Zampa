{{-- @author SebastianBCF --}}
{{-- Estilos PDF para plantilla modern. Renderizados inline por DomPDF via @include. --}}
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
