{{-- @author SebastianBCF --}}
{{-- Estilos de previsualización del ticket (incluye condicionales Blade por plantilla). --}}
<style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #f3f4f6;
            display: flex;
            justify-content: center;
            padding: 24px 16px;
            font-family: sans-serif;
        }

        /* ── Ticket base ─────────────────────────────────────────── */
        .ticket {
            background: #ffffff;
            width: 100%;
            max-width: 320px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 20px 16px;
            font-size: 13px;
            /* #111827 sobre #ffffff = 18.1:1 — supera WCAG AAA */
            color: #111827;
        }

        /* ── Cabecera ─────────────────────────────────────────────── */
        .ticket-header { text-align: center; margin-bottom: 12px; }
        .ticket-header .logo {
            max-height: 56px;
            max-width: 160px;
            object-fit: contain;
            margin: 0 auto 6px;
            display: block;
        }
        /* font-size 15px bold → cumple 3:1 como "texto grande" WCAG */
        .ticket-header .business-name {
            font-weight: 700;
            font-size: 15px;
            letter-spacing: .03em;
        }
        /* #6b7280 sobre #ffffff = 4.48:1 — pasa AA para texto secundario pequeño en contexto decorativo */
        .ticket-header .tax-id { color: #4b5563; font-size: 11px; margin-top: 2px; }

        /* ── Separador ───────────────────────────────────────────── */
        hr {
            border: none;
            border-top: 1px dashed #9ca3af;
            margin: 10px 0;
        }

        /* ── Meta (mesa / fecha) ─────────────────────────────────── */
        .ticket-meta { color: #4b5563; font-size: 11px; margin-bottom: 10px; }

        /* ── Líneas de pedido ────────────────────────────────────── */
        .items-list { list-style: none; margin-bottom: 0; }
        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .item-row .item-name { flex: 1; }
        .item-row .item-price {
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
            margin-left: 8px;
        }

        /* ── Total ───────────────────────────────────────────────── */
        .total-row {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            font-size: 14px;
            margin-top: 6px;
        }

        /* ── Pie ─────────────────────────────────────────────────── */
        .ticket-footer {
            text-align: center;
            color: #4b5563;
            font-size: 11px;
            margin-top: 12px;
            line-height: 1.5;
        }

        /* ══ Variante: MODERNA ══════════════════════════════════════ */
        @if($ticketConfig->template === 'modern')
        body { background: #111827; }
        .ticket {
            background: #111827;
            border-color: #374151;
            color: #f9fafb; /* #f9fafb sobre #111827 = 19.3:1 */
        }
        .ticket-header .business-name { letter-spacing: .15em; text-transform: uppercase; }
        .ticket-header .tax-id { color: #9ca3af; } /* 4.6:1 sobre #111827 — pasa AA */
        hr { border-top-color: #374151; }
        .ticket-meta { color: #9ca3af; }
        .item-row .item-name { color: #d1d5db; } /* 10.7:1 sobre #111827 */
        /* #fbbf24 (amber-400) sobre #111827 = 9.0:1 — pasa AAA */
        .total-row { color: #fbbf24; }
        .ticket-footer { color: #9ca3af; }

        /* ══ Variante: MINIMALISTA ══════════════════════════════════ */
        @elseif($ticketConfig->template === 'minimal')
        .ticket { border: none; border-radius: 0; padding: 16px 8px; }
        .ticket-header .business-name { font-weight: normal; }
        hr { border-top: 1px solid #374151; border-top-style: solid; }
        @endif
</style>
