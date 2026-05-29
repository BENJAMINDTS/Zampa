/**
 * @fileoverview Superadmin business map — Leaflet initialisation and pin helpers.
 *   Used in resources/views/superadmin/map.blade.php.
 * @module superadmin-map
 * @author BenjaminDTS
 */

/**
 * Selects the pin colour for a business marker.
 * Inactive businesses always appear red regardless of plan colour.
 *
 * @param {string}  planColor - Hex colour string assigned to the business plan.
 * @param {boolean} isActive  - Whether the business account is active.
 * @returns {string} CSS colour string for the map pin.
 */
export function getPinColor(planColor, isActive) {
    return isActive ? planColor : '#ef4444';
}

/**
 * Removes businesses that have no latitude or longitude coordinates.
 *
 * @param {Array<{lat?: number, lng?: number}>} businesses - Full business list.
 * @returns {Array<Object>} Only businesses with valid coordinates.
 */
export function filterBusinessesWithCoords(businesses) {
    return businesses.filter(b => b.lat != null && b.lng != null);
}

/**
 * Builds the HTML string for a Leaflet popup given a business object.
 *
 * @param {{ name: string, plan: string, active: boolean, address?: string }} business
 * @returns {string} HTML string for the popup content.
 */
export function buildPopupContent(business) {
    const estado  = business.active ? 'Activo' : 'Inactivo';
    const address = business.address ? '<br>' + business.address : '';
    return '<strong>' + business.name + '</strong>'
         + '<br>Plan: '   + business.plan
         + '<br>Estado: ' + estado
         + address;
}

/**
 * Initialises the Leaflet map with all business markers.
 * Reads business data from `<script id="superadmin-businesses" type="application/json">`.
 * Requires Leaflet (`L`) to be loaded as a global before this function runs.
 *
 * @returns {void}
 */
export function initSuperadminMap() {
    const raw        = document.getElementById('superadmin-businesses');
    const businesses = raw ? JSON.parse(raw.textContent) : [];

    const map = L.map('map').setView([40.4168, -3.7038], 6);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom:     19,
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);

    if (businesses.length === 0) return;

    const bounds = [];

    businesses.forEach(function (b) {
        const color = getPinColor(b.plan_color, b.active);

        const icon = L.divIcon({
            className: '',
            html:      '<div style="background:' + color + ';width:14px;'
                     + 'height:14px;border-radius:50%;border:2px solid white;'
                     + 'box-shadow:0 1px 4px rgba(0,0,0,.4)"></div>',
            iconSize:   [14, 14],
            iconAnchor: [7,  7],
        });

        L.marker([b.lat, b.lng], { icon })
         .addTo(map)
         .bindPopup(buildPopupContent(b));

        bounds.push([b.lat, b.lng]);
    });

    if (bounds.length > 1) {
        map.fitBounds(bounds, { padding: [40, 40] });
    } else if (bounds.length === 1) {
        map.setView(bounds[0], 13);
    }
}

if (typeof document !== 'undefined' && document.getElementById('superadmin-businesses')) {
    if (document.readyState !== 'loading') {
        initSuperadminMap();
    } else {
        document.addEventListener('DOMContentLoaded', initSuperadminMap);
    }
}
