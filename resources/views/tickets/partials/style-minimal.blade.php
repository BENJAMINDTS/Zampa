{-- @author SebastianBCF --}
{-- Estilos PDF para plantilla minimal. Renderizados inline por DomPDF via @include. --}
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
