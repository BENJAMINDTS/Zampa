<!DOCTYPE html>
<html lang="es" data-theme="{{ $theme }}" id="carta-root">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Carta — {{ $table->user->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    @if($theme === 'classic')
    <link href="https://fonts.googleapis.com/css2?family=Marcellus&family=Spectral:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    @elseif($theme === 'minimal')
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=Archivo:wght@400;500;600&display=swap" rel="stylesheet">
    @else
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @endif
    <link rel="stylesheet" href="{{ asset('css/carta/colors_and_type.css') }}">
    <link rel="stylesheet" href="{{ asset('css/carta/styles.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="stripe-key" content="{{ $stripePublicKey }}">
    <script src="https://js.stripe.com/v3/" defer></script>

    <!-- Dark mode: apply before paint to avoid flash -->
    <script>
        (function () {
            const saved = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = saved === 'dark' || (!saved && prefersDark);
            if (isDark) {
                document.documentElement.classList.add('dark');
                document.getElementById('carta-root').setAttribute('data-theme', 'dark');
            }
        })();
    </script>

    <style>
        /* ── Zampi Chatbot Animations ────────────────────────────── */
        @keyframes zampiSlideUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes zampiFloat {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-5px); }
        }
        @keyframes zampiPulse {
            0%, 100% { opacity: 1; }
            50%      { opacity: 0.4; }
        }
        @keyframes zampiTyping {
            0%, 80%, 100% { opacity: 1; }
            40%           { opacity: 0.2; }
        }

        .zampi-float    { animation: zampiFloat 3s ease-in-out infinite; }
        .zampi-pulse    { animation: zampiPulse 2s ease-in-out infinite; }
        .zampi-msg-appear { animation: zampiSlideUp 0.3s ease; }
        .zampi-typing-1 { animation: zampiTyping 1.2s ease-in-out 0.0s infinite; }
        .zampi-typing-2 { animation: zampiTyping 1.2s ease-in-out 0.2s infinite; }
        .zampi-typing-3 { animation: zampiTyping 1.2s ease-in-out 0.4s infinite; }

        /* Notificaciones del cart bar — animación directa via :class */
        @keyframes zampiNotifIn {
            0%   { opacity: 0; transform: translateY(14px) scale(0.70); }
            65%  { opacity: 1; transform: translateY(-3px)  scale(1.06); }
            100% { opacity: 1; transform: translateY(0)     scale(1);    }
        }
        @keyframes zampiNotifOut {
            0%   { opacity: 1; transform: translateY(0)    scale(1);   }
            100% { opacity: 0; transform: translateY(8px)  scale(0.8); }
        }
        .zampi-notif-pill     { display:flex; align-items:center; gap:5px; background:#991b1b;
                                border:1px solid #ef4444; border-radius:9999px;
                                padding:4px 10px; flex-shrink:0; }
        .zampi-notif-pill-in  { animation: zampiNotifIn  0.40s cubic-bezier(0.34,1.56,0.64,1) both; }
        .zampi-notif-pill-out { animation: zampiNotifOut 0.22s ease-in both; }
        @media (max-width: 640px) {
            .zampi-cartbar-row { padding-top: 20px !important; padding-bottom: 20px !important; }
            .zampi-hidden-mobile { display: none !important; }
            .zampi-notif-bar-active { flex: 1 !important; min-width: 0; overflow: visible; }
            .zampi-notif-bar .zampi-notif-pill:not(:last-child) { display: none !important; }
            .zampi-notif-bar-active .zampi-notif-pill { width: 100%; min-width: 0; display: flex; align-items: center; gap: 5px; overflow: hidden; }
            .zampi-notif-name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .zampi-notif-suffix { flex-shrink: 0; white-space: nowrap; }
        }

        .zampi-bubble-user {
            background: linear-gradient(135deg, #2E50B0, #1A3380);
            color: #fff;
            border-radius: 18px 18px 4px 18px;
            box-shadow: 0 4px 16px rgba(15,31,88,0.55);
            max-width: 78%;
            padding: 10px 14px;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            word-break: break-word;
        }
        .zampi-bubble-bot {
            background: #0E1A38;
            border: 1px solid rgba(46,80,176,0.35);
            color: #C8D8FF;
            border-radius: 18px 18px 18px 4px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.3);
            max-width: 75%;
            padding: 10px 14px;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            word-break: break-word;
        }
        .zampi-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1A3380, #3070E8);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }
        .zampi-product-card {
            background: #0E1A38;
            border: 1px solid rgba(46,80,176,0.45);
            border-radius: 16px;
            padding: 10px;
            min-width: 140px;
            max-width: 160px;
            flex-shrink: 0;
            box-shadow: 0 4px 20px rgba(15,31,88,0.4);
            cursor: pointer;
            transition: transform 200ms ease;
        }
        .zampi-card-img {
            height: 56px;
            border-radius: 10px;
            background: linear-gradient(135deg, #162648, #0A1430);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 6px;
            font-size: 28px;
        }
        .zampi-add-btn {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2E50B0, #1A3380);
            border: none;
            color: #fff;
            font-size: 18px;
            line-height: 1;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 8px rgba(46,80,176,0.6);
            transition: transform 150ms cubic-bezier(0.34, 1.56, 0.64, 1);
            flex-shrink: 0;
        }
        .zampi-add-btn:hover  { transform: scale(1.1); }
        .zampi-add-btn:active { transform: scale(0.92); }
        .zampi-qr-btn {
            background: rgba(46,80,176,0.22);
            color: #8FA8E8;
            border: 1px solid rgba(46,80,176,0.5);
            border-radius: 9999px;
            padding: 5px 12px;
            font-size: 12px;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: all 150ms ease;
            white-space: nowrap;
        }
        .zampi-qr-btn:hover  { background: #1A3380; color: #fff; }
        .zampi-order-summary {
            background: rgba(14,26,56,0.9);
            border: 1px solid rgba(46,80,176,0.45);
            border-radius: 16px;
            padding: 12px;
            margin-top: 8px;
            backdrop-filter: blur(12px);
        }
        .zampi-send-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            background: linear-gradient(135deg, #2E50B0, #1A3380);
            color: #fff;
            font-size: 20px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 14px rgba(46,80,176,0.65);
            flex-shrink: 0;
            transition: transform 150ms cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .zampi-send-btn:disabled { opacity: 0.4; cursor: not-allowed; }
        .zampi-scrollbar::-webkit-scrollbar       { width: 4px; }
        .zampi-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .zampi-scrollbar::-webkit-scrollbar-thumb { background: rgba(46,80,176,0.5); border-radius: 4px; }
        .zampi-chat-input::placeholder            { color: rgba(84,120,208,0.7); }

        /* ── Zampi Chat — Layout ────────────────────────────────────── */

        /* El overlay cubre siempre toda la pantalla, sin importar el tamaño */
        .zampi-overlay {
            position: fixed;
            inset: 0;
            z-index: 50;
            background: rgba(1,4,14,0.92);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        .zampi-panel {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ── Carta DS — Layout crítico ──────────────────────────────── */
        /* Garantiza que .carta llene el viewport independientemente de Tailwind */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        .carta {
            height: 100dvh;
            height: 100vh; /* fallback */
            width: 100%;
            max-width: 100%;
        }
        /* Animación spin para el spinner de envío */
        @keyframes spin { to { transform: rotate(360deg); } }
        /* Animación pulse para el banner de tapas y otros indicadores */
        .banner-closed {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 10px 16px;
            background: var(--color-amber-100, #fef9c3);
            color: var(--color-amber-800, #92400e);
            border-bottom: 1px solid var(--color-amber-200, #fde68a);
            flex-shrink: 0;
        }
        /* category__closed necesita definición de color */
        .category__closed {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-family: var(--font-body);
            font-size: 11px;
            font-weight: 600;
            color: var(--color-amber-700, #b45309);
        }
        .category__closedDot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: currentColor;
            flex-shrink: 0;
        }
        /* drawer--wide se usa para cobro partido/mixto */
        .drawer--wide { max-height: 88dvh; max-height: 88vh; }
        /* citem__customTags */
        .citem__customTags {
            display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px;
        }
        .citem__customTag {
            padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 500;
            background: var(--bg-chip); color: var(--fg-muted);
            border: 1px solid var(--border-subtle);
        }
        .citem__customTag--extra { color: var(--color-green-700); background: var(--color-green-50); border-color: var(--color-green-200); }
        .citem__customTag--removed { color: var(--color-amber-700); background: var(--color-amber-50); border-color: var(--color-amber-200); }
        /* cashTip clases adicionales */
        .cashTip__spin {
            display: inline-block; width: 14px; height: 14px;
            border: 2px solid rgba(255,255,255,0.4); border-top-color: #fff;
            border-radius: 50%; animation: spin 0.8s linear infinite;
        }
        .cashTip__ctaTag {
            font-size: 11px; font-weight: 500; opacity: 0.75; margin-left: 4px;
        }
        /* tip clase (propina tarjeta) */
        .tip {
            flex: 1; padding: 14px 8px; border: 1px solid var(--glass-border);
            border-radius: 12px; background: var(--glass-bg-surface);
            display: flex; flex-direction: column; align-items: center; gap: 4px;
            cursor: pointer; transition: border-color 150ms, background 150ms;
        }
        .tip:hover { border-color: var(--border-strong); }
        .tip--selected { border-color: var(--brand-primary); background: var(--color-green-50); }
        .tip .pct { font-family: var(--font-display); font-weight: 900; font-size: 18px; color: var(--fg-primary); }
        .tip .amt { font-family: var(--font-body); font-size: 12px; color: var(--fg-muted); }
        .tip-row { display: flex; gap: 8px; }
        [data-theme="dark"] .tip--selected { background: rgba(34,197,94,0.1); }
        /* bill footRow */
        .bill__footRow { display: flex; gap: 8px; }
        .bill__footRow--stack { flex-direction: column; }
        /* cart-empty emojis */
        .cart-empty__emoji { font-size: 48px; line-height: 1; margin-bottom: 12px; display: block; }
        /* drawer__subtitle */
        .drawer__subtitle {
            font-family: var(--font-body); font-size: 12px; color: var(--fg-muted);
            margin-top: 2px; line-height: 1.4;
        }
        /* method clases */
        .method {
            display: grid;
            grid-template-columns: 44px 1fr auto;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            background: var(--glass-bg-surface);
            cursor: pointer;
            text-align: left;
            transition: border-color 150ms, transform 150ms;
            width: 100%;
        }
        .method:hover { border-color: var(--brand-primary); transform: translateY(-1px); }
        .method__ic {
            width: 44px; height: 44px; border-radius: 50%;
            background: var(--bg-chip);
            display: inline-flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .method__txt { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
        .method__nm { font-family: var(--font-body); font-weight: 700; font-size: 15px; color: var(--fg-primary); }
        .method__ds { font-family: var(--font-body); font-size: 12px; color: var(--fg-muted); }
        .method__chev { color: var(--fg-muted); flex-shrink: 0; transition: transform 150ms, color 150ms; }
        .method:hover .method__chev { transform: translateX(3px); color: var(--fg-primary); }
    </style>

    @php
        // Datos de productos para Alpine. Se calculan aquí para evitar que
        // @json() reciba una expresión multi-línea con corchetes anidados,
        // lo que confunde al parser de Blade.
        $productsForAlpine = $categories->flatMap(function ($category) {
            return $category->products->map(fn ($p) => [
                'id'          => $p->id,
                'name'        => $p->name,
                'price'       => $p->variants->isNotEmpty() ? (float) $p->variants->min('price') : (float) $p->price,
                'hasVariants' => $p->variants->isNotEmpty(),
                'variants'    => $p->variants->map(fn ($v) => [
                    'id'    => $v->id,
                    'name'  => $v->name,
                    'price' => (float) $v->price,
                ])->values(),
                'categoryId'  => $category->id,
                'destination' => $category->destination,
                'allergenTypes' => $p->ingredients->where('is_allergen', true)
                    ->flatMap(fn ($i) => $i->allergen_types ?? [])
                    ->unique()->values()->toArray(),
                'removable'   => $p->ingredients
                    ->filter(fn ($i) => $i->pivot->is_removable)
                    ->map(fn ($i) => ['id' => $i->id, 'name' => $i->name])
                    ->values(),
                'extras'      => $p->ingredients
                    ->filter(fn ($i) => $i->pivot->is_extra)
                    ->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'price' => (float) $i->pivot->extra_price])
                    ->values(),
            ]);
        })->values();

        // El precio de cada tapa se resuelve en backend (getPriceForProduct) para que
        // Alpine no tenga que replicar la lógica de modalidad de precio.
        $tapaProductsForAlpine = $tapaProducts->map(fn ($p) => [
            'id'    => $p->id,
            'name'  => $p->name,
            'price' => $tapaConfig ? (float) $tapaConfig->getPriceForProduct($p) : (float) $p->price,
        ])->values();

        $tapaConfigForAlpine = [
            'enabled'       => $tapaConfig?->tapas_enabled ?? false,
            'free'          => $tapaConfig?->tapas_free ?? true,
            'tapaPrice'     => (float) ($tapaConfig?->tapa_price ?? 0),
            'extraEnabled'  => $tapaConfig?->extra_tapa_enabled ?? false,
            'extraPrice'    => (float) ($tapaConfig?->extra_tapa_price ?? 0),
            'maxVariants'   => $tapaConfig?->max_tapa_variants ?? 0,
            'shouldSuggest' => $shouldSuggest,
            'variantsUsed'  => $tapaVariantsUsed,
            'barItemsCount' => (int) $barItemsCount,
            'kitchenOpen'   => $kitchenOpen,
        ];

        $menuContext = [
            'tableHash'           => $table->unique_hash,
            'hasActiveOrder'      => $hasActiveOrder,
            'billRequested'       => $billRequested,
            'activeOrderTotal'    => (float) $activeOrderTotal,
            'originalOrderTotal'  => (float) $originalOrderTotal,
            'splitPaymentEnabled' => $splitPaymentEnabled,
            'splitPaymentMaxParts'=> $splitPaymentMaxParts,
        ];
    @endphp

    {{-- Los datos se inyectan en un <script> separado para evitar conflictos
         de escapado al pasar JSON como argumento en x-data. --}}
    <script id="menu-products" type="application/json">@json($productsForAlpine)</script>
    <script id="tapa-config" type="application/json">@json($tapaConfigForAlpine)</script>
    <script id="tapa-products" type="application/json">@json($tapaProductsForAlpine)</script>
    <script id="order-items" type="application/json">@json($activeOrderItemsForAlpine)</script>
    <script id="menu-context" type="application/json">@json($menuContext)</script>
</head>

<body>

    {{-- Skip to content --}}
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4
              bg-white text-indigo-700 px-4 py-2 rounded font-medium z-50 shadow">
        Saltar al contenido principal
    </a>

    {{-- ── Componente Alpine raíz + wrapper DS ───────────────────── --}}
    <div x-data="menuFilters()"
         class="carta"
         data-theme="{{ $theme }}"
         x-init="
            $nextTick(() => {
                const update = () => {
                    const w = window.innerWidth;
                    $el.dataset.bp = w < 640 ? 'mobile' : w < 1024 ? 'tablet' : 'desktop';
                };
                update();
                window.addEventListener('resize', update);
            });
         ">

        {{-- ── Header DS ────────────────────────────────────────────── --}}
        <header class="header" role="banner">
            <div class="header__brand">
                <div class="header__logo" aria-hidden="true">
                    {{ mb_strtoupper(mb_substr($table->user->business_name ?: $table->user->name, 0, 2)) }}
                </div>
                <div>
                    <div class="header__bizname">
                        {{ $table->user->business_name ?: $table->user->name }}
                    </div>
                    <div class="header__table">{{ $table->name }}</div>
                </div>
            </div>
            <div class="header__actions">
                {{-- Estado cocina --}}
                @if(!$kitchenOpen && $nextOpeningTime)
                <span class="kitchen-pill kitchen-pill--closed" aria-label="Cocina cerrada">
                    <span class="kitchen-pill__dot"></span>
                    <span class="kitchen-pill__stack">
                        <span class="kitchen-pill__l1">Cocina cerrada</span>
                        <span class="kitchen-pill__l2">abre {{ $nextOpeningTime }}</span>
                    </span>
                </span>
                @endif

                {{-- Toggle dark/light — sincroniza Tailwind + DS data-theme --}}
                <button class="theme-toggle"
                        x-data="{
                            dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
                            toggle() {
                                this.dark = !this.dark;
                                document.documentElement.classList.toggle('dark', this.dark);
                                document.querySelector('.carta').dataset.theme = this.dark ? 'dark' : '{{ $theme }}';
                                localStorage.setItem('theme', this.dark ? 'dark' : 'light');
                            }
                        }"
                        @click="toggle()"
                        :aria-label="dark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
                        :aria-pressed="dark.toString()">
                    <svg x-show="dark" aria-hidden="true" width="18" height="18"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg x-show="!dark" aria-hidden="true" width="18" height="18"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>
            </div>
        </header>

        {{-- ── FAB Chat IA (Zampi Design System) ─────────────────── --}}
        <div x-data="chatWidget()">

            {{-- Mascot SVG symbol (Official Zampi Design System — zm- prefixed IDs) --}}
            <svg aria-hidden="true" style="display:none;position:absolute;width:0;height:0;overflow:hidden;">
                <symbol id="zampi-mascot" viewBox="0 0 120 110">
                    <defs>
                        <radialGradient id="zm-bT" cx="38%" cy="28%" r="62%">
                            <stop offset="0%" stop-color="#FBDF6A"/>
                            <stop offset="45%" stop-color="#E8980C"/>
                            <stop offset="100%" stop-color="#A05500"/>
                        </radialGradient>
                        <radialGradient id="zm-bB" cx="38%" cy="22%" r="65%">
                            <stop offset="0%" stop-color="#F5C830"/>
                            <stop offset="55%" stop-color="#CC7008"/>
                            <stop offset="100%" stop-color="#8B4000"/>
                        </radialGradient>
                        <linearGradient id="zm-ch" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#FFD740"/>
                            <stop offset="100%" stop-color="#F59000"/>
                        </linearGradient>
                        <linearGradient id="zm-mt" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#7B3010"/>
                            <stop offset="100%" stop-color="#4A1800"/>
                        </linearGradient>
                        <linearGradient id="zm-lt" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#5CC830"/>
                            <stop offset="100%" stop-color="#348010"/>
                        </linearGradient>
                        <radialGradient id="zm-sc" cx="50%" cy="38%" r="55%">
                            <stop offset="0%" stop-color="#0C0620"/>
                            <stop offset="100%" stop-color="#04000E"/>
                        </radialGradient>
                        <filter id="zm-sh"><feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="#3A1800" flood-opacity="0.45"/></filter>
                        <filter id="zm-gP"><feGaussianBlur stdDeviation="3.5" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge></filter>
                        <filter id="zm-gC"><feGaussianBlur stdDeviation="2" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge></filter>
                    </defs>
                    <ellipse cx="60" cy="107" rx="40" ry="4" fill="#2A0800" opacity="0.25"/>
                    <line x1="60" y1="2" x2="60" y2="22" stroke="#7A5010" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M60 3 L78 9 L60 16 Z" fill="#D80E1A"/>
                    <ellipse cx="68" cy="8" rx="4" ry="1.8" fill="white" opacity="0.25" transform="rotate(-10,68,8)"/>
                    <ellipse cx="60" cy="28" rx="48" ry="22" fill="url(#zm-bT)" filter="url(#zm-sh)"/>
                    <ellipse cx="44" cy="20" rx="16" ry="8" fill="white" opacity="0.25" transform="rotate(-10,44,20)"/>
                    <ellipse cx="42" cy="16" rx="4.5" ry="1.8" fill="#FFF0A0" opacity="0.9" transform="rotate(-18,42,16)"/>
                    <ellipse cx="60" cy="12" rx="4.5" ry="1.8" fill="#FFF0A0" opacity="0.9"/>
                    <ellipse cx="78" cy="17" rx="4.5" ry="1.8" fill="#FFF0A0" opacity="0.9" transform="rotate(18,78,17)"/>
                    <ellipse cx="32" cy="26" rx="4" ry="1.6" fill="#FFF0A0" opacity="0.85" transform="rotate(-22,32,26)"/>
                    <ellipse cx="88" cy="27" rx="4" ry="1.6" fill="#FFF0A0" opacity="0.85" transform="rotate(22,88,27)"/>
                    <ellipse cx="60" cy="48" rx="48" ry="6" fill="#B06010"/>
                    <path d="M12 51 Q28 46 42 53 Q54 59 60 52 Q66 45 78 53 Q92 60 108 51 L108 59 Q92 66 78 60 Q66 54 60 60 Q54 66 42 60 Q28 53 12 59 Z" fill="url(#zm-ch)"/>
                    <path d="M16 55 C11 60 11 68 16 71 L19 71 C15 66 16 55 16 55 Z" fill="#FBBF24"/>
                    <ellipse cx="16.5" cy="71.5" rx="3" ry="2" fill="#F59000"/>
                    <path d="M104 55 C109 60 109 68 104 71 L101 71 C105 66 104 55 104 55 Z" fill="#FBBF24"/>
                    <ellipse cx="103.5" cy="71.5" rx="3" ry="2" fill="#F59000"/>
                    <path d="M12 59 Q22 55 34 62 Q46 68 60 61 Q74 54 86 62 Q98 68 108 59 L108 65 Q98 73 86 66 Q74 60 60 66 Q46 72 34 66 Q22 60 12 65 Z" fill="url(#zm-lt)"/>
                    <ellipse cx="60" cy="70" rx="48" ry="9" fill="url(#zm-mt)" filter="url(#zm-sh)"/>
                    <ellipse cx="60" cy="84" rx="48" ry="14" fill="url(#zm-bB)" filter="url(#zm-sh)"/>
                    <ellipse cx="60" cy="95" rx="43" ry="8" fill="#8B4000"/>
                    <ellipse cx="60" cy="101" rx="36" ry="5" fill="#6A3000"/>
                    <ellipse cx="46" cy="80" rx="16" ry="5" fill="white" opacity="0.15"/>
                    <ellipse cx="60" cy="66" rx="34" ry="28" fill="#6010B0" opacity="0.45" filter="url(#zm-gP)"/>
                    <ellipse cx="60" cy="66" rx="32" ry="26" fill="#40087A" stroke="#CC60F8" stroke-width="3"/>
                    <ellipse cx="60" cy="66" rx="29" ry="23" fill="#2C0660" stroke="#7828B8" stroke-width="1.2"/>
                    <ellipse cx="60" cy="66" rx="27" ry="21" fill="url(#zm-sc)"/>
                    <rect x="34" y="54" width="18" height="20" rx="7" fill="#22D3EE" opacity="0.25" filter="url(#zm-gC)"/>
                    <rect x="35" y="55" width="16" height="18" rx="6" fill="#030E1A"/>
                    <rect x="36" y="56" width="14" height="16" rx="5" fill="#22D3EE"/>
                    <ellipse cx="39" cy="58.5" rx="3.5" ry="2" fill="white" opacity="0.65"/>
                    <rect x="35" y="55" width="16" height="18" rx="6" fill="none" stroke="#A5F3FC" stroke-width="0.8" opacity="0.8"/>
                    <rect x="68" y="54" width="18" height="20" rx="7" fill="#22D3EE" opacity="0.25" filter="url(#zm-gC)"/>
                    <rect x="69" y="55" width="16" height="18" rx="6" fill="#030E1A"/>
                    <rect x="70" y="56" width="14" height="16" rx="5" fill="#22D3EE"/>
                    <ellipse cx="73" cy="58.5" rx="3.5" ry="2" fill="white" opacity="0.65"/>
                    <rect x="69" y="55" width="16" height="18" rx="6" fill="none" stroke="#A5F3FC" stroke-width="0.8" opacity="0.8"/>
                    <path d="M51 79 Q60 86 69 79" fill="none" stroke="#22D3EE" stroke-width="4" stroke-linecap="round" opacity="0.2"/>
                    <path d="M51 79 Q60 86 69 79" fill="none" stroke="#22D3EE" stroke-width="2" stroke-linecap="round"/>
                </symbol>
            </svg>

            {{-- Sprite SVG: 14 alérgenos Reglamento UE 1169/2011 --}}
            <svg aria-hidden="true" style="display:none;position:absolute;width:0;height:0;overflow:hidden;">
                {{-- 1. Gluten (cereales con gluten) --}}
                <symbol id="al-gluten" viewBox="0 0 24 24" fill="currentColor">
                    <rect x="11.2" y="4" width="1.6" height="16" rx="0.8"/>
                    <ellipse cx="12" cy="6.5" rx="2.8" ry="4"/>
                    <ellipse cx="7" cy="11" rx="2.2" ry="3.2" transform="rotate(-30 7 11)"/>
                    <ellipse cx="17" cy="11" rx="2.2" ry="3.2" transform="rotate(30 17 11)"/>
                    <ellipse cx="7.5" cy="16.5" rx="2" ry="2.8" transform="rotate(-25 7.5 16.5)"/>
                    <ellipse cx="16.5" cy="16.5" rx="2" ry="2.8" transform="rotate(25 16.5 16.5)"/>
                </symbol>
                {{-- 2. Crustáceos --}}
                <symbol id="al-crustaceans" viewBox="0 0 24 24">
                    <circle cx="18" cy="5" r="2.5" fill="currentColor"/>
                    <path d="M18 3 L22 1.5 M18.8 2 L21.5 0" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                    <path d="M18 5 C21 8 21 15 17 19 C14 22 9 22 6 19 C3 16 4 12 7 11 C10 10 12 12 11 15 C10.5 16.5 9.5 16.5 9 15.5"
                          stroke="currentColor" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                </symbol>
                {{-- 3. Huevo --}}
                <symbol id="al-egg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2 C8 2 5 6 5 11 C5 16.5 8.1 22 12 22 C15.9 22 19 16.5 19 11 C19 6 16 2 12 2 Z"/>
                </symbol>
                {{-- 4. Pescado --}}
                <symbol id="al-fish" viewBox="0 0 24 24" fill="currentColor">
                    <ellipse cx="9.5" cy="12" rx="7.5" ry="5.5"/>
                    <path d="M17 6.5 L24 4 L24 20 L17 17.5 Z"/>
                    <circle cx="5" cy="11" r="1.4" fill="#050B1F"/>
                </symbol>
                {{-- 5. Cacahuetes --}}
                <symbol id="al-peanuts" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2 C7 2 4 4.5 4 7.5 C4 10.5 6.5 12 9.5 12.3 Q9 12.8 9 13.5 Q9 14 9.5 14.2 C6.5 14.8 4 16.5 4 19 C4 21.5 7.5 23 12 23 C16.5 23 20 21.5 20 19 C20 16.5 17.5 14.8 14.5 14.2 Q15 14 15 13.5 Q15 12.8 14.5 12.3 C17.5 12 20 10.5 20 7.5 C20 4.5 17 2 12 2 Z"/>
                </symbol>
                {{-- 6. Soja --}}
                <symbol id="al-soy" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="6.5" r="4"/>
                    <circle cx="7" cy="15.5" r="4"/>
                    <circle cx="17" cy="15.5" r="4"/>
                    <path d="M12 10 L7 12 M12 10 L17 12" stroke="currentColor" stroke-width="1.5" fill="none"/>
                </symbol>
                {{-- 7. Leche / Lácteos --}}
                <symbol id="al-milk" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2 L19 13 C19 18.5 15.9 22 12 22 C8.1 22 5 18.5 5 13 Z"/>
                </symbol>
                {{-- 8. Frutos secos (nueces, almendras…) --}}
                <symbol id="al-nuts" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2 C10 2 8.5 3.5 8 5 L6 8 C5 9 5 10 5 11 C5 15 8.1 20 12 20 C15.9 20 19 15 19 11 C19 10 19 9 18 8 L16 5 C15.5 3.5 14 2 12 2 Z"/>
                    <path d="M12 20 L12 22 M10 21.5 L14 21.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                </symbol>
                {{-- 9. Apio --}}
                <symbol id="al-celery" viewBox="0 0 24 24" fill="currentColor">
                    <rect x="4.5" y="11" width="3" height="10" rx="1.5"/>
                    <rect x="10.5" y="8" width="3" height="13" rx="1.5"/>
                    <rect x="16.5" y="11" width="3" height="10" rx="1.5"/>
                    <path d="M6 11 C5 7 3.5 5 6 3 C8 4 8 8.5 6 11 Z"/>
                    <path d="M12 8 C11 4 9.5 2 12 1 C14 2 14 5.5 12 8 Z"/>
                    <path d="M18 11 C19 7 20.5 5 18 3 C16 4 16 8.5 18 11 Z"/>
                </symbol>
                {{-- 10. Mostaza --}}
                <symbol id="al-mustard" viewBox="0 0 24 24" fill="currentColor">
                    <rect x="8" y="11" width="8" height="10" rx="1.5"/>
                    <rect x="9" y="8" width="6" height="3.5" rx="1"/>
                    <rect x="9.5" y="5.5" width="5" height="3" rx="1.5"/>
                    <circle cx="12" cy="4" r="1.2"/>
                </symbol>
                {{-- 11. Sésamo --}}
                <symbol id="al-sesame" viewBox="0 0 24 24" fill="currentColor">
                    <ellipse cx="12" cy="4.5" rx="2" ry="3.5"/>
                    <ellipse cx="17.5" cy="7.5" rx="2" ry="3.5" transform="rotate(60 17.5 7.5)"/>
                    <ellipse cx="19.5" cy="14" rx="2" ry="3.5" transform="rotate(120 19.5 14)"/>
                    <ellipse cx="15" cy="19.5" rx="2" ry="3.5" transform="rotate(180 15 19.5)"/>
                    <ellipse cx="9" cy="19.5" rx="2" ry="3.5" transform="rotate(240 9 19.5)"/>
                    <ellipse cx="4.5" cy="14" rx="2" ry="3.5" transform="rotate(300 4.5 14)"/>
                </symbol>
                {{-- 12. Dióxido de azufre / Sulfitos --}}
                <symbol id="al-sulphites" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7 3 L17 3 C17 8.5 14.5 10.5 13 12 L13 17 L15 17 L15 20 L9 20 L9 17 L11 17 L11 12 C9.5 10.5 7 8.5 7 3 Z"/>
                </symbol>
                {{-- 13. Altramuces --}}
                <symbol id="al-lupin" viewBox="0 0 24 24" fill="currentColor">
                    <rect x="11.2" y="4" width="1.6" height="16" rx="0.8"/>
                    <ellipse cx="12" cy="6" rx="3.5" ry="2.2"/>
                    <ellipse cx="12" cy="10.5" rx="4" ry="2.5"/>
                    <ellipse cx="12" cy="15" rx="3.5" ry="2.2"/>
                    <ellipse cx="12" cy="18.5" rx="2.5" ry="1.8"/>
                </symbol>
                {{-- 14. Moluscos --}}
                <symbol id="al-molluscs" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 22 L3 9 Q3 4 12 3 Q21 4 21 9 Z"/>
                    <path d="M12 22 L4.5 8.5 M12 22 L7.5 4.5 M12 22 L12 3 M12 22 L16.5 4.5 M12 22 L19.5 8.5"
                          stroke="#050B1F" stroke-width="0.9" fill="none"/>
                </symbol>
            </svg>

            {{-- Sprite SVG: categorías de tipo de alimento --}}
            <svg aria-hidden="true" style="display:none;position:absolute;width:0;height:0;overflow:hidden;">
                <symbol id="fc-pizza" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 3 L21 20 L3 20 Z"/>
                    <ellipse cx="12" cy="20" rx="9" ry="2" opacity="0.5"/>
                    <circle cx="12" cy="15" r="1.8" opacity="0.45"/>
                    <circle cx="9"  cy="11" r="1.3" opacity="0.45"/>
                    <circle cx="15" cy="11" r="1.3" opacity="0.45"/>
                </symbol>
                <symbol id="fc-burger" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M4 9 Q12 5 20 9 L20 11 L4 11 Z"/>
                    <rect x="3" y="12" width="18" height="3"   rx="1.5"/>
                    <rect x="3" y="16" width="18" height="4.5" rx="2.5"/>
                </symbol>
                <symbol id="fc-pasta" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 14 Q3 22 12 22 Q21 22 21 14 Z"/>
                    <rect x="2" y="12" width="20" height="2.5" rx="1.25"/>
                    <rect x="11" y="3" width="1.8" height="9" rx="0.9"/>
                    <rect x="8.2" y="3" width="1.4" height="5.5" rx="0.7"/>
                    <rect x="14.4" y="3" width="1.4" height="5.5" rx="0.7"/>
                    <path d="M9.6 8.5 Q12 10.5 14.4 8.5" fill="none" stroke="currentColor" stroke-width="1.3"/>
                </symbol>
                <symbol id="fc-salad" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 14 Q3 21 12 21 Q21 21 21 14 Z"/>
                    <rect x="2" y="12" width="20" height="2.5" rx="1.25"/>
                    <path d="M8 12 C6 7 10 4 12 8 C14 4 18 7 16 12"/>
                    <path d="M10.5 12 C11.5 9.5 12.5 9.5 13.5 12" opacity="0.6"/>
                </symbol>
                <symbol id="fc-soup" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 14 Q3 22 12 22 Q21 22 21 14 Z"/>
                    <rect x="2" y="12" width="20" height="2.5" rx="1.25"/>
                    <path d="M8  10 C9  7 8  5 9  3" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    <path d="M12  9 C13 6 12 4 13 2" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    <path d="M16 10 C17 7 16 5 17 3" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                </symbol>
                <symbol id="fc-starter" viewBox="0 0 24 24" fill="currentColor">
                    <ellipse cx="12" cy="20" rx="10" ry="2.5"/>
                    <path d="M2 20 Q2 9 12 9 Q22 9 22 20"/>
                    <circle cx="12" cy="9" r="2.2"/>
                </symbol>
                <symbol id="fc-tapas" viewBox="0 0 24 24" fill="currentColor">
                    <ellipse cx="12" cy="21" rx="10" ry="2.5"/>
                    <circle cx="8"  cy="14" r="3.5"/>
                    <circle cx="12" cy="12" r="3.5" opacity="0.8"/>
                    <circle cx="16" cy="14" r="3.5" opacity="0.6"/>
                    <rect x="11.3" y="6" width="1.4" height="7" rx="0.7"/>
                </symbol>
                <symbol id="fc-dessert" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M6 12 Q12 5 18 12 Z"/>
                    <path d="M5 12 L7 21 L17 21 L19 12 Z"/>
                    <rect x="5" y="15.5" width="14" height="1.2" rx="0.6" opacity="0.35"/>
                    <rect x="5" y="18"   width="14" height="1.2" rx="0.6" opacity="0.35"/>
                    <circle cx="12" cy="7" r="2.5" opacity="0.7"/>
                </symbol>
                <symbol id="fc-drink" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7 4 L6 21 L18 21 L17 4 Z"/>
                    <rect x="14" y="2" width="2.2" height="13" rx="1.1" opacity="0.55"/>
                </symbol>
                <symbol id="fc-beer" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M5 9 L5 22 Q5 23 7 23 L16 23 Q18 23 18 22 L18 9 Z"/>
                    <path d="M18 11 Q23 11 23 15 Q23 19 18 19" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round"/>
                    <path d="M5 9 Q7 5 9 9 Q11 5 13 9 Q15 5 17 9 Q18 7 18 9"/>
                </symbol>
                <symbol id="fc-wine" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7 3 L17 3 C17 9.5 14 12 13 13.5 L13 18 L15 18 L15 21 L9 21 L9 18 L11 18 L11 13.5 C10 12 7 9.5 7 3 Z"/>
                </symbol>
                <symbol id="fc-cocktail" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 3 L21 3 L12 15 Z"/>
                    <rect x="11" y="15" width="2"  height="5"/>
                    <rect x="8"  y="20" width="8"  height="2.5" rx="1.25"/>
                    <circle cx="17" cy="4" r="1.8" opacity="0.65"/>
                </symbol>
                <symbol id="fc-meat" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M4 15 C3 10 6 6 10 6 C10 4 12 3 14 5 C17 4 21 7 21 12 C21 17 18 21 12 21 C7 21 5 19 4 15 Z"/>
                    <path d="M10 6 Q7 10 8 15" fill="none" stroke="currentColor" stroke-width="1" opacity="0.3"/>
                </symbol>
                <symbol id="fc-fish-dish" viewBox="0 0 26 22" fill="currentColor">
                    <ellipse cx="10" cy="11" rx="8" ry="5.5"/>
                    <path d="M18 6 L26 3 L26 19 L18 16 Z"/>
                    <circle cx="4.5" cy="10" r="1.6" opacity="0.3"/>
                </symbol>
                <symbol id="fc-seafood" viewBox="0 0 24 24">
                    <circle cx="18" cy="5" r="2.5" fill="currentColor"/>
                    <path d="M18 3 L22 1.5 M18.8 2 L21.5 0" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                    <path d="M18 5 C21 8 21 15 17 19 C14 22 9 22 6 19 C3 16 4 12 7 11 C10 10 12 12 11 15 C10.5 16.5 9.5 16.5 9 15.5"
                          stroke="currentColor" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                </symbol>
                <symbol id="fc-sandwich" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 8 Q12 4 21 8 L21 11 L3 11 Z"/>
                    <rect x="3" y="11"   width="18" height="2.5" rx="0.5" opacity="0.7"/>
                    <rect x="3" y="13.5" width="18" height="2"   rx="0.5" opacity="0.5"/>
                    <rect x="3" y="16"   width="18" height="4.5" rx="2.5"/>
                </symbol>
                <symbol id="fc-sushi" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="12" r="9"/>
                    <circle cx="12" cy="12" r="5.5" opacity="0.45"/>
                    <circle cx="12" cy="12" r="2.5"/>
                </symbol>
                <symbol id="fc-rice" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 13 Q3 21 12 21 Q21 21 21 13 Z"/>
                    <rect x="2" y="11" width="20" height="2.5" rx="1.25"/>
                    <circle cx="9"  cy="9"  r="1.3" opacity="0.8"/>
                    <circle cx="13" cy="8"  r="1.3" opacity="0.8"/>
                    <circle cx="11" cy="6"  r="1.3" opacity="0.8"/>
                    <circle cx="16" cy="10" r="1.1" opacity="0.6"/>
                    <circle cx="7"  cy="7"  r="1.1" opacity="0.6"/>
                </symbol>
                <symbol id="fc-vegan" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 22 C12 22 4 15 4 9 C4 4 8 2 12 2 C16 2 20 4 20 9 C20 15 12 22 12 22 Z"/>
                    <path d="M12 22 L12 9" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.3"/>
                    <path d="M12 14 L8 10 M12 18 L16 14" fill="none" stroke="currentColor" stroke-width="1" opacity="0.25"/>
                </symbol>
            </svg>

            {{-- Botón flotante Zampi (se oculta cuando el chat está abierto) --}}
            <button type="button"
                    @click="openChat()"
                    aria-label="Abrir asistente Zampi"
                    x-show="!open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed bottom-20 right-4 z-40 flex items-center gap-2 px-4 py-2.5 rounded-full font-semibold focus:outline-none focus:ring-4"
                    style="background:linear-gradient(135deg,#2E50B0,#1A3380); color:#fff; box-shadow:0 0 20px rgba(46,80,176,0.65),0 4px 16px rgba(15,31,88,0.55); transition:transform 200ms ease, box-shadow 200ms ease;"
                    onmouseenter="this.style.transform='scale(1.05)'; this.style.boxShadow='0 0 30px rgba(46,80,176,0.9),0 4px 20px rgba(15,31,88,0.65)';"
                    onmouseleave="this.style.transform='scale(1)'; this.style.boxShadow='0 0 20px rgba(46,80,176,0.65),0 4px 16px rgba(15,31,88,0.55)';">
                <div class="zampi-float flex-shrink-0">
                    <svg width="26" height="24" aria-hidden="true"><use href="#zampi-mascot"/></svg>
                </div>
                <span style="font-family:'Nunito',sans-serif; font-weight:800; font-size:14px;">Zampi</span>
                <template x-if="cartCount > 0">
                    <span x-text="cartCount"
                          style="position:absolute; top:-8px; right:-8px; min-width:20px; height:20px; border-radius:9999px; background:#FBBF24; color:#050B1F; font-size:11px; font-weight:900; display:flex; align-items:center; justify-content:center; padding:0 4px; font-family:'Nunito',sans-serif; box-shadow:0 0 8px rgba(251,191,36,0.6); pointer-events:none;"></span>
                </template>
            </button>

            {{-- Overlay + Panel (layout responsive vía .zampi-overlay / .zampi-panel) --}}
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="zampi-overlay"
                 @keydown.escape.window="closeChat()"
                 aria-modal="true" role="dialog" aria-label="Asistente virtual Zampi">

                {{-- Panel: full-screen en móvil / modal en tablet / flotante en desktop --}}
                <div class="zampi-panel zampi-scrollbar"
                     style="background:radial-gradient(ellipse at 50% 20%,#0E1A38 0%,#050B1F 60%,#01040E 100%);">

                    {{-- Cabecera --}}
                    <div style="flex-shrink:0; padding:12px 16px; background:rgba(10,20,48,0.9); backdrop-filter:blur(16px); border-bottom:1px solid rgba(46,80,176,0.4); display:flex; align-items:center; gap:10px;">
                        <div class="zampi-float" style="flex-shrink:0;">
                            <svg width="38" height="35" aria-hidden="true"><use href="#zampi-mascot"/></svg>
                        </div>
                        <div style="flex:1;">
                            <h2 style="font-family:'Nunito',sans-serif; font-weight:900; font-size:16px; color:#fff; line-height:1.2; margin:0;">Zampi</h2>
                            <p style="font-size:11px; color:#8FA8E8; letter-spacing:0.03em; margin:0;">{{ $table->name }} · {{ $table->user->business_name ?: $table->user->name }}</p>
                        </div>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="display:flex; align-items:center; gap:5px;">
                                <div class="zampi-pulse" style="width:7px; height:7px; border-radius:50%; background:#22C55E; box-shadow:0 0 6px #22C55E;"></div>
                                <span style="font-size:11px; color:#22C55E; font-weight:600; font-family:'Space Grotesk',sans-serif;">En línea</span>
                            </div>
                            <button type="button"
                                    @click="closeChat()"
                                    aria-label="Cerrar asistente Zampi"
                                    style="padding:6px; border-radius:50%; border:1px solid rgba(46,80,176,0.4); background:transparent; color:#8FA8E8; cursor:pointer; transition:all 150ms ease; display:flex; align-items:center; justify-content:center;"
                                    onmouseenter="this.style.background='rgba(46,80,176,0.25)'; this.style.color='#fff';"
                                    onmouseleave="this.style.background='transparent'; this.style.color='#8FA8E8';">
                                <svg aria-hidden="true" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Log de mensajes --}}
                    <div x-ref="chatLog"
                         role="log"
                         aria-live="polite"
                         aria-label="Conversación con Zampi"
                         @wheel.stop
                         @touchmove.stop
                         class="zampi-scrollbar"
                         style="flex:1; overflow-y:auto; padding:16px; display:flex; flex-direction:column; gap:12px;">

                        <template x-for="msg in messages" :key="msg._id">
                            <div class="zampi-msg-appear">

                                {{-- Sistema --}}
                                <template x-if="msg.type === 'system'">
                                    <div style="display:flex; justify-content:center;">
                                        <div style="background:rgba(46,80,176,0.22); border:1px solid rgba(46,80,176,0.3); color:#8FA8E8; font-size:11px; padding:5px 14px; border-radius:9999px; backdrop-filter:blur(8px); font-family:'Space Grotesk',sans-serif;"
                                             x-text="msg.text"></div>
                                    </div>
                                </template>

                                {{-- Bot --}}
                                <template x-if="msg.type === 'bot'">
                                    <div style="display:flex; gap:8px; align-items:flex-end;">
                                        <div class="zampi-avatar" style="flex-shrink:0;">
                                            <svg width="24" height="22" aria-hidden="true"><use href="#zampi-mascot"/></svg>
                                        </div>
                                        <div style="max-width:75%; min-width:0;">
                                            <div style="background:#0E1A38; border:1px solid rgba(46,80,176,0.35); color:#C8D8FF; font-size:14px; line-height:1.6; padding:10px 14px; border-radius:18px 18px 18px 4px; box-shadow:0 2px 12px rgba(0,0,0,0.3); font-family:'Space Grotesk',sans-serif; white-space:pre-line;"
                                                 x-text="msg.text"></div>
                                            {{-- Tarjetas de producto --}}
                                            <template x-if="msg.cards && msg.cards.length">
                                                <div style="display:flex; gap:8px; margin-top:8px; overflow-x:auto; padding-bottom:4px;">
                                                    <template x-for="card in msg.cards" :key="card.id">
                                                        <div style="background:#0E1A38; border:1px solid rgba(46,80,176,0.45); border-radius:16px; padding:10px; min-width:150px; max-width:160px; flex-shrink:0; box-shadow:0 4px 20px rgba(15,31,88,0.4); transition:transform 200ms ease; display:flex; flex-direction:column;"
                                                             onmouseenter="this.style.transform='translateY(-2px)'"
                                                             onmouseleave="this.style.transform='translateY(0)'">
                                                            {{-- Foto del plato o icono de categoría --}}
                                                            <template x-if="card.image">
                                                                <img :src="card.image"
                                                                     :alt="card.name"
                                                                     style="width:100%; height:72px; object-fit:cover; border-radius:10px; margin-bottom:8px; flex-shrink:0; display:block;"
                                                                     loading="lazy"/>
                                                            </template>
                                                            <template x-if="!card.image">
                                                                <div style="height:56px; border-radius:10px; background:linear-gradient(135deg,#162648,#0A1430); display:flex; align-items:center; justify-content:center; margin-bottom:8px; flex-shrink:0;">
                                                                    <template x-if="card.foodIcon">
                                                                        <svg width="34" height="34" viewBox="0 0 24 24"
                                                                             aria-hidden="true"
                                                                             style="color:rgba(139,168,232,0.6); overflow:visible;">
                                                                            <use :href="'#fc-' + card.foodIcon.svgId"/>
                                                                        </svg>
                                                                    </template>
                                                                    <template x-if="!card.foodIcon">
                                                                        <span x-text="card.emoji" style="font-size:26px;"></span>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            {{-- Nombre --}}
                                                            <div style="font-size:12px; font-weight:700; color:#fff; margin-bottom:3px; font-family:'Space Grotesk',sans-serif; line-height:1.3;"
                                                                 x-text="card.name"></div>
                                                            {{-- Descripción --}}
                                                            <div x-show="card.description"
                                                                 style="font-size:10px; color:#8FA8E8; margin-bottom:5px; line-height:1.4; flex-grow:1;"
                                                                 x-text="card.description"></div>
                                                            {{-- Alérgenos UE encima del precio --}}
                                                            <template x-if="card.allergens && card.allergens.length">
                                                                <div style="display:flex; flex-wrap:wrap; gap:3px; margin-bottom:7px;">
                                                                    <template x-for="al in card.allergens" :key="al.name">
                                                                        <div :title="al.name"
                                                                             style="display:inline-flex; align-items:center; gap:3px; background:rgba(245,158,11,0.12); border:1px solid rgba(245,158,11,0.35); border-radius:5px; padding:2px 6px 2px 4px; cursor:default;">
                                                                            {{-- Icono SVG oficial UE, o fallback ⚠ si no hay mapeo --}}
                                                                            <template x-if="al.svgId">
                                                                                <svg :aria-label="al.label" width="13" height="13"
                                                                                     style="flex-shrink:0; color:#FCD34D; overflow:visible;">
                                                                                    <use :href="'#al-' + al.svgId"/>
                                                                                </svg>
                                                                            </template>
                                                                            <template x-if="!al.svgId">
                                                                                <span style="font-size:11px; line-height:1; color:#FCD34D;">⚠</span>
                                                                            </template>
                                                                            <span x-text="al.label"
                                                                                  style="font-size:8px; color:#FCD34D; font-weight:700; font-family:'Space Grotesk',sans-serif; letter-spacing:0.02em;"></span>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            {{-- Precio + controles cantidad --}}
                                                            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:auto;">
                                                                <span style="font-size:13px; font-weight:700; color:#FBBF24; font-family:'Nunito',sans-serif;"
                                                                      x-text="Number(card.price).toFixed(2).replace('.',',') + ' €'"></span>
                                                                {{-- Sin cantidad: solo botón + --}}
                                                                <template x-if="cartQty(card.id) === 0">
                                                                    <button type="button"
                                                                            @click.stop="addToCart(card)"
                                                                            :aria-label="'Añadir ' + card.name + ' al pedido'"
                                                                            style="width:26px; height:26px; border-radius:50%; background:linear-gradient(135deg,#2E50B0,#1A3380); border:2px solid transparent; color:#fff; font-size:17px; line-height:1; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 0 8px rgba(46,80,176,0.6); flex-shrink:0; transition:all 150ms ease;"
                                                                            onmouseenter="this.style.background='#3D6AE0'; this.style.boxShadow='0 0 12px rgba(34,197,94,0.7)'; this.style.borderColor='#22C55E'; this.style.transform='scale(1.15)';"
                                                                            onmouseleave="this.style.background='linear-gradient(135deg,#2E50B0,#1A3380)'; this.style.boxShadow='0 0 8px rgba(46,80,176,0.6)'; this.style.borderColor='transparent'; this.style.transform='scale(1)';"
                                                                            onmousedown="this.style.borderColor='#16A34A'; this.style.transform='scale(0.9)';"
                                                                            onmouseup="this.style.borderColor='#22C55E'; this.style.transform='scale(1.15)';">+</button>
                                                                </template>
                                                                {{-- Con cantidad: controles [-] [n] [+] --}}
                                                                <template x-if="cartQty(card.id) > 0">
                                                                    <div style="display:flex; align-items:center; gap:5px;">
                                                                        <button type="button"
                                                                                @click.stop="decreaseQty(card)"
                                                                                :aria-label="'Quitar uno de ' + card.name"
                                                                                style="width:24px; height:24px; border-radius:50%; background:rgba(46,80,176,0.35); border:2px solid transparent; color:#fff; font-size:16px; line-height:1; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:all 150ms ease;"
                                                                                onmouseenter="this.style.background='#2E50B0'; this.style.borderColor='#EF4444'; this.style.boxShadow='0 0 10px rgba(239,68,68,0.6)'; this.style.transform='scale(1.15)';"
                                                                                onmouseleave="this.style.background='rgba(46,80,176,0.35)'; this.style.borderColor='transparent'; this.style.boxShadow='none'; this.style.transform='scale(1)';"
                                                                                onmousedown="this.style.borderColor='#B91C1C'; this.style.boxShadow='0 0 14px rgba(185,28,28,0.8)'; this.style.transform='scale(0.9)';"
                                                                                onmouseup="this.style.borderColor='#EF4444'; this.style.boxShadow='0 0 10px rgba(239,68,68,0.6)'; this.style.transform='scale(1.15)';">−</button>
                                                                        <span x-text="cartQty(card.id)"
                                                                              style="font-size:13px; font-weight:800; color:#fff; font-family:'Nunito',sans-serif; min-width:14px; text-align:center;"></span>
                                                                        <button type="button"
                                                                                @click.stop="addToCart(card)"
                                                                                :aria-label="'Añadir otro de ' + card.name"
                                                                                style="width:24px; height:24px; border-radius:50%; background:linear-gradient(135deg,#2E50B0,#1A3380); border:2px solid transparent; color:#fff; font-size:17px; line-height:1; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 0 8px rgba(46,80,176,0.6); flex-shrink:0; transition:all 150ms ease;"
                                                                                onmouseenter="this.style.background='#3D6AE0'; this.style.boxShadow='0 0 12px rgba(34,197,94,0.7)'; this.style.borderColor='#22C55E'; this.style.transform='scale(1.15)';"
                                                                                onmouseleave="this.style.background='linear-gradient(135deg,#2E50B0,#1A3380)'; this.style.boxShadow='0 0 8px rgba(46,80,176,0.6)'; this.style.borderColor='transparent'; this.style.transform='scale(1)';"
                                                                                onmousedown="this.style.borderColor='#16A34A'; this.style.transform='scale(0.9)';"
                                                                                onmouseup="this.style.borderColor='#22C55E'; this.style.transform='scale(1.15)';">+</button>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            {{-- Quick replies --}}
                                            <template x-if="msg.quickReplies && msg.quickReplies.length">
                                                <div style="display:flex; gap:6px; flex-wrap:wrap; margin-top:8px;">
                                                    <template x-for="(qr, qrIdx) in msg.quickReplies" :key="qrIdx">
                                                        <button type="button"
                                                                @click="handleQuickReply(qr)"
                                                                :style="getQrStyle(qr) + 'display:inline-flex;align-items:center;gap:4px;'"
                                                                @mouseenter="onQrEnter($el, qr)"
                                                                @mouseleave="onQrLeave($el, qr)">
                                                                <template x-if="qr === 'Ver mi pedido'">
                                                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                                </template>
                                                                <template x-if="qr === 'Confirmar pedido'">
                                                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                                </template>
                                                                <span x-text="qr"></span>
                                                            </button>
                                                    </template>
                                                </div>
                                            </template>
                                            {{-- Resumen del pedido en vivo (reactivo al store) --}}
                                            <template x-if="msg.cartLive">
                                                <div style="background:rgba(14,26,56,0.9); border:1px solid rgba(46,80,176,0.45); border-radius:16px; padding:12px; margin-top:8px; backdrop-filter:blur(12px);">
                                                    <div style="font-size:13px; font-weight:800; font-family:'Nunito',sans-serif; color:#fff; margin-bottom:8px;">🛒 Tu pedido</div>
                                                    <template x-if="$store.cart.items.length === 0">
                                                        <p style="font-size:12px; color:#8FA8E8; text-align:center; padding:8px 0;">El pedido está vacío.</p>
                                                    </template>
                                                    <template x-for="item in $store.cart.items" :key="item.productId">
                                                        <div style="display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid rgba(255,255,255,0.06);">
                                                            <span style="font-size:12px; color:#C8D8FF; flex:1;" x-text="item.name"></span>
                                                            <div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                                                                {{-- Controles cantidad --}}
                                                                <button type="button"
                                                                        @click.stop="decreaseQty(item)"
                                                                        :aria-label="'Quitar uno de ' + item.name"
                                                                        style="width:22px; height:22px; border-radius:50%; background:rgba(46,80,176,0.35); border:2px solid transparent; color:#fff; font-size:15px; line-height:1; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 150ms ease;"
                                                                        onmouseenter="this.style.background='#2E50B0'; this.style.borderColor='#EF4444'; this.style.boxShadow='0 0 10px rgba(239,68,68,0.6)'; this.style.transform='scale(1.15)';"
                                                                        onmouseleave="this.style.background='rgba(46,80,176,0.35)'; this.style.borderColor='transparent'; this.style.boxShadow='none'; this.style.transform='scale(1)';"
                                                                        onmousedown="this.style.borderColor='#B91C1C'; this.style.boxShadow='0 0 14px rgba(185,28,28,0.8)'; this.style.transform='scale(0.9)';"
                                                                        onmouseup="this.style.borderColor='#EF4444'; this.style.boxShadow='0 0 10px rgba(239,68,68,0.6)'; this.style.transform='scale(1.15)';">−</button>
                                                                <span x-text="item.quantity"
                                                                      style="font-size:12px; font-weight:800; color:#fff; min-width:14px; text-align:center; font-family:'Nunito',sans-serif;"></span>
                                                                <button type="button"
                                                                        @click.stop="addToCart(item)"
                                                                        :aria-label="'Añadir otro de ' + item.name"
                                                                        style="width:22px; height:22px; border-radius:50%; background:linear-gradient(135deg,#2E50B0,#1A3380); border:2px solid transparent; color:#fff; font-size:15px; line-height:1; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 150ms ease;"
                                                                        onmouseenter="this.style.background='#3D6AE0'; this.style.boxShadow='0 0 12px rgba(34,197,94,0.7)'; this.style.borderColor='#22C55E'; this.style.transform='scale(1.15)';"
                                                                        onmouseleave="this.style.background='linear-gradient(135deg,#2E50B0,#1A3380)'; this.style.boxShadow='none'; this.style.borderColor='transparent'; this.style.transform='scale(1)';"
                                                                        onmousedown="this.style.borderColor='#16A34A'; this.style.transform='scale(0.9)';"
                                                                        onmouseup="this.style.borderColor='#22C55E'; this.style.transform='scale(1.15)';">+</button>
                                                                {{-- Precio y eliminar --}}
                                                                <span style="font-size:11px; font-weight:600; color:#FBBF24; min-width:48px; text-align:right; font-family:'Nunito',sans-serif;"
                                                                      x-text="Number(item.price * item.quantity).toFixed(2).replace('.',',') + ' €'"></span>
                                                                <button type="button"
                                                                        @click.stop="removeCartItem(item.productId)"
                                                                        :aria-label="'Eliminar ' + item.name + ' del pedido'"
                                                                        style="width:20px; height:20px; border-radius:50%; background:rgba(220,38,38,0.2); border:1px solid rgba(220,38,38,0.4); color:#F87171; font-size:11px; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:all 150ms ease;"
                                                                        onmouseenter="this.style.background='#DC2626'; this.style.borderColor='#EF4444'; this.style.color='#fff'; this.style.transform='scale(1.15)';"
                                                                        onmouseleave="this.style.background='rgba(220,38,38,0.2)'; this.style.borderColor='rgba(220,38,38,0.4)'; this.style.color='#F87171'; this.style.transform='scale(1)';"
                                                                        onmousedown="this.style.transform='scale(0.9)';"
                                                                        onmouseup="this.style.transform='scale(1.15)';">✕</button>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <template x-if="$store.cart.items.length > 0">
                                                        <div style="display:flex; justify-content:space-between; margin-top:8px; padding-top:8px; border-top:1px solid rgba(46,80,176,0.4);">
                                                            <span style="font-size:13px; font-weight:700; color:#fff;">Total</span>
                                                            <span style="font-size:16px; font-weight:900; color:#FBBF24; font-family:'Nunito',sans-serif;"
                                                                  x-text="Number($store.cart.total).toFixed(2).replace('.',',') + ' €'"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Usuario --}}
                                <template x-if="msg.type === 'user'">
                                    <div style="display:flex; justify-content:flex-end;">
                                        <div style="max-width:78%;">
                                            <div style="background:linear-gradient(135deg,#2E50B0,#1A3380); color:#fff; font-size:14px; line-height:1.5; padding:10px 14px; border-radius:18px 18px 4px 18px; box-shadow:0 4px 16px rgba(15,31,88,0.55); font-family:'Space Grotesk',sans-serif;"
                                                 x-text="msg.text"></div>
                                            <div style="font-size:10px; color:#3A5090; text-align:right; margin-top:3px; padding-right:4px;"
                                                 x-text="formatTime(msg.time) + ' ✓✓'"></div>
                                        </div>
                                    </div>
                                </template>

                            </div>
                        </template>

                        {{-- Indicador de escritura --}}
                        <template x-if="isTyping">
                            <div style="display:flex; gap:8px; align-items:flex-end;" aria-label="Zampi está escribiendo">
                                <div class="zampi-avatar" style="flex-shrink:0;">
                                    <svg width="24" height="22" aria-hidden="true"><use href="#zampi-mascot"/></svg>
                                </div>
                                <div style="background:#0E1A38; border:1px solid rgba(46,80,176,0.35); border-radius:18px 18px 18px 4px; padding:12px 16px; display:flex; gap:5px; align-items:center;">
                                    <div class="zampi-typing-1" style="width:7px; height:7px; border-radius:50%; background:#5478D0;"></div>
                                    <div class="zampi-typing-2" style="width:7px; height:7px; border-radius:50%; background:#5478D0;"></div>
                                    <div class="zampi-typing-3" style="width:7px; height:7px; border-radius:50%; background:#5478D0;"></div>
                                </div>
                            </div>
                        </template>

                        {{-- Error --}}
                        <template x-if="error">
                            <div style="display:flex; justify-content:center;" role="alert">
                                <div style="background:rgba(208,14,26,0.15); border:1px solid rgba(208,14,26,0.4); color:#F04040; font-size:12px; padding:5px 14px; border-radius:9999px; font-family:'Space Grotesk',sans-serif;"
                                     x-text="error"></div>
                            </div>
                        </template>

                        {{-- Conversación cerrada --}}
                        <template x-if="closed">
                            <div style="display:flex; justify-content:center;">
                                <div style="background:rgba(46,80,176,0.15); border:1px solid rgba(46,80,176,0.25); color:#8FA8E8; font-size:11px; padding:5px 14px; border-radius:9999px; font-family:'Space Grotesk',sans-serif;">
                                    Conversación finalizada. Inicia una nueva para seguir pidiendo.
                                </div>
                            </div>
                        </template>

                        {{-- Centinela de scroll -- siempre al final del log --}}
                        <div x-ref="chatEnd" style="height:1px; flex-shrink:0;" aria-hidden="true"></div>

                    </div>

                    {{-- Barra de carrito flotante --}}
                    <div x-show="chatCart.length > 0"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         style="flex-shrink:0; width:100%;">
                        <div style="width:100%; box-sizing:border-box; background:linear-gradient(135deg,#0f3d2a,#0a2e1f); border-top:2px solid #22c55e; box-shadow:0 -4px 20px rgba(34,197,94,0.2);">
                        {{-- Fila principal --}}
                        <div class="zampi-cartbar-row" style="padding:14px 16px; display:flex; align-items:center; gap:12px; overflow:visible;">
                        {{-- Resumen del carrito --}}
                        <div class="zampi-cart-left" style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                            <div style="position:relative; flex-shrink:0;">
                                <svg aria-hidden="true" width="26" height="26" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span x-text="cartCount"
                                      style="position:absolute; top:-6px; right:-6px; min-width:17px; height:17px; border-radius:9999px; background:#EF4444; color:#fff; font-size:10px; font-weight:900; display:flex; align-items:center; justify-content:center; padding:0 3px; font-family:'Nunito',sans-serif;"></span>
                            </div>
                            <span :class="cartNotifs.length > 0 ? 'zampi-hidden-mobile' : ''"
                                  style="font-size:13px; color:#fff; font-family:'Space Grotesk',sans-serif; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-weight:600;"
                                  x-text="cartCount + (cartCount === 1 ? ' artículo' : ' artículos')"></span>
                            <span :class="cartNotifs.length > 0 ? 'zampi-hidden-mobile' : ''"
                                  style="font-size:15px; font-weight:900; color:#FBBF24; font-family:'Nunito',sans-serif; flex-shrink:0;"
                                  x-text="Number(cartTotal).toFixed(2).replace('.',',') + ' €'"></span>
                            {{-- Notificaciones de eliminación acumulables (máx 3) --}}
                            <div class="zampi-notif-bar" :class="cartNotifs.length > 0 ? 'zampi-notif-bar-active' : ''"
                                 style="display:flex; gap:5px; flex-shrink:0; flex-wrap:nowrap;" role="status" aria-live="polite">
                                <template x-for="notif in cartNotifs" :key="notif.id">
                                    <div :class="notif.leaving ? 'zampi-notif-pill zampi-notif-pill-out' : 'zampi-notif-pill zampi-notif-pill-in'">
                                        <span style="font-size:12px;" aria-hidden="true">🗑️</span>
                                        <span class="zampi-notif-name" style="font-size:11px; font-weight:600; color:#fff; font-family:'Space Grotesk',sans-serif; white-space:nowrap;" x-text="notif.name"></span>
                                        <span class="zampi-notif-suffix" style="font-size:11px; font-weight:600; color:#fff; font-family:'Space Grotesk',sans-serif; white-space:nowrap;"> eliminado</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                        {{-- Botón único: abre el panel de pedido del menú digital --}}
                        <div style="display:flex; gap:8px; flex-shrink:0;">
                            <button type="button"
                                    @click="$store.cart.open = true"
                                    aria-label="Ver y confirmar pedido"
                                    style="padding:0; border-radius:9999px; background:#16A34A; border:none; color:#fff; font-size:13px; font-weight:700; font-family:'Space Grotesk',sans-serif; cursor:pointer; white-space:nowrap; transition:background 150ms ease; box-shadow:0 4px 14px rgba(22,163,74,0.45);"
                                    onmouseenter="this.style.background='#15803D';"
                                    onmouseleave="this.style.background='#16A34A';">
                                <span style="display:flex; flex-direction:row; align-items:center; gap:6px; padding:8px 18px; line-height:1;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span>Ver pedido</span>
                                </span>
                            </button>
                        </div>
                        </div>{{-- /Fila principal --}}
                        </div>{{-- /Cart bar inner --}}
                    </div>

                    {{-- Área de input --}}
                    <div style="flex-shrink:0; padding:10px 12px; background:rgba(5,11,31,0.95); backdrop-filter:blur(16px); border-top:1px solid rgba(46,80,176,0.3);">
                        <div style="display:flex; gap:8px; align-items:center; background:rgba(14,26,56,0.7); border:1px solid rgba(96,152,248,0.3); border-radius:9999px; padding:8px 8px 8px 16px;">
                            <label for="zampi-chat-input" class="sr-only">Escribe tu mensaje a Zampi</label>
                            <input id="zampi-chat-input"
                                   type="text"
                                   x-ref="chatInput"
                                   x-model="input"
                                   @keydown.enter="sendMessage()"
                                   :disabled="sending || closed"
                                   placeholder="Escribe tu pedido..."
                                   style="flex:1; background:none; border:none; outline:none; font-family:'Space Grotesk',sans-serif; font-size:14px; color:#C8D8FF; min-width:0;">
                            <button type="button"
                                    @click="sendMessage()"
                                    :disabled="!input.trim() || sending || closed"
                                    aria-label="Enviar mensaje a Zampi"
                                    style="width:38px; height:38px; border-radius:50%; border:none; cursor:pointer; background:linear-gradient(135deg,#2E50B0,#1A3380); color:#fff; font-size:18px; display:flex; align-items:center; justify-content:center; box-shadow:0 0 14px rgba(46,80,176,0.65); flex-shrink:0; transition:transform 150ms cubic-bezier(0.34,1.56,0.64,1);"
                                    onmousedown="this.style.transform='scale(0.92)'"
                                    onmouseup="this.style.transform='scale(1)'"
                                    onmouseleave="this.style.transform='scale(1)'">↑</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>{{-- /chatWidget --}}

        {{-- ══════════════════════════════════════════════════════════════════════
             RESTO DE LA CARTA — estructura HTML idéntica al Design System DS
             Zampi chatbot preservado intacto en las líneas anteriores (1-1071)
             ══════════════════════════════════════════════════════════════════════ --}}

        {{-- ── Banner cocina cerrada (DS: banner-closed) ──────────────────────── --}}
        @if(!$kitchenOpen)
        <div class="banner-closed" role="status" aria-live="polite">
            <span class="banner-closed__ic" aria-hidden="true">🌙</span>
            <div>
                <div class="banner-closed__title">La cocina está cerrada</div>
                <div class="banner-closed__sub">
                    @if($nextOpeningTime)Abre a las {{ $nextOpeningTime }}. @endif
                    Mientras tanto puedes pedir de barra 🍺
                </div>
            </div>
        </div>
        @endif

        {{-- ── Indicador de tapas ────────────────────────────────────────────── --}}
        @if($tapaConfig && $tapaConfig->tapas_enabled && $barItemsCount > 0)
        <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;background:linear-gradient(135deg,var(--color-amber-100),var(--color-amber-50));border-bottom:1px solid var(--color-amber-200);font-family:var(--font-body);font-size:13px;color:var(--color-amber-800);flex-shrink:0;"
             role="status" aria-live="polite">
            <span aria-hidden="true" style="font-size:20px">🍽️</span>
            <div style="flex:1">
                <strong>{{ $barItemsCount }} {{ Str::plural('tapa', $barItemsCount) }}</strong>
                {{ $barItemsCount > 1 ? 'disponibles' : 'disponible' }}
                @if($tapaConfig->tapas_free) · Gratuita@if($barItemsCount > 1)s@endif @else · {{ number_format($tapaConfig->tapa_price, 2, ',', '.') }} €@endif
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════════════
             FILTER BAR (mobile) + ALLERGEN SHEET — x-data local para allergensOpen
             ══════════════════════════════════════════════════════════════════════ --}}
        @if($businessOpen && $categories->isNotEmpty())
        <div x-data="{ allergensOpen: false }">

            {{-- DS: .filter-bar > .filter-row --}}
            <nav class="filter-bar" aria-label="Filtros de la carta">
                <div class="filter-row">
                    {{-- Destino: cocina / barra --}}
                    <button type="button"
                            class="chip chip--small"
                            :class="activeDestination === 'kitchen' ? 'chip--active' : ''"
                            @click="setDestination('kitchen')"
                            :aria-pressed="(activeDestination === 'kitchen').toString()">
                        🍳 Cocina
                    </button>
                    <button type="button"
                            class="chip chip--small"
                            :class="activeDestination === 'bar' ? 'chip--active' : ''"
                            @click="setDestination('bar')"
                            :aria-pressed="(activeDestination === 'bar').toString()">
                        🍺 Barra
                    </button>
                    {{-- Separador visual --}}
                    <span style="width:1px;background:var(--border-subtle);margin:2px 4px;align-self:stretch;flex-shrink:0" aria-hidden="true"></span>
                    {{-- Categorías --}}
                    <button type="button"
                            class="chip chip--small"
                            :class="activeCategory === null ? 'chip--active' : ''"
                            @click="setCategory(null)"
                            :aria-pressed="(activeCategory === null).toString()">
                        Todas
                    </button>
                    @foreach($categories as $cat)
                    <button type="button"
                            class="chip chip--small"
                            x-show="isCategoryVisible({{ $cat->id }})"
                            :class="activeCategory === {{ $cat->id }} ? 'chip--active' : ''"
                            @click="setCategory({{ $cat->id }})"
                            :aria-pressed="(activeCategory === {{ $cat->id }}).toString()">
                        {{ $cat->name }}
                    </button>
                    @endforeach
                </div>
                {{-- Botón alérgenos fijo a la derecha --}}
                @if($allergens->isNotEmpty())
                <button type="button"
                        class="filter-bar__pinned"
                        :class="activeAllergens.length > 0 ? 'filter-bar__pinned--on' : ''"
                        @click="allergensOpen = true"
                        aria-label="Filtrar por alérgenos">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                         stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 6h18M6 12h12M10 18h4"/>
                    </svg>
                    <span>Alérgenos</span>
                    <span class="filter-bar__count"
                          x-show="activeAllergens.length > 0"
                          x-text="activeAllergens.length"
                          aria-hidden="true"></span>
                </button>
                @endif
            </nav>

            {{-- DS: .scrim > .drawer.drawer--allergens (sheet móvil) --}}
            @if($allergens->isNotEmpty())
            <div class="scrim"
                 x-show="allergensOpen"
                 x-transition:enter="transition duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click.self="allergensOpen = false"
                 role="dialog" aria-modal="true" aria-label="Filtrar por alérgenos">
                <div class="drawer drawer--allergens" @click.stop>
                    <div class="drawer__grabber" aria-hidden="true"></div>
                    <div class="drawer__head">
                        <div>
                            <div class="drawer__title">Filtrar por alérgenos</div>
                            <div style="font-family:var(--font-body);font-size:12px;color:var(--fg-muted);margin-top:2px">
                                Ocultamos los platos que los contengan.
                            </div>
                        </div>
                        <button type="button" class="icon-btn" @click="allergensOpen = false" aria-label="Cerrar">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="drawer__body">
                        <div class="allergens-grid">
                            @foreach($allergens as $allergen)
                            <button type="button"
                                    class="allergen-chip"
                                    :class="activeAllergens.includes('{{ $allergen->slug }}') ? 'allergen-chip--active' : ''"
                                    @click="toggleAllergen('{{ $allergen->slug }}')"
                                    :aria-pressed="activeAllergens.includes('{{ $allergen->slug }}').toString()">
                                <span class="allergen" aria-hidden="true">
                                    <svg width="16" height="16" style="overflow:visible;display:block">
                                        <use href="#al-{{ $allergen->slug }}"/>
                                    </svg>
                                </span>
                                <span>{{ $allergen->name }}</span>
                            </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="drawer__foot">
                        <button type="button" class="btn-primary" @click="allergensOpen = false">
                            Ver resultados
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>{{-- /allergensOpen x-data --}}
        @endif

        {{-- ══════════════════════════════════════════════════════════════════════
             CARTA MAIN — .carta__main > .carta__filters + .carta__body
             ══════════════════════════════════════════════════════════════════════ --}}
        <div class="carta__main">

            {{-- DS: .carta__filters — sidebar (tablet + desktop, hidden on mobile via CSS) --}}
            @if($businessOpen && $categories->isNotEmpty())
            <div class="carta__filters" aria-label="Filtros">

                {{-- Menú del día --}}
                <x-daily-menu-availability />

                {{-- Destino (cocina / barra / todo) --}}
                <div class="filter-section">
                    <div class="filter-section__label">Servicio</div>
                    <div class="dest-toggle" role="group" aria-label="Filtrar por servicio">
                        <button type="button"
                                :class="activeDestination === null ? 'active' : ''"
                                @click="setDestination(null)"
                                :aria-pressed="(activeDestination === null).toString()">
                            Todo
                        </button>
                        <button type="button"
                                :class="activeDestination === 'kitchen' ? 'active' : ''"
                                @click="setDestination('kitchen')"
                                :aria-pressed="(activeDestination === 'kitchen').toString()"
                                aria-label="Cocina">
                            🍳
                        </button>
                        <button type="button"
                                :class="activeDestination === 'bar' ? 'active' : ''"
                                @click="setDestination('bar')"
                                :aria-pressed="(activeDestination === 'bar').toString()"
                                aria-label="Barra">
                            🍺
                        </button>
                    </div>
                </div>

                {{-- Categorías --}}
                <div class="filter-section">
                    <div class="filter-section__label">Categorías</div>
                    <div class="filter-list" role="list">
                        <div class="filter-list__item"
                             :class="activeCategory === null ? 'filter-list__item--active' : ''"
                             @click="setCategory(null)"
                             @keydown.enter="setCategory(null)"
                             role="button" tabindex="0"
                             :aria-pressed="(activeCategory === null).toString()">
                            <span>Todas</span>
                            <span class="filter-list__count">{{ $categories->sum(fn($c) => $c->products->count()) }}</span>
                        </div>
                        @foreach($categories as $cat)
                        <div class="filter-list__item"
                             x-show="isCategoryVisible({{ $cat->id }})"
                             :class="activeCategory === {{ $cat->id }} ? 'filter-list__item--active' : ''"
                             @click="setCategory({{ $cat->id }})"
                             @keydown.enter="setCategory({{ $cat->id }})"
                             role="button" tabindex="0"
                             :aria-pressed="(activeCategory === {{ $cat->id }}).toString()">
                            <span>{{ $cat->name }}</span>
                            <span class="filter-list__count">{{ $cat->products->count() }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Alérgenos --}}
                @if($allergens->isNotEmpty())
                <div class="filter-section">
                    <div class="filter-section__label">Alérgenos</div>
                    <div class="filter-list filter-list--allergens" role="list">
                        @foreach($allergens as $allergen)
                        <div class="filter-list__item"
                             :class="activeAllergens.includes('{{ $allergen->slug }}') ? 'filter-list__item--active' : ''"
                             @click="toggleAllergen('{{ $allergen->slug }}')"
                             @keydown.enter="toggleAllergen('{{ $allergen->slug }}')"
                             role="button" tabindex="0"
                             :aria-pressed="activeAllergens.includes('{{ $allergen->slug }}').toString()">
                            <span class="filter-list__check" aria-hidden="true">
                                <svg x-show="activeAllergens.includes('{{ $allergen->slug }}')"
                                     width="10" height="10" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <span class="allergen" aria-hidden="true">
                                <svg width="16" height="16" style="overflow:visible;display:block">
                                    <use href="#al-{{ $allergen->slug }}"/>
                                </svg>
                            </span>
                            <span style="font-size:13px">{{ $allergen->name }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Contador de visibles --}}
                <div style="margin-top:auto;padding-top:16px;border-top:1px solid var(--border-subtle)">
                    <p style="font-family:var(--font-body);font-size:11px;color:var(--fg-muted)"
                       role="status" aria-live="polite" aria-atomic="true">
                        <span x-text="visibleCount"></span>&nbsp;<span x-text="visibleCount === 1 ? 'producto visible' : 'productos visibles'"></span>
                    </p>
                    <button type="button"
                            x-show="hasActiveFilters"
                            x-transition
                            @click="clearAll()"
                            style="font-family:var(--font-body);font-size:12px;font-weight:600;color:var(--brand-primary);background:none;border:none;cursor:pointer;padding:4px 0;margin-top:6px">
                        Limpiar filtros
                    </button>
                </div>

            </div>
            @endif

            {{-- DS: .carta__body — área scrolleable con productos --}}
            <div class="carta__body" id="main-content">

                {{-- Banner Menú del Día --}}
                <x-daily-menu-banner :hash="$table->unique_hash" />

                @if(!$businessOpen)
                {{-- ══ NEGOCIO CERRADO ══ --}}
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:64px 24px;text-align:center;gap:16px;min-height:60%"
                     role="status" aria-live="polite">
                    <div style="font-size:56px;line-height:1">🕐</div>
                    <div style="font-family:var(--font-display);font-weight:900;font-size:24px;color:var(--fg-primary);line-height:1.2">
                        {{ $table->user->business_name ?: $table->user->name }} está cerrado
                    </div>
                    <div style="font-family:var(--font-body);font-size:14px;color:var(--fg-muted);max-width:280px;line-height:1.5">
                        Estamos fuera de nuestro horario de atención. Vuelve cuando estemos abiertos.
                    </div>
                    @if($businessNextOpening)
                    <div style="background:var(--glass-bg-surface);border:1px solid var(--glass-border);border-radius:9999px;padding:8px 20px;font-family:var(--font-body);font-size:13px;font-weight:600;color:var(--fg-primary)">
                        Próxima apertura a las {{ $businessNextOpening }}
                    </div>
                    @endif
                    @if($hasActiveOrder)
                    <button type="button"
                            @click="$store.bill.open()"
                            style="margin-top:8px;padding:12px 24px;border-radius:9999px;background:var(--cta-view-order);color:#fff;border:none;cursor:pointer;font-family:var(--font-body);font-weight:700;font-size:14px;box-shadow:var(--shadow-fab)">
                        💳 Solicitar la cuenta
                    </button>
                    @endif
                </div>

                @else
                {{-- ══ PRODUCTOS POR CATEGORÍA ══ --}}
                @forelse($categories as $category)
                @php $photoCycle = 0; @endphp
                <div class="category"
                     x-show="isCategoryVisible({{ $category->id }})"
                     x-transition
                     @if(!$kitchenOpen && $category->destination === 'kitchen')
                     style="opacity:0.45;pointer-events:none"
                     @endif>

                    <div class="category__head">
                        <span class="category__name">{{ $category->name }}</span>
                        <span class="category__count">{{ $category->products->count() }}</span>
                        @if(!$kitchenOpen && $category->destination === 'kitchen')
                        <span class="category__closed" role="status">
                            <span class="category__closedDot" aria-hidden="true"></span>
                            Cocina cerrada
                        </span>
                        @endif
                    </div>

                    <div class="product-grid">
                        @foreach($category->products as $product)
                        @php
                            $photoCycle++;
                            $photoStyle = (($photoCycle - 1) % 6) + 1;
                            $allergenSlugs = $product->ingredients
                                ->where('is_allergen', true)
                                ->flatMap(fn($i) => $i->allergen_types ?? [])
                                ->unique()->values();
                        @endphp
                        <div class="pcard"
                             x-show="isProductVisible({{ $product->id }})"
                             x-transition>

                            {{-- Foto --}}
                            @if($product->image)
                            <div class="pcard__photo">
                                <img src="{{ Storage::url($product->image) }}"
                                     alt="Foto de {{ $product->name }}"
                                     loading="lazy" decoding="async"
                                     style="width:100%;height:100%;object-fit:cover">
                            </div>
                            @else
                            <div class="pcard__photo pcard__photo--food-{{ $photoStyle }}" aria-hidden="true"></div>
                            @endif

                            {{-- Body --}}
                            <div class="pcard__body">

                                {{-- Badges alérgenos --}}
                                <div class="pcard__badges">
                                    @foreach($allergenSlugs->take(5) as $slug)
                                    <span class="allergen"
                                          title="{{ \App\Models\Ingredient::ALLERGEN_TYPES[$slug] ?? $slug }}"
                                          aria-label="{{ \App\Models\Ingredient::ALLERGEN_TYPES[$slug] ?? $slug }}">
                                        <svg aria-hidden="true" width="12" height="12" style="overflow:visible;display:block">
                                            <use href="#al-{{ $slug }}"/>
                                        </svg>
                                    </span>
                                    @endforeach
                                </div>

                                {{-- Nombre --}}
                                <div class="pcard__name">{{ $product->name }}</div>

                                {{-- Descripción --}}
                                @if($product->description)
                                <div class="pcard__desc">{{ $product->description }}</div>
                                @endif

                                {{-- Footer: precio + controles --}}
                                <div class="pcard__foot" @click.stop>
                                    @if($product->variants->isNotEmpty())
                                    {{-- Producto CON variantes --}}
                                    <div x-data="{
                                             selectedVId: {{ $product->variants->first()->id }},
                                             variants: {{ $product->variants->map(fn($v) => ['id' => $v->id, 'name' => $v->name, 'price' => (float)$v->price])->values()->toJson() }},
                                             get sv() { return this.variants.find(v => v.id === this.selectedVId) || this.variants[0]; }
                                         }"
                                         style="width:100%;display:flex;flex-direction:column;gap:6px">
                                        {{-- Selector de variante --}}
                                        <div style="display:flex;flex-wrap:wrap;gap:4px">
                                            @foreach($product->variants as $variant)
                                            <button type="button"
                                                    @click.stop="selectedVId = {{ $variant->id }}"
                                                    :class="selectedVId === {{ $variant->id }} ? 'chip chip--small chip--active' : 'chip chip--small'"
                                                    style="font-size:10px;padding:3px 8px"
                                                    :aria-pressed="(selectedVId === {{ $variant->id }}).toString()">
                                                {{ $variant->name }}
                                                <span style="opacity:0.75">{{ number_format($variant->price, 2, ',', '.') }}&nbsp;€</span>
                                            </button>
                                            @endforeach
                                        </div>
                                        {{-- Precio + añadir variante --}}
                                        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
                                            <span class="pcard__price" x-text="Number(sv.price).toFixed(2).replace('.',',') + ' €'"></span>
                                            @if($orderingAllowed)
                                            <button type="button"
                                                    class="btn-add"
                                                    @click.stop="$store.cart.addWithVariant({{ $product->id }}, selectedVId, sv.name, sv.price, products.find(p => p.id === {{ $product->id }}))"
                                                    :aria-label="'Añadir ' + sv.name + ' de {{ $product->name }}'">+</button>
                                            @endif
                                        </div>
                                    </div>

                                    @else
                                    {{-- Producto SIN variantes --}}
                                    <span class="pcard__price">{{ number_format((float)$product->price, 2, ',', '.') }}&nbsp;€</span>

                                    @if($orderingAllowed)
                                    {{-- btn-add cuando qty = 0 --}}
                                    <button type="button"
                                            class="btn-add"
                                            x-show="!$store.cart.items.some(i => i.productId === {{ $product->id }} && !i.variantId)"
                                            @click.stop="$store.cart.add(products.find(p => p.id === {{ $product->id }}))"
                                            aria-label="Añadir {{ $product->name }}">+</button>
                                    {{-- qty control cuando qty > 0 --}}
                                    <div class="qty"
                                         x-show="$store.cart.items.some(i => i.productId === {{ $product->id }} && !i.variantId)"
                                         @click.stop>
                                        <button class="qty-minus"
                                                @click.stop="$store.cart.dec('{{ $product->id }}:none')"
                                                aria-label="Quitar uno de {{ $product->name }}">−</button>
                                        <span class="qty-n"
                                              x-text="$store.cart.items.find(i => i.productId === {{ $product->id }} && !i.variantId)?.quantity || 0"></span>
                                        <button class="qty-plus"
                                                @click.stop="$store.cart.add(products.find(p => p.id === {{ $product->id }}))"
                                                aria-label="Añadir otro de {{ $product->name }}">+</button>
                                    </div>
                                    @else
                                    <span style="font-family:var(--font-body);font-size:11px;color:var(--fg-muted)">Cerrado</span>
                                    @endif
                                    @endif

                                </div>{{-- /pcard__foot --}}
                            </div>{{-- /pcard__body --}}
                        </div>{{-- /pcard --}}
                        @endforeach
                    </div>{{-- /product-grid --}}
                </div>{{-- /category --}}
                @empty
                <div style="text-align:center;color:var(--fg-muted);padding:32px 16px;font-family:var(--font-body);font-size:14px;line-height:1.5"
                     role="status">
                    <div style="font-size:40px;margin-bottom:10px">🔍</div>
                    La carta no está disponible en este momento.
                </div>
                @endforelse
                @endif

            </div>{{-- /carta__body --}}
        </div>{{-- /carta__main --}}

        {{-- ══════════════════════════════════════════════════════════════════════
             CART BAR — .cart-bar position:absolute dentro de .carta
             ══════════════════════════════════════════════════════════════════════ --}}
        <button type="button"
                class="cart-bar"
                x-show="$store.cart.count > 0 && !$store.chat.open"
                x-transition
                @click="$store.cart.open = true"
                :aria-label="'Ver pedido — ' + $store.cart.count + ($store.cart.count === 1 ? ' artículo' : ' artículos') + ', total ' + Number($store.cart.total).toFixed(2).replace('.',',') + ' €'">
            <div class="cart-bar__left">
                <span class="cart-bar__icon">
                    <svg aria-hidden="true" width="18" height="18" fill="none"
                         stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="cart-bar__count" x-text="$store.cart.count" aria-hidden="true"></span>
                </span>
                <div style="display:flex;flex-direction:column;gap:2px;align-items:flex-start">
                    <span x-text="$store.cart.count + ($store.cart.count === 1 ? ' artículo' : ' artículos')"></span>
                    <span class="cart-bar__total"
                          x-text="Number($store.cart.total).toFixed(2).replace('.',',') + ' €'"></span>
                </div>
            </div>
            <span class="cart-bar__cta">Ver pedido</span>
        </button>

        {{-- ══════════════════════════════════════════════════════════════════════
             FAB BILL — .fab.fab--bill position:absolute dentro de .carta
             ══════════════════════════════════════════════════════════════════════ --}}
        <button type="button"
                class="fab fab--bill"
                x-show="$store.bill.active && !$store.chat.open"
                x-transition
                @click="$store.bill.open()"
                :disabled="$store.bill.requested && $store.bill.paymentDone"
                :aria-label="$store.bill.paymentDone ? 'Pago completado' : ($store.bill.requested ? 'Cuenta solicitada' : 'Solicitar la cuenta')">
            <svg aria-hidden="true" width="18" height="18" fill="none"
                 stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
            </svg>
            <span x-text="$store.bill.paymentDone ? '¡Pagado!' : ($store.bill.requested ? 'Solicitado' : 'Cuenta')"></span>
        </button>

        {{-- Enlace descarga ticket post-pago --}}
        <template x-if="$store.bill.paymentDone && $store.bill.paidOrderId">
            <a :href="$store.bill.ticketDownloadBase + '/' + $store.bill.paidOrderId + '/download?hash=' + $store.bill.tableHash"
               target="_blank" rel="noopener noreferrer"
               class="delete-pill"
               style="background:var(--cta-view-order);border-color:var(--color-green-400);color:#fff;text-decoration:none"
               :aria-label="'Descargar ticket del pedido #' + $store.bill.paidOrderId">
                <svg aria-hidden="true" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16v-8m0 8l-3-3m3 3l3-3M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2"/>
                </svg>
                <strong>Descargar ticket</strong>
            </a>
        </template>

        {{-- ══════════════════════════════════════════════════════════════════════
             CART DRAWER — DS: .scrim > .drawer.drawer--cart
             ══════════════════════════════════════════════════════════════════════ --}}
        <div class="scrim"
             x-show="$store.cart.open"
             x-transition:enter="transition duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="$store.cart.open = false"
             role="dialog" aria-modal="true" aria-label="Tu pedido">
            <div class="drawer drawer--cart" @click.stop>
                <div class="drawer__grabber" aria-hidden="true"></div>
                {{-- Cart head --}}
                <div class="cart-head">
                    <div class="cart-head__row">
                        <div class="cart-head__heading">
                            <span class="cart-head__kicker">Tu comanda</span>
                            <h2 class="cart-head__title">Tu&nbsp;pedido</h2>
                        </div>
                        <button type="button" class="icon-btn cart-head__close"
                                @click="$store.cart.open = false" aria-label="Cerrar">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="cart-head__chips">
                        <span class="cart-head__chip cart-head__chip--mesa">
                            <span class="cart-head__chipDot" aria-hidden="true"></span>
                            {{ $table->name }}
                        </span>
                        <span class="cart-head__chip"
                              x-text="$store.cart.count === 0 ? 'Sin artículos' : $store.cart.count + ($store.cart.count === 1 ? ' artículo' : ' artículos')"></span>
                    </div>
                </div>
                {{-- Cart body --}}
                <div class="cart-drawer__body">
                    {{-- Empty state --}}
                    <template x-if="$store.cart.items.length === 0">
                        <div class="cart-empty">
                            <div class="cart-empty__art" aria-hidden="true">
                                <span class="cart-empty__emoji">🍽️</span>
                            </div>
                            <div class="cart-empty__h">Tu pedido está vacío</div>
                            <div class="cart-empty__p">Echa un ojo a la carta y añade tus platos.</div>
                        </div>
                    </template>
                    {{-- Items list --}}
                    <template x-if="$store.cart.items.length > 0">
                        <div>
                            <div class="cart-drawer__list">
                                <template x-for="item in $store.cart.items" :key="item._key">
                                    <div class="citem">
                                        <div class="citem__top">
                                            <div class="citem__photo" aria-hidden="true">🍽️</div>
                                            <div class="citem__main">
                                                <div class="citem__name" x-text="item.name"></div>
                                                <div class="citem__unit">
                                                    <span class="citem__unitPrice"
                                                          x-text="Number(item.price).toFixed(2).replace('.',',') + ' €'"></span>
                                                    <span class="citem__unitDot">·</span>
                                                    <span>por unidad</span>
                                                </div>
                                                {{-- Modificaciones --}}
                                                <template x-if="item.mods && item.mods.length > 0">
                                                    <div class="citem__customTags" aria-label="Personalizaciones">
                                                        <template x-for="m in item.mods.filter(m => m.action === 'add')" :key="'e'+m.ingredientId">
                                                            <span class="citem__customTag citem__customTag--extra"
                                                                  x-text="'+ ' + m.name"></span>
                                                        </template>
                                                        <template x-for="m in item.mods.filter(m => m.action === 'remove')" :key="'r'+m.ingredientId">
                                                            <span class="citem__customTag citem__customTag--removed"
                                                                  x-text="'Sin ' + m.name"></span>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="citem__side">
                                                <div class="qty">
                                                    <button class="qty-minus"
                                                            @click="$store.cart.dec(item._key)"
                                                            aria-label="Quitar uno">−</button>
                                                    <span class="qty-n" x-text="item.quantity"></span>
                                                    <button class="qty-plus"
                                                            @click="$store.cart.inc(item._key)"
                                                            aria-label="Añadir uno">+</button>
                                                </div>
                                                <span class="citem__price"
                                                      x-text="Number(item.price * item.quantity).toFixed(2).replace('.',',') + ' €'"></span>
                                            </div>
                                        </div>
                                        {{-- Quitar ingredientes --}}
                                        <template x-if="item.removable && item.removable.length > 0">
                                            <div class="citem__quitar">
                                                <div class="citem__quitarLabel">Quitar</div>
                                                <div class="citem__chips">
                                                    <template x-for="ing in item.removable" :key="ing.id">
                                                        <button type="button"
                                                                class="chip-quitar"
                                                                :class="$store.cart.hasMod(item._key, ing.id, 'remove') ? 'chip-quitar--on' : ''"
                                                                @click="$store.cart.toggleRemove(item._key, ing)"
                                                                :aria-pressed="$store.cart.hasMod(item._key, ing.id, 'remove').toString()"
                                                                x-text="'Sin ' + ing.name"></button>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                            {{-- Cart summary --}}
                            <div class="cart-sum" aria-label="Resumen del pedido">
                                <div class="cart-sum__head">
                                    <span class="cart-sum__lab">Resumen</span>
                                    <span class="cart-sum__ct"
                                          x-text="$store.cart.count + ($store.cart.count === 1 ? ' artículo' : ' artículos')"></span>
                                </div>
                                <div class="cart-sum__rows">
                                    <div class="cart-sum__row">
                                        <span>Subtotal (sin IVA)</span>
                                        <span class="num"
                                              x-text="Number($store.cart.total / 1.1).toFixed(2).replace('.',',') + ' €'"></span>
                                    </div>
                                    <div class="cart-sum__row cart-sum__row--muted">
                                        <span>IVA 10% incluido</span>
                                        <span class="num"
                                              x-text="Number($store.cart.total - $store.cart.total / 1.1).toFixed(2).replace('.',',') + ' €'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                {{-- Cart foot --}}
                <template x-if="$store.cart.items.length > 0">
                    <div class="cart-foot">
                        <div class="cart-foot__totalRow">
                            <span class="cart-foot__totalLab">A pagar</span>
                            <span class="cart-foot__totalVal"
                                  x-text="Number($store.cart.total).toFixed(2).replace('.',',') + ' €'"></span>
                        </div>
                        <button type="button"
                                class="cart-foot__cta"
                                @click="$store.cart.send()"
                                :disabled="$store.cart.sending || $store.cart.sent">
                            <span class="cart-foot__ctaIc" aria-hidden="true">
                                <template x-if="$store.cart.sent">✓</template>
                                <template x-if="$store.cart.sending && !$store.cart.sent">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite">
                                        <circle cx="12" cy="12" r="10" stroke-dasharray="31.4" stroke-dashoffset="10"/>
                                    </svg>
                                </template>
                                <template x-if="!$store.cart.sending && !$store.cart.sent">👨‍🍳</template>
                            </span>
                            <span class="cart-foot__ctaLab"
                                  x-text="$store.cart.sending ? 'Enviando a cocina…' : ($store.cart.sent ? 'Pedido enviado' : $store.cart.sendLabel)"></span>
                            <template x-if="!$store.cart.sending && !$store.cart.sent">
                                <span class="cart-foot__ctaArrow" aria-hidden="true">→</span>
                            </template>
                        </button>
                        <template x-if="$store.bill.active">
                            <button type="button"
                                    class="cart-foot__secondary"
                                    @click="$store.cart.open = false; $store.bill.open()">
                                <svg aria-hidden="true" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                </svg>
                                Pedir cuenta
                            </button>
                        </template>
                    </div>
                </template>
                {{-- Error de envío --}}
                <template x-if="$store.cart.error">
                    <p style="padding:8px 16px;font-family:var(--font-body);font-size:13px;color:var(--color-red-400);text-align:center"
                       role="alert" x-text="$store.cart.error"></p>
                </template>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════════
             BILL DRAWER — DS: .scrim > .drawer.drawer--bill (elección de método)
             ══════════════════════════════════════════════════════════════════════ --}}
        <div class="scrim"
             x-show="$store.bill.choosing"
             x-transition:enter="transition duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="$store.bill.close()"
             role="dialog" aria-modal="true" aria-label="Solicitar la cuenta">
            <div class="drawer drawer--bill" @click.stop>
                <div class="drawer__grabber" aria-hidden="true"></div>
                <div class="drawer__head">
                    <div class="drawer__heading">
                        <div class="drawer__title">Solicitar la cuenta</div>
                        <div class="drawer__subtitle">¿Cómo quieres pagar?</div>
                    </div>
                    <div class="drawer__total" aria-label="Total a pagar">
                        <div class="lab">Total</div>
                        <div class="val" x-text="Number($store.bill.orderTotal).toFixed(2).replace('.',',') + ' €'"></div>
                    </div>
                    <button type="button" class="icon-btn" @click="$store.bill.close()" aria-label="Cerrar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="drawer__body">
                    <div class="method-list">
                        {{-- Efectivo --}}
                        <button type="button" class="method method--cash" @click="$store.bill.openCashTip()">
                            <span class="method__ic" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="7" width="20" height="14" rx="2"/>
                                    <path d="M16 3H8l-2 4h12l-2-4z"/>
                                    <circle cx="12" cy="14" r="2"/>
                                </svg>
                            </span>
                            <span class="method__txt">
                                <span class="method__nm">Efectivo</span>
                                <span class="method__ds">Pagas al camarero · puedes añadir propina</span>
                            </span>
                            <span class="method__chev" aria-hidden="true">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                    <polyline points="9 6 15 12 9 18"/>
                                </svg>
                            </span>
                        </button>
                        {{-- Tarjeta --}}
                        <button type="button" class="method method--card" @click="$store.bill.openCardPayment()">
                            <span class="method__ic" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="1" y="4" width="22" height="16" rx="2"/>
                                    <line x1="1" y1="10" x2="23" y2="10"/>
                                </svg>
                            </span>
                            <span class="method__txt">
                                <span class="method__nm">Tarjeta</span>
                                <span class="method__ds">Pago seguro · Stripe</span>
                            </span>
                            <span class="method__chev" aria-hidden="true">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                    <polyline points="9 6 15 12 9 18"/>
                                </svg>
                            </span>
                        </button>
                        {{-- Cobro mixto --}}
                        <button type="button" class="method method--mixed" @click="$store.bill.openMixed()">
                            <span class="method__ic" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="6" width="9" height="12" rx="2"/>
                                    <rect x="13" y="5" width="9" height="14" rx="2"/>
                                    <path d="M6.5 12h.01M17 12h.01"/>
                                </svg>
                            </span>
                            <span class="method__txt">
                                <span class="method__nm">Cobro mixto</span>
                                <span class="method__ds">Una parte en efectivo, otra con tarjeta</span>
                            </span>
                            <span class="method__chev" aria-hidden="true">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                    <polyline points="9 6 15 12 9 18"/>
                                </svg>
                            </span>
                        </button>
                        {{-- Cobro partido --}}
                        <template x-if="$store.bill.splitEnabled">
                            <button type="button" class="method method--split" @click="$store.bill.openSplit()">
                                <span class="method__ic" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="8" cy="9" r="3"/>
                                        <circle cx="16" cy="9" r="3"/>
                                        <path d="M3 20a5 5 0 0 1 10 0M11 20a5 5 0 0 1 10 0"/>
                                    </svg>
                                </span>
                                <span class="method__txt">
                                    <span class="method__nm">Cobro partido</span>
                                    <span class="method__ds">Dividir entre comensales</span>
                                </span>
                                <span class="method__chev" aria-hidden="true">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                        <polyline points="9 6 15 12 9 18"/>
                                    </svg>
                                </span>
                            </button>
                        </template>
                    </div>
                </div>
                <div class="drawer__foot">
                    <button type="button" class="btn-text" @click="$store.bill.close()">Cancelar</button>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════════
             BILL SUB-SHEETS — propina efectivo, tarjeta, confirmaciones
             Mantenemos la lógica Alpine pero con estructura DS drawer
             ══════════════════════════════════════════════════════════════════════ --}}

        {{-- Propina efectivo (cashTip) --}}
        <div class="scrim"
             x-show="$store.bill.showingTip"
             x-transition
             @click.self="$store.bill.closeTip()"
             role="dialog" aria-modal="true" aria-label="¿Quieres dejar propina?">
            <div class="drawer drawer--bill" @click.stop>
                <div class="drawer__grabber" aria-hidden="true"></div>
                <div class="drawer__head">
                    <button type="button" class="icon-btn icon-btn--back" @click="$store.bill.close()" aria-label="Cambiar método">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                    </button>
                    <div class="drawer__heading">
                        <div class="drawer__title">¿Quieres dejar propina?</div>
                        <div class="drawer__subtitle">Pago en efectivo · es opcional</div>
                    </div>
                    <div class="drawer__total" aria-label="Total a pagar">
                        <div class="lab">Total a pagar</div>
                        <div class="val" x-text="Number($store.bill.orderTotal + ($store.bill.tipAmount || 0)).toFixed(2).replace('.',',') + ' €'"></div>
                    </div>
                    <button type="button" class="icon-btn" @click="$store.bill.closeTip()" aria-label="Cerrar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="drawer__body">
                    <div class="cashTip">
                        <div class="cashTip__hero">
                            <span class="lab">Total del pedido</span>
                            <span class="val" x-text="Number($store.bill.orderTotal).toFixed(2).replace('.',',') + ' €'"></span>
                        </div>
                        <section class="cashTip__section">
                            <div class="cashTip__label">
                                <span>Propina sugerida</span>
                                <span class="hint">Toca para elegir</span>
                            </div>
                            <div class="cashTip__grid">
                                <template x-for="pct in [5, 10, 15, 20]" :key="pct">
                                    <button type="button"
                                            class="cashTip__chip"
                                            :class="$store.bill.tipPercent === pct ? 'is-active' : ''"
                                            :aria-pressed="($store.bill.tipPercent === pct).toString()"
                                            @click="$store.bill.setTipPercent(pct)">
                                        <span class="cashTip__pct" x-text="pct + '%'"></span>
                                        <span class="cashTip__amt" x-text="Number(Math.round($store.bill.orderTotal * pct) / 100).toFixed(2).replace('.',',') + ' €'"></span>
                                    </button>
                                </template>
                            </div>
                            <div class="cashTip__input">
                                <label for="cash-tip-input-ds" class="sr-only">Propina personalizada en euros</label>
                                <input id="cash-tip-input-ds"
                                       type="number" min="0" step="0.50" inputmode="decimal"
                                       placeholder="Otra cantidad…"
                                       @input="$store.bill.updateCustomTip($event.target.value)"
                                       :value="$store.bill.tipPercent === null && $store.bill.tipAmount > 0 ? $store.bill.tipAmount : ''">
                                <span class="cashTip__currency" aria-hidden="true">€</span>
                            </div>
                        </section>
                        <section class="cashTip__summary" aria-live="polite">
                            <div class="cashTip__row">
                                <span>Pedido</span>
                                <span class="v" x-text="Number($store.bill.orderTotal).toFixed(2).replace('.',',') + ' €'"></span>
                            </div>
                            <template x-if="($store.bill.tipAmount || 0) > 0">
                                <div class="cashTip__row cashTip__row--tip">
                                    <span>Propina</span>
                                    <span class="v" x-text="'+ ' + Number($store.bill.tipAmount).toFixed(2).replace('.',',') + ' €'"></span>
                                </div>
                            </template>
                            <div class="cashTip__row cashTip__row--total">
                                <span>Total a pagar</span>
                                <span class="v" x-text="Number($store.bill.orderTotal + ($store.bill.tipAmount || 0)).toFixed(2).replace('.',',') + ' €'"></span>
                            </div>
                        </section>
                    </div>
                </div>
                <div class="drawer__foot">
                    <div class="bill__footRow bill__footRow--stack">
                        <button type="button"
                                class="btn-primary cashTip__cta"
                                :disabled="$store.bill.sending"
                                @click="$store.bill.confirmCashPayment ? $store.bill.confirmCashPayment() : $store.bill.requestCash()">
                            <template x-if="$store.bill.sending">
                                <span><span class="cashTip__spin" aria-hidden="true"></span> Enviando solicitud…</span>
                            </template>
                            <template x-if="!$store.bill.sending">
                                <span x-text="($store.bill.tipAmount || 0) > 0
                                    ? 'Solicitar cuenta · ' + Number($store.bill.orderTotal + ($store.bill.tipAmount || 0)).toFixed(2).replace('.',',') + ' €'
                                    : 'Solicitar cuenta · ' + Number($store.bill.orderTotal).toFixed(2).replace('.',',') + ' €'"></span>
                            </template>
                        </button>
                        <button type="button" class="btn-secondary" @click="$store.bill.close()">← Cambiar método</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pago con tarjeta (tip %) --}}
        <div class="scrim"
             x-show="$store.bill.payingCard && !$store.bill.stripeReady"
             x-transition
             @click.self="$store.bill.close()"
             role="dialog" aria-modal="true" aria-label="Elige tu propina">
            <div class="drawer drawer--bill" @click.stop>
                <div class="drawer__grabber" aria-hidden="true"></div>
                <div class="drawer__head">
                    <button type="button" class="icon-btn icon-btn--back" @click="$store.bill.close()" aria-label="Cambiar método">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                    <div class="drawer__heading">
                        <div class="drawer__title">Elige tu propina</div>
                        <div class="drawer__subtitle">Añade una propina al servicio</div>
                    </div>
                    <div class="drawer__total">
                        <div class="lab">Total a pagar</div>
                        <div class="val" x-text="Number($store.bill.grandTotal).toFixed(2).replace('.',',') + ' €'"></div>
                    </div>
                    <button type="button" class="icon-btn" @click="$store.bill.close()" aria-label="Cerrar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="drawer__body">
                    <div class="tip-row" role="group" aria-label="Porcentaje de propina">
                        <template x-for="p in [0, 10, 15, 20]" :key="p">
                            <div class="tip"
                                 :class="$store.bill.tipPercent === p ? 'tip--selected' : ''"
                                 @click="$store.bill.setTipPercent(p)"
                                 :aria-pressed="($store.bill.tipPercent === p).toString()"
                                 role="button" tabindex="0">
                                <div class="pct" x-text="p + '%'"></div>
                                <div class="amt" x-text="p === 0 ? '—' : Number($store.bill.orderTotal * p / 100).toFixed(2).replace('.',',') + ' €'"></div>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="drawer__foot">
                    <div class="bill__footRow bill__footRow--stack">
                        <button type="button" class="btn-primary"
                                @click="$store.bill.proceedToStripe()">
                            Continuar — <span x-text="Number($store.bill.grandTotal).toFixed(2).replace('.',',') + ' €'"></span>
                        </button>
                        <button type="button" class="btn-secondary" @click="$store.bill.close()">← Cambiar método</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stripe payment --}}
        <div class="scrim"
             x-show="$store.bill.stripeReady"
             x-transition
             @click.self="$store.bill.close()"
             role="dialog" aria-modal="true" aria-label="Pago con tarjeta">
            <div class="drawer drawer--bill" @click.stop>
                <div class="drawer__grabber" aria-hidden="true"></div>
                <div class="drawer__head">
                    <div class="drawer__heading">
                        <div class="drawer__title">Pago con tarjeta</div>
                        <div class="drawer__subtitle" x-text="$store.bill.tipPercent + '% de propina incluida'"></div>
                    </div>
                    <div class="drawer__total">
                        <div class="lab">Total a pagar</div>
                        <div class="val" x-text="Number($store.bill.grandTotal).toFixed(2).replace('.',',') + ' €'"></div>
                    </div>
                    <button type="button" class="icon-btn" @click="$store.bill.close()" aria-label="Cerrar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="drawer__body">
                    <div style="display:flex;flex-direction:column;gap:16px">
                        <div style="display:flex;align-items:center;gap:6px;font-family:var(--font-body);font-size:12px;font-weight:600;color:var(--fg-muted)">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                            </svg>
                            PAGO SEGURO · STRIPE
                        </div>
                        <div id="stripe-card-element" style="padding:16px;border:1px solid var(--glass-border);border-radius:12px;background:var(--glass-bg-surface)"></div>
                        <template x-if="$store.bill.stripeError">
                            <p style="color:var(--color-red-400);font-size:13px" role="alert" x-text="$store.bill.stripeError"></p>
                        </template>
                    </div>
                </div>
                <div class="drawer__foot">
                    <div class="bill__footRow bill__footRow--stack">
                        <button type="button" class="btn-primary"
                                @click="$store.bill.payWithStripe()"
                                :disabled="$store.bill.sending">
                            <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                            </svg>
                            <span x-text="$store.bill.sending ? 'Procesando…' : 'Pagar ' + Number($store.bill.grandTotal).toFixed(2).replace('.',',') + ' €'"></span>
                        </button>
                        <button type="button" class="btn-secondary" @click="$store.bill.close()">← Cambiar método</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Efectivo confirmado --}}
        <div class="scrim"
             x-show="$store.bill.requested && $store.bill.method === 'cash' && !$store.bill.paymentDone"
             x-transition
             @click.self="$store.bill.close()"
             role="dialog" aria-modal="true" aria-label="Camarero avisado">
            <div class="drawer drawer--bill" @click.stop>
                <div class="drawer__grabber" aria-hidden="true"></div>
                <div class="drawer__body">
                    <div class="bill__done" style="text-align:center;padding:24px 0">
                        <div style="font-size:56px;line-height:1;margin-bottom:12px">✅</div>
                        <div style="font-family:var(--font-display);font-weight:900;font-size:22px;color:var(--fg-primary)">Camarero avisado</div>
                        <div style="font-family:var(--font-body);font-size:13px;color:var(--fg-muted);margin-top:8px;line-height:1.5">
                            Pasará a tu mesa en unos instantes.
                        </div>
                    </div>
                </div>
                <div class="drawer__foot">
                    <button type="button" class="btn-text" @click="$store.bill.close()">Cerrar</button>
                </div>
            </div>
        </div>

        {{-- Tarjeta confirmada --}}
        <div class="scrim"
             x-show="$store.bill.paymentDone && $store.bill.method !== 'cash' && !$store.bill.showingSplit && !$store.bill.showingMixed"
             x-transition
             @click.self="$store.bill.close()"
             role="dialog" aria-modal="true" aria-label="Pago confirmado">
            <div class="drawer drawer--bill" @click.stop>
                <div class="drawer__grabber" aria-hidden="true"></div>
                <div class="drawer__body">
                    <div class="bill__done" style="text-align:center;padding:24px 0">
                        <div style="font-size:56px;line-height:1;margin-bottom:12px">🎉</div>
                        <div style="font-family:var(--font-display);font-weight:900;font-size:22px;color:var(--fg-primary)">¡Pago confirmado!</div>
                        <div style="font-family:var(--font-body);font-size:13px;color:var(--fg-muted);margin-top:8px;line-height:1.5">
                            Gracias por tu visita. ¡Hasta pronto!
                        </div>
                        <template x-if="$store.bill.paidOrderId">
                            <a :href="$store.bill.ticketDownloadBase + '/' + $store.bill.paidOrderId + '/download?hash=' + $store.bill.tableHash"
                               target="_blank" rel="noopener noreferrer"
                               style="display:inline-flex;align-items:center;gap:8px;margin-top:16px;padding:10px 20px;border-radius:9999px;background:var(--glass-bg-surface);border:1px solid var(--glass-border);color:var(--fg-primary);text-decoration:none;font-family:var(--font-body);font-weight:600;font-size:13px">
                                <svg aria-hidden="true" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16v-8m0 8l-3-3m3 3l3-3M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2"/>
                                </svg>
                                Descargar ticket
                            </a>
                        </template>
                    </div>
                </div>
                <div class="drawer__foot">
                    <button type="button" class="btn-text" @click="$store.bill.close()">Cerrar</button>
                </div>
            </div>
        </div>

        {{-- SPLIT PAYMENT --}}
        <div class="scrim"
             x-show="$store.bill.showingSplit"
             x-transition
             @click.self="$store.bill.closeSplitSelector()"
             role="dialog" aria-modal="true" aria-label="Cobro partido">
            <div class="drawer drawer--bill drawer--wide" @click.stop>
                <div class="drawer__grabber" aria-hidden="true"></div>
                <div class="drawer__head">
                    <button type="button" class="icon-btn icon-btn--back" @click="$store.bill.close()" aria-label="Cambiar método">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                    <div class="drawer__heading">
                        <div class="drawer__title">Cobro partido</div>
                        <div class="drawer__subtitle">Dividís la cuenta en tiempo real</div>
                    </div>
                    <div class="drawer__total">
                        <div class="lab">Total</div>
                        <div class="val" x-text="Number($store.bill.orderTotal).toFixed(2).replace('.',',') + ' €'"></div>
                    </div>
                    <button type="button" class="icon-btn" @click="$store.bill.closeSplitSelector()" aria-label="Cerrar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="drawer__body">
                    <div style="display:flex;flex-direction:column;gap:12px">
                        {{-- Por ítems --}}
                        <button type="button"
                                @click="!$store.bill.splitEquitativeLocked && $store.bill.openSplitItems()"
                                :disabled="$store.bill.splitEquitativeLocked"
                                style="width:100%;display:flex;align-items:center;gap:12px;padding:14px;border-radius:14px;background:var(--glass-bg-surface);border:1px solid var(--glass-border);cursor:pointer;text-align:left"
                                :style="$store.bill.splitEquitativeLocked ? 'opacity:0.5;cursor:not-allowed' : ''">
                            <span style="font-size:24px;flex-shrink:0" aria-hidden="true">🧾</span>
                            <div>
                                <div style="font-family:var(--font-body);font-weight:700;font-size:14px;color:var(--fg-primary)">Pagar por ítems</div>
                                <div style="font-family:var(--font-body);font-size:12px;color:var(--fg-muted);margin-top:2px"
                                     x-text="$store.bill.splitEquitativeLocked ? 'No disponible: ya hay pagos a partes iguales' : 'Elige exactamente qué platos pagas tú'"></div>
                            </div>
                        </button>
                        {{-- Equitativo --}}
                        <button type="button"
                                @click="$store.bill.openSplitEq()"
                                style="width:100%;display:flex;align-items:center;gap:12px;padding:14px;border-radius:14px;background:var(--glass-bg-surface);border:1px solid var(--glass-border);cursor:pointer;text-align:left">
                            <span style="font-size:24px;flex-shrink:0" aria-hidden="true">➗</span>
                            <div>
                                <div style="font-family:var(--font-body);font-weight:700;font-size:14px;color:var(--fg-primary)">Dividir a partes iguales</div>
                                <div style="font-family:var(--font-body);font-size:12px;color:var(--fg-muted);margin-top:2px">El total se divide entre todos por igual</div>
                            </div>
                        </button>
                    </div>
                </div>
                <div class="drawer__foot">
                    <button type="button" class="btn-text" @click="$store.bill.closeSplitSelector()">Cancelar</button>
                </div>
            </div>
        </div>

        {{-- SPLIT POR ÍTEMS --}}
        <div class="scrim"
             x-show="$store.bill.splitShowItems"
             x-transition
             @click.self="$store.bill.closeSplitItems()"
             role="dialog" aria-modal="true" aria-label="Pagar por ítems">
            <div class="drawer drawer--bill drawer--wide" @click.stop
                 style="max-height:88dvh;overflow:hidden;display:flex;flex-direction:column">
                <div class="drawer__grabber" aria-hidden="true"></div>
                <div class="drawer__head" style="flex-shrink:0">
                    <div class="drawer__heading">
                        <div class="drawer__title">Pagar por ítems</div>
                        <div class="drawer__subtitle">Marca los platos que quieres pagar tú</div>
                    </div>
                    <button type="button" class="icon-btn" @click="$store.bill.closeSplitItems()" aria-label="Cerrar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div style="flex:1;overflow-y:auto;padding:14px 18px 0">
                    <fieldset style="border:none;padding:0;display:flex;flex-direction:column;gap:8px">
                        <legend class="sr-only">Selecciona los ítems que quieres pagar</legend>
                        <template x-for="item in $store.bill.splitItems" :key="item.id">
                            <label :for="'spi-' + item.id"
                                   :class="item.claimed
                                       ? 'opacity-50 cursor-not-allowed'
                                       : $store.bill.isItemSelected(item.id)
                                           ? 'ring-2'
                                           : ''"
                                   style="display:flex;align-items:center;gap:12px;padding:12px;border-radius:12px;border:2px solid var(--glass-border);cursor:pointer;background:var(--glass-bg-surface)">
                                <input type="checkbox"
                                       :id="'spi-' + item.id"
                                       :checked="$store.bill.isItemSelected(item.id)"
                                       :disabled="item.claimed"
                                       @change="$store.bill.toggleSplitItem(item.id, item.claimed)"
                                       style="width:18px;height:18px;flex-shrink:0">
                                <div style="flex:1;min-width:0">
                                    <div style="font-family:var(--font-body);font-weight:600;font-size:13px;color:var(--fg-primary)" x-text="item.name"></div>
                                    <div style="font-family:var(--font-body);font-size:11px;color:var(--fg-muted);margin-top:2px"
                                         x-text="item.quantity + ' × ' + Number(item.price).toFixed(2).replace('.',',') + ' €'"></div>
                                </div>
                                <template x-if="item.claimed">
                                    <span style="font-size:11px;font-weight:600;color:var(--color-amber-600);background:var(--color-amber-100);border-radius:9999px;padding:3px 8px">Ya reclamado</span>
                                </template>
                                <template x-if="!item.claimed">
                                    <span style="font-family:var(--font-display);font-weight:700;font-size:14px;color:var(--price-gold)"
                                          x-text="Number(item.total).toFixed(2).replace('.',',') + ' €'"></span>
                                </template>
                            </label>
                        </template>
                    </fieldset>
                </div>
                <div class="drawer__foot" style="flex-shrink:0">
                    <div style="display:flex;justify-content:space-between;margin-bottom:10px;font-family:var(--font-body);font-size:13px">
                        <span style="color:var(--fg-muted)">Mi parte</span>
                        <span style="font-weight:700;color:var(--price-gold)"
                              x-text="Number($store.bill.splitSelectedTotal || 0).toFixed(2).replace('.',',') + ' €'"></span>
                    </div>
                    <button type="button" class="btn-primary"
                            :disabled="!$store.bill.splitSelected || $store.bill.splitSelected.length === 0"
                            @click="$store.bill.paySelectedItems()">
                        Pagar mi parte
                    </button>
                    <button type="button" class="btn-text" @click="$store.bill.closeSplitItems()">Cancelar</button>
                </div>
            </div>
        </div>

        {{-- SPLIT EQUITATIVO --}}
        <div class="scrim"
             x-show="$store.bill.splitShowEq"
             x-transition
             @click.self="$store.bill.closeSplitEq()"
             role="dialog" aria-modal="true" aria-label="Dividir a partes iguales">
            <div class="drawer drawer--bill" @click.stop>
                <div class="drawer__grabber" aria-hidden="true"></div>
                <div class="drawer__head">
                    <div class="drawer__heading">
                        <div class="drawer__title">Dividir a partes iguales</div>
                        <div class="drawer__subtitle">El total se divide entre todos</div>
                    </div>
                    <div class="drawer__total">
                        <div class="lab">Total</div>
                        <div class="val" x-text="Number($store.bill.orderTotal).toFixed(2).replace('.',',') + ' €'"></div>
                    </div>
                    <button type="button" class="icon-btn" @click="$store.bill.closeSplitEq()" aria-label="Cerrar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="drawer__body">
                    <div style="display:flex;flex-direction:column;gap:16px">
                        <label style="font-family:var(--font-body);font-size:13px;font-weight:600;color:var(--fg-secondary)">
                            ¿Entre cuántas personas?
                        </label>
                        <input type="number"
                               min="2"
                               :max="$store.bill.splitMaxParts || 20"
                               :value="$store.bill.splitPeople || 2"
                               @input="$store.bill.splitPeople = parseInt($event.target.value) || 2"
                               style="padding:12px;border-radius:12px;border:1px solid var(--glass-border);background:var(--glass-bg-surface);color:var(--fg-primary);font-family:var(--font-display);font-size:24px;font-weight:900;text-align:center;width:100%">
                        <div style="text-align:center">
                            <div style="font-family:var(--font-body);font-size:12px;color:var(--fg-muted)">Tu parte</div>
                            <div style="font-family:var(--font-display);font-weight:900;font-size:32px;color:var(--price-gold)"
                                 x-text="Number($store.bill.orderTotal / ($store.bill.splitPeople || 2)).toFixed(2).replace('.',',') + ' €'"></div>
                        </div>
                    </div>
                </div>
                <div class="drawer__foot">
                    <button type="button" class="btn-primary" @click="$store.bill.payEquitative()">
                        Pagar mi parte con tarjeta
                    </button>
                    <button type="button" class="btn-text" @click="$store.bill.closeSplitEq()">Cancelar</button>
                </div>
            </div>
        </div>

        {{-- MIXED PAYMENT --}}
        <div class="scrim"
             x-show="$store.bill.showingMixed"
             x-transition
             @click.self="$store.bill.closeMixed()"
             role="dialog" aria-modal="true" aria-label="Cobro mixto">
            <div class="drawer drawer--bill drawer--wide" @click.stop>
                <div class="drawer__grabber" aria-hidden="true"></div>
                <div class="drawer__head">
                    <button type="button" class="icon-btn icon-btn--back" @click="$store.bill.close()" aria-label="Cambiar método">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                    <div class="drawer__heading">
                        <div class="drawer__title">Pago mixto</div>
                        <div class="drawer__subtitle">Efectivo + tarjeta</div>
                    </div>
                    <div class="drawer__total">
                        <div class="lab">Total</div>
                        <div class="val" x-text="Number($store.bill.orderTotal).toFixed(2).replace('.',',') + ' €'"></div>
                    </div>
                    <button type="button" class="icon-btn" @click="$store.bill.closeMixed()" aria-label="Cerrar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="drawer__body">
                    <div style="display:flex;flex-direction:column;gap:16px">
                        <div style="font-family:var(--font-body);font-size:13px;color:var(--fg-muted);line-height:1.5">
                            Indica cuánto pagas en efectivo. El resto se cobará con tarjeta.
                        </div>
                        <div style="display:flex;align-items:center;gap:8px">
                            <label for="mixed-cash-input" style="font-family:var(--font-body);font-size:13px;font-weight:600;color:var(--fg-secondary);white-space:nowrap">Efectivo (€)</label>
                            <input id="mixed-cash-input"
                                   type="number" min="0" step="0.01"
                                   :max="$store.bill.orderTotal"
                                   :value="$store.bill.mixedCashAmount || ''"
                                   @input="$store.bill.mixedCashAmount = parseFloat($event.target.value) || 0"
                                   style="flex:1;padding:10px 12px;border-radius:10px;border:1px solid var(--glass-border);background:var(--glass-bg-surface);color:var(--fg-primary);font-family:var(--font-body);font-size:16px">
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:12px;border-radius:10px;background:var(--glass-bg-surface);border:1px solid var(--glass-border)">
                            <span style="font-family:var(--font-body);font-size:13px;color:var(--fg-muted)">Parte con tarjeta</span>
                            <span style="font-family:var(--font-display);font-weight:700;font-size:16px;color:var(--price-gold)"
                                  x-text="Number(Math.max(0, $store.bill.orderTotal - ($store.bill.mixedCashAmount || 0))).toFixed(2).replace('.',',') + ' €'"></span>
                        </div>
                    </div>
                </div>
                <div class="drawer__foot">
                    <button type="button" class="btn-primary"
                            :disabled="!$store.bill.mixedCashAmount || $store.bill.mixedCashAmount >= $store.bill.orderTotal"
                            @click="$store.bill.processMixed()">
                        Continuar con tarjeta
                    </button>
                    <button type="button" class="btn-text" @click="$store.bill.closeMixed()">Cancelar</button>
                </div>
            </div>
        </div>

        {{-- Mixed: Stripe de la parte tarjeta --}}
        <div class="scrim"
             x-show="$store.bill.showingMixedStripe"
             x-transition
             @click.self="$store.bill.close()"
             role="dialog" aria-modal="true" aria-label="Pago parte tarjeta">
            <div class="drawer drawer--bill" @click.stop>
                <div class="drawer__grabber" aria-hidden="true"></div>
                <div class="drawer__head">
                    <div class="drawer__heading">
                        <div class="drawer__title">Parte con tarjeta</div>
                        <div class="drawer__subtitle">Pago seguro · Stripe</div>
                    </div>
                    <div class="drawer__total">
                        <div class="lab">A pagar</div>
                        <div class="val" x-text="Number(Math.max(0, $store.bill.orderTotal - ($store.bill.mixedCashAmount || 0))).toFixed(2).replace('.',',') + ' €'"></div>
                    </div>
                    <button type="button" class="icon-btn" @click="$store.bill.close()" aria-label="Cerrar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="drawer__body">
                    <div id="stripe-card-element-mixed" style="padding:16px;border:1px solid var(--glass-border);border-radius:12px;background:var(--glass-bg-surface)"></div>
                    <template x-if="$store.bill.stripeError">
                        <p style="color:var(--color-red-400);font-size:13px;margin-top:8px" role="alert" x-text="$store.bill.stripeError"></p>
                    </template>
                </div>
                <div class="drawer__foot">
                    <button type="button" class="btn-primary"
                            @click="$store.bill.payMixedCard()"
                            :disabled="$store.bill.sending">
                        <span x-text="$store.bill.sending ? 'Procesando…' : 'Pagar con tarjeta'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════════
             TAPA MODAL — DS: .modal > .modal__panel
             ══════════════════════════════════════════════════════════════════════ --}}
        <div class="modal"
             x-show="$store.cart.showTapaModal"
             x-transition:enter="transition duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="$store.cart.closeTapaModal()"
             role="dialog" aria-modal="true" aria-label="Tapas disponibles">
            <div class="modal__panel" @click.stop>
                <div class="modal__head">
                    <div>
                        <div class="modal__title">¿Te llevas una tapa?</div>
                        <div class="modal__sub">
                            @if($tapaConfig && $tapaConfig->tapas_free)
                            La primera tapa es invitación de la casa 🍻
                            @else
                            Con cada bebida puedes añadir una tapa
                            @endif
                        </div>
                    </div>
                    <button type="button" class="icon-btn" @click="$store.cart.closeTapaModal()" aria-label="Cerrar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px">
                    <template x-for="tapa in $store.cart.tapaProducts" :key="tapa.id">
                        <div class="tapa"
                             @click="$store.cart.addTapa(tapa)">
                            <div class="tapa__photo" aria-hidden="true">🍽️</div>
                            <div class="tapa__main">
                                <div class="tapa__name" x-text="tapa.name"></div>
                                <div class="tapa__price"
                                     :class="tapa.price === 0 ? 'tapa__price--free' : ''"
                                     x-text="tapa.price === 0 ? 'Gratis' : '+ ' + Number(tapa.price).toFixed(2).replace('.',',') + ' €'"></div>
                            </div>
                        </div>
                    </template>
                </div>
                <div style="display:flex;gap:8px;margin-top:4px">
                    <button type="button" class="btn-secondary" style="flex:1" @click="$store.cart.closeTapaModal()">Ahora no</button>
                </div>
            </div>
        </div>

        {{-- ── Daily menu dialogs (componentes Blade existentes) --}}
        <x-daily-menu-stepper :hash="$table->unique_hash" />
        <x-daily-menu-exclusivity-dialog />
        <x-daily-menu-timing-control />

    </div>{{-- /carta (Alpine) --}}
</body>
</html>
