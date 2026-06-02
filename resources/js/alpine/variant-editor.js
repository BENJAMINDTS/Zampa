/**
 * Alpine.js component — variant editor.
 * Manages product variant list (add / remove / toggle).
 *
 * @author BenjaminDTS
 */

/**
 * @param {Array<{name: string, price: string|number, sort_order: number}>} initialVariants
 * @returns {object}
 */
export function variantEditor(initialVariants) {
    return {
        useVariants: initialVariants.length > 0,
        variants:    initialVariants.length > 0 ? initialVariants : [],

        enableVariants() {
            this.useVariants = true;
            if (this.variants.length === 0) {
                this.variants.push({ name: '', price: '', sort_order: 0 });
            }
        },

        disableVariants() {
            this.useVariants = false;
        },

        addVariant() {
            this.variants.push({ name: '', price: '', sort_order: this.variants.length });
        },

        removeVariant(index) {
            this.variants.splice(index, 1);
            if (this.variants.length === 0) {
                this.useVariants = false;
            }
        },
    };
}

/**
 * @param {import('alpinejs').Alpine} Alpine
 * @returns {void}
 */
export function registerVariantEditor(Alpine) {
    Alpine.data('variantEditor', (initialVariants) => variantEditor(initialVariants));
}
