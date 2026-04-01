<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carta — {{ $table->user->name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen">

    {{-- Skip to content --}}
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-white dark:bg-gray-800 text-indigo-700 dark:text-indigo-300 px-4 py-2 rounded font-medium z-50 shadow">
        Saltar al contenido principal
    </a>

    {{-- Header --}}
    <header class="sticky top-0 z-40 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">
                    Carta digital
                </p>
                <h1 class="text-lg sm:text-xl font-bold leading-tight">
                    {{ $table->user->name }}
                </h1>
            </div>
            <div class="text-right">
                <span class="inline-flex items-center gap-1 text-xs font-medium bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 px-2.5 py-1 rounded-full">
                    <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 10h11M9 21V3M19 14l2 2-2 2m2-2H13"/>
                    </svg>
                    {{ $table->name }}
                </span>
            </div>
        </div>
    </header>

    {{-- Category quick-nav --}}
    @if ($categories->isNotEmpty())
        <nav aria-label="Categorías del menú"
             class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <ul class="flex gap-1 py-2" role="list">
                    @foreach ($categories as $category)
                        <li>
                            <a href="#categoria-{{ $category->id }}"
                               class="inline-block whitespace-nowrap px-3 py-1.5 rounded-full text-sm font-medium
                                      text-gray-600 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/40
                                      hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </nav>
    @endif

    {{-- Main content --}}
    <main id="main-content" class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-10">

        @forelse ($categories as $category)
            <section aria-labelledby="titulo-categoria-{{ $category->id }}"
                     id="categoria-{{ $category->id }}">

                {{-- Category heading --}}
                <div class="flex items-center gap-3 mb-4">
                    <h2 id="titulo-categoria-{{ $category->id }}"
                        class="text-xl sm:text-2xl font-bold">
                        {{ $category->name }}
                    </h2>

                    @if ($category->destination === 'bar')
                        <span class="inline-flex items-center gap-1 text-xs font-medium
                                     bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300
                                     px-2 py-0.5 rounded-full border border-amber-200 dark:border-amber-700"
                              aria-label="Categoría servida desde la barra">
                            <svg aria-hidden="true" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 3a1 1 0 000 2h1.5l1.4 7H3a1 1 0 000 2h1.2l.4 2H3a1 1 0 100 2h14a1 1 0 100-2h-1.6l.4-2H17a1 1 0 000-2h-3.9L14.5 5H16a1 1 0 000-2H3z"/>
                            </svg>
                            Barra
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-xs font-medium
                                     bg-orange-100 dark:bg-orange-900/50 text-orange-700 dark:text-orange-300
                                     px-2 py-0.5 rounded-full border border-orange-200 dark:border-orange-700"
                              aria-label="Categoría preparada en cocina">
                            <svg aria-hidden="true" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"
                                      clip-rule="evenodd"/>
                            </svg>
                            Cocina
                        </span>
                    @endif
                </div>

                {{-- Products grid --}}
                <ul class="space-y-3" role="list" aria-label="Productos de {{ $category->name }}">
                    @foreach ($category->products as $product)
                        <li class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700
                                   shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                            <div class="flex gap-3 sm:gap-4 p-3 sm:p-4">

                                {{-- Product image --}}
                                @if ($product->image)
                                    <div class="flex-shrink-0">
                                        <img src="{{ Storage::url($product->image) }}"
                                             alt="Foto de {{ $product->name }}"
                                             class="h-20 w-20 sm:h-24 sm:w-24 object-cover rounded-lg">
                                    </div>
                                @endif

                                {{-- Product info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="font-semibold text-base sm:text-lg leading-snug">
                                            {{ $product->name }}
                                        </h3>
                                        <span class="flex-shrink-0 font-bold text-base sm:text-lg
                                                     text-indigo-600 dark:text-indigo-400"
                                              aria-label="Precio: {{ number_format($product->price, 2, ',', '.') }} euros">
                                            {{ number_format($product->price, 2, ',', '.') }}&nbsp;€
                                        </span>
                                    </div>

                                    @if ($product->description)
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                            {{ $product->description }}
                                        </p>
                                    @endif

                                    {{-- Allergens --}}
                                    @if ($product->ingredients->isNotEmpty())
                                        <div class="mt-2" aria-label="Alérgenos de {{ $product->name }}">
                                            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-1">
                                                Alérgenos
                                            </p>
                                            <ul class="flex flex-wrap gap-1" role="list">
                                                @foreach ($product->ingredients as $allergen)
                                                    <li>
                                                        <span class="inline-block text-xs font-medium
                                                                     bg-red-100 dark:bg-red-900/40
                                                                     text-red-700 dark:text-red-300
                                                                     border border-red-200 dark:border-red-700
                                                                     px-2 py-0.5 rounded-full"
                                                              role="listitem">
                                                            {{ $allergen->name }}
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
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

    {{-- Footer --}}
    <footer class="mt-12 border-t border-gray-200 dark:border-gray-800 py-6 text-center">
        <p class="text-xs text-gray-400 dark:text-gray-600">
            Carta digital generada con
            <span class="font-semibold text-indigo-500 dark:text-indigo-400">Zampa</span>
        </p>
    </footer>

</body>
</html>
