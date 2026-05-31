{{-- @author SebastianBCF --}}
<x-app-layout>
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Cabecera --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
      <div class="flex items-center gap-3">
        <span class="p-2.5 bg-indigo-100 dark:bg-indigo-900/50 rounded-xl shadow-sm" aria-hidden="true">
          <svg class="h-6 w-6 text-indigo-700 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
          </svg>
        </span>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">Categorías</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
            {{ $categories->total() }} {{ $categories->total() === 1 ? 'categoría' : 'categorías' }} en tu carta
          </p>
        </div>
      </div>
      <a href="{{ route('categories.create') }}"
         class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white
                font-semibold text-sm py-2.5 px-4 rounded-lg shadow-md
                focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                dark:focus:ring-offset-gray-900 transition-colors">
        <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Nueva categoría
      </a>
    </div>

    {{-- Búsqueda --}}
    <form method="GET" action="{{ route('categories.index') }}"
          class="mb-6 flex flex-col sm:flex-row gap-3 items-stretch sm:items-center"
          role="search" aria-label="Buscar categorías">
      <div class="relative flex-1">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
        </svg>
        <input type="text" name="search" id="search" value="{{ request('search') }}"
               placeholder="Buscar categoría..."
               aria-label="Buscar categoría por nombre"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 py-2 pl-9 pr-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
      </div>
      <button type="submit"
              class="inline-flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm py-2.5 px-4 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-colors">
        <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
        </svg>
        Buscar
      </button>
      @if(request()->filled('search'))
        <a href="{{ route('categories.index') }}"
           class="inline-flex items-center justify-center gap-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold text-sm py-2.5 px-4 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-colors"
           aria-label="Limpiar búsqueda">
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
         class="rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 p-4 mb-6 flex items-start gap-3 shadow-sm">
      <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Grid de tarjetas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      @forelse ($categories as $category)

      @php $isKitchen = $category->destination === 'kitchen'; @endphp

      <article class="flex flex-col bg-white dark:bg-gray-800
                       border border-gray-200 dark:border-gray-700
                       rounded-xl shadow-md hover:shadow-xl
                       transition-shadow duration-200 overflow-hidden"
               aria-label="Categoría {{ $category->name }}">

        <div class="flex flex-col flex-1 p-5">
          {{-- Icono + nombre --}}
          <div class="flex items-start gap-3 mb-4">
            <span class="mt-0.5 p-2 rounded-lg shadow-sm
                         bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600"
                  aria-hidden="true">
              @if($isKitchen)
              <svg class="h-5 w-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              @else
              <svg class="h-5 w-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
              @endif
            </span>
            <div>
              <h2 class="text-base font-bold text-gray-900 dark:text-gray-100 leading-snug">
                {{ $category->name }}
              </h2>
              <span class="inline-flex items-center gap-1 mt-1 text-xs font-semibold px-2 py-0.5 rounded-full
                           bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300
                           border border-gray-200 dark:border-gray-600">
                {{ $isKitchen ? 'Cocina' : 'Barra' }}
              </span>
            </div>
          </div>

          {{-- Botones --}}
          <div class="mt-auto flex gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('categories.edit', $category) }}"
               aria-label="Editar categoría {{ $category->name }}"
               class="flex-1 inline-flex items-center justify-center gap-1.5 text-sm font-semibold
                      bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200
                      border border-gray-300 dark:border-gray-500
                      hover:bg-gray-50 dark:hover:bg-gray-600 shadow-sm hover:shadow
                      py-2 px-3 min-h-[44px] rounded-lg
                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition-all">
              <svg class="h-3.5 w-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Editar
            </a>
            <form action="{{ route('categories.destroy', $category) }}" method="POST"
                  onsubmit="return confirm('¿Eliminar la categoría «{{ addslashes($category->name) }}»?');">
              @csrf
              @method('DELETE')
              <button type="submit"
                      aria-label="Eliminar categoría {{ $category->name }}"
                      class="inline-flex items-center justify-center gap-1.5 text-sm font-semibold
                             bg-white dark:bg-gray-700 text-red-600 dark:text-red-400
                             border border-red-200 dark:border-red-700
                             hover:bg-red-50 dark:hover:bg-red-900/20 shadow-sm hover:shadow
                             py-1.5 px-3 rounded-lg
                             focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 transition-all">
                <svg class="h-3.5 w-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Eliminar
              </button>
            </form>
          </div>
        </div>
      </article>

      @empty
      <div class="col-span-3 py-16 flex flex-col items-center justify-center
                  border border-dashed border-gray-300 dark:border-gray-600 rounded-xl">
        <span class="p-4 bg-gray-100 dark:bg-gray-700 rounded-full mb-4 shadow-sm" aria-hidden="true">
          <svg class="h-8 w-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
          </svg>
        </span>
        <p class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-1">Sin categorías todavía</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Crea tu primera categoría para organizar la carta.</p>
        <a href="{{ route('categories.create') }}"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white
                  font-semibold text-sm py-2 px-4 rounded-lg shadow-md
                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
          <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
          Crear primera categoría
        </a>
      </div>
      @endforelse
    </div>

    @if($categories->hasPages())
    <nav aria-label="Paginación de categorías" class="mt-8">
      {{ $categories->links() }}
    </nav>
    @endif

  </div>
</x-app-layout>
