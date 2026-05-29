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
        return { svgId: 'gluten',      label: 'Gluten' };
    if (/crustaceo|gamba|langosta|langostino|cangrejo|bogavante|cigala/.test(n))
        return { svgId: 'crustaceans', label: 'Crustáceos' };
    if (/huevo|yema|clara|ovoalbumina/.test(n))
        return { svgId: 'egg',         label: 'Huevo' };
    if (/pescado|merluza|bacalao|salmon|atun|anchoa|sardina|boqueron|lenguado|dorada|lubina/.test(n))
        return { svgId: 'fish',        label: 'Pescado' };
    if (/cacahuete|mani/.test(n))
        return { svgId: 'peanuts',     label: 'Cacahuetes' };
    if (/soja|soya|tofu|edamame/.test(n))
        return { svgId: 'soy',         label: 'Soja' };
    if (/leche|lacteo|lactosa|queso|mantequilla|nata|yogur|suero|caseina/.test(n))
        return { svgId: 'milk',        label: 'Lácteos' };
    if (/nuez|nueces|almendra|avellana|pistacho|anacardo|macadamia|pacana|brasil|castana/.test(n))
        return { svgId: 'nuts',        label: 'Frutos secos' };
    if (/apio/.test(n))
        return { svgId: 'celery',      label: 'Apio' };
    if (/mostaza/.test(n))
        return { svgId: 'mustard',     label: 'Mostaza' };
    if (/sesamo|tahini|tahina/.test(n))
        return { svgId: 'sesame',      label: 'Sésamo' };
    if (/sulfit|azufre|so2|dioxido/.test(n))
        return { svgId: 'sulphites',   label: 'Sulfitos' };
    if (/altramuz|lupino|lupina/.test(n))
        return { svgId: 'lupin',       label: 'Altramuces' };
    if (/molusco|calamar|pulpo|ostra|almeja|mejillon|sepia|chipiro/.test(n))
        return { svgId: 'molluscs',    label: 'Moluscos' };
    return { svgId: null, label: name };
}

/**
 * Returns an inline CSS string for a quick-reply button based on its label.
 *
 * @param {string} qr - Quick reply label.
 * @returns {string} Inline CSS style string.
 */
export function getQrStyle(qr) {
    const base = 'border-radius:9999px; padding:5px 12px; font-size:12px; font-family:\'Space Grotesk\',sans-serif; font-weight:600; cursor:pointer; transition:all 150ms ease; border:1px solid; ';
    if (qr === 'Confirmar pedido')
        return base + 'background:rgba(34,197,94,0.18); color:#22C55E; border-color:rgba(34,197,94,0.5);';
    if (qr === 'Ver mi pedido')
        return base + 'background:rgba(34,211,238,0.15); color:#22D3EE; border-color:rgba(34,211,238,0.45);';
    return base + 'background:rgba(46,80,176,0.22); color:#8FA8E8; border-color:rgba(46,80,176,0.5);';
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
        menuData:       null,
        msgSeq:         0,
        _now:           Date.now(),
        _ticker:        null,
        cartNotifs:     [],
        cartNotifSeq:   0,

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
            if (!this.menuData) await this.loadMenu();
            if (!this.conversationId) await this.initConversation();
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

        async initConversation() {
            const cats = this.menuData?.categories ?? [];
            const qrs  = cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);
            this.pushMsg({ type: 'system',
                text: (this.menuData?.table ?? 'Mesa') + ' · ' + (this.menuData?.restaurant ?? '') });
            this.pushMsg({ type: 'bot',
                text: '¡Hola! Soy Zampi, tu asistente de pedidos 🍔 ¿Qué te apetece hoy?',
                quickReplies: qrs.length ? [...qrs, 'Ver mi pedido'] : ['Ver mi pedido'] });
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
            const cats    = this.menuData?.categories ?? [];
            const matched = cats.find(c => label === this.getCategoryEmoji(c.name) + ' ' + c.name || c.name === label);
            if (matched) {
                this.showCategoryCards(matched);
            } else if (label === 'Ver mi pedido') {
                this.showCartSummary();
            } else if (label === 'Confirmar pedido') {
                Alpine.store('cart').open = true;
            } else if (label === 'Seguir eligiendo') {
                const qrs = cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);
                this.botDelay('¿Qué más te gustaría pedir?', { quickReplies: [...qrs, 'Ver mi pedido'] });
            } else if (label === '📋 Nuevo pedido') {
                Alpine.store('cart').items = [];
                const qrs = cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);
                this.botDelay('¡Claro! ¿Qué te gustaría pedir?', { quickReplies: [...qrs, 'Ver mi pedido'] });
            } else {
                this.sendToAI(label);
            }
        },

        showCategoryCards(category) {
            const emoji = this.getCategoryEmoji(category.name);
            const cards = category.products.map(p => ({
                id:    p.id,
                name:  p.name,
                desc:  p.description || '',
                price: p.price,
                emoji,
            }));
            this.botDelay(
                'Aquí tienes nuestra selección de ' + category.name + ' 😋',
                { cards, quickReplies: ['Ver mi pedido', 'Confirmar pedido'] }
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
            Alpine.store('cart').add({ id, name: card.name, price: card.price, destination, removable: [], extras: [] });
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
                this.showCartNotif(item.name);
                const qrs = (this.menuData?.categories ?? []).map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);
                this.pushMsg({ type: 'bot',
                    text: '🗑️ ' + item.name + ' eliminado del pedido.',
                    quickReplies: Alpine.store('cart').items.length ? ['Ver mi pedido', 'Confirmar pedido', ...qrs] : qrs,
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
            const lower = text.toLowerCase();
            const cats  = this.menuData?.categories ?? [];
            const matched = cats.find(c => c.name.toLowerCase() === lower);
            if (matched)                                   { this.showCategoryCards(matched); return; }
            if (lower.includes('mi pedido') ||
                lower.includes('ver pedido') ||
                lower.includes('carrito'))                 { this.showCartSummary(); return; }
            if (lower.includes('confirmar') &&
                lower.includes('pedido'))                  { this.confirmOrder(); return; }
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
                    const qrs = (this.menuData?.categories ?? []).map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);
                    this.pushMsg({ type: 'bot',
                        text: '🗑️ ' + item.name + ' eliminado del pedido. ¿Seguimos? 😊',
                        quickReplies: this.chatCart.length ? ['Ver mi pedido', 'Confirmar pedido', ...qrs] : qrs,
                    });
                    return true;
                }
            }
            return false;
        },

        tryAddByName(lower) {
            if (!this.menuData) return false;
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
            const existingStore = Alpine.store('cart').items.find(i => i.productId === found.id);
            if (existingStore) {
                existingStore.quantity += qty;
            } else {
                for (let _i = 0; _i < qty; _i++) {
                    Alpine.store('cart').add({ id: found.id, name: found.name, price: found.price, destination: found.destination || 'kitchen', removable: [], extras: [] });
                }
            }
            const cats = this.menuData.categories;
            this.pushMsg({
                type: 'bot',
                text: '✅ ' + (qty > 1 ? qty + '× ' : '') + found.name + ' añadido al pedido. ¿Algo más?',
                quickReplies: [...cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name), 'Ver mi pedido', 'Confirmar pedido'],
            });
            return true;
        },

        async sendToAI(text) {
            if (!this.conversationId || this.closed) {
                const cats = this.menuData?.categories ?? [];
                this.botDelay('Por favor elige una categoría para empezar 😊',
                    { quickReplies: [...cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name), 'Ver mi pedido'] });
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
                    body: JSON.stringify({ message: text }),
                });
                const data = await res.json();
                this.isTyping = false;
                if (res.ok && data.success) {
                    const cats  = this.menuData?.categories ?? [];
                    const qrs   = cats.map(c => this.getCategoryEmoji(c.name) + ' ' + c.name);
                    const cards = (data.data.cards ?? []).map(c => {
                        const matchedCat = cats.find(cat => cat.products.some(p => p.id === c.id));
                        return {
                            id:          c.id,
                            name:        c.name,
                            price:       c.price,
                            description: c.description,
                            image:       c.image ?? null,
                            allergens:   (c.allergens ?? []).map(a => ({
                                name: a,
                                ...getAllergenIcon(a),
                            })),
                            foodIcon: getFoodIcon(matchedCat?.name ?? '', c.name),
                            emoji:    getCategoryEmoji(matchedCat?.name ?? ''),
                        };
                    });
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
        getQrStyle(qr)         { return getQrStyle(qr); },

        onQrEnter(el, qr) {
            if (qr === 'Confirmar pedido') { el.style.background = '#16A34A'; el.style.color = '#fff'; }
            else if (qr === 'Ver mi pedido') { el.style.background = '#0891B2'; el.style.color = '#fff'; }
            else { el.style.background = '#1A3380'; el.style.color = '#fff'; }
        },

        onQrLeave(el, qr) {
            if (qr === 'Confirmar pedido') { el.style.background = 'rgba(34,197,94,0.18)'; el.style.color = '#22C55E'; }
            else if (qr === 'Ver mi pedido') { el.style.background = 'rgba(34,211,238,0.15)'; el.style.color = '#22D3EE'; }
            else { el.style.background = 'rgba(46,80,176,0.22)'; el.style.color = '#8FA8E8'; }
        },

        scrollBottom() {
            this.$nextTick(() => {
                requestAnimationFrame(() => {
                    this.$refs.chatEnd?.scrollIntoView({ block: 'end' });
                });
            });
        },
    }));
}
