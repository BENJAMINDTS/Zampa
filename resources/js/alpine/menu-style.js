/**
 * Alpine.js component — menu style selector.
 * Manages the live demo preview for the three menu themes: modern, classic, minimal.
 *
 * @author BenjaminDTS
 */

/**
 * @param {string} initialStyle  One of 'modern' | 'classic' | 'minimal'.
 * @returns {object}
 */
export function menuStylePage(initialStyle) {
    return {
        menu_style:     initialStyle,
        savedMenuStyle: initialStyle,
        styleLabels:    { modern: 'Moderno', classic: 'Clásico', minimal: 'Minimalista' },

        /* ── Demo state ───────────────────────────────────────────────────────── */
        demoFilter: 'all',
        demoCart:   {},
        demoCategories: [
            { id: 'all',    label: 'Todo' },
            { id: 'food',   label: 'Entrantes' },
            { id: 'drinks', label: 'Bebidas' },
        ],
        demoProducts: [
            { id: 1, name: 'Pizza Margherita',  desc: 'Tomate, mozzarella, albahaca', price: 12.00, cat: 'food',   emoji: '🍕' },
            { id: 2, name: 'Croquetas caseras', desc: 'Bechamel y jamón ibérico',     price: 8.50,  cat: 'food',   emoji: '🥘' },
            { id: 3, name: 'Agua mineral',      desc: '500 ml, con o sin gas',        price: 1.50,  cat: 'drinks', emoji: '💧' },
            { id: 4, name: 'Vino de la casa',   desc: 'Tinto, blanco o rosado',       price: 3.00,  cat: 'drinks', emoji: '🍷' },
        ],

        get demoFiltered() {
            return this.demoFilter === 'all'
                ? this.demoProducts
                : this.demoProducts.filter(p => p.cat === this.demoFilter);
        },
        get demoCartCount() {
            return Object.values(this.demoCart).reduce((s, q) => s + q, 0);
        },
        get demoCartTotal() {
            const total = Object.entries(this.demoCart).reduce((s, [id, q]) => {
                const p = this.demoProducts.find(x => x.id == id);
                return s + (p ? p.price * q : 0);
            }, 0);
            return total.toFixed(2).replace('.', ',');
        },
        demoQty(id)    { return this.demoCart[id] || 0; },
        demoAdd(id)    { this.demoCart = { ...this.demoCart, [id]: (this.demoCart[id] || 0) + 1 }; },
        demoRemove(id) {
            const q = (this.demoCart[id] || 0) - 1;
            const c = { ...this.demoCart };
            if (q <= 0) delete c[id]; else c[id] = q;
            this.demoCart = c;
        },
        demoReset() {
            this.demoCart   = {};
            this.demoFilter = 'all';
        },

        /* ── Theme tokens (change with menu_style) ────────────────────────────── */
        get theme() {
            const t = {
                modern: {
                    wrapBg:        '#F3F4F6',
                    phoneBg:       '#FFFFFF',
                    headerBg:      '#FFFFFF',
                    border:        '#F3F4F6',
                    text:          '#111827',
                    textSec:       '#6B7280',
                    accent:        '#16A34A',
                    chipBg:        '#F3F4F6',
                    chipActiveBg:  '#16A34A',
                    chipActiveTxt: '#FFFFFF',
                    cardBg:        '#F9FAFB',
                    cardBorder:    '#F3F4F6',
                    cardRadius:    '16px',
                    imageBg:       '#DCFCE7',
                    imageRadius:   '12px',
                    price:         '#FBBF24',
                    btnText:       '#FFFFFF',
                    btnRadius:     '10px',
                    chipRadius:    '9999px',
                    cartBg:        'linear-gradient(180deg,#0F3D2A,#0A2E1F)',
                    cartBorder:    '#16A34A',
                    cartPrice:     '#FBBF24',
                    shadow:        '0 1px 2px rgba(0,0,0,.04), 0 4px 10px rgba(0,0,0,.05)',
                    font:          'system-ui, sans-serif',
                    fontDisplay:   'system-ui, sans-serif',
                },
                classic: {
                    wrapBg:        '#F4ECDA',
                    phoneBg:       '#FFFDF6',
                    headerBg:      '#F4ECDA',
                    border:        '#E3D6BB',
                    text:          '#2E2013',
                    textSec:       '#5C4A33',
                    accent:        '#7C5326',
                    chipBg:        '#E3D6BB',
                    chipActiveBg:  '#7C5326',
                    chipActiveTxt: '#FFFFFF',
                    cardBg:        '#FBF4E4',
                    cardBorder:    '#C9B690',
                    cardRadius:    '4px',
                    imageBg:       '#E3D6BB',
                    imageRadius:   '3px',
                    price:         '#A6791E',
                    btnText:       '#FFFFFF',
                    btnRadius:     '3px',
                    chipRadius:    '3px',
                    cartBg:        'linear-gradient(180deg,#8A5A22,#5A3A12)',
                    cartBorder:    '#A6791E',
                    cartPrice:     '#E0B864',
                    shadow:        '0 1px 2px rgba(0,0,0,.04)',
                    font:          'Georgia, serif',
                    fontDisplay:   'Georgia, serif',
                },
                minimal: {
                    wrapBg:        '#FAFAFA',
                    phoneBg:       '#FFFFFF',
                    headerBg:      '#FFFFFF',
                    border:        '#ECECEE',
                    text:          '#111111',
                    textSec:       '#52525B',
                    accent:        '#111111',
                    chipBg:        '#FFFFFF',
                    chipActiveBg:  '#111111',
                    chipActiveTxt: '#FFFFFF',
                    cardBg:        '#FFFFFF',
                    cardBorder:    '#ECECEE',
                    cardRadius:    '0px',
                    imageBg:       '#F4F4F5',
                    imageRadius:   '0px',
                    price:         '#111111',
                    btnText:       '#FFFFFF',
                    btnRadius:     '0px',
                    chipRadius:    '0px',
                    cartBg:        'linear-gradient(180deg,#18181B,#09090B)',
                    cartBorder:    '#232327',
                    cartPrice:     '#FAFAFA',
                    shadow:        'none',
                    font:          'Arial, sans-serif',
                    fontDisplay:   '"Courier New", monospace',
                },
            };
            return t[this.menu_style] || t.modern;
        },
    };
}

/**
 * @param {import('alpinejs').Alpine} Alpine
 * @returns {void}
 */
export function registerMenuStyle(Alpine) {
    Alpine.data('menuStylePage', (initialStyle) => menuStylePage(initialStyle));
}
