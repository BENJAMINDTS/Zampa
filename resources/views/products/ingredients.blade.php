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

            <form method="POST" action="{{ route('products.ingredients.sync', $product) }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    @foreach($ingredients as $ingredient)
                        @php $pivot = $product->ingredients->find($ingredient->id)?->pivot; @endphp

                        <fieldset class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-800 shadow-sm">
                            <legend class="text-sm font-bold text-gray-700 dark:text-gray-200 px-1">
                                {{ $ingredient->name }}
                                @if($ingredient->is_allergen)
                                    <span role="img" aria-label="Alérgeno">⚠️</span>
                                    <span class="text-red-600 dark:text-red-400 text-xs font-semibold ml-1">Alérgeno</span>
                                @endif
                            </legend>

                            <div class="mt-3 space-y-3">

                                {{-- Checkbox: Incluir en el plato --}}
                                <div class="flex items-center gap-2">
                                    <input type="checkbox"
                                           name="ingredients[{{ $ingredient->id }}][included]"
                                           id="included-{{ $ingredient->id }}"
                                           value="1"
                                           {{ $pivot !== null ? 'checked' : '' }}
                                           class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                                    <label for="included-{{ $ingredient->id }}"
                                           class="text-sm text-gray-700 dark:text-gray-300">
                                        Incluir en el plato
                                    </label>
                                </div>

                                {{-- Input: Cantidad base --}}
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
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                                    @error('ingredients.' . $ingredient->id . '.quantity_base')
                                        <p id="error-qty-{{ $ingredient->id }}"
                                           role="alert"
                                           class="mt-1 text-sm text-red-600 dark:text-red-400"
                                           aria-describedby="qty-{{ $ingredient->id }}">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                {{-- Checkbox: El cliente puede quitarlo --}}
                                <div class="flex items-center gap-2">
                                    <input type="checkbox"
                                           name="ingredients[{{ $ingredient->id }}][is_removable]"
                                           id="removable-{{ $ingredient->id }}"
                                           value="1"
                                           {{ $pivot?->is_removable ? 'checked' : '' }}
                                           class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                                    <label for="removable-{{ $ingredient->id }}"
                                           class="text-sm text-gray-700 dark:text-gray-300">
                                        El cliente puede quitarlo
                                    </label>
                                </div>

                                {{-- Checkbox: Disponible como extra --}}
                                <div class="flex items-center gap-2">
                                    <input type="checkbox"
                                           name="ingredients[{{ $ingredient->id }}][is_extra]"
                                           id="extra-{{ $ingredient->id }}"
                                           value="1"
                                           {{ $pivot?->is_extra ? 'checked' : '' }}
                                           class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                                    <label for="extra-{{ $ingredient->id }}"
                                           class="text-sm text-gray-700 dark:text-gray-300">
                                        Disponible como extra
                                    </label>
                                </div>

                                {{-- Input: Precio extra --}}
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
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
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

                <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3">
                    <a href="{{ route('products.edit', $product) }}"
                       class="text-gray-600 dark:text-gray-400 hover:underline text-sm focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 rounded">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                        Guardar receta
                    </button>
                </div>

            </form>

        </div>
    </main>
</x-app-layout>
