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

          <form action="{{ route('ingredients.update', $ingredient) }}" method="POST" class="space-y-4 sm:space-y-6">
            @csrf
            @method('PUT')

            {{-- Campo: Nombre --}}
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Nombre del ingrediente
              </label>
              <input
                type="text"
                name="name"
                id="name"
                value="{{ old('name', $ingredient->name) }}"
                required
                aria-required="true"
                @error('name') aria-describedby="error-name" @enderror
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-green-500 focus:ring-green-500 focus:outline-none focus:ring-2 focus:ring-offset-2"
              >
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
                  class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:text-white shadow-sm focus:border-orange-500 focus:ring-orange-500"
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
              <a
                href="{{ route('ingredients.index') }}"
                class="text-gray-600 dark:text-gray-400 hover:underline text-sm focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 rounded"
              >
                Cancelar
              </a>
              <button
                type="submit"
                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
              >
                Actualizar Ingrediente
              </button>
            </div>

          </form>

        </div>
      </div>
    </div>
  </div>
</x-app-layout>
