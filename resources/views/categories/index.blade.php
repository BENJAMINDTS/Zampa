{{-- @author SebastianBCF --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      {{ __('Mis Categorías') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="p-6 text-gray-900 dark:text-gray-100">

        {{-- Flash de éxito — triple redundancia: icono + color + texto --}}
        @if(session('success'))
        <div role="alert" aria-live="polite"
             class="rounded-md bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 p-4 mb-6 flex items-start gap-3">
          <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <p class="text-sm text-emerald-800 dark:text-emerald-300">{{ session('success') }}</p>
        </div>
        @endif

        {{-- Botón Crear --}}
        <div class="flex justify-end mb-6">
          <a href="{{ route('categories.create') }}"
             class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm py-2.5 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition">
            <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva Categoría
          </a>
        </div>

        {{-- Grid de tarjetas --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          @forelse ($categories as $category)
          <div class="p-6 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm bg-white dark:bg-gray-700 flex flex-col justify-between">
            <div>
              <h3 class="text-base font-bold text-indigo-600 dark:text-indigo-400">{{ $category->name }}</h3>
              <p class="text-gray-500 dark:text-gray-300 text-sm mt-1 leading-relaxed">
                Destino:
                <span class="font-semibold inline-flex items-center gap-1">
                  @if($category->destination === 'kitchen')
                    <svg class="h-3.5 w-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Cocina
                  @else
                    <svg class="h-3.5 w-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Barra
                  @endif
                </span>
              </p>
            </div>

            {{-- Botones de Acción --}}
            <div class="mt-4 flex justify-end gap-2 border-t pt-4 dark:border-gray-600">
              <a href="{{ route('categories.edit', $category) }}"
                 aria-label="Editar categoría {{ $category->name }}"
                 class="inline-flex items-center gap-1.5 text-sm bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 border border-indigo-200 dark:border-indigo-700 py-1.5 px-3 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition">
                <svg class="h-3.5 w-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Editar
              </a>
              <form action="{{ route('categories.destroy', $category) }}" method="POST"
                    onsubmit="return confirm('¿Seguro que quieres borrar la categoría {{ addslashes($category->name) }}?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        aria-label="Borrar categoría {{ $category->name }}"
                        class="inline-flex items-center gap-1.5 text-sm bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50 border border-red-200 dark:border-red-700 py-1.5 px-3 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 transition">
                  <svg class="h-3.5 w-3.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                  Borrar
                </button>
              </form>
            </div>
          </div>
          @empty
          <p class="text-gray-500 dark:text-gray-400 col-span-3">
            Aún no tienes categorías. <a href="{{ route('categories.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Crea la primera</a>.
          </p>
          @endforelse
        </div>

        @if($categories->hasPages())
        <nav aria-label="Paginación de categorías" class="mt-6">
          {{ $categories->links() }}
        </nav>
        @endif

      </div>
    </div>
  </div>
</x-app-layout>
