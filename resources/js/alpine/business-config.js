/**
 * @fileoverview Business configuration Alpine component — schedule management
 *   and tapas toggles. Used in resources/views/negocio/config.blade.php.
 * @module business-config
 * @author BenjaminDTS
 */

/** @constant {number} Maximum number of schedule slots allowed per type. */
const MAX_SLOTS = 4;

/**
 * Creates an empty time-slot object with blank open/close times.
 *
 * @returns {{ opens_at: string, closes_at: string }}
 */
export function emptySlot() {
    return { opens_at: '', closes_at: '' };
}

/**
 * Adds a slot to the list if the maximum has not been reached.
 *
 * @param {Array<Object>} slots - Current slots array (mutated in place).
 * @param {number}        max   - Maximum allowed slots.
 * @returns {void}
 */
export function addScheduleSlot(slots, max = MAX_SLOTS) {
    if (slots.length < max) slots.push(emptySlot());
}

/**
 * Removes the slot at the given index from the array (mutates in place).
 *
 * @param {Array<Object>} slots - Current slots array.
 * @param {number}        index - Index to remove.
 * @returns {void}
 */
export function removeScheduleSlot(slots, index) {
    slots.splice(index, 1);
}

/**
 * Returns true when the slots array has reached or exceeded the limit.
 *
 * @param {Array<Object>} slots - Current slots array.
 * @param {number}        max   - Maximum allowed slots.
 * @returns {boolean}
 */
export function maxSlotsReached(slots, max = MAX_SLOTS) {
    return slots.length >= max;
}

/**
 * Reads initial configuration from `<script id="config-init" type="application/json">`.
 *
 * @returns {Object} Parsed config object, or empty object if missing.
 */
export function readConfigInit() {
    const raw = document.getElementById('config-init');
    return raw ? JSON.parse(raw.textContent) : {};
}

/**
 * Alpine component factory for the business configuration form.
 * Reads all initial values from the injected JSON data block.
 *
 * @returns {Object} Alpine component with schedule and tapa state.
 */
export function businessConfig() {
    const cfg = readConfigInit();

    return {
        /** @type {boolean} */
        tapas_enabled: cfg.tapas_enabled ?? false,
        /** @type {boolean} */
        tapas_free: cfg.tapas_free ?? false,
        /** @type {string} */
        price_mode: cfg.price_mode ?? 'fixed',
        /** @type {boolean} */
        extra_tapa_enabled: cfg.extra_tapa_enabled ?? false,
        /** @type {Array<{opens_at: string, closes_at: string}>} */
        kitchen_schedules: cfg.kitchen_schedules ?? [],
        /** @type {Array<{opens_at: string, closes_at: string}>} */
        business_schedules: cfg.business_schedules ?? [],
        /** @type {boolean} */
        split_enabled: cfg.split_enabled ?? false,

        addKitchenSchedule() {
            addScheduleSlot(this.kitchen_schedules);
        },

        /**
         * @param {number} i - Index of the slot to remove.
         */
        removeKitchenSchedule(i) {
            removeScheduleSlot(this.kitchen_schedules, i);
        },

        addBusinessSchedule() {
            addScheduleSlot(this.business_schedules);
        },

        /**
         * @param {number} i - Index of the slot to remove.
         */
        removeBusinessSchedule(i) {
            removeScheduleSlot(this.business_schedules, i);
        },
    };
}

/**
 * Registers the `businessConfig` Alpine.data component.
 *
 * @returns {void}
 */
export function registerBusinessConfig() {
    Alpine.data('businessConfig', businessConfig);
}
