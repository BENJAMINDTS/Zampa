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

        {{-- Alerta de éxito al guardar, editar o borrar --}}
        @if(session('success'))
        <div role="alert" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
          {{ session('success') }}
        </div>
        @endif

        {{-- Botón Crear --}}
        <div class="flex justify-end mb-4">
          <a href="{{ route('categories.create') }}" class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
            + Nueva Categoría
          </a>
        </div>

        {{-- Lista --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          @foreach ($categories as $category)
          <div class="p-6 border rounded-lg shadow-sm bg-white dark:bg-gray-700 flex flex-col justify-between">
            <div>
              <h3 class="text-xl font-bold text-orange-500">{{ $category->name }}</h3>
              <p class="text-gray-500 dark:text-gray-300 text-sm mt-1">
                Destino: <span class="font-semibold">{{ $category->destination === 'kitchen' ? 'Cocina' : 'Barra' }}</span>
              </p>
            </div>

            {{-- Botones de Acción --}}
            <div class="mt-4 flex justify-end space-x-2 border-t pt-4 dark:border-gray-600">
              <a href="{{ route('categories.edit', $category) }}" class="text-sm bg-indigo-50 text-indigo-600 hover:bg-indigo-100 border border-indigo-200 py-1 px-3 rounded">
                Editar
              </a>
              <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres borrar la categoría {{ $category->name }}?');">
                @csrf
                @method('DELETE')
                <button type="submit" aria-label="Borrar categoría {{ $category->name }}" class="text-sm bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 py-1 px-3 rounded">
                  Borrar
                </button>
              </form>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</x-app-layout>