@extends('layouts.superadmin')

@section('header', 'Nuevo negocio')

@section('content')

<div class="max-w-2xl mx-auto">

    <div class="mb-6">
        <a href="{{ route('superadmin.businesses.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver a negocios
        </a>
        <h1 class="text-xl sm:text-2xl font-bold text-white mt-2">Nuevo negocio</h1>
        <p class="text-slate-400 text-sm mt-1">Crea una cuenta de gerente y asígnale un plan.</p>
    </div>

    <section class="bg-slate-900 rounded-xl ring-1 ring-slate-800 p-6">

        <form method="POST" action="{{ route('superadmin.businesses.store') }}" novalidate>
            @csrf

            <div class="space-y-5">

                {{-- Sección: datos del negocio --}}
                <div class="pb-4 border-b border-slate-800">
                    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Datos del negocio</h2>
                </div>

                {{-- Nombre del negocio --}}
                <div>
                    <label for="business_name" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Nombre del negocio <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input id="business_name"
                           name="business_name"
                           type="text"
                           value="{{ old('business_name') }}"
                           aria-required="true"
                           aria-describedby="{{ $errors->has('business_name') ? 'error-business_name' : '' }}"
                           class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                  px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                  {{ $errors->has('business_name') ? 'border-red-500 focus:ring-red-500' : '' }}"
                           placeholder="Ej: Restaurante El Rincón">
                    @error('business_name')
                        <p id="error-business_name" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Dirección --}}
                <div>
                    <label for="address" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Dirección <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input id="address"
                           name="address"
                           type="text"
                           value="{{ old('address') }}"
                           aria-required="true"
                           aria-describedby="{{ $errors->has('address') ? 'error-address' : '' }}"
                           class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                  px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                  {{ $errors->has('address') ? 'border-red-500 focus:ring-red-500' : '' }}"
                           placeholder="Ej: Calle Mayor 12, Madrid">
                    @error('address')
                        <p id="error-address" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Ubicación — mapa interactivo --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">
                        Ubicación <span class="text-slate-500 font-normal">(opcional — haz clic en el mapa)</span>
                    </label>
                    <div id="location-picker"
                         style="height: 300px;"
                         class="rounded-lg border border-slate-700 mb-3"
                         aria-label="Selector de ubicación en mapa"
                         role="img">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="lat" class="block text-xs font-medium text-slate-400 mb-1">Latitud</label>
                            <input id="lat"
                                   name="lat"
                                   type="number"
                                   step="0.0000001"
                                   value="{{ old('lat') }}"
                                   readonly
                                   aria-describedby="{{ $errors->has('lat') ? 'error-lat' : '' }}"
                                   class="w-full rounded-lg bg-slate-700 border border-slate-600 text-slate-300
                                          px-3 py-2 text-xs focus:outline-none cursor-default
                                          {{ $errors->has('lat') ? 'border-red-500' : '' }}"
                                   placeholder="Selecciona en el mapa">
                            @error('lat')
                                <p id="error-lat" role="alert" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="lng" class="block text-xs font-medium text-slate-400 mb-1">Longitud</label>
                            <input id="lng"
                                   name="lng"
                                   type="number"
                                   step="0.0000001"
                                   value="{{ old('lng') }}"
                                   readonly
                                   aria-describedby="{{ $errors->has('lng') ? 'error-lng' : '' }}"
                                   class="w-full rounded-lg bg-slate-700 border border-slate-600 text-slate-300
                                          px-3 py-2 text-xs focus:outline-none cursor-default
                                          {{ $errors->has('lng') ? 'border-red-500' : '' }}"
                                   placeholder="Selecciona en el mapa">
                            @error('lng')
                                <p id="error-lng" role="alert" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Plan --}}
                <div>
                    <label for="plan_id" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Plan de suscripción <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <select id="plan_id"
                            name="plan_id"
                            aria-required="true"
                            aria-describedby="{{ $errors->has('plan_id') ? 'error-plan_id' : '' }}"
                            class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white
                                   px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                   {{ $errors->has('plan_id') ? 'border-red-500 focus:ring-red-500' : '' }}">
                        <option value="">-- Selecciona un plan --</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} — {{ number_format($plan->price, 2, ',', '.') }} €/mes · {{ $plan->max_tables }} mesas
                            </option>
                        @endforeach
                    </select>
                    @error('plan_id')
                        <p id="error-plan_id" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Sección: datos del responsable --}}
                <div class="pt-4 pb-4 border-b border-slate-800">
                    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Datos del responsable</h2>
                </div>

                {{-- Nombre del responsable --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Nombre completo <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input id="name"
                           name="name"
                           type="text"
                           value="{{ old('name') }}"
                           aria-required="true"
                           aria-describedby="{{ $errors->has('name') ? 'error-name' : '' }}"
                           class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                  px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                  {{ $errors->has('name') ? 'border-red-500 focus:ring-red-500' : '' }}"
                           placeholder="Ej: Juan García">
                    @error('name')
                        <p id="error-name" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Email <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input id="email"
                           name="email"
                           type="email"
                           value="{{ old('email') }}"
                           aria-required="true"
                           aria-describedby="{{ $errors->has('email') ? 'error-email' : '' }}"
                           class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                  px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                  {{ $errors->has('email') ? 'border-red-500 focus:ring-red-500' : '' }}"
                           placeholder="gerente@restaurante.com">
                    @error('email')
                        <p id="error-email" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contraseña --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Contraseña <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input id="password"
                           name="password"
                           type="password"
                           aria-required="true"
                           aria-describedby="{{ $errors->has('password') ? 'error-password' : '' }}"
                           class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                  px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400
                                  {{ $errors->has('password') ? 'border-red-500 focus:ring-red-500' : '' }}"
                           placeholder="Mínimo 8 caracteres">
                    @error('password')
                        <p id="error-password" role="alert" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirmar contraseña --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Confirmar contraseña <span class="text-red-400" aria-hidden="true">*</span>
                    </label>
                    <input id="password_confirmation"
                           name="password_confirmation"
                           type="password"
                           aria-required="true"
                           class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500
                                  px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400"
                           placeholder="Repite la contraseña">
                </div>

            </div>

            <div class="flex items-center justify-end gap-3 mt-8 pt-5 border-t border-slate-800">
                <a href="{{ route('superadmin.businesses.index') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-600">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-5 py-2 rounded-lg bg-amber-400 text-slate-900 text-sm font-semibold hover:bg-amber-300 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 focus:ring-offset-slate-900">
                    Crear negocio
                </button>
            </div>

        </form>

    </section>

</div>

@endsection

@push('styles')
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
<script>
(function () {
    const latInput     = document.getElementById('lat');
    const lngInput     = document.getElementById('lng');
    const addressInput = document.getElementById('address');

    const initialLat = parseFloat(latInput.value) || 40.4168;
    const initialLng = parseFloat(lngInput.value) || -3.7038;
    const hasInitial = latInput.value !== '' && lngInput.value !== '';

    const map = L.map('location-picker').setView([initialLat, initialLng], hasInitial ? 13 : 6);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    let marker = hasInitial
        ? L.marker([initialLat, initialLng], { draggable: true }).addTo(map)
        : null;

    function setCoords(lat, lng) {
        latInput.value = lat.toFixed(7);
        lngInput.value = lng.toFixed(7);
    }

    function reverseGeocode(lat, lng) {
        const url = 'https://nominatim.openstreetmap.org/reverse'
            + '?lat=' + lat + '&lon=' + lng
            + '&format=json&addressdetails=1';

        fetch(url, { headers: { 'Accept-Language': 'es' } })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data && data.address) {
                    addressInput.value = formatAddress(data, null);
                }
            })
            .catch(function () {});
    }

    function placeMarker(latlng) {
        if (marker) {
            marker.setLatLng(latlng);
        } else {
            marker = L.marker(latlng, { draggable: true }).addTo(map);
            marker.on('dragend', function () {
                const pos = marker.getLatLng();
                setCoords(pos.lat, pos.lng);
                reverseGeocode(pos.lat, pos.lng);
            });
        }
        setCoords(latlng.lat, latlng.lng);
    }

    if (marker) {
        marker.on('dragend', function () {
            const pos = marker.getLatLng();
            setCoords(pos.lat, pos.lng);
            reverseGeocode(pos.lat, pos.lng);
        });
    }

    map.on('click', function (e) {
        placeMarker(e.latlng);
        reverseGeocode(e.latlng.lat, e.latlng.lng);
    });

    // ── Autocomplete de dirección vía Nominatim ──────────────────────────────

    const dropdown = document.createElement('ul');
    dropdown.className = 'absolute w-full bg-slate-800 border border-slate-600 rounded-lg mt-1 shadow-xl hidden';
    dropdown.style.zIndex = '2000';
    dropdown.setAttribute('role', 'listbox');
    dropdown.setAttribute('aria-label', 'Sugerencias de dirección');

    addressInput.parentElement.style.position = 'relative';
    addressInput.parentElement.style.zIndex   = '2000';
    addressInput.parentElement.appendChild(dropdown);

    let debounceTimer = null;
    let currentQuery  = '';

    addressInput.setAttribute('autocomplete', 'off');
    addressInput.setAttribute('aria-autocomplete', 'list');
    addressInput.setAttribute('aria-controls', 'address-suggestions');
    dropdown.id = 'address-suggestions';

    function closeSuggestions() {
        dropdown.innerHTML = '';
        dropdown.classList.add('hidden');
    }

    function extractHouseNumber(query) {
        // "N4", "Nº 4", "No4", "Num. 4" → "4"
        let m = query.match(/\bN[oº°úum]*\.?\s*(\d+)/i);
        if (m) return m[1];
        // Standalone number adjacent to a street name (e.g. "Calle X 4")
        m = query.match(/(?<!\d)(\d{1,4})(?!\d)/);
        return m ? m[1] : null;
    }

    function formatAddress(item, originalQuery) {
        const a = item.address || {};
        const parts = [];

        let street = a.road || a.pedestrian || a.footway || a.path || '';
        const houseNum = a.house_number || (originalQuery ? extractHouseNumber(originalQuery) : null);
        if (houseNum) street += ' ' + houseNum;
        if (street) parts.push(street);

        const city = a.city || a.town || a.village || a.municipality || a.county || '';
        if (city) parts.push(city);

        if (a.postcode) parts.push(a.postcode);

        return parts.length ? parts.join(', ') : item.display_name;
    }

    function selectSuggestion(item, originalQuery) {
        addressInput.value = formatAddress(item, originalQuery);
        closeSuggestions();
        const latlng = L.latLng(parseFloat(item.lat), parseFloat(item.lon));
        map.setView(latlng, 15);
        placeMarker(latlng);
    }

    function renderSuggestions(results, originalQuery) {
        dropdown.innerHTML = '';

        if (!results.length) {
            closeSuggestions();
            return;
        }

        results.forEach(function (item) {
            const li = document.createElement('li');
            li.className = 'px-4 py-2.5 text-sm text-slate-300 hover:bg-slate-700 hover:text-white cursor-pointer';
            li.setAttribute('role', 'option');
            li.textContent = formatAddress(item, originalQuery);
            li.addEventListener('mousedown', function (e) {
                e.preventDefault();
                selectSuggestion(item, originalQuery);
            });
            dropdown.appendChild(li);
        });

        dropdown.classList.remove('hidden');
    }

    function normalizeQuery(q) {
        // "N4", "Nº4", "No 4", "Num 4" → "4" so Nominatim finds house-level results
        return q.replace(/\bN[oº°úum]*\.?\s*(\d+)/gi, '$1').trim();
    }

    function fetchSuggestions(query) {
        currentQuery = query;
        const url = 'https://nominatim.openstreetmap.org/search'
            + '?q=' + encodeURIComponent(normalizeQuery(query))
            + '&format=json&limit=5&addressdetails=1';

        fetch(url, { headers: { 'Accept-Language': 'es' } })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (currentQuery === query) {
                    renderSuggestions(data, query);
                }
            })
            .catch(function () { closeSuggestions(); });
    }

    addressInput.addEventListener('input', function () {
        const query = addressInput.value.trim();
        clearTimeout(debounceTimer);

        if (query.length < 3) {
            closeSuggestions();
            return;
        }

        debounceTimer = setTimeout(function () {
            fetchSuggestions(query);
        }, 450);
    });

    addressInput.addEventListener('blur', function () {
        setTimeout(closeSuggestions, 150);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSuggestions();
    });
}());
</script>
@endpush
