<x-app-layout>
  <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 mt-4 sm:mt-10">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 mb-4 sm:mb-6">
      <div>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
          <svg class="h-6 w-6 text-indigo-500" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
          Mi Carta Digital
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 ml-8">
          {{ $products->total() }} {{ $products->total() === 1 ? 'producto registrado' : 'productos registrados' }}
        </p>
      </div>
      <a href="{{ route('products.create') }}"
         class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm py-2.5 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition">
        <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Añadir Producto
      </a>
    </div>

    {{-- Filtros: búsqueda, categoría y alérgeno --}}
    <form method="GET" action="{{ route('products.index') }}"
          class="mb-4 flex flex-col sm:flex-row gap-3 items-stretch sm:items-center flex-wrap"
          role="search" aria-label="Filtrar productos">
      <div class="relative flex-1 min-w-48">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
        </svg>
        <input type="text" name="search" id="search" value="{{ request('search') }}"
               placeholder="Buscar producto..."
               aria-label="Buscar producto por nombre"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 py-2 pl-9 pr-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
      </div>
      <div class="relative">
        <select name="category" id="category" aria-label="Filtrar por categoría"
                class="w-full sm:w-48 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 py-2 pl-3 pr-8 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 appearance-none">
          <option value="">Todas las categorías</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
              {{ $cat->name }}
            </option>
          @endforeach
        </select>
        <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </div>
      <div class="relative">
        <select name="allergen" id="allergen" aria-label="Filtrar por alérgeno"
                class="w-full sm:w-52 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 py-2 pl-3 pr-8 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 appearance-none">
          <option value="">Todos los alérgenos</option>
          @foreach($allergenTypes as $slug => $label)
            <option value="{{ $slug }}" {{ request('allergen') === $slug ? 'selected' : '' }}>
              {{ $label }}
            </option>
          @endforeach
        </select>
        <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </div>
      <button type="submit"
              class="inline-flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm py-2 px-4 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-colors">
        <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
        </svg>
        Filtrar
      </button>
      @if(request()->hasAny(['search', 'category', 'allergen']))
        <a href="{{ route('products.index') }}"
           class="inline-flex items-center justify-center gap-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold text-sm py-2 px-4 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-colors"
           aria-label="Limpiar filtros">
          <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Limpiar
        </a>
      @endif
    </form>

    {{-- Flash de éxito --}}
    @if(session('success'))
    <div role="alert" aria-live="polite"
         class="rounded-md bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 p-4 mb-4 flex items-start gap-3">
      <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <p class="text-sm text-emerald-800 dark:text-emerald-300">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Tabla --}}
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-x-auto" role="region" aria-label="Listado de productos" tabindex="0">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <caption class="sr-only">Listado de productos de mi carta digital</caption>
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            {{-- Imagen --}}
            <th scope="col" class="hidden sm:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              <span class="inline-flex items-center gap-1.5">
                <svg class="h-3.5 w-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Imagen
              </span>
            </th>

            {{-- Nombre --}}
            <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              <span class="inline-flex items-center gap-1.5">
                <svg class="h-3.5 w-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                </svg>
                Nombre
              </span>
            </th>

            {{-- Precio --}}
            <th scope="col" class="hidden md:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              <span class="inline-flex items-center gap-1.5">
                <svg class="h-3.5 w-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                Precio
              </span>
            </th>

            {{-- Alérgenos --}}
            <th scope="col" class="hidden lg:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              <span class="inline-flex items-center gap-1.5">
                <svg class="h-3.5 w-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Alérgenos
              </span>
            </th>

            {{-- Acciones --}}
            <th scope="col" class="px-4 sm:px-6 py-3 relative">
              <span class="sr-only">Acciones</span>
            </th>
          </tr>
        </thead>

        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          @forelse($products as $product)
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">

            {{-- Imagen / Placeholder --}}
            <td class="hidden sm:table-cell px-4 sm:px-6 py-4 whitespace-nowrap">
              @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}"
                     alt="Foto de {{ $product->name }}"
                     loading="lazy" decoding="async"
                     class="h-10 w-10 sm:h-12 sm:w-12 object-cover rounded-md">
              @else
                <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-md bg-gray-100 dark:bg-gray-700 flex items-center justify-center"
                     aria-label="Sin imagen">
                  <svg class="h-5 w-5 text-gray-300 dark:text-gray-600" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
              @endif
            </td>

            {{-- Nombre + estado activo/inactivo + categoría --}}
            <td class="px-4 sm:px-6 py-4">
              <div class="flex flex-col gap-1">
                <span class="font-medium text-gray-900 dark:text-gray-100 text-sm sm:text-base leading-tight">
                  {{ $product->name }}
                </span>

                <div class="flex items-center flex-wrap gap-2">
                  {{-- Estado activo / inactivo --}}
                  @if($product->is_active)
                    <span class="inline-flex items-center gap-1 text-xs text-emerald-700 dark:text-emerald-400">
                      <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                      Activo
                    </span>
                  @else
                    <span class="inline-flex items-center gap-1 text-xs text-amber-600 dark:text-amber-400">
                      <svg class="h-3 w-3" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                      </svg>
                      Oculto en carta
                    </span>
                  @endif

                  {{-- Categorías --}}
                  @foreach($product->categories as $cat)
                    <span class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                      @if($cat->destination === 'kitchen')
                        <svg class="h-3 w-3" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                      @else
                        <svg class="h-3 w-3" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                      @endif
                      {{ $cat->name }}
                    </span>
                  @endforeach
                </div>
              </div>
            </td>

            {{-- Precio --}}
            <td class="hidden md:table-cell px-4 sm:px-6 py-4 text-gray-600 dark:text-gray-300 text-sm">
              @if($product->variants->isNotEmpty())
                <div class="flex items-center gap-1 mb-1 text-gray-500 dark:text-gray-400 text-xs">
                  <svg class="h-3 w-3" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                  </svg>
                  desde {{ number_format($product->getEffectivePrice(), 2, ',', '.') }} €
                </div>
                <div class="flex flex-wrap gap-1">
                  @foreach($product->variants as $variant)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs font-medium border border-indigo-200 dark:border-indigo-700">
                      {{ $variant->name }}&nbsp;·&nbsp;{{ number_format($variant->price, 2, ',', '.') }}&nbsp;€
                    </span>
                  @endforeach
                </div>
              @else
                <span class="inline-flex items-center gap-1.5 font-semibold text-gray-800 dark:text-gray-200">
                  <svg class="h-3.5 w-3.5 text-gray-400 dark:text-gray-500" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  {{ number_format($product->price, 2, ',', '.') }} €
                </span>
              @endif
            </td>

            {{-- Alérgenos --}}
            <td class="hidden lg:table-cell px-4 sm:px-6 py-4">
              @php
                $allergenSlugs = $product->ingredients->where('is_allergen', true)
                    ->flatMap(fn($i) => $i->allergen_types ?? [])
                    ->unique()->values();
              @endphp
              @if($allergenSlugs->isNotEmpty())
                <div class="flex flex-wrap gap-3" role="list" aria-label="Alérgenos de {{ $product->name }}">
                  @foreach($allergenSlugs as $slug)
                    <x-allergen-badge :slug="$slug" />
                  @endforeach
                </div>
              @else
                <span class="inline-flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500">
                  <svg class="h-3.5 w-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  Sin alérgenos
                </span>
              @endif
            </td>

            {{-- Acciones --}}
            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
              <div class="flex justify-end items-center gap-2">
                <a href="{{ route('products.edit', $product) }}"
                   aria-label="Editar producto {{ $product->name }}"
                   class="inline-flex items-center gap-1.5 text-sm bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 border border-indigo-200 dark:border-indigo-700 py-1.5 px-3 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition">
                  <svg class="h-3.5 w-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                  Editar
                </a>
                <form action="{{ route('products.destroy', $product) }}" method="POST"
                      onsubmit="return confirm('¿Seguro que quieres borrar el producto {{ addslashes($product->name) }}?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit"
                          aria-label="Borrar producto {{ $product->name }}"
                          class="inline-flex items-center gap-1.5 text-sm bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50 border border-red-200 dark:border-red-700 py-1.5 px-3 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 transition">
                    <svg class="h-3.5 w-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Borrar
                  </button>
                </form>
              </div>
            </td>

          </tr>
          @empty
          {{-- Estado vacío --}}
          <tr>
            <td colspan="5" class="px-6 py-16 text-center">
              <div class="flex flex-col items-center gap-3">
                <div class="h-16 w-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                  <svg class="h-8 w-8 text-gray-300 dark:text-gray-600" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M12 18v-6m-3 3h6"/>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Aún no tienes productos</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Empieza añadiendo tu primer producto a la carta.</p>
                </div>
                <a href="{{ route('products.create') }}"
                   class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition mt-1">
                  <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                  Añadir primer producto
                </a>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($products->hasPages())
    <nav aria-label="Paginación de productos" class="mt-4">
      {{ $products->links() }}
    </nav>
    @endif

  </div>
</x-app-layout>
