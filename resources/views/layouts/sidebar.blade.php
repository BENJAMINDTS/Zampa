{{-- @author SebastianBCF --}}
@php
    $cartaActive   = request()->routeIs('categories.*', 'ingredients.*', 'products.*', 'daily-menus.*');
    $localActive   = request()->routeIs('tables.*', 'zones.*', 'staff.*');
    $negocioActive = request()->routeIs('tapas.*', 'negocio.*', 'manager.*', 'ticket-config.*', 'negocio.menu-style.*');
    $cocinaActive  = request()->routeIs('kitchen.*');
    $barraActive   = request()->routeIs('bar.*');
@endphp

<aside
    id="sidebar"
    class="fixed inset-y-0 left-0 z-30 w-64 flex flex-col flex-shrink-0
           bg-white dark:bg-gray-900
           border-r border-gray-200 dark:border-gray-800
           transition-transform duration-200 ease-in-out
           -translate-x-full
           lg:static lg:inset-auto lg:z-auto lg:!translate-x-0"
    :class="sidebarOpen ? '!translate-x-0' : ''"
    aria-label="Navegación principal">

    {{-- Logo --}}
    <div class="flex items-center h-16 px-5 border-b border-gray-200 dark:border-gray-800 flex-shrink-0">
        <a href="{{ route(Auth::user()->homeRoute()) }}"
           aria-label="Ir a inicio"
           class="flex items-center gap-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-md">
            <x-application-logo class="h-7 w-auto fill-current text-indigo-600 dark:text-indigo-400" />
            <span class="font-bold text-lg tracking-tight text-gray-900 dark:text-white">Zampa</span>
        </a>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5"
         aria-label="Menú principal"
         x-data="{
             cartaOpen: JSON.parse(localStorage.getItem('nav_carta') ?? 'true'),
             localOpen: JSON.parse(localStorage.getItem('nav_local') ?? 'true'),
             negocioOpen: JSON.parse(localStorage.getItem('nav_negocio') ?? 'true'),
             toggleCarta()  { this.cartaOpen  = !this.cartaOpen;  localStorage.setItem('nav_carta',   this.cartaOpen); },
             toggleLocal()  { this.localOpen  = !this.localOpen;  localStorage.setItem('nav_local',   this.localOpen); },
             toggleNegocio(){ this.negocioOpen= !this.negocioOpen;localStorage.setItem('nav_negocio', this.negocioOpen); },
         }">

        @if(Auth::user()->role === 'admin')

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
           aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                  {{ request()->routeIs('dashboard')
                     ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300'
                     : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
            <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        {{-- Carta --}}
        <div>
            <button @click="toggleCarta()"
                    :aria-expanded="cartaOpen.toString()"
                    aria-controls="nav-carta"
                    class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                           {{ $cartaActive
                              ? 'text-indigo-700 dark:text-indigo-300'
                              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100' }}">
                <span class="flex items-center gap-3">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Carta
                </span>
                <svg :class="cartaOpen ? 'rotate-90' : ''"
                     class="h-4 w-4 transition-transform flex-shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div id="nav-carta" x-show="cartaOpen" x-cloak class="mt-0.5 ml-7 pl-3 border-l border-gray-200 dark:border-gray-700 space-y-0.5">
                <a href="{{ route('categories.index') }}"
                   aria-current="{{ request()->routeIs('categories.*') ? 'page' : 'false' }}"
                   class="flex items-center px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('categories.*')
                             ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium'
                             : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    Categorías
                </a>
                <a href="{{ route('ingredients.index') }}"
                   aria-current="{{ request()->routeIs('ingredients.*') ? 'page' : 'false' }}"
                   class="flex items-center px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('ingredients.*')
                             ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium'
                             : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    Ingredientes
                </a>
                <a href="{{ route('products.index') }}"
                   aria-current="{{ request()->routeIs('products.*') ? 'page' : 'false' }}"
                   class="flex items-center px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('products.*')
                             ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium'
                             : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    Productos
                </a>
                <a href="{{ route('daily-menus.index') }}"
                   aria-current="{{ request()->routeIs('daily-menus.*') ? 'page' : 'false' }}"
                   class="flex items-center px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('daily-menus.*')
                             ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium'
                             : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    Menú del Día
                </a>
            </div>
        </div>

        {{-- Local --}}
        <div>
            <button @click="toggleLocal()"
                    :aria-expanded="localOpen.toString()"
                    aria-controls="nav-local"
                    class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                           {{ $localActive
                              ? 'text-indigo-700 dark:text-indigo-300'
                              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100' }}">
                <span class="flex items-center gap-3">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Local
                </span>
                <svg :class="localOpen ? 'rotate-90' : ''"
                     class="h-4 w-4 transition-transform flex-shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div id="nav-local" x-show="localOpen" x-cloak class="mt-0.5 ml-7 pl-3 border-l border-gray-200 dark:border-gray-700 space-y-0.5">
                <a href="{{ route('tables.map') }}"
                   aria-current="{{ request()->routeIs('tables.*', 'zones.*') ? 'page' : 'false' }}"
                   class="flex items-center px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('tables.*', 'zones.*')
                             ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium'
                             : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    Mapa de mesas
                </a>
                <a href="{{ route('staff.index') }}"
                   aria-current="{{ request()->routeIs('staff.*') ? 'page' : 'false' }}"
                   class="flex items-center px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('staff.*')
                             ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium'
                             : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    Mi equipo
                </a>
            </div>
        </div>

        {{-- Negocio --}}
        <div>
            <button @click="toggleNegocio()"
                    :aria-expanded="negocioOpen.toString()"
                    aria-controls="nav-negocio"
                    class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                           {{ $negocioActive
                              ? 'text-indigo-700 dark:text-indigo-300'
                              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100' }}">
                <span class="flex items-center gap-3">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Negocio
                </span>
                <svg :class="negocioOpen ? 'rotate-90' : ''"
                     class="h-4 w-4 transition-transform flex-shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div id="nav-negocio" x-show="negocioOpen" x-cloak class="mt-0.5 ml-7 pl-3 border-l border-gray-200 dark:border-gray-700 space-y-0.5">
                <a href="{{ route('negocio.config.edit') }}"
                   aria-current="{{ request()->routeIs('negocio.*', 'tapas.*') ? 'page' : 'false' }}"
                   class="flex items-center px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('negocio.*', 'tapas.*')
                             ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium'
                             : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    Configuración
                </a>
                <a href="{{ route('manager.income') }}"
                   aria-current="{{ request()->routeIs('manager.*') ? 'page' : 'false' }}"
                   class="flex items-center px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('manager.*')
                             ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium'
                             : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    Ingresos
                </a>
                <a href="{{ route('ticket-config.edit') }}"
                   aria-current="{{ request()->routeIs('ticket-config.*') ? 'page' : 'false' }}"
                   class="flex items-center px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('ticket-config.*')
                             ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium'
                             : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    Ticket PDF
                </a>
                <a href="{{ route('negocio.menu-style.edit') }}"
                   aria-current="{{ request()->routeIs('negocio.menu-style.*') ? 'page' : 'false' }}"
                   class="flex items-center px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('negocio.menu-style.*')
                             ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium'
                             : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    Carta digital
                </a>
            </div>
        </div>

        @endif

        {{-- Divider before service panels --}}
        @if(Auth::user()->canAccessKitchen() || Auth::user()->canAccessBar())
        <div class="pt-2">
            <p class="px-3 mb-1 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                Servicio
            </p>
        @endif

        {{-- Cocina --}}
        @if(Auth::user()->canAccessKitchen())
          @if(Auth::user()->role === 'admin')
            {{-- Admin: grupo desplegable con sub-items --}}
            <div x-data="{
                cocinaOpen: JSON.parse(localStorage.getItem('nav_cocina') ?? 'true'),
                toggle() { this.cocinaOpen = !this.cocinaOpen; localStorage.setItem('nav_cocina', JSON.stringify(this.cocinaOpen)); }
            }">
                <button @click="toggle()"
                        :aria-expanded="cocinaOpen.toString()"
                        aria-controls="nav-cocina"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                               {{ $cocinaActive
                                  ? 'text-red-700 dark:text-red-300'
                                  : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100' }}">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        Cocina
                    </span>
                    <svg :class="cocinaOpen ? 'rotate-90' : ''"
                         class="h-4 w-4 transition-transform flex-shrink-0"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div id="nav-cocina" x-show="cocinaOpen" x-cloak class="mt-0.5 ml-7 pl-3 border-l border-gray-200 dark:border-gray-700 space-y-0.5">
                    <span x-data="kitchenBadge('{{ route('kitchen.badge') }}')" x-init="init()">
                        <a href="{{ route('kitchen.index') }}"
                           aria-current="{{ request()->routeIs('kitchen.index') ? 'page' : 'false' }}"
                           class="flex items-center justify-between px-3 py-1.5 rounded-md text-sm transition-colors
                                  {{ request()->routeIs('kitchen.index')
                                     ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 font-medium'
                                     : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200' }}">
                            Comandas
                            <span x-show="count > 0" x-cloak x-text="count"
                                  class="inline-flex items-center justify-center min-w-[1.25rem] h-4 px-1 text-xs font-bold text-white bg-red-500 rounded-full"
                                  :aria-label="count + ' pedidos nuevos'"></span>
                        </a>
                    </span>
                    <a href="{{ route('kitchen.availability') }}"
                       aria-current="{{ request()->routeIs('kitchen.availability') ? 'page' : 'false' }}"
                       class="flex items-center px-3 py-1.5 rounded-md text-sm transition-colors
                              {{ request()->routeIs('kitchen.availability')
                                 ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 font-medium'
                                 : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200' }}">
                        Disponibilidad
                    </a>
                </div>
            </div>
          @else
            {{-- Staff de cocina: dos links planos --}}
            <span x-data="kitchenBadge('{{ route('kitchen.badge') }}')" x-init="init()" class="block">
                <a href="{{ route('kitchen.index') }}"
                   aria-current="{{ request()->routeIs('kitchen.index') ? 'page' : 'false' }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('kitchen.index')
                             ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300'
                             : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    Cocina
                    <span x-show="count > 0" x-cloak x-text="count"
                          class="ml-auto inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 text-xs font-bold text-white bg-red-500 rounded-full"
                          :aria-label="count + ' pedidos nuevos sin atender'"></span>
                </a>
            </span>
            <a href="{{ route('kitchen.availability') }}"
               aria-current="{{ request()->routeIs('kitchen.availability') ? 'page' : 'false' }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('kitchen.availability')
                         ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300'
                         : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
                Disponibilidad
            </a>
          @endif
        @endif

        {{-- Barra --}}
        @if(Auth::user()->canAccessBar())
          @if(Auth::user()->role === 'admin')
            {{-- Admin: grupo desplegable con sub-items --}}
            <div x-data="{
                barraOpen: JSON.parse(localStorage.getItem('nav_barra') ?? 'true'),
                toggle() { this.barraOpen = !this.barraOpen; localStorage.setItem('nav_barra', JSON.stringify(this.barraOpen)); }
            }">
                <button @click="toggle()"
                        :aria-expanded="barraOpen.toString()"
                        aria-controls="nav-barra"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                               {{ $barraActive
                                  ? 'text-amber-700 dark:text-amber-300'
                                  : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100' }}">
                    <span class="flex items-center gap-3">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Barra
                    </span>
                    <svg :class="barraOpen ? 'rotate-90' : ''"
                         class="h-4 w-4 transition-transform flex-shrink-0"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div id="nav-barra" x-show="barraOpen" x-cloak class="mt-0.5 ml-7 pl-3 border-l border-gray-200 dark:border-gray-700 space-y-0.5">
                    <span x-data="barBadge('{{ route('bar.badge') }}')" x-init="init()">
                        <a href="{{ route('bar.index') }}"
                           aria-current="{{ request()->routeIs('bar.index') ? 'page' : 'false' }}"
                           class="flex items-center justify-between px-3 py-1.5 rounded-md text-sm transition-colors
                                  {{ request()->routeIs('bar.index')
                                     ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 font-medium'
                                     : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200' }}">
                            Pedidos
                            <span x-show="count > 0" x-cloak x-text="count"
                                  class="inline-flex items-center justify-center min-w-[1.25rem] h-4 px-1 text-xs font-bold text-white bg-amber-500 rounded-full"
                                  :aria-label="count + ' bebidas pendientes'"></span>
                        </a>
                    </span>
                    <a href="{{ route('bar.availability') }}"
                       aria-current="{{ request()->routeIs('bar.availability') ? 'page' : 'false' }}"
                       class="flex items-center px-3 py-1.5 rounded-md text-sm transition-colors
                              {{ request()->routeIs('bar.availability')
                                 ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 font-medium'
                                 : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200' }}">
                        Disponibilidad
                    </a>
                </div>
            </div>
          @else
            {{-- Staff de barra: dos links planos --}}
            <span x-data="barBadge('{{ route('bar.badge') }}')" x-init="init()" class="block">
                <a href="{{ route('bar.index') }}"
                   aria-current="{{ request()->routeIs('bar.index') ? 'page' : 'false' }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('bar.index')
                             ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300'
                             : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Barra
                    <span x-show="count > 0" x-cloak x-text="count"
                          class="ml-auto inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 text-xs font-bold text-white bg-amber-500 rounded-full"
                          :aria-label="count + ' bebidas pendientes en barra'"></span>
                </a>
            </span>
            <a href="{{ route('bar.availability') }}"
               aria-current="{{ request()->routeIs('bar.availability') ? 'page' : 'false' }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('bar.availability')
                         ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300'
                         : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
                Disponibilidad
            </a>
          @endif
        @endif

        {{-- Mapa de mesas (solo lectura) — camarero --}}
        @if(Auth::user()->role === 'waiter')
        <a href="{{ route('tables.map') }}"
           aria-current="{{ request()->routeIs('tables.map') ? 'page' : 'false' }}"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                  {{ request()->routeIs('tables.map')
                     ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300'
                     : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
            <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
            </svg>
            Mapa de mesas
        </a>
        @endif

        @if(Auth::user()->canAccessKitchen() || Auth::user()->canAccessBar())
        </div>
        @endif

    </nav>

    {{-- User section --}}
    <div class="flex-shrink-0 border-t border-gray-200 dark:border-gray-800 p-4">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50
                        flex items-center justify-center" aria-hidden="true">
                <span class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ Auth::user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        aria-label="Cerrar sesión"
                        class="p-2.5 min-h-[44px] min-w-[44px] flex items-center justify-center text-gray-400 hover:text-gray-700 dark:hover:text-gray-200
                               rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
