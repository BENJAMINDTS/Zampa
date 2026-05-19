{{-- @author Ayrtonalania --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      Nuevo Menú del Día
    </h2>
  </x-slot>

  <div class="py-10">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

      {{-- ── Breadcrumb + volver ─────────────────────────────────── --}}
      <div class="flex items-center justify-between mb-6">
        <nav aria-label="Migas de pan" class="text-sm text-gray-500 dark:text-gray-400">
          <ol class="flex items-center gap-1.5">
            <li>
              <a href="{{ route('daily-menus.index') }}"
                 class="hover:underline text-orange-500 hover:text-orange-600 font-medium">
                Menú del Día
              </a>
            </li>
            <li aria-hidden="true" class="text-gray-300 dark:text-gray-600">/</li>
            <li class="text-gray-700 dark:text-gray-300 font-medium">Nuevo</li>
          </ol>
        </nav>
        <a href="{{ route('daily-menus.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400
                  hover:text-gray-900 dark:hover:text-gray-100
                  border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 min-h-[38px]
                  hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors
                  focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
          </svg>
          Volver al listado
        </a>
      </div>

      <x-toast />

      {{-- ── Aviso informativo ────────────────────────────────────── --}}
      <div class="flex items-start gap-3 bg-blue-50 dark:bg-blue-900/20
                  border border-blue-200 dark:border-blue-800
                  rounded-xl px-4 py-3 mb-6">
        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <p class="text-sm text-blue-700 dark:text-blue-300">
          Después de crear el menú podrás añadir sus <strong>secciones y productos</strong> desde la pantalla de configuración.
        </p>
      </div>

      {{-- ── Formulario ───────────────────────────────────────────── --}}
      <form action="{{ route('daily-menus.store') }}" method="POST" novalidate>
        @csrf

        {{-- ── Bloque 1: Información general ───────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mb-5">
          <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
              <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Información general
            </h3>
          </div>
          <div class="px-5 py-5 space-y-4">

            {{-- Título --}}
            <div>
              <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Título del menú
                <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
              </label>
              <input type="text" id="title" name="title"
                     value="{{ old('title') }}"
                     placeholder="Ej: Menú del día de lunes"
                     aria-required="true"
                     aria-describedby="{{ $errors->has('title') ? 'error-title' : 'hint-title' }}"
                     autofocus
                     class="w-full border rounded-lg px-3 py-2.5 text-sm
                            dark:bg-gray-700 dark:text-gray-100
                            focus:ring-orange-400 focus:border-orange-400 focus:outline-none
                            {{ $errors->has('title') ? 'border-red-400 dark:border-red-600' : 'border-gray-300 dark:border-gray-600' }}">
              <p id="hint-title" class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                El cliente lo verá en el banner de la carta digital.
              </p>
              @error('title')
                <p id="error-title" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                  <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                  {{ $message }}
                </p>
              @enderror
            </div>

            {{-- Descripción --}}
            <div>
              <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Descripción
                <span class="text-xs font-normal text-gray-400 ml-1">(opcional)</span>
              </label>
              <textarea id="description" name="description" rows="3"
                        placeholder="Ej: Incluye primer plato, segundo plato, postre y bebida"
                        aria-describedby="{{ $errors->has('description') ? 'error-description' : 'hint-description' }}"
                        class="w-full border rounded-lg px-3 py-2.5 text-sm resize-none
                               dark:bg-gray-700 dark:text-gray-100
                               focus:ring-orange-400 focus:border-orange-400 focus:outline-none
                               {{ $errors->has('description') ? 'border-red-400 dark:border-red-600' : 'border-gray-300 dark:border-gray-600' }}">{{ old('description') }}</textarea>
              <p id="hint-description" class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                Aparece debajo del título en el banner. Máx. 1000 caracteres.
              </p>
              @error('description')
                <p id="error-description" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                  <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                  {{ $message }}
                </p>
              @enderror
            </div>

          </div>
        </div>

        {{-- ── Bloque 2: Precio y disponibilidad ───────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mb-5">
          <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
              <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Precio y disponibilidad
            </h3>
          </div>
          <div class="px-5 py-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

              {{-- Precio --}}
              <div>
                <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Precio
                  <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
                </label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium" aria-hidden="true">€</span>
                  <input type="number" id="price" name="price"
                         value="{{ old('price') }}"
                         step="0.01" min="0.01"
                         placeholder="0.00"
                         aria-required="true"
                         aria-describedby="{{ $errors->has('price') ? 'error-price' : '' }}"
                         class="w-full border rounded-lg pl-7 pr-3 py-2.5 text-sm
                                dark:bg-gray-700 dark:text-gray-100
                                focus:ring-orange-400 focus:border-orange-400 focus:outline-none
                                {{ $errors->has('price') ? 'border-red-400 dark:border-red-600' : 'border-gray-300 dark:border-gray-600' }}">
                </div>
                @error('price')
                  <p id="error-price" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                  </p>
                @enderror
              </div>

              {{-- Máximo por día --}}
              <div>
                <label for="max_per_day" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Máximo de menús por día
                  <span class="text-xs font-normal text-gray-400 ml-1">(opcional)</span>
                </label>
                <input type="number" id="max_per_day" name="max_per_day"
                       value="{{ old('max_per_day') }}"
                       min="1" placeholder="Sin límite"
                       aria-describedby="hint-max_per_day {{ $errors->has('max_per_day') ? 'error-max_per_day' : '' }}"
                       class="w-full border rounded-lg px-3 py-2.5 text-sm
                              dark:bg-gray-700 dark:text-gray-100
                              focus:ring-orange-400 focus:border-orange-400 focus:outline-none
                              {{ $errors->has('max_per_day') ? 'border-red-400 dark:border-red-600' : 'border-gray-300 dark:border-gray-600' }}">
                <p id="hint-max_per_day" class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                  Cuando se alcance el límite, el botón del menú se desactiva en la carta.
                </p>
                @error('max_per_day')
                  <p id="error-max_per_day" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                  </p>
                @enderror
              </div>

            </div>

            {{-- Activo --}}
            <div class="mt-4">
              <label for="is_active"
                     class="flex items-start gap-3 p-3.5 rounded-lg border cursor-pointer
                            border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/40
                            transition-colors">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1"
                       {{ old('is_active', true) ? 'checked' : '' }}
                       class="h-4 w-4 mt-0.5 text-orange-500 border-gray-300 rounded
                              focus:ring-orange-400 flex-shrink-0">
                <div>
                  <span class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Menú activo desde el primer momento
                  </span>
                  <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Aparecerá en la carta digital en el día programado. Puedes desactivarlo después si lo necesitas.
                  </span>
                </div>
              </label>
            </div>

          </div>
        </div>

        {{-- ── Bloque 3: Programación ───────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mb-6"
             x-data="{ mode: '{{ old('day_of_week') ? 'week' : (old('specific_date') ? 'date' : 'week') }}' }">
          <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
              <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              Programación
              <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
              Define cuándo estará disponible este menú en la carta.
            </p>
          </div>
          <div class="px-5 py-5">

            <fieldset>
              <legend class="sr-only">Tipo de programación</legend>
              <div class="grid grid-cols-2 gap-3 mb-4">

                <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors"
                       :class="mode === 'week'
                         ? 'border-orange-400 bg-orange-50 dark:bg-orange-900/20'
                         : 'border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/40'">
                  <input type="radio" name="_mode" value="week"
                         x-model="mode"
                         class="h-4 w-4 text-orange-500 border-gray-300 focus:ring-orange-400">
                  <div>
                    <span class="block text-sm font-medium text-gray-700 dark:text-gray-200">Día de la semana</span>
                    <span class="block text-xs text-gray-500 dark:text-gray-400">Se repite cada semana</span>
                  </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors"
                       :class="mode === 'date'
                         ? 'border-orange-400 bg-orange-50 dark:bg-orange-900/20'
                         : 'border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/40'">
                  <input type="radio" name="_mode" value="date"
                         x-model="mode"
                         class="h-4 w-4 text-orange-500 border-gray-300 focus:ring-orange-400">
                  <div>
                    <span class="block text-sm font-medium text-gray-700 dark:text-gray-200">Fecha específica</span>
                    <span class="block text-xs text-gray-500 dark:text-gray-400">Solo ese día concreto</span>
                  </div>
                </label>

              </div>

              {{-- Selector día de semana --}}
              <div x-show="mode === 'week'" x-cloak>
                <label for="day_of_week" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Día de la semana
                </label>
                <select id="day_of_week" name="day_of_week"
                        :required="mode === 'week'"
                        aria-describedby="{{ $errors->has('day_of_week') ? 'error-day_of_week' : '' }}"
                        class="w-full border rounded-lg px-3 py-2.5 text-sm
                               dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100
                               focus:ring-orange-400 focus:border-orange-400 focus:outline-none
                               {{ $errors->has('day_of_week') ? 'border-red-400' : 'border-gray-300' }}">
                  <option value="">— Selecciona un día —</option>
                  @foreach(['monday' => 'Lunes', 'tuesday' => 'Martes', 'wednesday' => 'Miércoles', 'thursday' => 'Jueves', 'friday' => 'Viernes', 'saturday' => 'Sábado', 'sunday' => 'Domingo'] as $val => $label)
                    <option value="{{ $val }}" {{ old('day_of_week') === $val ? 'selected' : '' }}>
                      {{ $label }}
                    </option>
                  @endforeach
                </select>
                @error('day_of_week')
                  <p id="error-day_of_week" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                  </p>
                @enderror
              </div>

              {{-- Input fecha específica --}}
              <div x-show="mode === 'date'" x-cloak>
                <label for="specific_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Fecha
                </label>
                <input type="date" id="specific_date" name="specific_date"
                       value="{{ old('specific_date') }}"
                       min="{{ date('Y-m-d') }}"
                       :required="mode === 'date'"
                       aria-describedby="{{ $errors->has('specific_date') ? 'error-specific_date' : 'hint-specific_date' }}"
                       class="w-full border rounded-lg px-3 py-2.5 text-sm
                              dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100
                              focus:ring-orange-400 focus:border-orange-400 focus:outline-none
                              {{ $errors->has('specific_date') ? 'border-red-400' : 'border-gray-300' }}">
                <p id="hint-specific_date" class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                  Una excepción de fecha tiene prioridad sobre el menú semanal del mismo día.
                </p>
                @error('specific_date')
                  <p id="error-specific_date" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                  </p>
                @enderror
              </div>

            </fieldset>
          </div>
        </div>

        {{-- ── Acciones ─────────────────────────────────────────── --}}
        <div class="flex items-center justify-between gap-3">
          <a href="{{ route('daily-menus.index') }}"
             class="inline-flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400
                    hover:text-gray-900 dark:hover:text-gray-100
                    border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 min-h-[44px]
                    hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors
                    focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Cancelar
          </a>
          <button type="submit"
                  class="inline-flex items-center gap-2 text-sm font-semibold
                         bg-orange-500 hover:bg-orange-600 text-white
                         rounded-lg px-5 py-2.5 min-h-[44px] transition-colors
                         focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Crear menú del día
          </button>
        </div>

      </form>
    </div>
  </div>
</x-app-layout>
