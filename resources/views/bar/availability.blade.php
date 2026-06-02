{{-- @author BenjaminDTS --}}
<x-app-layout>
  <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6 mt-4 sm:mt-10">

    {{-- Cabecera --}}
    <div class="flex items-start gap-4 mb-6">
      <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center shrink-0 mt-0.5">
        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
             aria-hidden="true">
          <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
      </div>
      <div>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">
          Disponibilidad — Barra
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
          Desactiva los productos que se agoten. Desaparecen de la carta al instante.
        </p>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl overflow-hidden
                border border-gray-200 dark:border-gray-700">

      {{-- Banner de agotados --}}
      @php $unavailableCount = $barProducts->where('is_available', false)->count(); @endphp
      @if($unavailableCount > 0)
        <div class="flex items-center gap-2 px-4 py-2.5
                    bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-800">
          <svg class="w-4 h-4 text-amber-500 dark:text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
               aria-hidden="true">
            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
          </svg>
          <p class="text-xs font-semibold text-amber-700 dark:text-amber-300">
            {{ $unavailableCount }} producto{{ $unavailableCount > 1 ? 's' : '' }}
            agotado{{ $unavailableCount > 1 ? 's' : '' }}
          </p>
        </div>
      @endif

      {{-- Lista --}}
      @if($barProducts->isEmpty())
        <div class="px-4 py-12 text-center">
          <svg class="mx-auto mb-3 w-10 h-10 text-gray-300 dark:text-gray-600" viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
               stroke-linejoin="round" aria-hidden="true">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
          <p class="text-sm text-gray-400 dark:text-gray-500">No hay productos de barra activos.</p>
        </div>
      @else
        <ul class="divide-y divide-gray-100 dark:divide-gray-700/60" role="list"
            aria-label="Productos de barra">
          @foreach($barProducts as $product)
          <li
            class="flex items-center gap-3 px-4 py-3.5 transition-colors
                   hover:bg-gray-50 dark:hover:bg-gray-800/60"
            x-data="productAvailability({
              available: {{ $product->is_available ? 'true' : 'false' }},
              toggleUrl: '{{ url('/bar/productos/' . $product->id . '/disponibilidad') }}'
            })"
            :class="available ? '' : 'bg-amber-50/60 dark:bg-amber-900/10'"
          >
            {{-- Dot de estado --}}
            <span class="shrink-0 w-2 h-2 rounded-full transition-colors"
                  :class="available ? 'bg-green-500' : 'bg-amber-400'"
                  aria-hidden="true"></span>

            {{-- Nombre + badge agotado --}}
            <div class="flex-1 min-w-0 flex items-center gap-2 flex-wrap">
              <span class="text-sm font-medium transition-colors leading-snug"
                    :class="available
                      ? 'text-gray-800 dark:text-gray-200'
                      : 'text-amber-700 dark:text-amber-400 line-through'">
                {{ $product->name }}
              </span>
              <span x-show="!available" x-cloak
                    class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md text-xs font-semibold
                           bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">
                <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                  <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                Agotado
              </span>
            </div>

            {{-- Spinner + toggle --}}
            <div class="flex items-center gap-2 shrink-0">
              <svg x-show="loading" x-cloak
                   class="w-4 h-4 text-gray-400 animate-spin"
                   viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
              </svg>

              <button
                @click="toggle()"
                :disabled="loading"
                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2
                       border-transparent transition-colors duration-200 ease-in-out
                       focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2
                       disabled:opacity-50 disabled:cursor-not-allowed"
                :class="available ? 'bg-amber-500' : 'bg-gray-300 dark:bg-gray-600'"
                role="switch"
                :aria-checked="available.toString()"
                :aria-label="'{{ $product->name }}: ' + (available
                  ? 'disponible, pulsa para marcar agotado'
                  : 'agotado, pulsa para restaurar')"
              >
                <span
                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full
                         bg-white shadow ring-0 transition duration-200 ease-in-out"
                  :class="available ? 'translate-x-5' : 'translate-x-0'"
                ></span>
              </button>
            </div>
          </li>
          @endforeach
        </ul>
      @endif

    </div>
  </div>
</x-app-layout>
