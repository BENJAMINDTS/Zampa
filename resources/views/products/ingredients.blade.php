<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl sm:text-2xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
            Receta de: {{ $product->name }}
        </h2>
    </x-slot>

    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4
              bg-white text-indigo-700 px-4 py-2 rounded font-medium z-50">
        Saltar al contenido principal
    </a>

    <main id="main-content">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-12">

            @if(session('success'))
                <div role="alert"
                     class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('products.ingredients.sync', $product) }}"
                  x-data="{
                      search: '',
                      page: 1,
                      perPage: 12,
                      names: @js($ingredients->pluck('name')->toArray()),

                      get filtered() {
                          const q = this.search.toLowerCase().trim();
                          return this.names.reduce((acc, name, i) => {
                              if (!q || name.toLowerCase().includes(q)) acc.push(i);
                              return acc;
                          }, []);
                      },

                      get totalPages() {
                          return Math.max(1, Math.ceil(this.filtered.length / this.perPage));
                      },

                      get pageIndices() {
                          const start = (this.page - 1) * this.perPage;
                          return this.filtered.slice(start, start + this.perPage);
                      },

                      get rangeStart() { return (this.page - 1) * this.perPage + 1; },
                      get rangeEnd()   { return Math.min(this.page * this.perPage, this.filtered.length); },

                      isShown(index) { return this.pageIndices.includes(index); },

                      onSearch() { this.page = 1; }
                  }">
                @csrf

                {{-- Barra de búsqueda --}}
                <div class="mb-4 flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                            <svg class="h-4 w-4 text-gray-400" aria-hidden="true" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                            </svg>
                        </span>
                        <input type="search"
                               x-model="search"
                               @input="onSearch"
                               placeholder="Buscar ingrediente…"
                               aria-label="Buscar ingrediente"
                               class="block w-full rounded-md border-gray-300 dark:border-gray-600
                                      bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200
                                      pl-9 pr-4 py-2 text-sm shadow-sm
                                      focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                    </div>

                    <p class="shrink-0 text-sm text-gray-500 dark:text-gray-400" aria-live="polite">
                        <span x-text="filtered.length === names.length
                            ? names.length + ' ingredientes'
                            : rangeEnd > 0
                                ? rangeStart + '–' + rangeEnd + ' de ' + filtered.length + ' resultados'
                                : '0 resultados'">
                        </span>
                    </p>
                </div>

                {{-- Sin resultados --}}
                <p x-show="filtered.length === 0"
                   class="py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                    Ningún ingrediente coincide con la búsqueda.
                </p>

                {{-- Grid de ingredientes --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    @foreach($ingredients as $ingredient)
                        @php $pivot = $product->ingredients->find($ingredient->id)?->pivot; @endphp

                        <fieldset x-show="isShown({{ $loop->index }})"
                                  class="border border-gray-200 dark:border-gray-700 rounded-lg p-4
                                         bg-white dark:bg-gray-800 shadow-sm">
                            <legend class="text-sm font-bold text-gray-700 dark:text-gray-200 px-1">
                                {{ $ingredient->name }}
                                @if($ingredient->is_allergen)
                                    <span role="img" aria-label="Alérgeno">⚠️</span>
                                    <span class="text-red-600 dark:text-red-400 text-xs font-semibold ml-1">Alérgeno</span>
                                @endif
                            </legend>

                            <div class="mt-3 space-y-3">

                                <div class="flex items-center gap-2">
                                    <input type="checkbox"
                                           name="ingredients[{{ $ingredient->id }}][included]"
                                           id="included-{{ $ingredient->id }}"
                                           value="1"
                                           {{ $pivot !== null ? 'checked' : '' }}
                                           class="h-4 w-4 rounded border-gray-300 dark:border-gray-600
                                                  text-orange-500 focus:outline-none focus:ring-2
                                                  focus:ring-orange-500 focus:ring-offset-2">
                                    <label for="included-{{ $ingredient->id }}"
                                           class="text-sm text-gray-700 dark:text-gray-300">
                                        Incluir en el plato
                                    </label>
                                </div>

                                <div class="flex flex-col gap-1">
                                    <label for="qty-{{ $ingredient->id }}"
                                           class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Cantidad base
                                    </label>
                                    <input type="number"
                                           name="ingredients[{{ $ingredient->id }}][quantity_base]"
                                           id="qty-{{ $ingredient->id }}"
                                           value="{{ $pivot->quantity_base ?? 1 }}"
                                           min="0"
                                           step="0.1"
                                           aria-required="true"
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600
                                                  bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200
                                                  shadow-sm text-sm focus:outline-none focus:ring-2
                                                  focus:ring-orange-500 focus:ring-offset-2">
                                    @error('ingredients.' . $ingredient->id . '.quantity_base')
                                        <p id="error-qty-{{ $ingredient->id }}"
                                           role="alert"
                                           class="mt-1 text-sm text-red-600 dark:text-red-400"
                                           aria-describedby="qty-{{ $ingredient->id }}">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="flex items-center gap-2">
                                    <input type="checkbox"
                                           name="ingredients[{{ $ingredient->id }}][is_removable]"
                                           id="removable-{{ $ingredient->id }}"
                                           value="1"
                                           {{ $pivot?->is_removable ? 'checked' : '' }}
                                           class="h-4 w-4 rounded border-gray-300 dark:border-gray-600
                                                  text-orange-500 focus:outline-none focus:ring-2
                                                  focus:ring-orange-500 focus:ring-offset-2">
                                    <label for="removable-{{ $ingredient->id }}"
                                           class="text-sm text-gray-700 dark:text-gray-300">
                                        El cliente puede quitarlo
                                    </label>
                                </div>

                                <div class="flex items-center gap-2">
                                    <input type="checkbox"
                                           name="ingredients[{{ $ingredient->id }}][is_extra]"
                                           id="extra-{{ $ingredient->id }}"
                                           value="1"
                                           {{ $pivot?->is_extra ? 'checked' : '' }}
                                           class="h-4 w-4 rounded border-gray-300 dark:border-gray-600
                                                  text-orange-500 focus:outline-none focus:ring-2
                                                  focus:ring-orange-500 focus:ring-offset-2">
                                    <label for="extra-{{ $ingredient->id }}"
                                           class="text-sm text-gray-700 dark:text-gray-300">
                                        Disponible como extra
                                    </label>
                                </div>

                                <div class="flex flex-col gap-1">
                                    <label for="price-{{ $ingredient->id }}"
                                           class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Precio extra (€)
                                    </label>
                                    <input type="number"
                                           name="ingredients[{{ $ingredient->id }}][extra_price]"
                                           id="price-{{ $ingredient->id }}"
                                           value="{{ $pivot->extra_price ?? '0.00' }}"
                                           min="0"
                                           step="0.01"
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600
                                                  bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200
                                                  shadow-sm text-sm focus:outline-none focus:ring-2
                                                  focus:ring-orange-500 focus:ring-offset-2">
                                    @error('ingredients.' . $ingredient->id . '.extra_price')
                                        <p id="error-price-{{ $ingredient->id }}"
                                           role="alert"
                                           class="mt-1 text-sm text-red-600 dark:text-red-400"
                                           aria-describedby="price-{{ $ingredient->id }}">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                            </div>
                        </fieldset>
                    @endforeach
                </div>

                {{-- Paginación --}}
                <nav x-show="totalPages > 1"
                     aria-label="Paginación de ingredientes"
                     class="mt-6 flex items-center justify-center gap-2">

                    <button type="button"
                            @click="page = Math.max(1, page - 1)"
                            :disabled="page === 1"
                            :class="page === 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                            class="rounded-md px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300
                                   border border-gray-300 dark:border-gray-600
                                   focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2
                                   transition-colors"
                            aria-label="Página anterior">
                        ← Anterior
                    </button>

                    <template x-for="p in totalPages" :key="p">
                        <button type="button"
                                @click="page = p"
                                :class="page === p
                                    ? 'bg-orange-500 text-white border-orange-500'
                                    : 'text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700'"
                                class="rounded-md px-3 py-1.5 text-sm border
                                       focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2
                                       transition-colors"
                                :aria-label="'Página ' + p"
                                :aria-current="page === p ? 'page' : null"
                                x-text="p">
                        </button>
                    </template>

                    <button type="button"
                            @click="page = Math.min(totalPages, page + 1)"
                            :disabled="page === totalPages"
                            :class="page === totalPages ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                            class="rounded-md px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300
                                   border border-gray-300 dark:border-gray-600
                                   focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2
                                   transition-colors"
                            aria-label="Página siguiente">
                        Siguiente →
                    </button>
                </nav>

                <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3">
                    <a href="{{ route('products.edit', $product) }}"
                       class="text-gray-600 dark:text-gray-400 hover:underline text-sm
                              focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 rounded">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6
                                   rounded shadow-sm focus:outline-none focus:ring-2
                                   focus:ring-orange-500 focus:ring-offset-2">
                        Guardar receta
                    </button>
                </div>

            </form>

        </div>
    </main>
</x-app-layout>
