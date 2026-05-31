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
 * Reads menu context from `<script id="menu-context" type="application/json">`.
 *
 * @returns {Object} Parsed context or empty object.
 */
function readMenuContext() {
    const raw = document.getElementById('menu-context');
    return raw ? JSON.parse(raw.textContent) : {};
}

/**
 * Registers the `menuFilters` Alpine.data component.
 *
 * @returns {void}
 */
export function registerMenuFilters() {
    Alpine.data('menuFilters', () => {
        const products = readMenuProducts();
        const ctx      = readMenuContext();

        return {
            products,
            /** @type {string[]} Allergen type keys currently excluded. */
            activeAllergens:   [],
            /** @type {string|null} */
            activeDestination: null,
            /** @type {number|null} */
            activeCategory:    null,

            /** @type {Object|null} Product currently shown in the detail drawer. */
            selectedProduct:   null,
            /** @type {boolean} True while the close animation is running. */
            pdetailClosing:    false,
            /** @type {number} Quantity selected in the detail drawer. */
            pdetailQty:        1,
            /** @type {number[]} Ingredient IDs marked for removal. */
            pdetailRemovedIds: [],
            /** @type {number[]} Extra ingredient IDs selected. */
            pdetailExtraIds:   [],
            /** @type {boolean} Whether the kitchen is currently open. */
            kitchenOpen:       ctx.kitchenOpen ?? true,

            /**
             * Opens the product detail drawer for the given product.
             *
             * @param {Object} product - Product object from the products array.
             * @returns {void}
             */
            openProduct(product) {
                if (!product) return;
                this.selectedProduct = product;
                this.pdetailClosing  = false;
                const key      = product.id + ':none';
                const cartItem = Alpine.store('cart').items.find(i => i._key === key);
                this.pdetailQty        = cartItem ? cartItem.quantity : 1;
                this.pdetailRemovedIds = cartItem
                    ? cartItem.mods.filter(m => m.action === 'remove').map(m => m.ingredientId)
                    : [];
                this.pdetailExtraIds   = cartItem
                    ? cartItem.mods.filter(m => m.action === 'add').map(m => m.ingredientId)
                    : [];
                this.$nextTick(() => this._fillPdetailAllergens());
            },

            /**
             * Closes the product detail drawer with the exit animation.
             *
             * @returns {void}
             */
            closeProduct() {
                if (this.pdetailClosing) return;
                this.pdetailClosing = true;
                setTimeout(() => { this.selectedProduct = null; this.pdetailClosing = false; }, 260);
            },

            /**
             * Re-initialises allergen SVG pictograms inside the pdetail after Alpine renders.
             *
             * @returns {void}
             */
            _fillPdetailAllergens() {
                const p = window.ZAMPA_PICTOGRAMS;
                if (!p) return;
                document.querySelectorAll('.pdetail svg[data-al]').forEach(svg => {
                    const slug = svg.getAttribute('data-al');
                    if (!slug) return;
                    const c     = p.ALLERGEN_COLORS[slug] || '#888';
                    const inner = p.pictogramSVG(slug);
                    if (!inner) return;
                    svg.style.setProperty('--c', c);
                    svg.innerHTML = inner;
                });
            },

            /**
             * Applies the quantity, removed ingredients, and extras from the detail
             * drawer to the cart, then closes the drawer.
             *
             * @returns {void}
             */
            pdetailAddToCart() {
                if (!this.selectedProduct) return;
                const cart       = Alpine.store('cart');
                const product    = this.selectedProduct;
                const key        = product.id + ':none';
                const cartItem   = cart.items.find(i => i._key === key);
                const currentQty = cartItem ? cartItem.quantity : 0;
                const targetQty  = this.pdetailQty;

                if (targetQty > currentQty) {
                    for (let i = 0; i < targetQty - currentQty; i++) cart.add(product);
                } else if (targetQty < currentQty) {
                    for (let i = 0; i < currentQty - targetQty; i++) cart.dec(key);
                }

                const updated = cart.items.find(i => i._key === key);
                if (updated) {
                    updated.mods = [];
                    for (const ingId of this.pdetailRemovedIds) {
                        const ing = product.removable?.find(r => r.id === ingId);
                        if (ing) updated.mods.push({ ingredientId: ingId, name: ing.name, action: 'remove', amountCharged: 0 });
                    }
                    for (const ingId of this.pdetailExtraIds) {
                        const ing = product.extras?.find(e => e.id === ingId);
                        if (ing) updated.mods.push({ ingredientId: ingId, name: ing.name, action: 'add', amountCharged: ing.price });
                    }
                }
                this.closeProduct();
            },

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
