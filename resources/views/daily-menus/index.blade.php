{{-- @author Ayrtonalania --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      Menú del Día
    </h2>
  </x-slot>

  @php
    $dayLabels = [
      'monday'    => 'Lun',
      'tuesday'   => 'Mar',
      'wednesday' => 'Mié',
      'thursday'  => 'Jue',
      'friday'    => 'Vie',
      'saturday'  => 'Sáb',
      'sunday'    => 'Dom',
    ];
    $dayNames = [
      'monday'    => 'Lunes',
      'tuesday'   => 'Martes',
      'wednesday' => 'Miércoles',
      'thursday'  => 'Jueves',
      'friday'    => 'Viernes',
      'saturday'  => 'Sábado',
      'sunday'    => 'Domingo',
    ];
    $todayKey = strtolower(now()->englishDayOfWeek);
  @endphp

  <div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <x-toast />

      {{-- ── Cabecera + acción principal ─────────────────────────── --}}
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Define un menú por día de la semana o para fechas concretas.
            Solo el menú activo de hoy aparece en la carta digital.
          </p>
        </div>
        <a href="{{ route('daily-menus.create') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold
                  bg-orange-500 hover:bg-orange-600 text-white
                  py-2.5 px-4 rounded-lg min-h-[44px] transition-colors flex-shrink-0
                  focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
          Nuevo Menú del Día
        </a>
      </div>

      {{-- ── Resumen semanal ──────────────────────────────────────── --}}
      <section aria-labelledby="resumen-semanal-titulo" class="mb-10">
        <h3 id="resumen-semanal-titulo"
            class="text-base font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
          <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          Semana actual
        </h3>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2">
          @foreach($weekSummary as $day => $data)
            @php
              $isToday   = $day === $todayKey;
              $hasMenu   = !is_null($data['menu']);
              $hasExc    = !is_null($data['exception']);
              $activeMenu = $hasExc ? $data['exception'] : ($hasMenu ? $data['menu'] : null);
            @endphp
            <div class="relative rounded-xl border p-3 text-center transition-colors
                        {{ $isToday
                             ? 'ring-2 ring-orange-400 border-orange-300 bg-orange-50 dark:bg-orange-900/20'
                             : ($activeMenu
                                 ? 'border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/10'
                                 : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800') }}">

              {{-- Badge excepción --}}
              @if($hasExc)
                <span class="absolute -top-2 -right-2 bg-blue-500 text-white text-[10px] font-bold
                             px-1.5 py-0.5 rounded-full leading-none shadow-sm"
                      title="Excepción para esta fecha">
                  Exc.
                </span>
              @endif

              {{-- Badge hoy --}}
              @if($isToday)
                <span class="absolute -top-2 left-1/2 -translate-x-1/2
                             bg-orange-500 text-white text-[10px] font-bold
                             px-1.5 py-0.5 rounded-full leading-none shadow-sm whitespace-nowrap">
                  Hoy
                </span>
              @endif

              <p class="font-bold text-sm {{ $isToday ? 'text-orange-700 dark:text-orange-300' : 'text-gray-700 dark:text-gray-200' }}">
                {{ $dayLabels[$day] }}
              </p>

              @if($activeMenu)
                <p class="text-xs font-medium mt-1.5 truncate
                           {{ $isToday ? 'text-orange-600 dark:text-orange-400' : 'text-green-700 dark:text-green-400' }}"
                   title="{{ $activeMenu->title }}">
                  {{ $activeMenu->title }}
                </p>
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-300 mt-0.5">
                  {{ number_format($activeMenu->price, 2, ',', '') }} €
                </p>
                @if($hasExc)
                  <p class="text-[10px] text-blue-600 dark:text-blue-400 mt-0.5">
                    {{ $data['exception']->specific_date->format('d/m') }}
                  </p>
                @endif
              @else
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">Sin menú</p>
              @endif
            </div>
          @endforeach
        </div>
      </section>

      {{-- ── Listado completo ─────────────────────────────────────── --}}
      <section aria-labelledby="tabla-menus-titulo">

        <h3 id="tabla-menus-titulo"
            class="text-base font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
          <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
          Todos los menús
          <span class="text-xs font-normal text-gray-400 dark:text-gray-500">
            ({{ $menus->total() }})
          </span>
        </h3>

        @if($menus->isEmpty())
          {{-- Estado vacío --}}
          <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-dashed
                      border-gray-300 dark:border-gray-600">
            <div class="text-5xl mb-4" aria-hidden="true">🍽️</div>
            <h4 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-1">
              Aún no tienes menús del día
            </h4>
            <p class="text-sm text-gray-400 dark:text-gray-500 mb-5 max-w-xs mx-auto">
              Crea tu primer menú y aparecerá automáticamente en la carta digital cuando corresponda.
            </p>
            <a href="{{ route('daily-menus.create') }}"
               class="inline-flex items-center gap-2 text-sm font-semibold
                      bg-orange-500 hover:bg-orange-600 text-white
                      py-2.5 px-5 rounded-lg transition-colors
                      focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              Crear primer menú
            </a>
          </div>

        @else
          <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700"
                     aria-label="Listado de menús del día">
                <thead class="bg-gray-50 dark:bg-gray-800">
                  <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                      Día / Fecha
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                      Título
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">
                      Precio
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">
                      Secciones
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                      Estado
                    </th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                      Acciones
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700">
                  @foreach($menus as $menu)
                    @php
                      $isToday = !$menu->specific_date && $menu->day_of_week === $todayKey;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors
                               {{ $isToday ? 'bg-orange-50/50 dark:bg-orange-900/10' : '' }}">

                      {{-- Día / Fecha --}}
                      <td class="px-4 py-3.5 text-sm">
                        <div class="flex items-center gap-2 flex-wrap">
                          @if($menu->specific_date)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold
                                         bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                                         px-2 py-0.5 rounded-full">
                              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                              </svg>
                              {{ $menu->specific_date->format('d/m/Y') }}
                            </span>
                          @else
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                              {{ $dayNames[$menu->day_of_week] ?? $menu->day_of_week }}
                            </span>
                            @if($isToday)
                              <span class="text-xs font-bold bg-orange-500 text-white px-1.5 py-0.5 rounded-full">
                                Hoy
                              </span>
                            @endif
                          @endif
                        </div>
                      </td>

                      {{-- Título --}}
                      <td class="px-4 py-3.5 text-sm font-semibold text-gray-900 dark:text-gray-100 max-w-[200px]">
                        <span class="truncate block" title="{{ $menu->title }}">{{ $menu->title }}</span>
                        @if($menu->description)
                          <span class="block text-xs font-normal text-gray-400 dark:text-gray-500 truncate mt-0.5"
                                title="{{ $menu->description }}">
                            {{ $menu->description }}
                          </span>
                        @endif
                      </td>

                      {{-- Precio --}}
                      <td class="px-4 py-3.5 text-sm font-semibold text-gray-700 dark:text-gray-300 hidden sm:table-cell whitespace-nowrap">
                        {{ number_format($menu->price, 2, ',', '') }} €
                      </td>

                      {{-- Secciones --}}
                      <td class="px-4 py-3.5 hidden md:table-cell">
                        @if($menu->sections_count > 0)
                          <span class="inline-flex items-center gap-1 text-xs font-medium
                                       bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300
                                       px-2 py-0.5 rounded-full">
                            {{ $menu->sections_count }} sección{{ $menu->sections_count !== 1 ? 'es' : '' }}
                          </span>
                        @else
                          <span class="inline-flex items-center gap-1 text-xs font-medium
                                       bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400
                                       px-2 py-0.5 rounded-full"
                                title="Este menú no tiene secciones configuradas y no aparecerá correctamente en la carta">
                            ⚠ Sin secciones
                          </span>
                        @endif
                      </td>

                      {{-- Estado --}}
                      <td class="px-4 py-3.5">
                        @if($menu->is_active)
                          <span class="inline-flex items-center gap-1 text-xs font-semibold
                                       bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400
                                       px-2.5 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block" aria-hidden="true"></span>
                            Activo
                          </span>
                        @else
                          <span class="inline-flex items-center gap-1 text-xs font-medium
                                       bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400
                                       px-2.5 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400 inline-block" aria-hidden="true"></span>
                            Inactivo
                          </span>
                        @endif
                      </td>

                      {{-- Acciones --}}
                      <td class="px-4 py-3.5">
                        <div class="flex items-center justify-end gap-2 flex-wrap">

                          <a href="{{ route('daily-menus.sections', $menu) }}"
                             aria-label="Configurar secciones del menú {{ $menu->title }}"
                             class="inline-flex items-center gap-1 text-xs font-medium
                                    text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300
                                    border border-indigo-200 dark:border-indigo-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/20
                                    py-1.5 px-2.5 rounded-lg min-h-[34px] transition-colors
                                    focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Secciones
                          </a>

                          <a href="{{ route('daily-menus.edit', $menu) }}"
                             aria-label="Editar menú {{ $menu->title }}"
                             class="inline-flex items-center gap-1 text-xs font-medium
                                    text-amber-700 dark:text-amber-400 hover:text-amber-900 dark:hover:text-amber-300
                                    border border-amber-200 dark:border-amber-700 hover:bg-amber-50 dark:hover:bg-amber-900/20
                                    py-1.5 px-2.5 rounded-lg min-h-[34px] transition-colors
                                    focus:outline-none focus:ring-2 focus:ring-amber-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Editar
                          </a>

                          <form action="{{ route('daily-menus.destroy', $menu) }}" method="POST"
                                onsubmit="return confirm('¿Eliminar «{{ addslashes($menu->title) }}»?\nEsta acción no se puede deshacer.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    aria-label="Eliminar menú {{ $menu->title }}"
                                    class="inline-flex items-center gap-1 text-xs font-medium
                                           text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300
                                           border border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/20
                                           py-1.5 px-2.5 rounded-lg min-h-[34px] transition-colors
                                           focus:outline-none focus:ring-2 focus:ring-red-400">
                              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4h6v3M4 7h16"/>
                              </svg>
                              Eliminar
                            </button>
                          </form>

                        </div>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

          @if($menus->hasPages())
            <nav aria-label="Paginación de menús del día" class="mt-6">
              {{ $menus->links() }}
            </nav>
          @endif
        @endif

      </section>

    </div>
  </div>
</x-app-layout>
