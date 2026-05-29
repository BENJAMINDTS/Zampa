{{-- @author SebastianBCF --}}
<x-app-layout>
  <div class="max-w-xl mx-auto px-4 sm:px-6 py-8">

    {{-- Cabecera --}}
    <div class="flex items-center gap-3 mb-6">
      <a href="{{ route('categories.index') }}"
         aria-label="Volver al listado de categorías"
         class="p-1.5 rounded-lg text-gray-500 dark:text-gray-400
                border border-gray-300 dark:border-gray-600 shadow-sm
                hover:bg-gray-100 dark:hover:bg-gray-700
                focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
        <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
      </a>
      <div class="flex items-center gap-2">
        <span class="p-2 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg shadow-sm" aria-hidden="true">
          <svg class="h-5 w-5 text-indigo-700 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
        </span>
        <div>
          <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">Editar categoría</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $category->name }}</p>
        </div>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-md p-6">

      @if($errors->any())
      <div role="alert" aria-live="assertive"
           class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 p-4 mb-6 flex items-start gap-3 shadow-sm">
        <svg class="h-5 w-5 text-red-500 shrink-0 mt-0.5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <ul class="text-sm text-red-700 dark:text-red-400 list-disc list-inside space-y-0.5">
          @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
      </div>
      @endif

      <form action="{{ route('categories.update', $category) }}" method="POST" novalidate class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Nombre --}}
        <div>
          <label for="name" class="flex items-center gap-1.5 text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
            <svg aria-hidden="true" class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h8m-8 6h16"/>
            </svg>
            Nombre de la categoría
            <span aria-hidden="true" class="text-red-500">*</span>
            <span class="sr-only">(obligatorio)</span>
          </label>
          <input type="text" name="name" id="name"
                 value="{{ old('name', $category->name) }}"
                 required aria-required="true"
                 aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                 aria-describedby="{{ $errors->has('name') ? 'name-error' : '' }}"
                 class="block w-full rounded-lg border-gray-300 dark:border-gray-600
                        bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                        placeholder-gray-400 dark:placeholder-gray-500
                        shadow-sm focus:border-indigo-500 focus:ring-indigo-500
                        @error('name') border-red-400 dark:border-red-500 @enderror">
          @error('name')
          <p id="name-error" role="alert" class="mt-1.5 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
            <svg aria-hidden="true" class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $message }}
          </p>
          @enderror
        </div>

        {{-- Destino — selector visual --}}
        <fieldset>
          <legend class="flex items-center gap-1.5 text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
            <svg aria-hidden="true" class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Destino de preparación
            <span aria-hidden="true" class="text-red-500">*</span>
            <span class="sr-only">(obligatorio)</span>
          </legend>

          <div class="grid grid-cols-2 gap-3" x-data="{ dest: '{{ old('destination', $category->destination) }}' }">

            <label class="cursor-pointer">
              <input type="radio" name="destination" value="kitchen" x-model="dest" class="sr-only">
              <div class="flex flex-col items-center gap-2 p-4 rounded-xl border shadow-sm transition-all"
                   :class="dest === 'kitchen'
                     ? 'border-orange-400 dark:border-orange-500 bg-orange-50 dark:bg-orange-900/20 shadow-md ring-2 ring-orange-400 ring-offset-1'
                     : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 hover:shadow-md hover:border-orange-300'">
                <span class="p-2 rounded-lg transition-colors"
                      :class="dest === 'kitchen' ? 'bg-orange-100 dark:bg-orange-900/40' : 'bg-gray-100 dark:bg-gray-600'">
                  <svg class="h-6 w-6 transition-colors"
                       :class="dest === 'kitchen' ? 'text-orange-600 dark:text-orange-400' : 'text-gray-500 dark:text-gray-400'"
                       fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                </span>
                <span class="text-sm font-semibold"
                      :class="dest === 'kitchen' ? 'text-orange-700 dark:text-orange-300' : 'text-gray-700 dark:text-gray-300'">
                  Cocina
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400 text-center">Platos y postres</span>
              </div>
            </label>

            <label class="cursor-pointer">
              <input type="radio" name="destination" value="bar" x-model="dest" class="sr-only">
              <div class="flex flex-col items-center gap-2 p-4 rounded-xl border shadow-sm transition-all"
                   :class="dest === 'bar'
                     ? 'border-blue-400 dark:border-blue-500 bg-blue-50 dark:bg-blue-900/20 shadow-md ring-2 ring-blue-400 ring-offset-1'
                     : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 hover:shadow-md hover:border-blue-300'">
                <span class="p-2 rounded-lg transition-colors"
                      :class="dest === 'bar' ? 'bg-blue-100 dark:bg-blue-900/40' : 'bg-gray-100 dark:bg-gray-600'">
                  <svg class="h-6 w-6 transition-colors"
                       :class="dest === 'bar' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400'"
                       fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                  </svg>
                </span>
                <span class="text-sm font-semibold"
                      :class="dest === 'bar' ? 'text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300'">
                  Barra
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400 text-center">Bebidas y cócteles</span>
              </div>
            </label>

          </div>

          @error('destination')
          <p id="destination-error" role="alert" class="mt-1.5 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
            <svg aria-hidden="true" class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $message }}
          </p>
          @enderror
        </fieldset>

        {{-- Acciones --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
          <a href="{{ route('categories.index') }}"
             class="text-sm font-medium text-gray-600 dark:text-gray-400
                    hover:text-gray-900 dark:hover:text-gray-100
                    focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded px-2 py-1 transition-colors">
            Cancelar
          </a>
          <button type="submit"
                  class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white
                         font-semibold text-sm py-2.5 px-5 rounded-lg shadow-md
                         focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            Actualizar categoría
          </button>
        </div>

      </form>
    </div>
  </div>
</x-app-layout>
