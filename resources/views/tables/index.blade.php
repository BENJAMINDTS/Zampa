{{-- @author AyrtonAlania --}}
<x-app-layout>
  <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 mt-4 sm:mt-10">

    {{-- Cabecera --}}
    <div class="flex flex-wrap justify-between items-center gap-3 mb-4 sm:mb-6">
      <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Mis Mesas y Códigos QR</h2>

      <div class="flex flex-wrap items-center gap-3">
        {{-- Contador de plan --}}
        @php
          $used     = $tables->count();
          $pct      = $maxTables > 0 ? $used / $maxTables : 1;
          $badgeCss = $used >= $maxTables
              ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
              : ($pct >= 0.8
                  ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                  : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400');
        @endphp
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium {{ $badgeCss }}"
              aria-label="{{ $used }} de {{ $maxTables }} mesas usadas">
          <svg aria-hidden="true" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M3 3h7v7H3zm0 11h7v7H3zm11-11h7v7h-7zm0 11h7v7h-7z"/>
          </svg>
          {{ $used }} de {{ $maxTables }} mesas usadas
        </span>

        @if(Auth::user()->isAdmin())
        <a href="{{ route('tables.map') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white
                  bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors"
           aria-label="Ir al mapa visual de mesas">
          <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
          </svg>
          Ver Mapa
        </a>
        @endif
      </div>
    </div>

    {{-- Mensaje flash --}}
    @if(session('success'))
    <div role="alert" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
      {{ session('success') }}
    </div>
    @endif

    @if($tables->isEmpty())
      <div class="bg-white shadow-md rounded-lg p-8 text-center text-gray-500">
        No tienes mesas registradas. Crea mesas desde el panel de administración para generar sus QR.
      </div>
    @else
      {{-- Grid de tarjetas QR --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($tables as $table)
          <div class="bg-white shadow-md rounded-lg p-5 flex flex-col items-center gap-4">

            {{-- Nombre de la mesa --}}
            <h3 class="text-lg font-semibold text-gray-800 text-center">{{ $table->name }}</h3>

            {{-- QR inline (SVG) --}}
            <div class="border border-gray-200 rounded-md p-2 bg-white" aria-label="Código QR para {{ $table->name }}">
              {!! QrCode::format('svg')
                    ->size(200)
                    ->margin(1)
                    ->generate(route('menu.show', $table->unique_hash)) !!}
            </div>

            {{-- URL del enlace --}}
            <p class="text-xs text-gray-400 break-all text-center">
              {{ route('menu.show', $table->unique_hash) }}
            </p>

            {{-- Acciones --}}
            <div class="flex flex-col sm:flex-row gap-2 w-full">

              {{-- Descargar SVG --}}
              <a
                href="{{ route('tables.qr.download', $table) }}"
                class="flex-1 text-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2 px-3 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                aria-label="Descargar QR de {{ $table->name }} en formato SVG"
              >
                Descargar SVG
              </a>

              {{-- Regenerar QR --}}
              <form
                action="{{ route('tables.qr.regenerate', $table) }}"
                method="POST"
                onsubmit="return confirm('¿Regenerar el QR de {{ addslashes($table->name) }}? El enlace anterior dejará de funcionar.');"
                class="flex-1"
              >
                @csrf
                <button
                  type="submit"
                  class="w-full bg-red-100 hover:bg-red-200 text-red-700 text-sm font-medium py-2 px-3 rounded focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2"
                  aria-label="Regenerar QR de {{ $table->name }}, invalidando el enlace actual"
                >
                  Regenerar QR
                </button>
              </form>

            </div>
          </div>
        @endforeach
      </div>
    @endif

  </div>
</x-app-layout>
