/**
 * @fileoverview Cart Alpine store and tapa-modal logic for the public menu.
 *   Registered as `Alpine.store('cart')` for global access across components.
 *   Used in resources/views/menu/show.blade.php.
 * @module cart
 * @author BenjaminDTS
 */

/**
 * Calculates the total price for a single line item, including any extra mods.
 *
 * @param {{ price: number, quantity: number, mods: Array<{action: string, amountCharged: number}> }} item
 * @returns {number} Line total in euros.
 */
export function calculateLineTotal(item) {
    const extra = item.mods
        .filter(m => m.action === 'add')
        .reduce((s, m) => s + m.amountCharged, 0);
    return (item.price + extra) * item.quantity;
}

/**
 * Calculates the sum of all line totals in the cart.
 *
 * @param {Array<Object>} items - Cart items.
 * @returns {number} Grand total.
 */
export function calculateCartTotal(items) {
    return items.reduce((s, item) => s + calculateLineTotal(item), 0);
}

/**
 * Returns true when the tapa modal should be shown given current cart state and config.
 *
 * @param {{ enabled: boolean, kitchenOpen: boolean, maxVariants: number }} cfg
 * @param {number} barItemsCount  - Number of bar items in the current cart.
 * @param {number} variantsUsed   - Number of tapa variants already selected.
 * @param {Array}  tapaProducts   - Available tapa products.
 * @returns {boolean}
 */
export function shouldShowTapaModal(cfg, barItemsCount, variantsUsed, tapaProducts) {
    if (!cfg.enabled)                    return false;
    if (!cfg.kitchenOpen)                return false;
    if (barItemsCount <= 0)              return false;
    if (variantsUsed >= cfg.maxVariants) return false;
    if (tapaProducts.length === 0)       return false;
    return true;
}

/**
 * Reads menu context (tableHash + bill init data) from
 * `<script id="menu-context" type="application/json">`.
 *
 * @returns {Object} Parsed context or empty object.
 */
export function readMenuContext() {
    const raw = document.getElementById('menu-context');
    return raw ? JSON.parse(raw.textContent) : {};
}

/**
 * Reads tapa configuration from `<script id="tapa-config" type="application/json">`.
 *
 * @returns {Object} Tapa config or empty object.
 */
export function readTapaConfig() {
    const raw = document.getElementById('tapa-config');
    return raw ? JSON.parse(raw.textContent) : {};
}

/**
 * Reads tapa product list from `<script id="tapa-products" type="application/json">`.
 *
 * @returns {Array<Object>} Tapa products or empty array.
 */
export function readTapaProducts() {
    const raw = document.getElementById('tapa-products');
    return raw ? JSON.parse(raw.textContent) : [];
}

/**
 * Registers the `cart` global Alpine store.
 * Reads all configuration from injected JSON script tags.
 *
 * @returns {void}
 */
export function registerCart() {
    const ctx          = readMenuContext();
    const tapaConfig   = readTapaConfig();
    const tapaProdList = readTapaProducts();

    Alpine.store('cart', {
        /** @type {Array<Object>} Items currently in the cart. */
        items:   [],
        /** @type {boolean} Whether the cart drawer is open. */
        open:    false,
        /** @type {boolean} True while the close animation is running. */
        closing: false,
        /** @type {boolean} Submission in progress. */
        sending: false,
        /** @type {boolean} Order successfully sent. */
        sent:    false,
        /** @type {string|null} Last error message. */
        error:   null,
        /** @type {string} Unique table hash from URL. */
        tableHash: ctx.tableHash ?? '',

        showTapaModal:  false,
        tapaConfig:     tapaConfig,
        tapaProducts:   tapaProdList,
        _barItemsCount: tapaConfig.barItemsCount ?? 0,
        _variantsUsed:  tapaConfig.variantsUsed  ?? 0,

        close() {
            if (this.closing) return;
            this.closing = true;
            this.open = false;
            setTimeout(() => { this.closing = false; }, 260);
        },

        add(product) {
            const key      = product.id + ':none';
            const existing = this.items.find(i => i._key === key);
            if (existing) {
                existing.quantity++;
            } else {
                this.items.push({
                    _key:        key,
                    productId:   product.id,
                    variantId:   null,
                    variantName: null,
                    name:        product.name,
                    price:       product.price,
                    quantity:    1,
                    destination: product.destination ?? null,
                    mods:        [],
                    removable:   product.removable || [],
                    extras:      product.extras    || [],
                });
            }
            if (product.destination === 'bar') {
                this._barItemsCount++;
                this._checkTapaSuggestion();
            }
        },

        addWithVariant(productId, variantId, variantName, variantPrice, productData) {
            const key      = productId + ':' + variantId;
            const existing = this.items.find(i => i._key === key);
            if (existing) {
                existing.quantity++;
            } else {
                this.items.push({
                    _key:        key,
                    productId:   productId,
                    variantId:   variantId,
                    variantName: variantName,
                    name:        (productData?.name ?? '') + ' — ' + variantName,
                    price:       variantPrice,
                    quantity:    1,
                    destination: productData?.destination ?? null,
                    mods:        [],
                    removable:   [],
                    extras:      [],
                });
            }
            if (productData?.destination === 'bar') {
                this._barItemsCount++;
                this._checkTapaSuggestion();
            }
        },

        _checkTapaSuggestion() {
            this.showTapaModal = shouldShowTapaModal(
                this.tapaConfig,
                this._barItemsCount,
                this._variantsUsed,
                this.tapaProducts,
            );
        },

        closeTapaModal() {
            this.showTapaModal = false;
        },

        addTapa(tapaProduct) {
            this.add({
                id:          tapaProduct.id,
                name:        tapaProduct.name,
                price:       tapaProduct.price,
                destination: 'kitchen',
                removable:   [],
                extras:      [],
            });
            this._variantsUsed++;
            this.showTapaModal = false;
        },

        inc(key) {
            const item = this.items.find(i => i._key === key);
            if (item) item.quantity++;
        },

        dec(key) {
            const idx = this.items.findIndex(i => i._key === key);
            if (idx === -1) return;
            this.items[idx].quantity--;
            if (this.items[idx].quantity <= 0) this.items.splice(idx, 1);
        },

        toggleRemove(key, ing) {
            const item = this.items.find(i => i._key === key);
            if (!item) return;
            const i = item.mods.findIndex(m => m.ingredientId === ing.id && m.action === 'remove');
            if (i === -1) item.mods.push({ ingredientId: ing.id, name: ing.name, action: 'remove', amountCharged: 0 });
            else          item.mods.splice(i, 1);
        },

        toggleExtra(key, ing) {
            const item = this.items.find(i => i._key === key);
            if (!item) return;
            const i = item.mods.findIndex(m => m.ingredientId === ing.id && m.action === 'add');
            if (i === -1) item.mods.push({ ingredientId: ing.id, name: ing.name, action: 'add', amountCharged: ing.price });
            else          item.mods.splice(i, 1);
        },

        hasMod(key, ingId, action) {
            const item = this.items.find(i => i._key === key);
            return item ? item.mods.some(m => m.ingredientId === ingId && m.action === action) : false;
        },

        lineTotal(item) {
            return calculateLineTotal(item);
        },

        get total() {
            return calculateCartTotal(this.items);
        },

        get count() {
            return this.items.reduce((s, i) => s + i.quantity, 0);
        },

        get sendLabel() {
            const dests = [...new Set(this.items.map(i => i.destination).filter(Boolean))];
            if (dests.length === 1 && dests[0] === 'bar')     return '🍺 Enviar pedido a barra';
            if (dests.length === 1 && dests[0] === 'kitchen') return '🍽️ Enviar pedido a cocina';
            return '🛎️ Enviar pedido';
        },

        fmt(n) {
            return n.toFixed(2).replace('.', ',') + ' €';
        },

        async send() {
            if (!this.items.length || this.sending) return;
            this.sending = true;
            this.error   = null;
            try {
                const res = await fetch('/api/v1/orders', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({
                        table_hash: this.tableHash,
                        items: this.items.map(item => ({
                            product_id:    item.productId,
                            variant_id:    item.variantId ?? null,
                            quantity:      item.quantity,
                            modifications: item.mods.map(m => ({
                                ingredient_id:  m.ingredientId,
                                action:         m.action,
                                amount_charged: m.amountCharged,
                            })),
                        })),
                    }),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    const bill              = Alpine.store('bill');
                    bill.active             = true;
                    bill.requested          = false;
                    bill.method             = null;
                    bill.paymentDone        = false;
                    bill.orderTotal         = parseFloat(data.total) || 0;
                    bill.grandTotal         = parseFloat(data.total) || 0;
                    bill.originalOrderTotal = parseFloat(data.total) || 0;
                    this.sent  = true;
                    this.items = [];
                    this.open  = false;
                } else {
                    this.error = data.message ?? 'Error al enviar el pedido.';
                }
            } catch {
                this.error = 'Error de conexión. Inténtalo de nuevo.';
            } finally {
                this.sending = false;
            }
        },
    });
}
