@extends('layouts.superadmin')

@section('header', 'Mapa de negocios')

@section('content')

<div class="max-w-7xl mx-auto">

    <h1 class="text-xl sm:text-2xl font-bold text-white mb-4">
        Mapa de negocios
    </h1>

    {{-- Aviso de negocios sin coordenadas --}}
    @if($withoutCoords > 0)
    <div role="alert"
         class="mb-4 p-3 rounded bg-yellow-500/10 border border-yellow-500/30
                text-yellow-400 text-sm">
        {{ $withoutCoords }}
        {{ Str::plural('negocio', $withoutCoords) }}
        sin coordenadas
        {{ $withoutCoords === 1 ? 'no aparece' : 'no aparecen' }}
        en el mapa.
    </div>
    @endif

    {{-- Mapa Leaflet --}}
    <div id="map"
         style="height: 600px;"
         class="rounded-lg border border-slate-700 shadow-sm"
         aria-label="Mapa de negocios registrados en Zampa"
         role="img">
    </div>

</div>

@endsection

@push('scripts')
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WLs="
        crossorigin=""></script>

<script>
(function () {
    const businesses = @json($businesses);

    const map = L.map('map').setView([40.4168, -3.7038], 6);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© <a href="https://www.openstreetmap.org/copyright">' +
                     'OpenStreetMap</a> contributors'
    }).addTo(map);

    if (businesses.length === 0) {
        return;
    }

    const bounds = [];

    businesses.forEach(function (b) {
        const color = b.active ? '#22c55e' : '#ef4444';

        const icon = L.divIcon({
            className: '',
            html: '<div style="background:' + color + ';width:14px;' +
                  'height:14px;border-radius:50%;border:2px solid white;' +
                  'box-shadow:0 1px 4px rgba(0,0,0,.4)"></div>',
            iconSize:   [14, 14],
            iconAnchor: [7,  7],
        });

        const estado  = b.active ? 'Activo' : 'Inactivo';
        const address = b.address ? '<br>' + b.address : '';

        const popup = '<strong>' + b.name + '</strong>' +
                      '<br>Plan: '   + b.plan +
                      '<br>Estado: ' + estado +
                      address;

        L.marker([b.lat, b.lng], { icon })
         .addTo(map)
         .bindPopup(popup);

        bounds.push([b.lat, b.lng]);
    });

    if (bounds.length > 1) {
        map.fitBounds(bounds, { padding: [40, 40] });
    } else if (bounds.length === 1) {
        map.setView(bounds[0], 13);
    }
}());
</script>
@endpush
