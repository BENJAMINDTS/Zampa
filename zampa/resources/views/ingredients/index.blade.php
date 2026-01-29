<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      {{ __('Despensa de Ingredientes') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="p-6 text-gray-900 dark:text-gray-100">
        <div class="flex justify-end mb-4">
          <a href="{{ route('ingredients.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            + Nuevo Ingrediente
          </a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          @foreach ($ingredients as $ingredient)
          <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 shadow-sm text-center">
            <div class="text-2xl mb-2">🍅</div>
            <h3 class="font-bold text-gray-700 dark:text-gray-300">{{ $ingredient->name }}</h3>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</x-app-layout>