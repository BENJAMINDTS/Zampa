{{-- @author SebastianBCF --}}
<header class="flex-shrink-0 flex items-center h-16 px-4 sm:px-6 gap-4
               bg-white dark:bg-gray-900
               border-b border-gray-200 dark:border-gray-800"
        role="banner">

    {{-- Hamburger (mobile only) --}}
    <button @click="sidebarOpen = !sidebarOpen"
            :aria-expanded="sidebarOpen.toString()"
            aria-controls="sidebar"
            aria-label="Abrir menú lateral"
            class="lg:hidden p-2.5 min-h-[44px] min-w-[44px] rounded-md text-gray-500 dark:text-gray-400
                   hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center justify-center
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
        <svg x-show="!sidebarOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        <svg x-show="sidebarOpen" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    {{-- Page heading (from named slot) --}}
    <div class="flex-1 min-w-0">
        @isset($header)
            {{ $header }}
        @endisset
    </div>

    {{-- Right controls --}}
    <div class="flex items-center gap-2">

        {{-- Dark mode toggle --}}
        <button x-data="{
                    isDark: document.documentElement.classList.contains('dark'),
                    toggle() {
                        this.isDark = !this.isDark;
                        document.documentElement.classList.toggle('dark', this.isDark);
                        localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                    }
                }"
                @click="toggle()"
                :aria-label="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
                :aria-pressed="isDark.toString()"
                class="p-2.5 min-h-[44px] min-w-[44px] rounded-md text-gray-500 dark:text-gray-400
                       hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center justify-center
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
            {{-- Sun (shown in dark mode) --}}
            <svg x-show="isDark" x-cloak class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            {{-- Moon (shown in light mode) --}}
            <svg x-show="!isDark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
        </button>

        {{-- Profile --}}
        <a href="{{ route('profile.edit') }}"
           aria-label="Mi perfil"
           class="flex items-center gap-2 px-2 py-1.5 min-h-[44px] rounded-md
                  hover:bg-gray-100 dark:hover:bg-gray-800
                  focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
            <div class="h-7 w-7 rounded-full bg-indigo-100 dark:bg-indigo-900/50
                        flex items-center justify-center flex-shrink-0" aria-hidden="true">
                <span class="text-xs font-semibold text-indigo-700 dark:text-indigo-300">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </span>
            </div>
            <span class="hidden sm:block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ Auth::user()->name }}
            </span>
        </a>
    </div>
</header>
