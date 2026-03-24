<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      {{ __('Nuevo Ingrediente') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-md mx-auto sm:px-6 lg:px-8">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
        <form method="POST" action="{{ route('ingredients.store') }}">
          @csrf
          <div class="mb-6">
            <label class="block text-gray-700 dark:text-gray-300 mb-2 font-bold">Nombre</label>
            <input type="text" name="name" required placeholder="Ej: Pan Brioche"
              class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white">
          </div>
          <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 font-bold">
            Guardar
          </button>
        </form>
      </div>
    </div>
  </div>
</x-app-layout>