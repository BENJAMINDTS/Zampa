/**
 * @fileoverview Bar panel Alpine components: bar item polling, bill-request
 *   polling, and notification polling. Used in resources/views/bar/index.blade.php.
 * @module bar-panel
 * @author BenjaminDTS
 */

const BILL_POLL_MS         = 15000;
const NOTIFICATION_POLL_MS = 20000;
const WAITER_CALL_POLL_MS  = 15000;
const BAR_PANEL_TICK_MS    = 5000;

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
            setInterval(() => this.poll(), BILL_POLL_MS);
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
 * Uses sessionStorage to track acknowledged order IDs so that a page refresh
 * does not re-surface notifications the waiter already saw. Only genuinely new
 * notifications (order IDs not previously acknowledged in this browser session)
 * are shown. Explicit dismiss clears both the server flag and the local record
 * so a future re-trigger for the same order will show again correctly.
 *
 * @param {Array<Object>} initialOrders - Pre-loaded ready orders from PHP.
 * @param {Object}        urls          - API endpoint URL map.
 * @returns {Object} Alpine component definition.
 */
export function notificationPolling(initialOrders, urls) {
    const STORAGE_KEY = 'zampa:bar:ack-notifications';

    /** @returns {Set<number>} Order IDs already acknowledged in this session. */
    function getAcknowledged() {
        try {
            return new Set(JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '[]'));
        } catch {
            return new Set();
        }
    }

    /** @param {number} id */
    function addAcknowledged(id) {
        try {
            const set = getAcknowledged();
            set.add(id);
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify([...set]));
        } catch {
            // sessionStorage unavailable — degrade silently
        }
    }

    /** @param {number} id */
    function removeAcknowledged(id) {
        try {
            const set = getAcknowledged();
            set.delete(id);
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify([...set]));
        } catch {
            // silent
        }
    }

    return {
        /**
         * Orders ready to be served, pre-filtered so previously-acknowledged
         * orders do not reappear as ghost notifications after a page refresh.
         *
         * @type {Array<Object>}
         */
        readyOrders: initialOrders.filter(o => !getAcknowledged().has(o.id)),

        init() {
            // Mark currently-visible orders as acknowledged so a page refresh
            // does not re-surface them before the waiter takes action.
            this.readyOrders.forEach(o => addAcknowledged(o.id));
            this.poll();
            setInterval(() => this.poll(), NOTIFICATION_POLL_MS);
        },

        async poll() {
            try {
                const res = await fetch(urls.notificationsReady, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) return;
                const data      = await res.json();
                const acked     = getAcknowledged();
                const serverIds = new Set(data.orders.map(o => o.id));

                // Genuinely new orders: server reports ready, never shown before.
                const newOrders = data.orders.filter(o => !acked.has(o.id));
                newOrders.forEach(o => addAcknowledged(o.id));

                const visibleIds = new Set(this.readyOrders.map(o => o.id));

                this.readyOrders = [
                    // Keep currently-visible orders that the server still reports as ready.
                    ...this.readyOrders.filter(o => serverIds.has(o.id)),
                    // Append new orders not yet in the visible list.
                    ...newOrders.filter(o => !visibleIds.has(o.id)),
                ];
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
                // Remove acknowledgement so a future re-trigger shows the notification again.
                removeAcknowledged(id);
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
            this.pollInterval = setInterval(() => this.tick(), BAR_PANEL_TICK_MS);
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
 * Alpine component for showing waiter-call notifications from customers.
 *
 * @param {Object} urls - API endpoint URL map.
 * @returns {Object} Alpine component definition.
 */
export function waiterCallPolling(urls) {
    return {
        /** @type {Array<Object>} Active waiter call requests. */
        waiterOrders: [],

        init() {
            this.poll();
            setInterval(() => this.poll(), WAITER_CALL_POLL_MS);
        },

        async poll() {
            try {
                const res = await fetch(urls.waiterCalls, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) return;
                const data = await res.json();
                this.waiterOrders = data.orders ?? [];
            } catch {
                // silent
            }
        },

        async dismiss(id) {
            const url = urls.waiterDismissTemplate.replace('__ID__', id);
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
                this.waiterOrders = this.waiterOrders.filter(o => o.id !== id);
            } catch {
                // keep item — next poll will reflect DB
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
    const urls          = readBarUrls();
    const readyOrders   = readReadyOrders();
    const initialOrders = readBarInit();

    Alpine.data('billRequestPolling',  () => billRequestPolling(urls));
    Alpine.data('notificationPolling', () => notificationPolling(readyOrders, urls));
    Alpine.data('waiterCallPolling',   () => waiterCallPolling(urls));
    Alpine.data('barPanel',            () => barPanel(initialOrders, urls));
}
