<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      {{ __('Nueva Categoría') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">

        <form method="POST" action="{{ route('categories.store') }}">
          @csrf

          {{-- Nombre --}}
          <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300 mb-2">Nombre</label>
            <input type="text" name="name" required class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white">
          </div>

          {{-- Destino --}}
          <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300 mb-2">Destino</label>
            <select name="destination" class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white">
              <option value="kitchen">Cocina 🍳</option>
              <option value="bar">Barra 🍺</option>
            </select>
          </div>

          <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
            Guardar
          </button>
        </form>

      </div>
    </div>
  </div>
</x-app-layout>