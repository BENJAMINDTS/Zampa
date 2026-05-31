{{-- @author SebastianBCF --}}
{{-- @author AyrtonAlania --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl sm:text-2xl text-gray-800 dark:text-gray-200 leading-tight">
      {{ __('Nuevo Ingrediente') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 sm:p-6">

          <form method="POST" action="{{ route('ingredients.store') }}" class="space-y-4 sm:space-y-6" novalidate>
            @csrf

            @if($errors->any())
            <div role="alert" aria-live="assertive"
                 class="rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 p-4 flex items-start gap-3">
              <svg class="h-5 w-5 text-red-500 dark:text-red-400 shrink-0 mt-0.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.538-1.333-3.308 0L3.06 19c-.77 1.333.192 3 1.732 3z"/>
              </svg>
              <ul class="text-sm text-red-700 dark:text-red-400 list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
              </ul>
            </div>
            @endif

            {{-- Campo: Nombre --}}
            <div>
              <x-input-label for="name" value="Nombre del ingrediente" :required="true" />
              <x-text-input
                type="text"
                name="name"
                id="name"
                class="mt-1 block w-full @error('name') border-red-500 dark:border-red-500 @enderror"
                :value="old('name')"
                aria-required="true"
                aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                aria-describedby="{{ $errors->has('name') ? 'error-name' : '' }}"
                placeholder="Ej: Pan Brioche"
              />
              @error('name')
                <p id="error-name" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ $message }}
                </p>
              @enderror
            </div>

            {{-- Campo: Alérgenos UE --}}
            <fieldset>
              <legend class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Alérgenos (Reglamento UE 1169/2011)
              </legend>
              <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                Selecciona todos los alérgenos que contiene este ingrediente.
              </p>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach(\App\Models\Ingredient::ALLERGEN_TYPES as $slug => $label)
                  @php $checked = in_array($slug, old('allergen_types', []), true); @endphp
                  <div class="flex items-center gap-2">
                    <input
                      type="checkbox"
                      name="allergen_types[]"
                      id="allergen_{{ $slug }}"
                      value="{{ $slug }}"
                      {{ $checked ? 'checked' : '' }}
                      class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-green-600 focus:ring-green-500 focus:outline-none focus:ring-2 focus:ring-offset-2"
                    >
                    <label for="allergen_{{ $slug }}" class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                      <img src="{{ asset('images/allergens/' . $slug . '.svg') }}" alt="" aria-hidden="true" class="h-5 w-5 object-contain">
                      {{ $label }}
                    </label>
                  </div>
                @endforeach
              </div>
            </fieldset>

            {{-- Acciones --}}
            <div class="flex items-center justify-end space-x-3 pt-2">
              <a href="{{ route('ingredients.index') }}"
                 class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-md px-3 py-2 transition">
                Cancelar
              </a>
              <x-primary-button>Guardar ingrediente</x-primary-button>
            </div>

          </form>

        </div>
      </div>
    </div>
  </div>
</x-app-layout>
