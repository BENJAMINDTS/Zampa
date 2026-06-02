/**
 * Alpine.js component — ticket config editor.
 * Reads initial state from the #ticket-config-init JSON script tag
 * injected by the Blade view to avoid embedding PHP values inline.
 *
 * @author BenjaminDTS
 */

/**
 * @returns {object}
 */
export function ticketConfigData() {
    const el   = document.getElementById('ticket-config-init');
    const init = el ? JSON.parse(el.textContent) : {};

    return {
        template:        init.template        ?? 'classic',
        savedTemplate:   init.savedTemplate   ?? 'classic',
        templateLabels:  { classic: 'Clásica', modern: 'Moderna', minimal: 'Minimalista' },
        taxId:           init.taxId           ?? '',
        savedTaxId:      init.savedTaxId      ?? '',
        footerText:      init.footerText      ?? '',
        savedFooterText: init.savedFooterText ?? '',
        footerCount:     init.footerCount     ?? 0,
        logoUrl:         init.logoUrl         ?? '',
        logoChanged:     false,
        businessName:    init.businessName    ?? '',

        /**
         * @param {Event} e
         * @returns {void}
         */
        handleLogo(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.logoChanged = true;
            const reader     = new FileReader();
            reader.onload    = ev => { this.logoUrl = ev.target.result; };
            reader.readAsDataURL(file);
        },
    };
}

/**
 * @param {import('alpinejs').Alpine} Alpine
 * @returns {void}
 */
export function registerTicketConfig(Alpine) {
    Alpine.data('ticketConfigData', () => ticketConfigData());
}
