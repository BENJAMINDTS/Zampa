{{-- @author SebastianBCF --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      Editar Categoría: {{ $category->name }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

        @if($errors->any())
        <div role="alert" aria-live="assertive"
             class="rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 p-4 mb-6 flex items-start gap-3">
          <svg class="h-5 w-5 text-red-500 dark:text-red-400 shrink-0 mt-0.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.538-1.333-3.308 0L3.06 19c-.77 1.333.192 3 1.732 3z"/>
          </svg>
          <div>
            <p class="text-sm font-medium text-red-800 dark:text-red-300">Corrige los siguientes errores:</p>
            <ul class="mt-1 text-sm text-red-700 dark:text-red-400 list-disc list-inside space-y-0.5">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        </div>
        @endif

        <form action="{{ route('categories.update', $category) }}" method="POST" class="space-y-6" novalidate>
          @csrf
          @method('PUT')

          <div>
            <x-input-label for="name" value="Nombre de la categoría" :required="true" />
            <x-text-input
              type="text"
              name="name"
              id="name"
              class="mt-1 block w-full @error('name') border-red-500 dark:border-red-500 @enderror"
              :value="old('name', $category->name)"
              aria-required="true"
              aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
              aria-describedby="{{ $errors->has('name') ? 'name-error' : '' }}"
            />
            @error('name')
              <p id="name-error" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <x-input-label for="destination" value="Destino de preparación" :required="true" />
            <select
              name="destination"
              id="destination"
              aria-required="true"
              aria-invalid="{{ $errors->has('destination') ? 'true' : 'false' }}"
              aria-describedby="{{ $errors->has('destination') ? 'destination-error' : '' }}"
              class="mt-1 block w-full h-10 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 dark:focus:ring-indigo-600 sm:text-sm @error('destination') border-red-500 dark:border-red-500 @enderror"
            >
              <option value="kitchen" @selected(old('destination', $category->destination) === 'kitchen')>Cocina — platos y postres</option>
              <option value="bar" @selected(old('destination', $category->destination) === 'bar')>Barra — bebidas y cócteles</option>
            </select>
            @error('destination')
              <p id="destination-error" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
          </div>

          <div class="flex items-center justify-end gap-3">
            <a href="{{ route('categories.index') }}"
               class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-md px-2 py-1 transition">
              Cancelar
            </a>
            <x-primary-button>
              Actualizar categoría
            </x-primary-button>
          </div>
        </form>

      </div>
    </div>
  </div>
</x-app-layout>
