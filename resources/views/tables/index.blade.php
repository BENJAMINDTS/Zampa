{{-- @author AyrtonAlania --}}
<x-app-layout>
  <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 mt-4 sm:mt-10">

    {{-- Cabecera --}}
    <div class="flex justify-between items-center mb-4 sm:mb-6">
      <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Mis Mesas y Códigos QR</h2>
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
