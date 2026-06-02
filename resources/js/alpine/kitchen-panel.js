/**
 * @fileoverview Kitchen panel Alpine component — real-time order polling and
 *   item-ready marking. Used in resources/views/kitchen/index.blade.php.
 * @module kitchen-panel
 * @author BenjaminDTS
 */

/**
 * Merges incoming poll data with existing local state, preserving the `marking`
 * flag on items that are already being submitted to avoid flickering.
 *
 * @param {Array<Object>} incoming - Orders from the server poll response.
 * @param {Array<Object>} current  - Current local orders array.
 * @returns {Array<Object>} Merged orders with `marking` state preserved.
 */
export function mergeKitchenOrders(incoming, current) {
    const markingIds = {};
    current.forEach(o => o.items.forEach(i => { if (i.marking) markingIds[i.id] = true; }));

    return incoming.map(o => ({
        ...o,
        all_ready: false,
        serving:   false,
        items:     o.items.map(i => ({ ...i, marking: !!markingIds[i.id] })),
    }));
}

/**
 * Returns true when every item in an order has been removed (ready), meaning
 * the order is complete and can be marked as served.
 *
 * @param {Array<Object>} items - Remaining order items after a markReady call.
 * @returns {boolean}
 */
export function isOrderComplete(items) {
    return items.length === 0;
}

/**
 * Reads initial orders from the injected JSON data block
 * `<script id="kitchen-init" type="application/json">`.
 *
 * @returns {Array<Object>} Parsed orders array, or empty array if missing.
 */
export function readKitchenInit() {
    const raw = document.getElementById('kitchen-init');
    return raw ? JSON.parse(raw.textContent) : [];
}

/**
 * Reads the API URL configuration from
 * `<script id="kitchen-urls" type="application/json">`.
 *
 * @returns {{ pending: string }} URL map.
 */
export function readKitchenUrls() {
    const raw = document.getElementById('kitchen-urls');
    return raw ? JSON.parse(raw.textContent) : {};
}

/**
 * Alpine component factory for the kitchen panel.
 *
 * @param {Array<Object>} initialOrders - Pre-loaded orders from PHP.
 * @param {{ pending: string }} urls    - Backend endpoint URLs.
 * @returns {Object} Alpine component with polling and action methods.
 */
export function kitchenPanel(initialOrders, urls) {
    return {
        /** @type {Array<Object>} Active orders displayed on screen. */
        orders: initialOrders,

        /** @type {boolean} Whether the last poll succeeded. */
        polling: true,

        /** @type {string} Human-readable time of the last successful poll. */
        lastUpdated: '--:--',

        /** @type {number|null} setInterval handle. */
        pollInterval: null,

        init() {
            this.tick();
            this.pollInterval = setInterval(() => this.tick(), 5000);
        },

        async tick() {
            try {
                const res  = await fetch(urls.pending, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await res.json();

                this.orders      = mergeKitchenOrders(data.orders, this.orders);
                this.polling     = true;
                this.lastUpdated = new Date().toLocaleTimeString('es-ES', {
                    hour: '2-digit', minute: '2-digit', second: '2-digit',
                });
            } catch {
                this.polling = false;
            }
        },

        /**
         * Marks a single order item as ready on the server, then removes it
         * from the local list. Sets `order.all_ready` when the list empties.
         *
         * @param {Object} item  - The order item to mark ready.
         * @param {Object} order - The parent order.
         * @returns {Promise<void>}
         */
        async markReady(item, order) {
            item.marking = true;
            try {
                const res  = await fetch(`/cocina/items/${item.id}/listo`, {
                    method:  'POST',
                    headers: {
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept':           'application/json',
                    },
                });
                const data = await res.json();

                order.items = order.items.filter(i => i.id !== item.id);

                if (data.all_ready) {
                    order.all_ready = true;
                }
            } catch {
                item.marking = false;
            }
        },

        /**
         * Marks a complete order as served, removing it from the local list.
         *
         * @param {Object} order - The order to mark served.
         * @returns {Promise<void>}
         */
        async markServed(order) {
            order.serving = true;
            try {
                await fetch(`/cocina/orders/${order.id}/servido`, {
                    method:  'POST',
                    headers: {
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept':           'application/json',
                    },
                });
                this.orders = this.orders.filter(o => o.id !== order.id);
            } catch {
                order.serving = false;
            }
        },
    };
}

/**
 * Alpine component for toggling a product's temporary availability.
 * Used in both kitchen and bar panels to retire products during service.
 *
 * @param {{ available: boolean, toggleUrl: string }} params
 * @returns {Object} Alpine component definition.
 */
export function productAvailability({ available, toggleUrl }) {
    return {
        /** @type {boolean} Whether the product is currently available on the menu. */
        available,

        /** @type {boolean} Whether a toggle request is in flight. */
        loading: false,

        /**
         * Sends a PATCH to the toggle endpoint and updates local state.
         *
         * @returns {Promise<void>}
         */
        async toggle() {
            this.loading = true;
            try {
                const res  = await fetch(toggleUrl, {
                    method:  'PATCH',
                    headers: {
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept':           'application/json',
                    },
                });
                const data = await res.json();
                this.available = data.is_available;
            } finally {
                this.loading = false;
            }
        },
    };
}

/**
 * Registers the `kitchenPanel` and `productAvailability` Alpine.data components.
 * Reads initial state from injected JSON script tags.
 *
 * @returns {void}
 */
export function registerKitchenPanel() {
    Alpine.data('kitchenPanel',        () => kitchenPanel(readKitchenInit(), readKitchenUrls()));
    Alpine.data('productAvailability', (params) => productAvailability(params));
}
