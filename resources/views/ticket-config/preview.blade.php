{{-- @author SebastianBCF --}}
{{-- Vista standalone del ticket para previsualización (embebida en iframe).
     No extiende x-app-layout para evitar conflictos de CSS con el panel admin. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Forzamos esquema claro: el ticket es siempre en papel blanco --}}
    <meta name="color-scheme" content="light">
    <title>Previsualización del ticket — {{ $user->business_name ?? $user->name }}</title>
    @include('ticket-config.partials.preview-styles')
</head>
<body>
    <article class="ticket" aria-label="Previsualización del ticket de {{ $user->business_name ?? $user->name }}">

        <header class="ticket-header">
            @if($ticketConfig->hasLogo())
                <img src="{{ $ticketConfig->getLogoUrl() }}"
                     alt="Logo de {{ $user->business_name ?? $user->name }}"
                     class="logo">
            @endif
            <p class="business-name">{{ $user->business_name ?? $user->name }}</p>
            @if($ticketConfig->tax_id)
                <p class="tax-id">NIF/CIF: {{ $ticketConfig->tax_id }}</p>
            @endif
        </header>

        <hr aria-hidden="true">

        <p class="ticket-meta">
            <span>Mesa 3</span> · <time datetime="{{ now()->toIso8601String() }}">{{ now()->format('d/m/Y H:i') }}</time>
        </p>

        {{-- Líneas de ejemplo con lista semántica --}}
        <ul class="items-list" aria-label="Artículos del pedido de ejemplo">
            <li class="item-row">
                <span class="item-name">Agua mineral ×2</span>
                <span class="item-price">3,00 €</span>
            </li>
            <li class="item-row">
                <span class="item-name">Pizza Margarita ×1</span>
                <span class="item-price">12,50 €</span>
            </li>
            <li class="item-row">
                <span class="item-name">Café con leche ×1</span>
                <span class="item-price">1,80 €</span>
            </li>
        </ul>

        <hr aria-hidden="true">

        <div class="total-row" role="note" aria-label="Total: 17,30 euros">
            <span aria-hidden="true">TOTAL</span>
            <span>17,30 €</span>
        </div>

        <hr aria-hidden="true">

        <footer class="ticket-footer">
            {{ $ticketConfig->footer_text ?: 'Gracias por su visita.' }}
        </footer>

    </article>
</body>
</html>
