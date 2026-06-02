<!DOCTYPE html>
<html lang="es" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Zampa') }}</title>
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="shortcut icon" href="/favicon.svg">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|playfair-display:400,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Dark mode: apply before paint to avoid flash -->
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
    <body class="h-full font-sans antialiased bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100">

        <!-- Skip to content -->
        <a href="#main-content"
           class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4
                  bg-white text-indigo-700 px-4 py-2 rounded font-medium z-50 shadow-md">
            Saltar al contenido principal
        </a>

        <div class="flex h-full"
             x-data="{ sidebarOpen: false }"
             @keydown.escape.window="sidebarOpen = false">

            <!-- Mobile overlay -->
            <div x-show="sidebarOpen"
                 x-cloak
                 @click="sidebarOpen = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-20 bg-black/50 lg:hidden"
                 aria-hidden="true">
            </div>

            <!-- Sidebar -->
            @include('layouts.sidebar')

            <!-- Main area -->
            <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

                <!-- Topbar -->
                @include('layouts.topbar')

                <!-- Page Content -->
                <main id="main-content" class="flex-1 overflow-y-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <x-toast />
        @stack('scripts')
    </body>
</html>
