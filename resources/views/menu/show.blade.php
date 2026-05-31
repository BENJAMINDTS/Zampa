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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/carta/colors_and_type.css') }}">
    <link rel="stylesheet" href="{{ asset('css/carta/styles.css') }}">
    <meta name="stripe-key" content="{{ $stripePublicKey }}">
    <script src="{{ asset('js/allergen-pictograms.js') }}"></script>
    <script>window.ZAMPA_ALLERGEN_LABELS = @json(\App\Models\Ingredient::ALLERGEN_TYPES);</script>
    <script src="https://js.stripe.com/v3/" defer></script>

    <!-- Dark mode: señal para que x-init de .carta la aplique antes del primer paint -->
    <script>
        (function () {
            const saved = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.setAttribute('data-dark-pending', '1');
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

        /* ── Layout crítico — garantiza que .carta llene el viewport ── */
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }
        .carta { height: 100dvh; height: 100vh; width: 100%; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>

    @php
        // Mapeo de nombre de categoría → emoji fallback (coincidencia exacta luego parcial).
        $getEmoji = static function (string $categoryName): string {
            $n = mb_strtolower(trim($categoryName));

            $exact = [
                'hamburguesas' => '🍔', 'hamburguesa' => '🍔', 'burgers' => '🍔', 'burger' => '🍔',
                'pizzas' => '🍕', 'pizza' => '🍕',
                'ensaladas' => '🥗', 'ensalada' => '🥗',
                'postres' => '🍮', 'postre' => '🍮',
                'tartas' => '🍰', 'tarta' => '🍰', 'pasteles' => '🎂', 'pastel' => '🎂',
                'helados' => '🍦', 'helado' => '🍦',
                'sopas' => '🍲', 'sopa' => '🍲',
                'cremas' => '🥣', 'crema' => '🥣', 'caldos' => '🥣', 'caldo' => '🥣',
                'entrantes' => '🥙', 'entrante' => '🥙',
                'aperitivos' => '🫒', 'aperitivo' => '🫒',
                'tapas' => '🫕', 'tapa' => '🫕',
                'pinchos' => '🍢', 'pincho' => '🍢',
                'montaditos' => '🥖', 'montadito' => '🥖',
                'bocadillos' => '🥖', 'bocadillo' => '🥖',
                'sandwiches' => '🥪', 'sandwich' => '🥪',
                'wraps' => '🌯', 'wrap' => '🌯',
                'carnes' => '🥩', 'carne' => '🥩',
                'asados' => '🍖', 'asado' => '🍖',
                'pollos' => '🍗', 'pollo' => '🍗',
                'pescados' => '🐟', 'pescado' => '🐟',
                'mariscos' => '🦞', 'marisco' => '🦞',
                'pastas' => '🍝', 'pasta' => '🍝',
                'arroces' => '🍚', 'arroz' => '🍚',
                'paellas' => '🥘', 'paella' => '🥘',
                'verduras' => '🥦', 'verdura' => '🥦',
                'vegetarianos' => '🥦', 'vegetariano' => '🥦',
                'veganos' => '🥦', 'vegano' => '🥦',
                'bebidas' => '🥤', 'bebida' => '🥤',
                'refrescos' => '🥤', 'refresco' => '🥤',
                'cervezas' => '🍺', 'cerveza' => '🍺',
                'vinos' => '🍷', 'vino' => '🍷',
                'cócteles' => '🍸', 'cocteles' => '🍸', 'coctel' => '🍸',
                'copas' => '🥂', 'copa' => '🥂',
                'zumos' => '🍊', 'zumo' => '🍊',
                'cafés' => '☕', 'café' => '☕', 'cafes' => '☕', 'cafe' => '☕',
                'infusiones' => '🍵', 'infusión' => '🍵', 'infusion' => '🍵',
                'especiales' => '⭐', 'especial' => '⭐',
                'recomendados' => '⭐', 'sugerencias' => '⭐',
                'desayunos' => '🥐', 'desayuno' => '🥐',
                'brunch' => '🥞',
            ];

            if (isset($exact[$n])) {
                return $exact[$n];
            }

            $partial = [
                'hambur' => '🍔', 'burger' => '🍔',
                'pizza'  => '🍕',
                'ensalad' => '🥗',
                'postre' => '🍮', 'tarta' => '🍰', 'helado' => '🍦', 'pastel' => '🎂',
                'sopa' => '🍲', 'crema' => '🥣', 'caldo' => '🥣', 'gazpacho' => '🍅',
                'entrante' => '🥙', 'aperitiv' => '🫒',
                'tapa' => '🫕', 'pincho' => '🍢',
                'bocadill' => '🥖', 'montadit' => '🥖',
                'sandwich' => '🥪', 'wrap' => '🌯',
                'carne' => '🥩', 'ternera' => '🥩', 'asado' => '🍖', 'cordero' => '🍖',
                'pollo' => '🍗',
                'pescad' => '🐟', 'bacalao' => '🐟', 'salmon' => '🐟',
                'marisco' => '🦞', 'gambas' => '🦐', 'langostino' => '🦐',
                'pasta' => '🍝',
                'arroz' => '🍚', 'paella' => '🥘', 'risotto' => '🍚',
                'verdura' => '🥦', 'vegano' => '🥦', 'vegeta' => '🥦',
                'bebida' => '🥤', 'refresc' => '🥤',
                'cervez' => '🍺',
                'vino' => '🍷',
                'coctel' => '🍸', 'cóctel' => '🍸',
                'copa' => '🥂',
                'zumo' => '🍊',
                'café' => '☕', 'cafe' => '☕', 'coffee' => '☕',
                'infusi' => '🍵',
                'especial' => '⭐', 'recomend' => '⭐', 'sugerid' => '⭐',
                'desayun' => '🥐', 'brunch' => '🥞',
            ];

            foreach ($partial as $keyword => $emoji) {
                if (str_contains($n, $keyword)) {
                    return $emoji;
                }
            }

            return '🍽️';
        };

        // Datos de productos para Alpine. Se calculan aquí para evitar que
        // la directiva json() reciba una expresión multi-línea con corchetes anidados,
        // lo que confunde al parser de Blade.
        $alpinePhotoCycle  = 0;
        $productsForAlpine = $categories->flatMap(function ($category) use (&$alpinePhotoCycle, $getEmoji) {
            return $category->products->map(function ($p) use ($category, &$alpinePhotoCycle, $getEmoji) {
                $alpinePhotoCycle++;
                return [
                    'id'           => $p->id,
                    'name'         => $p->name,
                    'description'  => $p->description,
                    'imageUrl'     => $p->image ? Storage::url($p->image) : null,
                    'photoStyle'   => (($alpinePhotoCycle - 1) % 6) + 1,
                    'categoryName' => $category->name,
                    'emoji'        => $getEmoji($category->name),
                    'price'        => $p->variants->isNotEmpty() ? (float) $p->variants->min('price') : (float) $p->price,
                    'hasVariants'  => $p->variants->isNotEmpty(),
                    'variants'     => $p->variants->map(fn ($v) => [
                        'id'    => $v->id,
                        'name'  => $v->name,
                        'price' => (float) $v->price,
                    ])->values(),
                    'categoryId'   => $category->id,
                    'destination'  => $category->destination,
                    'allergenTypes' => $p->ingredients->where('is_allergen', true)
                        ->flatMap(fn ($i) => $i->allergen_types ?? [])
                        ->unique()->values()->toArray(),
                    'removable'    => $p->ingredients
                        ->filter(fn ($i) => $i->pivot->is_removable)
                        ->map(fn ($i) => ['id' => $i->id, 'name' => $i->name])
                        ->values(),
                    'extras'       => $p->ingredients
                        ->filter(fn ($i) => $i->pivot->is_extra)
                        ->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'price' => (float) $i->pivot->extra_price])
                        ->values(),
                ];
            });
        })->values();

        // El precio de cada tapa se resuelve en backend (getPriceForProduct) para que
        // Alpine no tenga que replicar la lógica de modalidad de precio.
        $tapaProductsForAlpine = $tapaProducts->map(fn ($p) => [
            'id'          => $p->id,
            'name'        => $p->name,
            'description' => $p->description ?? '',
            'price'       => $tapaConfig ? (float) $tapaConfig->getPriceForProduct($p) : (float) $p->price,
            'emoji'       => $getEmoji('tapas'),
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
            'kitchenOpen'         => $kitchenOpen,
        ];
    @endphp

    {{-- Los datos se inyectan en un <script> separado para evitar conflictos
         de escapado al pasar JSON como argumento en x-data. --}}
    <script id="menu-products" type="application/json">@json($productsForAlpine)</script>
    <script id="tapa-config" type="application/json">@json($tapaConfigForAlpine)</script>
    <script id="tapa-products" type="application/json">@json($tapaProductsForAlpine)</script>
    <script id="order-items" type="application/json">@json($activeOrderItemsForAlpine)</script>
    <script id="menu-context" type="application/json">@json($menuContext)</script>

    {{-- Inicializa los badges de alérgenos con los pictogramas oficiales del DS --}}
    <script>
    (function initAllergenBadges() {
        function fill() {
            var p = window.ZAMPA_PICTOGRAMS;
            var labels = window.ZAMPA_ALLERGEN_LABELS || {};
            if (!p) return;
            document.querySelectorAll('svg[data-al]').forEach(function (svg) {
                var slug = svg.getAttribute('data-al');
                var c = p.ALLERGEN_COLORS[slug] || '#888';
                var inner = p.pictogramSVG(slug);
                if (!inner) return;
                svg.style.setProperty('--c', c);
                svg.innerHTML = inner;
            });
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fill);
        } else {
            fill();
        }
        document.addEventListener('alpine:initialized', fill);
    })();
    </script>
</head>

<body>

    {{-- Skip to content --}}
    <a href="#main-content" class="sr-only"
       style="--skip-link:1"
       onfocus="this.style.position='absolute';this.style.top='1rem';this.style.left='1rem';this.style.zIndex='9999';this.style.padding='8px 16px';this.style.background='var(--bg-base)';this.style.color='var(--brand-primary)';this.style.borderRadius='6px';this.style.boxShadow='var(--shadow-pop)';this.style.fontFamily='var(--font-body)';this.style.fontWeight='600';this.style.width='auto';this.style.height='auto';this.style.clip='auto';this.style.overflow='visible'"
       onblur="this.removeAttribute('style')">
        Saltar al contenido principal
    </a>

    {{-- ── Componente Alpine raíz + wrapper DS ───────────────────── --}}
    <div x-data="menuFilters()"
         class="carta"
         data-theme="{{ $theme }}"
         x-init="
            if (document.documentElement.getAttribute('data-dark-pending') === '1') {
                $el.dataset.theme = 'dark';
                document.documentElement.removeAttribute('data-dark-pending');
            }
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
                    <div class="header__table">{{ $table->name }}{{ $table->zone ? ' · ' . $table->zone->name : '' }}</div>
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

                {{-- Toggle dark/light — data-theme en .carta, html conserva el tema base --}}
                <button class="theme-toggle"
                        x-data="{
                            dark: document.querySelector('.carta')?.dataset.theme === 'dark',
                            toggle() {
                                this.dark = !this.dark;
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

            {{-- Los pictogramas de alérgenos se inyectan vía allergen-pictograms.js (ZAMPA_PICTOGRAMS) --}}

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
                 style="display:none"
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

        {{-- ── Indicador de tapas (DS: .tapas-indicator) — reactivo Alpine ──── --}}
        @if($tapaConfig && $tapaConfig->tapas_enabled)
        <button type="button"
                class="tapas-indicator"
                x-show="$store.cart.tapaConfig.enabled && $store.cart._barItemsCount > 0"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                style="display:none"
                @click="$store.cart.showTapaModal = true"
                :aria-label="Math.max(0, $store.cart._barItemsCount - $store.cart._variantsUsed) + ' ' + (Math.max(0, $store.cart._barItemsCount - $store.cart._variantsUsed) === 1 ? 'tapa' : 'tapas') + ' ' + ($store.cart.tapaConfig.free ? 'de cortesía' : 'incluidas') + ' — Pedir'">
            <span class="tapas-indicator__glyph" aria-hidden="true">🍻</span>
            <span class="tapas-indicator__copy">
                <strong x-text="Math.max(0, $store.cart._barItemsCount - $store.cart._variantsUsed)"></strong>
                <span x-text="(Math.max(0, $store.cart._barItemsCount - $store.cart._variantsUsed) === 1 ? 'tapa' : 'tapas') + ' ' + ($store.cart.tapaConfig.free ? 'de cortesía' : 'incluidas')"></span>
            </span>
            <span class="tapas-indicator__tokens" aria-hidden="true">
                <template x-for="i in Array.from({length: Math.min($store.cart._barItemsCount, 8)}, (_, idx) => idx)" :key="i">
                    <span :class="'tk' + (i < $store.cart._variantsUsed ? ' tk--used' : '')"></span>
                </template>
                <span class="tapas-indicator__tokensMore"
                      x-show="$store.cart._barItemsCount > 8"
                      x-text="'+' + ($store.cart._barItemsCount - 8)"></span>
            </span>
            <span class="tapas-indicator__cta">Pedir →</span>
        </button>
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
                            :class="activeCategory === {{ $cat->id }} ? 'chip--active' : ''"
                            @click="setCategory({{ $cat->id }})"
                            :aria-pressed="(activeCategory === {{ $cat->id }}).toString()">
                        {{ $cat->name }}
                    </button>
                    @endforeach
                </div>
                {{-- Botón alérgenos fijo a la derecha (siempre visible) --}}
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
            </nav>

            {{-- DS: .scrim > .drawer.drawer--allergens (sheet móvil) --}}
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
                            @foreach(\App\Models\Ingredient::ALLERGEN_TYPES as $slug => $name)
                            <button type="button"
                                    class="allergen-chip"
                                    :class="activeAllergens.includes('{{ $slug }}') ? 'allergen-chip--active' : ''"
                                    @click="toggleAllergen('{{ $slug }}')"
                                    :aria-pressed="activeAllergens.includes('{{ $slug }}').toString()">
                                <svg class="allergen-img"
                                     data-al="{{ $slug }}"
                                     viewBox="0 0 100 100"
                                     width="22" height="22"
                                     role="img"
                                     aria-label="{{ $name }}">
                                </svg>
                                <span>{{ $name }}</span>
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
        </div>{{-- /allergensOpen x-data --}}
        @endif

        {{-- ══════════════════════════════════════════════════════════════════════
             CARTA MAIN — .carta__main > .carta__filters + .carta__body
             ══════════════════════════════════════════════════════════════════════ --}}
        <div class="carta__main">

            {{-- DS: .carta__filters — sidebar (tablet + desktop, hidden on mobile via CSS) --}}
            @if($businessOpen && $categories->isNotEmpty())
            <div class="carta__filters" aria-label="Filtros">

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

                {{-- Alérgenos — siempre los 14 del Reglamento UE 1169/2011 --}}
                <div class="filter-section">
                    <div class="filter-section__label">Alérgenos</div>
                    <div class="filter-list filter-list--allergens" role="list">
                        @foreach(\App\Models\Ingredient::ALLERGEN_TYPES as $slug => $name)
                        <div class="filter-list__item"
                             :class="activeAllergens.includes('{{ $slug }}') ? 'filter-list__item--active' : ''"
                             @click="toggleAllergen('{{ $slug }}')"
                             @keydown.enter="toggleAllergen('{{ $slug }}')"
                             role="button" tabindex="0"
                             :aria-pressed="activeAllergens.includes('{{ $slug }}').toString()">
                            <span class="filter-list__check" aria-hidden="true">
                                <svg x-show="activeAllergens.includes('{{ $slug }}')"
                                     width="10" height="10" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <svg class="allergen-img"
                                 data-al="{{ $slug }}"
                                 viewBox="0 0 100 100"
                                 width="22" height="22"
                                 role="img"
                                 aria-label="{{ $name }}">
                            </svg>
                            <span style="font-size:13px">{{ $name }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

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
                             role="button"
                             tabindex="0"
                             x-show="isProductVisible({{ $product->id }})"
                             x-transition
                             @click="openProduct(products.find(p => p.id === {{ $product->id }}))"
                             @keydown.enter.prevent="openProduct(products.find(p => p.id === {{ $product->id }}))"
                             @keydown.space.prevent="openProduct(products.find(p => p.id === {{ $product->id }}))"
                             aria-label="Ver detalle de {{ $product->name }}">

                            {{-- Foto --}}
                            @if($product->image)
                            <div class="pcard__photo">
                                <img src="{{ Storage::url($product->image) }}"
                                     alt="Foto de {{ $product->name }}"
                                     loading="lazy" decoding="async"
                                     style="width:100%;height:100%;object-fit:cover">
                            </div>
                            @else
                            <div class="pcard__photo pcard__photo--food-{{ $photoStyle }}" aria-hidden="true">{{ $getEmoji($category->name) }}</div>
                            @endif

                            {{-- Body --}}
                            <div class="pcard__body">

                                {{-- Badges alérgenos — pictogramas oficiales DS --}}
                                <div class="pcard__badges">
                                    @foreach($allergenSlugs->take(5) as $slug)
                                    <svg class="allergen-img"
                                         data-al="{{ $slug }}"
                                         viewBox="0 0 100 100"
                                         width="22" height="22"
                                         role="img"
                                         aria-label="Contiene {{ \App\Models\Ingredient::ALLERGEN_TYPES[$slug] ?? $slug }}">
                                    </svg>
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
                                    {{-- Tapa de cortesía: bloqueado --}}
                                    <span x-show="$store.cart.items.some(i => i.productId === {{ $product->id }} && !i.variantId && i.isTapa)"
                                          style="font-family:var(--font-body);font-size:11px;font-weight:700;color:var(--color-amber-800);background:linear-gradient(135deg,#F5DC92,#ECC25A);padding:3px 8px;border-radius:9999px;"
                                          @click.stop>
                                        🫕 Cortesía
                                    </span>
                                    {{-- btn-add cuando qty = 0 y no es tapa --}}
                                    <button type="button"
                                            class="btn-add"
                                            x-show="!$store.cart.items.some(i => i.productId === {{ $product->id }} && !i.variantId)"
                                            @click.stop="$store.cart.add(products.find(p => p.id === {{ $product->id }}))"
                                            aria-label="Añadir {{ $product->name }}">+</button>
                                    {{-- qty control cuando qty > 0 y NO es tapa --}}
                                    <div class="qty"
                                         x-show="$store.cart.items.some(i => i.productId === {{ $product->id }} && !i.variantId && !i.isTapa)"
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
        <template x-if="$store.cart.barShouldShow">
        <button type="button"
                :class="$store.cart._barLeaving ? 'cart-bar cart-bar--leaving' : 'cart-bar'"
                @click="$store.cart.open = true"
                :aria-label="'Ver pedido — ' + $store.cart.displayCount + ($store.cart.displayCount === 1 ? ' artículo' : ' artículos') + ', total ' + Number($store.cart.displayTotal).toFixed(2).replace('.',',') + ' €'">
            <div class="cart-bar__left">
                <span class="cart-bar__icon">
                    <svg aria-hidden="true" width="18" height="18" fill="none"
                         stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="cart-bar__count" x-text="$store.cart.displayCount" aria-hidden="true"></span>
                </span>
                <div style="display:flex;flex-direction:column;gap:2px;align-items:flex-start">
                    <span x-text="$store.cart.displayCount + ($store.cart.displayCount === 1 ? ' artículo' : ' artículos')"></span>
                    <span class="cart-bar__total"
                          x-text="Number($store.cart.displayTotal).toFixed(2).replace('.',',') + ' €'"></span>
                </div>
            </div>
            <span class="cart-bar__cta">Ver pedido</span>
        </button>
        </template>

        {{-- ══════════════════════════════════════════════════════════════════════
             FAB CLUSTER — DS: .fab-cluster (Mis pedidos + Cuenta + Mi ticket)
             ══════════════════════════════════════════════════════════════════════ --}}
        <div class="fab-cluster"
             x-show="$store.bill.active && !$store.chat.open"
             x-transition:enter="transition duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            {{-- fab--orders: Mis pedidos (visible cuando hay pedido activo no pagado) --}}
            <button type="button"
                    class="fab fab--orders"
                    x-show="$store.bill.active && !$store.bill.paymentDone"
                    @click="$store.bill.open()"
                    :aria-label="'Mi pedido activo — ver cuenta'">
                <span class="fab--orders__ic" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 3h14v18l-2-1.5-2 1.5-2-1.5-2 1.5-2-1.5-2 1.5-2-1.5L5 21V3z"/>
                        <path d="M8 8h8M8 12h8M8 16h5" stroke-width="2.4"/>
                    </svg>
                    <span class="fab--orders__badge" aria-hidden="true">1</span>
                </span>
                <span class="fab--orders__label">Mi pedido</span>
            </button>

            {{-- fab--bill: Cuenta --}}
            <button type="button"
                    :class="'fab fab--bill' + ($store.bill.active && !$store.bill.paymentDone ? ' fab--in-cluster' : '')"
                    x-show="!$store.bill.paymentDone"
                    @click="$store.bill.open()"
                    :disabled="$store.bill.requested && !$store.bill.paymentDone ? undefined : undefined"
                    :aria-label="$store.bill.requested ? 'Cuenta solicitada' : 'Solicitar la cuenta'">
                <span class="fab__ic" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 2v20l3-2 3 2 3-2 3 2 3-2V2H4z"/>
                        <path d="M8 7h8M8 11h8M8 15h6"/>
                    </svg>
                </span>
                <span class="fab__label"
                      x-text="$store.bill.requested ? 'Solicitada' : 'Cuenta'"></span>
            </button>

            {{-- fab--ticket: Mi ticket (visible tras pago completado) --}}
            <template x-if="$store.bill.paymentDone && $store.bill.paidOrderId">
                <a :href="$store.bill.ticketDownloadBase + '/' + $store.bill.paidOrderId + '/download?hash=' + $store.bill.tableHash"
                   target="_blank" rel="noopener noreferrer"
                   class="fab fab--ticket fab--in-cluster"
                   :aria-label="'Descargar ticket #' + $store.bill.paidOrderId">
                    <span class="fab__ic" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 2v20l3-2 3 2 3-2 3 2 3-2V2H4z"/>
                            <path d="M8 7h8M8 11h8M8 15h6"/>
                        </svg>
                    </span>
                    <span class="fab__label">Mi ticket</span>
                </a>
            </template>

        </div>

        {{-- ══════════════════════════════════════════════════════════════════════
             CART DRAWER — DS: .scrim > .drawer.drawer--cart
             ══════════════════════════════════════════════════════════════════════ --}}
        <div class="scrim"
             x-show="$store.cart.open"
             x-transition:enter="transition duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition duration-260"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="$store.cart.close()"
             role="dialog" aria-modal="true" aria-label="Tu pedido">
            <div class="drawer drawer--cart"
                 :class="{ 'is-closing': $store.cart.closing }"
                 @click.stop>
                <div class="drawer__grabber" aria-hidden="true"></div>
                {{-- Cart head --}}
                <div class="cart-head">
                    <div class="cart-head__row">
                        <div class="cart-head__heading">
                            <span class="cart-head__kicker">Tu comanda</span>
                            <h2 class="cart-head__title">Tu&nbsp;pedido</h2>
                        </div>
                        <button type="button" class="icon-btn cart-head__close"
                                @click="$store.cart.close()" aria-label="Cerrar">
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
                                <span class="cart-empty__plate"></span>
                                <span class="cart-empty__fork"></span>
                                <span class="cart-empty__knife"></span>
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
                                                {{-- Tapa de cortesía: cantidad fija, sin controles --}}
                                                <template x-if="item.isTapa">
                                                    <span style="font-family:var(--font-body);font-size:11px;font-weight:700;color:var(--color-amber-800);background:linear-gradient(135deg,#F5DC92,#ECC25A);padding:3px 8px;border-radius:9999px;white-space:nowrap;">
                                                        🫕 Cortesía
                                                    </span>
                                                </template>
                                                {{-- Ítem normal: qty control --}}
                                                <template x-if="!item.isTapa">
                                                    <div class="qty">
                                                        <button class="qty-minus"
                                                                @click="$store.cart.dec(item._key)"
                                                                aria-label="Quitar uno">−</button>
                                                        <span class="qty-n" x-text="item.quantity"></span>
                                                        <button class="qty-plus"
                                                                @click="$store.cart.inc(item._key)"
                                                                aria-label="Añadir uno">+</button>
                                                    </div>
                                                </template>
                                                <span class="citem__price"
                                                      x-text="Number($store.cart.lineTotal(item)).toFixed(2).replace('.',',') + ' €'"></span>
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
                        <template x-if="$store.cart._tapaPending">
                            <button type="button"
                                    class="cart-foot__tapaPending"
                                    @click="$store.cart.showTapaModal = true"
                                    aria-live="polite">
                                <span aria-hidden="true">🫕</span>
                                <span>Tienes <strong x-text="Math.max(0, $store.cart._barItemsCount - $store.cart._variantsUsed)"></strong> tapa<template x-if="Math.max(0, $store.cart._barItemsCount - $store.cart._variantsUsed) !== 1"><span>s</span></template> disponible<template x-if="Math.max(0, $store.cart._barItemsCount - $store.cart._variantsUsed) !== 1"><span>s</span></template> — Elegir →</span>
                            </button>
                        </template>
                        <button type="button"
                                class="cart-foot__cta"
                                :class="{ 'cart-foot__cta--sending': $store.cart.sending }"
                                @click="$store.cart.send()"
                                :disabled="$store.cart.sending || $store.cart._tapaPending"
                                :title="$store.cart._tapaPending ? 'Elige tus tapas antes de enviar' : ''">
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
             BILL DRAWER — DS: scrim único con step machine (BillFlow)
             ══════════════════════════════════════════════════════════════════════ --}}

        {{-- ── Scrim único — visible mientras step !== '' ─────────────────────── --}}
        <div class="scrim"
             x-show="$store.bill.step !== ''"
             x-transition:enter="transition duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="$store.bill.close()"
             @keydown.escape.window="if ($store.bill.step !== '') $store.bill.close()"
             role="dialog" aria-modal="true" aria-labelledby="bill-drawer-title">

            {{-- Drawer: ancho extendido para split/mixed --}}
            <div :class="'drawer drawer--bill' + (['split','splitItems','splitEq','splitTip','splitPay','mixed','mixedTip','mixedPay'].includes($store.bill.step) ? ' drawer--wide' : '')"
                 @click.stop>
                <div class="drawer__grabber" aria-hidden="true"></div>

                {{-- ── CABECERA DINÁMICA ──────────────────────────────────────── --}}
                <div class="drawer__head">

                    {{-- Botón atrás (pasos intermedios) --}}
                    <template x-if="['cashConfirm','tip','pay','split','splitItems','splitEq','mixed','mixedTip','splitTip','splitPay','mixedPay'].includes($store.bill.step)">
                        <button type="button" class="icon-btn icon-btn--back"
                                @click="$store.bill.step === 'splitItems' ? $store.bill.closeSplitItems()
                                    : $store.bill.step === 'splitEq'   ? $store.bill.closeSplitEq()
                                    : $store.bill.step === 'splitPay'  ? $store.bill.closeSplitPayment()
                                    : $store.bill.step === 'mixedPay'  ? $store.bill.closeMixedPayment()
                                    : $store.bill.backToMethod()"
                                aria-label="Cambiar método de pago">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"/>
                            </svg>
                        </button>
                    </template>

                    <div class="drawer__heading">
                        <div class="drawer__title" id="bill-drawer-title"
                             x-text="{
                                method:      'Solicitar la cuenta',
                                cashConfirm: '¿Quieres dejar propina?',
                                cashDone:    'Camarero avisado',
                                tip:         'Elige tu propina',
                                pay:         'Pago con tarjeta',
                                cardDone:    '¡Pago confirmado!',
                                split:       'Cobro partido',
                                splitItems:  'Pagar por ítems',
                                splitEq:     'Dividir a partes iguales',
                                splitTip:    'Propina (parte tarjeta)',
                                mixed:       'Pago mixto',
                                mixedTip:    'Propina (parte tarjeta)',
                                mixedPay:    'Parte con tarjeta',
                                splitPay:    'Pago con tarjeta',
                                splitDone:   '¡Parte pagada!',
                                mixedWaiter: 'Esperando al camarero',
                                mixedDone:   'Cuenta cerrada',
                             }[$store.bill.step] || 'Cuenta'">
                        </div>
                        <div class="drawer__subtitle"
                             x-text="{
                                method:      '¿Cómo quieres pagar?',
                                cashConfirm: 'Pago en efectivo · propina opcional',
                                cashDone:    'Pasará a tu mesa en unos instantes',
                                tip:         'Añade una propina al servicio',
                                pay:         ($store.bill.tipPercent || 0) + '% de propina incluida',
                                cardDone:    '¡Gracias! Hasta pronto',
                                split:       'Dividís la cuenta en tiempo real',
                                splitItems:  'Marca los platos que quieres pagar tú',
                                splitEq:     'El total se divide entre todos',
                                splitTip:    'Propina sobre tu parte con tarjeta',
                                mixed:       'Efectivo + tarjeta',
                                mixedTip:    'Propina sobre la parte con tarjeta',
                                mixedPay:    'Pago seguro · Stripe',
                                splitPay:    'Pago seguro · Stripe',
                                splitDone:   'Tu parte ha quedado saldada',
                                mixedWaiter: 'Tarjeta confirmada · efectivo pendiente',
                                mixedDone:   '¡Gracias! Hasta pronto',
                             }[$store.bill.step] || ''">
                        </div>
                    </div>

                    {{-- Total (se oculta en las pantallas de éxito) --}}
                    <template x-if="!['cashDone','cardDone','splitDone','mixedWaiter','mixedDone'].includes($store.bill.step)">
                        <div class="drawer__total" aria-label="Total a pagar">
                            <div class="lab">
                                <span x-text="['splitItems','splitTip','splitEq'].includes($store.bill.step) ? 'Total' : 'Total'"></span>
                            </div>
                            <div class="val"
                                 x-text="Number(
                                    $store.bill.step === 'cashConfirm' ? $store.bill.cashGrandTotal :
                                    $store.bill.step === 'tip'         ? ($store.bill.orderTotal + ($store.bill.tipAmount || 0)) :
                                    $store.bill.step === 'pay'         ? $store.bill.grandTotal :
                                    $store.bill.step === 'mixedTip'    ? $store.bill.mixedTipGrandTotal :
                                    $store.bill.step === 'mixedPay'    ? $store.bill.mixedTipGrandTotal :
                                    $store.bill.step === 'splitTip'    ? $store.bill.splitTipGrandTotal :
                                    $store.bill.step === 'splitPay'    ? $store.bill.splitTipGrandTotal :
                                    $store.bill.orderTotal
                                 ).toFixed(2).replace('.',',') + ' €'">
                            </div>
                        </div>
                    </template>

                    <button type="button" class="icon-btn" @click="$store.bill.close()" aria-label="Cerrar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- ── CUERPO: contenido por step ──────────────────────────────── --}}
                <div class="drawer__body">

                    {{-- ── method: selector de método ─── --}}
                    <div x-show="$store.bill.step === 'method'">
                        <div class="method-list">
                            <button type="button" class="method method--cash"
                                    @click="$store.bill.openCashTip()">
                                <span class="method__ic" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="6" width="20" height="12" rx="2"/>
                                        <circle cx="12" cy="12" r="3"/>
                                        <path d="M6 10h.01M18 10h.01M6 14h.01M18 14h.01"/>
                                    </svg>
                                </span>
                                <span class="method__txt">
                                    <span class="method__nm">Efectivo</span>
                                    <span class="method__ds">Pagas al camarero · puedes añadir propina</span>
                                </span>
                                <span class="method__chev" aria-hidden="true">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 6 15 12 9 18"/></svg>
                                </span>
                            </button>

                            <button type="button" class="method method--card"
                                    @click="$store.bill.openCardPayment()">
                                <span class="method__ic" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="5" width="20" height="14" rx="2"/>
                                        <path d="M2 10h20M6 15h4"/>
                                    </svg>
                                </span>
                                <span class="method__txt">
                                    <span class="method__nm">Tarjeta</span>
                                    <span class="method__ds">Pago seguro · Stripe</span>
                                </span>
                                <span class="method__chev" aria-hidden="true">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 6 15 12 9 18"/></svg>
                                </span>
                            </button>

                            <button type="button" class="method method--mixed"
                                    @click="$store.bill.openMixed()">
                                <span class="method__ic" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 6 15 12 9 18"/></svg>
                                </span>
                            </button>

                            @if($splitPaymentEnabled)
                            <button type="button" class="method method--split"
                                    @click="$store.bill.openSplit()">
                                <span class="method__ic" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 6 15 12 9 18"/></svg>
                                </span>
                            </button>
                            @endif
                        </div>
                    </div>

                    {{-- ── cashConfirm: efectivo + propina opcional ─── --}}
                    <div x-show="$store.bill.step === 'cashConfirm'">
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
                                                :class="$store.bill.cashTipPercent === pct ? 'is-active' : ''"
                                                :aria-pressed="($store.bill.cashTipPercent === pct).toString()"
                                                @click="$store.bill.setCashTipPercent(pct)">
                                            <span class="cashTip__pct" x-text="pct + '%'"></span>
                                            <span class="cashTip__amt"
                                                  x-text="Number(Math.round($store.bill.orderTotal * pct) / 100).toFixed(2).replace('.',',') + ' €'"></span>
                                        </button>
                                    </template>
                                </div>
                                <div class="cashTip__input">
                                    <label for="cash-tip-bill" class="sr-only">Propina personalizada en euros</label>
                                    <input id="cash-tip-bill"
                                           type="number" min="0" step="0.50" inputmode="decimal"
                                           placeholder="Otra cantidad…"
                                           @input="$store.bill.updateCustomCashTip($event.target.value)"
                                           :value="$store.bill.cashTipPercent === null && $store.bill.cashTipAmount > 0 ? $store.bill.cashTipAmount : ''">
                                    <span class="cashTip__currency" aria-hidden="true">€</span>
                                </div>
                            </section>
                            <section class="cashTip__summary" aria-live="polite">
                                <div class="cashTip__row">
                                    <span>Pedido</span>
                                    <span class="v" x-text="Number($store.bill.orderTotal).toFixed(2).replace('.',',') + ' €'"></span>
                                </div>
                                <div class="cashTip__row cashTip__row--tip"
                                     x-show="($store.bill.cashTipAmount || 0) > 0">
                                    <span>Propina</span>
                                    <span class="v" x-text="'+ ' + Number($store.bill.cashTipAmount || 0).toFixed(2).replace('.',',') + ' €'"></span>
                                </div>
                                <div class="cashTip__row cashTip__row--total">
                                    <span>Total a pagar</span>
                                    <span class="v" x-text="Number($store.bill.cashGrandTotal || $store.bill.orderTotal).toFixed(2).replace('.',',') + ' €'"></span>
                                </div>
                            </section>
                            <p class="cashTip__info">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                                <span>La propina se entrega directamente al camarero en efectivo.</span>
                            </p>
                        </div>
                    </div>

                    {{-- ── cashDone: PaymentSuccess cash ─── --}}
                    <div x-show="$store.bill.step === 'cashDone'">
                        <div class="ps ps--cash">
                            <div class="ps-medal ps-medal--cash">
                                <span class="ps-medal__ring" aria-hidden="true"></span>
                                <span class="ps-medal__disc" aria-hidden="true">
                                    <span class="ps-medal__emoji">💶</span>
                                </span>
                            </div>
                            <h3 class="ps__head">Camarero avisado</h3>
                            <div class="ps__amount">
                                <span class="ps__amountLab">Total solicitado</span>
                                <span class="ps__amountVal"
                                      x-text="Number($store.bill.cashGrandTotal || $store.bill.orderTotal).toFixed(2).replace('.',',') + ' €'"></span>
                                <span class="ps__amountTip"
                                      x-show="($store.bill.cashTipAmount || 0) > 0"
                                      x-text="'incluye ' + Number($store.bill.cashTipAmount).toFixed(2).replace('.',',') + ' € de propina'">
                                </span>
                            </div>
                            <div class="ps__meta">
                                <span class="ps__chip">{{ $table->name }}</span>
                                <span class="ps__chip ps__chip--method">Efectivo</span>
                            </div>
                            <p class="ps__note">Pasará a tu mesa en unos instantes a cobrar y traerte el cambio.</p>
                            <div class="ps__cta">
                                <template x-if="$store.bill.paidOrderId">
                                    <a :href="$store.bill.ticketDownloadBase + '/' + $store.bill.paidOrderId + '/download?hash=' + $store.bill.tableHash"
                                       target="_blank" rel="noopener noreferrer"
                                       class="ps__btn ps__btn--soft">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                                        </svg>
                                        Ticket de cortesía
                                    </a>
                                </template>
                                <button type="button" class="ps__btn ps__btn--ghost" @click="$store.bill.close()">Ver carta</button>
                            </div>
                        </div>
                    </div>

                    {{-- ── tip: selector de propina para tarjeta ─── --}}
                    <div x-show="$store.bill.step === 'tip'">
                        <div class="tip-row" role="group" aria-label="Porcentaje de propina">
                            <template x-for="pct in [0, 10, 15, 20]" :key="pct">
                                <div :class="'tip' + ($store.bill.tipPercent === pct ? ' tip--selected' : '')"
                                     @click="$store.bill.setTipPercent(pct)"
                                     role="button" tabindex="0"
                                     :aria-pressed="($store.bill.tipPercent === pct).toString()"
                                     @keydown.enter="$store.bill.setTipPercent(pct)">
                                    <div class="pct" x-text="pct + '%'"></div>
                                    <div class="amt"
                                         x-text="pct === 0 ? '—' : Number(Math.round($store.bill.orderTotal * pct) / 100).toFixed(2).replace('.',',') + ' €'"></div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- ── pay: formulario Stripe ─── --}}
                    <div x-show="$store.bill.step === 'pay'">
                        <div class="stripe-mock" style="display:flex;flex-direction:column;gap:16px">
                            <div style="display:flex;align-items:center;gap:6px;font-family:var(--font-body);font-size:12px;font-weight:600;color:var(--fg-muted)">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                                </svg>
                                PAGO SEGURO · STRIPE
                            </div>
                            <div id="stripe-payment-element"
                                 style="padding:16px;border:1px solid var(--glass-border);border-radius:12px;background:var(--glass-bg-surface)"></div>
                            <template x-if="$store.bill.stripeError">
                                <p style="color:var(--color-red-400);font-size:13px" role="alert" x-text="$store.bill.stripeError"></p>
                            </template>
                        </div>
                    </div>

                    {{-- ── cardDone: PaymentSuccess card ─── --}}
                    <div x-show="$store.bill.step === 'cardDone'">
                        <div class="ps ps--card">
                            <div class="ps-medal ps-medal--check">
                                <div class="ps-medal__confetti" aria-hidden="true">
                                    <span style="--cx:-54px;--cy:-8px;--cd:0ms;background:var(--color-green-500);width:9px;height:9px"></span>
                                    <span style="--cx:50px;--cy:-14px;--cd:60ms;background:var(--price-gold);width:7px;height:7px"></span>
                                    <span style="--cx:-38px;--cy:34px;--cd:120ms;background:var(--brand-primary);width:6px;height:6px"></span>
                                    <span style="--cx:44px;--cy:30px;--cd:90ms;background:var(--color-green-400);width:8px;height:8px"></span>
                                    <span style="--cx:-8px;--cy:-46px;--cd:150ms;background:var(--price-gold);width:6px;height:6px"></span>
                                    <span style="--cx:14px;--cy:-42px;--cd:40ms;background:var(--brand-primary);width:7px;height:7px"></span>
                                    <span style="--cx:60px;--cy:8px;--cd:180ms;background:var(--color-green-500);width:5px;height:5px"></span>
                                    <span style="--cx:-60px;--cy:16px;--cd:30ms;background:var(--price-gold);width:6px;height:6px"></span>
                                </div>
                                <span class="ps-medal__ring" aria-hidden="true"></span>
                                <span class="ps-medal__disc" aria-hidden="true">
                                    <svg viewBox="0 0 52 52" class="ps-medal__svg" aria-hidden="true">
                                        <path class="ps-medal__tick" d="M14 27l8 8 16-18" fill="none"
                                              stroke="currentColor" stroke-width="5"
                                              stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </div>
                            <h3 class="ps__head">¡Pago confirmado!</h3>
                            <div class="ps__amount">
                                <span class="ps__amountLab">Total pagado</span>
                                <span class="ps__amountVal"
                                      x-text="Number($store.bill.grandTotal || $store.bill.orderTotal).toFixed(2).replace('.',',') + ' €'"></span>
                                <span class="ps__amountTip"
                                      x-show="($store.bill.tipAmount || 0) > 0"
                                      x-text="'incluye ' + Number($store.bill.tipAmount).toFixed(2).replace('.',',') + ' € de propina'">
                                </span>
                            </div>
                            <div class="ps__meta">
                                <span class="ps__chip">{{ $table->name }}</span>
                                <template x-if="$store.bill.paidOrderId">
                                    <span class="ps__chip" x-text="'Pedido #' + $store.bill.paidOrderId"></span>
                                </template>
                                <span class="ps__chip ps__chip--method">Tarjeta · Stripe</span>
                            </div>
                            <p class="ps__note">El ticket es tu comprobante. ¡Gracias por tu visita!</p>
                            <div class="ps__cta">
                                <template x-if="$store.bill.paidOrderId">
                                    <a :href="$store.bill.ticketDownloadBase + '/' + $store.bill.paidOrderId + '/download?hash=' + $store.bill.tableHash"
                                       target="_blank" rel="noopener noreferrer"
                                       class="ps__btn ps__btn--primary">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                                        </svg>
                                        Descargar ticket
                                    </a>
                                </template>
                                <button type="button" class="ps__btn ps__btn--ghost" @click="$store.bill.close()">Ver carta</button>
                            </div>
                        </div>
                    </div>

                    {{-- ── split: intro — elegir modo ─── --}}
                    <div x-show="$store.bill.step === 'split'">
                        <div class="split-flow">
                            <div class="split-intro">
                                <div class="split-intro__head">
                                    <div class="split-intro__title">¿Cómo dividís la cuenta?</div>
                                    <div class="split-intro__sub">Cualquiera del grupo puede empezar — los demás se sincronizan en tiempo real.</div>
                                </div>
                                <div class="split-intro__modes">
                                    {{-- Modo por ítems --}}
                                    <button type="button" class="split-mode split-mode--items"
                                            :disabled="$store.bill.splitEquitativeLocked"
                                            :style="$store.bill.splitEquitativeLocked ? 'opacity:0.5;cursor:not-allowed' : ''"
                                            @click="!$store.bill.splitEquitativeLocked && $store.bill.setSplitStage('items')">
                                        <div class="split-mode__art" aria-hidden="true">
                                            <span class="rect rect--a"></span>
                                            <span class="rect rect--b"></span>
                                            <span class="rect rect--c"></span>
                                            <span class="tag">M</span>
                                            <span class="tag tag--b">I</span>
                                        </div>
                                        <div class="split-mode__h">Por ítems</div>
                                        <div class="split-mode__p"
                                             x-text="$store.bill.splitEquitativeLocked
                                                 ? 'No disponible: ya hay pagos a partes iguales'
                                                 : 'Cada uno reclama lo que ha pedido. Útil cuando hay distinto consumo.'">
                                        </div>
                                    </button>
                                    {{-- Modo equitativo --}}
                                    <button type="button" class="split-mode split-mode--equit"
                                            @click="$store.bill.setSplitStage('equit')">
                                        <div class="split-mode__art" aria-hidden="true">
                                            <svg viewBox="0 0 60 60" width="60" height="60">
                                                <circle cx="30" cy="30" r="22" fill="none" stroke="currentColor" stroke-width="2"></circle>
                                                <path d="M30 30 L30 8 A22 22 0 0 1 49 41 Z" fill="currentColor" opacity="0.85"></path>
                                                <path d="M30 30 L49 41 A22 22 0 0 1 11 41 Z" fill="currentColor" opacity="0.45"></path>
                                                <path d="M30 30 L11 41 A22 22 0 0 1 30 8 Z" fill="currentColor" opacity="0.20"></path>
                                            </svg>
                                        </div>
                                        <div class="split-mode__h">A partes iguales</div>
                                        <div class="split-mode__p">Elige cuántos sois. Cada uno paga la misma parte.</div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── splitItems: reclamar ítems ─── --}}
                    <div x-show="$store.bill.step === 'splitItems'">
                        <div class="split-items">
                            <div class="split-items__head">
                                <button type="button" class="back" @click="$store.bill.setSplitStage('intro')">← Cambiar modo</button>
                            </div>

                            <div class="split-items__status">
                                <div class="split-items__progress">
                                    <div class="bar">
                                        <div class="bar__fill"
                                             :style="'width: ' + (($store.bill.splitItems.filter(i => i.claimed).length / Math.max(1, $store.bill.splitItems.length)) * 100) + '%'">
                                        </div>
                                    </div>
                                    <span class="ct"
                                          x-text="$store.bill.splitItems.filter(i => i.claimed).length + '/' + $store.bill.splitItems.length + ' reclamados'">
                                    </span>
                                </div>
                                <div class="split-items__legend">
                                    Marca los platos que quieres pagar tú. Los ya marcados por otros están bloqueados.
                                </div>
                            </div>

                            <div class="split-items__list">
                                <template x-for="item in $store.bill.splitItems" :key="item.id">
                                    <div class="split-line">
                                        <div class="split-line__photo" aria-hidden="true">🍽️</div>
                                        <div class="split-line__main">
                                            <div class="split-line__name" x-text="item.name"></div>
                                            <div class="split-line__price">
                                                <span x-text="Number(item.price).toFixed(2).replace('.',',') + ' €'"></span>
                                                <span>/ ud.</span>
                                            </div>
                                        </div>
                                        <div class="split-line__slots">
                                            <template x-for="idx in item.quantity" :key="idx">
                                                <button type="button"
                                                        :class="'slot' + ($store.bill.isItemSelected(item.id) && idx === 1 ? ' slot--you slot--just' : (item.claimed ? ' slot--other' : ''))"
                                                        @click="!item.claimed && $store.bill.toggleSplitItem(item.id, item.claimed)"
                                                        :disabled="item.claimed"
                                                        :aria-label="item.claimed ? 'Ya reclamado' : 'Reclamar'">
                                                    <span x-text="$store.bill.isItemSelected(item.id) && idx === 1 ? 'T' : (item.claimed ? '✓' : '+')"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="split-items__foot">
                                <div class="split-items__yourTotal">
                                    <span class="lab">Tu parte</span>
                                    <span class="val"
                                          x-text="Number($store.bill.splitItemsTotal || 0).toFixed(2).replace('.',',') + ' €'"></span>
                                </div>
                                <button type="button" class="btn-primary"
                                        :disabled="!$store.bill.splitSelected || $store.bill.splitSelected.length === 0"
                                        @click="$store.bill.paySelectedItems()">
                                    Pagar mi parte ·
                                    <span x-text="Number($store.bill.splitItemsTotal || 0).toFixed(2).replace('.',',') + ' €'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- ── splitEq: partes iguales con donut ─── --}}
                    <div x-show="$store.bill.step === 'splitEq'">
                        <div class="split-equit"
                             x-data="{
                                 parts: $store.bill.splitPeople || 2,
                                 mySlice: 0,
                                 get slice() { return $store.bill.orderTotal / this.parts; },
                                 get fmt() { return (n) => Number(n).toFixed(2).replace('.',',') + ' €'; },
                                 arc(i) {
                                     const R_OUT = 48, R_IN = 26, CX = 60, CY = 60;
                                     const a0 = (i / this.parts) * Math.PI * 2 - Math.PI / 2;
                                     const a1 = ((i + 1) / this.parts) * Math.PI * 2 - Math.PI / 2;
                                     const lg = (a1 - a0) > Math.PI ? 1 : 0;
                                     const x0o = CX + R_OUT * Math.cos(a0), y0o = CY + R_OUT * Math.sin(a0);
                                     const x1o = CX + R_OUT * Math.cos(a1), y1o = CY + R_OUT * Math.sin(a1);
                                     const x0i = CX + R_IN  * Math.cos(a1), y0i = CY + R_IN  * Math.sin(a1);
                                     const x1i = CX + R_IN  * Math.cos(a0), y1i = CY + R_IN  * Math.sin(a0);
                                     return 'M '+x0o+' '+y0o+' A '+R_OUT+' '+R_OUT+' 0 '+lg+' 1 '+x1o+' '+y1o+' L '+x0i+' '+y0i+' A '+R_IN+' '+R_IN+' 0 '+lg+' 0 '+x1i+' '+y1i+' Z';
                                 },
                                 labelPos(i) {
                                     const mid = ((i + 0.5) / this.parts) * Math.PI * 2 - Math.PI / 2;
                                     const r = 37;
                                     return { x: 60 + r * Math.cos(mid), y: 60 + r * Math.sin(mid) };
                                 },
                             }">
                            <div class="split-items__head">
                                <button type="button" class="back" @click="$store.bill.setSplitStage('intro')">← Cambiar modo</button>
                                <div class="split-items__diners">
                                    <span class="lab">Total a dividir</span>
                                    <span class="val" x-text="Number($store.bill.orderTotal).toFixed(2).replace('.',',') + ' €'"></span>
                                </div>
                            </div>

                            {{-- Donut SVG --}}
                            <div class="split-equit__pie">
                                <svg viewBox="0 0 120 120" width="220" height="220" aria-hidden="true">
                                    <template x-for="i in Array.from({length: parts}, (_, k) => k)" :key="i">
                                        <g :class="'slice' + (i === mySlice ? ' slice--mine' : '')"
                                           @click="mySlice = i"
                                           style="cursor:pointer">
                                            <path :d="arc(i)"
                                                  :fill="i === mySlice ? 'var(--brand-primary)' : 'var(--bg-chip)'"
                                                  stroke="var(--bg-base)" stroke-width="2"
                                                  :opacity="i === mySlice ? 1 : 0.55"/>
                                            <text :x="labelPos(i).x" :y="labelPos(i).y"
                                                  text-anchor="middle" dominant-baseline="middle"
                                                  font-size="10" font-weight="800"
                                                  :fill="i === mySlice ? '#fff' : 'var(--fg-secondary)'"
                                                  x-text="i === mySlice ? 'TÚ' : (i + 1)">
                                            </text>
                                        </g>
                                    </template>
                                    {{-- Centro --}}
                                    <circle cx="60" cy="60" r="24" fill="var(--bg-surface)"/>
                                    <text x="60" y="57" text-anchor="middle" font-size="6.5" font-weight="600" fill="var(--fg-muted)">TU PARTE</text>
                                    <text x="60" y="68" text-anchor="middle" font-size="13" font-weight="900" fill="var(--fg-primary)"
                                          font-family="var(--font-display)"
                                          x-text="Number(slice).toFixed(2).replace('.',',') + ' €'">
                                    </text>
                                </svg>
                            </div>

                            {{-- Stepper --}}
                            <div class="split-equit__stepper">
                                <span class="lab">¿Cuántos sois?</span>
                                <div class="step">
                                    <button type="button"
                                            @click="parts = Math.max(2, parts - 1); $store.bill.splitPeople = parts"
                                            :disabled="parts <= 2">−</button>
                                    <span class="step__num" x-text="parts"></span>
                                    <button type="button"
                                            @click="parts = Math.min($store.bill.splitMaxParts || 10, parts + 1); $store.bill.splitPeople = parts"
                                            :disabled="parts >= ($store.bill.splitMaxParts || 10)">+</button>
                                </div>
                                <span class="hint" x-text="'máx. ' + ($store.bill.splitMaxParts || 10)"></span>
                            </div>

                            <div class="split-equit__live">
                                <span>Tú pagas</span>
                                <div class="dots">
                                    <template x-for="i in Array.from({length: parts}, (_, k) => k)" :key="i">
                                        <span :class="'dot' + (i === mySlice ? ' dot--mine' : '')"></span>
                                    </template>
                                </div>
                            </div>

                            <button type="button" class="btn-primary"
                                    @click="$store.bill.splitPeople = parts; $store.bill.payEquitative()">
                                Pagar mi parte ·
                                <span x-text="Number(slice).toFixed(2).replace('.',',') + ' €'"></span>
                            </button>
                        </div>
                    </div>

                    {{-- ── splitTip: propina para split ─── --}}
                    <div x-show="$store.bill.step === 'splitTip'">
                        <div class="split-pay">
                            <button type="button" class="back" @click="$store.bill.backToMethod()">← Cambiar método</button>
                            <div class="split-pay__amt">
                                <span class="lab">Tu parte</span>
                                <span class="val"
                                      x-text="Number($store.bill.splitTipBase || 0).toFixed(2).replace('.',',') + ' €'"></span>
                            </div>
                            <div class="tip-row" role="group" aria-label="Porcentaje de propina">
                                <template x-for="pct in [0, 10, 15, 20]" :key="pct">
                                    <div :class="'tip' + ($store.bill.splitTipPercent === pct ? ' tip--selected' : '')"
                                         @click="$store.bill.setSplitTipPercent(pct)"
                                         role="button" tabindex="0"
                                         :aria-pressed="($store.bill.splitTipPercent === pct).toString()"
                                         @keydown.enter="$store.bill.setSplitTipPercent(pct)">
                                        <div class="pct" x-text="pct + '%'"></div>
                                        <div class="amt"
                                             x-text="pct === 0 ? '—' : Number(Math.round($store.bill.splitTipBase * pct) / 100).toFixed(2).replace('.',',') + ' €'"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- ── splitPay: Stripe para cobro partido ─── --}}
                    <div x-show="$store.bill.step === 'splitPay'">
                        <div class="stripe-mock" style="display:flex;flex-direction:column;gap:16px">
                            <div style="display:flex;align-items:center;gap:6px;font-family:var(--font-body);font-size:12px;font-weight:600;color:var(--fg-muted)">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                                </svg>
                                PAGO SEGURO · STRIPE
                            </div>
                            <div id="split-stripe-element"
                                 style="padding:16px;border:1px solid var(--glass-border);border-radius:12px;background:var(--glass-bg-surface)"></div>
                            <template x-if="$store.bill.splitStripeError">
                                <p style="color:var(--color-red-400);font-size:13px" role="alert" x-text="$store.bill.splitStripeError"></p>
                            </template>
                        </div>
                    </div>

                    {{-- ── splitDone: parte pagada ─── --}}
                    <div x-show="$store.bill.step === 'splitDone'">
                        <div class="ps ps--card">
                            <div class="ps-medal ps-medal--check">
                                <div class="ps-medal__confetti" aria-hidden="true">
                                    <span style="--cx:-54px;--cy:-8px;--cd:0ms;background:var(--color-green-500);width:9px;height:9px"></span>
                                    <span style="--cx:50px;--cy:-14px;--cd:60ms;background:var(--price-gold);width:7px;height:7px"></span>
                                    <span style="--cx:-38px;--cy:34px;--cd:120ms;background:var(--brand-primary);width:6px;height:6px"></span>
                                    <span style="--cx:44px;--cy:30px;--cd:90ms;background:var(--color-green-400);width:8px;height:8px"></span>
                                </div>
                                <span class="ps-medal__ring" aria-hidden="true"></span>
                                <span class="ps-medal__disc" aria-hidden="true">
                                    <svg viewBox="0 0 52 52" class="ps-medal__svg" aria-hidden="true">
                                        <path class="ps-medal__tick" d="M14 27l8 8 16-18" fill="none"
                                              stroke="currentColor" stroke-width="5"
                                              stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </div>
                            <h3 class="ps__head">¡Parte pagada!</h3>
                            <div class="ps__amount">
                                <span class="ps__amountLab">Tu parte</span>
                                <span class="ps__amountVal"
                                      x-text="Number($store.bill.splitStripeTotal || 0).toFixed(2).replace('.',',') + ' €'"></span>
                                <span class="ps__amountTip"
                                      x-show="($store.bill.splitStripeTip || 0) > 0"
                                      x-text="'incluye ' + Number($store.bill.splitStripeTip || 0).toFixed(2).replace('.',',') + ' € de propina'">
                                </span>
                            </div>
                            <div class="ps__meta">
                                <span class="ps__chip">{{ $table->name }}</span>
                                <span class="ps__chip ps__chip--method">Tarjeta · Stripe</span>
                            </div>
                            <p class="ps__note">Tu parte de la cuenta está pagada. ¡Gracias!</p>
                            <div class="ps__cta">
                                <button type="button" class="ps__btn ps__btn--ghost" @click="$store.bill.close()">Ver carta</button>
                            </div>
                        </div>
                    </div>

                    {{-- ── mixed: fader efectivo/tarjeta ─── --}}
                    <div x-show="$store.bill.step === 'mixed'"
                         x-data="{
                             get card() { return Math.max(0, $store.bill.orderTotal - ($store.bill.mixedCashAmount || 0)); },
                             get cashRatio() { return Math.max(0, Math.min(1, ($store.bill.mixedCashAmount || 0) / $store.bill.orderTotal)); },
                             get cashAllOnly() { return ($store.bill.mixedCashAmount || 0) >= $store.bill.orderTotal - 0.001; },
                             get cardOnly() { return ($store.bill.mixedCashAmount || 0) <= 0.001; },
                             get cardBelowMin() { return !this.cashAllOnly && this.card > 0 && this.card < 0.50; },
                             get canContinue() { return !this.cardBelowMin; },
                             fmt(n) { return Number(n).toFixed(2).replace('.',',') + ' €'; },
                             presets: [],
                             initPresets() {
                                 const t = $store.bill.orderTotal;
                                 this.presets = [
                                     { label: '10 €',       value: 10 },
                                     { label: '20 €',       value: 20 },
                                     { label: '50 €',       value: Math.min(50, t) },
                                     { label: 'Mitad',      value: Math.round(t / 2 * 2) / 2 },
                                     { label: 'Solo efect.', value: t },
                                 ];
                             },
                         }"
                         x-init="initPresets()">
                        <div class="mixed-flow">
                            <div class="mixed-stage">
                                <div class="mixed-stage__head">
                                    <div class="mixed-stage__title">¿Cuánto en efectivo?</div>
                                    <div class="mixed-stage__sub">
                                        El resto lo cobramos con tarjeta. Total a pagar:
                                        <strong x-text="Number($store.bill.orderTotal).toFixed(2).replace('.',',') + ' €'"></strong>
                                    </div>
                                </div>

                                {{-- Fader visual --}}
                                <div class="fader">
                                    <div class="fader__bar">
                                        <div class="fader__cash"
                                             :style="'width: ' + (cashRatio * 100) + '%'">
                                            <span class="fader__lab">EFECTIVO</span>
                                            <span class="fader__amt"
                                                  x-text="fmt($store.bill.mixedCashAmount || 0)"></span>
                                        </div>
                                        <div class="fader__card"
                                             :style="'width: ' + ((1 - cashRatio) * 100) + '%'">
                                            <span class="fader__lab">TARJETA</span>
                                            <span class="fader__amt" x-text="fmt(card)"></span>
                                        </div>
                                    </div>
                                    <input type="range"
                                           min="0"
                                           :max="$store.bill.orderTotal"
                                           step="0.50"
                                           :value="$store.bill.mixedCashAmount || 0"
                                           @input="$store.bill.mixedCashAmount = Number($event.target.value)"
                                           class="fader__range"
                                           aria-label="Cantidad en efectivo">
                                </div>

                                {{-- Chips de presets --}}
                                <div class="mixed-chips">
                                    <template x-for="p in presets" :key="p.label">
                                        <button type="button"
                                                :class="'mixed-chip' + (Math.abs(p.value - ($store.bill.mixedCashAmount || 0)) < 0.01 ? ' mixed-chip--active' : '')"
                                                @click="$store.bill.mixedCashAmount = Math.min($store.bill.orderTotal, p.value)">
                                            <span x-text="p.label"></span>
                                        </button>
                                    </template>
                                </div>

                                {{-- Ajuste fino --}}
                                <div class="mixed-precise">
                                    <span class="lab">Ajuste fino</span>
                                    <button type="button" class="mixed-precise__b"
                                            @click="$store.bill.mixedCashAmount = Math.max(0, Math.round((($store.bill.mixedCashAmount || 0) - 0.5) * 100) / 100)">
                                        −0,50
                                    </button>
                                    <span class="mixed-precise__num"
                                          x-text="Number($store.bill.mixedCashAmount || 0).toFixed(2).replace('.',',') + ' €'"></span>
                                    <button type="button" class="mixed-precise__b"
                                            @click="$store.bill.mixedCashAmount = Math.min($store.bill.orderTotal, Math.round((($store.bill.mixedCashAmount || 0) + 0.5) * 100) / 100)">
                                        +0,50
                                    </button>
                                </div>

                                {{-- Avisos --}}
                                <div class="mixed-warn" x-show="cardBelowMin">
                                    <span class="mixed-warn__ic">!</span>
                                    <div>
                                        <strong>La parte de tarjeta debe ser al menos 0,50 €.</strong>
                                        <span x-text="'Sube el efectivo a ' + fmt($store.bill.orderTotal) + ' (100% efectivo) o bájalo a ' + fmt($store.bill.orderTotal - 0.50) + '.'"></span>
                                    </div>
                                </div>
                                <div class="mixed-note" x-show="cashAllOnly">
                                    ✓ Pagarás el total en efectivo. Continuando te llevamos al cobro en efectivo.
                                </div>
                                <div class="mixed-note" x-show="cardOnly && !cashAllOnly">
                                    ✓ Pagarás el total con tarjeta.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── mixedTip: propina sobre parte tarjeta ─── --}}
                    <div x-show="$store.bill.step === 'mixedTip'"
                         x-data="{
                             get card() { return Math.max(0, $store.bill.orderTotal - ($store.bill.mixedCashAmount || 0)); },
                             fmt(n) { return Number(n).toFixed(2).replace('.',',') + ' €'; },
                         }">
                        <div class="mixed-flow">
                            <div class="mixed-stage">
                                <div class="mixed-stage__head">
                                    <div class="mixed-stage__title">Añadir propina</div>
                                    <div class="mixed-stage__sub">Va incluida en la parte de tarjeta, nunca en el efectivo.</div>
                                </div>

                                {{-- Resumen del reparto --}}
                                <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
                                    <span class="mixed-tile mixed-tile--cash"
                                          x-text="'Efectivo ' + fmt($store.bill.mixedCashAmount || 0)"></span>
                                    <span class="mixed-tile mixed-tile--card"
                                          x-text="'Tarjeta base ' + fmt(card)"></span>
                                </div>

                                {{-- Chips de propina --}}
                                <div class="mixed-tipChips">
                                    <button type="button"
                                            :class="'mixed-tipChip' + ($store.bill.mixedTipPercent === 0 ? ' mixed-tipChip--active' : '')"
                                            :aria-pressed="$store.bill.mixedTipPercent === 0"
                                            @click="$store.bill.setMixedTipPercent(0)">
                                        <span class="pct">Sin</span>
                                        <span class="amt">propina</span>
                                    </button>
                                    <template x-for="p in [5, 10, 15, 20]" :key="p">
                                        <button type="button"
                                                :class="'mixed-tipChip' + ($store.bill.mixedTipPercent === p ? ' mixed-tipChip--active' : '')"
                                                :aria-pressed="$store.bill.mixedTipPercent === p"
                                                @click="$store.bill.setMixedTipPercent(p)">
                                            <span class="pct" x-text="p + '%'"></span>
                                            <span class="amt"
                                                  x-text="fmt(Math.round(card * p) / 100)"></span>
                                        </button>
                                    </template>
                                </div>

                                {{-- Importe libre --}}
                                <label for="mixed-tip-free" class="sr-only">Propina personalizada en euros</label>
                                <div class="mixed-tipInput">
                                    <input id="mixed-tip-free"
                                           type="number" min="0" max="500" step="0.50" inputmode="decimal"
                                           placeholder="O introduce un importe…"
                                           :value="$store.bill.mixedTipPercent === null && $store.bill.mixedTipAmount > 0 ? $store.bill.mixedTipAmount : ''"
                                           @input="$store.bill.updateCustomMixedTip ? $store.bill.updateCustomMixedTip($event.target.value) : null">
                                    <span class="mixed-tipInput__cur" aria-hidden="true">€</span>
                                </div>

                                {{-- Resumen --}}
                                <div class="mixed-tipSummary" aria-live="polite">
                                    <div class="mixed-tipSummary__row">
                                        <span>Parte de tarjeta</span>
                                        <span class="v" x-text="fmt(card)"></span>
                                    </div>
                                    <div class="mixed-tipSummary__row mixed-tipSummary__row--tip"
                                         x-show="($store.bill.mixedTipAmount || 0) > 0">
                                        <span>Propina</span>
                                        <span class="v"
                                              x-text="'+ ' + fmt($store.bill.mixedTipAmount || 0)"></span>
                                    </div>
                                    <div class="mixed-tipSummary__row mixed-tipSummary__row--total">
                                        <span>Total con tarjeta</span>
                                        <span class="v"
                                              x-text="fmt($store.bill.mixedTipGrandTotal || card)"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── mixedPay: Stripe parte tarjeta ─── --}}
                    <div x-show="$store.bill.step === 'mixedPay'"
                         x-data="{
                             get card() { return Math.max(0, $store.bill.orderTotal - ($store.bill.mixedCashAmount || 0)); },
                             fmt(n) { return Number(n).toFixed(2).replace('.',',') + ' €'; },
                         }">
                        <div class="mixed-flow">
                            <div class="mixed-stage">
                                <div class="mixed-stage__head">
                                    <div class="mixed-stage__title">Pago de la parte tarjeta</div>
                                    <div class="mixed-stage__sub" style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
                                        <span class="mixed-tile mixed-tile--cash"
                                              x-text="'Efectivo ' + fmt($store.bill.mixedCashAmount || 0)"></span>
                                        <span class="mixed-tile mixed-tile--card"
                                              x-text="'Tarjeta ' + fmt($store.bill.mixedTipGrandTotal || card)"></span>
                                    </div>
                                </div>
                                <div id="mixed-stripe-element"
                                     style="padding:16px;border:1px solid var(--glass-border);border-radius:12px;background:var(--glass-bg-surface)"></div>
                                <template x-if="$store.bill.mixedStripeError">
                                    <p style="color:var(--color-red-400);font-size:13px;margin-top:4px"
                                       role="alert" x-text="$store.bill.mixedStripeError"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- ── mixedWaiter: esperando al camarero ─── --}}
                    <div x-show="$store.bill.step === 'mixedWaiter'"
                         x-data="{
                             get card() { return Math.max(0, $store.bill.orderTotal - ($store.bill.mixedCashAmount || 0)); },
                             get grandTotal() { return $store.bill.orderTotal + ($store.bill.mixedTipAmount || 0); },
                             get cardCharge() { return $store.bill.mixedTipGrandTotal || this.card; },
                             fmt(n) { return Number(n).toFixed(2).replace('.',',') + ' €'; },
                         }">
                        <div class="mixed-flow">
                            <div class="mixed-stage mixed-stage--waiting">
                                <div class="mixed-waiting">
                                    <div class="mixed-waiting__ic">
                                        <div class="mixed-waiting__pulse"></div>
                                        <span>🧑‍🍳</span>
                                    </div>
                                    <div class="mixed-stage__title">Esperando al camarero</div>
                                    <div class="mixed-stage__sub">
                                        Tu pago con tarjeta de
                                        <strong x-text="fmt(cardCharge)"></strong>
                                        está confirmado.<br>
                                        El camarero recogerá
                                        <strong x-text="fmt($store.bill.mixedCashAmount || 0)"></strong>
                                        en efectivo para cerrar la cuenta.
                                    </div>
                                    <div class="mixed-receipt">
                                        <div class="mixed-receipt__row">
                                            <span>Total</span>
                                            <span class="b" x-text="fmt(grandTotal)"></span>
                                        </div>
                                        <div class="mixed-receipt__row mixed-receipt__row--ok">
                                            <span>· Tarjeta (confirmado)</span>
                                            <span class="b" x-text="fmt(cardCharge)"></span>
                                        </div>
                                        <div class="mixed-receipt__row mixed-receipt__row--pending">
                                            <span>· Efectivo (pendiente)</span>
                                            <span class="b" x-text="fmt($store.bill.mixedCashAmount || 0)"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── mixedDone: cuenta cerrada ─── --}}
                    <div x-show="$store.bill.step === 'mixedDone'"
                         x-data="{
                             get card() { return Math.max(0, $store.bill.orderTotal - ($store.bill.mixedCashAmount || 0)); },
                             get cardCharge() { return $store.bill.mixedTipGrandTotal || this.card; },
                             fmt(n) { return Number(n).toFixed(2).replace('.',',') + ' €'; },
                         }">
                        <div class="mixed-flow">
                            <div class="mixed-stage">
                                <div class="ps ps--card" style="padding-top:8px">
                                    <div class="ps-medal ps-medal--check">
                                        <div class="ps-medal__confetti" aria-hidden="true">
                                            <span style="--cx:-54px;--cy:-8px;--cd:0ms;background:var(--color-green-500);width:9px;height:9px"></span>
                                            <span style="--cx:50px;--cy:-14px;--cd:60ms;background:var(--price-gold);width:7px;height:7px"></span>
                                            <span style="--cx:-38px;--cy:34px;--cd:120ms;background:var(--brand-primary);width:6px;height:6px"></span>
                                            <span style="--cx:44px;--cy:30px;--cd:90ms;background:var(--color-green-400);width:8px;height:8px"></span>
                                        </div>
                                        <span class="ps-medal__ring" aria-hidden="true"></span>
                                        <span class="ps-medal__disc" aria-hidden="true">
                                            <svg viewBox="0 0 52 52" class="ps-medal__svg" aria-hidden="true">
                                                <path class="ps-medal__tick" d="M14 27l8 8 16-18" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </div>
                                    <h3 class="ps__head">Cuenta cerrada · ¡Gracias!</h3>
                                    <div class="ps__amount">
                                        <span class="ps__amountLab">Total pagado</span>
                                        <span class="ps__amountVal"
                                              x-text="fmt($store.bill.orderTotal + ($store.bill.mixedTipAmount || 0))"></span>
                                    </div>
                                    <div class="ps__meta">
                                        <span class="ps__chip">{{ $table->name }}</span>
                                        <span class="ps__chip ps__chip--method">Pago mixto</span>
                                    </div>
                                    <p class="ps__note"
                                       x-text="'Tarjeta ' + fmt(cardCharge) + ' · Efectivo ' + fmt($store.bill.mixedCashAmount || 0)">
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>{{-- /drawer__body --}}

                {{-- ── PIE: botones por step ───────────────────────────────────── --}}
                <div class="drawer__foot">

                    {{-- method --}}
                    <div x-show="$store.bill.step === 'method'">
                        <button type="button" class="btn-text" @click="$store.bill.close()">Cancelar</button>
                    </div>

                    {{-- cashConfirm --}}
                    <div x-show="$store.bill.step === 'cashConfirm'">
                        <div class="bill__footRow bill__footRow--stack">
                            <button type="button" class="btn-primary cashTip__cta"
                                    :disabled="$store.bill.sending"
                                    @click="$store.bill.confirmCashPayment()">
                                <span x-show="$store.bill.sending">
                                    <span class="cashTip__spin" aria-hidden="true"></span>
                                    Enviando solicitud…
                                </span>
                                <span x-show="!$store.bill.sending"
                                      x-text="($store.bill.cashTipAmount || 0) > 0
                                          ? 'Solicitar cuenta · ' + Number($store.bill.cashGrandTotal).toFixed(2).replace('.',',') + ' € (con propina)'
                                          : 'Solicitar cuenta · ' + Number($store.bill.orderTotal).toFixed(2).replace('.',',') + ' € (sin propina)'">
                                </span>
                            </button>
                            <button type="button" class="btn-secondary" @click="$store.bill.backToMethod()">← Cambiar método</button>
                        </div>
                    </div>

                    {{-- cashDone --}}
                    <div x-show="$store.bill.step === 'cashDone'">
                        <div class="bill__footRow">
                            <button type="button" class="btn-text bill__cancelNotice" @click="$store.bill.backToMethod()">Cancelar aviso</button>
                        </div>
                    </div>

                    {{-- tip --}}
                    <div x-show="$store.bill.step === 'tip'">
                        <div class="bill__footRow bill__footRow--stack">
                            <button type="button" class="btn-primary"
                                    @click="$store.bill.proceedToStripe()">
                                Continuar —
                                <span x-text="Number($store.bill.orderTotal + ($store.bill.tipAmount || 0)).toFixed(2).replace('.',',') + ' €'"></span>
                            </button>
                            <button type="button" class="btn-secondary" @click="$store.bill.backToMethod()">← Cambiar método</button>
                        </div>
                    </div>

                    {{-- pay --}}
                    <div x-show="$store.bill.step === 'pay'">
                        <div class="bill__footRow bill__footRow--stack">
                            <button type="button" class="btn-primary"
                                    @click="$store.bill.submitCardPayment ? $store.bill.submitCardPayment() : $store.bill.payWithStripe()"
                                    :disabled="$store.bill.sending || !$store.bill.stripeReady">
                                <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                                </svg>
                                <span x-text="$store.bill.sending ? 'Procesando…' : 'Pagar ' + Number($store.bill.grandTotal || $store.bill.orderTotal).toFixed(2).replace('.',',') + ' €'"></span>
                            </button>
                            <button type="button" class="btn-secondary" @click="$store.bill.backToMethod()">← Cambiar método</button>
                        </div>
                    </div>

                    {{-- cardDone: sin botones adicionales en el footer --}}

                    {{-- split --}}
                    <div x-show="$store.bill.step === 'split'">
                        <button type="button" class="btn-text" @click="$store.bill.closeSplitSelector()">Cancelar</button>
                    </div>

                    {{-- splitItems --}}
                    <div x-show="$store.bill.step === 'splitItems'">
                        <div style="display:flex;justify-content:space-between;margin-bottom:10px;font-family:var(--font-body);font-size:13px">
                            <span style="color:var(--fg-muted)">Mi parte</span>
                            <span style="font-weight:700;color:var(--price-gold)"
                                  x-text="Number($store.bill.splitItemsTotal || 0).toFixed(2).replace('.',',') + ' €'"></span>
                        </div>
                        <button type="button" class="btn-primary"
                                :disabled="!$store.bill.splitSelected || $store.bill.splitSelected.length === 0"
                                @click="$store.bill.paySelectedItems()">
                            Pagar mi parte
                        </button>
                        <button type="button" class="btn-text" @click="$store.bill.closeSplitItems()">Atrás</button>
                    </div>

                    {{-- splitEq --}}
                    <div x-show="$store.bill.step === 'splitEq'">
                        <button type="button" class="btn-primary" @click="$store.bill.payEquitative()">
                            Pagar mi parte con tarjeta
                        </button>
                        <button type="button" class="btn-text" @click="$store.bill.closeSplitEq()">Atrás</button>
                    </div>

                    {{-- splitTip --}}
                    <div x-show="$store.bill.step === 'splitTip'">
                        <div class="bill__footRow bill__footRow--stack">
                            <button type="button" class="btn-primary"
                                    @click="$store.bill.confirmSplitTip()">
                                Continuar —
                                <span x-text="Number($store.bill.splitTipGrandTotal || $store.bill.splitTipBase).toFixed(2).replace('.',',') + ' €'"></span>
                            </button>
                            <button type="button" class="btn-secondary" @click="$store.bill.backToMethod()">← Cambiar método</button>
                        </div>
                    </div>

                    {{-- mixed --}}
                    <div x-show="$store.bill.step === 'mixed'">
                        <button type="button" class="btn-primary"
                                :disabled="!$store.bill.mixedCashValid && !($store.bill.mixedCashAmount >= $store.bill.orderTotal - 0.001)"
                                @click="$store.bill.proceedFromMixed()">
                            <span x-text="($store.bill.mixedCashAmount >= $store.bill.orderTotal - 0.001) ? 'Continuar · Pago en efectivo' : 'Continuar con tarjeta'"></span>
                        </button>
                        <button type="button" class="btn-text" @click="$store.bill.closeMixed()">Cancelar</button>
                    </div>

                    {{-- mixedTip --}}
                    <div x-show="$store.bill.step === 'mixedTip'">
                        <div class="bill__footRow bill__footRow--stack">
                            <button type="button" class="btn-primary"
                                    @click="$store.bill.confirmMixedTip()">
                                Continuar —
                                <span x-text="Number($store.bill.mixedTipGrandTotal || $store.bill.mixedCardAmount).toFixed(2).replace('.',',') + ' €'"></span>
                            </button>
                            <button type="button" class="btn-secondary" @click="$store.bill.closeMixedTip()">← Atrás</button>
                        </div>
                    </div>

                    {{-- splitPay --}}
                    <div x-show="$store.bill.step === 'splitPay'">
                        <div class="bill__footRow bill__footRow--stack">
                            <button type="button" class="btn-primary"
                                    @click="$store.bill.submitSplitPayment()"
                                    :disabled="$store.bill.sending || !$store.bill.splitStripeReady">
                                <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                                </svg>
                                <span x-text="$store.bill.sending ? 'Procesando…' : 'Pagar ' + Number($store.bill.splitStripeTotal || 0).toFixed(2).replace('.',',') + ' €'"></span>
                            </button>
                            <button type="button" class="btn-secondary" @click="$store.bill.closeSplitPayment()">← Atrás</button>
                        </div>
                    </div>

                    {{-- mixedPay --}}
                    <div x-show="$store.bill.step === 'mixedPay'">
                        <div class="bill__footRow bill__footRow--stack">
                            <button type="button" class="btn-primary"
                                    @click="$store.bill.payMixedCard()"
                                    :disabled="$store.bill.sending || !$store.bill.mixedStripeReady">
                                <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                                </svg>
                                <span x-text="$store.bill.sending ? 'Procesando…' : 'Pagar ' + Number($store.bill.mixedTipGrandTotal || $store.bill.mixedCardAmount).toFixed(2).replace('.',',') + ' €'"></span>
                            </button>
                            <button type="button" class="btn-secondary" @click="$store.bill.closeMixedPayment()">← Atrás</button>
                        </div>
                    </div>

                </div>{{-- /drawer__foot --}}
            </div>{{-- /drawer--bill --}}
        </div>{{-- /scrim --}}


                {{-- ======================================================================
             TAPA MODAL — DS: TapasModalRich v2
             ====================================================================== --}}
        <div class="modal"
             x-show="$store.cart.showTapaModal"
             x-transition:enter="transition duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display:none"
             @click.self="$store.cart.closeTapaModal()"
             role="dialog" aria-modal="true" aria-label="Tapas disponibles">

            <div class="modal__panel tapas-modal"
                 x-data="{
                     selectedTapaId: null,
                     showChefExtra: false,
                     get left()         { return Math.max(0, $store.cart._barItemsCount - $store.cart._variantsUsed); },
                     get limitReached() { return $store.cart._variantsUsed >= $store.cart.tapaConfig.maxVariants && $store.cart.tapaConfig.maxVariants > 0; },
                     get tokensShown()  { return Math.min(Math.max($store.cart._barItemsCount, 1), 12); },
                     get tokensOver()   { return Math.max(0, $store.cart._barItemsCount - 12); },
                 }"
                 x-effect="if (!$store.cart.showTapaModal) { selectedTapaId = null; showChefExtra = false; }"
                 @click.stop>

                {{-- Header --}}
                <header class="tapas-head tapas-head--v2">
                    <button type="button" class="tapas-close"
                            @click="$store.cart.closeTapaModal()" aria-label="Cerrar">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <div class="tapas-head__pre">
                        <span class="tapas-head__ornament" aria-hidden="true">— ❦ —</span>
                        <span class="tapas-head__kicker">LA CASA · TAPAS</span>
                    </div>
                    <h2 class="tapas-head__big"
                        x-text="limitReached ? 'Has llegado al límite' : (left > 0 ? 'Lleva una tapa' : 'Sin tapas disponibles')">
                    </h2>
                    <p class="tapas-head__lead">
                        <template x-if="limitReached">
                            <span x-text="'Has elegido ' + $store.cart.tapaConfig.maxVariants + ' tapas distintas — máximo por pedido.'"></span>
                        </template>
                        <template x-if="!limitReached && left > 0">
                            <span>Tienes <strong x-text="left"></strong>&nbsp;<span x-text="left === 1 ? 'tapa de cortesía' : 'tapas de cortesía'"></span> por canjear.</span>
                        </template>
                        <template x-if="!limitReached && left === 0">
                            <span>Pide una bebida de barra y desbloquearás una tapa.</span>
                        </template>
                    </p>
                    <div class="tapas-coins" aria-hidden="true">
                        <template x-for="i in Array.from({length: tokensShown}, (_, idx) => idx)" :key="i">
                            <span :class="'tapa-coin' + (i < $store.cart._variantsUsed ? ' tapa-coin--used' : '')">
                                <span class="tapa-coin__face">🥟</span>
                            </span>
                        </template>
                        <span class="tapa-coin tapa-coin--more"
                              x-show="tokensOver > 0"
                              x-text="'+' + tokensOver"></span>
                    </div>
                    <div class="tapas-rule">
                        <span class="tapas-rule__line"></span>
                        <span class="tapas-rule__text">1 tapa por bebida de barra</span>
                        <span class="tapas-rule__line"></span>
                    </div>
                    <template x-if="!limitReached && $store.cart._variantsUsed > 0 && $store.cart.tapaConfig.maxVariants > 0">
                        <div class="tapas-variants tapas-variants--v2">
                            <span>Variantes usadas</span>
                            <span class="tapas-variants__pips">
                                <template x-for="i in Array.from({length: $store.cart.tapaConfig.maxVariants}, (_, idx) => idx)" :key="i">
                                    <span :class="'pip' + (i < $store.cart._variantsUsed ? ' pip--on' : '')"></span>
                                </template>
                            </span>
                            <span class="tapas-variants__num"
                                  x-text="$store.cart._variantsUsed + '/' + $store.cart.tapaConfig.maxVariants"></span>
                        </div>
                    </template>
                </header>

                {{-- Body --}}
                <div class="tapas-body">
                    <div class="tapas-listLabel">Elige tu tapa</div>
                    <div class="tapas-list tapas-list--v2"
                         :data-disabled="(limitReached || left === 0).toString()">
                        <template x-for="(tapa, idx) in $store.cart.tapaProducts" :key="tapa.id">
                            <button type="button"
                                    :class="'tapa-card tapa-card--v2' + (selectedTapaId === tapa.id ? ' tapa-card--selected' : '')"
                                    :disabled="limitReached || left === 0"
                                    :style="'--i:' + idx"
                                    @click="selectedTapaId = selectedTapaId === tapa.id ? null : tapa.id"
                                    :aria-pressed="(selectedTapaId === tapa.id).toString()">
                                <div class="tapa-card__plate" aria-hidden="true">
                                    <span class="tapa-card__plateInner" x-text="tapa.emoji || '🫕'"></span>
                                </div>
                                <div class="tapa-card__main">
                                    <div class="tapa-card__name" x-text="tapa.name"></div>
                                    <div class="tapa-card__hint" x-text="tapa.description || 'Sugerencia de la casa'"></div>
                                </div>
                                <div class="tapa-card__meta">
                                    <span :class="'tapa-card__pill ' + (tapa.price === 0 ? 'tapa-card__pill--free' : 'tapa-card__pill--paid')"
                                          x-text="tapa.price === 0 ? 'Cortesía' : '+ ' + Number(tapa.price).toFixed(2).replace('.',',') + ' €'"></span>
                                    <span :class="'tapa-card__radio' + (selectedTapaId === tapa.id ? ' is-on' : '')"
                                          aria-hidden="true"
                                          x-text="selectedTapaId === tapa.id ? '&#10003;' : ''"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Footer --}}
                <footer class="tapas-foot tapas-foot--v2">
                    <button type="button" class="tapas-foot__secondary"
                            @click="$store.cart.closeTapaModal()">
                        Ahora no
                    </button>
                    <button type="button" class="tapas-foot__primary"
                            :disabled="!selectedTapaId || limitReached || left === 0"
                            @click="$store.cart.addTapa($store.cart.tapaProducts.find(t => t.id === selectedTapaId)); selectedTapaId = null">
                        <span x-text="limitReached ? 'Límite alcanzado' : (selectedTapaId ? 'Añadir tapa' : 'Elige una tapa')"></span>
                        <span class="tapas-foot__arrow" x-show="selectedTapaId && !limitReached" aria-hidden="true">→</span>
                    </button>
                </footer>
            </div>
        </div>
        {{-- ══════════════════════════════════════════════════════════════════════
             PRODUCT DETAIL MODAL — DS: .scrim.scrim--center > .pdetail
             ══════════════════════════════════════════════════════════════════════ --}}
        <div class="scrim scrim--center"
             x-show="selectedProduct !== null"
             :class="{ 'is-closing': pdetailClosing }"
             @click="closeProduct()"
             @keydown.escape.window="closeProduct()"
             style="display:none"
             role="dialog"
             aria-modal="true"
             :aria-label="selectedProduct ? 'Detalle de ' + selectedProduct.name : ''">

            <div class="pdetail"
                 :class="{ 'is-closing': pdetailClosing }"
                 @click.stop>

                {{-- Botón cerrar --}}
                <button class="pdetail__close" @click="closeProduct()" aria-label="Cerrar">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <div class="pdetail__layout">

                    {{-- Foto / Hero --}}
                    <div class="pdetail__photo"
                         :class="selectedProduct?.imageUrl ? '' : 'pdetail__photo--food-' + (selectedProduct?.photoStyle || 1)">
                        <template x-if="selectedProduct?.imageUrl">
                            <img :src="selectedProduct.imageUrl"
                                 :alt="'Foto de ' + selectedProduct.name"
                                 style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0">
                        </template>
                        <template x-if="!selectedProduct?.imageUrl">
                            <div class="pdetail__photo-emoji" aria-hidden="true"
                                 x-text="selectedProduct?.emoji || '🍽️'"></div>
                        </template>
                        <div class="pdetail__photo-fade" aria-hidden="true"></div>
                        <div class="pdetail__chip-row">
                            <span class="pdetail__catchip" x-text="selectedProduct?.categoryName || ''"></span>
                            <template x-if="selectedProduct?.destination === 'kitchen' && !kitchenOpen">
                                <span class="pdetail__catchip pdetail__catchip--warn">Cocina cerrada</span>
                            </template>
                        </div>
                        <div class="pdetail__grabber" aria-hidden="true"></div>
                    </div>

                    {{-- Contenido --}}
                    <div class="pdetail__content">
                        <div class="pdetail__scroll">

                            <h2 class="pdetail__name" x-text="selectedProduct?.name"></h2>
                            <div class="pdetail__price"
                                 x-text="Number(selectedProduct?.price || 0).toFixed(2).replace('.', ',') + ' €'"></div>
                            <p class="pdetail__desc"
                               x-text="selectedProduct?.description"
                               x-show="selectedProduct?.description"></p>

                            {{-- Alérgenos --}}
                            <section class="pdetail__section"
                                     x-show="(selectedProduct?.allergenTypes || []).length > 0">
                                <div class="pdetail__label">Contiene</div>
                                <div class="pdetail__allergens">
                                    <template x-for="al in (selectedProduct?.allergenTypes || [])" :key="al">
                                        <span class="pdetail__allergen">
                                            <svg class="allergen-img"
                                                 :data-al="al"
                                                 viewBox="0 0 100 100"
                                                 width="22" height="22"
                                                 role="img"
                                                 :aria-label="'Contiene ' + (window.ZAMPA_ALLERGEN_LABELS?.[al] || al)">
                                            </svg>
                                            <span x-text="window.ZAMPA_ALLERGEN_LABELS?.[al] || al"></span>
                                        </span>
                                    </template>
                                </div>
                            </section>

                            {{-- Quitar ingredientes --}}
                            <section class="pdetail__section"
                                     x-show="(selectedProduct?.removable || []).length > 0">
                                <div class="pdetail__label">Personaliza · quita lo que no quieras</div>
                                <div class="pdetail__chips">
                                    <template x-for="ing in (selectedProduct?.removable || [])" :key="ing.id">
                                        <button type="button"
                                                :class="pdetailRemovedIds.includes(ing.id)
                                                    ? 'pdetail__chip pdetail__chip--off'
                                                    : 'pdetail__chip'"
                                                @click="pdetailRemovedIds.includes(ing.id)
                                                    ? pdetailRemovedIds.splice(pdetailRemovedIds.indexOf(ing.id), 1)
                                                    : pdetailRemovedIds.push(ing.id)">
                                            <span class="pdetail__chip-x" aria-hidden="true"
                                                  x-text="pdetailRemovedIds.includes(ing.id) ? '✕' : '−'"></span>
                                            <span x-text="ing.name"></span>
                                        </button>
                                    </template>
                                </div>
                            </section>

                            {{-- Extras --}}
                            <section class="pdetail__section"
                                     x-show="(selectedProduct?.extras || []).length > 0">
                                <div class="pdetail__label">Extras</div>
                                <div class="pdetail__extras">
                                    <template x-for="ex in (selectedProduct?.extras || [])" :key="ex.id">
                                        <button type="button"
                                                :class="pdetailExtraIds.includes(ex.id)
                                                    ? 'pdetail__extra pdetail__extra--on'
                                                    : 'pdetail__extra'"
                                                @click="pdetailExtraIds.includes(ex.id)
                                                    ? pdetailExtraIds.splice(pdetailExtraIds.indexOf(ex.id), 1)
                                                    : pdetailExtraIds.push(ex.id)">
                                            <span class="pdetail__extra-name" x-text="ex.name"></span>
                                            <span class="pdetail__extra-price"
                                                  x-text="ex.price > 0
                                                      ? '+ ' + Number(ex.price).toFixed(2).replace('.',',') + ' €'
                                                      : 'gratis'"></span>
                                            <span class="pdetail__check"
                                                  :class="pdetailExtraIds.includes(ex.id) ? 'pdetail__check--on' : ''"
                                                  aria-hidden="true"
                                                  x-text="pdetailExtraIds.includes(ex.id) ? '✓' : ''"></span>
                                        </button>
                                    </template>
                                </div>
                            </section>

                        </div>{{-- /pdetail__scroll --}}

                        {{-- Footer: cantidad + CTA --}}
                        @if($orderingAllowed)
                        <div class="pdetail__foot">
                            <div class="pdetail__qty">
                                <button type="button"
                                        class="pdetail__qtybtn pdetail__qtybtn--minus"
                                        @click="pdetailQty = Math.max(1, pdetailQty - 1)"
                                        aria-label="Quitar uno">−</button>
                                <span class="pdetail__qtyn" x-text="pdetailQty"></span>
                                <button type="button"
                                        class="pdetail__qtybtn pdetail__qtybtn--plus"
                                        @click="pdetailQty++"
                                        aria-label="Añadir uno">+</button>
                            </div>
                            <button type="button"
                                    class="pdetail__cta"
                                    @click="pdetailAddToCart()"
                                    :disabled="selectedProduct?.destination === 'kitchen' && !kitchenOpen">
                                <span class="pdetail__cta-lab"
                                      x-text="$store.cart.items.some(i => i.productId === selectedProduct?.id && !i.variantId)
                                          ? 'Actualizar carrito' : 'Añadir al carrito'"></span>
                                <span class="pdetail__cta-price"
                                      x-text="Number(((selectedProduct?.price || 0) + (selectedProduct?.extras || []).filter(e => pdetailExtraIds.includes(e.id)).reduce((s, e) => s + e.price, 0)) * pdetailQty).toFixed(2).replace('.',',') + ' €'"></span>
                            </button>
                        </div>
                        @endif

                    </div>{{-- /pdetail__content --}}
                </div>{{-- /pdetail__layout --}}
            </div>{{-- /pdetail --}}
        </div>{{-- /scrim.scrim--center --}}

    </div>{{-- /carta (Alpine) --}}
</body>
</html>


