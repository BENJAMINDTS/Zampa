{-- @author SebastianBCF --}
{-- Estilos PDF para plantilla classic. Renderizados inline por DomPDF via @include. --}
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
