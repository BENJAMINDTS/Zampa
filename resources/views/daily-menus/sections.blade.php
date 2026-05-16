{{-- @author Ayrtonalania --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      Configurar — {{ $dailyMenu->title }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

      {{-- Breadcrumb --}}
      <nav aria-label="Migas de pan" class="mb-4 text-sm text-gray-500 dark:text-gray-400">
        <ol class="flex items-center gap-1">
          <li><a href="{{ route('daily-menus.index') }}" class="hover:underline text-orange-500">Menú del Día</a></li>
          <li aria-hidden="true">/</li>
          <li class="text-gray-700 dark:text-gray-300">Configurar secciones</li>
        </ol>
      </nav>

      {{-- Flash messages --}}
      @if(session('success'))
        <div role="alert" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
          {{ session('success') }}
        </div>
      @endif
      @if(session('error'))
        <div role="alert" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
          {{ session('error') }}
        </div>
      @endif

      {{-- ============================================================
           BLOQUE A — Secciones del menú
      ============================================================ --}}
      <section aria-labelledby="secciones-titulo" class="mb-10">
        <h3 id="secciones-titulo" class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
          Secciones del menú
        </h3>

        @php
          $sectionLabels = [
            'first_course'  => 'Primer Plato',
            'second_course' => 'Segundo Plato',
            'dessert'       => 'Postre',
            'coffee'        => 'Café / Infusión',
            'drink'         => 'Bebida',
            'bread'         => 'Pan',
          ];
          $existingSections = $dailyMenu->sections->keyBy('type');
        @endphp

        <div class="space-y-4">
          @foreach($sectionTypes as $type)
            @php $section = $existingSections->get($type); @endphp
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-4 sm:p-5">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <h4 class="font-semibold text-gray-800 dark:text-gray-200">
                  {{ $sectionLabels[$type] }}
                </h4>
                @if(!$section)
                  <form action="{{ route('daily-menus.sections', $dailyMenu) }}" method="POST">
                    @csrf
                    <input type="hidden" name="_action" value="create_section">
                    <input type="hidden" name="section_type" value="{{ $type }}">
                    <button type="submit"
                            aria-label="Añadir sección {{ $sectionLabels[$type] }}"
                            class="text-sm bg-orange-50 text-orange-600 hover:bg-orange-100 border border-orange-200 py-1 px-3 rounded dark:bg-orange-900/20 dark:text-orange-300 dark:border-orange-700">
                      + Añadir sección
                    </button>
                  </form>
                @endif
              </div>

              @if($section)
                {{-- Configuración de la sección existente --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                  <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_required_{{ $section->id }}"
                           class="h-4 w-4 text-orange-500 border-gray-300 rounded"
                           {{ $section->is_required ? 'checked' : '' }} disabled>
                    <label for="is_required_{{ $section->id }}" class="text-sm text-gray-600 dark:text-gray-400">
                      Obligatoria
                    </label>
                  </div>
                  <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_free_{{ $section->id }}"
                           class="h-4 w-4 text-orange-500 border-gray-300 rounded"
                           {{ $section->is_free ? 'checked' : '' }} disabled>
                    <label for="is_free_{{ $section->id }}" class="text-sm text-gray-600 dark:text-gray-400">
                      Incluida en precio
                    </label>
                  </div>
                  <div class="flex items-center gap-2">
                    <label for="max_qty_{{ $section->id }}" class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">
                      Máx. elecciones:
                    </label>
                    <input type="number" id="max_qty_{{ $section->id }}" value="{{ $section->max_quantity }}"
                           min="1" disabled
                           class="w-16 border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm
                                  dark:bg-gray-700 dark:text-gray-100 bg-gray-50">
                  </div>
                </div>

                @if($section->is_free && $type === 'bread')
                  <p class="text-xs text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded px-3 py-2 mb-3">
                    El pan aparecerá como ítem gratuito (precio 0,00 €) en el panel de cocina.
                  </p>
                @endif

                {{-- Sync de productos --}}
                <form action="{{ route('daily-menus.sync-products', [$dailyMenu, $section]) }}" method="POST">
                  @csrf
                  <div class="mb-3">
                    <label for="products_{{ $section->id }}"
                           class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                      Productos disponibles en esta sección
                    </label>
                    <select id="products_{{ $section->id }}" name="product_ids[]"
                            multiple size="6"
                            aria-label="Seleccionar productos para {{ $sectionLabels[$type] }}"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-2 py-1 text-sm
                                   dark:bg-gray-700 dark:text-gray-100">
                      @php
                        $assignedProductIds = $section->products->pluck('id')->toArray();
                        $grouped = $products->groupBy(fn($p) => $p->category?->name ?? 'Sin categoría');
                      @endphp
                      @foreach($grouped as $categoryName => $categoryProducts)
                        <optgroup label="{{ $categoryName }}">
                          @foreach($categoryProducts as $product)
                            <option value="{{ $product->id }}"
                                    {{ in_array($product->id, $assignedProductIds) ? 'selected' : '' }}>
                              {{ $product->name }}
                            </option>
                          @endforeach
                        </optgroup>
                      @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Mantén Ctrl (o Cmd) para seleccionar múltiples productos.</p>
                  </div>
                  <div class="flex justify-end">
                    <button type="submit"
                            aria-label="Guardar productos de {{ $sectionLabels[$type] }}"
                            class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white py-1.5 px-4 rounded">
                      Guardar productos
                    </button>
                  </div>
                </form>

              @else
                <p class="text-sm text-gray-400 dark:text-gray-500 italic">
                  Esta sección no está configurada para este menú.
                </p>
              @endif
            </div>
          @endforeach
        </div>
      </section>

      {{-- ============================================================
           BLOQUE B — Configuración de tiempos (rondas)
      ============================================================ --}}
      <section aria-labelledby="timing-titulo"
               x-data="{
                 rounds: {{ json_encode(
                   $dailyMenu->timingRules->count() > 0
                     ? $dailyMenu->timingRules->map(fn($r) => [
                         'round_number'           => $r->round_number,
                         'default_delay_minutes'  => $r->default_delay_minutes,
                         'estimated_prep_minutes' => $r->estimated_prep_minutes,
                         'section_types'          => $r->section_types ?? [],
                       ])->values()->toArray()
                     : [['round_number' => 1, 'default_delay_minutes' => 0, 'estimated_prep_minutes' => 15, 'section_types' => []]]
                 ) }}
               }">
        <h3 id="timing-titulo" class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
          Rondas de envío a cocina / barra
        </h3>

        <form action="{{ route('daily-menus.timing', $dailyMenu) }}" method="POST">
          @csrf

          <div class="space-y-4">
            <template x-for="(round, index) in rounds" :key="index">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-4 sm:p-5">
                <div class="flex items-center justify-between mb-4">
                  <h4 class="font-semibold text-gray-700 dark:text-gray-300">
                    Ronda <span x-text="round.round_number"></span>
                  </h4>
                  <button type="button"
                          x-show="rounds.length > 1"
                          @click="rounds.splice(index, 1); rounds.forEach((r, i) => r.round_number = i + 1)"
                          aria-label="Eliminar ronda"
                          class="text-sm text-red-500 hover:text-red-700">
                    Eliminar ronda
                  </button>
                </div>

                <input type="hidden" :name="`rounds[${index}][round_number]`" :value="round.round_number">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                  <div>
                    <label :for="`delay_${index}`"
                           class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                      Tiempo sugerido al cliente (min)
                    </label>
                    <input type="number"
                           :id="`delay_${index}`"
                           :name="`rounds[${index}][default_delay_minutes]`"
                           x-model.number="round.default_delay_minutes"
                           min="0" required
                           aria-required="true"
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm
                                  dark:bg-gray-700 dark:text-gray-100">
                  </div>
                  <div>
                    <label :for="`prep_${index}`"
                           class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                      Tiempo de preparación (min)
                    </label>
                    <input type="number"
                           :id="`prep_${index}`"
                           :name="`rounds[${index}][estimated_prep_minutes]`"
                           x-model.number="round.estimated_prep_minutes"
                           min="1" required
                           aria-required="true"
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm
                                  dark:bg-gray-700 dark:text-gray-100">
                    <p class="mt-1 text-xs text-gray-400">
                      El sistema restará este valor al tiempo elegido por el cliente para calcular cuándo despachar a cocina.
                    </p>
                  </div>
                </div>

                <div>
                  <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Secciones en esta ronda
                  </p>
                  <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($sectionTypes as $st)
                      <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox"
                               :name="`rounds[${index}][section_types][]`"
                               value="{{ $st }}"
                               :checked="round.section_types.includes('{{ $st }}')"
                               @change="round.section_types.includes('{{ $st }}')
                                 ? round.section_types.splice(round.section_types.indexOf('{{ $st }}'), 1)
                                 : round.section_types.push('{{ $st }}')"
                               class="h-4 w-4 text-orange-500 border-gray-300 rounded">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                          {{ $sectionLabels[$st] ?? $st }}
                        </span>
                      </label>
                    @endforeach
                  </div>
                </div>
              </div>
            </template>
          </div>

          {{-- Botón añadir ronda --}}
          <div class="mt-4">
            <button type="button"
                    x-show="rounds.length < 4"
                    @click="rounds.push({
                      round_number: rounds.length + 1,
                      default_delay_minutes: 0,
                      estimated_prep_minutes: 15,
                      section_types: []
                    })"
                    class="text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600
                           text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-600
                           py-2 px-4 rounded">
              + Añadir ronda
            </button>
          </div>

          {{-- Guardar tiempos --}}
          <div class="flex justify-end mt-6">
            <button type="submit"
                    class="px-5 py-2 text-sm text-white bg-orange-500 hover:bg-orange-700 rounded-md font-medium">
              Guardar tiempos
            </button>
          </div>

        </form>
      </section>

    </div>
  </div>
</x-app-layout>
