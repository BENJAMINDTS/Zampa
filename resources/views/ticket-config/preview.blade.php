{{-- @author SebastianBCF --}}
{{-- Vista standalone del ticket para previsualización (embebida en iframe).
     No extiende x-app-layout para evitar conflictos de CSS con el panel admin.
     Las plantillas reales (classic/modern/minimal) se crean en el Bloque 17.4. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Previsualización del ticket</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #f9fafb; display: flex; justify-content: center; padding: 24px 16px; font-family: sans-serif; }
        .ticket {
            background: white;
            width: 100%;
            max-width: 320px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 20px 16px;
            font-size: 13px;
            color: #111827;
        }
        .ticket-header { text-align: center; margin-bottom: 12px; }
        .ticket-header .logo { max-height: 56px; max-width: 160px; object-fit: contain; margin: 0 auto 6px; display: block; }
        .ticket-header .business-name { font-weight: 700; font-size: 15px; letter-spacing: .03em; }
        .ticket-header .tax-id { color: #6b7280; font-size: 11px; margin-top: 2px; }
        hr { border: none; border-top: 1px dashed #d1d5db; margin: 10px 0; }
        .ticket-meta { color: #6b7280; font-size: 11px; margin-bottom: 10px; }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .item-row .item-name { flex: 1; }
        .item-row .item-price { font-variant-numeric: tabular-nums; white-space: nowrap; margin-left: 8px; }
        .total-row { display: flex; justify-content: space-between; font-weight: 700; font-size: 14px; margin-top: 6px; }
        .ticket-footer { text-align: center; color: #6b7280; font-size: 11px; margin-top: 12px; line-height: 1.5; }

        {{-- Variantes de plantilla --}}
        @if($ticketConfig->template === 'modern')
        body { background: #1f2937; }
        .ticket { background: #111827; border-color: #374151; color: #f9fafb; }
        .ticket-header .business-name { letter-spacing: .15em; text-transform: uppercase; }
        .ticket-header .tax-id { color: #9ca3af; }
        hr { border-top-color: #374151; }
        .ticket-meta { color: #9ca3af; }
        .item-row .item-name { color: #d1d5db; }
        .total-row { color: #fbbf24; }
        .ticket-footer { color: #9ca3af; }
        @elseif($ticketConfig->template === 'minimal')
        .ticket { border: none; border-radius: 0; padding: 16px 8px; }
        .ticket-header .business-name { font-weight: normal; }
        hr { border-top: 1px solid #111827; }
        @endif
    </style>
</head>
<body>
    <article class="ticket" aria-label="Previsualización del ticket">
        <header class="ticket-header">
            @if($ticketConfig->hasLogo())
                <img src="{{ $ticketConfig->getLogoUrl() }}" alt="Logo de {{ $user->business_name ?? $user->name }}" class="logo">
            @endif
            <div class="business-name">{{ $user->business_name ?? $user->name }}</div>
            @if($ticketConfig->tax_id)
                <div class="tax-id">NIF/CIF: {{ $ticketConfig->tax_id }}</div>
            @endif
        </header>

        <hr>

        <div class="ticket-meta">
            Mesa 3 · {{ now()->format('d/m/Y H:i') }}
        </div>

        {{-- Líneas de ejemplo --}}
        <div class="item-row">
            <span class="item-name">Agua mineral ×2</span>
            <span class="item-price">3,00 €</span>
        </div>
        <div class="item-row">
            <span class="item-name">Pizza Margarita ×1</span>
            <span class="item-price">12,50 €</span>
        </div>
        <div class="item-row">
            <span class="item-name">Café con leche ×1</span>
            <span class="item-price">1,80 €</span>
        </div>

        <hr>

        <div class="total-row">
            <span>TOTAL</span>
            <span>17,30 €</span>
        </div>

        @if($ticketConfig->footer_text)
            <hr>
            <footer class="ticket-footer">{{ $ticketConfig->footer_text }}</footer>
        @else
            <hr>
            <footer class="ticket-footer">Gracias por su visita.</footer>
        @endif
    </article>
</body>
</html>
