<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      {{ __('Mis Categorías') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="p-6 text-gray-900 dark:text-gray-100">

        {{-- Botón Crear --}}
        <div class="flex justify-end mb-4">
          <a href="{{ route('categories.create') }}" class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
            + Nueva Categoría
          </a>
        </div>

        {{-- Lista --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          @foreach ($categories as $category)
          <div class="p-6 border rounded-lg shadow-sm bg-white dark:bg-gray-700">
            <h3 class="text-xl font-bold text-orange-500">{{ $category->name }}</h3>
            <p class="text-gray-500 text-sm">Destino: {{ $category->destination }}</p>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</x-app-layout>