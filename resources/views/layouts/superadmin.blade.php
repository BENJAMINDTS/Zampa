<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Zampa') }} — Superadmin</title>
    @vite(['resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full font-sans antialiased">

    {{-- Skip to content --}}
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4
              bg-amber-400 text-slate-900 px-4 py-2 rounded font-semibold z-50">
        Saltar al contenido principal
    </a>

    <div class="flex h-full min-h-screen" x-data="{ sidebarOpen: false }">

        {{-- Mobile sidebar overlay --}}
        <div x-show="sidebarOpen"
             x-cloak
             @click="sidebarOpen = false"
             class="fixed inset-0 z-20 bg-black/60 lg:hidden"
             aria-hidden="true"></div>

        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-30 w-64 flex-shrink-0 bg-slate-900 border-r border-slate-800 flex flex-col
                      transform transition-transform duration-200 ease-in-out
                      lg:static lg:translate-x-0"
               id="superadmin-sidebar"
               aria-label="Barra de navegación superadmin">

            {{-- Logo / Branding --}}
            <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-800">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-amber-400 text-slate-900 font-black text-sm select-none">
                    SA
                </span>
                <div class="leading-tight">
                    <p class="text-white font-bold text-sm">Zampa</p>
                    <p class="text-amber-400 text-xs font-medium uppercase tracking-widest">Superadmin</p>
                </div>
            </div>

            {{-- Navigation --}}
            <nav aria-label="Navegación superadmin" class="flex-1 px-4 py-6 space-y-1">

                <a href="{{ route('superadmin.dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('superadmin.dashboard')
                              ? 'bg-amber-400 text-slate-900'
                              : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Panel principal
                </a>

                <a href="{{ route('superadmin.plans.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('superadmin.plans.*')
                              ? 'bg-amber-400 text-slate-900'
                              : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Planes
                </a>

                <a href="{{ route('superadmin.businesses.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('superadmin.businesses.*')
                              ? 'bg-amber-400 text-slate-900'
                              : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Negocios
                </a>

                <a href="{{ route('superadmin.map') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('superadmin.map')
                              ? 'bg-amber-400 text-slate-900'
                              : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    Mapa
                </a>

            </nav>

            {{-- User info + logout --}}
            <div class="px-4 py-4 border-t border-slate-800">
                <div class="flex items-center gap-3 px-3 py-2 mb-2">
                    <div class="w-8 h-8 rounded-full bg-amber-400 flex items-center justify-center flex-shrink-0">
                        <span class="text-slate-900 font-bold text-xs">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-white text-sm font-medium truncate">{{ Auth::user()->name }}</p>
                        <p class="text-amber-400 text-xs font-medium">Superadmin</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-slate-400
                                   hover:bg-slate-800 hover:text-white transition-colors">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Cerrar sesión
                    </button>
                </form>
            </div>

        </aside>

        {{-- Main content --}}
        <div class="flex-1 flex flex-col min-w-0 bg-slate-950">

            {{-- Top bar --}}
            <header class="bg-slate-900 border-b border-slate-800 px-4 sm:px-6 py-4 flex items-center gap-3 flex-shrink-0">
                {{-- Hamburger (mobile only) --}}
                <button @click="sidebarOpen = !sidebarOpen"
                        :aria-expanded="sidebarOpen.toString()"
                        aria-controls="superadmin-sidebar"
                        aria-label="Abrir menú"
                        class="lg:hidden p-2.5 min-h-[44px] min-w-[44px] flex items-center justify-center
                               rounded-md text-slate-400 hover:bg-slate-800 hover:text-white
                               focus:outline-none focus:ring-2 focus:ring-amber-400 transition-colors">
                    <svg x-show="!sidebarOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="sidebarOpen" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <div class="flex-1 text-white font-semibold text-lg">
                    @yield('header')
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                             bg-amber-400/10 text-amber-400 ring-1 ring-amber-400/30">
                    SUPERADMIN
                </span>
            </header>

            <main id="main-content" class="flex-1 overflow-auto p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>

        </div>

    </div>

    @stack('scripts')
</body>
</html>
