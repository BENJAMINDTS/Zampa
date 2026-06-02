/**
 * @fileoverview Orders Alpine store for the public menu.
 *   Shows the list of submitted orders and their detail.
 *   Registered as `Alpine.store('orders')` for global access.
 *   Used in resources/views/menu/show.blade.php.
 * @module orders
 * @author Ayrton
 */

/**
 * Registers the `orders` global Alpine store.
 * Reads initial order list from `<script id="all-orders" type="application/json">`.
 *
 * @returns {void}
 */
export function registerOrders() {
    const raw  = document.getElementById('all-orders');
    const list = raw ? JSON.parse(raw.textContent) : [];

    Alpine.store('orders', {
        /** @type {boolean} Whether the orders panel is open. */
        open: false,

        /** @type {number|null} ID of the order shown in detail view, null = list view. */
        selectedId: null,

        /** @type {Array<Object>} All orders for this table session. */
        list,

        /** @type {Object|null} Order to show in the confirmed popup, null = hidden. */
        confirmedOrder: null,

        /** @returns {Object|null} The currently selected order, or null. */
        get selected() {
            return this.list.find(o => o.id === this.selectedId) ?? null;
        },

        /** @returns {number} Total sum of all order amounts. */
        get grandTotal() {
            return this.list.reduce((s, o) => s + o.total, 0);
        },

        /** @returns {number} Number of orders in the list. */
        get count() {
            return this.list.length;
        },

        /** Opens the panel showing the orders list. */
        openList() {
            this.open       = true;
            this.selectedId = null;
        },

        /** Navigates to the detail view for a given order ID. */
        openDetail(id) {
            this.selectedId = id;
        },

        /** Navigates back to the list view from the detail view. */
        backToList() {
            this.selectedId = null;
        },

        /** Closes the panel and resets the selected order. */
        close() {
            this.open       = false;
            this.selectedId = null;
        },

        /**
         * Appends a newly submitted order to the list and shows the
         * order-confirmed popup automatically.
         *
         * @param {Object} order - { id, number, itemCount, total, sentAt, items[] }
         */
        push(order) {
            this.list.push(order);
            this.confirmedOrder = order;
        },

        /** Shows the confirmed popup for a given order. */
        showConfirmed(order) {
            this.confirmedOrder = order;
        },

        /** Dismisses the order-confirmed popup. */
        dismissConfirmed() {
            this.confirmedOrder = null;
        },

        /**
         * Formats a number as a Euro price string.
         *
         * @param {number} n
         * @returns {string}
         */
        fmt(n) {
            return Number(n).toFixed(2).replace('.', ',') + ' €';
        },
    });
}
