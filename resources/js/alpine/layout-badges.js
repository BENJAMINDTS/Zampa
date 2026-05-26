/**
 * @fileoverview Navigation badge polling components for kitchen and bar panels.
 *   Used in resources/views/layouts/app.blade.php to display pending-item counts.
 * @module layout-badges
 * @author BenjaminDTS
 */

/**
 * Creates an Alpine.js component that polls a JSON endpoint and displays
 * the pending item count as a badge in the navigation bar.
 *
 * @param {string} url - Endpoint URL that returns `{ pending_count: number }`.
 * @returns {Object} Alpine component definition with `count` and `init`/`fetch`.
 */
export function badgePolling(url) {
    return {
        /** @type {number} Current pending count shown in the badge. */
        count: 0,

        init() {
            this.fetchCount();
            setInterval(() => this.fetchCount(), 10000);
        },

        async fetchCount() {
            try {
                const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                this.count = data.pending_count ?? 0;
            } catch {
                // Fail silently — badge simply stays at last known value.
            }
        },
    };
}

/**
 * Registers `kitchenBadge` and `barBadge` as Alpine.data components.
 * Called from app.js inside the `alpine:init` event listener.
 *
 * @returns {void}
 */
export function registerLayoutBadges() {
    Alpine.data('kitchenBadge', url => badgePolling(url));
    Alpine.data('barBadge',     url => badgePolling(url));
}
