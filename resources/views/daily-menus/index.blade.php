{{-- @author Ayrtonalania --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      Menú del Día
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="p-6 text-gray-900 dark:text-gray-100">

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

        {{-- Botón crear --}}
        <div class="flex justify-end mb-6">
          <a href="{{ route('daily-menus.create') }}"
             class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
            + Nuevo Menú del Día
          </a>
        </div>

        {{-- Resumen semanal --}}
        <section aria-labelledby="resumen-semanal-titulo" class="mb-8">
          <h3 id="resumen-semanal-titulo" class="text-lg font-semibold mb-3 text-gray-700 dark:text-gray-300">
            Resumen semanal
          </h3>
          <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2">
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
            @endphp
            @foreach($weekSummary as $day => $data)
              <div class="relative border rounded-lg p-3 text-center
                          {{ $data['menu'] ? 'bg-orange-50 dark:bg-orange-900/20 border-orange-300' : 'bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600' }}">
                @if($data['exception'])
                  <span class="absolute -top-2 -right-2 bg-blue-500 text-white text-xs px-1.5 py-0.5 rounded-full leading-none">
                    Exc.
                  </span>
                @endif
                <p class="font-bold text-sm text-gray-700 dark:text-gray-200">{{ $dayLabels[$day] }}</p>
                @if($data['menu'])
                  <p class="text-xs text-orange-600 dark:text-orange-400 mt-1 truncate" title="{{ $data['menu']->title }}">
                    {{ $data['menu']->title }}
                  </p>
                  <p class="text-xs font-semibold text-gray-600 dark:text-gray-300 mt-0.5">
                    {{ number_format($data['menu']->price, 2) }} €
                  </p>
                @else
                  <p class="text-xs text-gray-400 mt-1">Sin menú</p>
                @endif
              </div>
            @endforeach
          </div>
        </section>

        {{-- Tabla de menús --}}
        <section aria-labelledby="tabla-menus-titulo">
          <h3 id="tabla-menus-titulo" class="text-lg font-semibold mb-3 text-gray-700 dark:text-gray-300">
            Todos los menús
          </h3>
          <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                   aria-label="Listado de menús del día">
              <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                  <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Día / Fecha
                  </th>
                  <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Título
                  </th>
                  <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">
                    Precio
                  </th>
                  <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">
                    Secciones
                  </th>
                  <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Activo
                  </th>
                  <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($menus as $menu)
                  <tr>
                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                      @if($menu->specific_date)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                          Fecha específica
                        </span>
                        <br>
                        <span class="text-xs text-gray-500">{{ $menu->specific_date->format('d/m/Y') }}</span>
                      @else
                        @php
                          $dayNames = [
                            'monday' => 'Lunes', 'tuesday' => 'Martes', 'wednesday' => 'Miércoles',
                            'thursday' => 'Jueves', 'friday' => 'Viernes', 'saturday' => 'Sábado', 'sunday' => 'Domingo'
                          ];
                        @endphp
                        {{ $dayNames[$menu->day_of_week] ?? $menu->day_of_week }}
                      @endif
                    </td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                      {{ $menu->title }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hidden sm:table-cell">
                      {{ number_format($menu->price, 2) }} €
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 hidden md:table-cell">
                      {{ $menu->sections_count ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                      @if($menu->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                          Activo
                        </span>
                      @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                          Inactivo
                        </span>
                      @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                      <div class="flex flex-col sm:flex-row justify-end gap-2">
                        <a href="{{ route('daily-menus.sections', $menu) }}"
                           aria-label="Configurar secciones del menú {{ $menu->title }}"
                           class="text-sm bg-indigo-50 text-indigo-600 hover:bg-indigo-100 border border-indigo-200 py-1 px-3 rounded dark:bg-indigo-900/30 dark:text-indigo-300 dark:border-indigo-700">
                          Secciones
                        </a>
                        <a href="{{ route('daily-menus.edit', $menu) }}"
                           aria-label="Editar menú {{ $menu->title }}"
                           class="text-sm bg-yellow-50 text-yellow-700 hover:bg-yellow-100 border border-yellow-200 py-1 px-3 rounded dark:bg-yellow-900/30 dark:text-yellow-300 dark:border-yellow-700">
                          Editar
                        </a>
                        <form action="{{ route('daily-menus.destroy', $menu) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar el menú {{ addslashes($menu->title) }}? Esta acción no se puede deshacer.')">
                          @csrf
                          @method('DELETE')
                          <button type="submit"
                                  aria-label="Eliminar menú {{ $menu->title }}"
                                  class="w-full text-sm bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 py-1 px-3 rounded dark:bg-red-900/30 dark:text-red-400 dark:border-red-700">
                            Eliminar
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">
                      No hay menús del día configurados.
                      <a href="{{ route('daily-menus.create') }}" class="text-orange-500 hover:underline ml-1">Crear el primero</a>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          @if($menus->hasPages())
            <nav aria-label="Paginación de menús del día" class="mt-6">
              {{ $menus->links() }}
            </nav>
          @endif
        </section>

      </div>
    </div>
  </div>
</x-app-layout>
