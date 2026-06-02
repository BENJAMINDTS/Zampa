{{-- @author BenjaminDTS --}}
<x-app-layout>
  <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6 mt-4 sm:mt-10">

    <div class="mb-6">
      <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100">Disponibilidad — Cocina</h2>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Desactiva los productos que se agoten durante el servicio. Desaparecen de la carta al instante y se pueden reactivar en cualquier momento.
      </p>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">

      @php $unavailableCount = $products->where('is_available', false)->count(); @endphp
      @if($unavailableCount > 0)
        <div class="px-4 py-2 bg-red-50 dark:bg-red-900/20 border-b border-red-200 dark:border-red-800">
          <p class="text-xs font-semibold text-red-700 dark:text-red-300">
            {{ $unavailableCount }} producto{{ $unavailableCount > 1 ? 's' : '' }} marcado{{ $unavailableCount > 1 ? 's' : '' }} como agotado{{ $unavailableCount > 1 ? 's' : '' }}
          </p>
        </div>
      @endif

      @if($products->isEmpty())
        <p class="px-4 py-10 text-sm text-center text-gray-400 dark:text-gray-500">No hay productos de cocina activos.</p>
      @else
        <ul class="divide-y divide-gray-100 dark:divide-gray-700" role="list" aria-label="Productos de cocina">
          @foreach($products as $product)
          <li
            class="flex items-center justify-between gap-3 px-4 py-3 transition-colors"
            x-data="productAvailability({
              available: {{ $product->is_available ? 'true' : 'false' }},
              toggleUrl: '{{ url('/cocina/productos/' . $product->id . '/disponibilidad') }}'
            })"
            :class="available ? '' : 'bg-red-50 dark:bg-red-900/20'"
          >
            <div class="flex-1 min-w-0">
              <span
                class="text-sm font-medium transition-colors"
                :class="available ? 'text-gray-800 dark:text-gray-200' : 'text-red-500 dark:text-red-400 line-through'"
              >{{ $product->name }}</span>
              <span
                class="ml-2 text-xs transition-colors"
                :class="available ? 'text-gray-400' : 'text-red-500 font-semibold'"
                x-text="available ? '' : 'Agotado'"
              ></span>
            </div>

            <button
              @click="toggle()"
              :disabled="loading"
              class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
              :class="available ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'"
              role="switch"
              :aria-checked="available.toString()"
              :aria-label="'{{ $product->name }}: ' + (available ? 'disponible, pulsa para marcar agotado' : 'agotado, pulsa para restaurar')"
            >
              <span
                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                :class="available ? 'translate-x-5' : 'translate-x-0'"
              ></span>
            </button>
          </li>
          @endforeach
        </ul>
      @endif

    </div>
  </div>
</x-app-layout>
