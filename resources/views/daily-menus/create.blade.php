{{-- @author Ayrtonalania --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      Nuevo Menú del Día
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 sm:p-8">

        <form action="{{ route('daily-menus.store') }}" method="POST" novalidate>
          @csrf

          <div class="space-y-5">

            {{-- Título --}}
            <div>
              <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Título <span class="text-red-500" aria-hidden="true">*</span>
              </label>
              <input type="text" id="title" name="title"
                     value="{{ old('title') }}"
                     aria-required="true"
                     aria-describedby="{{ $errors->has('title') ? 'error-title' : '' }}"
                     class="w-full border rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100
                            {{ $errors->has('title') ? 'border-red-500' : 'border-gray-300' }}">
              @error('title')
                <p id="error-title" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
              @enderror
            </div>

            {{-- Descripción --}}
            <div>
              <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Descripción
              </label>
              <textarea id="description" name="description" rows="3"
                        aria-describedby="{{ $errors->has('description') ? 'error-description' : '' }}"
                        class="w-full border rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100
                               {{ $errors->has('description') ? 'border-red-500' : 'border-gray-300' }}">{{ old('description') }}</textarea>
              @error('description')
                <p id="error-description" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
              @enderror
            </div>

            {{-- Precio y Max por día --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Precio (€) <span class="text-red-500" aria-hidden="true">*</span>
                </label>
                <input type="number" id="price" name="price"
                       value="{{ old('price') }}"
                       step="0.01" min="0.01"
                       aria-required="true"
                       aria-describedby="{{ $errors->has('price') ? 'error-price' : '' }}"
                       class="w-full border rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100
                              {{ $errors->has('price') ? 'border-red-500' : 'border-gray-300' }}">
                @error('price')
                  <p id="error-price" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
              </div>
              <div>
                <label for="max_per_day" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Máximo por día
                </label>
                <input type="number" id="max_per_day" name="max_per_day"
                       value="{{ old('max_per_day') }}"
                       min="1"
                       aria-describedby="hint-max_per_day {{ $errors->has('max_per_day') ? 'error-max_per_day' : '' }}"
                       class="w-full border rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100
                              {{ $errors->has('max_per_day') ? 'border-red-500' : 'border-gray-300' }}">
                <p id="hint-max_per_day" class="mt-1 text-xs text-gray-400">Vacío = sin límite</p>
                @error('max_per_day')
                  <p id="error-max_per_day" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
              </div>
            </div>

            {{-- Activo --}}
            <div class="flex items-center gap-3">
              <input type="hidden" name="is_active" value="0">
              <input type="checkbox" id="is_active" name="is_active" value="1"
                     {{ old('is_active', true) ? 'checked' : '' }}
                     class="h-4 w-4 text-orange-500 border-gray-300 rounded">
              <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Menú activo
              </label>
            </div>

            {{-- Selector de modo: día de semana vs fecha específica --}}
            <div x-data="{
                   mode: '{{ old('day_of_week') ? 'week' : (old('specific_date') ? 'date' : 'week') }}'
                 }">
              <fieldset>
                <legend class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Programación <span class="text-red-500" aria-hidden="true">*</span>
                </legend>
                <div class="flex gap-4 mb-3">
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="_mode" value="week"
                           x-model="mode"
                           class="h-4 w-4 text-orange-500 border-gray-300">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Día de la semana</span>
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="_mode" value="date"
                           x-model="mode"
                           class="h-4 w-4 text-orange-500 border-gray-300">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Fecha específica</span>
                  </label>
                </div>

                {{-- Selector día de semana --}}
                <div x-show="mode === 'week'" x-cloak>
                  <label for="day_of_week" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                    Día de la semana
                  </label>
                  <select id="day_of_week" name="day_of_week"
                          :required="mode === 'week'"
                          aria-describedby="{{ $errors->has('day_of_week') ? 'error-day_of_week' : '' }}"
                          class="w-full border rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100
                                 {{ $errors->has('day_of_week') ? 'border-red-500' : 'border-gray-300' }}">
                    <option value="">— Seleccionar —</option>
                    @foreach(['monday' => 'Lunes', 'tuesday' => 'Martes', 'wednesday' => 'Miércoles', 'thursday' => 'Jueves', 'friday' => 'Viernes', 'saturday' => 'Sábado', 'sunday' => 'Domingo'] as $val => $label)
                      <option value="{{ $val }}" {{ old('day_of_week') === $val ? 'selected' : '' }}>
                        {{ $label }}
                      </option>
                    @endforeach
                  </select>
                  @error('day_of_week')
                    <p id="error-day_of_week" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                  @enderror
                </div>

                {{-- Input fecha específica --}}
                <div x-show="mode === 'date'" x-cloak>
                  <label for="specific_date" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                    Fecha
                  </label>
                  <input type="date" id="specific_date" name="specific_date"
                         value="{{ old('specific_date') }}"
                         min="{{ date('Y-m-d') }}"
                         :required="mode === 'date'"
                         aria-describedby="{{ $errors->has('specific_date') ? 'error-specific_date' : '' }}"
                         class="w-full border rounded-md px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100
                                {{ $errors->has('specific_date') ? 'border-red-500' : 'border-gray-300' }}">
                  @error('specific_date')
                    <p id="error-specific_date" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                  @enderror
                </div>
              </fieldset>
            </div>

          </div>

          {{-- Acciones --}}
          <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('daily-menus.index') }}"
               class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md">
              Cancelar
            </a>
            <button type="submit"
                    class="px-4 py-2 text-sm text-white bg-orange-500 hover:bg-orange-700 rounded-md font-medium">
              Guardar
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</x-app-layout>
