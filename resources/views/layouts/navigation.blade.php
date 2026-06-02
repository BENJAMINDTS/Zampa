{{-- @author SebastianBCF --}}
<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    @php
        $cartaActive   = request()->routeIs('categories.*', 'ingredients.*', 'products.*', 'daily-menus.*');
        $localActive   = request()->routeIs('tables.*', 'zones.*', 'staff.*');
        $negocioActive = request()->routeIs('tapas.*', 'negocio.*', 'manager.*', 'ticket-config.*');
    @endphp

    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route(Auth::user()->homeRoute()) }}" aria-label="Ir a inicio">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @if(Auth::user()->role === 'admin')
                    {{-- Dashboard: acceso directo de alta frecuencia --}}
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    {{-- Carta: Categorías, Ingredientes, Productos --}}
                    <div class="relative flex items-stretch"
                         x-data="{ open: false }"
                         @click.outside="open = false"
                         @close.stop="open = false">
                        <button
                            @click="open = !open"
                            :aria-expanded="open.toString()"
                            aria-haspopup="menu"
                            aria-label="{{ __('Menú Carta') }}"
                            class="{{ $cartaActive
                                ? 'inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 dark:border-indigo-600 text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
                                : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out' }}"
                        >
                            {{ __('Carta') }}
                            <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            @click="open = false"
                            class="absolute z-50 top-full mt-2 w-48 rounded-md shadow-lg ltr:origin-top-left rtl:origin-top-right start-0"
                            style="display: none;"
                            role="menu"
                        >
                            <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white dark:bg-gray-700">
                                <x-dropdown-link role="menuitem" :href="route('categories.index')" :active="request()->routeIs('categories.*')">
                                    {{ __('Categorías') }}
                                </x-dropdown-link>
                                <x-dropdown-link role="menuitem" :href="route('ingredients.index')" :active="request()->routeIs('ingredients.*')">
                                    {{ __('Ingredientes') }}
                                </x-dropdown-link>
                                <x-dropdown-link role="menuitem" :href="route('products.index')" :active="request()->routeIs('products.*')">
                                    {{ __('Productos') }}
                                </x-dropdown-link>
                                <x-dropdown-link role="menuitem" :href="route('daily-menus.index')" :active="request()->routeIs('daily-menus.*')">
                                    {{ __('Menú del Día') }}
                                </x-dropdown-link>
                            </div>
                        </div>
                    </div>

                    {{-- Local: Mapa de mesas, Mi equipo --}}
                    <div class="relative flex items-stretch"
                         x-data="{ open: false }"
                         @click.outside="open = false"
                         @close.stop="open = false">
                        <button
                            @click="open = !open"
                            :aria-expanded="open.toString()"
                            aria-haspopup="menu"
                            aria-label="{{ __('Menú Local') }}"
                            class="{{ $localActive
                                ? 'inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 dark:border-indigo-600 text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
                                : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out' }}"
                        >
                            {{ __('Local') }}
                            <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            @click="open = false"
                            class="absolute z-50 top-full mt-2 w-48 rounded-md shadow-lg ltr:origin-top-left rtl:origin-top-right start-0"
                            style="display: none;"
                            role="menu"
                        >
                            <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white dark:bg-gray-700">
                                <x-dropdown-link role="menuitem" :href="route('tables.map')" :active="request()->routeIs('tables.*', 'zones.*')">
                                    {{ __('Mapa de mesas') }}
                                </x-dropdown-link>
                                <x-dropdown-link role="menuitem" :href="route('staff.index')" :active="request()->routeIs('staff.*')">
                                    {{ __('Mi equipo') }}
                                </x-dropdown-link>
                            </div>
                        </div>
                    </div>

                    {{-- Negocio: Tapas, Ingresos --}}
                    <div class="relative flex items-stretch"
                         x-data="{ open: false }"
                         @click.outside="open = false"
                         @close.stop="open = false">
                        <button
                            @click="open = !open"
                            :aria-expanded="open.toString()"
                            aria-haspopup="menu"
                            aria-label="{{ __('Menú Negocio') }}"
                            class="{{ $negocioActive
                                ? 'inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 dark:border-indigo-600 text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
                                : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out' }}"
                        >
                            {{ __('Negocio') }}
                            <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            @click="open = false"
                            class="absolute z-50 top-full mt-2 w-48 rounded-md shadow-lg ltr:origin-top-left rtl:origin-top-right start-0"
                            style="display: none;"
                            role="menu"
                        >
                            <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white dark:bg-gray-700">
                                <x-dropdown-link role="menuitem" :href="route('negocio.config.edit')" :active="request()->routeIs('negocio.*', 'tapas.*')">
                                    {{ __('Configuración del negocio') }}
                                </x-dropdown-link>
                                <x-dropdown-link role="menuitem" :href="route('manager.income')" :active="request()->routeIs('manager.*')">
                                    {{ __('Ingresos') }}
                                </x-dropdown-link>
                                <x-dropdown-link role="menuitem" :href="route('ticket-config.edit')" :active="request()->routeIs('ticket-config.*')">
                                    {{ __('Ticket PDF') }}
                                </x-dropdown-link>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Panel de Cocina: admin y kitchen --}}
                    @if(Auth::user()->canAccessKitchen())
                    <span
                        x-data="kitchenBadge('{{ route('kitchen.badge') }}')"
                        x-init="init()"
                        class="relative inline-flex items-center"
                    >
                        <x-nav-link :href="route('kitchen.index')" :active="request()->routeIs('kitchen.*')">
                            {{ __('Cocina') }}
                        </x-nav-link>
                        <span
                            x-show="count > 0"
                            x-text="count"
                            class="absolute -top-1 right-0 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full pointer-events-none"
                            :aria-label="count + ' pedidos nuevos sin atender'"
                        ></span>
                    </span>
                    @endif

                    {{-- Panel de Barra: admin y waiter --}}
                    @if(Auth::user()->canAccessBar())
                    <span
                        x-data="barBadge('{{ route('bar.badge') }}')"
                        x-init="init()"
                        class="relative inline-flex items-center"
                    >
                        <x-nav-link :href="route('bar.index')" :active="request()->routeIs('bar.*')">
                            {{ __('Barra') }}
                        </x-nav-link>
                        <span
                            x-show="count > 0"
                            x-text="count"
                            class="absolute -top-1 right-0 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-amber-500 rounded-full pointer-events-none"
                            :aria-label="count + ' bebidas pendientes en barra'"
                        ></span>
                    </span>
                    @endif

                    {{-- Mapa de mesas (solo lectura) — camarero --}}
                    @if(Auth::user()->role === 'waiter')
                    <x-nav-link :href="route('tables.map')" :active="request()->routeIs('tables.map')">
                        {{ __('Mapa') }}
                    </x-nav-link>
                    @endif
                </div>

                <!-- Dark mode toggle (desktop) -->
                <div class="hidden sm:flex sm:items-center sm:ms-4"
                     x-data="{
                         dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
                         toggle() {
                             this.dark = !this.dark;
                             document.documentElement.classList.toggle('dark', this.dark);
                             localStorage.setItem('theme', this.dark ? 'dark' : 'light');
                         }
                     }">
                    <button @click="toggle()"
                            :aria-label="dark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
                            :aria-pressed="dark.toString()"
                            class="p-2 rounded-md text-gray-500 dark:text-gray-400
                                   hover:bg-gray-100 dark:hover:bg-gray-700
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                   transition-colors duration-150">
                        {{-- Sol: visible en dark mode para volver a claro --}}
                        <svg x-show="dark" aria-hidden="true" class="h-5 w-5 text-amber-400"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        {{-- Luna: visible en light mode para pasar a oscuro --}}
                        <svg x-show="!dark" aria-hidden="true" class="h-5 w-5 text-gray-600"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>
                </div>

                <!-- Settings Dropdown -->
                <div class="hidden sm:flex sm:items-center sm:ms-2">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    aria-label="Menú de usuario: {{ Auth::user()->name }}">
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Hamburger -->
                <div class="-me-2 flex items-center sm:hidden">
                    <button @click="open = ! open"
                            :aria-expanded="open.toString()"
                            aria-controls="responsive-nav-menu"
                            aria-label="Abrir menú de navegación"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div id="responsive-nav-menu" :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
            <div class="pt-2 pb-3 space-y-1">
                @if(Auth::user()->role === 'admin')
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>

                <h3 class="px-3 pt-3 pb-1 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500" role="presentation">{{ __('Carta') }}</h3>
                <x-responsive-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')">
                    {{ __('Categorías') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('ingredients.index')" :active="request()->routeIs('ingredients.*')">
                    {{ __('Ingredientes') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                    {{ __('Productos') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('daily-menus.index')" :active="request()->routeIs('daily-menus.*')">
                    {{ __('Menú del Día') }}
                </x-responsive-nav-link>

                <h3 class="px-3 pt-3 pb-1 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500" role="presentation">{{ __('Local') }}</h3>
                <x-responsive-nav-link :href="route('tables.map')" :active="request()->routeIs('tables.*', 'zones.*')">
                    {{ __('Mapa de mesas') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('staff.index')" :active="request()->routeIs('staff.*')">
                    {{ __('Mi equipo') }}
                </x-responsive-nav-link>

                <h3 class="px-3 pt-3 pb-1 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500" role="presentation">{{ __('Negocio') }}</h3>
                <x-responsive-nav-link :href="route('negocio.config.edit')" :active="request()->routeIs('negocio.*', 'tapas.*')">
                    {{ __('Configuración del negocio') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('manager.income')" :active="request()->routeIs('manager.*')">
                    {{ __('Ingresos') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('ticket-config.edit')" :active="request()->routeIs('ticket-config.*')">
                    {{ __('Ticket PDF') }}
                </x-responsive-nav-link>

                @endif

                @if(Auth::user()->canAccessKitchen() || Auth::user()->canAccessBar())
                <h3 class="px-3 pt-3 pb-1 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500" role="presentation">{{ __('Servicio') }}</h3>
                @endif

                {{-- Panel de Cocina: admin y kitchen --}}
                @if(Auth::user()->canAccessKitchen())
                <span x-data="kitchenBadge('{{ route('kitchen.badge') }}')" x-init="init()" class="flex items-center">
                    <x-responsive-nav-link :href="route('kitchen.index')" :active="request()->routeIs('kitchen.*')">
                        {{ __('Cocina') }}
                        <span
                            x-show="count > 0"
                            x-text="count"
                            class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full"
                            :aria-label="count + ' pedidos nuevos sin atender'"
                        ></span>
                    </x-responsive-nav-link>
                </span>
                @endif

                {{-- Mapa de mesas (solo lectura) — camarero --}}
                @if(Auth::user()->role === 'waiter')
                <x-responsive-nav-link :href="route('tables.map')" :active="request()->routeIs('tables.map')">
                    {{ __('Mapa de mesas') }}
                </x-responsive-nav-link>
                @endif

                {{-- Panel de Barra: admin y waiter --}}
                @if(Auth::user()->canAccessBar())
                <span x-data="barBadge('{{ route('bar.badge') }}')" x-init="init()" class="flex items-center">
                    <x-responsive-nav-link :href="route('bar.index')" :active="request()->routeIs('bar.*')">
                        {{ __('Barra') }}
                        <span
                            x-show="count > 0"
                            x-text="count"
                            class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-amber-500 rounded-full"
                            :aria-label="count + ' bebidas pendientes en barra'"
                        ></span>
                    </x-responsive-nav-link>
                </span>
                @endif
            </div>

            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>

                <!-- Dark mode toggle (mobile) -->
                <div class="mt-3 px-4"
                     x-data="{
                         dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
                         toggle() {
                             this.dark = !this.dark;
                             document.documentElement.classList.toggle('dark', this.dark);
                             localStorage.setItem('theme', this.dark ? 'dark' : 'light');
                         }
                     }">
                    <button @click="toggle()"
                            :aria-label="dark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
                            :aria-pressed="dark.toString()"
                            class="flex items-center gap-2 w-full py-2 text-sm font-medium
                                   text-gray-600 dark:text-gray-400
                                   hover:text-gray-900 dark:hover:text-gray-100
                                   focus:outline-none focus:underline transition-colors">
                        <svg x-show="dark" aria-hidden="true" class="h-5 w-5 text-amber-400"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <svg x-show="!dark" aria-hidden="true" class="h-5 w-5"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        <span x-text="dark ? 'Modo claro' : 'Modo oscuro'"></span>
                    </button>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        </div>
</nav>
