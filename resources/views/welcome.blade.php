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
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|playfair-display:400,600,700,700i&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Dark mode: apply before paint to avoid flash --}}
    <script>
        (function() {
            const saved = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>

<body class="bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 font-sans antialiased"
    x-data="landing()">

    {{-- Skip to content --}}
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4
              bg-amber-600 text-white px-4 py-2 rounded-lg font-medium z-[9999]">
        Saltar al contenido principal
    </a>

    {{-- ==============================
     NAVBAR
    ============================== --}}
    <header :class="scrolled
        ? 'bg-white/95 dark:bg-gray-950/95 backdrop-blur-md border-b border-gray-100 dark:border-gray-800 shadow-sm'
        : 'bg-transparent border-b border-transparent'"
        class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                <a href="#inicio" class="flex items-center gap-2 group" aria-label="Zampa inicio">
                    <x-application-logo class="w-9 h-9 flex-shrink-0" />
                    <span class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                        Zampa
                    </span>
                </a>

                <nav class="hidden md:flex items-center gap-7" aria-label="Navegación principal">
                    <a href="#inicio"         class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Inicio</a>
                    <a href="#como-funciona"  class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Cómo funciona</a>
                    <a href="#funciones"      class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Funciones</a>
                    <a href="#planes"         class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Planes</a>
                    <a href="#nosotros"       class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Nosotros</a>
                    <a href="#contacto"       class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Contacto</a>
                </nav>

                <div class="hidden md:flex items-center gap-3">
                    <button @click="toggleTheme()"
                        :aria-label="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
                        :aria-pressed="isDark.toString()"
                        class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500 transition-colors">
                        <svg x-show="isDark" x-cloak class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg x-show="!isDark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                    @auth
                    <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Mi panel</a>
                    @else
                    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">Acceder</a>
                    @endauth
                </div>

                <div class="md:hidden flex items-center gap-2">
                    <button @click="toggleTheme()"
                        :aria-label="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
                        class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <svg x-show="isDark" x-cloak class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg x-show="!isDark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                    <button @click="mobileOpen = !mobileOpen"
                        :aria-expanded="mobileOpen.toString()"
                        class="p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        aria-label="Abrir menú">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="mobileOpen"  stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div x-show="mobileOpen" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="md:hidden border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-950 px-4 py-4 space-y-1">
            <a href="#inicio"        @click="mobileOpen=false" class="block text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-amber-600 py-2">Inicio</a>
            <a href="#como-funciona" @click="mobileOpen=false" class="block text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-amber-600 py-2">Cómo funciona</a>
            <a href="#funciones"     @click="mobileOpen=false" class="block text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-amber-600 py-2">Funciones</a>
            <a href="#planes"        @click="mobileOpen=false" class="block text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-amber-600 py-2">Planes</a>
            <a href="#nosotros"      @click="mobileOpen=false" class="block text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-amber-600 py-2">Nosotros</a>
            <a href="#contacto"      @click="mobileOpen=false" class="block text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-amber-600 py-2">Contacto</a>
            <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                @auth
                <a href="{{ route('dashboard') }}" class="block text-center px-4 py-2.5 bg-amber-600 text-white text-sm font-semibold rounded-lg">Mi panel</a>
                @else
                <a href="{{ route('login') }}"     class="block text-center px-4 py-2.5 bg-amber-600 text-white text-sm font-semibold rounded-lg">Acceder</a>
                @endauth
            </div>
        </div>
    </header>

    <main id="main-content">

        {{-- ==============================
         HERO
        ============================== --}}
        <section id="inicio" class="pt-28 pb-24 landing-hero-gradient overflow-hidden relative">

            {{-- Decorative blobs --}}
            <div class="hero-blob w-[500px] h-[500px] bg-amber-300/20 dark:bg-amber-900/25 -top-32 -right-32 opacity-70" aria-hidden="true"></div>
            <div class="hero-blob hero-blob-2 w-[380px] h-[380px] bg-amber-500/10 dark:bg-amber-800/20 bottom-0 left-0 opacity-60" aria-hidden="true"></div>
            <div class="hero-blob hero-blob-3 w-[260px] h-[260px] bg-yellow-300/15 dark:bg-yellow-900/15 top-1/2 left-1/3 opacity-50" aria-hidden="true"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-16">

                    {{-- Text --}}
                    <div class="flex-1 text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5
                            bg-amber-100/80 dark:bg-amber-950/60
                            text-amber-800 dark:text-amber-300
                            text-xs font-semibold rounded-full mb-7
                            border border-amber-200 dark:border-amber-800/60
                            backdrop-blur-sm">
                            <span class="w-2 h-2 bg-amber-500 rounded-full zampi-pulse" aria-hidden="true"></span>
                            Nuevo: Chatbot IA contextualizado a tu carta
                        </div>

                        {{-- Headline: Playfair Display for drama --}}
                        <h1 class="font-display text-5xl sm:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white leading-[1.1] tracking-tight mb-6">
                            Tu restaurante,<br>
                            <em class="not-italic text-gradient-animate">digitalizado</em><br>
                            <span class="text-[0.92em]">en minutos.</span>
                        </h1>

                        <p class="text-lg sm:text-xl text-gray-600 dark:text-gray-400 mb-10 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                            Carta QR, pedidos en tiempo real, panel de cocina, pagos con Stripe y un chatbot IA que conoce tu menú. Todo sin complicaciones.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <a href="#contacto"
                                class="inline-flex items-center justify-center gap-2 px-7 py-4
                              bg-amber-600 hover:bg-amber-700 active:bg-amber-800
                              text-white font-semibold rounded-xl
                              shadow-lg shadow-amber-200 dark:shadow-amber-900/50
                              hover:-translate-y-0.5 hover:shadow-xl
                              transition-all duration-200 text-base">
                                Contáctanos
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </a>
                            <a href="#planes"
                                class="inline-flex items-center justify-center px-7 py-4
                              border-2 border-gray-300 dark:border-gray-700
                              text-gray-700 dark:text-gray-300
                              hover:border-amber-500 hover:text-amber-700 dark:hover:border-amber-500 dark:hover:text-amber-400
                              font-semibold rounded-xl transition-all text-base">
                                Ver planes
                            </a>
                        </div>

                        {{-- Trust badges --}}
                        <div class="flex items-center gap-5 mt-10 justify-center lg:justify-start text-xs text-gray-500 dark:text-gray-400">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                Sin tarjeta requerida
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                Setup en 1 día
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                Soporte incluido
                            </span>
                        </div>
                    </div>

                    {{-- Hero visual --}}
                    <div class="flex-1 flex justify-center lg:justify-end">
                        <div class="relative">
                            <div class="w-64 h-64 sm:w-80 sm:h-80
                                bg-gradient-to-br from-amber-100 to-orange-50
                                dark:from-amber-950/50 dark:to-gray-900
                                rounded-full flex items-center justify-center
                                shadow-2xl shadow-amber-100/80 dark:shadow-amber-900/30
                                ring-1 ring-amber-200/60 dark:ring-amber-800/40">
                                <div class="zampi-float">
                                    <x-application-logo class="w-44 h-44 sm:w-56 sm:h-56 drop-shadow-lg" />
                                </div>
                            </div>

                            {{-- Badge: pedido recibido --}}
                            <div class="absolute -top-4 -right-4 bg-white dark:bg-gray-800 rounded-2xl shadow-xl px-3.5 py-2.5 flex items-center gap-2.5 border border-gray-100 dark:border-gray-700 badge-in badge-in-delay-1">
                                <div class="w-8 h-8 bg-amber-100 dark:bg-amber-900/50 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-900 dark:text-white">Pedido recibido</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Mesa 7 · 3 ítems</p>
                                </div>
                            </div>

                            {{-- Badge: listo para servir --}}
                            <div class="absolute -bottom-4 -left-4 bg-white dark:bg-gray-800 rounded-2xl shadow-xl px-3.5 py-2.5 flex items-center gap-2.5 border border-gray-100 dark:border-gray-700 badge-in badge-in-delay-2">
                                <div class="w-8 h-8 bg-green-100 dark:bg-green-900/40 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-900 dark:text-white">Listo para servir</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Cocina · Mesa 4</p>
                                </div>
                            </div>

                            {{-- Badge: Zampi dice --}}
                            <div class="absolute top-1/2 -left-8 sm:-left-12 bg-white dark:bg-gray-800 rounded-2xl shadow-xl px-3 py-2.5 border border-gray-100 dark:border-gray-700 badge-in badge-in-delay-3">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Zampi:</p>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-[8rem]">"El chuletón lleva gluten en la salsa"</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==============================
         SOCIAL PROOF — infinite marquee
        ============================== --}}
        <section class="py-8 bg-gray-50 dark:bg-gray-900 border-y border-gray-200 dark:border-gray-800 overflow-hidden">
            <p class="text-center text-xs font-semibold tracking-widest text-gray-400 dark:text-gray-500 uppercase mb-5">
                Confiado por restaurantes en toda España
            </p>
            <div class="relative overflow-hidden" aria-hidden="true">
                {{-- Fade edges --}}
                <div class="absolute left-0 top-0 bottom-0 w-20 bg-gradient-to-r from-gray-50 dark:from-gray-900 to-transparent z-10 pointer-events-none"></div>
                <div class="absolute right-0 top-0 bottom-0 w-20 bg-gradient-to-l from-gray-50 dark:from-gray-900 to-transparent z-10 pointer-events-none"></div>

                <div class="marquee-inner">
                    @php
                    $brands = ['La Taberna', 'El Rincón', 'Brasería Norte', 'Café Central', 'Tapas &amp; Co.', 'Casa Pepe', 'La Brasserie', 'El Patio'];
                    $all = array_merge($brands, $brands); // duplicate for seamless loop
                    @endphp
                    @foreach ($all as $brand)
                    <span class="flex items-center gap-4 px-8 text-sm font-bold text-gray-400 dark:text-gray-600 whitespace-nowrap">
                        {{ $brand }}
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-300 dark:bg-amber-700" aria-hidden="true"></span>
                    </span>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ==============================
         CÓMO FUNCIONA
        ============================== --}}
        <section id="como-funciona" class="py-24 bg-white dark:bg-gray-950 dot-bg relative overflow-hidden">
            {{-- Big decorative number in background --}}
            <div class="absolute -right-10 top-10 text-[18rem] font-display font-bold text-gray-50 dark:text-gray-900 leading-none pointer-events-none select-none" aria-hidden="true">4</div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16 reveal">
                    <p class="text-xs font-bold tracking-widest text-amber-600 dark:text-amber-400 uppercase mb-3">El flujo completo</p>
                    <h2 class="font-display text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white mb-4">¿Cómo funciona?</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400 max-w-lg mx-auto">
                        De la mesa al pago, completamente digitalizado en cuatro pasos.
                    </p>
                </div>

                @php
                $steps = [
                    ['num' => '01', 'title' => 'Escanea el QR', 'desc' => 'El cliente escanea el QR único de su mesa. Sin app, sin registro. Solo la carta en su móvil.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 9h.008v.008H10.5V12zm0 3h.008v.008H10.5V15zm0 3h.008v.008H10.5V18zm3-6h.008v.008H13.5V12zm0 3h.008v.008H13.5V15zm0 3h.008v.008H13.5V18z" />'],
                    ['num' => '02', 'title' => 'Pide desde el móvil', 'desc' => 'Explora la carta, filtra por alérgenos, consulta a Zampi y lanza el pedido directo a cocina.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />'],
                    ['num' => '03', 'title' => 'Cocina en tiempo real', 'desc' => 'Cocineros y barman reciben la comanda al instante en su panel. Sin papel, sin confusiones.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 00.495-7.467 5.99 5.99 0 00-1.925 3.546 5.974 5.974 0 01-2.133-1.001A3.75 3.75 0 0012 18z" />'],
                    ['num' => '04', 'title' => 'Paga sin esperas', 'desc' => 'El cliente solicita la cuenta desde su móvil. Paga en efectivo o tarjeta con propina configurable.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />'],
                ];
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-4">
                    @foreach ($steps as $i => $step)
                    <div class="reveal reveal-delay-{{ $i + 1 }} relative flex flex-col items-center text-center sm:text-left sm:items-start group">
                        @if ($i < 3)
                        <div class="hidden lg:block absolute top-7 left-[calc(50%+2.5rem)] right-0 h-px bg-gradient-to-r from-amber-300 to-amber-100/0 dark:from-amber-700 dark:to-gray-950" aria-hidden="true"></div>
                        @endif
                        <div class="relative w-14 h-14 rounded-2xl bg-amber-600 flex items-center justify-center mb-5 flex-shrink-0 shadow-lg shadow-amber-200 dark:shadow-amber-900/40 z-10 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">{!! $step['icon'] !!}</svg>
                        </div>
                        <span class="text-xs font-bold tracking-widest text-amber-500 dark:text-amber-400 mb-1 uppercase">Paso {{ $step['num'] }}</span>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $step['title'] }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ $step['desc'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ==============================
         FUNCIONES — Bento grid
        ============================== --}}
        <section id="funciones" class="py-24 bg-gray-50 dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16 reveal">
                    <p class="text-xs font-bold tracking-widest text-amber-600 dark:text-amber-400 uppercase mb-3">Todo incluido</p>
                    <h2 class="font-display text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white mb-4">Lo que hace Zampa</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                        Una plataforma completa que cubre todo el flujo. Del QR al cobro, sin fisuras.
                    </p>
                </div>

                {{-- Bento grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 auto-rows-auto">

                    {{-- ─ Row 1 ─────────────────────────────────────────── --}}

                    {{-- Carta QR: lg:col-span-2 --}}
                    <div class="reveal reveal-delay-1 sm:col-span-1 lg:col-span-2 bg-white dark:bg-gray-950 rounded-2xl p-6 border border-gray-100 dark:border-gray-800 hover:border-amber-200 dark:hover:border-amber-800/50 hover:shadow-lg dark:hover:shadow-black/30 hover:-translate-y-1 transition-all duration-200 group card-shine cursor-default">
                        <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-950/70 group-hover:bg-amber-200 dark:group-hover:bg-amber-900/70 flex items-center justify-center mb-4 transition-colors">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 9h.008v.008H10.5V12zm0 3h.008v.008H10.5V15zm0 3h.008v.008H10.5V18zm3-6h.008v.008H13.5V12zm0 3h.008v.008H13.5V15zm0 3h.008v.008H13.5V18z" /></svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-amber-700 dark:group-hover:text-amber-400 transition-colors">Carta Digital QR</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Cada mesa tiene su propio QR. El cliente escanea, ve la carta en su móvil y pide sin esperar.</p>
                    </div>

                    {{-- Pedidos RT: lg:col-span-2 --}}
                    <div class="reveal reveal-delay-2 sm:col-span-1 lg:col-span-2 bg-white dark:bg-gray-950 rounded-2xl p-6 border border-gray-100 dark:border-gray-800 hover:border-amber-200 dark:hover:border-amber-800/50 hover:shadow-lg dark:hover:shadow-black/30 hover:-translate-y-1 transition-all duration-200 group card-shine cursor-default">
                        <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-950/70 group-hover:bg-amber-200 dark:group-hover:bg-amber-900/70 flex items-center justify-center mb-4 transition-colors">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-amber-700 dark:group-hover:text-amber-400 transition-colors">Pedidos en Tiempo Real</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Los pedidos llegan al panel de cocina y barra al instante. Sin papel, sin demoras.</p>
                    </div>

                    {{-- Panel Cocina: lg:col-span-2 --}}
                    <div class="reveal reveal-delay-3 sm:col-span-1 lg:col-span-2 bg-white dark:bg-gray-950 rounded-2xl p-6 border border-gray-100 dark:border-gray-800 hover:border-amber-200 dark:hover:border-amber-800/50 hover:shadow-lg dark:hover:shadow-black/30 hover:-translate-y-1 transition-all duration-200 group card-shine cursor-default">
                        <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-950/70 group-hover:bg-amber-200 dark:group-hover:bg-amber-900/70 flex items-center justify-center mb-4 transition-colors">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 00.495-7.467 5.99 5.99 0 00-1.925 3.546 5.974 5.974 0 01-2.133-1.001A3.75 3.75 0 0012 18z" /></svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-amber-700 dark:group-hover:text-amber-400 transition-colors">Panel de Cocina</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Comandas en tiempo real, marcar platos como listos, notificación automática al camarero.</p>
                    </div>

                    {{-- ─ Row 2 ─────────────────────────────────────────── --}}

                    {{-- CHATBOT FEATURED: lg:col-span-3, row-span-2 (tall card) --}}
                    <div class="reveal reveal-delay-1 sm:col-span-2 lg:col-span-3 lg:row-span-2 bento-featured rounded-2xl p-6 border hover:shadow-xl dark:hover:shadow-black/40 hover:-translate-y-1 transition-all duration-300 group card-shine cursor-default overflow-hidden relative">
                        {{-- Background glow --}}
                        <div class="absolute -bottom-12 -right-12 w-48 h-48 bg-amber-400/10 rounded-full blur-2xl pointer-events-none" aria-hidden="true"></div>
                        <div class="relative">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-11 h-11 rounded-xl bg-amber-600 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-300">
                                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" /></svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-amber-700 dark:group-hover:text-amber-400 transition-colors">Chatbot IA — Zampi</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Asistente disponible 24/7</p>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-5">Zampi conoce tu carta al detalle: resuelve dudas de alérgenos, recomienda platos y atiende al cliente en cualquier momento.</p>
                            {{-- Mini chat preview --}}
                            <div class="space-y-3 rounded-xl bg-gray-50 dark:bg-gray-900/80 p-4 border border-gray-100 dark:border-gray-800">
                                <div class="flex gap-2">
                                    <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 flex-shrink-0 mt-0.5"></div>
                                    <div class="bg-white dark:bg-gray-800 rounded-xl rounded-tl-none px-3 py-2 text-xs text-gray-700 dark:text-gray-300 shadow-sm border border-gray-100 dark:border-gray-700 max-w-[80%]">
                                        ¿El chuletón tiene gluten?
                                    </div>
                                </div>
                                <div class="flex gap-2 justify-end">
                                    <div class="bg-amber-600 rounded-xl rounded-tr-none px-3 py-2 text-xs text-white max-w-[85%]">
                                        No lleva gluten, pero la salsa sí tiene harina de trigo. ¿Lo prefieres sin salsa?
                                    </div>
                                    <div class="w-6 h-6 rounded-full bg-amber-200 dark:bg-amber-800 flex-shrink-0 mt-0.5 flex items-center justify-center">
                                        <x-application-logo class="w-4 h-4" />
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 flex-shrink-0 mt-0.5"></div>
                                    <div class="bg-white dark:bg-gray-800 rounded-xl rounded-tl-none px-3 py-2 text-xs text-gray-700 dark:text-gray-300 shadow-sm border border-gray-100 dark:border-gray-700">
                                        ¿Y algún postre sin lactosa?
                                    </div>
                                </div>
                                <div class="flex gap-2 justify-end">
                                    <div class="bg-amber-600 rounded-xl rounded-tr-none px-3 py-2 text-xs text-white max-w-[85%]">
                                        Sorbete de limón y tarta de Santiago. Ambos sin lactosa.
                                    </div>
                                    <div class="w-6 h-6 rounded-full bg-amber-200 dark:bg-amber-800 flex-shrink-0 mt-0.5 flex items-center justify-center">
                                        <x-application-logo class="w-4 h-4" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pagos: lg:col-span-3 --}}
                    <div class="reveal reveal-delay-2 sm:col-span-1 lg:col-span-3 bg-white dark:bg-gray-950 rounded-2xl p-6 border border-gray-100 dark:border-gray-800 hover:border-amber-200 dark:hover:border-amber-800/50 hover:shadow-lg dark:hover:shadow-black/30 hover:-translate-y-1 transition-all duration-200 group card-shine cursor-default">
                        <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-950/70 group-hover:bg-amber-200 dark:group-hover:bg-amber-900/70 flex items-center justify-center mb-4 transition-colors">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-amber-700 dark:group-hover:text-amber-400 transition-colors">Pagos con Stripe</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Solicitud de cuenta desde la mesa, efectivo o tarjeta con propina configurable. Flujo limpio.</p>
                    </div>

                    {{-- Dashboard: lg:col-span-3 --}}
                    <div class="reveal reveal-delay-3 sm:col-span-1 lg:col-span-3 bg-white dark:bg-gray-950 rounded-2xl p-6 border border-gray-100 dark:border-gray-800 hover:border-amber-200 dark:hover:border-amber-800/50 hover:shadow-lg dark:hover:shadow-black/30 hover:-translate-y-1 transition-all duration-200 group card-shine cursor-default">
                        <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-950/70 group-hover:bg-amber-200 dark:group-hover:bg-amber-900/70 flex items-center justify-center mb-4 transition-colors">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" /></svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-amber-700 dark:group-hover:text-amber-400 transition-colors">Dashboard de Ingresos</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Ingresos por período, mesa top, platos más pedidos, horas punta. Decisiones con datos reales.</p>
                    </div>

                    {{-- ─ Row 3 ─────────────────────────────────────────── --}}

                    {{-- Mapa: lg:col-span-2 --}}
                    <div class="reveal reveal-delay-1 sm:col-span-1 lg:col-span-2 bg-white dark:bg-gray-950 rounded-2xl p-6 border border-gray-100 dark:border-gray-800 hover:border-amber-200 dark:hover:border-amber-800/50 hover:shadow-lg dark:hover:shadow-black/30 hover:-translate-y-1 transition-all duration-200 group card-shine cursor-default">
                        <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-950/70 group-hover:bg-amber-200 dark:group-hover:bg-amber-900/70 flex items-center justify-center mb-4 transition-colors">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" /></svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-amber-700 dark:group-hover:text-amber-400 transition-colors">Mapa Visual de Mesas</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Arrastra y coloca mesas en un plano digital. Soporte multi-planta incluido.</p>
                    </div>

                    {{-- Alérgenos: lg:col-span-2 --}}
                    <div class="reveal reveal-delay-2 sm:col-span-1 lg:col-span-2 bg-white dark:bg-gray-950 rounded-2xl p-6 border border-gray-100 dark:border-gray-800 hover:border-amber-200 dark:hover:border-amber-800/50 hover:shadow-lg dark:hover:shadow-black/30 hover:-translate-y-1 transition-all duration-200 group card-shine cursor-default">
                        <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-950/70 group-hover:bg-amber-200 dark:group-hover:bg-amber-900/70 flex items-center justify-center mb-4 transition-colors">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-amber-700 dark:group-hover:text-amber-400 transition-colors">Gestión de Alérgenos</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Zampa calcula los alérgenos automáticamente a partir de los ingredientes. Mostrados en la carta.</p>
                    </div>

                    {{-- Barra: lg:col-span-2 --}}
                    <div class="reveal reveal-delay-3 sm:col-span-2 lg:col-span-2 bg-white dark:bg-gray-950 rounded-2xl p-6 border border-gray-100 dark:border-gray-800 hover:border-amber-200 dark:hover:border-amber-800/50 hover:shadow-lg dark:hover:shadow-black/30 hover:-translate-y-1 transition-all duration-200 group card-shine cursor-default">
                        <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-950/70 group-hover:bg-amber-200 dark:group-hover:bg-amber-900/70 flex items-center justify-center mb-4 transition-colors">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" /></svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-amber-700 dark:group-hover:text-amber-400 transition-colors">Panel de Barra</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Las bebidas van directamente a la barra. El barman ve su panel separado de cocina.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==============================
         PLANES
        ============================== --}}
        <section id="planes" class="py-24 landing-plans-gradient">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-10 reveal">
                    <p class="text-xs font-bold tracking-widest text-amber-600 dark:text-amber-400 uppercase mb-3">Precios</p>
                    <h2 class="font-display text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white mb-4">Planes para cada negocio</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto mb-8">
                        Desde el bar de barrio hasta la cadena de restaurantes. Escala cuando necesites.
                    </p>
                    <div class="inline-flex items-center bg-gray-100 dark:bg-gray-800 rounded-xl p-1 gap-1">
                        <button @click="billing='monthly'"
                            :class="billing==='monthly' ? 'bg-white dark:bg-gray-700 shadow text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400'"
                            class="px-4 py-2 text-sm font-medium rounded-lg transition-all">Mensual</button>
                        <button @click="billing='yearly'"
                            :class="billing==='yearly' ? 'bg-white dark:bg-gray-700 shadow text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400'"
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
                    <div class="reveal reveal-delay-{{ $loop->index + 1 }} relative rounded-2xl border flex flex-col
                        {{ $isHighlighted ? 'border-amber-500 plan-popular-glow scale-[1.02]' : 'border-gray-200 dark:border-gray-700 shadow-md dark:shadow-black/30' }}
                        bg-white dark:bg-gray-900 overflow-hidden card-shine">
                        @if ($isHighlighted)
                        <div class="absolute top-0 left-0 right-0 bg-gradient-to-r from-amber-600 to-amber-500 text-white text-xs font-bold text-center py-1.5 tracking-widest uppercase">
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
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $feat }}
                                </li>
                                @endforeach
                            </ul>
                            <a href="#contacto"
                                class="block text-center w-full py-3 mt-8 rounded-xl font-semibold text-sm transition-all
                              {{ $isHighlighted ? 'bg-amber-600 hover:bg-amber-700 text-white shadow-lg' : 'border-2 border-amber-600 dark:border-amber-500 text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/30' }}">
                                Contratar {{ $plan->name }}
                            </a>
                        </div>
                        <div class="px-6 pb-4 text-xs text-gray-400 dark:text-gray-500 space-y-1 border-t border-gray-100 dark:border-gray-800 pt-3">
                            @if($plan->max_tables) <p>Hasta {{ $plan->max_tables }} mesas</p> @else <p>Mesas ilimitadas</p> @endif
                            @if($plan->max_staff)  <p>Hasta {{ $plan->max_staff }} staff</p>  @else <p>Staff ilimitado</p>  @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-center text-gray-500 dark:text-gray-400">Los planes se configuran próximamente.</p>
                @endif

                <div class="mt-12 reveal bg-white dark:bg-gray-900 rounded-2xl border-2 border-dashed border-amber-300 dark:border-amber-700 p-8 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mb-2">¿Necesitas algo personalizado?</p>
                    <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-xl mx-auto">
                        Cadenas de restaurantes, franquicias o locales con necesidades especiales. Precio sin compromiso.
                    </p>
                    <a href="#contacto" class="inline-flex items-center px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl shadow transition-all">
                        Hablar con nosotros
                    </a>
                </div>
            </div>
        </section>

        {{-- ==============================
         CONFIGURACIÓN TOTAL
        ============================== --}}
        <section id="setup" class="py-24 bg-amber-600 dark:bg-amber-800 text-white overflow-hidden relative">
            <div class="absolute inset-0 opacity-10 pointer-events-none" aria-hidden="true">
                <div class="absolute -top-24 -right-24 w-96 h-96 bg-white rounded-full"></div>
                <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-white rounded-full"></div>
            </div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row items-center gap-12">
                    <div class="flex-1 text-center lg:text-left reveal">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-500 dark:bg-amber-700 rounded-full text-xs font-semibold mb-6 border border-amber-400/50">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Servicio premium
                        </div>
                        <h2 class="font-display text-3xl sm:text-4xl font-bold mb-6">Configuración Total<br>del Restaurante</h2>
                        <p class="text-amber-100 text-lg mb-4 max-w-lg">
                            Nuestro equipo configura Zampa por ti de principio a fin. Tú solo tienes que abrir las puertas.
                        </p>
                        <p class="text-amber-200 text-sm mb-8 max-w-lg">
                            Precio variable según el volumen de tu negocio. Presupuesto personalizado, sin compromiso.
                        </p>
                        <a href="#contacto"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-white dark:bg-gray-900 text-amber-700 dark:text-amber-400 font-bold rounded-xl shadow-lg hover:bg-amber-50 dark:hover:bg-gray-800 transition-all text-base">
                            Solicitar presupuesto
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                        </a>
                    </div>

                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @php
                        $setupItems = [
                            ['title' => 'Alta de productos',   'desc' => 'Cargamos toda tu carta: nombres, descripciones, precios, fotos e ingredientes con alérgenos.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />'],
                            ['title' => 'Alta de staff',       'desc' => 'Creamos las cuentas de cocineros, camareros y gerentes con sus roles y permisos.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />'],
                            ['title' => 'Mapa de mesas',       'desc' => 'Configuramos el plano de tu local con todas las mesas, zonas y plantas.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />'],
                            ['title' => 'QRs listos',          'desc' => 'Generamos los QR de cada mesa, listos para imprimir y pegar.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 9h.008v.008H10.5V12zm0 3h.008v.008H10.5V15zm0 3h.008v.008H10.5V18zm3-6h.008v.008H13.5V12zm0 3h.008v.008H13.5V15zm0 3h.008v.008H13.5V18z" />'],
                            ['title' => 'Zampi personalizado',  'desc' => 'Configuramos el chatbot con el contexto y tono de voz de tu restaurante.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25zm.75-12h9v9h-9v-9z" />'],
                            ['title' => 'Formación del equipo', 'desc' => 'Sesión de formación para que tu equipo use Zampa desde el primer día.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />'],
                        ];
                        @endphp
                        @foreach ($setupItems as $i => $item)
                        <div class="reveal reveal-delay-{{ $i + 1 }} bg-amber-500/30 dark:bg-black/20 rounded-xl p-4 border border-amber-400/30 dark:border-white/10 hover:bg-amber-500/40 transition-colors">
                            <div class="w-9 h-9 bg-amber-400/40 rounded-lg flex items-center justify-center mb-3">
                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">{!! $item['icon'] !!}</svg>
                            </div>
                            <h3 class="font-semibold text-white mb-1 text-sm">{{ $item['title'] }}</h3>
                            <p class="text-amber-100 text-xs leading-relaxed">{{ $item['desc'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- ==============================
         NOSOTROS — with counter animation
        ============================== --}}
        <section id="nosotros" class="py-24 bg-white dark:bg-gray-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row items-center gap-16">
                    <div class="flex-1 reveal">
                        <p class="text-xs font-bold tracking-widest text-amber-600 dark:text-amber-400 uppercase mb-3">El equipo</p>
                        <h2 class="font-display text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-6">
                            Creamos Zampa porque<br>
                            <span class="text-amber-600 dark:text-amber-400">los restaurantes merecen lo mejor.</span>
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 text-lg mb-4 leading-relaxed">
                            Somos un equipo de desarrolladores apasionados por la gastronomía. Vimos restaurantes increíbles perder clientes por colas, pedidos mal anotados y comandas perdidas.
                        </p>
                        <p class="text-gray-600 dark:text-gray-400 text-lg mb-8 leading-relaxed">
                            Zampa nació con una idea clara: tecnología potente sin curva de aprendizaje. Que el cocinero no aprenda nada nuevo, que el cliente solo escanee un QR.
                        </p>

                        {{-- Stats with counter animation --}}
                        <div x-data="statsCounter()" class="grid grid-cols-3 gap-6 pt-6 border-t border-gray-100 dark:border-gray-800">
                            <div class="text-center">
                                <p class="text-3xl font-extrabold text-amber-600 dark:text-amber-400 font-display tabular-nums"
                                    data-count-to="50" data-prefix="+" aria-label="Más de 50 restaurantes activos">
                                    +50
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Restaurantes activos</p>
                            </div>
                            <div class="text-center">
                                <p class="text-3xl font-extrabold text-amber-600 dark:text-amber-400 font-display tabular-nums"
                                    data-count-to="10" data-prefix="+" data-suffix="k" aria-label="Más de 10.000 pedidos procesados">
                                    +10k
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Pedidos procesados</p>
                            </div>
                            <div class="text-center">
                                <p class="text-3xl font-extrabold text-amber-600 dark:text-amber-400 font-display tabular-nums"
                                    data-count-to="99.9" data-suffix="%" data-decimals="1" aria-label="99.9% de uptime garantizado">
                                    99.9%
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Uptime garantizado</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 reveal reveal-delay-2 bg-amber-50 dark:bg-gray-900 rounded-3xl p-8 border border-amber-100 dark:border-gray-800">
                        <div class="flex items-center gap-4 mb-6">
                            <x-application-logo class="w-14 h-14" />
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
                                <p class="text-sm text-white">"El chuletón no lleva gluten, pero la salsa sí tiene harina de trigo. ¿Lo prefieres sin salsa?"</p>
                            </div>
                            <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-amber-100 dark:border-gray-700">
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Cliente:</p>
                                <p class="text-sm text-gray-800 dark:text-gray-200">"Perfecto. ¿Y algún postre sin lactosa?"</p>
                            </div>
                            <div class="bg-amber-600 dark:bg-amber-700 rounded-2xl p-4 ml-4">
                                <p class="text-xs font-semibold text-amber-100 mb-1">Zampi:</p>
                                <p class="text-sm text-white">"El sorbete de limón y la tarta de Santiago, ambos sin lactosa."</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==============================
         TESTIMONIOS
        ============================== --}}
        <section class="py-24 bg-gray-50 dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12 reveal">
                    <p class="text-xs font-bold tracking-widest text-amber-600 dark:text-amber-400 uppercase mb-3">Testimonios</p>
                    <h2 class="font-display text-4xl font-bold text-gray-900 dark:text-white">Lo que dicen nuestros restaurantes</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @php
                    $testimonials = [
                        ['name' => 'Carlos M.', 'initials' => 'C', 'color' => 'bg-amber-500',  'role' => 'Gerente · La Taberna',  'text' => 'Desde que usamos Zampa los pedidos perdidos son historia. El panel de cocina es increíblemente intuitivo y el equipo lo aprendió en un día.'],
                        ['name' => 'Laura P.',  'initials' => 'L', 'color' => 'bg-blue-500',   'role' => 'Dueña · Café Central',   'text' => 'El chatbot Zampi es una pasada. Los clientes preguntan por alérgenos y él responde al instante. Nos ha evitado más de un malentendido.'],
                        ['name' => 'Marcos T.', 'initials' => 'M', 'color' => 'bg-green-500',  'role' => 'Chef · Brasería Norte',  'text' => 'Ver las comandas en tiempo real sin papel y poder marcarlas desde la pantalla ha cambiado totalmente el ritmo de la cocina.'],
                    ];
                    @endphp
                    @foreach ($testimonials as $i => $t)
                    <div class="reveal reveal-delay-{{ $i + 1 }} bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col hover:-translate-y-1 hover:shadow-md transition-all duration-200">
                        <div class="flex mb-4" aria-label="5 estrellas">
                            @for ($s = 0; $s < 5; $s++)
                            <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            @endfor
                        </div>
                        <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed mb-6 flex-1">"{{ $t['text'] }}"</p>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full {{ $t['color'] }} flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-sm font-bold" aria-hidden="true">{{ $t['initials'] }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $t['name'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t['role'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ==============================
         CTA FINAL
        ============================== --}}
        <section class="py-24 bg-gray-900 dark:bg-black relative overflow-hidden">
            <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
                <div class="absolute top-0 left-1/4 w-96 h-96 bg-amber-600/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 right-1/4 w-64 h-64 bg-amber-500/8 rounded-full blur-2xl"></div>
            </div>
            <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center reveal">
                <div class="w-16 h-16 bg-amber-600/20 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                </div>
                <h2 class="font-display text-4xl sm:text-5xl font-bold text-white mb-4">
                    ¿Listo para digitalizar<br>tu restaurante?
                </h2>
                <p class="text-gray-400 mb-10 text-lg max-w-xl mx-auto">
                    Empieza hoy. Sin permanencias, sin complicaciones. Tu equipo funcionando al 100% desde el primer día.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#contacto"
                        class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-amber-600 hover:bg-amber-500 active:bg-amber-700 text-white font-bold rounded-xl shadow-lg shadow-amber-900/30 hover:-translate-y-0.5 transition-all text-base">
                        Empezar ahora
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                    </a>
                    <a href="#planes" class="inline-flex items-center justify-center px-8 py-4 border-2 border-gray-600 hover:border-gray-400 text-gray-300 hover:text-white font-semibold rounded-xl transition-all text-base">
                        Ver planes
                    </a>
                </div>
            </div>
        </section>

        {{-- ==============================
         CONTACTO
        ============================== --}}
        <section id="contacto" class="py-24 bg-white dark:bg-gray-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row gap-16 items-start">
                    <div class="flex-1 reveal">
                        <p class="text-xs font-bold tracking-widest text-amber-600 dark:text-amber-400 uppercase mb-3">Contacto</p>
                        <h2 class="font-display text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white mb-6">¿Hablamos?</h2>
                        <p class="text-gray-600 dark:text-gray-400 text-lg mb-8">
                            Cuéntanos cómo es tu negocio y te explicamos cómo Zampa puede ayudarte. Sin compromiso.
                        </p>
                        <div class="space-y-5">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-950/60 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Email</p>
                                    <a href="mailto:administracion@zampa.app" class="text-sm text-amber-600 dark:text-amber-400 hover:underline">administracion@zampa.app</a>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-950/60 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Horario</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Lunes a viernes · 9:00 – 18:00</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-950/60 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Ubicación</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">España · Soporte remoto disponible</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 w-full reveal reveal-delay-2">
                        <form action="mailto:administracion@zampa.app" method="GET"
                            class="bg-gray-50 dark:bg-gray-900 rounded-2xl p-8 border border-gray-100 dark:border-gray-800 shadow-sm space-y-5"
                            aria-label="Formulario de contacto">
                            <div>
                                <label for="contact-name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Nombre <span class="text-red-500" aria-hidden="true">*</span>
                                </label>
                                <input id="contact-name" name="name" type="text" required aria-required="true"
                                    placeholder="Tu nombre o el del restaurante"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 dark:focus:ring-amber-800 outline-none text-sm transition-all">
                            </div>
                            <div>
                                <label for="contact-email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Email <span class="text-red-500" aria-hidden="true">*</span>
                                </label>
                                <input id="contact-email" name="email" type="email" required aria-required="true"
                                    placeholder="tu@email.com"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 dark:focus:ring-amber-800 outline-none text-sm transition-all">
                            </div>
                            <div>
                                <label for="contact-subject" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Asunto</label>
                                <select id="contact-subject" name="subject"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 dark:focus:ring-amber-800 outline-none text-sm transition-all">
                                    <option value="info">Quiero más información</option>
                                    <option value="plan">Contratar un plan</option>
                                    <option value="setup">Configuración Total del Restaurante</option>
                                    <option value="presupuesto">Presupuesto personalizado</option>
                                    <option value="soporte">Soporte técnico</option>
                                </select>
                            </div>
                            <div>
                                <label for="contact-message" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Mensaje <span class="text-red-500" aria-hidden="true">*</span>
                                </label>
                                <textarea id="contact-message" name="body" required aria-required="true" rows="4"
                                    placeholder="Cuéntanos sobre tu restaurante: número de mesas, staff, si tienes varias plantas..."
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 dark:focus:ring-amber-800 outline-none text-sm transition-all resize-none"></textarea>
                            </div>
                            <button type="submit"
                                class="w-full py-3.5 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-semibold rounded-xl shadow-md shadow-amber-100 dark:shadow-amber-900/20 hover:shadow-lg hover:-translate-y-0.5 transition-all text-sm cursor-pointer">
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
    <footer class="bg-gray-900 dark:bg-black text-gray-400 py-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between gap-10">
                <div class="max-w-xs">
                    <div class="flex items-center gap-2 mb-4">
                        <x-application-logo class="w-8 h-8" />
                        <span class="text-white font-bold text-lg font-display">Zampa</span>
                    </div>
                    <p class="text-sm leading-relaxed">
                        Plataforma SaaS para digitalización de bares y restaurantes. Carta QR, cocina, pagos y chatbot IA.
                    </p>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-white text-sm font-semibold mb-3">Producto</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#como-funciona" class="hover:text-amber-400 transition-colors">Cómo funciona</a></li>
                            <li><a href="#funciones"     class="hover:text-amber-400 transition-colors">Funciones</a></li>
                            <li><a href="#planes"        class="hover:text-amber-400 transition-colors">Planes</a></li>
                            <li><a href="#setup"         class="hover:text-amber-400 transition-colors">Config. Total</a></li>
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
            <div class="mt-10 pt-8 border-t border-gray-800 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-xs">© {{ date('Y') }} Zampa. Todos los derechos reservados.</p>
                <div class="flex items-center gap-2 text-xs">
                    <x-application-logo class="w-5 h-5 opacity-50" />
                    <span>Hecho con cariño por el equipo Zampa</span>
                </div>
            </div>
        </div>
    </footer>

</body>

</html>
