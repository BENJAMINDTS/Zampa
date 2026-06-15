/**
 * @fileoverview Chat widget Alpine component for the public menu (Zampi IA).
 *   Handles category browsing, cart integration, AI messaging, and quick replies.
 *   Registered as `Alpine.data('chatWidget')`.
 *   Used in resources/views/menu/show.blade.php.
 * @module chat-widget
 * @author BenjaminDTS
 */

/**
 * Returns a human-readable time label relative to `now`.
 *
 * @param {number} now - Current timestamp (ms).
 * @param {number} ts  - Message timestamp (ms).
 * @returns {string} 'Ahora' if under 60 s, otherwise 'HH:MM'.
 */
export function formatTime(now, ts) {
    if (!ts) return '';
    const diff = now - ts;
    if (diff < 60000) return 'Ahora';
    const d = new Date(ts);
    return d.getHours().toString().padStart(2, '0') + ':' + d.getMinutes().toString().padStart(2, '0');
}

/**
 * Returns an emoji that represents the given category name.
 *
 * @param {string} name - Category name (any case, may have accents).
 * @returns {string} A single emoji character.
 */
export function getCategoryEmoji(name) {
    const s = (name || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    if (/pizza/.test(s))                                               return '🍕';
    if (/burger|hamburgues/.test(s))                                   return '🍔';
    if (/pasta|espaguet|fettuccin|lasana|canelones|carbonara/.test(s)) return '🍝';
    if (/paella|arroz|risotto/.test(s))                                return '🍚';
    if (/ensalada|salad/.test(s))                                      return '🥗';
    if (/sopa|crema|caldo|gazpacho/.test(s))                           return '🥣';
    if (/postre|dulce|helado|tarta|bizcocho|mousse|flan|brownie/.test(s)) return '🍰';
    if (/cerveza|beer|cana|birra|caña/.test(s))                        return '🍺';
    if (/vino|wine|cava|champan|prosecco|sangria/.test(s))             return '🍷';
    if (/coctel|cocktail|mojito|margarita|daiquiri|gin/.test(s))       return '🍹';
    if (/bebida|refresco|agua|zumo|juice|batido|cola|limonad|cafe/.test(s)) return '🥤';
    if (/sushi|maki|nigiri|temaki/.test(s))                            return '🍣';
    if (/bocadillo|sandwich|bocata|baguet|wrap/.test(s))               return '🥪';
    if (/tapa|tapas|pincho|montadito|racion/.test(s))                  return '🍢';
    if (/entrante|aperitivo|starter/.test(s))                          return '🫔';
    if (/carne|ternera|pollo|pavo|cordero|parrilla|filete|chulet/.test(s)) return '🥩';
    if (/pescado|merluza|bacalao|salmon|atun|dorada|lubina/.test(s))   return '🐟';
    if (/marisco|gamba|langosta|pulpo|calamar|mejillo/.test(s))        return '🦞';
    if (/vegano|vegetarian|vegan|veggie/.test(s))                      return '🥦';
    if (/combo|menu del dia|menu|oferta/.test(s))                      return '🎯';
    return '🍽️';
}

/**
 * Returns an SVG icon descriptor for a given food item based on category and product names.
 *
 * @param {string} categoryName - Name of the category.
 * @param {string} productName  - Name of the product.
 * @returns {{ svgId: string, label: string }|null} Icon descriptor or null if none matches.
 */
export function getFoodIcon(categoryName, productName) {
    const norm = s => (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    const s = norm(categoryName) + ' ' + norm(productName);
    if (/pizza/.test(s))                                                                    return { svgId: 'pizza',     label: 'Pizza' };
    if (/burger|hamburgues/.test(s))                                                        return { svgId: 'burger',    label: 'Hamburguesa' };
    if (/pasta|espaguet|fettuccin|lasana|canelones|macarron|carbonara|bolonesa/.test(s))    return { svgId: 'pasta',     label: 'Pasta' };
    if (/paella|arroz|risotto/.test(s))                                                     return { svgId: 'rice',      label: 'Arroz' };
    if (/ensalada|salad/.test(s))                                                           return { svgId: 'salad',     label: 'Ensalada' };
    if (/sopa|crema|caldo|gazpacho|vichyssoise|consomme/.test(s))                           return { svgId: 'soup',      label: 'Sopa' };
    if (/tapa|tapas|pincho|montadito|ración|racion/.test(s))                                return { svgId: 'tapas',     label: 'Tapas' };
    if (/entrante|aperitivo|starter/.test(s))                                               return { svgId: 'starter',   label: 'Entrante' };
    if (/postre|dulce|helado|tarta|bizcocho|mousse|flan|brownie|crepe|tiramisu/.test(s))    return { svgId: 'dessert',   label: 'Postre' };
    if (/cerveza|beer|cana|birra|copa de|caña/.test(s))                                     return { svgId: 'beer',      label: 'Cerveza' };
    if (/vino|wine|cava|champan|prosecco|sangria/.test(s))                                  return { svgId: 'wine',      label: 'Vino' };
    if (/coctel|cocktail|mojito|margarita|daiquiri|gin|combinado|destilado/.test(s))        return { svgId: 'cocktail',  label: 'Cóctel' };
    if (/bebida|refresco|agua|zumo|juice|batido|smoothie|cola|limonad|infusion|cafe/.test(s)) return { svgId: 'drink',  label: 'Bebida' };
    if (/sushi|maki|nigiri|temaki|onigiri|japon/.test(s))                                   return { svgId: 'sushi',     label: 'Sushi' };
    if (/bocadillo|sandwich|bocata|baguet|wrap|sub/.test(s))                                return { svgId: 'sandwich',  label: 'Bocadillo' };
    if (/carne|ternera|vaca|cerdo|pollo|pavo|cordero|parrilla|grill|asado|filete|costill|entrecot|chulet/.test(s)) return { svgId: 'meat', label: 'Carne' };
    if (/pescado|merluza|bacalao|salmon|atun|dorada|lubina|lenguado|rodaballo/.test(s))     return { svgId: 'fish-dish', label: 'Pescado' };
    if (/marisco|gamba|langosta|pulpo|calamar|sepia|almeja|mejillo|ostra|bogavante|chipiro/.test(s)) return { svgId: 'seafood', label: 'Marisco' };
    if (/vegano|vegetarian|vegan|veggie/.test(s))                                           return { svgId: 'vegan',     label: 'Vegano' };
    return null;
}

/**
 * Returns SVG icon descriptor and label for a given allergen name (EU Regulation 1169/2011).
 *
 * @param {string} name - Allergen name.
 * @returns {{ svgId: string|null, label: string }} Icon descriptor.
 */
export function getAllergenIcon(name) {
    const n = name.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    if (/gluten|trigo|cebada|centeno|avena|espelta|kamut|harina/.test(n))
        return { svgId: 'gluten',          label: 'Gluten' };
    if (/crustaceo|gamba|langosta|langostino|cangrejo|bogavante|cigala/.test(n))
        return { svgId: 'crustaceos',      label: 'Crustáceos' };
    if (/huevo|yema|clara|ovoalbumina/.test(n))
        return { svgId: 'huevos',          label: 'Huevos' };
    if (/pescado|merluza|bacalao|salmon|atun|anchoa|sardina|boqueron|lenguado|dorada|lubina/.test(n))
        return { svgId: 'pescado',         label: 'Pescado' };
    if (/cacahuete|mani/.test(n))
        return { svgId: 'cacahuetes',      label: 'Cacahuetes' };
    if (/soja|soya|tofu|edamame/.test(n))
        return { svgId: 'soja',            label: 'Soja' };
    if (/leche|lacteo|lactosa|queso|mantequilla|nata|yogur|suero|caseina/.test(n))
        return { svgId: 'lacteos',         label: 'Lácteos' };
    if (/nuez|nueces|almendra|avellana|pistacho|anacardo|macadamia|pacana|brasil|castana/.test(n))
        return { svgId: 'frutos-cascara',  label: 'Frutos secos' };
    if (/apio/.test(n))
        return { svgId: 'apio',            label: 'Apio' };
    if (/mostaza/.test(n))
        return { svgId: 'mostaza',         label: 'Mostaza' };
    if (/sesamo|tahini|tahina/.test(n))
        return { svgId: 'sesamo',          label: 'Sésamo' };
    if (/sulfit|azufre|so2|dioxido/.test(n))
        return { svgId: 'sulfitos',        label: 'Sulfitos' };
    if (/altramuz|lupino|lupina/.test(n))
        return { svgId: 'altramuces',      label: 'Altramuces' };
    if (/molusco|calamar|pulpo|ostra|almeja|mejillon|sepia|chipiro/.test(n))
        return { svgId: 'moluscos',        label: 'Moluscos' };
    return { svgId: null, label: name };
}


/**
 * Registers the `chatWidget` Alpine.data component.
 *
 * @returns {void}
 */
export function registerChatWidget() {
    Alpine.data('chatWidget', () => ({
        open:           false,
        conversationId: null,
        messages:       [],
        input:          '',
        sending:        false,
        isTyping:       false,
        closed:         false,
        error:          null,
        menuData:           null,
        pendingVariantProd: null,
        msgSeq:             0,
        _now:           Date.now(),
        _ticker:        null,
        cartNotifs:           [],
        cartNotifSeq:         0,
        lastDiscussedProduct:  null,
        lastDiscussedProducts: [],

        get chatCart() {
            return Alpine.store('cart').items.map(i => ({
                id:    i.productId,
                name:  i.name,
                price: i.price,
                qty:   i.quantity,
            }));
        },

        init() {
            this._ticker = setInterval(() => { this._now = Date.now(); }, 30000);
            let _prevCartVisible = false;
            this.$watch(() => Alpine.store('cart').count, (count) => {
                const visible = count > 0;
                if (visible !== _prevCartVisible) {
                    _prevCartVisible = visible;
                    this.scrollBottom();
                }
            });
        },

        destroy() {
            clearInterval(this._ticker);
        },

        formatTime(ts) {
            return formatTime(this._now, ts);
        },

        get tableHash()    { return Alpine.store('cart').tableHash; },
        get cartCount()    { return Alpine.store('cart').count; },
        get cartTotal()    { return Alpine.store('cart').total; },
        get cartTotalStr() { return '$' + this.cartTotal.toFixed(2).replace('.', ','); },

        async openChat() {
            this.open = true;
            Alpine.store('chat').open = true;
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => {
                this.$refs.chatInput?.focus();
                this.scrollBottom();
            });
            const isFirstOpen = !this.messages.length;
            const promises = [];
            if (!this.menuData) promises.push(this.loadMenu());
            if (isFirstOpen) promises.push(this.startConversationBackend());
            await Promise.all(promises);
            if (isFirstOpen) this.initChatUI();
        },

        closeChat() {
            this.open = false;
            Alpine.store('chat').open = false;
            document.body.style.overflow = '';
        },

        async loadMenu() {
            try {
                const res = await fetch('/api/v1/menu/' + this.tableHash, {
                    headers: { 'Accept': 'application/json' },
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.success) this.menuData = data.data;
                }
            } catch { /* falla silenciosamente */ }
        },

        initChatUI() {
            const cats = this.menuData?.categories ?? [];
            const qrs  = cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);
            this.pushMsg({ type: 'system',
                text: (this.menuData?.table ?? 'Mesa') + ' · ' + (this.menuData?.restaurant ?? '') });
            this.pushMsg({ type: 'bot',
                text: '¡Hola! Soy Zampi, tu asistente de pedidos 🍔 ¿Qué te apetece hoy?',
                quickReplies: qrs });
        },

        async startConversationBackend() {
            try {
                const res = await fetch('/api/v1/chat/' + this.tableHash + '/start', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                });
                const data = await res.json();
                if (res.ok && data.success) this.conversationId = data.data.conversation_id;
            } catch { /* silent */ }
        },

        pushMsg(msg) {
            this.msgSeq++;
            this.messages.push({ _id: this.msgSeq, ...msg });
            this.$nextTick(() => this.scrollBottom());
            if (msg.cards && msg.cards.some(c => (c.allergens || []).some(a => a.svgId))) {
                setTimeout(() => window.zampiRefillAllergens?.(), 0);
            }
        },

        async botDelay(text, extra = {}, ms = 880) {
            this.isTyping = true;
            await new Promise(r => setTimeout(r, ms + Math.random() * 350));
            this.isTyping = false;
            this.pushMsg({ type: 'bot', text, ...extra });
        },

        handleQuickReply(label) {
            if (this.isTyping || this.sending) return;
            this.pushMsg({ type: 'user', text: label, time: Date.now() });

            // Variant selection for pending product
            if (this.pendingVariantProd) {
                const pending = this.pendingVariantProd;
                const variant = pending.variants.find(v => {
                    const expected = v.name + ' (' + Number(v.price).toFixed(2).replace('.', ',') + ' €)';
                    return label === expected || label === v.name;
                });
                if (variant) {
                    this.pendingVariantProd = null;
                    const qty = pending.qty || 1;
                    const existing = Alpine.store('cart').items.find(i => i.productId === pending.id && i.variantId === variant.id);
                    if (existing) {
                        existing.quantity += qty;
                    } else {
                        for (let _i = 0; _i < qty; _i++) {
                            Alpine.store('cart').addWithVariant(pending.id, variant.id, variant.name, variant.price, {
                                name: pending.name,
                                destination: pending.destination,
                            });
                        }
                    }
                    const cats = this.menuData?.categories ?? [];
                    this.botDelay(
                        '✅ ' + (qty > 1 ? qty + '× ' : '') + pending.name + ' (' + variant.name + ') añadido al pedido. ¿Algo más?',
                        { quickReplies: [...cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name), 'Confirmar pedido'] }
                    );
                    return;
                }
                // label didn't match any variant — clear pending and fall through
                this.pendingVariantProd = null;
            }

            const cats    = this.menuData?.categories ?? [];
            const matched = cats.find(c => label === this.getCategoryEmoji(c.name) + ' ' + c.name || c.name === label);
            if (matched) {
                this.showCategoryCards(matched);
            } else if (label === 'Confirmar pedido') {
                Alpine.store('cart').open = true;
            } else if (label === 'Seguir eligiendo') {
                const qrs = cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);
                this.botDelay('¿Qué más te gustaría pedir?', { quickReplies: qrs });
            } else if (label === '📋 Nuevo pedido') {
                Alpine.store('cart').items = [];
                const qrs = cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);
                this.botDelay('¡Claro! ¿Qué te gustaría pedir?', { quickReplies: qrs });
            } else {
                this.sendToAI(label);
            }
        },

        showCategoryCards(category) {
            const emoji = this.getCategoryEmoji(category.name);
            const cards = category.products.map(p => ({
                id:          p.id,
                name:        p.name,
                description: p.description || '',
                image:       p.image ?? null,
                price:       p.price,
                variants:    p.variants || [],
                destination: category.destination,
                allergens:   (p.ingredients || [])
                    .filter(i => i.is_allergen)
                    .map(i => ({ name: i.name, ...getAllergenIcon(i.name) })),
                foodIcon:    getFoodIcon(category.name, p.name),
                emoji,
            }));
            this.lastDiscussedProducts = cards;
            this.lastDiscussedProduct  = cards.length === 1 ? cards[0] : null;
            this.botDelay(
                'Aquí tienes nuestra selección de ' + category.name + ' 😋',
                { cards, quickReplies: ['Confirmar pedido'] }
            );
        },

        cartQty(id) {
            return Alpine.store('cart').items.find(i => i.productId === id)?.quantity ?? 0;
        },

        addToCart(card) {
            const id = card.id ?? card.productId;
            let destination = 'kitchen';
            const cat = (this.menuData?.categories ?? []).find(c => c.products.some(p => p.id === id));
            if (cat) destination = cat.destination;

            const variants = card.variants || [];
            if (variants.length > 0) {
                this.pendingVariantProd = { id, name: card.name, destination, variants };
                const qrs = variants.map(v => v.name + ' (' + Number(v.price).toFixed(2).replace('.', ',') + ' €)');
                this.botDelay('¿Cómo lo quieres? Elige una opción:', { quickReplies: qrs });
                return;
            }

            Alpine.store('cart').add({ id, name: card.name, price: card.price, destination, removable: [], extras: [] });
            this.lastDiscussedProduct = { id, name: card.name, price: card.price, destination, variants: card.variants || [] };
        },

        decreaseQty(card) {
            const key = card._key ?? ((card.id ?? card.productId) + ':none');
            Alpine.store('cart').dec(key);
        },

        decreaseQtyMin1(item) {
            const existing = Alpine.store('cart').items.find(i => i.productId === item.id);
            if (!existing || existing.quantity <= 1) return;
            existing.quantity--;
        },

        showCartNotif(name) {
            const id = ++this.cartNotifSeq;
            if (this.cartNotifs.length >= 3) this.cartNotifs.shift();
            this.cartNotifs.push({ id, name, leaving: false });
            setTimeout(() => {
                const notif = this.cartNotifs.find(n => n.id === id);
                if (notif) notif.leaving = true;
                setTimeout(() => {
                    this.cartNotifs = this.cartNotifs.filter(n => n.id !== id);
                }, 250);
            }, 2500);
        },

        removeCartItem(id) {
            const item = Alpine.store('cart').items.find(i => i.productId === id);
            Alpine.store('cart').items = Alpine.store('cart').items.filter(i => i.productId !== id);
            if (item) {
                Alpine.store('cart')._showDeletePill(item.name);
                this.showCartNotif(item.name);
                const qrs = (this.menuData?.categories ?? []).map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);
                this.pushMsg({ type: 'bot',
                    text: '🗑️ ' + item.name + ' eliminado del pedido.',
                    quickReplies: Alpine.store('cart').items.length ? ['Confirmar pedido', ...qrs] : qrs,
                });
            }
        },

        showCartSummary() {
            if (!this.chatCart.length) {
                const qrs = (this.menuData?.categories ?? []).map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);
                this.botDelay('Tu pedido está vacío. ¡Elige algo primero! 😊', { quickReplies: qrs });
                return;
            }
            this.botDelay('¡Aquí está tu pedido! ¿Confirmamos? 🛒', {
                cartLive: true,
                quickReplies: ['Confirmar pedido', 'Seguir eligiendo'],
            });
        },

        async confirmOrder() {
            if (!this.chatCart.length) {
                this.botDelay('No hay nada en tu pedido. ¡Elige algo primero!');
                return;
            }
            this.sending  = true;
            this.isTyping = true;
            this.error    = null;
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
                        items: this.chatCart.map(i => ({
                            product_id:    i.id,
                            quantity:      i.qty,
                            modifications: [],
                        })),
                    }),
                });
                const data = await res.json();
                this.isTyping = false;
                if (res.ok && data.success) {
                    Alpine.store('cart').items = [];
                    const bill       = Alpine.store('bill');
                    bill.active      = true;
                    bill.requested   = false;
                    bill.method      = null;
                    bill.paymentDone = false;
                    this.pushMsg({ type: 'system',
                        text: '✅ Pedido #' + data.order_id + ' confirmado — en preparación' });
                    this.pushMsg({ type: 'bot',
                        text: '¡Tu pedido está en camino! En unos minutos te lo llevamos a la mesa 🚀',
                        quickReplies: ['📋 Nuevo pedido'] });
                } else {
                    this.isTyping = false;
                    this.error = data.message ?? 'Error al confirmar el pedido.';
                }
            } catch {
                this.isTyping = false;
                this.error = 'Error de conexión. Inténtalo de nuevo.';
            } finally {
                this.sending = false;
                this.$nextTick(() => this.scrollBottom());
            }
        },

        async sendMessage() {
            const text = this.input.trim();
            if (!text || this.sending || this.isTyping) return;
            this.input = '';
            this.pushMsg({ type: 'user', text, time: Date.now() });
            const norm  = s => (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
            const lower = norm(text);
            const cats  = this.menuData?.categories ?? [];

            // Coincidencia exacta con nombre de categoría
            const exactCat = cats.find(c => norm(c.name) === lower);
            if (exactCat) { this.showCategoryCards(exactCat); return; }

            // Coincidencia parcial: el mensaje contiene el nombre de una categoría
            // Permite "qué hay de postres", "ponme los entrantes", "ver bebidas", etc.
            const mentionedCat = cats.find(c => lower.includes(norm(c.name)));
            if (mentionedCat) { this.showCategoryCards(mentionedCat); return; }

            // Si el mensaje tiene intención de explorar una categoría pero ninguna
            // coincidió con las disponibles → mostrar las categorías reales en lugar
            // de dejar que la IA invente o liste categorías que no existen.
            const isCategoryBrowse = /\b(que\s+hay\s+(de|en)|que\s+teneis\s+de|teneis\s+de|hay\s+de|ver\s+(los|las|el|la|todos|todas)|mostrar\s+(los|las)|cuales?\s+son\s+(las?\s+)?categor|que\s+(tipo|categoria|categorias)|alguna?\s+opcion)\b/.test(lower);
            if (isCategoryBrowse) {
                const qrs = cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);
                this.botDelay(
                    'No tenemos esa categoría en este momento 😊 Aquí tienes las disponibles:',
                    { quickReplies: qrs }
                );
                return;
            }

            if (lower.includes('mi pedido') ||
                lower.includes('ver pedido') ||
                lower.includes('carrito'))                 { this.showCartSummary(); return; }
            if (lower.includes('confirmar') &&
                lower.includes('pedido'))                  { this.confirmOrder(); return; }

            // Interceptar "muéstrame la carta / el menú / qué hay" →
            // mostrar chips de categoría en lugar de mandar al AI a listar todo
            const hasCarta = /\b(carta|menu|menus|platos|oferta)\b/.test(lower);
            if (hasCarta) {
                const qrs = cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);
                this.botDelay(
                    'Aquí tienes las categorías disponibles 😊 Toca la que más te apetezca:',
                    { quickReplies: qrs }
                );
                return;
            }

            if (this.tryRemoveByName(lower, text)) return;
            if (this.tryAddByName(lower)) return;
            await this.sendToAI(text);
        },

        tryRemoveByName(lower, originalText) {
            if (!this.chatCart.length) return false;
            const norm = s => (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
            const removePatterns = [/\b(quita|quitar|elimina|eliminar|borra|borrar|saca|sacar|cancela|cancelar|no quiero|sin )\b/];
            if (!removePatterns.some(p => p.test(norm(lower)))) return false;
            const nl = norm(lower);
            for (const item of this.chatCart) {
                const words = norm(item.name).split(/\s+/).filter(w => w.length > 3);
                if (words.some(w => nl.includes(w))) {
                    Alpine.store('cart').items = Alpine.store('cart').items.filter(i => i.productId !== item.id);
                    Alpine.store('cart')._showDeletePill(item.name);
                    const qrs = (this.menuData?.categories ?? []).map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);
                    this.pushMsg({ type: 'bot',
                        text: '🗑️ ' + item.name + ' eliminado del pedido. ¿Seguimos? 😊',
                        quickReplies: this.chatCart.length ? ['Confirmar pedido', ...qrs] : qrs,
                    });
                    return true;
                }
            }
            return false;
        },

        tryAddByName(lower) {
            if (!this.menuData) return false;

            // Referencia contextual: "añádelo", "lo mismo", "2 más de eso", etc.
            const ctxPat = [
                /\b(a[ñn][aá]delo|a[ñn][aá]dela|ponlo|ponla|lo\s+mismo|lo\s+anterior)\b/,
                /\b(m[aá]s\s+de\s+(eso|ese|esa)|[1-5]\s+m[aá]s(\s+de\s+(eso|ese|esa))?|uno\s+m[aá]s|dos\s+m[aá]s|tres\s+m[aá]s)\b/,
                /\b(a[ñn][aá]de|agrega|ponme|dame|quiero)\s+([1-5]|uno|dos|tres|cuatro|cinco)\s+m[aá]s\b/,
            ];
            if (ctxPat.some(p => p.test(lower)) && this.lastDiscussedProduct) {
                const numWords = { uno: 1, una: 1, dos: 2, tres: 3, cuatro: 4, cinco: 5 };
                let qty = 1;
                const dMatch = lower.match(/\b([1-5])\b/);
                if (dMatch) { qty = parseInt(dMatch[1]); }
                else { for (const [w, n] of Object.entries(numWords)) { if (new RegExp(`\\b${w}\\b`).test(lower)) { qty = n; break; } } }

                const found = this.lastDiscussedProduct;
                const cats  = this.menuData.categories;
                const cat   = cats.find(c => c.products.some(p => p.id === found.id));
                const dest  = cat?.destination ?? found.destination ?? 'kitchen';

                if ((found.variants || []).length > 0) {
                    this.pendingVariantProd = { id: found.id, name: found.name, destination: dest, variants: found.variants, qty };
                    const qrs = found.variants.map(v => v.name + ' (' + Number(v.price).toFixed(2).replace('.', ',') + ' €)');
                    this.pushMsg({ type: 'bot', text: '¿Cómo lo quieres?', quickReplies: qrs });
                    return true;
                }
                const existing = Alpine.store('cart').items.find(i => i.productId === found.id);
                if (existing) {
                    existing.quantity += qty;
                } else {
                    Alpine.store('cart').add({ id: found.id, name: found.name, price: found.price, destination: dest, removable: [], extras: [] });
                    if (qty > 1) {
                        const item = Alpine.store('cart').items.find(i => i.productId === found.id);
                        if (item) item.quantity = qty;
                    }
                }
                this.pushMsg({
                    type: 'bot',
                    text: '✅ ' + (qty > 1 ? qty + '× ' : '') + found.name + ' añadido. ¿Algo más?',
                    quickReplies: [...cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name), 'Confirmar pedido'],
                });
                return true;
            }
            // Si hay varios platos discutidos y el usuario referencia "eso" de forma ambigua
            if (ctxPat.some(p => p.test(lower)) && this.lastDiscussedProducts.length > 1) {
                const qrs = this.lastDiscussedProducts.map(p => p.name);
                this.pushMsg({ type: 'bot', text: '¿Cuál de estos platos quieres añadir?', quickReplies: qrs });
                return true;
            }

            const blockPatterns = [
                /\b(modifica|cambia|actualiza|edita|borra|elimina|quita)\b/,
                /\b(precio|coste|cuesta|cu[aá]nto|vale|valor)\b/,
                /[¿?]/,
                /\b(qu[eé]|cu[aá]l|c[oó]mo|cu[aá]ndo|d[oó]nde|cu[aá]ntos)\b/,
                /\b(lleva|tiene|contiene|incluye|hay)\b/,
                /\b(alergi[ao]|al[eé]rgeno|intolerancia|gluten|lactosa)\b/,
                /\b(ingrediente|receta|composici[oó]n|preparaci[oó]n)\b/,
                /\b(informaci[oó]n|info|saber|conocer|explicar|describir|d[eé]cuéntame)\b/,
                /\bno\s+(quiero|me|le|pido|necesito)\b/,
                /\b(s[oó]lo|solo|sin)\s+\w/,
            ];
            if (blockPatterns.some(p => p.test(lower))) return false;
            const intentPatterns = [
                /\b(quiero|quisiera|deseo)\b/,
                /\b(dame|deme|tr[aá]eme|trae)\b/,
                /\b(ponme|pon|ponednos)\b/,
                /\b(agrega|agr[eé]game|a[ñn]ade|a[ñn][aá]deme)\b/,
                /\b(pido|pedimos|pedir)\b/,
                /\b(necesito|necesitamos)\b/,
                /\bme\s+(pones|traes|das|puedes\s+traer)\b/,
            ];
            const quantityOnlyIntent = lower.trim().length < 45
                && /^(un[ao]?|dos|tres|cuatro|cinco|[1-5])\s+\w/.test(lower.trim());
            if (!intentPatterns.some(p => p.test(lower)) && !quantityOnlyIntent) return false;
            const allProducts = this.menuData.categories.flatMap(c =>
                c.products.map(p => ({ id: p.id, name: p.name, price: p.price,
                    variants: p.variants || [],
                    destination: c.destination, emoji: this.getCategoryEmoji(c.name) }))
            );
            const found = allProducts.find(p => {
                const name = p.name.toLowerCase();
                const esc  = name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                return new RegExp(`\\b${esc}\\b`).test(lower) || lower === name;
            });
            if (!found) return false;
            const numWords = { uno: 1, una: 1, dos: 2, tres: 3, cuatro: 4, cinco: 5 };
            let qty = 1;
            const digitMatch = lower.match(/\b([1-5])\b/);
            if (digitMatch) {
                qty = parseInt(digitMatch[1]);
            } else {
                for (const [word, num] of Object.entries(numWords)) {
                    if (new RegExp(`\\b${word}\\b`).test(lower)) { qty = num; break; }
                }
            }
            const cats = this.menuData.categories;
            if (found.variants.length > 0) {
                this.pendingVariantProd = { id: found.id, name: found.name, destination: found.destination || 'kitchen', variants: found.variants, qty };
                const qrs = found.variants.map(v => v.name + ' (' + Number(v.price).toFixed(2).replace('.', ',') + ' €)');
                this.pushMsg({ type: 'bot', text: '¿Cómo lo quieres?', quickReplies: qrs });
                return true;
            }
            const existingStore = Alpine.store('cart').items.find(i => i.productId === found.id);
            if (existingStore) {
                existingStore.quantity += qty;
            } else {
                for (let _i = 0; _i < qty; _i++) {
                    Alpine.store('cart').add({ id: found.id, name: found.name, price: found.price, destination: found.destination || 'kitchen', removable: [], extras: [] });
                }
            }
            this.pushMsg({
                type: 'bot',
                text: '✅ ' + (qty > 1 ? qty + '× ' : '') + found.name + ' añadido al pedido. ¿Algo más?',
                quickReplies: [...cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name), 'Confirmar pedido'],
            });
            return true;
        },

        async sendToAI(text) {
            if (!this.conversationId || this.closed) {
                const cats = this.menuData?.categories ?? [];
                this.botDelay('Por favor elige una categoría para empezar 😊',
                    { quickReplies: cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name) });
                return;
            }
            this.sending  = true;
            this.isTyping = true;
            this.error    = null;
            try {
                const res = await fetch('/api/v1/chat/' + this.conversationId + '/message', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({
                        message:    text,
                        cart_items: this.chatCart.map(i => ({ product_id: i.id, name: i.name, qty: i.qty })),
                    }),
                });
                const data = await res.json();
                this.isTyping = false;
                if (res.ok && data.success) {
                    const cats  = this.menuData?.categories ?? [];
                    const qrs   = cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);

                    // Solo mostrar tarjetas de productos que existan en el menuData cargado.
                    const menuProductIds = new Set(cats.flatMap(c => c.products.map(p => p.id)));
                    const cards = (data.data.cards ?? [])
                        .filter(c => menuProductIds.has(c.id))
                        .map(c => {
                            const matchedCat = cats.find(cat => cat.products.some(p => p.id === c.id));
                            return {
                                id:          c.id,
                                name:        c.name,
                                price:       c.price,
                                description: c.description,
                                image:       c.image ?? null,
                                destination: matchedCat?.destination ?? 'kitchen',
                                variants:    matchedCat?.products.find(p => p.id === c.id)?.variants ?? [],
                                allergens:   (c.allergens ?? []).map(a => ({
                                    name: a,
                                    ...getAllergenIcon(a),
                                })),
                                foodIcon: getFoodIcon(matchedCat?.name ?? '', c.name),
                                emoji:    getCategoryEmoji(matchedCat?.name ?? ''),
                            };
                        });

                    // Actualizar contexto del último plato discutido para referencias futuras
                    if (cards.length === 1) {
                        this.lastDiscussedProduct  = cards[0];
                        this.lastDiscussedProducts = cards;
                    } else if (cards.length > 1) {
                        this.lastDiscussedProduct  = null;
                        this.lastDiscussedProducts = cards;
                    }

                    // Ejecutar acciones de carrito dictadas por la IA
                    for (const action of (data.data.actions ?? [])) {
                        if (action.type === 'add') {
                            const matchCat  = cats.find(c => c.products.some(p => p.id === action.product_id));
                            const matchProd = matchCat?.products.find(p => p.id === action.product_id);
                            if (matchProd && matchCat) {
                                const existing = Alpine.store('cart').items.find(i => i.productId === matchProd.id);
                                if (existing) {
                                    existing.quantity += (action.qty || 1);
                                } else {
                                    Alpine.store('cart').add({ id: matchProd.id, name: matchProd.name, price: matchProd.price, destination: matchCat.destination, removable: [], extras: [] });
                                    if ((action.qty || 1) > 1) {
                                        const ci = Alpine.store('cart').items.find(i => i.productId === matchProd.id);
                                        if (ci) ci.quantity = action.qty;
                                    }
                                }
                                this.lastDiscussedProduct = { id: matchProd.id, name: matchProd.name, price: matchProd.price, destination: matchCat.destination, variants: matchProd.variants ?? [] };
                            }
                        } else if (action.type === 'remove') {
                            Alpine.store('cart').items = Alpine.store('cart').items.filter(i => i.productId !== action.product_id);
                        }
                    }

                    this.pushMsg({
                        type:         'bot',
                        text:         data.data.reply,
                        cards:        cards.length ? cards : undefined,
                        quickReplies: [...qrs, 'Confirmar pedido'],
                    });
                    if (data.data.closed) this.closed = true;
                } else {
                    this.error = data.message ?? 'Error al enviar el mensaje.';
                }
            } catch {
                this.isTyping = false;
                this.error = 'Error de conexión. Inténtalo de nuevo.';
            } finally {
                this.sending = false;
                this.$nextTick(() => this.scrollBottom());
            }
        },

        getCategoryEmoji(name) { return getCategoryEmoji(name); },
        getFoodIcon(cat, prod) { return getFoodIcon(cat, prod); },
        getAllergenIcon(name)   { return getAllergenIcon(name); },
        scrollBottom() {
            this.$nextTick(() => {
                requestAnimationFrame(() => {
                    this.$refs.chatEnd?.scrollIntoView({ block: 'end' });
                });
            });
        },
    }));
}
