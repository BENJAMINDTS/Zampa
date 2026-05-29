<x-app-layout>
  <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 mt-4 sm:mt-10">
    <div class="flex justify-between items-center mb-4 sm:mb-6">
      <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100">Mi Carta Digital</h2>
      <a href="{{ route('products.create') }}"
         class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm py-2.5 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition">
        <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Añadir Plato
      </a>
    </div>

    @if(session('success'))
    <div role="alert" aria-live="polite"
         class="rounded-md bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 p-4 mb-4 flex items-start gap-3">
      <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <p class="text-sm text-emerald-800 dark:text-emerald-300">{{ session('success') }}</p>
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-x-auto" role="region" aria-label="Listado de platos" tabindex="0">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <caption class="sr-only">Listado de platos de mi carta digital</caption>
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <th scope="col" class="hidden sm:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Imagen</th>
            <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nombre</th>
            <th scope="col" class="hidden md:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Precio</th>
            <th scope="col" class="hidden lg:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Alérgenos</th>
            <th scope="col" class="px-4 sm:px-6 py-3 relative"><span class="sr-only">Acciones</span></th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          @foreach($products as $product)
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <td class="hidden sm:table-cell px-4 sm:px-6 py-4 whitespace-nowrap">
              @if($product->image)
              <img src="{{ asset('storage/' . $product->image) }}" alt="Foto de {{ $product->name }}" class="h-10 w-10 sm:h-12 sm:w-12 object-cover rounded-md">
              @else
              <span class="text-gray-400 dark:text-gray-500 text-sm">Sin imagen</span>
              @endif
            </td>
            <td class="px-4 sm:px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-gray-100 text-sm sm:text-base">{{ $product->name }}</td>
            <td class="hidden md:table-cell px-4 sm:px-6 py-4 text-gray-600 dark:text-gray-300 text-sm">
              @if($product->variants->isNotEmpty())
                <div class="mb-1">desde {{ number_format($product->getEffectivePrice(), 2, ',', '.') }} €</div>
                <div class="flex flex-wrap gap-1">
                  @foreach($product->variants as $variant)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs font-medium border border-indigo-200 dark:border-indigo-700">
                      {{ $variant->name }}&nbsp;·&nbsp;{{ number_format($variant->price, 2, ',', '.') }}&nbsp;€
                    </span>
                  @endforeach
                </div>
              @else
                {{ number_format($product->price, 2, ',', '.') }} €
              @endif
            </td>
            <td class="hidden lg:table-cell px-4 sm:px-6 py-4">
              @if($product->ingredients->where('is_allergen', true)->isNotEmpty())
                <div class="flex flex-wrap gap-3" role="list" aria-label="Alérgenos de {{ $product->name }}">
                  @foreach($product->ingredients->where('is_allergen', true)->unique('allergen_type') as $allergen)
                    <x-allergen-badge :ingredient="$allergen" />
                  @endforeach
                </div>
              @else
                <span class="text-gray-400 dark:text-gray-500 text-xs">Ninguno</span>
              @endif
            </td>
            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
              <div class="flex justify-end items-center gap-2">
                <a href="{{ route('products.edit', $product) }}"
                   aria-label="Editar plato {{ $product->name }}"
                   class="inline-flex items-center gap-1 text-sm bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 border border-indigo-200 dark:border-indigo-700 py-1.5 px-3 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition">
                  <svg class="h-3.5 w-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                  Editar
                </a>
                <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres borrar el plato {{ addslashes($product->name) }}?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit"
                          aria-label="Borrar plato {{ $product->name }}"
                          class="inline-flex items-center gap-1 text-sm bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50 border border-red-200 dark:border-red-700 py-1.5 px-3 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 transition">
                    <svg class="h-3.5 w-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Borrar
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @endforeach
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