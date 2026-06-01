<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zampa — Digitaliza tu restaurante</title>
    <meta name="description" content="Zampa digitaliza tu restaurante: carta QR, pedidos, cocina, pagos y chatbot IA. Todo en uno.">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="shortcut icon" href="/favicon.svg">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|playfair-display:400,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Dark mode: apply before paint to avoid flash (same as layouts/app.blade.php) --}}
    <script>
        (function () {
            const saved = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 font-sans antialiased transition-colors duration-200"
      x-data="landing()">

{{-- ==============================
     NAVBAR
============================== --}}
<header class="fixed top-0 left-0 right-0 z-50
               bg-white/90 dark:bg-gray-950/90
               backdrop-blur
               border-b border-gray-100 dark:border-gray-800
               shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="#inicio" class="flex items-center gap-2 group" aria-label="Zampa inicio">
                <x-application-logo class="w-9 h-9 flex-shrink-0"/>
                <span class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                    Zampa
                </span>
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden md:flex items-center gap-8" aria-label="Navegación principal">
                <a href="#inicio"    class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Inicio</a>
                <a href="#funciones" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Funciones</a>
                <a href="#planes"    class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Planes</a>
                <a href="#setup"     class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Config. Total</a>
                <a href="#nosotros"  class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Nosotros</a>
                <a href="#contacto"  class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Contacto</a>
            </nav>

            {{-- Right controls --}}
            <div class="hidden md:flex items-center gap-3">
                {{-- Dark mode toggle --}}
                <button @click="toggleTheme()"
                        :aria-label="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
                        :aria-pressed="isDark.toString()"
                        class="p-2 rounded-lg text-gray-500 dark:text-gray-400
                               hover:bg-gray-100 dark:hover:bg-gray-800
                               focus:outline-none focus:ring-2 focus:ring-amber-500
                               transition-colors">
                    <svg x-show="isDark" x-cloak class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg x-show="!isDark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>

                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Mi panel</a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">Acceder</a>
                @endauth
            </div>

            {{-- Mobile controls --}}
            <div class="md:hidden flex items-center gap-2">
                {{-- Dark mode toggle (mobile) --}}
                <button @click="toggleTheme()"
                        :aria-label="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
                        class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <svg x-show="isDark" x-cloak class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg x-show="!isDark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>
                {{-- Hamburger --}}
                <button @click="mobileOpen = !mobileOpen"
                        class="p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        aria-label="Abrir menú">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileOpen"  stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="mobileOpen" x-cloak
         class="md:hidden border-t border-gray-100 dark:border-gray-800
                bg-white dark:bg-gray-950 px-4 py-4 space-y-3">
        <a href="#inicio"    @click="mobileOpen=false" class="block text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-amber-600 dark:hover:text-amber-400">Inicio</a>
        <a href="#funciones" @click="mobileOpen=false" class="block text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-amber-600 dark:hover:text-amber-400">Funciones</a>
        <a href="#planes"    @click="mobileOpen=false" class="block text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-amber-600 dark:hover:text-amber-400">Planes</a>
        <a href="#setup"     @click="mobileOpen=false" class="block text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-amber-600 dark:hover:text-amber-400">Configuración Total</a>
        <a href="#nosotros"  @click="mobileOpen=false" class="block text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-amber-600 dark:hover:text-amber-400">Nosotros</a>
        <a href="#contacto"  @click="mobileOpen=false" class="block text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-amber-600 dark:hover:text-amber-400">Contacto</a>
        <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex flex-col gap-2">
            @auth
                <a href="{{ route('dashboard') }}" class="block text-center px-4 py-2 bg-amber-600 text-white text-sm font-semibold rounded-lg">Mi panel</a>
            @else
                <a href="{{ route('login') }}" class="block text-center px-4 py-2 bg-amber-600 text-white text-sm font-semibold rounded-lg">Acceder</a>
            @endauth
        </div>
    </div>
</header>

<main id="main-content">

{{-- ==============================
     HERO
============================== --}}
<section id="inicio" class="pt-32 pb-20 landing-hero-gradient overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-16">

            {{-- Text --}}
            <div class="flex-1 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1
                            bg-amber-100 dark:bg-amber-950/60
                            text-amber-800 dark:text-amber-300
                            text-xs font-semibold rounded-full mb-6">
                    <span class="w-2 h-2 bg-amber-500 rounded-full zampi-pulse"></span>
                    Nuevo: Chatbot IA contextualizado a tu carta
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white leading-tight mb-6">
                    Tu restaurante,<br>
                    <span class="text-amber-600 dark:text-amber-400">digitalizado</span><br>
                    en minutos.
                </h1>
                <p class="text-lg sm:text-xl text-gray-600 dark:text-gray-400 mb-8 max-w-xl mx-auto lg:mx-0">
                    Carta QR, pedidos en tiempo real, panel de cocina, pagos con Stripe y un chatbot IA que conoce tu menú. Todo sin complicaciones.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="#contacto"
                       class="inline-flex items-center justify-center px-6 py-3
                              bg-amber-600 hover:bg-amber-700
                              text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all text-base">
                        Contáctanos
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                    <a href="#planes"
                       class="inline-flex items-center justify-center px-6 py-3
                              border-2 border-amber-600 dark:border-amber-500
                              text-amber-700 dark:text-amber-400
                              hover:bg-amber-50 dark:hover:bg-amber-950/40
                              font-semibold rounded-xl transition-all text-base">
                        Ver planes
                    </a>
                </div>
            </div>

            {{-- Zampi mascot --}}
            <div class="flex-1 flex justify-center lg:justify-end">
                <div class="relative">
                    <div class="w-64 h-64 sm:w-80 sm:h-80
                                bg-amber-100 dark:bg-amber-950/40
                                rounded-full flex items-center justify-center shadow-2xl">
                        <div class="zampi-float">
                            <x-application-logo class="w-44 h-44 sm:w-56 sm:h-56 drop-shadow-lg"/>
                        </div>
                    </div>
                    <div class="absolute -top-4 -right-4 bg-white dark:bg-gray-800
                                rounded-2xl shadow-lg px-4 py-2
                                flex items-center gap-2
                                border border-amber-100 dark:border-amber-900/50
                                badge-in badge-in-delay-1">
                        <span class="text-xl">🍽️</span>
                        <div>
                            <p class="text-xs font-semibold text-gray-900 dark:text-white">Pedido recibido</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Mesa 7 · 3 ítems</p>
                        </div>
                    </div>
                    <div class="absolute -bottom-4 -left-4 bg-white dark:bg-gray-800
                                rounded-2xl shadow-lg px-4 py-2
                                flex items-center gap-2
                                border border-green-100 dark:border-green-900/50
                                badge-in badge-in-delay-2">
                        <span class="text-xl">✅</span>
                        <div>
                            <p class="text-xs font-semibold text-gray-900 dark:text-white">Listo para servir</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Cocina · Mesa 4</p>
                        </div>
                    </div>
                    <div class="absolute top-1/2 -left-8 bg-white dark:bg-gray-800
                                rounded-2xl shadow-lg px-3 py-2
                                border border-amber-100 dark:border-amber-900/50
                                badge-in badge-in-delay-3">
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">💬 Zampi dice:</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 max-w-28">"El chuletón lleva gluten en la salsa"</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ==============================
     SOCIAL PROOF
============================== --}}
<section class="py-10 bg-gray-100 dark:bg-gray-900 border-y border-gray-200 dark:border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-center text-sm font-medium text-gray-500 dark:text-gray-400 mb-6">Confiado por restaurantes en toda España</p>
        <div class="flex flex-wrap justify-center items-center gap-8">
            <span class="text-lg font-bold text-gray-500 dark:text-gray-500">La Taberna</span>
            <span class="text-lg font-bold text-gray-500 dark:text-gray-500">El Rincón</span>
            <span class="text-lg font-bold text-gray-500 dark:text-gray-500">Brasería Norte</span>
            <span class="text-lg font-bold text-gray-500 dark:text-gray-500">Café Central</span>
            <span class="text-lg font-bold text-gray-500 dark:text-gray-500">Tapas &amp; Co.</span>
        </div>
    </div>
</section>

{{-- ==============================
     FUNCIONES
============================== --}}
<section id="funciones" class="py-20 bg-white dark:bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">Todo lo que necesita tu restaurante</h2>
            <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                Una plataforma completa que cubre todo el flujo: desde que el cliente escanea el QR hasta que el chef termina el plato.
            </p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @php
            $features = [
                ['icon' => '📱', 'title' => 'Carta Digital QR',        'desc' => 'Cada mesa tiene su propio QR. El cliente escanea, ve la carta en su móvil y pide sin esperar al camarero.'],
                ['icon' => '🛒', 'title' => 'Pedidos en Tiempo Real',  'desc' => 'Los pedidos llegan al panel de cocina y barra al instante. Sin papel, sin confusiones, sin demoras.'],
                ['icon' => '👨‍🍳', 'title' => 'Panel de Cocina',        'desc' => 'Los cocineros ven las comandas en tiempo real, marcan platos como listos y notifican automáticamente al camarero.'],
                ['icon' => '💳', 'title' => 'Pagos con Stripe',        'desc' => 'Solicitud de cuenta desde la mesa, pago en efectivo o tarjeta con propina configurable. Todo en un flujo limpio.'],
                ['icon' => '🤖', 'title' => 'Chatbot IA — Zampi',      'desc' => 'Zampi conoce tu carta al detalle: resuelve dudas de alérgenos, recomienda platos y atiende al cliente 24/7.'],
                ['icon' => '📊', 'title' => 'Dashboard de Ingresos',   'desc' => 'Ingresos por período, mesa que más vende, platos top y horas punta. Toma decisiones con datos reales.'],
                ['icon' => '🗺️', 'title' => 'Mapa Visual de Mesas',    'desc' => 'Arrastra y coloca tus mesas en un plano digital. Soporte multi-planta para establecimientos con varios pisos.'],
                ['icon' => '⚠️', 'title' => 'Gestión de Alérgenos',    'desc' => 'Vincula ingredientes a productos. Zampa calcula los alérgenos automáticamente y los muestra en la carta.'],
                ['icon' => '🍺', 'title' => 'Panel de Barra',          'desc' => 'Las bebidas van directamente a la barra. El barman ve su panel sin mezclas con la cocina.'],
            ];
            @endphp
            @foreach ($features as $f)
            <div class="bg-gray-50 dark:bg-gray-900
                        hover:bg-amber-50 dark:hover:bg-amber-950/30
                        rounded-2xl p-6
                        border border-gray-100 dark:border-gray-800
                        hover:border-amber-200 dark:hover:border-amber-800
                        transition-all group">
                <div class="text-3xl mb-4">{{ $f['icon'] }}</div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-amber-700 dark:group-hover:text-amber-400 transition-colors">{{ $f['title'] }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ $f['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ==============================
     PLANES
============================== --}}
<section id="planes" class="py-20 landing-plans-gradient">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">Planes para cada tipo de negocio</h2>
            <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto mb-8">
                Desde el bar de barrio hasta la cadena de restaurantes. Escala cuando necesites.
            </p>
            <div class="inline-flex items-center bg-gray-100 dark:bg-gray-800 rounded-xl p-1 gap-1">
                <button @click="billing='monthly'"
                        :class="billing==='monthly'
                            ? 'bg-white dark:bg-gray-700 shadow text-gray-900 dark:text-white'
                            : 'text-gray-500 dark:text-gray-400'"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-all">Mensual</button>
                <button @click="billing='yearly'"
                        :class="billing==='yearly'
                            ? 'bg-white dark:bg-gray-700 shadow text-gray-900 dark:text-white'
                            : 'text-gray-500 dark:text-gray-400'"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-all">
                    Anual <span class="ml-1 text-xs text-green-600 dark:text-green-400 font-semibold">−20%</span>
                </button>
            </div>
        </div>

        @if($plans->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">
            @php
            $planFeatures = [
                'Básico'      => ['Carta digital QR', 'Hasta 20 mesas', 'Hasta 10 staff', '1 planta', 'Panel de cocina', 'Chatbot Zampi básico'],
                'Profesional' => ['Todo lo del plan Básico', 'Hasta 50 mesas', 'Hasta 25 staff', 'Hasta 3 plantas', 'Panel de barra', 'Pagos con Stripe', 'Dashboard de ingresos'],
                'Premium'     => ['Todo lo del plan Profesional', 'Mesas ilimitadas', 'Staff ilimitado', 'Plantas ilimitadas', 'Soporte prioritario', 'Configuración personalizada', 'SLA garantizado'],
            ];
            $highlighted = ['Profesional'];
            @endphp
            @foreach ($plans as $plan)
            @php
                $isHighlighted = in_array($plan->name, $highlighted);
                $featureList   = $planFeatures[$plan->name] ?? [];
                $monthlyPrice  = number_format((float) $plan->price_monthly, 0, ',', '.');
                $yearlyPrice   = number_format((float) ($plan->price_yearly ?? $plan->price_monthly * 12 * 0.8), 0, ',', '.');
            @endphp
            <div class="relative rounded-2xl border flex flex-col
                        {{ $isHighlighted
                           ? 'border-amber-500 plan-popular-glow'
                           : 'border-gray-200 dark:border-gray-700 shadow-md dark:shadow-black/30' }}
                        bg-white dark:bg-gray-900 overflow-hidden">
                @if ($isHighlighted)
                <div class="absolute top-0 left-0 right-0 bg-amber-600 text-white text-xs font-bold text-center py-1.5 tracking-wide uppercase">
                    Más popular
                </div>
                @endif
                <div class="p-6 flex-1 flex flex-col {{ $isHighlighted ? 'pt-10' : '' }}">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">{{ $plan->name }}</h3>
                    <div class="mt-4 mb-6">
                        <div x-show="billing==='monthly'">
                            <span class="text-4xl font-extrabold text-gray-900 dark:text-white">{{ $monthlyPrice }}€</span>
                            <span class="text-gray-500 dark:text-gray-400 text-sm">/mes</span>
                        </div>
                        <div x-show="billing==='yearly'" x-cloak>
                            <span class="text-4xl font-extrabold text-gray-900 dark:text-white">{{ $yearlyPrice }}€</span>
                            <span class="text-gray-500 dark:text-gray-400 text-sm">/año</span>
                            <p class="text-xs text-green-600 dark:text-green-400 font-medium mt-1">Ahorra un 20% pagando anual</p>
                        </div>
                    </div>
                    <ul class="space-y-3 flex-1" role="list">
                        @foreach ($featureList as $feat)
                        <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 mt-0.5 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            {{ $feat }}
                        </li>
                        @endforeach
                    </ul>
                    <a href="#contacto"
                       class="block text-center w-full py-3 mt-8 rounded-xl font-semibold text-sm transition-all
                              {{ $isHighlighted
                                 ? 'bg-amber-600 hover:bg-amber-700 text-white shadow-lg'
                                 : 'border-2 border-amber-600 dark:border-amber-500 text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/30' }}">
                        Contratar {{ $plan->name }}
                    </a>
                </div>
                <div class="px-6 pb-4 text-xs text-gray-400 dark:text-gray-500 space-y-1 border-t border-gray-100 dark:border-gray-800 pt-3">
                    @if($plan->max_tables) <p>Hasta {{ $plan->max_tables }} mesas</p> @else <p>Mesas ilimitadas</p> @endif
                    @if($plan->max_staff)  <p>Hasta {{ $plan->max_staff }} staff</p>  @else <p>Staff ilimitado</p>   @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-center text-gray-500 dark:text-gray-400">Los planes se configuran próximamente.</p>
        @endif

        {{-- Custom plan --}}
        <div class="mt-12 bg-white dark:bg-gray-900
                    rounded-2xl border-2 border-dashed border-amber-300 dark:border-amber-700
                    p-8 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white mb-2">¿Necesitas algo personalizado?</p>
            <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-xl mx-auto">
                Cadenas de restaurantes, franquicias o locales con necesidades especiales. Precio a acordar según volumen y características.
            </p>
            <a href="#contacto"
               class="inline-flex items-center px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl shadow transition-all">
                Hablar con nosotros
            </a>
        </div>
    </div>
</section>

{{-- ==============================
     CONFIGURACIÓN TOTAL
============================== --}}
<section id="setup" class="py-20 bg-amber-600 dark:bg-amber-800 text-white overflow-hidden relative">
    <div class="absolute inset-0 opacity-10" aria-hidden="true">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-white rounded-full"></div>
        <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-white rounded-full"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center gap-12">
            <div class="flex-1 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-500 dark:bg-amber-700 rounded-full text-xs font-semibold mb-6">
                    ⚙️ Servicio premium
                </div>
                <h2 class="text-3xl sm:text-4xl font-bold mb-6">Configuración Total<br>del Restaurante</h2>
                <p class="text-amber-100 text-lg mb-4 max-w-lg">
                    Nuestro equipo configura Zampa por ti de principio a fin. Tú solo tienes que abrir las puertas.
                </p>
                <p class="text-amber-200 text-sm mb-8 max-w-lg">
                    Precio variable según el volumen de tu negocio: número de productos, mesas, personal y complejidad del setup. Presupuesto personalizado, sin compromiso.
                </p>
                <a href="#contacto"
                   class="inline-flex items-center px-6 py-3
                          bg-white dark:bg-gray-900
                          text-amber-700 dark:text-amber-400
                          font-bold rounded-xl shadow-lg
                          hover:bg-amber-50 dark:hover:bg-gray-800
                          transition-all text-base">
                    Solicitar presupuesto
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-4">
                @php
                $setupItems = [
                    ['icon' => '🍽️', 'title' => 'Alta de productos',    'desc' => 'Cargamos toda tu carta: nombres, descripciones, precios, fotos e ingredientes con alérgenos.'],
                    ['icon' => '👥', 'title' => 'Alta de staff',         'desc' => 'Creamos las cuentas de cocineros, camareros y gerentes con sus roles y permisos.'],
                    ['icon' => '🗺️', 'title' => 'Mapa de mesas',        'desc' => 'Configuramos el plano de tu local con todas las mesas, zonas y plantas.'],
                    ['icon' => '📲', 'title' => 'QRs listos',            'desc' => 'Generamos los QR de cada mesa, listos para imprimir y pegar.'],
                    ['icon' => '🤖', 'title' => 'Zampi personalizado',   'desc' => 'Configuramos el chatbot con el contexto y tono de voz de tu restaurante.'],
                    ['icon' => '🎓', 'title' => 'Formación del equipo',  'desc' => 'Sesión de formación para que tu equipo use Zampa desde el primer día.'],
                ];
                @endphp
                @foreach ($setupItems as $item)
                <div class="bg-amber-500/40 dark:bg-black/20 rounded-xl p-4 border border-amber-400/30 dark:border-white/10">
                    <p class="text-2xl mb-2">{{ $item['icon'] }}</p>
                    <h3 class="font-semibold text-white mb-1 text-sm">{{ $item['title'] }}</h3>
                    <p class="text-amber-100 text-xs leading-relaxed">{{ $item['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ==============================
     NOSOTROS
============================== --}}
<section id="nosotros" class="py-20 bg-white dark:bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center gap-16">
            <div class="flex-1">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-6">
                    Creamos Zampa porque<br>
                    <span class="text-amber-600 dark:text-amber-400">los restaurantes lo merecen mejor.</span>
                </h2>
                <p class="text-gray-600 dark:text-gray-400 text-lg mb-4 leading-relaxed">
                    Somos un equipo de desarrolladores apasionados por la gastronomía. Vimos restaurantes increíbles perder clientes por colas, pedidos mal anotados y comandas perdidas. Decidimos que ya era hora de cambiar eso.
                </p>
                <p class="text-gray-600 dark:text-gray-400 text-lg mb-8 leading-relaxed">
                    Zampa nació con una idea clara: tecnología potente sin curva de aprendizaje. Que el cocinero no aprenda nada nuevo, que el cliente solo escanee un QR, y que el gerente tenga sus números en tiempo real.
                </p>
                <div class="grid grid-cols-3 gap-6 pt-6 border-t border-gray-100 dark:border-gray-800">
                    <div class="text-center">
                        <p class="text-3xl font-extrabold text-amber-600 dark:text-amber-400">+50</p>
                        <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Restaurantes activos</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-extrabold text-amber-600 dark:text-amber-400">+10k</p>
                        <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Pedidos procesados</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-extrabold text-amber-600 dark:text-amber-400">99.9%</p>
                        <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Uptime garantizado</p>
                    </div>
                </div>
            </div>
            <div class="flex-1 bg-amber-50 dark:bg-gray-900 rounded-3xl p-8 border border-amber-100 dark:border-gray-800">
                <div class="flex items-center gap-4 mb-6">
                    <x-application-logo class="w-14 h-14"/>
                    <div>
                        <p class="font-bold text-gray-900 dark:text-white text-lg">Zampi</p>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Tu asistente IA de restaurante</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-amber-100 dark:border-gray-700">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Cliente:</p>
                        <p class="text-sm text-gray-800 dark:text-gray-200">"¿El chuletón tiene gluten?"</p>
                    </div>
                    <div class="bg-amber-600 dark:bg-amber-700 rounded-2xl p-4 ml-4">
                        <p class="text-xs font-semibold text-amber-100 mb-1">Zampi:</p>
                        <p class="text-sm text-white">"El chuletón no lleva gluten, pero la salsa de la casa sí tiene harina de trigo. ¿Lo prefieres sin salsa? 😊"</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-amber-100 dark:border-gray-700">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Cliente:</p>
                        <p class="text-sm text-gray-800 dark:text-gray-200">"Perfecto, sin salsa. ¿Y algún postre sin lactosa?"</p>
                    </div>
                    <div class="bg-amber-600 dark:bg-amber-700 rounded-2xl p-4 ml-4">
                        <p class="text-xs font-semibold text-amber-100 mb-1">Zampi:</p>
                        <p class="text-sm text-white">"¡Claro! El sorbete de limón y la tarta de Santiago, ambos sin lactosa. 🍋"</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ==============================
     TESTIMONIOS
============================== --}}
<section class="py-20 bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-12">Lo que dicen nuestros restaurantes</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @php
            $testimonials = [
                ['name' => 'Carlos M.', 'role' => 'Gerente · La Taberna', 'text' => 'Desde que usamos Zampa los pedidos perdidos son historia. El panel de cocina es increíblemente intuitivo y el equipo lo aprendió en un día.'],
                ['name' => 'Laura P.',  'role' => 'Dueña · Café Central',  'text' => 'El chatbot Zampi es una pasada. Los clientes preguntan por alérgenos y él responde al instante. Nos ha evitado más de un malentendido serio.'],
                ['name' => 'Marcos T.','role' => 'Chef · Brasería Norte',  'text' => 'Ver las comandas en tiempo real sin papel y poder marcarlas desde la pantalla ha cambiado totalmente el ritmo de la cocina.'],
            ];
            @endphp
            @foreach ($testimonials as $t)
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex mb-4" aria-label="5 estrellas">
                    @for ($i = 0; $i < 5; $i++)
                    <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    @endfor
                </div>
                <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed mb-4">"{{ $t['text'] }}"</p>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $t['name'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t['role'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ==============================
     CONTACTO
============================== --}}
<section id="contacto" class="py-20 bg-white dark:bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-16 items-start">
            <div class="flex-1">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-6">¿Hablamos?</h2>
                <p class="text-gray-600 dark:text-gray-400 text-lg mb-8">
                    Cuéntanos cómo es tu negocio y te explicamos cómo Zampa puede ayudarte. Sin compromiso.
                </p>
                <div class="space-y-5">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-amber-100 dark:bg-amber-950/60 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Email</p>
                            <a href="mailto:hola@zampa.app" class="text-sm text-amber-600 dark:text-amber-400 hover:underline">administracion@zampa.app</a>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-amber-100 dark:bg-amber-950/60 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Horario</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Lunes a viernes · 9:00 – 18:00</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-amber-100 dark:bg-amber-950/60 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Ubicación</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">España · Soporte remoto disponible</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex-1 w-full">
                <form action="mailto:hola@zampa.app" method="GET"
                      class="bg-gray-50 dark:bg-gray-900
                             rounded-2xl p-8
                             border border-gray-100 dark:border-gray-800
                             shadow-sm space-y-5"
                      aria-label="Formulario de contacto">
                    <div>
                        <label for="contact-name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Nombre <span aria-hidden="true">*</span>
                        </label>
                        <input id="contact-name" name="name" type="text" required
                               placeholder="Tu nombre o el del restaurante"
                               class="w-full px-4 py-3 rounded-xl
                                      border border-gray-200 dark:border-gray-700
                                      bg-white dark:bg-gray-800
                                      text-gray-900 dark:text-gray-100
                                      placeholder-gray-400 dark:placeholder-gray-500
                                      focus:border-amber-500 focus:ring-2 focus:ring-amber-200 dark:focus:ring-amber-800
                                      outline-none text-sm transition-all">
                    </div>
                    <div>
                        <label for="contact-email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Email <span aria-hidden="true">*</span>
                        </label>
                        <input id="contact-email" name="email" type="email" required
                               placeholder="tu@email.com"
                               class="w-full px-4 py-3 rounded-xl
                                      border border-gray-200 dark:border-gray-700
                                      bg-white dark:bg-gray-800
                                      text-gray-900 dark:text-gray-100
                                      placeholder-gray-400 dark:placeholder-gray-500
                                      focus:border-amber-500 focus:ring-2 focus:ring-amber-200 dark:focus:ring-amber-800
                                      outline-none text-sm transition-all">
                    </div>
                    <div>
                        <label for="contact-subject" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Asunto</label>
                        <select id="contact-subject" name="subject"
                                class="w-full px-4 py-3 rounded-xl
                                       border border-gray-200 dark:border-gray-700
                                       bg-white dark:bg-gray-800
                                       text-gray-900 dark:text-gray-100
                                       focus:border-amber-500 focus:ring-2 focus:ring-amber-200 dark:focus:ring-amber-800
                                       outline-none text-sm transition-all">
                            <option value="info">Quiero más información</option>
                            <option value="plan">Contratar un plan</option>
                            <option value="setup">Configuración Total del Restaurante</option>
                            <option value="presupuesto">Presupuesto personalizado</option>
                            <option value="soporte">Soporte técnico</option>
                        </select>
                    </div>
                    <div>
                        <label for="contact-message" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Mensaje <span aria-hidden="true">*</span>
                        </label>
                        <textarea id="contact-message" name="body" required rows="4"
                                  placeholder="Cuéntanos sobre tu restaurante: número de mesas, staff, si tienes varias plantas..."
                                  class="w-full px-4 py-3 rounded-xl
                                         border border-gray-200 dark:border-gray-700
                                         bg-white dark:bg-gray-800
                                         text-gray-900 dark:text-gray-100
                                         placeholder-gray-400 dark:placeholder-gray-500
                                         focus:border-amber-500 focus:ring-2 focus:ring-amber-200 dark:focus:ring-amber-800
                                         outline-none text-sm transition-all resize-none"></textarea>
                    </div>
                    <button type="submit"
                            class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all text-sm">
                        Enviar mensaje
                    </button>
                    <p class="text-xs text-gray-400 dark:text-gray-500 text-center">Respondemos en menos de 24 horas.</p>
                </form>
            </div>
        </div>
    </div>
</section>

</main>

{{-- ==============================
     FOOTER
============================== --}}
<footer class="bg-gray-900 dark:bg-black text-gray-400 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between gap-10">
            <div class="max-w-xs">
                <div class="flex items-center gap-2 mb-4">
                    <x-application-logo class="w-8 h-8"/>
                    <span class="text-white font-bold text-lg">Zampa</span>
                </div>
                <p class="text-sm leading-relaxed">
                    Plataforma SaaS para digitalización de bares y restaurantes. Carta QR, cocina, pagos y chatbot IA en un solo lugar.
                </p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-white text-sm font-semibold mb-3">Producto</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#funciones" class="hover:text-amber-400 transition-colors">Funciones</a></li>
                        <li><a href="#planes"    class="hover:text-amber-400 transition-colors">Planes</a></li>
                        <li><a href="#setup"     class="hover:text-amber-400 transition-colors">Config. Total</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white text-sm font-semibold mb-3">Empresa</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#nosotros" class="hover:text-amber-400 transition-colors">Nosotros</a></li>
                        <li><a href="#contacto" class="hover:text-amber-400 transition-colors">Contacto</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white text-sm font-semibold mb-3">Acceso</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('login') }}" class="hover:text-amber-400 transition-colors">Iniciar sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="mt-10 pt-8 border-t border-gray-800 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-center gap-4">
            <p class="text-xs">© {{ date('Y') }} Zampa. Todos los derechos reservados.</p>
            <div class="flex items-center gap-2 text-xs">
                <x-application-logo class="w-5 h-5 opacity-60"/>
                <span>Con cariño de Zampi 🧡</span>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
