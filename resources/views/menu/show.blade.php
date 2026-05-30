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

        {{-- ── FAB Solicitar cuenta ────────────────────────────────── --}}
        <div class="fixed bottom-6 left-4 z-50"
             x-show="$store.bill.active && !$store.chat.open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-75"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-75">
            <button type="button"
                    @click="$store.bill.open()"
                    :disabled="$store.bill.requested || $store.bill.sending"
                    aria-label="Solicitar la cuenta"
                    class="flex items-center gap-2 px-4 py-3 rounded-full shadow-xl font-bold text-sm
                           transition-colors focus:outline-none focus:ring-4 focus:ring-indigo-400
                           disabled:opacity-70 disabled:cursor-not-allowed"
                    :class="$store.bill.requested
                        ? 'bg-green-600 text-white'
                        : 'bg-indigo-600 hover:bg-indigo-700 text-white'">
                <template x-if="!$store.bill.requested && !$store.bill.sending">
                    <span class="flex items-center gap-2">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                        </svg>
                        Pedir la cuenta
                    </span>
                </template>
                <template x-if="$store.bill.sending">
                    <span class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        Enviando...
                    </span>
                </template>
                <template x-if="$store.bill.requested">
                    <span class="flex items-center gap-2">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-text="$store.bill.paymentDone ? '¡Pago completado!' : ($store.bill.method === 'cash' ? 'Cuenta solicitada · Efectivo' : ($store.bill.method === 'mixed' ? 'Tarjeta pagada · Espera al camarero' : 'Cuenta solicitada · Tarjeta'))"></span>
                    </span>
                </template>
            </button>
            <template x-if="$store.bill.error">
                <p class="mt-1 text-xs text-red-600 bg-white rounded px-2 py-1 shadow"
                   role="alert"
                   x-text="$store.bill.error"></p>
            </template>
            <template x-if="$store.bill.paymentDone && $store.bill.paidOrderId">
                <a :href="$store.bill.ticketDownloadBase + '/' + $store.bill.paidOrderId + '/download?hash=' + $store.bill.tableHash"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="mt-2 w-full flex items-center justify-center gap-2 rounded-xl
                          bg-white/20 hover:bg-white/30 text-white text-sm font-medium
                          py-2.5 px-4 transition-colors focus:outline-none
                          focus:ring-2 focus:ring-white/50"
                   :aria-label="'Descargar ticket del pedido #' + $store.bill.paidOrderId">
                    <svg aria-hidden="true" class="w-4 h-4 shrink-0" fill="none"
                         stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 16v-8m0 8l-3-3m3 3l3-3M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2"/>
                    </svg>
                    Descargar ticket
                </a>
            </template>
        </div>

        {{-- ── Sheet: elección de método de pago ───────────────────── --}}
        <div x-show="$store.bill.choosing"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
             @click.self="$store.bill.close()"
             @keydown.escape.window="$store.bill.close()"
             aria-modal="true" role="dialog" aria-label="Elige cómo pagar">

            <div x-show="$store.bill.choosing"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-900
                        rounded-t-2xl shadow-2xl px-5 pb-8 pt-4">

                <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto mb-5"></div>

                <h2 class="text-lg font-bold text-gray-900 dark:text-white text-center mb-1">
                    Solicitar la cuenta
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">
                    ¿Cómo quieres pagar?
                </p>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button"
                            @click="$store.bill.openCashTip()"
                            class="flex flex-col items-center justify-center gap-2 py-5 rounded-2xl
                                   bg-gray-50 dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700
                                   hover:border-green-500 hover:bg-green-50 dark:hover:bg-green-900/20
                                   transition-colors focus:outline-none focus:ring-2 focus:ring-green-500">
                        <span class="text-3xl" aria-hidden="true">💵</span>
                        <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">Efectivo</span>
                    </button>

                    <button type="button"
                            @click="$store.bill.openCardPayment()"
                            class="flex flex-col items-center justify-center gap-2 py-5 rounded-2xl
                                   bg-gray-50 dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700
                                   hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20
                                   transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <span class="text-3xl" aria-hidden="true">💳</span>
                        <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">Tarjeta</span>
                    </button>
                </div>

                {{-- Cobro mixto (efectivo + tarjeta) --}}
                <div class="mt-3">
                    <button type="button"
                            @click="$store.bill.openMixed()"
                            class="w-full flex items-center justify-center gap-2 py-3.5 rounded-2xl
                                   bg-gray-50 dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700
                                   hover:border-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20
                                   transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <span class="text-xl" aria-hidden="true">💵💳</span>
                        <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">Cobro mixto</span>
                        <span class="text-xs text-gray-400 dark:text-gray-500">(efectivo + tarjeta)</span>
                    </button>
                </div>

                {{-- Cobro partido (solo si está habilitado para este restaurante) --}}
                <template x-if="$store.bill.splitEnabled">
                    <div class="mt-3 space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 whitespace-nowrap px-2">o paga tu parte</span>
                            <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                        </div>
                        <button type="button"
                                @click="$store.bill.openSplit()"
                                class="w-full flex items-center justify-center gap-2 py-3.5 rounded-2xl
                                       bg-gray-50 dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700
                                       hover:border-violet-500 hover:bg-violet-50 dark:hover:bg-violet-900/20
                                       transition-colors focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <span class="text-xl" aria-hidden="true">🤝</span>
                            <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">Cobro partido</span>
                        </button>
                    </div>
                </template>

                <button type="button"
                        @click="$store.bill.close()"
                        class="w-full mt-4 py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-gray-400
                               hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none focus:underline transition-colors">
                    Cancelar
                </button>
            </div>
        </div>{{-- /sheet --}}

        {{-- ── Sheet: propina (antes del pago con tarjeta) ───────── --}}
        <div x-show="$store.bill.showingTip"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
             @click.self="$store.bill.closeTip()"
             @keydown.escape.window="$store.bill.closeTip()"
             aria-modal="true" role="dialog" aria-label="Añadir propina">

            <div x-show="$store.bill.showingTip"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-900
                        rounded-t-2xl shadow-2xl px-5 pb-8 pt-4">

                <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto mb-5"></div>

                {{-- Cabecera --}}
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">¿Quieres dejar propina?</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">La propina es completamente opcional</p>
                    </div>
                    <button type="button"
                            @click="$store.bill.closeTip()"
                            aria-label="Cancelar propina"
                            class="p-1.5 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100
                                   dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-400">
                        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Resumen del pedido --}}
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl px-4 py-3 mb-5 flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Total del pedido</span>
                    <span class="text-lg font-bold text-gray-900 dark:text-white"
                          x-text="$store.bill.orderTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                </div>

                {{-- Botones de porcentaje --}}
                <div class="grid grid-cols-4 gap-2 mb-4" role="group" aria-label="Porcentaje de propina">
                    <template x-for="pct in [5, 10, 15, 20]" :key="pct">
                        <button type="button"
                                @click="$store.bill.setTipPercent(pct)"
                                :aria-pressed="$store.bill.tipPercent === pct"
                                :class="$store.bill.tipPercent === pct
                                    ? 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border-indigo-400'
                                    : 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700'"
                                class="flex flex-col items-center py-3 rounded-xl border-2 font-semibold text-sm
                                       transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <span x-text="pct + '%'"></span>
                            <span class="text-xs font-normal mt-0.5 text-gray-500 dark:text-gray-400"
                                  x-text="(Math.round($store.bill.orderTotal * pct) / 100).toFixed(2).replace('.', ',') + ' €'"></span>
                        </button>
                    </template>
                </div>

                {{-- Input de propina personalizada --}}
                <div class="relative mb-5">
                    <label for="custom-tip-input" class="sr-only">Propina personalizada en euros</label>
                    <input type="number"
                           id="custom-tip-input"
                           min="0"
                           step="0.50"
                           placeholder="Otro importe"
                           @input="$store.bill.updateCustomTip($event.target.value)"
                           :value="$store.bill.tipPercent === null && $store.bill.tipAmount > 0 ? $store.bill.tipAmount : ''"
                           aria-describedby="custom-tip-hint"
                           class="w-full rounded-xl border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                  px-4 py-3 pr-10 text-right placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                  [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium pointer-events-none"
                          aria-hidden="true">€</span>
                </div>
                <p id="custom-tip-hint" class="sr-only">Escribe un importe personalizado en euros</p>

                {{-- Desglose total + propina --}}
                <div class="space-y-1 mb-5">
                    <template x-if="$store.bill.tipAmount > 0">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Propina</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300"
                                  x-text="'+ ' + $store.bill.tipAmount.toFixed(2).replace('.', ',') + ' €'"></span>
                        </div>
                    </template>
                    <div class="flex justify-between text-base font-bold border-t border-gray-200 dark:border-gray-700 pt-2">
                        <span class="text-gray-900 dark:text-white">Total a pagar</span>
                        <span class="text-indigo-600 dark:text-indigo-400"
                              x-text="$store.bill.grandTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                    </div>
                </div>

                {{-- Botón continuar --}}
                <button type="button"
                        @click="$store.bill.proceedToStripe()"
                        class="w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700
                               text-white font-bold text-base shadow-sm transition-colors
                               focus:outline-none focus:ring-4 focus:ring-indigo-400">
                    <span x-show="$store.bill.tipAmount > 0">
                        💳 Pagar <span x-text="$store.bill.grandTotal.toFixed(2).replace('.', ',') + ' €'"></span> con propina
                    </span>
                    <span x-show="$store.bill.tipAmount <= 0">
                        💳 Pagar sin propina
                    </span>
                </button>

                <button type="button"
                        @click="$store.bill.closeTip()"
                        class="w-full mt-3 py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-gray-400
                               hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none focus:underline transition-colors">
                    Cancelar
                </button>
            </div>
        </div>{{-- /tip sheet --}}

        {{-- ── Sheet: propina para efectivo ──────────────────────── --}}
        <div x-show="$store.bill.showingCashTip"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
             @click.self="$store.bill.closeCashTip()"
             @keydown.escape.window="$store.bill.closeCashTip()"
             aria-modal="true" role="dialog" aria-label="Añadir propina para pago en efectivo">

            <div x-show="$store.bill.showingCashTip"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-900
                        rounded-t-2xl shadow-2xl px-5 pb-8 pt-4">

                <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto mb-5"></div>

                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">¿Quieres dejar propina?</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">La propina es completamente opcional</p>
                    </div>
                    <button type="button"
                            @click="$store.bill.closeCashTip()"
                            aria-label="Cancelar propina"
                            class="p-1.5 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100
                                   dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-400">
                        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl px-4 py-3 mb-5 flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Total del pedido</span>
                    <span class="text-lg font-bold text-gray-900 dark:text-white"
                          x-text="$store.bill.orderTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                </div>

                <div class="grid grid-cols-4 gap-2 mb-4" role="group" aria-label="Porcentaje de propina">
                    <template x-for="pct in [5, 10, 15, 20]" :key="pct">
                        <button type="button"
                                @click="$store.bill.setCashTipPercent(pct)"
                                :aria-pressed="$store.bill.cashTipPercent === pct"
                                :class="$store.bill.cashTipPercent === pct
                                    ? 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 border-green-400'
                                    : 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700'"
                                class="flex flex-col items-center py-3 rounded-xl border-2 font-semibold text-sm
                                       transition-colors focus:outline-none focus:ring-2 focus:ring-green-500">
                            <span x-text="pct + '%'"></span>
                            <span class="text-xs font-normal mt-0.5 text-gray-500 dark:text-gray-400"
                                  x-text="(Math.round($store.bill.orderTotal * pct) / 100).toFixed(2).replace('.', ',') + ' €'"></span>
                        </button>
                    </template>
                </div>

                <div class="relative mb-5">
                    <label for="cash-tip-input" class="sr-only">Propina personalizada en euros</label>
                    <input type="number"
                           id="cash-tip-input"
                           min="0"
                           step="0.50"
                           placeholder="Otro importe"
                           @input="$store.bill.updateCustomCashTip($event.target.value)"
                           :value="$store.bill.cashTipPercent === null && $store.bill.cashTipAmount > 0 ? $store.bill.cashTipAmount : ''"
                           class="w-full rounded-xl border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                  px-4 py-3 pr-10 text-right placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500
                                  [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium pointer-events-none"
                          aria-hidden="true">€</span>
                </div>

                <div class="space-y-1 mb-4">
                    <template x-if="$store.bill.cashTipAmount > 0">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Propina</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300"
                                  x-text="'+ ' + $store.bill.cashTipAmount.toFixed(2).replace('.', ',') + ' €'"></span>
                        </div>
                    </template>
                    <div class="flex justify-between text-base font-bold border-t border-gray-200 dark:border-gray-700 pt-2">
                        <span class="text-gray-900 dark:text-white">Total a pagar</span>
                        <span class="text-green-600 dark:text-green-400"
                              x-text="$store.bill.cashGrandTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                    </div>
                </div>

                <div class="mb-5 bg-green-50 dark:bg-green-900/20 rounded-xl px-4 py-3 flex items-center gap-2 text-sm text-green-700 dark:text-green-300">
                    <svg aria-hidden="true" class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>La propina se entrega directamente al camarero en efectivo</span>
                </div>

                <button type="button"
                        @click="$store.bill.confirmCashPayment()"
                        :disabled="$store.bill.sending"
                        class="w-full py-3.5 rounded-xl bg-green-600 hover:bg-green-700 disabled:opacity-60
                               text-white font-bold text-base shadow-sm transition-colors
                               focus:outline-none focus:ring-4 focus:ring-green-400">
                    <span x-show="!$store.bill.sending">
                        <span x-show="$store.bill.cashTipAmount > 0">
                            💵 Solicitar cuenta —
                            <span x-text="$store.bill.cashGrandTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                            con propina
                        </span>
                        <span x-show="$store.bill.cashTipAmount <= 0">
                            💵 Solicitar cuenta sin propina
                        </span>
                    </span>
                    <span x-show="$store.bill.sending" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        Enviando solicitud...
                    </span>
                </button>

                <button type="button"
                        @click="$store.bill.closeCashTip()"
                        class="w-full mt-3 py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-gray-400
                               hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none focus:underline transition-colors">
                    Cancelar
                </button>
            </div>
        </div>{{-- /cash tip sheet --}}

        {{-- ── Sheet: pago con tarjeta (Stripe Elements) ──────────── --}}
        <div x-show="$store.bill.payingCard"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
             @keydown.escape.window="$store.bill.closeCardPayment()"
             aria-modal="true" role="dialog" aria-label="Pago con tarjeta">

            <div x-show="$store.bill.payingCard"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-900
                        rounded-t-2xl shadow-2xl px-5 pb-8 pt-4 max-h-[92dvh] overflow-y-auto">

                <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto mb-5"></div>

                {{-- Cabecera --}}
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Pago con tarjeta</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            Total:
                            <span class="font-semibold text-gray-900 dark:text-white"
                                  x-text="$store.bill.stripeTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                        </p>
                        <template x-if="$store.bill.tipAmount > 0">
                            <p class="text-xs text-indigo-500 dark:text-indigo-400 mt-0.5">
                                Incluye propina de
                                <span x-text="$store.bill.tipAmount.toFixed(2).replace('.', ',') + ' €'"></span>
                            </p>
                        </template>
                    </div>
                    <button type="button"
                            @click="$store.bill.closeCardPayment()"
                            aria-label="Cerrar pago"
                            class="p-1.5 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100
                                   dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-400">
                        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Spinner mientras carga Stripe Elements --}}
                <div x-show="!$store.bill.stripeReady && !$store.bill.stripeError"
                     class="flex items-center justify-center py-10">
                    <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24" aria-label="Cargando formulario de pago">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                </div>

                {{-- Formulario Stripe Elements --}}
                <div id="stripe-payment-element" class="mb-4"></div>

                {{-- Error de Stripe --}}
                <template x-if="$store.bill.stripeError">
                    <p class="mb-3 text-sm text-red-600 dark:text-red-400 font-medium text-center"
                       role="alert"
                       x-text="$store.bill.stripeError"></p>
                </template>

                {{-- Botón pagar --}}
                <button type="button"
                        x-show="$store.bill.stripeReady"
                        @click="$store.bill.submitCardPayment()"
                        :disabled="$store.bill.sending"
                        class="w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60
                               text-white font-bold text-base shadow-sm transition-colors
                               focus:outline-none focus:ring-4 focus:ring-indigo-400">
                    <span x-show="!$store.bill.sending">
                        💳 Pagar <span x-text="$store.bill.stripeTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                    </span>
                    <span x-show="$store.bill.sending" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        Procesando...
                    </span>
                </button>

                <p class="mt-3 text-center text-xs text-gray-400 dark:text-gray-500">
                    Pago seguro procesado por
                    <span class="font-semibold text-indigo-500">Stripe</span>
                    &middot; Tarjeta de prueba: 4242 4242 4242 4242
                </p>
            </div>
        </div>{{-- /stripe sheet --}}

        {{-- ── Sheet: selector de modo de cobro partido ─────────────── --}}
        <div x-show="$store.bill.showingSplit"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
             @click.self="$store.bill.closeSplitSelector()"
             @keydown.escape.window="$store.bill.closeSplitSelector()"
             aria-modal="true" role="dialog" aria-label="Elige cómo pagar tu parte">

            <div x-show="$store.bill.showingSplit"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-900
                        rounded-t-2xl shadow-2xl px-5 pb-8 pt-4">

                <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto mb-5"></div>

                <h2 class="text-lg font-bold text-gray-900 dark:text-white text-center mb-1">
                    Cobro partido
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">
                    ¿Cómo quieres dividir la cuenta?
                </p>

                <div class="space-y-3">
                    <button type="button"
                            @click="!$store.bill.splitEquitativeLocked && $store.bill.openSplitItems()"
                            :disabled="$store.bill.splitEquitativeLocked"
                            :class="$store.bill.splitEquitativeLocked
                                ? 'opacity-50 cursor-not-allowed bg-gray-50 dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700'
                                : 'bg-gray-50 dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 hover:border-violet-500 hover:bg-violet-50 dark:hover:bg-violet-900/20 focus:ring-2 focus:ring-violet-500'"
                            class="w-full flex items-center gap-3 py-4 px-4 rounded-2xl
                                   transition-colors focus:outline-none">
                        <span class="text-2xl flex-shrink-0" aria-hidden="true">🧾</span>
                        <div class="text-left">
                            <p class="font-semibold text-sm text-gray-800 dark:text-gray-200">Pagar por ítems</p>
                            <p class="text-xs mt-0.5"
                               :class="$store.bill.splitEquitativeLocked
                                   ? 'text-amber-600 dark:text-amber-400'
                                   : 'text-gray-500 dark:text-gray-400'"
                               x-text="$store.bill.splitEquitativeLocked
                                   ? 'No disponible: ya hay pagos a partes iguales en curso'
                                   : 'Elige exactamente qué platos pagas tú'">
                            </p>
                        </div>
                    </button>

                    <button type="button"
                            @click="$store.bill.openSplitEq()"
                            class="w-full flex items-center gap-3 py-4 px-4 rounded-2xl
                                   bg-gray-50 dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700
                                   hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20
                                   transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <span class="text-2xl flex-shrink-0" aria-hidden="true">➗</span>
                        <div class="text-left">
                            <p class="font-semibold text-sm text-gray-800 dark:text-gray-200">Dividir a partes iguales</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">El total se divide entre todos por igual</p>
                        </div>
                    </button>
                </div>

                <button type="button"
                        @click="$store.bill.closeSplitSelector()"
                        class="w-full mt-4 py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-gray-400
                               hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none focus:underline transition-colors">
                    Cancelar
                </button>
            </div>
        </div>{{-- /split selector sheet --}}

        {{-- ── Sheet: cobro partido por ítems (Modo A) ────────────────── --}}
        <div x-show="$store.bill.splitShowItems"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
             @click.self="$store.bill.closeSplitItems()"
             @keydown.escape.window="$store.bill.closeSplitItems()"
             aria-modal="true" role="dialog" aria-label="Selecciona qué ítems pagas">

            <div x-show="$store.bill.splitShowItems"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute bottom-0 left-0 right-0 max-h-[88dvh] flex flex-col
                        bg-white dark:bg-gray-900 rounded-t-2xl shadow-2xl overflow-hidden">

                {{-- Cabecera --}}
                <div class="flex-shrink-0 px-5 pt-4 pb-3 border-b border-gray-200 dark:border-gray-700">
                    <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto mb-4"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Pagar por ítems</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Marca los platos que quieres pagar tú</p>
                        </div>
                        <button type="button"
                                @click="$store.bill.closeSplitItems()"
                                aria-label="Cerrar selección de ítems"
                                class="p-1.5 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100
                                       dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-400">
                            <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Lista de ítems --}}
                <div class="flex-1 overflow-y-auto px-5 py-3">
                    <template x-if="$store.bill.splitItems.length === 0">
                        <p class="text-center text-sm text-gray-400 dark:text-gray-500 py-8">
                            No hay ítems en el pedido activo.
                        </p>
                    </template>
                    <fieldset class="space-y-2">
                        <legend class="sr-only">Selecciona los ítems que quieres pagar</legend>
                        <template x-for="item in $store.bill.splitItems" :key="item.id">
                            <label :for="'split-item-' + item.id"
                                   :class="item.claimed
                                       ? 'opacity-50 cursor-not-allowed bg-gray-50 dark:bg-gray-800/50 border-gray-200 dark:border-gray-700'
                                       : $store.bill.isItemSelected(item.id)
                                           ? 'ring-2 ring-violet-500 bg-violet-50 dark:bg-violet-900/20 border-violet-400 dark:border-violet-600 cursor-pointer'
                                           : 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 cursor-pointer hover:border-violet-400'"
                                   class="flex items-center gap-3 p-3 rounded-xl border-2 transition-colors select-none">
                                <input type="checkbox"
                                       :id="'split-item-' + item.id"
                                       :checked="$store.bill.isItemSelected(item.id)"
                                       :disabled="item.claimed"
                                       @change="$store.bill.toggleSplitItem(item.id, item.claimed)"
                                       :aria-label="item.name + ' — ' + item.total.toFixed(2).replace('.', ',') + ' €'"
                                       :aria-disabled="item.claimed"
                                       class="w-5 h-5 rounded text-violet-600 border-gray-300
                                              focus:ring-violet-500 flex-shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-sm text-gray-900 dark:text-white leading-snug"
                                       x-text="item.name"></p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5"
                                       x-text="item.quantity + ' × ' + item.price.toFixed(2).replace('.', ',') + ' €'"></p>
                                </div>
                                <div class="flex-shrink-0 text-right">
                                    <template x-if="item.claimed">
                                        <span class="inline-block text-xs font-medium text-amber-600 dark:text-amber-400
                                                     bg-amber-100 dark:bg-amber-900/40 px-2 py-0.5 rounded-full"
                                              aria-label="Ítem ya reclamado por otro comensal">
                                            Ya reclamado
                                        </span>
                                    </template>
                                    <template x-if="!item.claimed">
                                        <span class="font-bold text-sm text-gray-700 dark:text-gray-300"
                                              x-text="item.total.toFixed(2).replace('.', ',') + ' €'"></span>
                                    </template>
                                </div>
                            </label>
                        </template>
                    </fieldset>
                </div>

                {{-- Footer con total reactivo y botón --}}
                <div class="flex-shrink-0 px-5 py-4 border-t border-gray-200 dark:border-gray-700
                            bg-white dark:bg-gray-900 space-y-3">
                    <div role="status" aria-live="polite"
                         class="flex items-center justify-between bg-gray-50 dark:bg-gray-800 rounded-xl px-4 py-3">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Tu total</span>
                        <span class="text-xl font-bold text-violet-600 dark:text-violet-400"
                              x-text="$store.bill.splitItemsTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                    </div>

                    <button type="button"
                            @click="$store.bill.openSplitTip(
                                $store.bill.splitItemsTotal,
                                'items',
                                $store.bill.splitSelected
                            )"
                            :disabled="$store.bill.splitSelected.length === 0 || $store.bill.sending"
                            class="w-full py-3.5 rounded-xl bg-violet-600 hover:bg-violet-700 disabled:opacity-50
                                   text-white font-bold text-base shadow-sm transition-colors
                                   focus:outline-none focus:ring-4 focus:ring-violet-400">
                        <span x-show="!$store.bill.sending">
                            💳 Pagar mi selección —
                            <span x-text="$store.bill.splitItemsTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                        </span>
                        <span x-show="$store.bill.sending" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            Procesando...
                        </span>
                    </button>

                    <button type="button"
                            @click="$store.bill.closeSplitItems()"
                            class="w-full py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-gray-400
                                   hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none focus:underline transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>{{-- /split items sheet --}}

        {{-- ── Sheet: cobro partido equitativo (Modo B) ───────────────── --}}
        <div x-show="$store.bill.splitShowEq"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
             @click.self="$store.bill.closeSplitEq()"
             @keydown.escape.window="$store.bill.closeSplitEq()"
             aria-modal="true" role="dialog" aria-label="Dividir la cuenta a partes iguales">

            <div x-show="$store.bill.splitShowEq"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-900
                        rounded-t-2xl shadow-2xl px-5 pb-8 pt-4">

                <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto mb-5"></div>

                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Dividir a partes iguales</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">¿Cuántas personas sois?</p>
                    </div>
                    <button type="button"
                            @click="$store.bill.closeSplitEq()"
                            aria-label="Cerrar cobro equitativo"
                            class="p-1.5 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100
                                   dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-400">
                        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Total del pedido --}}
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl px-4 py-3 mb-5 flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Total del pedido</span>
                    <span class="text-lg font-bold text-gray-900 dark:text-white"
                          x-text="$store.bill.orderTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                </div>

                {{-- Selector de número de personas --}}
                <div class="mb-5">
                    <label for="split-people-input"
                           class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Número de personas
                    </label>
                    <div class="flex items-center gap-3">
                        <button type="button"
                                @click="if ($store.bill.splitPeople > 2) $store.bill.splitPeople--"
                                :disabled="$store.bill.splitPeople <= 2"
                                aria-label="Reducir número de personas"
                                class="w-10 h-10 flex items-center justify-center rounded-full border-2
                                       border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400
                                       hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400
                                       disabled:opacity-40 disabled:cursor-not-allowed
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                            <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                            </svg>
                        </button>
                        <input type="number"
                               id="split-people-input"
                               x-model.number="$store.bill.splitPeople"
                               min="2"
                               :max="$store.bill.splitMaxParts || 20"
                               aria-required="true"
                               aria-describedby="split-people-hint"
                               class="flex-1 text-center text-2xl font-bold text-gray-900 dark:text-white
                                      bg-transparent border-0 focus:outline-none focus:ring-0">
                        <button type="button"
                                @click="if (!$store.bill.splitMaxParts || $store.bill.splitPeople < $store.bill.splitMaxParts) $store.bill.splitPeople++"
                                :disabled="$store.bill.splitMaxParts && $store.bill.splitPeople >= $store.bill.splitMaxParts"
                                aria-label="Aumentar número de personas"
                                class="w-10 h-10 flex items-center justify-center rounded-full border-2
                                       border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400
                                       hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400
                                       disabled:opacity-40 disabled:cursor-not-allowed
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                            <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                    </div>
                    <p id="split-people-hint" class="sr-only">Mínimo 2 personas</p>
                </div>

                {{-- Resumen reactivo --}}
                <div role="status" aria-live="polite"
                     class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl px-4 py-4 mb-5 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Total del pedido</span>
                        <span class="font-medium text-gray-900 dark:text-white"
                              x-text="$store.bill.orderTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Personas</span>
                        <span class="font-medium text-gray-900 dark:text-white"
                              x-text="$store.bill.splitPeople"></span>
                    </div>
                    <div class="flex justify-between text-base font-bold border-t border-indigo-200 dark:border-indigo-700 pt-2">
                        <span class="text-indigo-700 dark:text-indigo-300">Tu parte</span>
                        <span class="text-indigo-600 dark:text-indigo-400 text-xl"
                              x-text="$store.bill.splitMyPart.toFixed(2).replace('.', ',') + ' €'"></span>
                    </div>
                </div>

                {{-- Botón pagar --}}
                <button type="button"
                        @click="$store.bill.openSplitTip($store.bill.splitMyPart, 'equitable', [])"
                        :disabled="$store.bill.sending"
                        class="w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60
                               text-white font-bold text-base shadow-sm transition-colors
                               focus:outline-none focus:ring-4 focus:ring-indigo-400">
                    <span x-show="!$store.bill.sending">
                        💳 Pagar mi parte —
                        <span x-text="$store.bill.splitMyPart.toFixed(2).replace('.', ',') + ' €'"></span>
                    </span>
                    <span x-show="$store.bill.sending" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        Procesando...
                    </span>
                </button>

                <button type="button"
                        @click="$store.bill.closeSplitEq()"
                        class="w-full mt-3 py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-gray-400
                               hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none focus:underline transition-colors">
                    Cancelar
                </button>
            </div>
        </div>{{-- /split equitable sheet --}}

        {{-- ── Sheet: propina para cobro partido ──────────────────── --}}
        <div x-show="$store.bill.showingSplitTip"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
             @click.self="$store.bill.closeSplitTip()"
             @keydown.escape.window="$store.bill.closeSplitTip()"
             aria-modal="true" role="dialog" aria-label="Añadir propina al cobro partido">

            <div x-show="$store.bill.showingSplitTip"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-900
                        rounded-t-2xl shadow-2xl px-5 pb-8 pt-4">

                <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto mb-5"></div>

                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">¿Quieres dejar propina?</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">La propina es completamente opcional</p>
                    </div>
                    <button type="button"
                            @click="$store.bill.closeSplitTip()"
                            aria-label="Cancelar propina"
                            class="p-1.5 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100
                                   dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-400">
                        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl px-4 py-3 mb-5 flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Tu parte</span>
                    <span class="text-lg font-bold text-gray-900 dark:text-white"
                          x-text="$store.bill.splitTipBase.toFixed(2).replace('.', ',') + ' €'"></span>
                </div>

                <div class="grid grid-cols-4 gap-2 mb-4" role="group" aria-label="Porcentaje de propina">
                    <template x-for="pct in [5, 10, 15, 20]" :key="pct">
                        <button type="button"
                                @click="$store.bill.setSplitTipPercent(pct)"
                                :aria-pressed="$store.bill.splitTipPercent === pct"
                                :class="$store.bill.splitTipPercent === pct
                                    ? 'ring-2 ring-violet-500 bg-violet-50 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 border-violet-400'
                                    : 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700'"
                                class="flex flex-col items-center py-3 rounded-xl border-2 font-semibold text-sm
                                       transition-colors focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <span x-text="pct + '%'"></span>
                            <span class="text-xs font-normal mt-0.5 text-gray-500 dark:text-gray-400"
                                  x-text="(Math.round($store.bill.splitTipBase * pct) / 100).toFixed(2).replace('.', ',') + ' €'"></span>
                        </button>
                    </template>
                </div>

                <div class="relative mb-5">
                    <label for="split-tip-input" class="sr-only">Propina personalizada en euros</label>
                    <input type="number"
                           id="split-tip-input"
                           min="0"
                           step="0.50"
                           placeholder="Otro importe"
                           @input="$store.bill.updateCustomSplitTip($event.target.value)"
                           :value="$store.bill.splitTipPercent === null && $store.bill.splitTipAmount > 0 ? $store.bill.splitTipAmount : ''"
                           class="w-full rounded-xl border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                  px-4 py-3 pr-10 text-right placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500
                                  [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium pointer-events-none"
                          aria-hidden="true">€</span>
                </div>

                <div class="space-y-1 mb-5">
                    <template x-if="$store.bill.splitTipAmount > 0">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Propina</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300"
                                  x-text="'+ ' + $store.bill.splitTipAmount.toFixed(2).replace('.', ',') + ' €'"></span>
                        </div>
                    </template>
                    <div class="flex justify-between text-base font-bold border-t border-gray-200 dark:border-gray-700 pt-2">
                        <span class="text-gray-900 dark:text-white">Total a pagar</span>
                        <span class="text-violet-600 dark:text-violet-400"
                              x-text="$store.bill.splitTipGrandTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                    </div>
                </div>

                <button type="button"
                        @click="$store.bill.confirmSplitTip()"
                        :disabled="$store.bill.sending"
                        class="w-full py-3.5 rounded-xl bg-violet-600 hover:bg-violet-700 disabled:opacity-60
                               text-white font-bold text-base shadow-sm transition-colors
                               focus:outline-none focus:ring-4 focus:ring-violet-400">
                    <span x-show="!$store.bill.sending">
                        <span x-show="$store.bill.splitTipAmount > 0">
                            💳 Pagar <span x-text="$store.bill.splitTipGrandTotal.toFixed(2).replace('.', ',') + ' €'"></span> con propina
                        </span>
                        <span x-show="$store.bill.splitTipAmount <= 0">
                            💳 Pagar sin propina
                        </span>
                    </span>
                    <span x-show="$store.bill.sending" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        Procesando...
                    </span>
                </button>

                <button type="button"
                        @click="$store.bill.closeSplitTip()"
                        class="w-full mt-3 py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-gray-400
                               hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none focus:underline transition-colors">
                    Cancelar
                </button>
            </div>
        </div>{{-- /split tip sheet --}}

        {{-- ── Sheet: pago parcial con tarjeta (Stripe Elements) ──────── --}}
        <div x-show="$store.bill.splitPayingCard"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
             @keydown.escape.window="$store.bill.closeSplitPayment()"
             aria-modal="true" role="dialog" aria-label="Pago parcial con tarjeta">

            <div x-show="$store.bill.splitPayingCard"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-900
                        rounded-t-2xl shadow-2xl px-5 pb-8 pt-4 max-h-[92dvh] overflow-y-auto">

                <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto mb-5"></div>

                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Pago parcial con tarjeta</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            Tu parte:
                            <span class="font-semibold text-gray-900 dark:text-white"
                                  x-text="$store.bill.splitStripeTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                        </p>
                    </div>
                    <button type="button"
                            @click="$store.bill.closeSplitPayment()"
                            aria-label="Cerrar pago parcial"
                            class="p-1.5 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100
                                   dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-400">
                        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Spinner mientras carga Stripe Elements --}}
                <div x-show="!$store.bill.splitStripeReady && !$store.bill.splitStripeError"
                     class="flex items-center justify-center py-10">
                    <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"
                         aria-label="Cargando formulario de pago">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                </div>

                <div id="split-stripe-element" class="mb-4"></div>

                <template x-if="$store.bill.splitStripeError">
                    <p class="mb-3 text-sm text-red-600 dark:text-red-400 font-medium text-center"
                       role="alert"
                       x-text="$store.bill.splitStripeError"></p>
                </template>

                <button type="button"
                        x-show="$store.bill.splitStripeReady"
                        @click="$store.bill.submitSplitPayment()"
                        :disabled="$store.bill.sending"
                        class="w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60
                               text-white font-bold text-base shadow-sm transition-colors
                               focus:outline-none focus:ring-4 focus:ring-indigo-400">
                    <span x-show="!$store.bill.sending">
                        💳 Pagar
                        <span x-text="$store.bill.splitStripeTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                    </span>
                    <span x-show="$store.bill.sending" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        Procesando...
                    </span>
                </button>

                <p class="mt-3 text-center text-xs text-gray-400 dark:text-gray-500">
                    Pago parcial seguro procesado por
                    <span class="font-semibold text-indigo-500">Stripe</span>
                    &middot; Tarjeta de prueba: 4242 4242 4242 4242
                </p>
            </div>
        </div>{{-- /split stripe sheet --}}

        {{-- ── Sheet: cobro mixto — selector de importe ───────────── --}}
        <div x-show="$store.bill.showingMixed"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
             @click.self="$store.bill.closeMixed()"
             @keydown.escape.window="$store.bill.closeMixed()"
             aria-modal="true" role="dialog" aria-label="Cobro mixto — elige importe en efectivo">

            <div x-show="$store.bill.showingMixed"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-900
                        rounded-t-2xl shadow-2xl px-5 pb-8 pt-4">

                <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto mb-5"></div>

                <div class="flex items-center justify-between mb-5">
                    <button type="button" @click="$store.bill.closeMixed()"
                            class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 focus:outline-none focus:underline"
                            aria-label="Volver a métodos de pago">
                        ← Volver
                    </button>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Cobro mixto</h2>
                    <div class="w-14"></div>
                </div>

                <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">
                    Elige cuánto pagas en efectivo. El resto se cobrará con tarjeta.
                </p>

                {{-- Total del pedido --}}
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl px-4 py-3 mb-5 flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Total del pedido</span>
                    <span class="font-bold text-gray-900 dark:text-white"
                          x-text="$store.bill.orderTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                </div>

                {{-- Slider de importe en efectivo --}}
                <div class="mb-5">
                    <label for="mixed-cash-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Importe en efectivo
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="range"
                               id="mixed-cash-slider"
                               :min="0.01"
                               :max="($store.bill.orderTotal - 0.50).toFixed(2)"
                               step="0.01"
                               x-model="$store.bill.mixedCashAmount"
                               class="flex-1 accent-amber-500"
                               aria-label="Importe en efectivo">
                        <div class="relative w-28">
                            <input type="number"
                                   id="mixed-cash-input"
                                   :min="0.01"
                                   :max="($store.bill.orderTotal - 0.50).toFixed(2)"
                                   step="0.01"
                                   x-model="$store.bill.mixedCashAmount"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 pr-7
                                          text-sm text-right bg-white dark:bg-gray-800 dark:text-white
                                          focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                   aria-label="Importe exacto en efectivo">
                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-sm">€</span>
                        </div>
                    </div>
                </div>

                {{-- Resumen del desglose --}}
                <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-4 py-3 mb-6 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-300 flex items-center gap-1">
                            <span aria-hidden="true">💵</span> Pagas en efectivo
                        </span>
                        <span class="font-semibold text-gray-900 dark:text-white"
                              x-text="parseFloat($store.bill.mixedCashAmount || 0).toFixed(2).replace('.', ',') + ' €'"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-300 flex items-center gap-1">
                            <span aria-hidden="true">💳</span> Pagas con tarjeta
                        </span>
                        <span class="font-semibold text-gray-900 dark:text-white"
                              x-text="$store.bill.mixedCardAmount.toFixed(2).replace('.', ',') + ' €'"></span>
                    </div>
                </div>

                <template x-if="!$store.bill.mixedCashValid">
                    <p class="text-xs text-amber-600 dark:text-amber-400 text-center mb-3" role="alert">
                        El importe con tarjeta debe ser al menos 0,50 €.
                    </p>
                </template>

                <button type="button"
                        @click="$store.bill.openMixedTip()"
                        :disabled="!$store.bill.mixedCashValid || $store.bill.sending"
                        class="w-full py-3.5 rounded-xl bg-amber-500 hover:bg-amber-600 disabled:opacity-50
                               text-white font-bold text-base shadow-sm transition-colors
                               focus:outline-none focus:ring-4 focus:ring-amber-300">
                    Continuar → Añadir propina
                </button>

                <button type="button"
                        @click="$store.bill.closeMixed()"
                        class="w-full mt-3 py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-gray-400
                               hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none focus:underline transition-colors">
                    Cancelar
                </button>
            </div>
        </div>{{-- /mixed selector sheet --}}

        {{-- ── Sheet: cobro mixto — propina (sobre la parte de tarjeta) ── --}}
        <div x-show="$store.bill.showingMixedTip"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
             @click.self="$store.bill.closeMixedTip()"
             @keydown.escape.window="$store.bill.closeMixedTip()"
             aria-modal="true" role="dialog" aria-label="Añadir propina al pago con tarjeta">

            <div x-show="$store.bill.showingMixedTip"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-900
                        rounded-t-2xl shadow-2xl px-5 pb-8 pt-4">

                <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto mb-5"></div>

                <div class="flex items-center justify-between mb-5">
                    <button type="button" @click="$store.bill.closeMixedTip()"
                            class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 focus:outline-none focus:underline"
                            aria-label="Volver a cobro mixto">
                        ← Volver
                    </button>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Propina (tarjeta)</h2>
                    <div class="w-14"></div>
                </div>

                <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-4">
                    ¿Quieres añadir propina al pago con tarjeta?
                </p>

                <div class="grid grid-cols-4 gap-2 mb-4">
                    <template x-for="pct in [5, 10, 15, 20]" :key="pct">
                        <button type="button"
                                @click="$store.bill.setMixedTipPercent(pct)"
                                :class="$store.bill.mixedTipPercent === pct
                                    ? 'bg-amber-500 text-white border-amber-500'
                                    : 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-amber-400'"
                                class="py-2.5 rounded-xl border-2 text-sm font-semibold transition-colors
                                       focus:outline-none focus:ring-2 focus:ring-amber-400"
                                :aria-pressed="$store.bill.mixedTipPercent === pct"
                                :aria-label="'Propina ' + pct + '%'">
                            <span x-text="pct + '%'"></span>
                        </button>
                    </template>
                </div>

                <div class="relative mb-5">
                    <input type="number"
                           min="0" max="500" step="0.01"
                           placeholder="0,00"
                           @input="$store.bill.updateCustomMixedTip($event.target.value)"
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-3 pr-10
                                  text-sm bg-white dark:bg-gray-800 dark:text-white
                                  focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                           aria-label="Importe personalizado de propina">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">€</span>
                </div>

                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl px-4 py-3 mb-5 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Pago con tarjeta</span>
                        <span x-text="$store.bill.mixedTipBase.toFixed(2).replace('.', ',') + ' €'"
                              class="font-medium text-gray-700 dark:text-gray-300"></span>
                    </div>
                    <template x-if="$store.bill.mixedTipAmount > 0">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Propina</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300"
                                  x-text="'+ ' + $store.bill.mixedTipAmount.toFixed(2).replace('.', ',') + ' €'"></span>
                        </div>
                    </template>
                    <div class="flex justify-between text-base font-bold border-t border-gray-200 dark:border-gray-700 pt-2">
                        <span class="text-gray-900 dark:text-white">Total con tarjeta</span>
                        <span class="text-amber-600 dark:text-amber-400"
                              x-text="$store.bill.mixedTipGrandTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                    </div>
                </div>

                <button type="button"
                        @click="$store.bill.confirmMixedTip()"
                        :disabled="$store.bill.sending"
                        class="w-full py-3.5 rounded-xl bg-amber-500 hover:bg-amber-600 disabled:opacity-60
                               text-white font-bold text-base shadow-sm transition-colors
                               focus:outline-none focus:ring-4 focus:ring-amber-300">
                    <span x-show="!$store.bill.sending">
                        <span x-show="$store.bill.mixedTipAmount > 0"
                              x-text="'💳 Pagar ' + $store.bill.mixedTipGrandTotal.toFixed(2).replace(\'.\', \',\') + ' € con tarjeta'"></span>
                        <span x-show="$store.bill.mixedTipAmount <= 0">💳 Pagar sin propina</span>
                    </span>
                    <span x-show="$store.bill.sending" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        Procesando...
                    </span>
                </button>

                <button type="button"
                        @click="$store.bill.closeMixedTip()"
                        class="w-full mt-3 py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-gray-400
                               hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none focus:underline transition-colors">
                    Cancelar
                </button>
            </div>
        </div>{{-- /mixed tip sheet --}}

        {{-- ── Sheet: cobro mixto — pago con tarjeta (Stripe) ─────── --}}
        <div x-show="$store.bill.mixedPayingCard"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
             @click.self="$store.bill.closeMixedPayment()"
             @keydown.escape.window="$store.bill.closeMixedPayment()"
             aria-modal="true" role="dialog" aria-label="Pago con tarjeta — cobro mixto">

            <div x-show="$store.bill.mixedPayingCard"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-900
                        rounded-t-2xl shadow-2xl px-5 pb-8 pt-4">

                <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto mb-5"></div>

                <div class="flex items-center justify-between mb-5">
                    <button type="button" @click="$store.bill.closeMixedPayment()"
                            class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 focus:outline-none focus:underline"
                            aria-label="Volver">← Volver</button>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Pago con tarjeta</h2>
                    <div class="w-14"></div>
                </div>

                {{-- Resumen antes de pagar --}}
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl px-4 py-3 mb-5 space-y-1">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400 flex items-center gap-1">
                            <span aria-hidden="true">💵</span> Efectivo al camarero
                        </span>
                        <span class="font-semibold text-gray-800 dark:text-white"
                              x-text="parseFloat($store.bill.mixedCashAmount || 0).toFixed(2).replace('.', ',') + ' €'"></span>
                    </div>
                    <div class="flex justify-between text-sm font-bold">
                        <span class="text-gray-700 dark:text-gray-200 flex items-center gap-1">
                            <span aria-hidden="true">💳</span> Cargo tarjeta ahora
                        </span>
                        <span class="text-amber-600 dark:text-amber-400"
                              x-text="$store.bill.mixedStripeTotal.toFixed(2).replace('.', ',') + ' €'"></span>
                    </div>
                </div>

                <template x-if="$store.bill.mixedStripeError">
                    <p class="mb-3 text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20
                               rounded-lg px-4 py-2"
                       role="alert"
                       x-text="$store.bill.mixedStripeError"></p>
                </template>

                <div id="mixed-stripe-element" class="mb-5 min-h-[120px]"></div>

                <div x-show="!$store.bill.mixedStripeReady"
                     class="flex items-center justify-center py-6 text-sm text-gray-400 gap-2">
                    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    Cargando formulario...
                </div>

                <button type="button"
                        @click="$store.bill.submitMixedPayment()"
                        :disabled="!$store.bill.mixedStripeReady || $store.bill.sending"
                        x-show="$store.bill.mixedStripeReady"
                        class="w-full py-3.5 rounded-xl bg-amber-500 hover:bg-amber-600 disabled:opacity-60
                               text-white font-bold text-base shadow-sm transition-colors
                               focus:outline-none focus:ring-4 focus:ring-amber-300">
                    <span x-show="!$store.bill.sending"
                          x-text="'💳 Pagar ' + $store.bill.mixedStripeTotal.toFixed(2).replace(\'.\', \',\') + ' €'"></span>
                    <span x-show="$store.bill.sending" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        Procesando...
                    </span>
                </button>
            </div>
        </div>{{-- /mixed stripe sheet --}}

        {{-- ── Cart Bar DS ─────────────────────────────────────────── --}}
        <button type="button"
                class="cart-bar"
                x-show="$store.cart.count > 0 && !$store.chat.open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4"
                @click="$store.cart.open = true"
                aria-label="Ver pedido">
            <div class="cart-bar__left">
                <span class="cart-bar__icon">
                    <svg aria-hidden="true" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="cart-bar__count" x-text="$store.cart.count" aria-hidden="true"></span>
                </span>
                <span x-text="$store.cart.count + ' ' + ($store.cart.count === 1 ? 'artículo' : 'artículos')"></span>
            </div>
            <span class="cart-bar__cta">Ver pedido</span>
        </button>

        {{-- ── Drawer del carrito ───────────────────────────────────── --}}
        <div x-show="$store.cart.open"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
             @click.self="$store.cart.open = false"
             aria-modal="true" role="dialog" aria-label="Tu pedido">

            <div x-show="$store.cart.open"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute bottom-0 left-0 right-0 max-h-[90dvh] flex flex-col
                        bg-white dark:bg-gray-900 rounded-t-2xl shadow-2xl overflow-hidden">

                {{-- Handle + cabecera --}}
                <div class="flex-shrink-0 px-4 pt-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                    <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto mb-3"></div>
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                            Tu pedido
                            <span class="ml-1 text-sm font-normal text-gray-400">
                                (<span x-text="$store.cart.count"></span> <span x-text="$store.cart.count === 1 ? 'artículo' : 'artículos'"></span>)
                            </span>
                        </h2>
                        <button type="button"
                                @click="$store.cart.open = false"
                                class="p-1.5 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800
                                       focus:outline-none focus:ring-2 focus:ring-gray-400">
                            <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Lista de items --}}
                <div class="flex-1 overflow-y-auto px-4 py-3 space-y-4">
                    <template x-for="item in $store.cart.items" :key="item._key">
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3 space-y-3">

                            {{-- Fila producto --}}
                            <div class="flex items-start gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 dark:text-white text-sm leading-snug" x-text="item.name"></p>
                                    <p class="text-xs text-gray-400 mt-0.5" x-text="$store.cart.fmt(item.price) + ' / ud.'"></p>
                                </div>

                                {{-- Cantidad --}}
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <button type="button" @click="$store.cart.dec(item._key)"
                                            class="w-7 h-7 rounded-full border-2 border-gray-300 dark:border-gray-600
                                                   flex items-center justify-center text-gray-600 dark:text-gray-300
                                                   hover:border-red-400 hover:text-red-500 transition-colors
                                                   focus:outline-none focus:ring-2 focus:ring-red-400">
                                        <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    <span class="w-5 text-center font-bold text-gray-900 dark:text-white text-sm" x-text="item.quantity"></span>
                                    <button type="button" @click="$store.cart.inc(item._key)"
                                            class="w-7 h-7 rounded-full border-2 border-gray-300 dark:border-gray-600
                                                   flex items-center justify-center text-gray-600 dark:text-gray-300
                                                   hover:border-green-500 hover:text-green-600 transition-colors
                                                   focus:outline-none focus:ring-2 focus:ring-green-400">
                                        <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Subtotal --}}
                                <span class="flex-shrink-0 font-bold text-green-600 dark:text-green-400 text-sm"
                                      x-text="$store.cart.fmt($store.cart.lineTotal(item))"></span>
                            </div>

                            {{-- Modificaciones --}}
                            <template x-if="item.removable.length > 0 || item.extras.length > 0">
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-2 space-y-2">

                                    <template x-if="item.removable.length > 0">
                                        <div>
                                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Quitar</p>
                                            <div class="flex flex-wrap gap-1.5">
                                                <template x-for="ing in item.removable" :key="ing.id">
                                                    <button type="button"
                                                            @click="$store.cart.toggleRemove(item._key, ing)"
                                                            :class="$store.cart.hasMod(item._key, ing.id, 'remove')
                                                                ? 'bg-red-100 dark:bg-red-900/40 border-red-400 text-red-700 dark:text-red-300'
                                                                : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300'"
                                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border text-xs font-medium transition-colors
                                                                   focus:outline-none focus:ring-2 focus:ring-red-400">
                                                        <span x-text="'Sin ' + ing.name"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="item.extras.length > 0">
                                        <div>
                                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Extra</p>
                                            <div class="flex flex-wrap gap-1.5">
                                                <template x-for="ing in item.extras" :key="ing.id">
                                                    <button type="button"
                                                            @click="$store.cart.toggleExtra(item._key, ing)"
                                                            :class="$store.cart.hasMod(item._key, ing.id, 'add')
                                                                ? 'bg-green-100 dark:bg-green-900/40 border-green-500 text-green-700 dark:text-green-300'
                                                                : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300'"
                                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border text-xs font-medium transition-colors
                                                                   focus:outline-none focus:ring-2 focus:ring-green-400">
                                                        <span x-text="'+ ' + ing.name + (ing.price > 0 ? ' (+' + ing.price.toFixed(2).replace(\'.\',\',\') + \' €)\' : \'\'  )"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                </div>
                            </template>

                        </div>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="flex-shrink-0 px-4 py-4 border-t border-gray-200 dark:border-gray-700 space-y-3 bg-white dark:bg-gray-900">

                    <template x-if="$store.cart.error">
                        <p class="text-sm text-red-600 dark:text-red-400 text-center font-medium" x-text="$store.cart.error"></p>
                    </template>

                    <div class="flex items-center justify-between">
                        <span class="text-base font-semibold text-gray-700 dark:text-gray-300">Total</span>
                        <span class="text-xl font-bold text-gray-900 dark:text-white" x-text="$store.cart.fmt($store.cart.total)"></span>
                    </div>

                    <button type="button"
                            @click="$store.cart.send()"
                            :disabled="$store.cart.sending"
                            class="w-full py-3.5 rounded-xl bg-green-600 hover:bg-green-700 disabled:opacity-60
                                   text-white font-bold text-base shadow-sm transition-colors
                                   focus:outline-none focus:ring-4 focus:ring-green-400">
                        <span x-show="!$store.cart.sending" x-text="$store.cart.sendLabel"></span>
                        <span x-show="$store.cart.sending" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            Enviando...
                        </span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ── Confirmación pedido enviado ─────────────────────────── --}}
        <div x-show="$store.cart.sent"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-6">
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl p-8 max-w-sm w-full text-center space-y-4">
                <div class="mx-auto w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
                    <svg class="w-9 h-9 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">¡Pedido enviado!</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tu pedido está en camino a cocina. En breve lo tendrás listo.</p>
                <button type="button"
                        @click="$store.cart.sent = false"
                        class="w-full py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white font-bold transition-colors
                               focus:outline-none focus:ring-4 focus:ring-green-400">
                    Aceptar
                </button>
            </div>
        </div>

        {{-- ── Barra de filtros ─────────────────────────────────────── --}}
        @if($businessOpen)
        <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 shadow-sm"
             role="region" aria-label="Filtros de la carta">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-3 space-y-2">

                {{-- Grupo: Destino --}}
                <div class="flex items-center gap-2 overflow-x-auto pb-1"
                     role="group" aria-label="Filtrar por origen">
                    <span class="flex-shrink-0 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        Origen
                    </span>

                    <button type="button"
                            @click="setDestination('kitchen')"
                            :aria-pressed="activeDestination === 'kitchen'"
                            :class="activeDestination === 'kitchen'
                                ? 'bg-orange-500 dark:bg-orange-600 text-white border-orange-500 dark:border-orange-600'
                                : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-orange-400 hover:text-orange-600 dark:hover:text-orange-400'"
                            class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                   text-sm font-medium border transition-colors duration-150
                                   focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        <svg aria-hidden="true" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"
                                  clip-rule="evenodd"/>
                        </svg>
                        Cocina
                    </button>

                    <button type="button"
                            @click="setDestination('bar')"
                            :aria-pressed="activeDestination === 'bar'"
                            :class="activeDestination === 'bar'
                                ? 'bg-amber-500 dark:bg-amber-600 text-white border-amber-500 dark:border-amber-600'
                                : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-amber-400 hover:text-amber-600 dark:hover:text-amber-400'"
                            class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                                   text-sm font-medium border transition-colors duration-150
                                   focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        <svg aria-hidden="true" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 3a1 1 0 000 2h1.5l1.4 7H3a1 1 0 000 2h1.2l.4 2H3a1 1 0 100 2h14a1 1 0 100-2h-1.6l.4-2H17a1 1 0 000-2h-3.9L14.5 5H16a1 1 0 000-2H3z"/>
                        </svg>
                        Barra
                    </button>
                </div>

                {{-- Grupo: Alérgenos --}}
                @if ($allergens->isNotEmpty())
                    <div x-show="visibleAllergenKeys.length > 0"
                         class="flex items-center flex-wrap gap-2 py-1"
                         role="group" aria-label="Filtrar por alérgenos ausentes">
                        <span class="flex-shrink-0 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            Sin
                        </span>

                        @foreach ($allergens as $allergen)
                            @php
                                $allergenKey  = "'{$allergen->slug}'";
                                $allergenName = $allergen->name;
                            @endphp
                            <div class="relative" x-data="{ tip: false }"
                                 x-show="visibleAllergenKeys.includes({{ $allergenKey }})">
                                <button type="button"
                                        @click="toggleAllergen({{ $allergenKey }})"
                                        @mouseenter="tip = true"
                                        @mouseleave="tip = false"
                                        @touchstart.passive="tip = true; setTimeout(() => tip = false, 1400)"
                                        :aria-pressed="activeAllergens.includes({{ $allergenKey }})"
                                        :class="activeAllergens.includes({{ $allergenKey }})
                                            ? 'border-orange-500 opacity-100 ring-2 ring-orange-400 ring-offset-2 ring-offset-gray-900'
                                            : 'border-transparent opacity-60 hover:opacity-100'"
                                        class="p-1 rounded-full border-2 border-transparent transition-all duration-200 cursor-pointer
                                               focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2
                                               dark:focus:ring-offset-gray-900"
                                        aria-label="{{ $allergenName }}">
                                    <div class="h-12 w-12 rounded-full overflow-hidden">
                                        <img src="{{ asset('images/allergens/' . $allergen->slug . '.svg') }}"
                                             alt="{{ $allergenName }}"
                                             class="h-full w-full object-contain">
                                    </div>
                                </button>

                                <div x-show="tip"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1
                                            text-xs font-medium text-white bg-gray-900 dark:bg-gray-700
                                            rounded-md whitespace-nowrap shadow-lg z-20 pointer-events-none"
                                     role="tooltip">
                                    {{ $allergenName }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Contador + limpiar --}}
                <div class="flex items-center justify-between min-h-[1.5rem]">
                    <p class="text-xs text-gray-400 dark:text-gray-500"
                       role="status" aria-live="polite" aria-atomic="true">
                        <span x-text="visibleCount"></span>&nbsp;<span x-text="visibleCount === 1 ? 'producto' : 'productos'"></span> visibles
                    </p>
                    <button type="button"
                            x-show="hasActiveFilters"
                            x-transition
                            @click="clearAll()"
                            class="text-xs font-medium text-indigo-600 dark:text-indigo-400
                                   hover:text-indigo-800 dark:hover:text-indigo-200
                                   focus:outline-none focus:underline transition-colors">
                        Limpiar filtros
                    </button>
                </div>

            </div>
        </div>
        @endif

        {{-- ── Filtro de categorías DS (.filter-bar) ──────────────────── --}}
        @if ($businessOpen && $categories->isNotEmpty())
            <nav class="filter-bar" aria-label="Filtrar por categoría">

                {{-- Chip "Todas" --}}
                <button type="button"
                        @click="setCategory(null)"
                        :aria-pressed="activeCategory === null"
                        :class="activeCategory === null ? 'chip chip--active' : 'chip'">
                    Todas
                </button>

                @foreach ($categories as $category)
                    <button type="button"
                            x-show="isCategoryVisible({{ $category->id }})"
                            @click="setCategory({{ $category->id }})"
                            :aria-pressed="activeCategory === {{ $category->id }}"
                            :class="activeCategory === {{ $category->id }} ? 'chip chip--active' : 'chip'">
                        {{ $category->name }}
                    </button>
                @endforeach

            </nav>
        @endif

        {{-- ════════════════════════════════════════════════════════════ --}}
        {{-- NEGOCIO CERRADO: carta inaccesible + horarios del día      --}}
        {{-- ════════════════════════════════════════════════════════════ --}}
        <div class="carta__main">
        <div class="carta__body" id="main-content">
        @if(!$businessOpen)
        <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
            <div class="text-center" role="status" aria-live="polite">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-800 mb-6">
                    <svg aria-hidden="true" class="w-10 h-10 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M12 6v6l4 2M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                    {{ $table->user->business_name ?: $table->user->name }} {{ __('está cerrado ahora') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    {{ __('Estamos fuera de nuestro horario de atención. Vuelve cuando estemos abiertos.') }}
                </p>
                @if($businessNextOpening)
                <p class="inline-block bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-sm font-semibold px-4 py-2 rounded-full">
                    {{ __('Próxima apertura a las') }} {{ $businessNextOpening }}
                </p>
                @endif
            </div>

            @if($hasActiveOrder)
            <div class="mt-8 rounded-2xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 p-5 text-center" role="alert">
                <p class="text-sm font-semibold text-green-800 dark:text-green-300 mb-1">
                    {{ __('Tienes un pedido activo') }}
                </p>
                <p class="text-xs text-green-700 dark:text-green-400 mb-4">
                    {{ __('El negocio ha cerrado pero puedes solicitar la cuenta desde el botón de abajo.') }}
                </p>
                <button type="button"
                        @click="$store.bill.open()"
                        :disabled="$store.bill.requested || $store.bill.sending"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-green-600 hover:bg-green-700
                               text-white text-sm font-bold shadow transition-colors
                               disabled:opacity-60 disabled:cursor-not-allowed
                               focus:outline-none focus:ring-4 focus:ring-green-400">
                    <template x-if="!$store.bill.requested">
                        <span>💳 {{ __('Solicitar la cuenta') }}</span>
                    </template>
                    <template x-if="$store.bill.requested">
                        <span>✅ {{ __('Cuenta solicitada') }}</span>
                    </template>
                </button>
            </div>
            @endif
        </main>
        @else

        {{-- ── Contenido principal ──────────────────────────────────── --}}
        <main class="carta__products max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-10">

            {{-- ── Menú del Día ──────────────────────────────────────── --}}
            <x-daily-menu-banner :hash="$table->unique_hash" />

            {{-- ── Banner cocina cerrada ──────────────────────────────── --}}
            @if(!$kitchenOpen)
            <div role="status"
                 class="rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <span aria-hidden="true" class="text-2xl leading-none">🕐</span>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">
                        {{ __('La cocina está cerrada en este momento') }}
                    </p>
                    <p class="text-xs text-blue-700 dark:text-blue-400 mt-0.5">
                        {{ __('Ahora solo se sirven bebidas.') }}
                        @if($nextOpeningTime)
                            {{ __('La cocina abre a las') }}
                            <span class="font-semibold">{{ $nextOpeningTime }}</span>.
                        @endif
                    </p>
                </div>
            </div>
            @endif

            {{-- ── Banner período de cierre de pedidos ──────────────── --}}
            @if($businessOpen && !$orderingAllowed)
            <div role="alert"
                 class="rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <span aria-hidden="true" class="text-2xl leading-none">🛑</span>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">
                        {{ __('La cocina ya no acepta pedidos') }}
                    </p>
                    <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">
                        {{ __('Estamos en el período de cierre. Puedes solicitar la cuenta desde el botón de abajo.') }}
                    </p>
                </div>
            </div>
            @endif

            {{-- Estado vacío cuando los filtros excluyen todo --}}
            <div x-show="hasActiveFilters && visibleCount === 0"
                 x-transition
                 class="text-center py-16"
                 role="status" aria-live="polite">
                <svg aria-hidden="true" class="mx-auto w-12 h-12 text-gray-300 dark:text-gray-600 mb-4"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
                <p class="text-lg font-medium text-gray-400 dark:text-gray-500">
                    Ningún producto coincide con los filtros seleccionados.
                </p>
                <button type="button"
                        @click="clearAll()"
                        class="mt-3 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline
                               focus:outline-none focus:underline">
                    Ver todos los productos
                </button>
            </div>

            @forelse ($categories as $category)
                <section x-show="isCategoryVisible({{ $category->id }})"
                         x-transition
                         aria-labelledby="titulo-categoria-{{ $category->id }}"
                         id="categoria-{{ $category->id }}">

                    {{-- Encabezado de categoría --}}
                    <div class="flex items-center gap-3 mb-4 px-3 py-2 rounded-lg
                                bg-white dark:bg-gray-800
                                border-l-4 border-indigo-500 dark:border-indigo-400
                                shadow-sm">
                        <h2 id="titulo-categoria-{{ $category->id }}"
                            class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ $category->name }}
                        </h2>

                        @if ($category->destination === 'bar')
                            <span class="inline-flex items-center gap-1 text-xs font-medium
                                         bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300
                                         px-2 py-0.5 rounded-full border border-amber-200 dark:border-amber-700"
                                  aria-label="Servida desde la barra">
                                <svg aria-hidden="true" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 3a1 1 0 000 2h1.5l1.4 7H3a1 1 0 000 2h1.2l.4 2H3a1 1 0 100 2h14a1 1 0 100-2h-1.6l.4-2H17a1 1 0 000-2h-3.9L14.5 5H16a1 1 0 000-2H3z"/>
                                </svg>
                                Barra
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-medium
                                         bg-orange-100 dark:bg-orange-900/50 text-orange-700 dark:text-orange-300
                                         px-2 py-0.5 rounded-full border border-orange-200 dark:border-orange-700"
                                  aria-label="Preparada en cocina">
                                <svg aria-hidden="true" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"
                                          clip-rule="evenodd"/>
                                </svg>
                                Cocina
                            </span>
                        @endif
                    </div>

                    {{-- Lista de productos --}}
                    <ul class="space-y-3" role="list" aria-label="Productos de {{ $category->name }}">
                        @foreach ($category->products as $product)
                            <li x-show="isProductVisible({{ $product->id }})"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="pcard">
                                    {{-- Foto --}}
                                    @if ($product->image)
                                        <div class="pcard__photo">
                                            <img src="{{ Storage::url($product->image) }}"
                                                 alt="Foto de {{ $product->name }}"
                                                 loading="lazy"
                                                 decoding="async"
                                                 class="w-full h-full object-cover">
                                        </div>
                                    @else
                                        <div class="pcard__photo pcard__photo--food-1" aria-hidden="true"></div>
                                    @endif

                                    {{-- Info --}}
                                    <div class="pcard__body">
                                        @if($product->variants->isNotEmpty())
                                        {{-- Producto con variantes --}}
                                        <div x-data="{
                                            selectedVariantId: {{ $product->variants->first()->id }},
                                            variants: {{ $product->variants->map(fn($v) => ['id' => $v->id, 'name' => $v->name, 'price' => (float)$v->price])->values()->toJson() }},
                                            get selectedVariant() { return this.variants.find(v => v.id === this.selectedVariantId); }
                                        }">
                                            <div class="flex items-start justify-between gap-2">
                                                <h3 class="pcard__name">
                                                    {{ $product->name }}
                                                </h3>
                                                <span class="pcard__price"
                                                      x-text="'desde ' + Number(Math.min(...variants.map(v => v.price))).toFixed(2).replace('.',',') + ' €'"
                                                      aria-label="Desde {{ number_format($product->variants->min('price'), 2, ',', '.') }} euros"></span>
                                            </div>

                                            @if ($product->description)
                                                <p class="pcard__desc">
                                                    {{ $product->description }}
                                                </p>
                                            @endif

                                            {{-- Alérgenos --}}
                                            @php
                                                $productAllergenSlugs = $product->ingredients->where('is_allergen', true)
                                                    ->flatMap(fn($i) => $i->allergen_types ?? [])
                                                    ->unique()->values();
                                            @endphp
                                            @if ($productAllergenSlugs->isNotEmpty())
                                                <div class="mt-2" aria-label="Alérgenos de {{ $product->name }}">
                                                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-1">
                                                        Alérgenos
                                                    </p>
                                                    <ul class="flex flex-wrap gap-3" role="list">
                                                        @foreach ($productAllergenSlugs as $slug)
                                                            <li><x-allergen-badge :slug="$slug" /></li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif

                                            {{-- Chips de variante (server-rendered + Alpine para estado activo) --}}
                                            <div class="mt-3 flex flex-wrap gap-2" role="group" aria-label="Elige tamaño de {{ $product->name }}">
                                                @foreach($product->variants as $variant)
                                                    <button type="button"
                                                            @click="selectedVariantId = {{ $variant->id }}"
                                                            :class="selectedVariantId === {{ $variant->id }}
                                                                ? 'bg-indigo-600 text-white border-indigo-600'
                                                                : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:border-indigo-400'"
                                                            :aria-pressed="(selectedVariantId === {{ $variant->id }}).toString()"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-xs font-semibold
                                                                   transition-colors duration-150
                                                                   focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-1">
                                                        {{ $variant->name }}
                                                        <span class="opacity-75">{{ number_format($variant->price, 2, ',', '.') }}&nbsp;€</span>
                                                    </button>
                                                @endforeach
                                            </div>

                                            {{-- Botón añadir variante seleccionada --}}
                                            <div class="mt-3 flex justify-end">
                                                @if($orderingAllowed)
                                                <button type="button"
                                                        @click="$store.cart.addWithVariant(
                                                            {{ $product->id }},
                                                            selectedVariantId,
                                                            selectedVariant.name,
                                                            selectedVariant.price,
                                                            products.find(p => p.id === {{ $product->id }})
                                                        )"
                                                        :aria-label="'Añadir ' + selectedVariant.name + ' de {{ $product->name }} al pedido'"
                                                        class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full
                                                               bg-green-600 hover:bg-green-700 active:scale-95
                                                               text-white text-sm font-semibold shadow-sm
                                                               transition-all duration-150
                                                               focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                                    <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                    Añadir
                                                </button>
                                                @else
                                                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full
                                                             bg-gray-200 dark:bg-gray-700
                                                             text-gray-400 dark:text-gray-500 text-sm font-semibold
                                                             cursor-not-allowed select-none"
                                                      aria-disabled="true" title="{{ __('Pedidos cerrados') }}">
                                                    <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                    Añadir
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                        @else
                                        {{-- Producto sin variantes (comportamiento original) --}}
                                        <div class="flex items-start justify-between gap-2">
                                            <h3 class="pcard__name">
                                                {{ $product->name }}
                                            </h3>
                                            <span class="flex-shrink-0 font-bold text-base sm:text-lg
                                                         text-indigo-600 dark:text-indigo-400"
                                                  aria-label="Precio: {{ number_format($product->price, 2, ',', '.') }} euros">
                                                {{ number_format($product->price, 2, ',', '.') }}&nbsp;€
                                            </span>
                                        </div>

                                        @if ($product->description)
                                            <p class="pcard__desc">
                                                {{ $product->description }}
                                            </p>
                                        @endif

                                        {{-- Alérgenos --}}
                                        @php
                                            $productAllergenSlugs2 = $product->ingredients->where('is_allergen', true)
                                                ->flatMap(fn($i) => $i->allergen_types ?? [])
                                                ->unique()->values();
                                        @endphp
                                        @if ($productAllergenSlugs2->isNotEmpty())
                                            <div class="mt-2" aria-label="Alérgenos de {{ $product->name }}">
                                                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-1">
                                                    Alérgenos
                                                </p>
                                                <ul class="flex flex-wrap gap-3" role="list">
                                                    @foreach ($productAllergenSlugs2 as $slug)
                                                        <li><x-allergen-badge :slug="$slug" /></li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        {{-- Botón añadir al carrito --}}
                                        <div class="mt-3 flex justify-end">
                                            @if($orderingAllowed)
                                            <button type="button"
                                                    @click="$store.cart.add(products.find(p => p.id === {{ $product->id }}))"
                                                    aria-label="Añadir {{ $product->name }} al pedido"
                                                    class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full
                                                           bg-green-600 hover:bg-green-700 active:scale-95
                                                           text-white text-sm font-semibold shadow-sm
                                                           transition-all duration-150
                                                           focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                                <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                Añadir
                                            </button>
                                            @else
                                            <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full
                                                         bg-gray-200 dark:bg-gray-700
                                                         text-gray-400 dark:text-gray-500 text-sm font-semibold
                                                         cursor-not-allowed select-none"
                                                  aria-disabled="true"
                                                  title="{{ __('Pedidos cerrados') }}">
                                                <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                Añadir
                                            </span>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @empty
                <div class="text-center py-20" role="status">
                    <svg aria-hidden="true" class="mx-auto w-12 h-12 text-gray-300 dark:text-gray-600 mb-4"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                                 M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-lg font-medium text-gray-400 dark:text-gray-500">
                        La carta no está disponible en este momento.
                    </p>
                </div>
            @endforelse

        </main>
        @endif{{-- /businessOpen --}}
        </div>{{-- /carta__body --}}
        </div>{{-- /carta__main --}}

        {{-- ── Banner de Tapas disponibles ─────────────────────────── --}}
        @if($tapaConfig && $barItemsCount > 0)
        <section
            aria-label="Tapas disponibles"
            class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 mb-8"
        >
            <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <span aria-hidden="true" class="text-2xl leading-none">🍽️</span>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">
                        {{ __('¡Tienes') }}
                        <span class="font-bold text-base">{{ $barItemsCount }}</span>
                        {{ Str::plural('tapa', $barItemsCount) }} {{ __('disponible') }}{{ $barItemsCount > 1 ? 's' : '' }}
                    </p>
                    <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">
                        @if($tapaConfig->tapas_free)
                            {{ __('Las tapas son gratuitas. Al añadir una bebida te sugeriremos tu tapa.') }}
                        @else
                            {{ __('Precio por tapa:') }}
                            <span class="font-semibold">{{ number_format($tapaConfig->tapa_price ?? 0, 2) }} €</span>
                        @endif
                        &bull; {{ __('Máximo') }} {{ $tapaConfig->max_tapa_variants }} {{ Str::plural('variante', $tapaConfig->max_tapa_variants) }} {{ __('distintas.') }}
                        @if($tapaConfig->extra_tapa_enabled)
                            &bull; {{ __('Tapa extra disponible por') }}
                            <span class="font-semibold">{{ number_format($tapaConfig->extra_tapa_price ?? 0, 2) }} €</span>.
                        @endif
                    </p>
                </div>
            </div>
        </section>
        @endif

        {{-- ── Modal sugerencia de tapa ────────────────────────────── --}}
        <div x-show="$store.cart.showTapaModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
             @click.self="$store.cart.closeTapaModal()"
             @keydown.escape.window="$store.cart.closeTapaModal()"
             aria-modal="true"
             role="dialog"
             aria-label="Elige tu tapa"
             style="display:none">

            <div x-show="$store.cart.showTapaModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-900
                        rounded-t-2xl shadow-2xl px-5 pb-8 pt-4 max-h-[85dvh] overflow-y-auto">

                <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600 mx-auto mb-4"></div>

                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-1">
                    🍽️ {{ __('¿Quieres una tapa?') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4"
                   x-text="'Te quedan ' + ($store.cart.tapaConfig.maxVariants - $store.cart._variantsUsed) + ' variante(s) disponible(s).'">
                </p>

                <ul class="space-y-2 mb-4" aria-label="Tapas disponibles">
                    <template x-for="tapa in $store.cart.tapaProducts" :key="tapa.id">
                        <li>
                            <button type="button"
                                    @click="$store.cart.addTapa(tapa)"
                                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl
                                           bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700
                                           hover:bg-amber-100 dark:hover:bg-amber-800/30 transition-colors
                                           focus:outline-none focus:ring-2 focus:ring-amber-500">
                                <span class="font-medium text-gray-900 dark:text-white" x-text="tapa.name"></span>
                                <span class="text-sm font-semibold text-amber-700 dark:text-amber-300"
                                      x-text="$store.cart.tapaConfig.free ? 'Gratis' : ($store.cart.tapaConfig.tapaPrice.toFixed(2).replace('.', ',') + ' €')">
                                </span>
                            </button>
                        </li>
                    </template>
                </ul>

                {{-- Tapa extra (si está habilitada) --}}
                <template x-if="$store.cart.tapaConfig.extraEnabled">
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mb-4">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                            {{ __('Tapa extra (no consume variante)') }} —
                            <span x-text="$store.cart.tapaConfig.extraPrice.toFixed(2).replace('.', ',') + ' €'"></span>
                        </p>
                        <template x-for="tapa in $store.cart.tapaProducts" :key="'extra-' + tapa.id">
                            <button type="button"
                                    @click="$store.cart.add({
                                        id: tapa.id,
                                        name: tapa.name + ' (extra)',
                                        price: $store.cart.tapaConfig.extraPrice,
                                        destination: 'kitchen',
                                        removable: [],
                                        extras: []
                                    }); $store.cart.closeTapaModal()"
                                    class="w-full mb-2 px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600
                                           text-sm text-left text-gray-700 dark:text-gray-300
                                           hover:border-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-400
                                           transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    x-text="tapa.name + ' (+' + $store.cart.tapaConfig.extraPrice.toFixed(2).replace('.', ',') + ' €)'">
                            </button>
                        </template>
                    </div>
                </template>

                <button type="button"
                        @click="$store.cart.closeTapaModal()"
                        class="w-full py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-gray-400
                               hover:text-gray-700 dark:hover:text-gray-200
                               focus:outline-none focus:underline transition-colors">
                    {{ __('Ahora no') }}
                </button>
            </div>
        </div>

        {{-- ── Footer ──────────────────────────────────────────────── --}}
        <footer class="mt-12 border-t border-gray-200 dark:border-gray-800 py-6 text-center">
            <p class="text-xs text-gray-400 dark:text-gray-600">
                Carta digital generada con
                <span class="font-semibold text-indigo-500 dark:text-indigo-400">Zampa</span>
            </p>
        </footer>

    </div>{{-- /x-data --}}
</body>
</html>
