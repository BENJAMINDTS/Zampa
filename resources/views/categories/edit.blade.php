{{-- @author SebastianBCF --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      Editar Categoría: {{ $category->name }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
        <form action="{{ route('categories.update', $category) }}" method="POST" class="space-y-6">
          @csrf
          @method('PUT')
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de la categoría</label>
            <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-orange-500 focus:ring-orange-500">
          </div>

          <div>
            <label for="destination" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Destino de preparación</label>
            <select name="destination" id="destination" required
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-orange-500 focus:ring-orange-500">
              <option value="kitchen" {{ $category->destination == 'kitchen' ? 'selected' : '' }}>Cocina (Platos, postres...)</option>
              <option value="bar" {{ $category->destination == 'bar' ? 'selected' : '' }}>Barra (Bebidas, cócteles...)</option>
            </select>
          </div>

          <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('categories.index') }}" class="text-gray-600 dark:text-gray-400 hover:underline text-sm">
              Cancelar
            </a>
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded shadow-sm">
              Actualizar Categoría
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
</x-app-layout>