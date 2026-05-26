/**
 * @fileoverview Menu filter Alpine component — allergen, destination, and
 *   category filtering for the public menu page.
 *   Registered as `Alpine.data('menuFilters')`.
 *   Used in resources/views/menu/show.blade.php.
 * @module menu-filters
 * @author BenjaminDTS
 */

/**
 * Returns true when the product passes all three active filters.
 *
 * @param {{ id: number, categoryId: number, destination: string, allergenTypes: string[] }} product
 * @param {number|null}   activeCategory    - Currently selected category ID, or null for all.
 * @param {string|null}   activeDestination - Currently selected destination, or null for all.
 * @param {string[]}      activeAllergens   - Allergen type strings to exclude.
 * @returns {boolean}
 */
export function isProductVisible(product, activeCategory, activeDestination, activeAllergens) {
    if (activeCategory !== null && product.categoryId !== activeCategory) return false;
    if (activeDestination !== null && product.destination !== activeDestination) return false;
    if (activeAllergens.some(type => product.allergenTypes.includes(type))) return false;
    return true;
}

/**
 * Returns true when at least one product in the category passes the active filters.
 *
 * @param {number}        categoryId
 * @param {Array<Object>} products
 * @param {number|null}   activeCategory
 * @param {string|null}   activeDestination
 * @param {string[]}      activeAllergens
 * @returns {boolean}
 */
export function isCategoryVisible(categoryId, products, activeCategory, activeDestination, activeAllergens) {
    if (activeCategory !== null && activeCategory !== categoryId) return false;
    const items = products.filter(p => p.categoryId === categoryId);
    if (items.length === 0) return true;
    return items.some(p => isProductVisible(p, activeCategory, activeDestination, activeAllergens));
}

/**
 * Toggles an allergen key in the active list (add if absent, remove if present).
 *
 * @param {string[]} activeAllergens - Current exclusion list (mutated).
 * @param {string}   id              - Allergen type key to toggle.
 * @returns {void}
 */
export function toggleAllergen(activeAllergens, id) {
    const idx = activeAllergens.indexOf(id);
    if (idx === -1) activeAllergens.push(id);
    else            activeAllergens.splice(idx, 1);
}

/**
 * Reads the products list from `<script id="menu-products" type="application/json">`.
 *
 * @returns {Array<Object>} Parsed products or empty array.
 */
export function readMenuProducts() {
    const raw = document.getElementById('menu-products');
    return raw ? JSON.parse(raw.textContent) : [];
}

/**
 * Registers the `menuFilters` Alpine.data component.
 *
 * @returns {void}
 */
export function registerMenuFilters() {
    Alpine.data('menuFilters', () => {
        const products = readMenuProducts();

        return {
            products,
            /** @type {string[]} Allergen type keys currently excluded. */
            activeAllergens:   [],
            /** @type {string|null} */
            activeDestination: null,
            /** @type {number|null} */
            activeCategory:    null,

            toggleAllergen(id) {
                toggleAllergen(this.activeAllergens, id);
            },

            setDestination(dest) {
                this.activeDestination = this.activeDestination === dest ? null : dest;
                if (this.activeCategory !== null && !this.isCategoryVisible(this.activeCategory)) {
                    this.activeCategory = null;
                }
                this.activeAllergens = this.activeAllergens.filter(a => this.visibleAllergenKeys.includes(a));
            },

            setCategory(id) {
                this.activeCategory = this.activeCategory === id ? null : id;
            },

            isProductVisible(productId) {
                const p = this.products.find(item => item.id === productId);
                if (!p) return true;
                return isProductVisible(p, this.activeCategory, this.activeDestination, this.activeAllergens);
            },

            isCategoryVisible(categoryId) {
                return isCategoryVisible(
                    categoryId,
                    this.products,
                    this.activeCategory,
                    this.activeDestination,
                    this.activeAllergens,
                );
            },

            get visibleAllergenKeys() {
                return [...new Set(
                    this.products
                        .filter(p => this.activeDestination === null || p.destination === this.activeDestination)
                        .flatMap(p => p.allergenTypes),
                )];
            },

            get visibleCount() {
                return this.products.filter(p => this.isProductVisible(p.id)).length;
            },

            get hasActiveFilters() {
                return this.activeAllergens.length > 0
                    || this.activeDestination !== null
                    || this.activeCategory !== null;
            },

            clearAll() {
                this.activeAllergens   = [];
                this.activeDestination = null;
                this.activeCategory    = null;
            },
        };
    });
}
