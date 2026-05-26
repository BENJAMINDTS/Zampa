/**
 * @fileoverview Bar panel Alpine components: bar item polling, bill-request
 *   polling, and notification polling. Used in resources/views/bar/index.blade.php.
 * @module bar-panel
 * @author BenjaminDTS
 */

/**
 * Reads URL configuration from `<script id="bar-urls" type="application/json">`.
 *
 * @returns {Object} URL map for all bar API endpoints.
 */
export function readBarUrls() {
    const raw = document.getElementById('bar-urls');
    return raw ? JSON.parse(raw.textContent) : {};
}

/**
 * Reads initial ready-orders list from `<script id="bar-ready-orders" type="application/json">`.
 *
 * @returns {Array<Object>} Pre-loaded ready orders.
 */
export function readReadyOrders() {
    const raw = document.getElementById('bar-ready-orders');
    return raw ? JSON.parse(raw.textContent) : [];
}

/**
 * Reads initial bar orders from `<script id="bar-init" type="application/json">`.
 *
 * @returns {Array<Object>} Pre-loaded bar orders.
 */
export function readBarInit() {
    const raw = document.getElementById('bar-init');
    return raw ? JSON.parse(raw.textContent) : [];
}

/**
 * Merges incoming bar-panel poll data with local state, preserving `marking`
 * flags on in-flight items.
 *
 * @param {Array<Object>} incoming - Orders from the server.
 * @param {Array<Object>} current  - Current local orders.
 * @returns {Array<Object>} Merged result.
 */
export function mergeBarOrders(incoming, current) {
    const markingIds = {};
    current.forEach(o => o.items.forEach(i => { if (i.marking) markingIds[i.id] = true; }));

    return incoming.map(o => ({
        ...o,
        all_ready: false,
        items: o.items.map(i => ({ ...i, marking: !!markingIds[i.id] })),
    }));
}

/**
 * Merges incoming bill-request poll data with local state, preserving the
 * `paying` flag so active payment flows are not interrupted.
 *
 * @param {Array<Object>} incoming  - Orders from the server.
 * @param {Array<Object>} current   - Current local bill orders.
 * @returns {Array<Object>} Merged result with paying state preserved.
 */
export function mergeBillOrders(incoming, current) {
    const payingIds = {};
    current.forEach(o => { if (o.paying) payingIds[o.id] = true; });
    return incoming.map(o => ({ ...o, paying: !!payingIds[o.id] }));
}

/**
 * Counts items in an array whose status is pending (not yet served).
 *
 * @param {Array<Object>} items - Bar items to count.
 * @returns {number} Number of pending items.
 */
export function countPendingBarItems(items) {
    return items.filter(i => !i.marking).length;
}

/**
 * Alpine component for polling bill-account requests from customers.
 *
 * @param {Object} urls - API endpoint URL map.
 * @returns {Object} Alpine component definition.
 */
export function billRequestPolling(urls) {
    return {
        /** @type {Array<Object>} Active bill requests. */
        billOrders: [],

        init() {
            this.poll();
            setInterval(() => this.poll(), 15000);
        },

        async poll() {
            try {
                const res = await fetch(urls.billRequests, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) return;
                const data = await res.json();
                this.billOrders = mergeBillOrders(data.orders, this.billOrders);
            } catch {
                // silent
            }
        },

        async dismiss(id) {
            const url = urls.billDismissTemplate.replace('__ID__', id);
            try {
                const res = await fetch(url, {
                    method:  'PATCH',
                    headers: {
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept':           'application/json',
                    },
                });
                if (!res.ok) return;
                this.billOrders = this.billOrders.filter(o => o.id !== id);
            } catch {
                // keep item — next poll will reflect DB
            }
        },

        async cashPayment(order) {
            order.paying = true;
            try {
                const url = `${urls.payments}/${order.id}/cash`;
                const res = await fetch(url, {
                    method:  'POST',
                    headers: {
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept':           'application/json',
                    },
                });
                if (res.ok) {
                    this.billOrders = this.billOrders.filter(o => o.id !== order.id);
                }
            } catch {
                order.paying = false;
            }
        },
    };
}

/**
 * Alpine component for showing notifications when kitchen marks orders ready.
 *
 * @param {Array<Object>} initialOrders - Pre-loaded ready orders from PHP.
 * @param {Object}        urls          - API endpoint URL map.
 * @returns {Object} Alpine component definition.
 */
export function notificationPolling(initialOrders, urls) {
    return {
        /** @type {Array<Object>} Orders ready to be served. */
        readyOrders: initialOrders,

        init() {
            this.poll();
            setInterval(() => this.poll(), 20000);
        },

        async poll() {
            try {
                const res = await fetch(urls.notificationsReady, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) return;
                const data   = await res.json();
                this.readyOrders = data.orders;
            } catch {
                // silent
            }
        },

        async dismiss(id) {
            const url = urls.notificationsDismissTemplate.replace('__ID__', id);
            try {
                const res = await fetch(url, {
                    method:  'PATCH',
                    headers: {
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept':           'application/json',
                    },
                });
                if (!res.ok) return;
                this.readyOrders = this.readyOrders.filter(o => o.id !== id);
            } catch {
                // keep item — next poll will reflect DB
            }
        },
    };
}

/**
 * Alpine component for the main bar panel (bar item polling and ready marking).
 *
 * @param {Array<Object>} initialOrders - Pre-loaded bar orders from PHP.
 * @param {Object}        urls          - API endpoint URL map.
 * @returns {Object} Alpine component definition.
 */
export function barPanel(initialOrders, urls) {
    return {
        /** @type {Array<Object>} Active orders with bar items. */
        orders: initialOrders,

        /** @type {boolean} Whether the last poll succeeded. */
        polling: true,

        /** @type {string} Human-readable timestamp of last successful poll. */
        lastUpdated: '--:--',

        /** @type {number|null} setInterval handle. */
        pollInterval: null,

        init() {
            this.tick();
            this.pollInterval = setInterval(() => this.tick(), 5000);
        },

        async tick() {
            try {
                const res  = await fetch(urls.barPending, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await res.json();

                this.orders      = mergeBarOrders(data.orders, this.orders);
                this.polling     = true;
                this.lastUpdated = new Date().toLocaleTimeString('es-ES', {
                    hour: '2-digit', minute: '2-digit', second: '2-digit',
                });
            } catch {
                this.polling = false;
            }
        },

        async markReady(item, order) {
            item.marking = true;
            try {
                const res  = await fetch(`${urls.barItems}/${item.id}`, {
                    method:  'PATCH',
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
    };
}

/**
 * Registers all bar-panel Alpine.data components.
 * Reads initial state from injected JSON script tags.
 *
 * @returns {void}
 */
export function registerBarComponents() {
    const urls         = readBarUrls();
    const readyOrders  = readReadyOrders();
    const initialOrders = readBarInit();

    Alpine.data('billRequestPolling',  () => billRequestPolling(urls));
    Alpine.data('notificationPolling', () => notificationPolling(readyOrders, urls));
    Alpine.data('barPanel',            () => barPanel(initialOrders, urls));
}
