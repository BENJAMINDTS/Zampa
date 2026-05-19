{{-- @author Ayrtonalania --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      Configurar secciones — {{ $dailyMenu->title }}
    </h2>
  </x-slot>

  @php
    $sectionLabels = [
      'first_course'  => 'Primer Plato',
      'second_course' => 'Segundo Plato',
      'dessert'       => 'Postre',
      'coffee'        => 'Café / Infusión',
      'drink'         => 'Bebida',
      'bread'         => 'Pan',
    ];
    $sectionIcons = [
      'first_course'  => '🍲',
      'second_course' => '🍖',
      'dessert'       => '🍮',
      'coffee'        => '☕',
      'drink'         => '🥤',
      'bread'         => '🍞',
    ];
    $sectionDescriptions = [
      'first_course'  => 'Entrantes, sopas, ensaladas…',
      'second_course' => 'Carnes, pescados, arroces…',
      'dessert'       => 'Postres del menú',
      'coffee'        => 'Café, té, infusiones…',
      'drink'         => 'Refrescos, agua, vino de la casa…',
      'bread'         => 'Pan incluido con el menú',
    ];
    $existingSections = $dailyMenu->sections->keyBy('type');
    $totalSections    = count($sectionTypes);
    $activeSections   = $existingSections->count();
  @endphp

  <div class="py-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

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
            <li class="text-gray-700 dark:text-gray-300 font-medium truncate max-w-[200px]">
              {{ $dailyMenu->title }}
            </li>
          </ol>
        </nav>
        <a href="{{ route('daily-menus.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400
                  hover:text-gray-900 dark:hover:text-gray-100
                  border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 min-h-[38px]
                  hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors focus:outline-none
                  focus:ring-2 focus:ring-orange-400 focus:ring-offset-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
          </svg>
          Volver al listado
        </a>
      </div>

      {{-- ── Alertas flash ───────────────────────────────────────── --}}
      @if(session('success'))
        <div role="alert"
             class="flex items-start gap-3 bg-green-50 dark:bg-green-900/20
                    border border-green-300 dark:border-green-700
                    text-green-800 dark:text-green-300 px-4 py-3 rounded-lg mb-6">
          <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
      @endif
      @if(session('error'))
        <div role="alert"
             class="flex items-start gap-3 bg-red-50 dark:bg-red-900/20
                    border border-red-300 dark:border-red-700
                    text-red-800 dark:text-red-300 px-4 py-3 rounded-lg mb-6">
          <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
          </svg>
          <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
      @endif

      {{-- ── Resumen de progreso ─────────────────────────────────── --}}
      <div class="bg-orange-50 dark:bg-orange-900/10 border border-orange-200 dark:border-orange-800
                  rounded-xl p-4 mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <p class="text-sm font-semibold text-orange-800 dark:text-orange-300">
            {{ $activeSections }} de {{ $totalSections }} secciones configuradas
          </p>
          <p class="text-xs text-orange-600 dark:text-orange-400 mt-0.5">
            Añade las secciones que quieres incluir en el menú y asigna los productos disponibles para cada una.
          </p>
        </div>
        <div class="flex gap-1.5 flex-wrap">
          @foreach($sectionTypes as $type)
            <span title="{{ $sectionLabels[$type] }}"
                  class="text-lg leading-none {{ $existingSections->has($type) ? 'opacity-100' : 'opacity-25' }}">
              {{ $sectionIcons[$type] }}
            </span>
          @endforeach
        </div>
      </div>

      {{-- ════════════════════════════════════════════════════════════
           BLOQUE A — Secciones del menú
      ════════════════════════════════════════════════════════════ --}}
      <section aria-labelledby="secciones-titulo" class="mb-12">

        <div class="flex items-center gap-2 mb-5">
          <h3 id="secciones-titulo" class="text-lg font-bold text-gray-900 dark:text-gray-100">
            Secciones del menú
          </h3>
          <span class="text-xs bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300
                       font-semibold px-2 py-0.5 rounded-full">
            {{ $activeSections }} activas
          </span>
        </div>

        <div class="space-y-4">
          @foreach($sectionTypes as $type)
            @php $section = $existingSections->get($type); @endphp

            <div class="rounded-xl border transition-colors
                        {{ $section
                           ? 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 shadow-sm'
                           : 'bg-gray-50 dark:bg-gray-800/40 border-dashed border-gray-300 dark:border-gray-600' }}">

              {{-- Cabecera de sección --}}
              <div class="flex items-center justify-between gap-3 p-4 sm:p-5">
                <div class="flex items-center gap-3 min-w-0">
                  <span class="text-2xl leading-none flex-shrink-0" aria-hidden="true">
                    {{ $sectionIcons[$type] }}
                  </span>
                  <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                      <h4 class="font-semibold text-gray-900 dark:text-gray-100">
                        {{ $sectionLabels[$type] }}
                      </h4>
                      @if($section)
                        <span class="inline-flex items-center gap-1 text-xs font-medium
                                     bg-green-100 dark:bg-green-900/30
                                     text-green-700 dark:text-green-400
                                     px-2 py-0.5 rounded-full">
                          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                          </svg>
                          Activa
                        </span>
                        @if($section->products->count() > 0)
                          <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $section->products->count() }} producto{{ $section->products->count() !== 1 ? 's' : '' }}
                          </span>
                        @else
                          <span class="text-xs text-amber-600 dark:text-amber-400 font-medium">
                            ⚠ Sin productos asignados
                          </span>
                        @endif
                      @else
                        <span class="text-xs text-gray-400 dark:text-gray-500">No incluida</span>
                      @endif
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                      {{ $sectionDescriptions[$type] }}
                    </p>
                  </div>
                </div>

                {{-- Acción principal: añadir o eliminar --}}
                @if(!$section)
                  <form action="{{ route('daily-menus.sections.store', $dailyMenu) }}" method="POST" class="flex-shrink-0">
                    @csrf
                    <input type="hidden" name="section_type" value="{{ $type }}">
                    <button type="submit"
                            aria-label="Añadir sección {{ $sectionLabels[$type] }} al menú"
                            class="inline-flex items-center gap-1.5 text-sm font-medium
                                   bg-orange-500 hover:bg-orange-600 text-white
                                   py-2 px-4 rounded-lg min-h-[38px] transition-colors
                                   focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                      </svg>
                      Añadir
                    </button>
                  </form>
                @else
                  <form action="{{ route('daily-menus.sections.destroy', [$dailyMenu, $section]) }}" method="POST"
                        class="flex-shrink-0"
                        onsubmit="return confirm('¿Eliminar la sección «{{ addslashes($sectionLabels[$type]) }}»?\nSe perderán los productos asignados a esta sección.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            aria-label="Eliminar sección {{ $sectionLabels[$type] }}"
                            class="inline-flex items-center gap-1.5 text-sm font-medium
                                   text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300
                                   border border-red-200 dark:border-red-800 hover:border-red-400
                                   bg-white dark:bg-transparent hover:bg-red-50 dark:hover:bg-red-900/20
                                   py-2 px-3 rounded-lg min-h-[38px] transition-colors
                                   focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4h6v3M4 7h16"/>
                      </svg>
                      Eliminar
                    </button>
                  </form>
                @endif
              </div>

              @if($section)
                <div class="border-t border-gray-100 dark:border-gray-700 px-4 sm:px-5 pb-5 pt-4 space-y-5">

                  {{-- Configuración de la sección --}}
                  <form action="{{ route('daily-menus.sections.update', [$dailyMenu, $section]) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-3">
                      Configuración
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">

                      {{-- Obligatoria --}}
                      <label for="is_required_{{ $section->id }}"
                             class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer
                                    border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/40
                                    transition-colors group">
                        <input type="checkbox"
                               id="is_required_{{ $section->id }}"
                               name="is_required"
                               value="1"
                               {{ $section->is_required ? 'checked' : '' }}
                               class="h-4 w-4 mt-0.5 text-orange-500 border-gray-300 rounded
                                      focus:ring-orange-400 flex-shrink-0">
                        <div>
                          <span class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Obligatoria
                          </span>
                          <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            El cliente debe elegir un plato de esta sección para poder continuar.
                          </span>
                        </div>
                      </label>

                      {{-- Incluida en precio --}}
                      <label for="is_free_{{ $section->id }}"
                             class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer
                                    border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/40
                                    transition-colors group">
                        <input type="checkbox"
                               id="is_free_{{ $section->id }}"
                               name="is_free"
                               value="1"
                               {{ $section->is_free ? 'checked' : '' }}
                               class="h-4 w-4 mt-0.5 text-orange-500 border-gray-300 rounded
                                      focus:ring-orange-400 flex-shrink-0">
                        <div>
                          <span class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Incluida en precio
                          </span>
                          <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            El ítem se registra como gratuito (0,00 €) aunque el producto tenga precio.
                          </span>
                        </div>
                      </label>

                      {{-- Máx. elecciones --}}
                      <div class="flex flex-col p-3 rounded-lg border border-gray-200 dark:border-gray-600">
                        <label for="max_qty_{{ $section->id }}"
                               class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                          Máx. elecciones
                        </label>
                        <input type="number"
                               id="max_qty_{{ $section->id }}"
                               name="max_quantity"
                               value="{{ $section->max_quantity }}"
                               min="1" max="10"
                               aria-required="true"
                               class="w-20 border border-gray-300 dark:border-gray-600 rounded-md
                                      px-3 py-1.5 text-sm dark:bg-gray-700 dark:text-gray-100
                                      focus:ring-orange-400 focus:border-orange-400">
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1.5">
                          Platos que el cliente puede elegir dentro de esta sección.
                        </span>
                      </div>

                    </div>

                    @if($section->is_free)
                      <div class="flex items-start gap-2 bg-blue-50 dark:bg-blue-900/20
                                  border border-blue-200 dark:border-blue-700 rounded-lg px-3 py-2 mb-4">
                        <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-xs text-blue-700 dark:text-blue-300">
                          Esta sección está marcada como incluida en el precio del menú. Los ítems llegarán a cocina con precio 0,00 €.
                        </p>
                      </div>
                    @endif

                    <button type="submit"
                            aria-label="Guardar configuración de {{ $sectionLabels[$type] }}"
                            class="inline-flex items-center gap-1.5 text-sm font-medium
                                   bg-gray-800 dark:bg-gray-600 hover:bg-gray-700 dark:hover:bg-gray-500
                                   text-white py-2 px-4 rounded-lg min-h-[38px] transition-colors
                                   focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                      </svg>
                      Guardar configuración
                    </button>
                  </form>

                  {{-- Productos de la sección --}}
                  <form action="{{ route('daily-menus.sync-products', [$dailyMenu, $section]) }}" method="POST">
                    @csrf
                    @php
                      $assignedProductIds = $section->products->pluck('id')->toArray();
                      $grouped = $products->groupBy(fn($p) => $p->category?->name ?? 'Sin categoría');
                    @endphp

                    <div class="border-t border-dashed border-gray-200 dark:border-gray-700 pt-5">
                      <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-3">
                        Productos disponibles para el cliente
                      </p>

                      @if($products->isEmpty())
                        <div class="text-center py-6 text-sm text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-700/30 rounded-lg border border-dashed border-gray-200 dark:border-gray-600">
                          No tienes productos activos. Crea productos en
                          <a href="{{ route('products.create') }}" class="text-orange-500 hover:underline font-medium">
                            tu carta
                          </a>
                          antes de asignarlos al menú.
                        </div>
                      @else
                        <fieldset>
                          <legend class="sr-only">Productos para {{ $sectionLabels[$type] }}</legend>
                          <div class="border border-gray-200 dark:border-gray-600 rounded-lg divide-y
                                      divide-gray-100 dark:divide-gray-700 max-h-52 overflow-y-auto">
                            @foreach($grouped as $categoryName => $categoryProducts)
                              <div class="px-3 py-1.5 bg-gray-50 dark:bg-gray-700/50 sticky top-0 z-10">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                  {{ $categoryName }}
                                </span>
                              </div>
                              @foreach($categoryProducts as $product)
                                <label for="product_{{ $section->id }}_{{ $product->id }}"
                                       class="flex items-center gap-3 px-3 py-2.5 cursor-pointer
                                              hover:bg-orange-50 dark:hover:bg-orange-900/10
                                              transition-colors select-none
                                              {{ in_array($product->id, $assignedProductIds)
                                                 ? 'bg-orange-50/50 dark:bg-orange-900/10'
                                                 : '' }}">
                                  <input type="checkbox"
                                         id="product_{{ $section->id }}_{{ $product->id }}"
                                         name="product_ids[]"
                                         value="{{ $product->id }}"
                                         {{ in_array($product->id, $assignedProductIds) ? 'checked' : '' }}
                                         class="h-4 w-4 text-orange-500 border-gray-300 rounded
                                                focus:ring-orange-400 flex-shrink-0">
                                  <span class="text-sm text-gray-700 dark:text-gray-300 flex-1">
                                    {{ $product->name }}
                                  </span>
                                  @if(in_array($product->id, $assignedProductIds))
                                    <span class="text-xs text-orange-500 font-medium flex-shrink-0">Asignado</span>
                                  @endif
                                </label>
                              @endforeach
                            @endforeach
                          </div>
                          <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">
                            Marca los productos que el cliente podrá elegir en esta sección del menú.
                          </p>
                        </fieldset>

                        <div class="flex justify-end mt-3">
                          <button type="submit"
                                  aria-label="Guardar productos asignados a {{ $sectionLabels[$type] }}"
                                  class="inline-flex items-center gap-1.5 text-sm font-medium
                                         bg-orange-500 hover:bg-orange-600 text-white
                                         py-2 px-4 rounded-lg min-h-[38px] transition-colors
                                         focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Guardar productos
                          </button>
                        </div>
                      @endif
                    </div>
                  </form>

                </div>
              @endif
            </div>
          @endforeach
        </div>
      </section>

      {{-- ════════════════════════════════════════════════════════════
           BLOQUE B — Rondas de envío
      ════════════════════════════════════════════════════════════ --}}
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

        <div class="flex items-center gap-2 mb-5">
          <h3 id="timing-titulo" class="text-lg font-bold text-gray-900 dark:text-gray-100">
            Rondas de envío a cocina / barra
          </h3>
        </div>

        <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800
                    rounded-xl px-4 py-3 mb-5 flex items-start gap-3">
          <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
          </svg>
          <div class="text-sm text-amber-800 dark:text-amber-300">
            <p class="font-semibold mb-0.5">¿Cómo funcionan las rondas?</p>
            <p class="text-xs leading-relaxed">
              Cada ronda agrupa secciones que llegan a cocina al mismo tiempo. La <strong>Ronda 1</strong> se envía
              nada más confirmar el pedido. Las siguientes se envían según el tiempo que indique el cliente
              (menos el tiempo de preparación que definas aquí).
            </p>
          </div>
        </div>

        <form action="{{ route('daily-menus.timing', $dailyMenu) }}" method="POST">
          @csrf

          <div class="space-y-4">
            <template x-for="(round, index) in rounds" :key="index">
              <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

                {{-- Cabecera de ronda --}}
                <div class="flex items-center justify-between px-4 sm:px-5 py-3
                            bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                  <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full
                                 bg-orange-500 text-white text-xs font-bold flex-shrink-0"
                          x-text="round.round_number"></span>
                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-sm">
                      Ronda <span x-text="round.round_number"></span>
                      <template x-if="index === 0">
                        <span class="ml-1.5 text-xs font-normal text-gray-500 dark:text-gray-400">
                          — se envía al confirmar el pedido
                        </span>
                      </template>
                    </h4>
                  </div>
                  <button type="button"
                          x-show="rounds.length > 1"
                          @click="rounds.splice(index, 1); rounds.forEach((r, i) => r.round_number = i + 1)"
                          aria-label="Eliminar ronda"
                          class="text-xs text-red-500 hover:text-red-700 dark:hover:text-red-400
                                 border border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/20
                                 px-2.5 py-1 rounded-md transition-colors
                                 focus:outline-none focus:ring-2 focus:ring-red-400">
                    Eliminar ronda
                  </button>
                </div>

                <div class="px-4 sm:px-5 py-4 space-y-4">
                  <input type="hidden" :name="`rounds[${index}][round_number]`" :value="round.round_number">

                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    {{-- Tiempo sugerido --}}
                    <div>
                      <label :for="`delay_${index}`"
                             class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Tiempo sugerido al cliente
                        <span class="font-normal text-gray-400">(minutos)</span>
                      </label>
                      <input type="number"
                             :id="`delay_${index}`"
                             :name="`rounds[${index}][default_delay_minutes]`"
                             x-model.number="round.default_delay_minutes"
                             min="0" required aria-required="true"
                             class="w-full border border-gray-300 dark:border-gray-600 rounded-lg
                                    px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-100
                                    focus:ring-orange-400 focus:border-orange-400">
                      <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                        Tiempo que verá el cliente en el selector. Ej: 20 min para el segundo plato.
                      </p>
                    </div>

                    {{-- Tiempo de preparación --}}
                    <div>
                      <label :for="`prep_${index}`"
                             class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Tiempo de preparación en cocina
                        <span class="font-normal text-gray-400">(minutos)</span>
                      </label>
                      <input type="number"
                             :id="`prep_${index}`"
                             :name="`rounds[${index}][estimated_prep_minutes]`"
                             x-model.number="round.estimated_prep_minutes"
                             min="1" required aria-required="true"
                             class="w-full border border-gray-300 dark:border-gray-600 rounded-lg
                                    px-3 py-2 text-sm dark:bg-gray-700 dark:text-gray-100
                                    focus:ring-orange-400 focus:border-orange-400">
                      <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                        El sistema envía la orden a cocina con esta antelación. Ej: si el cliente pide 20 min y la preparación es 10 min, se envía a cocina a los 10 min.
                      </p>
                    </div>
                  </div>

                  {{-- Secciones de esta ronda --}}
                  <div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Secciones que se envían en esta ronda
                    </p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                      @foreach($sectionTypes as $st)
                        <label class="flex items-center gap-2.5 p-2.5 rounded-lg border cursor-pointer
                                      border-gray-200 dark:border-gray-600
                                      hover:bg-orange-50 dark:hover:bg-orange-900/10 transition-colors"
                               :class="round.section_types.includes('{{ $st }}')
                                 ? 'bg-orange-50 dark:bg-orange-900/10 border-orange-300 dark:border-orange-700'
                                 : ''">
                          <input type="checkbox"
                                 :name="`rounds[${index}][section_types][]`"
                                 value="{{ $st }}"
                                 :checked="round.section_types.includes('{{ $st }}')"
                                 @change="round.section_types.includes('{{ $st }}')
                                   ? round.section_types.splice(round.section_types.indexOf('{{ $st }}'), 1)
                                   : round.section_types.push('{{ $st }}')"
                                 class="h-4 w-4 text-orange-500 border-gray-300 rounded focus:ring-orange-400">
                          <span class="text-sm text-gray-700 dark:text-gray-300 select-none">
                            {{ $sectionIcons[$st] }} {{ $sectionLabels[$st] ?? $st }}
                          </span>
                        </label>
                      @endforeach
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </div>

          {{-- Añadir ronda --}}
          <div class="mt-4">
            <button type="button"
                    x-show="rounds.length < 4"
                    @click="rounds.push({
                      round_number: rounds.length + 1,
                      default_delay_minutes: 20,
                      estimated_prep_minutes: 10,
                      section_types: []
                    })"
                    class="inline-flex items-center gap-2 text-sm font-medium
                           text-gray-600 dark:text-gray-300
                           border border-dashed border-gray-300 dark:border-gray-600
                           hover:border-orange-400 hover:text-orange-600 dark:hover:text-orange-400
                           bg-white dark:bg-transparent hover:bg-orange-50 dark:hover:bg-orange-900/10
                           py-2.5 px-4 rounded-lg w-full justify-center transition-colors
                           focus:outline-none focus:ring-2 focus:ring-orange-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              Añadir otra ronda
              <span class="text-xs text-gray-400 dark:text-gray-500 font-normal"
                    x-text="`(${rounds.length} de 4)`"></span>
            </button>
          </div>

          {{-- Guardar tiempos --}}
          <div class="flex justify-end mt-5">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold
                           text-white bg-orange-500 hover:bg-orange-600 rounded-lg min-h-[44px]
                           transition-colors focus:outline-none focus:ring-2 focus:ring-orange-400
                           focus:ring-offset-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
              Guardar configuración de tiempos
            </button>
          </div>

        </form>
      </section>

      {{-- ── Finalizar ────────────────────────────────────────────── --}}
      <div class="flex items-center justify-between mt-10 pt-6
                  border-t border-gray-200 dark:border-gray-700">
        <a href="{{ route('daily-menus.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400
                  hover:text-gray-900 dark:hover:text-gray-100
                  border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 min-h-[44px]
                  hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors
                  focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
          </svg>
          Volver al listado
        </a>
        <a href="{{ route('daily-menus.index') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold
                  bg-orange-500 hover:bg-orange-600 text-white
                  rounded-lg px-5 py-2.5 min-h-[44px] transition-colors
                  focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2">
          Finalizar configuración
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
        </a>
      </div>

    </div>
  </div>
</x-app-layout>
