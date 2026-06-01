{{-- @author SebastianBCF --}}
<x-app-layout>
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Cabecera --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
      <div class="flex items-center gap-3">
        <span class="p-2.5 bg-green-100 dark:bg-green-900/50 rounded-xl shadow-sm" aria-hidden="true">
          <svg class="h-6 w-6 text-green-700 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
          </svg>
        </span>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">Ingredientes</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
            {{ $ingredients->total() }} {{ $ingredients->total() === 1 ? 'ingrediente' : 'ingredientes' }} en tu despensa
          </p>
        </div>
      </div>
      <a href="{{ route('ingredients.create') }}"
         class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white
                font-semibold text-sm py-2.5 px-4 rounded-lg shadow-md
                focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2
                dark:focus:ring-offset-gray-900 transition-colors">
        <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Nuevo ingrediente
      </a>
    </div>

    {{-- Búsqueda y filtros --}}
    <form method="GET" action="{{ route('ingredients.index') }}"
          class="mb-6 flex flex-col sm:flex-row gap-3 items-stretch sm:items-center"
          role="search" aria-label="Buscar y filtrar ingredientes">
      <div class="relative flex-1">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
        </svg>
        <input type="text" name="search" id="search" value="{{ request('search') }}"
               placeholder="Buscar ingrediente..."
               aria-label="Buscar ingrediente por nombre"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 py-2 pl-9 pr-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
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
              class="inline-flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm py-2.5 px-4 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-colors">
        <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
        </svg>
        Filtrar
      </button>
      @if(request()->hasAny(['search', 'allergen']))
        <a href="{{ route('ingredients.index') }}"
           class="inline-flex items-center justify-center gap-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold text-sm py-2.5 px-4 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-colors"
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
         class="rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 p-4 mb-6 flex items-start gap-3 shadow-sm">
      <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Grid de tarjetas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      @forelse ($ingredients as $ingredient)

      <article class="flex flex-col bg-white dark:bg-gray-800
                       border border-gray-200 dark:border-gray-700
                       rounded-xl shadow-md hover:shadow-xl
                       transition-shadow duration-200 overflow-hidden"
               aria-label="Ingrediente {{ $ingredient->name }}">

        <div class="flex flex-col flex-1 p-5">
          {{-- Emoji + nombre --}}
          <div class="flex items-start gap-3 mb-4">
            <span class="text-3xl mt-0.5" role="img" aria-hidden="true">{{ $ingredient->ingredientEmoji() }}</span>
            <div class="flex-1 min-w-0">
              <h2 class="text-base font-bold text-gray-900 dark:text-gray-100 leading-snug">
                {{ $ingredient->name }}
              </h2>
              @if($ingredient->is_allergen && !empty($ingredient->allergen_types))
                <div class="mt-2 flex flex-wrap gap-1" role="list" aria-label="Alérgenos de {{ $ingredient->name }}">
                  @foreach($ingredient->allergen_types as $allergenSlug)
                    <x-allergen-badge :slug="$allergenSlug" />
                  @endforeach
                </div>
              @else
                <span class="inline-flex items-center gap-1 mt-1 text-xs text-gray-400 dark:text-gray-500">
                  Sin alérgenos
                </span>
              @endif
            </div>
          </div>

          {{-- Botones --}}
          <div class="mt-auto flex gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('ingredients.edit', $ingredient) }}"
               aria-label="Editar ingrediente {{ $ingredient->name }}"
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
            <form action="{{ route('ingredients.destroy', $ingredient) }}" method="POST"
                  onsubmit="return confirm('¿Eliminar el ingrediente «{{ addslashes($ingredient->name) }}»?');"
                  class="flex flex-1">
              @csrf
              @method('DELETE')
              <button type="submit"
                      aria-label="Eliminar ingrediente {{ $ingredient->name }}"
                      class="flex-1 inline-flex items-center justify-center gap-1.5 text-sm font-semibold
                             bg-white dark:bg-gray-700 text-red-600 dark:text-red-400
                             border border-red-200 dark:border-red-700
                             hover:bg-red-50 dark:hover:bg-red-900/20 shadow-sm hover:shadow
                             py-2 px-3 min-h-[44px] rounded-lg
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
                  border border-dashed border-gray-300 dark:border-gray-600 rounded-xl"
           role="status">
        <span class="p-4 bg-gray-100 dark:bg-gray-700 rounded-full mb-4 shadow-sm" aria-hidden="true">
          <svg class="h-8 w-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
          </svg>
        </span>
        <p class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-1">Sin ingredientes todavía</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Añade tu primer ingrediente a la despensa.</p>
        <a href="{{ route('ingredients.create') }}"
           class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white
                  font-semibold text-sm py-2 px-4 rounded-lg shadow-md
                  focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
          <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
          Crear primer ingrediente
        </a>
      </div>
      @endforelse
    </div>

    @if($ingredients->hasPages())
    <nav aria-label="Paginación de ingredientes" class="mt-8">
      {{ $ingredients->links() }}
    </nav>
    @endif

  </div>
</x-app-layout>
