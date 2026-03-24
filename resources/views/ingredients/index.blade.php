<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      {{ __('Despensa de Ingredientes') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="p-6 text-gray-900 dark:text-gray-100">

        {{-- Alerta de éxito al guardar, editar o borrar --}}
        @if(session('success'))
        <div role="alert" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
          {{ session('success') }}
        </div>
        @endif

        <div class="flex justify-end mb-4">
          <a href="{{ route('ingredients.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            + Nuevo Ingrediente
          </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          @foreach ($ingredients as $ingredient)
          <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 shadow-sm flex flex-col justify-between">
            <div class="text-center">
              <div class="text-2xl mb-2">🍅</div>
              <h3 class="font-bold text-gray-700 dark:text-gray-300">{{ $ingredient->name }}</h3>
              @if($ingredient->is_allergen)
              <span class="mt-1 inline-block text-xs font-semibold text-amber-700 dark:text-amber-400 bg-amber-100 dark:bg-amber-900 px-2 py-0.5 rounded-full">
                Alérgeno
              </span>
              @endif
            </div>

            {{-- Botones de Acción --}}
            <div class="mt-4 flex flex-col sm:flex-row justify-end gap-2 border-t pt-3 dark:border-gray-600">
              <a
                href="{{ route('ingredients.edit', $ingredient) }}"
                class="text-sm text-center bg-indigo-50 text-indigo-600 hover:bg-indigo-100 border border-indigo-200 py-1 px-3 rounded focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2"
              >
                Editar
              </a>
              <form
                action="{{ route('ingredients.destroy', $ingredient) }}"
                method="POST"
                onsubmit="return confirm('¿Seguro que quieres borrar el ingrediente {{ $ingredient->name }}?');"
              >
                @csrf
                @method('DELETE')
                <button
                  type="submit"
                  aria-label="Borrar ingrediente {{ $ingredient->name }}"
                  class="w-full text-sm bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 py-1 px-3 rounded focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2"
                >
                  Borrar
                </button>
              </form>
            </div>
          </div>
          @endforeach
        </div>

        @if($ingredients->hasPages())
        <nav aria-label="Paginación de ingredientes" class="mt-4">
          {{ $ingredients->links() }}
        </nav>
        @endif
      </div>
    </div>
  </div>
</x-app-layout>