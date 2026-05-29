{{-- @author SebastianBCF --}}
{{-- @author AyrtonAlania --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl sm:text-2xl text-gray-800 dark:text-gray-200 leading-tight">
      Editar Ingrediente: {{ $ingredient->name }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 sm:p-6">

          <form action="{{ route('ingredients.update', $ingredient) }}" method="POST" class="space-y-4 sm:space-y-6" novalidate>
            @csrf
            @method('PUT')

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
                :value="old('name', $ingredient->name)"
                aria-required="true"
                aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                aria-describedby="{{ $errors->has('name') ? 'error-name' : '' }}"
              />
              @error('name')
                <p id="error-name" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ $message }}
                </p>
              @enderror
            </div>

            {{-- Campo: Es alérgeno --}}
            <fieldset x-data="{ isAllergen: {{ $ingredient->is_allergen ? 'true' : 'false' }} }">
              <legend class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Clasificación alérgeno
              </legend>
              <div class="flex items-center gap-3">
                <input type="hidden" name="is_allergen" value="0">
                <input
                  type="checkbox"
                  name="is_allergen"
                  id="is_allergen"
                  value="1"
                  {{ old('is_allergen', $ingredient->is_allergen) ? 'checked' : '' }}
                  @change="isAllergen = $event.target.checked"
                  class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-green-600 focus:ring-green-500 focus:outline-none focus:ring-2 focus:ring-offset-2"
                >
                <label for="is_allergen" class="text-sm text-gray-700 dark:text-gray-300">
                  Marcar como alérgeno
                </label>
              </div>

              {{-- Selector de tipo de alérgeno oficial UE — visible solo si is_allergen está marcado --}}
              <div x-show="isAllergen" x-cloak class="mt-4">
                <label for="allergen_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Tipo de alérgeno oficial (UE)
                </label>
                <select
                  name="allergen_type"
                  id="allergen_type"
                  class="w-full h-10 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-600 dark:focus:ring-indigo-600 sm:text-sm"
                >
                  <option value="">— Selecciona el alérgeno oficial —</option>
                  @foreach(\App\Models\Ingredient::ALLERGEN_TYPES as $slug => $label)
                    <option value="{{ $slug }}" {{ old('allergen_type', $ingredient->allergen_type) === $slug ? 'selected' : '' }}>
                      {{ $label }}
                    </option>
                  @endforeach
                </select>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Según el Reglamento UE 1169/2011 de información alimentaria.
                </p>
              </div>
            </fieldset>

            {{-- Acciones --}}
            <div class="flex items-center justify-end space-x-3 pt-2">
              <a href="{{ route('ingredients.index') }}"
                 class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-md px-2 py-1 transition">
                Cancelar
              </a>
              <x-primary-button>Actualizar ingrediente</x-primary-button>
            </div>

          </form>

        </div>
      </div>
    </div>
  </div>
</x-app-layout>
